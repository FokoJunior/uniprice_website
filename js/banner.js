// Gestion de la bannière dynamique
function initBanner() {
    const slides = document.querySelectorAll('.banner-slide');
    let currentSlide = 0;
    const intervalTime = 5000; // Temps entre chaque transition (5 secondes)
    let slideInterval;

    // Fonction pour afficher un slide spécifique
    function showSlide(index) {
        // Masquer tous les slides
        slides.forEach(slide => {
            slide.classList.remove('active');
            // Réinitialiser les animations
            const content = slide.querySelector('.banner-content');
            if (content) {
                content.querySelectorAll('[data-aos]').forEach(element => {
                    element.classList.remove('aos-animate');
                });
            }
        });

        // Afficher le slide actif
        slides[index].classList.add('active');
        
        // Déclencher les animations
        const activeContent = slides[index].querySelector('.banner-content');
        if (activeContent) {
            setTimeout(() => {
                activeContent.querySelectorAll('[data-aos]').forEach(element => {
                    element.classList.add('aos-animate');
                });
            }, 100);
        }
    }

    // Fonction pour passer au slide suivant
    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }

    // Fonction pour passer au slide précédent
    function prevSlide() {
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(currentSlide);
    }

    // Démarrer le défilement automatique
    function startSlideShow() {
        if (slideInterval) {
            clearInterval(slideInterval);
        }
        slideInterval = setInterval(nextSlide, intervalTime);
    }

    // Arrêter le défilement automatique
    function stopSlideShow() {
        if (slideInterval) {
            clearInterval(slideInterval);
        }
    }

    // Ajouter les contrôles de navigation
    const banner = document.querySelector('.banner-carousel');
    if (banner) {
        // Créer les boutons de navigation
        const prevButton = document.createElement('button');
        prevButton.className = 'banner-nav prev';
        prevButton.innerHTML = '<i class="fas fa-chevron-left"></i>';
        
        const nextButton = document.createElement('button');
        nextButton.className = 'banner-nav next';
        nextButton.innerHTML = '<i class="fas fa-chevron-right"></i>';

        // Ajouter les boutons au banner
        banner.appendChild(prevButton);
        banner.appendChild(nextButton);

        // Ajouter les événements aux boutons
        prevButton.addEventListener('click', () => {
            prevSlide();
            stopSlideShow();
            startSlideShow();
        });

        nextButton.addEventListener('click', () => {
            nextSlide();
            stopSlideShow();
            startSlideShow();
        });

        // Pause au survol
        banner.addEventListener('mouseenter', stopSlideShow);
        banner.addEventListener('mouseleave', startSlideShow);
    }

    // Initialiser le diaporama
    showSlide(currentSlide);
    startSlideShow();

    // Initialiser AOS
    AOS.init({
        duration: 1000,
        once: false,
        mirror: true
    });
}

// Initialiser la bannière au chargement de la page
document.addEventListener('DOMContentLoaded', initBanner);
