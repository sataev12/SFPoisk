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

// Page d'acceuil, animation
document.addEventListener('DOMContentLoaded', function() {
    const annonces = document.querySelectorAll('.annonce');
    const options = {
        root: null,
        rootMargin: '0px',
        threshold: 0.1
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, options);

    annonces.forEach(annonce => {
        observer.observe(annonce);
    });
});

// Carrousel 
document.addEventListener('DOMContentLoaded', function() {
    // Initialisation du carrousel
    let currentIndex = 0;

    function moveCarousel(direction) {
        const items = document.querySelectorAll('.carousel-item');
        items[currentIndex].style.display = 'none'; // Masquer l'élément actuel
        items[currentIndex].classList.remove('active'); // Retirer la classe active

        currentIndex = (currentIndex + direction + items.length) % items.length; // Calculer le nouvel index
        items[currentIndex].style.display = 'block'; // Afficher le nouvel élément
        items[currentIndex].classList.add('active'); // Ajouter la classe active
    }

    // Afficher l'élément initial
    document.querySelectorAll('.carousel-item').forEach((item, index) => {
        item.style.display = index === currentIndex ? 'block' : 'none';
    });

    // Gestion des clics sur les boutons de navigation
    document.querySelector('.carousel-button.prev').addEventListener('click', function() {
        moveCarousel(-1);
    });

    document.querySelector('.carousel-button.next').addEventListener('click', function() {
        moveCarousel(1);
    });

    // Défilement automatique
    setInterval(() => {
        moveCarousel(1);
    }, 3000); // Changer d'élément toutes les 3 secondes
});

// Les derniers commentaires pour la page d'acceuil

document.addEventListener('DOMContentLoaded', function() {
    const commentsSection = document.querySelector('.comments-section');
    commentsSection.style.opacity = 0;
    commentsSection.style.transform = 'translateY(20px)';

    setTimeout(() => {
        commentsSection.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        commentsSection.style.opacity = 1;
        commentsSection.style.transform = 'translateY(0)';
    }, 100); // Délai avant l'animation
});

// Les annonce dans une page d'acceuil

document.addEventListener('DOMContentLoaded', function() {
    const prices = document.querySelectorAll('.annonce-price');

    prices.forEach(price => {
        price.addEventListener('mouseover', function() {
            this.style.transition = 'transform 0.3s';
            this.style.transform = 'scale(1.1)';
        });

        price.addEventListener('mouseout', function() {
            this.style.transform = 'scale(1)';
        });
    });
});

// Gérer le modal pour afficher la description de l'annonce

function openDescriptionModal(description) {
    const modal = document.createElement('div');
    modal.classList.add('modal');
    modal.innerHTML = `
        <div class="modal-content">
            <span class="close-button" onclick="this.parentElement.parentElement.remove()">&times;</span>
            <p>${description}</p>
        </div>
    `;
    document.body.appendChild(modal);
}


// Gérer le clic sur le bouton d'ajout/retrait des favoris



document.addEventListener('DOMContentLoaded', function() {
    if (!document.cookie.includes('user_cookie_consent=true')) {
        document.getElementById('cookie-consent-banner').style.display = 'block';
    }

    document.getElementById('accept-cookies').addEventListener('click', function() {
        document.cookie = "user_cookie_consent=true; path=/; max-age=" + (365 * 24 * 60 * 60);
        document.getElementById('cookie-consent-banner').style.display = 'none';
    });

    document.getElementById('decline-cookies').addEventListener('click', function() {
        document.cookie = "user_cookie_consent=; path=/; max-age=0";
        document.getElementById('cookie-consent-banner').style.display = 'none';
    });
});

