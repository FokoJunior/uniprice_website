document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('.row.g-4.mb-5');
    const productsPerPage = 9; // 3x3 grid
    let currentPage = 1;
    
    function showPage(page) {
        // Masquer toutes les lignes
        rows.forEach(row => {
            row.style.display = 'none';
        });
        
        // Afficher les lignes de la page actuelle
        const startRow = (page - 1) * 3;
        const endRow = startRow + 3;
        
        for (let i = startRow; i < endRow && i < rows.length; i++) {
            rows[i].style.display = 'flex';
            
            // Animation de fade-in
            rows[i].style.opacity = '0';
            setTimeout(() => {
                rows[i].style.transition = 'opacity 0.3s ease-in-out';
                rows[i].style.opacity = '1';
            }, 50 * (i - startRow)); // Délai progressif pour chaque ligne
        }
        
        updatePaginationState(page);
    }
    
    function updatePaginationState(page) {
        const totalPages = Math.ceil(rows.length / 3);
        const pageItems = document.querySelectorAll('.pagination .page-item');
        const prevButton = pageItems[0];
        const nextButton = pageItems[pageItems.length - 1];
        
        // Mettre à jour l'état des boutons précédent/suivant
        prevButton.classList.toggle('disabled', page === 1);
        nextButton.classList.toggle('disabled', page === totalPages);
        
        // Mettre à jour l'état actif des numéros de page
        pageItems.forEach((item, index) => {
            if (index > 0 && index < pageItems.length - 1) {
                item.classList.toggle('active', index === page);
            }
        });
    }
    
    // Gestionnaire d'événements pour les boutons de pagination
    document.querySelectorAll('.pagination .page-link').forEach((button, index) => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            
            const pageItems = document.querySelectorAll('.pagination .page-item');
            const totalPages = Math.ceil(rows.length / 3);
            
            if (index === 0 && currentPage > 1) {
                // Bouton précédent
                currentPage--;
                showPage(currentPage);
            } else if (index === pageItems.length - 1 && currentPage < totalPages) {
                // Bouton suivant
                currentPage++;
                showPage(currentPage);
            } else if (index > 0 && index < pageItems.length - 1) {
                // Numéros de page
                currentPage = index;
                showPage(currentPage);
            }
            
            // Scroll en haut de la section des produits
            document.querySelector('.products-grid').scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });
    });
    
    // Afficher la première page au chargement
    showPage(1);
});