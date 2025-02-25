<?php
session_start();
require_once "config/database.php";
require_once "models/Product.php";

// Connexion à la base de données
$database = new Database();
$db = $database->getConnection();

// Initialisation de l'objet produit
$product = new Product($db);

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 9;
$from_record_num = ($records_per_page * $page) - $records_per_page;

// Recherche
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Obtenir les produits
$stmt = $product->read();
$num = $stmt->rowCount();

// Inclure le header
include 'includes/header.php';
?>

    <!-- Products Start -->
    <div class="container-fluid py-5">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 700px;">
                <h1 class="display-4">Nos Produits</h1>
                <p>Découvrez notre gamme complète de produits d'entretien professionnels</p>
            </div>

            <!-- Search and Filter -->
            <div class="row mb-5">
                <div class="col-md-6">
                    <form action="" method="GET" class="d-flex">
                        <input type="text" name="search" class="form-control me-2" placeholder="Rechercher un produit..." value="<?php echo htmlspecialchars($search_term); ?>">
                        <button type="submit" class="btn btn-primary">Rechercher</button>
                    </form>
                </div>
                <div class="col-md-6">
                    <select name="category" class="form-select" onchange="this.form.submit()">
                        <option value="0">Toutes les catégories</option>
                        <option value="1" <?php echo $category_id === 1 ? 'selected' : ''; ?>>Détergents</option>
                        <option value="2" <?php echo $category_id === 2 ? 'selected' : ''; ?>>Désinfectants</option>
                        <option value="3" <?php echo $category_id === 3 ? 'selected' : ''; ?>>Nettoyants Spécialisés</option>
                        <option value="4" <?php echo $category_id === 4 ? 'selected' : ''; ?>>Accessoires</option>
                    </select>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="products-grid">
                <?php
                if($num > 0) {
                    echo "<div class='row g-4'>";
                    $count = 0;
                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        if($count % 3 == 0) {
                            if($count > 0) echo "</div>";
                            echo "<div class='row g-4 mb-5'>";
                        }
                        extract($row);
                        ?>
                        <div class="col-md-4">
                            <div class="product-item-content rounded shadow-sm h-100">
                                <div class="position-relative">
                                    <img class="img-fluid w-100 rounded-top" src="img/products/<?php echo $image; ?>" alt="<?php echo $name; ?>">
                                    <?php if($discount > 0): ?>
                                    <div class="position-absolute top-0 end-0 p-2">
                                        <span class="badge bg-danger">-<?php echo $discount; ?>%</span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="p-4">
                                    <h5 class="mb-3"><?php echo $name; ?></h5>
                                    <p class="mb-4"><?php echo $description; ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <?php if($discount > 0): ?>
                                            <p class="text-decoration-line-through text-muted mb-0"><?php echo number_format($price, 0, ',', ' '); ?> FCFA</p>
                                            <p class="text-danger fs-5 fw-bold mb-0">
                                                <?php echo number_format($price * (1 - $discount/100), 0, ',', ' '); ?> FCFA
                                            </p>
                                            <?php else: ?>
                                            <p class="text-dark fs-5 fw-bold mb-0"><?php echo number_format($price, 0, ',', ' '); ?> FCFA</p>
                                            <?php endif; ?>
                                        </div>
                                        <a href="cart.php?action=add&id=<?php echo $id; ?>" class="btn btn-primary rounded-pill px-3">
                                            <i class="fa fa-shopping-bag me-2"></i> Ajouter
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        $count++;
                    }
                    if($count > 0) echo "</div>";
                } else {
                    echo "<div class='alert alert-info'>Aucun produit trouvé.</div>";
                }
                ?>
            </div>

            <!-- Pagination -->
            <?php
            $total_rows = $num;
            $total_pages = ceil($total_rows / $records_per_page);
            
            if($total_pages > 1) {
                echo '<div class="row mt-5">
                    <div class="col-12">
                        <nav aria-label="Navigation des pages">
                            <ul class="pagination justify-content-center">';
                
                // Bouton précédent
                $prev_page = $page - 1;
                if($prev_page > 0) {
                    echo "<li class='page-item'>
                            <a class='page-link' href='?page={$prev_page}" . ($search_term ? "&search={$search_term}" : "") . ($category_id ? "&category={$category_id}" : "") . "' aria-label='Précédent'>
                                <span aria-hidden='true'>&laquo;</span>
                            </a>
                          </li>";
                } else {
                    echo "<li class='page-item disabled'>
                            <a class='page-link' href='#' aria-label='Précédent'>
                                <span aria-hidden='true'>&laquo;</span>
                            </a>
                          </li>";
                }
                
                // Numéros de page
                for($i = 1; $i <= $total_pages; $i++) {
                    if($i == $page) {
                        echo "<li class='page-item active'><a class='page-link' href='#'>{$i}</a></li>";
                    } else {
                        echo "<li class='page-item'><a class='page-link' href='?page={$i}" . ($search_term ? "&search={$search_term}" : "") . ($category_id ? "&category={$category_id}" : "") . "'>{$i}</a></li>";
                    }
                }
                
                // Bouton suivant
                $next_page = $page + 1;
                if($next_page <= $total_pages) {
                    echo "<li class='page-item'>
                            <a class='page-link' href='?page={$next_page}" . ($search_term ? "&search={$search_term}" : "") . ($category_id ? "&category={$category_id}" : "") . "' aria-label='Suivant'>
                                <span aria-hidden='true'>&raquo;</span>
                            </a>
                          </li>";
                } else {
                    echo "<li class='page-item disabled'>
                            <a class='page-link' href='#' aria-label='Suivant'>
                                <span aria-hidden='true'>&raquo;</span>
                            </a>
                          </li>";
                }
                
                echo '</ul></nav></div></div>';
            }
            ?>
        </div>
    </div>
    <!-- Products End -->

<?php
// Inclure le footer
include 'includes/footer.php';
?>
