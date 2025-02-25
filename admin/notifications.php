<?php
session_start();
require_once "../config/database.php";

if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Marquer une notification comme lue
if(isset($_POST['action']) && $_POST['action'] === 'mark_read') {
    $query = "UPDATE notifications SET read_at = NOW() WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_POST['notification_id']);
    $stmt->execute();
}

// Marquer toutes les notifications comme lues
if(isset($_POST['action']) && $_POST['action'] === 'mark_all_read') {
    $query = "UPDATE notifications SET read_at = NOW() WHERE read_at IS NULL";
    $stmt = $db->prepare($query);
    $stmt->execute();
}

// Supprimer une notification
if(isset($_POST['action']) && $_POST['action'] === 'delete') {
    $query = "DELETE FROM notifications WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_POST['notification_id']);
    $stmt->execute();
}

// Récupérer les notifications
$query = "SELECT * FROM notifications ORDER BY created_at DESC LIMIT 50";
$stmt = $db->prepare($query);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Compter les notifications non lues
$query = "SELECT COUNT(*) as count FROM notifications WHERE read_at IS NULL";
$stmt = $db->prepare($query);
$stmt->execute();
$unread = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Admin Uniprice</title>
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
        .notification-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            transition: background-color 0.3s;
        }
        .notification-item:hover {
            background-color: #f8f9fa;
        }
        .notification-item.unread {
            background-color: #e8f4fd;
        }
        .notification-item.unread:hover {
            background-color: #d8ebfb;
        }
        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        .notification-time {
            font-size: 0.85rem;
            color: #6c757d;
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
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog"></i> Paramètres
                        </a>
                        <a class="nav-link active" href="notifications.php">
                            <i class="fas fa-bell"></i> Notifications
                            <?php if($unread > 0): ?>
                                <span class="badge bg-danger"><?php echo $unread; ?></span>
                            <?php endif; ?>
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
                    <h2>Notifications</h2>
                    <?php if($unread > 0): ?>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="action" value="mark_all_read">
                            <button type="submit" class="btn btn-secondary">
                                <i class="fas fa-check-double"></i> Tout marquer comme lu
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Notifications List -->
                <div class="card">
                    <div class="card-body p-0">
                        <?php if(empty($notifications)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucune notification</p>
                            </div>
                        <?php else: ?>
                            <?php foreach($notifications as $notification): ?>
                                <div class="notification-item <?php echo is_null($notification['read_at']) ? 'unread' : ''; ?>">
                                    <div class="d-flex align-items-center">
                                        <div class="notification-icon bg-<?php 
                                            switch($notification['type']) {
                                                case 'order': echo 'primary'; break;
                                                case 'user': echo 'success'; break;
                                                case 'system': echo 'warning'; break;
                                                default: echo 'info';
                                            }
                                        ?> me-3">
                                            <i class="fas fa-<?php 
                                                switch($notification['type']) {
                                                    case 'order': echo 'shopping-cart'; break;
                                                    case 'user': echo 'user'; break;
                                                    case 'system': echo 'cog'; break;
                                                    default: echo 'bell';
                                                }
                                            ?>"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0"><?php echo $notification['title']; ?></h6>
                                                <div class="notification-time">
                                                    <?php 
                                                        $date = new DateTime($notification['created_at']);
                                                        echo $date->format('d/m/Y H:i');
                                                    ?>
                                                </div>
                                            </div>
                                            <p class="mb-0"><?php echo $notification['message']; ?></p>
                                            <?php if(!empty($notification['link'])): ?>
                                                <a href="<?php echo $notification['link']; ?>" class="btn btn-link btn-sm px-0">
                                                    Voir plus <i class="fas fa-arrow-right ms-1"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ms-3">
                                            <?php if(is_null($notification['read_at'])): ?>
                                                <form method="post" style="display: inline;">
                                                    <input type="hidden" name="action" value="mark_read">
                                                    <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
