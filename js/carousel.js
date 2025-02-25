document.addEventListener('DOMContentLoaded', function() {
    // Initialisation du carousel Bootstrap
    var carousel = new bootstrap.Carousel(document.getElementById('header-carousel'), {
        interval: 5000, // Temps entre chaque slide (5 secondes)
        wrap: true,     // Boucle infinie
        pause: 'hover', // Pause au survol
        keyboard: true  // Navigation avec les touches du clavier
    });

    // Gestion des animations
    var carouselElement = document.getElementById('header-carousel');
    
    carouselElement.addEventListener('slide.bs.carousel', function (e) {
        // Réinitialiser les animations sur l'ancien slide
        var currentSlide = e.from;
        var elements = document.querySelectorAll('.carousel-item')[currentSlide].querySelectorAll('.animated');
        elements.forEach(function(element) {
            element.style.opacity = '0';
            element.classList.remove('slideInDown');
        });
    });

    carouselElement.addEventListener('slid.bs.carousel', function (e) {
        // Déclencher les animations sur le nouveau slide
        var currentSlide = e.to;
        var elements = document.querySelectorAll('.carousel-item')[currentSlide].querySelectorAll('.animated');
        elements.forEach(function(element, index) {
            setTimeout(function() {
                element.style.opacity = '1';
                element.classList.add('slideInDown');
            }, index * 200); // Délai progressif pour chaque élément
        });
    });

    // Déclencher les animations pour le premier slide
    var firstSlideElements = document.querySelector('.carousel-item.active').querySelectorAll('.animated');
    firstSlideElements.forEach(function(element, index) {
        setTimeout(function() {
            element.style.opacity = '1';
            element.classList.add('slideInDown');
        }, index * 200);
    });
});
