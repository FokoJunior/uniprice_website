<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>Uniprice Dwash - Nos Produits</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="produits nettoyage, détergents, désinfectants, produits entretien" name="keywords">
    <meta content="Découvrez notre gamme complète de produits d'entretien professionnels" name="description">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Raleway:wght@600;800&display=swap" rel="stylesheet"> 

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/lightbox/css/lightbox.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    <link href="img/logo1.png" rel="icon">
    <link href="css/custom.css" rel="stylesheet">
</head>

<body>
    <!-- Topbar Start -->
    <div class="container-fluid fixed-top">
        <div class="container topbar bg-primary d-none d-lg-block">
            <div class="d-flex justify-content-between">
                <div class="top-info ps-2">
                    <small class="me-3"><i class="fas fa-map-marker-alt me-2 text-light"></i> <a href="#" class="text-light">Bependa 7e, Douala</a></small>
                    <small class="me-3"><i class="fas fa-phone-alt me-2 text-light"></i> <a href="tel:+237674127256" class="text-light">+237 674 127 256</a></small>
                    <small class="me-3"><i class="fas fa-phone-alt me-2 text-light"></i> <a href="tel:+237693444323" class="text-light">+237 693 444 323</a></small>
                    <small class="me-3"><i class="fas fa-envelope me-2 text-light"></i><a href="mailto:uniprice83@gmail.com" class="text-light">uniprice83@gmail.com</a></small>
                </div>
            </div>
        </div>
        <div class="container px-0">
            <nav class="navbar navbar-light bg-white navbar-expand-xl">
                <a href="index.html" class="navbar-brand"><h1 class="text-primary display-6">Uniprice Dwash</h1></a>
                <button class="navbar-toggler py-2 px-3" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                    <span class="fa fa-bars text-primary"></span>
                </button>
                <div class="collapse navbar-collapse bg-white" id="navbarCollapse">
                    <div class="navbar-nav mx-auto">
                        <a href="index.html" class="nav-item nav-link">Accueil</a>
                        <a href="products.php" class="nav-item nav-link active">Produits</a>
                        <a href="about.html" class="nav-item nav-link">À Propos</a>
                        <a href="services.html" class="nav-item nav-link">Services</a>
                        <a href="contact.html" class="nav-item nav-link">Contact</a>
                    </div>
                </div>
            </nav>
        </div>
    </div>
    <!-- Topbar End -->

    <!-- Banner Start -->
    <div class="banner-carousel mt-5">
        <div class="banner-slide active" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('img/image_produits/banner-1.png');">
            <div class="banner-content">
                <h1 data-aos="fade-up">Solutions Professionnelles</h1>
                <p data-aos="fade-up" data-aos-delay="200">Découvrez notre gamme complète de produits d'entretien</p>
            </div>
        </div>
        <div class="banner-slide" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('img/image_produits/banner-2.png');">
            <div class="banner-content">
                <h1 data-aos="fade-up">Qualité Garantie</h1>
                <p data-aos="fade-up" data-aos-delay="200">Des produits certifiés pour des résultats professionnels</p>
            </div>
        </div>
        <div class="banner-slide" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('img/image_produits/banner-3.png');">
            <div class="banner-content">
                <h1 data-aos="fade-up">Service Expert</h1>
                <p data-aos="fade-up" data-aos-delay="200">Une équipe dédiée à votre satisfaction</p>
            </div>
        </div>
    </div>
    <!-- Banner End -->

    <!-- Products Start -->
    <div class="container-fluid py-5">
        <div class="container">
            <div class="text-center mx-auto mb-5" style="max-width: 700px;">
                <h1 class="display-4">Nos Produits</h1>
                <p>Découvrez notre gamme complète de produits d'entretien professionnels</p>
            </div>
            
            <!-- Filter Buttons -->
            <div class="row g-3 mb-5">
                <div class="col-12 text-center">
                    <button class="btn btn-primary active mx-1 mb-2" data-filter="all">Tous les Produits</button>
                    <button class="btn btn-primary mx-1 mb-2" data-filter="detergents">Détergents</button>
                    <button class="btn btn-primary mx-1 mb-2" data-filter="desinfectants">Désinfectants</button>
                    <button class="btn btn-primary mx-1 mb-2" data-filter="nettoyants">Nettoyants Spécialisés</button>
                    <button class="btn btn-primary mx-1 mb-2" data-filter="accessoires">Accessoires</button>
                </div>
            </div>

            <div class="row g-4">
                <?php
                    $directory = 'img/image_produits/';
                    $files = array_diff(scandir($directory), array('..', '.'));
                    $products = [];

                    foreach ($files as $file) {
                        if (pathinfo($file, PATHINFO_EXTENSION) == 'png') {
                            $products[] = $file;
                        }
                    }

                    // Shuffle the products array for random display
                    shuffle($products);

                    // Display products
                    foreach ($products as $product) {
                        $filename = pathinfo($product, PATHINFO_FILENAME);
                        echo '<div class="col-xl-3 col-lg-4 col-md-6 product-item">';
                        echo '<div class="product-item-content rounded shadow-sm">';
                        echo '<img class="img-fluid rounded-top" src="' . $directory . $product . '" alt="' . $filename . '">';
                        echo '<div class="p-4">';
                        echo '<h5>' . ucfirst(str_replace("-", " ", $filename)) . '</h5>';
                        echo '<p>Description du produit</p>';
                        echo '<div class="d-flex justify-content-between flex-lg-wrap">';
                        echo '<p class="text-dark fs-5 fw-bold mb-0">' . rand(1000, 10000) . ' FCFA</p>';
                        echo '<a href="https://wa.me/+237693444323" class="btn border border-secondary rounded-pill px-3"><i class="fa fa-whatsapp me-2 text-primary"></i> WhatsApp</a>';
                        echo '</div></div></div></div>';
                    }
                ?>
            </div>

            <!-- Pagination -->
            <div class="row mt-5">
                <div class="col-12">
                    <nav aria-label="Navigation des pages">
                        <ul class="pagination justify-content-center">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" aria-label="Précédent">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#" aria-label="Suivant">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- Products End -->

     <!-- Footer Start -->
     <div class="container-fluid bg-dark text-white footer pt-5 mt-5">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-lg-3 col-md-6">
                    <div class="footer-item">
                        <h4 class="text-light mb-3">Pourquoi Nous Choisir</h4>
                        <p class="mb-4">Leader dans l'innovation des solutions d'entretien professionnelles</p>
                        <a href="#" class="btn btn-primary py-2 px-4">En Savoir Plus</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="d-flex flex-column text-start footer-item">
                        <h4 class="text-light mb-3">Liens Rapides</h4>
                        <a class="btn-link" href="#">À Propos</a>
                        <a class="btn-link" href="#">Nos Services</a>
                        <a class="btn-link" href="#">Produits</a>
                        <a class="btn-link" href="#">Contact</a>
                        <div class="social-icons mt-2">
                            <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                            <a href="https://wa.me/+237693444323" class="text-white me-3"><i class="fab fa-whatsapp"></i></a>
                            <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="text-white"><i class="fas fa-times"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="d-flex flex-column text-start footer-item">
                        <h4 class="text-light mb-3">Contact</h4>
                        <p><i class="fa fa-map-marker-alt me-3"></i>Bependa 7e, Douala</p>
                        <p><i class="fa fa-phone-alt me-3"></i>+237 674 127 256</p>
                        <p><i class="fa fa-phone-alt me-3"></i>+237 693 444 323</p>
                        <p><i class="fa fa-envelope me-3"></i>uniprice83@gmail.com</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="footer-item">
                        <h4 class="text-light mb-3">Heures d'ouverture</h4>
                        <p>Lundi - Vendredi: 8h - 17h</p>
                        <p>Samedi: 8h - 13h</p>
                        <p>Dimanche: Fermé</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-center">
                    <p class="mb-0 text-white">&copy; 2025 Uniprice Dwash. Tous droits réservés.</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->

    <!-- Copyright Start -->
    <!-- <div class="container-fluid copyright py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    &copy; <a class="fw-medium" href="#">Uniprice</a>, Tous droits réservés.
                </div>
                <div class="col-md-6 text-center text-md-end">
                    Développé par <a class="fw-medium" href="#">Votre Entreprise</a>
                </div>
            </div>
        </div>
    </div> -->
    <!-- Copyright End -->

    <!-- Back to Top -->
    <!-- <a href="#" class="btn btn-lg btn-primary btn-lg-square rounded-circle back-to-top"><i class="bi bi-arrow-up"></i></a> -->

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/lightbox/js/lightbox.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <!-- Custom JavaScript -->
    <script src="js/banner.js"></script>
    <script src="js/products.js"></script>
    <script src="js/main.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productsContainer = document.querySelector('.row.g-4');
            const products = Array.from(productsContainer.children);
            const buttons = document.querySelectorAll('.btn-primary');
            const productsPerPage = 6; // Number of products per page
            let currentPage = 1;

            function displayProducts(page) {
                const start = (page - 1) * productsPerPage;
                const end = start + productsPerPage;
                products.forEach((product, index) => {
                    product.style.display = (index >= start && index < end) ? 'block' : 'none';
                });
            }

            function setupPagination() {
                const paginationContainer = document.querySelector('.pagination');
                paginationContainer.innerHTML = '';
                const totalPages = Math.ceil(products.length / productsPerPage);

                for (let i = 1; i <= totalPages; i++) {
                    const pageItem = document.createElement('li');
                    pageItem.className = 'page-item';
                    pageItem.innerHTML = `<a class='page-link' href='#'>${i}</a>`;
                    pageItem.addEventListener('click', function() {
                        currentPage = i;
                        displayProducts(currentPage);
                        setupPagination();
                    });
                    paginationContainer.appendChild(pageItem);
                }
            }

            function filterProducts(filter) {
                products.forEach(product => {
                    if (filter === 'all' || product.classList.contains(filter)) {
                        product.style.display = 'block';
                    } else {
                        product.style.display = 'none';
                    }
                });
                setupPagination();
            }

            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    buttons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    const filter = this.getAttribute('data-filter');
                    filterProducts(filter);
                });
            });

            displayProducts(currentPage);
            setupPagination();
        });
    </script>

</body>
</html>
