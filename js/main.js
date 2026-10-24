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

    // Cookie consent
    initCookieConsent();
});

/**
 * Initialize mobile navigation
 */
function initMobileNav() {
    const header = document.querySelector('.site-header');
    if (!header) return;

    const toggle = header.querySelector('.nav-toggle');
    const nav = header.querySelector('.main-nav');
    if (!toggle || !nav) return;

    const closeMenu = () => {
        header.classList.remove('menu-open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Åbn menu');
    };

    toggle.addEventListener('click', () => {
        const isOpen = header.classList.toggle('menu-open');
        toggle.setAttribute('aria-expanded', String(isOpen));
        toggle.setAttribute('aria-label', isOpen ? 'Luk menu' : 'Åbn menu');
    });

    nav.addEventListener('click', (event) => {
        if (event.target.closest('a')) closeMenu();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeMenu();
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

function initCookieConsent() {
    const banner = document.getElementById('cookie-banner');
    if (!banner) return;

    const storedChoice = localStorage.getItem('bogoehallen-cookie-choice');
    if (storedChoice === 'accept' || storedChoice === 'decline') {
        banner.hidden = true;
        return;
    }

    banner.hidden = false;

    banner.querySelectorAll('[data-cookie-choice]').forEach((button) => {
        button.addEventListener('click', () => {
            const choice = button.dataset.cookieChoice;
            localStorage.setItem('bogoehallen-cookie-choice', choice);
            banner.hidden = true;
        });
    });
}

// Call on page load
setActiveNav();
