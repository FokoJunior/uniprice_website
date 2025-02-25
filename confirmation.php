<?php
session_start();
require_once "config/database.php";

if (!isset($_GET['order'])) {
    header('Location: products.php');
    exit;
}

$order_id = $_GET['order'];

include 'includes/header.php';
?>

    <!-- Confirmation Start -->
    <div class="container-fluid py-5">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 700px;">
                <h1 class="display-4">Commande Confirmée</h1>
                <p>Merci pour votre confiance !</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                            <h3 class="mt-4">Votre commande a été enregistrée avec succès</h3>
                            <p class="mb-4">Numéro de commande : <strong>#<?php echo $order_id; ?></strong></p>
                            
                            <div class="alert alert-info">
                                <p class="mb-0">
                                    <i class="fa fa-info-circle me-2"></i>
                                    Un email de confirmation vous a été envoyé avec les détails de votre commande.
                                </p>
                            </div>
                            
                            <p class="mb-4">
                                Nous traiterons votre commande dans les plus brefs délais.<br>
                                Notre équipe vous contactera pour confirmer la livraison.
                            </p>
                            
                            <div class="mt-4">
                                <a href="products.php" class="btn btn-primary">
                                    <i class="fa fa-shopping-bag me-2"></i>Continuer les achats
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Confirmation End -->

<?php include 'includes/footer.php'; ?>
