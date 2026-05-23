/**
 * Bogø Hallen - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Mobile navigation toggle
    initMobileNav();

    // Smooth scroll for anchor links
    initSmoothScroll();

    // Form validation
    initFormValidation();
});

/**
 * Initialize mobile navigation
 */
function initMobileNav() {
    const header = document.querySelector('.site-header');
    if (!header) return;

    // Check if mobile menu button needs to be created
    const nav = document.querySelector('.main-nav');
    if (!nav) return;

    // Add mobile-friendly class on small screens
    if (window.innerWidth < 768) {
        document.body.classList.add('mobile-view');
    }

    window.addEventListener('resize', function() {
        if (window.innerWidth < 768) {
            document.body.classList.add('mobile-view');
        } else {
            document.body.classList.remove('mobile-view');
        }
    });
}

/**
 * Initialize smooth scroll for anchor links
 */
function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const inputs = form.querySelectorAll('input[required], textarea[required]');
            let isValid = true;

            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = '#e74c3c';
                } else {
                    input.style.borderColor = '';
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Alle påkrævede felter skal udfyldes.');
            }
        });

        // Remove error styling when user types
        const inputs = form.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.style.borderColor = '';
            });
        });
    });
}

/**
 * Lazy load images (optional enhancement)
 */
function initLazyLoad() {
    if ('IntersectionObserver' in window) {
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.src = entry.target.dataset.src;
                    imageObserver.unobserve(entry.target);
                }
            });
        });
        images.forEach(img => imageObserver.observe(img));
    }
}

/**
 * Add active class to current navigation item
 */
function setActiveNav() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.main-nav a');

    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === currentPath ||
            (currentPath === '/' && link.getAttribute('href') === '/')) {
            link.classList.add('active');
        }
    });
}

// Call on page load
setActiveNav();
