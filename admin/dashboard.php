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

// Récupérer les statistiques
$stats = array();

// Nombre total de produits
$query = "SELECT COUNT(*) as total FROM products";
$stmt = $db->prepare($query);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['products'] = $row['total'];

// Nombre de commandes en attente
$query = "SELECT COUNT(*) as total FROM orders WHERE status = 'pending'";
$stmt = $db->prepare($query);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['pending_orders'] = $row['total'];

// Chiffre d'affaires total
$query = "SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'";
$stmt = $db->prepare($query);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$stats['total_revenue'] = $row['total'] ?? 0;

// Commandes récentes
$query = "SELECT o.*, 
          (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
          FROM orders o 
          ORDER BY o.created DESC 
          LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Admin Uniprice</title>
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
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
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
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Tableau de bord
                        </a>
                        <a class="nav-link" href="products.php">
                            <i class="fas fa-box"></i> Produits
                        </a>
                        <a class="nav-link" href="orders.php">
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Tableau de bord</h2>
                    <div class="btn-group">
                        <a href="add-product.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nouveau produit
                        </a>
                        <a href="../" class="btn btn-secondary" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Voir le site
                        </a>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="stat-card bg-primary text-white">
                            <h3><?php echo $stats['products']; ?></h3>
                            <p class="mb-0">Produits</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card bg-warning text-white">
                            <h3><?php echo $stats['pending_orders']; ?></h3>
                            <p class="mb-0">Commandes en attente</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card bg-success text-white">
                            <h3><?php echo number_format($stats['total_revenue'], 0, ',', ' '); ?> FCFA</h3>
                            <p class="mb-0">Chiffre d'affaires</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Commandes récentes</h5>
                        <a href="orders.php" class="btn btn-sm btn-primary">Voir tout</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Client</th>
                                        <th>Articles</th>
                                        <th>Total</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo $order['customer_name']; ?></td>
                                            <td><?php echo $order['items_count']; ?> articles</td>
                                            <td><?php echo number_format($order['total_amount'], 0, ',', ' '); ?> FCFA</td>
                                            <td>
                                                <?php
                                                $status_class = [
                                                    'pending' => 'warning',
                                                    'processing' => 'info',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger'
                                                ];
                                                $status_text = [
                                                    'pending' => 'En attente',
                                                    'processing' => 'En traitement',
                                                    'completed' => 'Terminée',
                                                    'cancelled' => 'Annulée'
                                                ];
                                                ?>
                                                <span class="badge bg-<?php echo $status_class[$order['status']]; ?>">
                                                    <?php echo $status_text[$order['status']]; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($order['created'])); ?></td>
                                            <td>
                                                <a href="view-order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
