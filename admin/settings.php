<?php
session_start();
require_once "../config/database.php";

if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Charger les paramètres actuels
$query = "SELECT * FROM settings";
$stmt = $db->prepare($query);
$stmt->execute();
$settings = [];
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Traiter la mise à jour des paramètres
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();
        
        // Site Settings
        $site_settings = [
            'site_name' => $_POST['site_name'],
            'site_description' => $_POST['site_description'],
            'contact_email' => $_POST['contact_email'],
            'contact_phone' => $_POST['contact_phone'],
            'address' => $_POST['address']
        ];

        // Social Media Settings
        $social_settings = [
            'facebook_url' => $_POST['facebook_url'],
            'twitter_url' => $_POST['twitter_url'],
            'instagram_url' => $_POST['instagram_url'],
            'whatsapp_number' => $_POST['whatsapp_number']
        ];

        // Email Settings
        $email_settings = [
            'smtp_host' => $_POST['smtp_host'],
            'smtp_port' => $_POST['smtp_port'],
            'smtp_username' => $_POST['smtp_username'],
            'smtp_password' => $_POST['smtp_password'],
            'sender_email' => $_POST['sender_email'],
            'sender_name' => $_POST['sender_name']
        ];

        // Mettre à jour tous les paramètres
        $all_settings = array_merge($site_settings, $social_settings, $email_settings);
        foreach($all_settings as $key => $value) {
            $query = "INSERT INTO settings (setting_key, setting_value) 
                     VALUES (:key, :value) 
                     ON DUPLICATE KEY UPDATE setting_value = :value";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':key', $key);
            $stmt->bindParam(':value', $value);
            $stmt->execute();
        }

        $db->commit();
        $success_message = "Paramètres mis à jour avec succès.";
        
        // Recharger les paramètres
        $query = "SELECT * FROM settings";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $settings = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    } catch(Exception $e) {
        $db->rollBack();
        $error_message = "Erreur lors de la mise à jour des paramètres : " . $e->getMessage();
    }
}

function get_setting($key, $default = '') {
    global $settings;
    return isset($settings[$key]) ? $settings[$key] : $default;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - Admin Uniprice</title>
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
                        <a class="nav-link active" href="settings.php">
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
                <h2 class="mb-4">Paramètres du Site</h2>

                <?php if(isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <?php if(isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <form method="post" class="needs-validation" novalidate>
                    <!-- Site Settings -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Informations Générales</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="site_name" class="form-label">Nom du site</label>
                                    <input type="text" class="form-control" id="site_name" name="site_name" 
                                           value="<?php echo get_setting('site_name', 'Uniprice'); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="site_description" class="form-label">Description</label>
                                    <input type="text" class="form-control" id="site_description" name="site_description"
                                           value="<?php echo get_setting('site_description'); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="contact_email" class="form-label">Email de contact</label>
                                    <input type="email" class="form-control" id="contact_email" name="contact_email"
                                           value="<?php echo get_setting('contact_email'); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="contact_phone" class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" id="contact_phone" name="contact_phone"
                                           value="<?php echo get_setting('contact_phone'); ?>" required>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="address" class="form-label">Adresse</label>
                                    <textarea class="form-control" id="address" name="address" rows="2"><?php echo get_setting('address'); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media Settings -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Réseaux Sociaux</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="facebook_url" class="form-label">Facebook</label>
                                    <input type="url" class="form-control" id="facebook_url" name="facebook_url"
                                           value="<?php echo get_setting('facebook_url'); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="twitter_url" class="form-label">Twitter</label>
                                    <input type="url" class="form-control" id="twitter_url" name="twitter_url"
                                           value="<?php echo get_setting('twitter_url'); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="instagram_url" class="form-label">Instagram</label>
                                    <input type="url" class="form-control" id="instagram_url" name="instagram_url"
                                           value="<?php echo get_setting('instagram_url'); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="whatsapp_number" class="form-label">WhatsApp</label>
                                    <input type="tel" class="form-control" id="whatsapp_number" name="whatsapp_number"
                                           value="<?php echo get_setting('whatsapp_number'); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Email Settings -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Configuration Email</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="smtp_host" class="form-label">Serveur SMTP</label>
                                    <input type="text" class="form-control" id="smtp_host" name="smtp_host"
                                           value="<?php echo get_setting('smtp_host'); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="smtp_port" class="form-label">Port SMTP</label>
                                    <input type="number" class="form-control" id="smtp_port" name="smtp_port"
                                           value="<?php echo get_setting('smtp_port', '587'); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="smtp_username" class="form-label">Nom d'utilisateur SMTP</label>
                                    <input type="text" class="form-control" id="smtp_username" name="smtp_username"
                                           value="<?php echo get_setting('smtp_username'); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="smtp_password" class="form-label">Mot de passe SMTP</label>
                                    <input type="password" class="form-control" id="smtp_password" name="smtp_password"
                                           value="<?php echo get_setting('smtp_password'); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="sender_email" class="form-label">Email d'envoi</label>
                                    <input type="email" class="form-control" id="sender_email" name="sender_email"
                                           value="<?php echo get_setting('sender_email'); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="sender_name" class="form-label">Nom d'envoi</label>
                                    <input type="text" class="form-control" id="sender_name" name="sender_name"
                                           value="<?php echo get_setting('sender_name', 'Uniprice'); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
    <script>
        // Validation des formulaires
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html>
