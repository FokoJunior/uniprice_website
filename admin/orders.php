<?php
session_start();
require_once "../config/database.php";

// Vérifier si l'admin est connecté
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Gestion des actions
if(isset($_POST['action']) && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    $query = "UPDATE orders SET status = :status WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $new_status);
    $stmt->bindParam(':id', $order_id);
    
    if($stmt->execute()) {
        $success_message = "Statut de la commande mis à jour.";
    } else {
        $error_message = "Erreur lors de la mise à jour du statut.";
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$from_record_num = ($records_per_page * $page) - $records_per_page;

// Filtres
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Construction de la requête
$query = "SELECT o.*, 
          (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
          FROM orders o 
          WHERE 1=1";

if($status_filter) {
    $query .= " AND o.status = :status";
}

if($search) {
    $query .= " AND (o.customer_name LIKE :search OR o.email LIKE :search OR o.phone LIKE :search)";
}

$query .= " ORDER BY o.created DESC LIMIT :from_record_num, :records_per_page";

$stmt = $db->prepare($query);

if($status_filter) {
    $stmt->bindParam(':status', $status_filter);
}

if($search) {
    $search_term = "%{$search}%";
    $stmt->bindParam(':search', $search_term);
}

$stmt->bindParam(':from_record_num', $from_record_num, PDO::PARAM_INT);
$stmt->bindParam(':records_per_page', $records_per_page, PDO::PARAM_INT);

$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Nombre total de commandes pour la pagination
$query = "SELECT COUNT(*) as total FROM orders WHERE 1=1";
if($status_filter) {
    $query .= " AND status = :status";
}
if($search) {
    $query .= " AND (customer_name LIKE :search OR email LIKE :search OR phone LIKE :search)";
}

$stmt = $db->prepare($query);
if($status_filter) {
    $stmt->bindParam(':status', $status_filter);
}
if($search) {
    $stmt->bindParam(':search', $search_term);
}
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$total_rows = $row['total'];
$total_pages = ceil($total_rows / $records_per_page);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Commandes - Admin Uniprice</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #333;
            color: white;
        }
        .sidebar .nav-link {
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            margin: 5px 0;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: #444;
        }
        .sidebar .nav-link i {
            width: 25px;
        }
        .main-content {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 position-fixed sidebar">
                <div class="p-3">
                    <h3 class="text-center mb-4">Admin Uniprice</h3>
                    <nav class="nav flex-column">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Tableau de bord
                        </a>
                        <a class="nav-link" href="products.php">
                            <i class="fas fa-box"></i> Produits
                        </a>
                        <a class="nav-link active" href="orders.php">
                            <i class="fas fa-shopping-cart"></i> Commandes
                        </a>
                        <a class="nav-link" href="categories.php">
                            <i class="fas fa-tags"></i> Catégories
                        </a>
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog"></i> Paramètres
                        </a>
                        <a class="nav-link text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Déconnexion
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-auto main-content">
                <h2 class="mb-4">Gestion des Commandes</h2>

                <?php if(isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <?php if(isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="get" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" placeholder="Rechercher..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-4">
                                <select name="status" class="form-select">
                                    <option value="">Tous les statuts</option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>En attente</option>
                                    <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>En traitement</option>
                                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Terminée</option>
                                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Annulée</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary">Filtrer</button>
                                <a href="orders.php" class="btn btn-secondary">Réinitialiser</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Orders List -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Client</th>
                                        <th>Contact</th>
                                        <th>Articles</th>
                                        <th>Total</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo $order['customer_name']; ?></td>
                                            <td>
                                                <?php echo $order['email']; ?><br>
                                                <?php echo $order['phone']; ?>
                                            </td>
                                            <td><?php echo $order['items_count']; ?> articles</td>
                                            <td><?php echo number_format($order['total_amount'], 0, ',', ' '); ?> FCFA</td>
                                            <td>
                                                <form method="post" style="display: inline;">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                        <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>En attente</option>
                                                        <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>En traitement</option>
                                                        <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Terminée</option>
                                                        <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Annulée</option>
                                                    </select>
                                                </form>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($order['created'])); ?></td>
                                            <td>
                                                <a href="view-order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="print-order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-secondary" target="_blank">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if($total_pages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
