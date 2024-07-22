document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('.annonce-detail .photos img');
    images.forEach(image => {
        image.addEventListener('click', function() {
            const modal = document.createElement('div');
            modal.classList.add('image-modal');
            modal.innerHTML = `
                <div class="modal-content">
                    <span class="close-button">&times;</span>
                    <img src="${this.src}" alt="Image en taille réelle">
                </div>
            `;
            document.body.appendChild(modal);

            const closeButton = modal.querySelector('.close-button');
            closeButton.addEventListener('click', function() {
                document.body.removeChild(modal);
            });

            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    document.body.removeChild(modal);
                }
            });
        });
    });
});

// Pour le classement  des utilisateurs

document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star');
    const ratingValue = document.getElementById('rating_rating');

    stars.forEach(star => {
        star.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            ratingValue.value = value;
            stars.forEach(s => s.classList.remove('selected'));
            for (let i = 0; i < value; i++) {
                stars[i].classList.add('selected');
            }
        });
    });
});

// Gérer le clic sur le bouton d'ajout/retrait des favoris


