<?php
session_start();
require_once "../config/database.php";
require_once "../models/Product.php";

// Vérifier si l'utilisateur est connecté
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);

$message = '';

if($_POST) {
    // Upload de l'image
    if(isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);

        if(in_array(strtolower($filetype), $allowed)) {
            $newname = uniqid() . '.' . $filetype;
            $upload_path = "../img/products/" . $newname;

            if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $product->image = $newname;
            }
        }
    }

    // Set des valeurs du produit
    $product->name = $_POST['name'];
    $product->description = $_POST['description'];
    $product->price = $_POST['price'];
    $product->discount = $_POST['discount'] ?? 0;
    $product->category_id = $_POST['category_id'];

    if($product->create()) {
        $message = "Produit créé avec succès.";
    } else {
        $message = "Impossible de créer le produit.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Produit - Admin Uniprice</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid py-5">
        <div class="container">
            <h1 class="mb-4">Ajouter un Produit</h1>
            
            <?php if($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="name" class="form-label">Nom du produit</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Prix (FCFA)</label>
                    <input type="number" class="form-control" id="price" name="price" required>
                </div>

                <div class="mb-3">
                    <label for="discount" class="form-label">Réduction (%)</label>
                    <input type="number" class="form-control" id="discount" name="discount" min="0" max="100">
                </div>

                <div class="mb-3">
                    <label for="category_id" class="form-label">Catégorie</label>
                    <select class="form-control" id="category_id" name="category_id" required>
                        <option value="1">Détergents</option>
                        <option value="2">Désinfectants</option>
                        <option value="3">Nettoyants Spécialisés</option>
                        <option value="4">Accessoires</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Image du produit</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                </div>

                <button type="submit" class="btn btn-primary">Ajouter le produit</button>
                <a href="products.php" class="btn btn-secondary">Retour à la liste</a>
            </form>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
