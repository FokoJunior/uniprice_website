<?php
session_start();
require_once "config/database.php";
require_once "models/Product.php";

// Vérifier si le panier est vide
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Calculer le total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $price = $item['discount'] > 0 ? 
        $item['price'] * (1 - $item['discount']/100) : 
        $item['price'];
    $total += $price * $item['quantity'];
}

// Traiter la commande
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des données
    $errors = [];
    
    if (empty($_POST['name'])) $errors[] = "Le nom est requis";
    if (empty($_POST['email'])) $errors[] = "L'email est requis";
    if (empty($_POST['phone'])) $errors[] = "Le téléphone est requis";
    if (empty($_POST['address'])) $errors[] = "L'adresse est requise";
    
    if (empty($errors)) {
        // Enregistrer la commande dans la base de données
        $query = "INSERT INTO orders (customer_name, email, phone, address, total_amount, status) 
                  VALUES (:name, :email, :phone, :address, :total, 'pending')";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':name', $_POST['name']);
        $stmt->bindParam(':email', $_POST['email']);
        $stmt->bindParam(':phone', $_POST['phone']);
        $stmt->bindParam(':address', $_POST['address']);
        $stmt->bindParam(':total', $total);
        
        if ($stmt->execute()) {
            $order_id = $db->lastInsertId();
            
            // Enregistrer les produits de la commande
            foreach ($_SESSION['cart'] as $id => $item) {
                $price = $item['discount'] > 0 ? 
                    $item['price'] * (1 - $item['discount']/100) : 
                    $item['price'];
                    
                $query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                         VALUES (:order_id, :product_id, :quantity, :price)";
                         
                $stmt = $db->prepare($query);
                $stmt->bindParam(':order_id', $order_id);
                $stmt->bindParam(':product_id', $id);
                $stmt->bindParam(':quantity', $item['quantity']);
                $stmt->bindParam(':price', $price);
                $stmt->execute();
            }
            
            // Vider le panier
            $_SESSION['cart'] = array();
            
            // Envoyer un email de confirmation
            $to = $_POST['email'];
            $subject = "Confirmation de commande - Uniprice";
            $message = "Merci pour votre commande n°" . $order_id . "\n\n";
            $message .= "Nous avons bien reçu votre commande et nous la traiterons dans les plus brefs délais.\n";
            $message .= "Total : " . number_format($total, 0, ',', ' ') . " FCFA\n\n";
            $message .= "Cordialement,\nL'équipe Uniprice";
            $headers = "From: unipricesarl83@gmail.com";
            
            mail($to, $subject, $message, $headers);
            
            // Rediriger vers la page de confirmation
            header('Location: confirmation.php?order=' . $order_id);
            exit;
        }
    }
}

include 'includes/header.php';
?>

    <!-- Checkout Start -->
    <div class="container-fluid py-5">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 700px;">
                <h1 class="display-4">Commander</h1>
                <p>Finalisez votre commande en remplissant les informations ci-dessous</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Informations de livraison</h5>
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nom complet</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Adresse de livraison</label>
                                    <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes (optionnel)</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-check me-2"></i>Confirmer la commande
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Résumé de la commande</h5>
                            
                            <?php foreach ($_SESSION['cart'] as $item): ?>
                                <div class="d-flex justify-content-between mb-3">
                                    <span>
                                        <?php echo $item['name']; ?> 
                                        <small class="text-muted">x<?php echo $item['quantity']; ?></small>
                                    </span>
                                    <span>
                                        <?php
                                        $price = $item['discount'] > 0 ? 
                                            $item['price'] * (1 - $item['discount']/100) : 
                                            $item['price'];
                                        echo number_format($price * $item['quantity'], 0, ',', ' '); ?> FCFA
                                    </span>
                                </div>
                            <?php endforeach; ?>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total</strong>
                                <strong><?php echo number_format($total, 0, ',', ' '); ?> FCFA</strong>
                            </div>
                            
                            <div class="alert alert-info mb-0">
                                <i class="fa fa-info-circle me-2"></i>
                                Le paiement se fera à la livraison
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Checkout End -->

<?php include 'includes/footer.php'; ?>
