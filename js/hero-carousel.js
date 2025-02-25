document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.querySelector('.hero-image-carousel');
    if (!carousel) return;

    const items = carousel.querySelectorAll('.hero-image-item');
    let currentIndex = 0;
    const interval = 5000; // Changement toutes les 5 secondes

    function showNextImage() {
        // Masquer l'image actuelle
        items[currentIndex].classList.remove('active');
        
        // Passer à l'image suivante
        currentIndex = (currentIndex + 1) % items.length;
        
        // Afficher la nouvelle image
        items[currentIndex].classList.add('active');
    }

    // Démarrer le carousel
    setInterval(showNextImage, interval);

    // Animation initiale
    items[0].classList.add('active');
});
