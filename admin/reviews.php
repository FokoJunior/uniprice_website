<?php
session_start();
require_once "../config/database.php";

if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Gestion des actions
if(isset($_POST['action'])) {
    switch($_POST['action']) {
        case 'approve':
            $query = "UPDATE reviews SET status = 'approved', moderated_at = NOW() WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $_POST['review_id']);
            if($stmt->execute()) {
                $success_message = "Avis approuvé avec succès.";
            }
            break;

        case 'reject':
            $query = "UPDATE reviews SET status = 'rejected', moderated_at = NOW() WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $_POST['review_id']);
            if($stmt->execute()) {
                $success_message = "Avis rejeté avec succès.";
            }
            break;

        case 'delete':
            $query = "DELETE FROM reviews WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $_POST['review_id']);
            if($stmt->execute()) {
                $success_message = "Avis supprimé avec succès.";
            }
            break;

        case 'reply':
            $query = "UPDATE reviews SET admin_reply = :reply, replied_at = NOW() WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':reply', $_POST['reply']);
            $stmt->bindParam(':id', $_POST['review_id']);
            if($stmt->execute()) {
                $success_message = "Réponse ajoutée avec succès.";
            }
            break;
    }
}

// Filtres
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$rating_filter = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;
$product_filter = isset($_GET['product']) ? $_GET['product'] : '';

// Construction de la requête
$query = "SELECT r.*, p.name as product_name, u.username 
          FROM reviews r 
          LEFT JOIN products p ON p.id = r.product_id 
          LEFT JOIN users u ON u.id = r.user_id 
          WHERE 1=1";

if($status_filter) {
    $query .= " AND r.status = :status";
}
if($rating_filter) {
    $query .= " AND r.rating = :rating";
}
if($product_filter) {
    $query .= " AND p.id = :product_id";
}

$query .= " ORDER BY r.created_at DESC";

$stmt = $db->prepare($query);

if($status_filter) {
    $stmt->bindParam(':status', $status_filter);
}
if($rating_filter) {
    $stmt->bindParam(':rating', $rating_filter);
}
if($product_filter) {
    $stmt->bindParam(':product_id', $product_filter);
}

$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste des produits pour le filtre
$query = "SELECT id, name FROM products ORDER BY name";
$stmt = $db->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Avis - Admin Uniprice</title>
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
        .rating {
            color: #ffc107;
        }
        .review-card {
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
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
                        <a class="nav-link" href="orders.php">
                            <i class="fas fa-shopping-cart"></i> Commandes
                        </a>
                        <a class="nav-link" href="categories.php">
                            <i class="fas fa-tags"></i> Catégories
                        </a>
                        <a class="nav-link active" href="reviews.php">
                            <i class="fas fa-star"></i> Avis
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
                <h2 class="mb-4">Gestion des Avis</h2>

                <?php if(isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="get" class="row g-3">
                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="">Tous les statuts</option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>En attente</option>
                                    <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approuvé</option>
                                    <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejeté</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="rating" class="form-select">
                                    <option value="">Toutes les notes</option>
                                    <?php for($i = 5; $i >= 1; $i--): ?>
                                        <option value="<?php echo $i; ?>" <?php echo $rating_filter === $i ? 'selected' : ''; ?>>
                                            <?php echo $i; ?> étoiles
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="product" class="form-select">
                                    <option value="">Tous les produits</option>
                                    <?php foreach($products as $product): ?>
                                        <option value="<?php echo $product['id']; ?>" <?php echo $product_filter == $product['id'] ? 'selected' : ''; ?>>
                                            <?php echo $product['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Reviews List -->
                <?php foreach($reviews as $review): ?>
                    <div class="card review-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">
                                    <?php echo $review['username'] ?: 'Utilisateur anonyme'; ?>
                                    <small class="text-muted ms-2">
                                        <?php echo date('d/m/Y H:i', strtotime($review['created_at'])); ?>
                                    </small>
                                </h6>
                                <div class="rating">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-o'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <span class="badge bg-<?php 
                                switch($review['status']) {
                                    case 'pending': echo 'warning'; break;
                                    case 'approved': echo 'success'; break;
                                    case 'rejected': echo 'danger'; break;
                                }
                            ?>">
                                <?php 
                                    switch($review['status']) {
                                        case 'pending': echo 'En attente'; break;
                                        case 'approved': echo 'Approuvé'; break;
                                        case 'rejected': echo 'Rejeté'; break;
                                    }
                                ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2 text-muted">Produit : <?php echo $review['product_name']; ?></h6>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                            
                            <?php if($review['admin_reply']): ?>
                                <div class="alert alert-info mt-3">
                                    <h6 class="alert-heading">Réponse de l'administrateur :</h6>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['admin_reply'])); ?></p>
                                    <small class="text-muted">
                                        Répondu le <?php echo date('d/m/Y H:i', strtotime($review['replied_at'])); ?>
                                    </small>
                                </div>
                            <?php endif; ?>

                            <div class="mt-3">
                                <?php if($review['status'] === 'pending'): ?>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-check"></i> Approuver
                                        </button>
                                    </form>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="action" value="reject">
                                        <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-times"></i> Rejeter
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" 
                                        data-bs-target="#replyModal" 
                                        data-review-id="<?php echo $review['id']; ?>"
                                        data-current-reply="<?php echo htmlspecialchars($review['admin_reply']); ?>">
                                    <i class="fas fa-reply"></i> Répondre
                                </button>
                                
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" 
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet avis ?')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if(empty($reviews)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucun avis trouvé</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Reply Modal -->
    <div class="modal fade" id="replyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Répondre à l'avis</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="reply">
                        <input type="hidden" name="review_id" id="reply_review_id">
                        <div class="mb-3">
                            <label for="reply" class="form-label">Votre réponse</label>
                            <textarea class="form-control" id="reply" name="reply" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Envoyer la réponse</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
    <script>
        // Gestion du modal de réponse
        document.getElementById('replyModal').addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var reviewId = button.getAttribute('data-review-id');
            var currentReply = button.getAttribute('data-current-reply');
            
            this.querySelector('#reply_review_id').value = reviewId;
            this.querySelector('#reply').value = currentReply;
        });
    </script>
</body>
</html>
