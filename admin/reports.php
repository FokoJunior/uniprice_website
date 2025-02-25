<?php
session_start();
require_once "../config/database.php";

if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Période du rapport
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Rapport des ventes
function getSalesReport($db, $start_date, $end_date) {
    $query = "SELECT 
                DATE(created) as date,
                COUNT(*) as orders_count,
                SUM(total_amount) as total_sales,
                AVG(total_amount) as average_order
              FROM orders 
              WHERE created BETWEEN :start_date AND :end_date
              AND status != 'cancelled'
              GROUP BY DATE(created)
              ORDER BY date";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Rapport des produits
function getProductsReport($db, $start_date, $end_date) {
    $query = "SELECT 
                p.name,
                p.price,
                COUNT(DISTINCT o.id) as orders_count,
                SUM(oi.quantity) as total_quantity,
                SUM(oi.quantity * oi.price) as total_revenue,
                AVG(oi.price) as average_price
              FROM products p
              LEFT JOIN order_items oi ON oi.product_id = p.id
              LEFT JOIN orders o ON o.id = oi.order_id
              WHERE (o.created BETWEEN :start_date AND :end_date OR o.created IS NULL)
              AND (o.status != 'cancelled' OR o.status IS NULL)
              GROUP BY p.id
              ORDER BY total_revenue DESC";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Rapport des catégories
function getCategoriesReport($db, $start_date, $end_date) {
    $query = "SELECT 
                c.name,
                COUNT(DISTINCT o.id) as orders_count,
                COUNT(DISTINCT p.id) as products_count,
                SUM(oi.quantity) as total_quantity,
                SUM(oi.quantity * oi.price) as total_revenue
              FROM categories c
              LEFT JOIN products p ON p.category_id = c.id
              LEFT JOIN order_items oi ON oi.product_id = p.id
              LEFT JOIN orders o ON o.id = oi.order_id
              WHERE (o.created BETWEEN :start_date AND :end_date OR o.created IS NULL)
              AND (o.status != 'cancelled' OR o.status IS NULL)
              GROUP BY c.id
              ORDER BY total_revenue DESC";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Rapport des clients
function getCustomersReport($db, $start_date, $end_date) {
    $query = "SELECT 
                u.username,
                u.email,
                COUNT(o.id) as orders_count,
                SUM(o.total_amount) as total_spent,
                AVG(o.total_amount) as average_order,
                MIN(o.created) as first_order,
                MAX(o.created) as last_order
              FROM users u
              LEFT JOIN orders o ON o.user_id = u.id
              WHERE (o.created BETWEEN :start_date AND :end_date OR o.created IS NULL)
              AND (o.status != 'cancelled' OR o.status IS NULL)
              GROUP BY u.id
              ORDER BY total_spent DESC";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupérer les données des rapports
$sales_report = getSalesReport($db, $start_date, $end_date);
$products_report = getProductsReport($db, $start_date, $end_date);
$categories_report = getCategoriesReport($db, $start_date, $end_date);
$customers_report = getCustomersReport($db, $start_date, $end_date);

// Calcul des totaux
$total_sales = array_sum(array_column($sales_report, 'total_sales'));
$total_orders = array_sum(array_column($sales_report, 'orders_count'));
$average_order = $total_orders > 0 ? $total_sales / $total_orders : 0;

// Export en CSV
if(isset($_POST['export']) && isset($_POST['report_type'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=rapport_' . $_POST['report_type'] . '_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
    
    switch($_POST['report_type']) {
        case 'sales':
            fputcsv($output, ['Date', 'Nombre de commandes', 'Ventes totales', 'Panier moyen']);
            foreach($sales_report as $row) {
                fputcsv($output, [
                    $row['date'],
                    $row['orders_count'],
                    $row['total_sales'],
                    $row['average_order']
                ]);
            }
            break;
            
        case 'products':
            fputcsv($output, ['Produit', 'Prix', 'Commandes', 'Quantité vendue', 'Chiffre d\'affaires', 'Prix moyen']);
            foreach($products_report as $row) {
                fputcsv($output, [
                    $row['name'],
                    $row['price'],
                    $row['orders_count'],
                    $row['total_quantity'],
                    $row['total_revenue'],
                    $row['average_price']
                ]);
            }
            break;
            
        case 'categories':
            fputcsv($output, ['Catégorie', 'Commandes', 'Produits', 'Quantité vendue', 'Chiffre d\'affaires']);
            foreach($categories_report as $row) {
                fputcsv($output, [
                    $row['name'],
                    $row['orders_count'],
                    $row['products_count'],
                    $row['total_quantity'],
                    $row['total_revenue']
                ]);
            }
            break;
            
        case 'customers':
            fputcsv($output, ['Client', 'Email', 'Commandes', 'Total dépensé', 'Panier moyen', 'Première commande', 'Dernière commande']);
            foreach($customers_report as $row) {
                fputcsv($output, [
                    $row['username'],
                    $row['email'],
                    $row['orders_count'],
                    $row['total_spent'],
                    $row['average_order'],
                    $row['first_order'],
                    $row['last_order']
                ]);
            }
            break;
    }
    
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapports - Admin Uniprice</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/fr.js"></script>
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
        .report-card {
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
                        <a class="nav-link active" href="reports.php">
                            <i class="fas fa-chart-line"></i> Rapports
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
                    <h2>Rapports</h2>
                    <div class="d-flex gap-2">
                        <form method="get" class="d-flex gap-2">
                            <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>" required>
                            <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>" required>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filtrer
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Chiffre d'affaires</h5>
                                <h3><?php echo number_format($total_sales, 0, ',', ' '); ?> FCFA</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Commandes</h5>
                                <h3><?php echo $total_orders; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Panier moyen</h5>
                                <h3><?php echo number_format($average_order, 0, ',', ' '); ?> FCFA</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sales Report -->
                <div class="card report-card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Rapport des ventes</h5>
                        <form method="post">
                            <input type="hidden" name="report_type" value="sales">
                            <button type="submit" name="export" class="btn btn-sm btn-success">
                                <i class="fas fa-download"></i> Exporter en CSV
                            </button>
                        </form>
                    </div>
                    <div class="card-body">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>

                <!-- Products Report -->
                <div class="card report-card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Rapport des produits</h5>
                        <form method="post">
                            <input type="hidden" name="report_type" value="products">
                            <button type="submit" name="export" class="btn btn-sm btn-success">
                                <i class="fas fa-download"></i> Exporter en CSV
                            </button>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Produit</th>
                                        <th>Prix</th>
                                        <th>Commandes</th>
                                        <th>Quantité vendue</th>
                                        <th>Chiffre d'affaires</th>
                                        <th>Prix moyen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($products_report as $product): ?>
                                        <tr>
                                            <td><?php echo $product['name']; ?></td>
                                            <td><?php echo number_format($product['price'], 0, ',', ' '); ?> FCFA</td>
                                            <td><?php echo $product['orders_count']; ?></td>
                                            <td><?php echo $product['total_quantity']; ?></td>
                                            <td><?php echo number_format($product['total_revenue'], 0, ',', ' '); ?> FCFA</td>
                                            <td><?php echo number_format($product['average_price'], 0, ',', ' '); ?> FCFA</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Categories Report -->
                <div class="card report-card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Rapport des catégories</h5>
                        <form method="post">
                            <input type="hidden" name="report_type" value="categories">
                            <button type="submit" name="export" class="btn btn-sm btn-success">
                                <i class="fas fa-download"></i> Exporter en CSV
                            </button>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Catégorie</th>
                                        <th>Commandes</th>
                                        <th>Produits</th>
                                        <th>Quantité vendue</th>
                                        <th>Chiffre d'affaires</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($categories_report as $category): ?>
                                        <tr>
                                            <td><?php echo $category['name']; ?></td>
                                            <td><?php echo $category['orders_count']; ?></td>
                                            <td><?php echo $category['products_count']; ?></td>
                                            <td><?php echo $category['total_quantity']; ?></td>
                                            <td><?php echo number_format($category['total_revenue'], 0, ',', ' '); ?> FCFA</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Customers Report -->
                <div class="card report-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Rapport des clients</h5>
                        <form method="post">
                            <input type="hidden" name="report_type" value="customers">
                            <button type="submit" name="export" class="btn btn-sm btn-success">
                                <i class="fas fa-download"></i> Exporter en CSV
                            </button>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Email</th>
                                        <th>Commandes</th>
                                        <th>Total dépensé</th>
                                        <th>Panier moyen</th>
                                        <th>Première commande</th>
                                        <th>Dernière commande</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($customers_report as $customer): ?>
                                        <tr>
                                            <td><?php echo $customer['username']; ?></td>
                                            <td><?php echo $customer['email']; ?></td>
                                            <td><?php echo $customer['orders_count']; ?></td>
                                            <td><?php echo number_format($customer['total_spent'], 0, ',', ' '); ?> FCFA</td>
                                            <td><?php echo number_format($customer['average_order'], 0, ',', ' '); ?> FCFA</td>
                                            <td><?php echo date('d/m/Y', strtotime($customer['first_order'])); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($customer['last_order'])); ?></td>
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
    <script>
        // Initialisation de Flatpickr pour les dates
        flatpickr("input[type=date]", {
            locale: "fr",
            dateFormat: "Y-m-d",
            maxDate: "today"
        });

        // Graphique des ventes
        var ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_map(function($row) {
                    return date('d/m', strtotime($row['date']));
                }, $sales_report)); ?>,
                datasets: [{
                    label: 'Chiffre d\'affaires (FCFA)',
                    data: <?php echo json_encode(array_map(function($row) {
                        return $row['total_sales'];
                    }, $sales_report)); ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1,
                    yAxisID: 'y'
                }, {
                    label: 'Nombre de commandes',
                    data: <?php echo json_encode(array_map(function($row) {
                        return $row['orders_count'];
                    }, $sales_report)); ?>,
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
