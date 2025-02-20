// assets/js/main.js

document.addEventListener('DOMContentLoaded', function() {
    initializeStrategicOperations();
});

// Stratejik operasyonların başlatılması
function initializeStrategicOperations() {
    initializeLazyLoading();
    initializeMenuOperations();
    initializeContactOperations();
    initializePerformanceOptimizations();
}

// Lazy loading implementasyonu
function initializeLazyLoading() {
    const lazyImages = document.querySelectorAll('img[loading="lazy"]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.add('loaded');
                observer.unobserve(img);
            }
        });
    });

    lazyImages.forEach(img => imageObserver.observe(img));
}

// Menü operasyonları
function initializeMenuOperations() {
    const menuImages = document.querySelectorAll('.menu-image img');
    
    menuImages.forEach(img => {
        img.addEventListener('click', () => {
            openImageModal(img.src, img.alt);
        });
    });
}

// İletişim operasyonları
function initializeContactOperations() {
    // Telefon numarası formatlama
    const phoneElements = document.querySelectorAll('.phone-number');
    phoneElements.forEach(el => {
        el.addEventListener('click', (e) => {
            e.preventDefault();
            window.location.href = `tel:${el.dataset.phone}`;
        });
    });

    // Adres kopyalama
    const addressElement = document.querySelector('.address-copy');
    if (addressElement) {
        addressElement.addEventListener('click', () => {
            navigator.clipboard.writeText(addressElement.textContent)
                .then(() => showNotification('Adres kopyalandı'));
        });
    }
}

// Performans optimizasyonları
function initializePerformanceOptimizations() {
    // Scroll optimizasyonu
    let ticking = false;
    window.addEventListener('scroll', () => {
        if (!ticking) {
            window.requestAnimationFrame(() => {
                handleScroll();
                ticking = false;
            });
            ticking = true;
        }
    });
}

// Modal işlemleri
function openImageModal(src, alt) {
    const modal = document.createElement('div');
    modal.className = 'image-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <img src="${src}" alt="${alt}">
        </div>
    `;

    modal.querySelector('.close-modal').addEventListener('click', () => {
        modal.remove();
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.remove();
        }
    });

    document.body.appendChild(modal);
}

// Bildirim sistemi
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.add('fade-out');
        setTimeout(() => notification.remove(), 300);
    }, 2000);
}

// Scroll işleyici
function handleScroll() {
    const scrollPosition = window.scrollY;
    const header = document.querySelector('header');
    
    if (scrollPosition > 100) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
}

// Yardımcı fonksiyonlar
const utils = {
    debounce: (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    formatPhoneNumber: (phone) => {
        return phone.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
    },

    isInViewport: (element) => {
        const rect = element.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }
};