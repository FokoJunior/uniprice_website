<?php
session_start();
require_once "../config/database.php";

if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Période par défaut : 30 derniers jours
$default_period = 30;
$period = isset($_GET['period']) ? (int)$_GET['period'] : $default_period;

// Dates de début et fin
$end_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime("-{$period} days"));

// Statistiques des ventes
$query = "SELECT 
            DATE(created) as date,
            COUNT(*) as orders_count,
            SUM(total_amount) as total_sales
          FROM orders 
          WHERE created BETWEEN :start_date AND :end_date
          AND status != 'cancelled'
          GROUP BY DATE(created)
          ORDER BY date";

$stmt = $db->prepare($query);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);
$stmt->execute();
$sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Produits les plus vendus
$query = "SELECT 
            p.name,
            COUNT(oi.id) as total_sold,
            SUM(oi.quantity) as total_quantity
          FROM order_items oi
          JOIN products p ON p.id = oi.product_id
          JOIN orders o ON o.id = oi.order_id
          WHERE o.created BETWEEN :start_date AND :end_date
          AND o.status != 'cancelled'
          GROUP BY p.id
          ORDER BY total_sold DESC
          LIMIT 10";

$stmt = $db->prepare($query);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);
$stmt->execute();
$top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques par catégorie
$query = "SELECT 
            c.name,
            COUNT(DISTINCT o.id) as orders_count,
            SUM(oi.quantity) as total_quantity,
            SUM(oi.quantity * oi.price) as total_sales
          FROM categories c
          JOIN products p ON p.category_id = c.id
          JOIN order_items oi ON oi.product_id = p.id
          JOIN orders o ON o.id = oi.order_id
          WHERE o.created BETWEEN :start_date AND :end_date
          AND o.status != 'cancelled'
          GROUP BY c.id
          ORDER BY total_sales DESC";

$stmt = $db->prepare($query);
$stmt->bindParam(':start_date', $start_date);
$stmt->bindParam(':end_date', $end_date);
$stmt->execute();
$category_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Préparer les données pour les graphiques
$dates = [];
$orders = [];
$sales = [];

foreach($sales_data as $data) {
    $dates[] = date('d/m', strtotime($data['date']));
    $orders[] = $data['orders_count'];
    $sales[] = $data['total_sales'];
}

$dates_json = json_encode($dates);
$orders_json = json_encode($orders);
$sales_json = json_encode($sales);

// Calcul des totaux
$total_orders = array_sum($orders);
$total_sales = array_sum($sales);
$average_order_value = $total_orders > 0 ? $total_sales / $total_orders : 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - Admin Uniprice</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <a class="nav-link active" href="stats.php">
                            <i class="fas fa-chart-bar"></i> Statistiques
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
                    <h2>Statistiques</h2>
                    <div class="btn-group">
                        <a href="?period=7" class="btn btn-outline-primary <?php echo $period == 7 ? 'active' : ''; ?>">7 jours</a>
                        <a href="?period=30" class="btn btn-outline-primary <?php echo $period == 30 ? 'active' : ''; ?>">30 jours</a>
                        <a href="?period=90" class="btn btn-outline-primary <?php echo $period == 90 ? 'active' : ''; ?>">90 jours</a>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="stat-card bg-primary text-white">
                            <h3><?php echo $total_orders; ?></h3>
                            <p class="mb-0">Commandes</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card bg-success text-white">
                            <h3><?php echo number_format($total_sales, 0, ',', ' '); ?> FCFA</h3>
                            <p class="mb-0">Chiffre d'affaires</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card bg-info text-white">
                            <h3><?php echo number_format($average_order_value, 0, ',', ' '); ?> FCFA</h3>
                            <p class="mb-0">Panier moyen</p>
                        </div>
                    </div>
                </div>

                <!-- Sales Chart -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Évolution des ventes</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>

                <div class="row">
                    <!-- Top Products -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Produits les plus vendus</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Produit</th>
                                                <th>Ventes</th>
                                                <th>Quantité</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($top_products as $product): ?>
                                                <tr>
                                                    <td><?php echo $product['name']; ?></td>
                                                    <td><?php echo $product['total_sold']; ?></td>
                                                    <td><?php echo $product['total_quantity']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category Stats -->
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Statistiques par catégorie</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Catégorie</th>
                                                <th>Commandes</th>
                                                <th>Quantité</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($category_stats as $stat): ?>
                                                <tr>
                                                    <td><?php echo $stat['name']; ?></td>
                                                    <td><?php echo $stat['orders_count']; ?></td>
                                                    <td><?php echo $stat['total_quantity']; ?></td>
                                                    <td><?php echo number_format($stat['total_sales'], 0, ',', ' '); ?> FCFA</td>
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
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
    <script>
        // Graphique des ventes
        var ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo $dates_json; ?>,
                datasets: [{
                    label: 'Chiffre d\'affaires (FCFA)',
                    data: <?php echo $sales_json; ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1,
                    yAxisID: 'y'
                }, {
                    label: 'Nombre de commandes',
                    data: <?php echo $orders_json; ?>,
                    borderColor: 'rgb(255, 99, 132)',
                    tension: 0.1,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    </script>
</body>
</html>
