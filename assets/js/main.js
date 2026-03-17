// Veloz Autohaus - Main JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Mobile Menu Toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mainNav = document.getElementById('mainNav');

    if (mobileMenuBtn && mainNav) {
        mobileMenuBtn.addEventListener('click', function() {
            mainNav.classList.toggle('active');
            mainNav.classList.toggle('open');
            this.classList.toggle('active');
        });
    }

    // Mobile Filter Toggle (Cars Page)
    const mobileFilterBtn = document.getElementById('mobileFilterBtn');
    const filtersSidebar = document.querySelector('.filters-sidebar');

    if (mobileFilterBtn && filtersSidebar) {
        mobileFilterBtn.addEventListener('click', function() {
            filtersSidebar.classList.toggle('active');
        });

        // Close filters when clicking outside
        document.addEventListener('click', function(e) {
            if (filtersSidebar.classList.contains('active') &&
                !filtersSidebar.contains(e.target) &&
                !mobileFilterBtn.contains(e.target)) {
                filtersSidebar.classList.remove('active');
            }
        });
    }

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                    observer.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }

    // Header scroll effect
    const header = document.querySelector('.header');
    if (header) {
        let lastScrollTop = 0;
        window.addEventListener('scroll', function() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            if (scrollTop > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }

            lastScrollTop = scrollTop;
        });
    }

    // Form validation
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let valid = true;
            const requiredFields = form.querySelectorAll('[required]');

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.classList.add('error');
                } else {
                    field.classList.remove('error');
                }
            });

            if (!valid) {
                e.preventDefault();
            }
        });
    });

    // Price formatter
    const priceInputs = document.querySelectorAll('input[data-format="price"]');
    priceInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const value = parseFloat(this.value.replace(/,/g, ''));
            if (!isNaN(value)) {
                this.value = value.toLocaleString();
            }
        });
    });

    // Car image gallery (detail page)
    const thumbnails = document.querySelectorAll('.thumbnail-gallery .thumbnail');
    const mainImage = document.getElementById('mainImage');

    if (thumbnails.length && mainImage) {
        thumbnails.forEach(thumb => {
            thumb.addEventListener('click', function() {
                const img = this.querySelector('img');
                if (img) {
                    mainImage.src = img.src;
                    thumbnails.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                }
            });
        });
    }

    // Animate on scroll
    const animateElements = document.querySelectorAll('.animate-on-scroll');
    if (animateElements.length && 'IntersectionObserver' in window) {
        const animateObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                }
            });
        }, { threshold: 0.1 });

        animateElements.forEach(el => {
            animateObserver.observe(el);
        });
    }

    // Gallery Slideshow (prev/next buttons + dots)
    initSlideshow('.gallery-slideshow', '.gallery-track', '.gallery-slide', '.gallery-nav');

    // Posts Slideshow
    initSlideshow('.posts-slideshow', '.posts-track', '.post-card', '.slideshow-nav');
});

// Initialize a slideshow with navigation
function initSlideshow(containerSel, trackSel, slideSel, navSel) {
    const container = document.querySelector(containerSel);
    if (!container) return;

    const track = container.querySelector(trackSel) || document.querySelector(trackSel);
    const nav = container.querySelector(navSel) || document.querySelector(navSel);
    if (!track) return;

    const slides = track.querySelectorAll(slideSel);
    if (slides.length === 0) return;

    let currentIndex = 0;
    let autoPlayTimer;
    const totalSlides = slides.length;

    function goToSlide(index) {
        if (index < 0) index = totalSlides - 1;
        if (index >= totalSlides) index = 0;
        currentIndex = index;

        // For full-width slides
        if (slides[0] && slides[0].style.minWidth === '100%' || containerSel === '.gallery-slideshow') {
            track.style.transform = `translateX(-${currentIndex * 100}%)`;
        } else {
            // For card-based slideshows, scroll by card width + gap
            const cardWidth = slides[0].offsetWidth + 24; // 24px gap
            track.style.transform = `translateX(-${currentIndex * cardWidth}px)`;
        }

        // Update dots
        if (nav) {
            const dots = nav.querySelectorAll('.dot');
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === currentIndex);
            });
        }
    }

    function startAutoPlay() {
        stopAutoPlay();
        autoPlayTimer = setInterval(() => {
            goToSlide(currentIndex + 1);
        }, 4000);
    }

    function stopAutoPlay() {
        if (autoPlayTimer) clearInterval(autoPlayTimer);
    }

    // Bind navigation buttons
    if (nav) {
        const prevBtn = nav.querySelector('button:first-child');
        const nextBtn = nav.querySelector('button:last-child');

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                goToSlide(currentIndex - 1);
                startAutoPlay();
            });
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                goToSlide(currentIndex + 1);
                startAutoPlay();
            });
        }

        // Bind dots
        const dots = nav.querySelectorAll('.dot');
        dots.forEach((dot, i) => {
            dot.addEventListener('click', () => {
                goToSlide(i);
                startAutoPlay();
            });
        });
    }

    // Pause on hover
    container.addEventListener('mouseenter', stopAutoPlay);
    container.addEventListener('mouseleave', startAutoPlay);

    // Touch swipe support
    let touchStartX = 0;
    let touchEndX = 0;

    container.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
        stopAutoPlay();
    }, { passive: true });

    container.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        const diff = touchStartX - touchEndX;
        if (Math.abs(diff) > 50) {
            if (diff > 0) {
                goToSlide(currentIndex + 1);
            } else {
                goToSlide(currentIndex - 1);
            }
        }
        startAutoPlay();
    }, { passive: true });

    // Start auto-play
    startAutoPlay();
}

// Utility function to format currency
function formatCurrency(amount) {
    return 'Rs. ' + amount.toLocaleString('en-US', { maximumFractionDigits: 0 });
}

// Utility function to show notification
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.add('show');
    }, 100);

    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}
