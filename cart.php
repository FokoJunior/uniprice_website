<?php
session_start();
require_once "config/database.php";
require_once "models/Product.php";

// Initialiser le panier s'il n'existe pas
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);

// Gérer les actions du panier
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'add':
            if (isset($_GET['id'])) {
                $product->id = $_GET['id'];
                $product->readOne();
                
                if (!isset($_SESSION['cart'][$_GET['id']])) {
                    $_SESSION['cart'][$_GET['id']] = [
                        'name' => $product->name,
                        'price' => $product->price,
                        'quantity' => 1,
                        'image' => $product->image,
                        'discount' => $product->discount
                    ];
                } else {
                    $_SESSION['cart'][$_GET['id']]['quantity']++;
                }
                
                header('Location: cart.php?status=added');
                exit;
            }
            break;
            
        case 'remove':
            if (isset($_GET['id'])) {
                unset($_SESSION['cart'][$_GET['id']]);
                header('Location: cart.php?status=removed');
                exit;
            }
            break;
            
        case 'update':
            if (isset($_POST['quantities'])) {
                foreach ($_POST['quantities'] as $id => $quantity) {
                    if ($quantity > 0) {
                        $_SESSION['cart'][$id]['quantity'] = $quantity;
                    } else {
                        unset($_SESSION['cart'][$id]);
                    }
                }
                header('Location: cart.php?status=updated');
                exit;
            }
            break;
            
        case 'clear':
            $_SESSION['cart'] = array();
            header('Location: cart.php?status=cleared');
            exit;
            break;
    }
}

include 'includes/header.php';

// Calculer le total
$total = 0;
foreach ($_SESSION['cart'] as $id => $item) {
    $price = $item['discount'] > 0 ? 
        $item['price'] * (1 - $item['discount']/100) : 
        $item['price'];
    $total += $price * $item['quantity'];
}
?>

    <!-- Cart Start -->
    <div class="container-fluid py-5">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 700px;">
                <h1 class="display-4">Mon Panier</h1>
                <p>Gérez vos articles et passez votre commande</p>
            </div>

            <?php if (isset($_GET['status'])): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <?php
                    switch ($_GET['status']) {
                        case 'added':
                            echo "Produit ajouté au panier avec succès.";
                            break;
                        case 'removed':
                            echo "Produit retiré du panier.";
                            break;
                        case 'updated':
                            echo "Panier mis à jour.";
                            break;
                        case 'cleared':
                            echo "Panier vidé.";
                            break;
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (empty($_SESSION['cart'])): ?>
                <div class="text-center">
                    <p class="mb-4">Votre panier est vide.</p>
                    <a href="products.php" class="btn btn-primary">Voir nos produits</a>
                </div>
            <?php else: ?>
                <form action="cart.php?action=update" method="post">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Prix unitaire</th>
                                    <th>Quantité</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="img/products/<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" style="width: 50px; height: 50px; object-fit: cover;" class="me-3">
                                                <span><?php echo $item['name']; ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($item['discount'] > 0): ?>
                                                <span class="text-decoration-line-through text-muted"><?php echo number_format($item['price'], 0, ',', ' '); ?> FCFA</span><br>
                                                <span class="text-danger"><?php echo number_format($item['price'] * (1 - $item['discount']/100), 0, ',', ' '); ?> FCFA</span>
                                            <?php else: ?>
                                                <?php echo number_format($item['price'], 0, ',', ' '); ?> FCFA
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <input type="number" name="quantities[<?php echo $id; ?>]" value="<?php echo $item['quantity']; ?>" min="0" class="form-control" style="width: 80px;">
                                        </td>
                                        <td>
                                            <?php
                                            $price = $item['discount'] > 0 ? 
                                                $item['price'] * (1 - $item['discount']/100) : 
                                                $item['price'];
                                            echo number_format($price * $item['quantity'], 0, ',', ' '); ?> FCFA
                                        </td>
                                        <td>
                                            <a href="cart.php?action=remove&id=<?php echo $id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir retirer ce produit ?');">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total :</strong></td>
                                    <td><strong><?php echo number_format($total, 0, ',', ' '); ?> FCFA</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <div>
                            <a href="cart.php?action=clear" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir vider votre panier ?');">
                                <i class="fa fa-trash me-2"></i>Vider le panier
                            </a>
                            <a href="products.php" class="btn btn-secondary">
                                <i class="fa fa-shopping-bag me-2"></i>Continuer les achats
                            </a>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-info">
                                <i class="fa fa-sync me-2"></i>Mettre à jour
                            </button>
                            <a href="checkout.php" class="btn btn-primary">
                                <i class="fa fa-shopping-cart me-2"></i>Commander
                            </a>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <!-- Cart End -->

<?php include 'includes/footer.php'; ?>
