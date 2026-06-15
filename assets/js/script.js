/* ============================================
   TIVI CULTURE ARCHIVE - JAVASCRIPT
   Interactive Functionality
   ============================================ */

document.addEventListener('DOMContentLoaded', function() {
    initializeMenuToggle();
    initializeScrollBehavior();
    initializeResponsiveDesign();
    initializeCardTap();
    updateActiveNavLink();
    initializeDailyWordRotation();
    initializeKeyboardNavigation();
    initializeAlertDismiss();
    initializeAjaxSearch();
    initializeFormValidation();
    initializeDailyWordFetch();
});

/**
 * Mobile tap support for explore cards (hover overlay on first tap, navigate on second)
 */
function initializeCardTap() {
    var isTouchDevice = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
    if (!isTouchDevice) return;

    document.addEventListener('touchstart', function(e) {
        var card = e.target.closest('.explore-card');
        if (!card) {
            // Close any open tapped card when touching elsewhere
            document.querySelectorAll('.explore-card.tapped').forEach(function(c) {
                c.classList.remove('tapped');
            });
            return;
        }
        if (!card.classList.contains('tapped')) {
            // First tap: show hover overlay, prevent navigation
            e.preventDefault();
            document.querySelectorAll('.explore-card.tapped').forEach(function(c) {
                c.classList.remove('tapped');
            });
            card.classList.add('tapped');
        }
        // Second tap: let the default <a> navigation happen
    }, { passive: false });
}

/**
 * Initialize Mobile Menu Toggle
 */
function initializeMenuToggle() {
    // New mobile/tablet drawer in nav.php handles the toggle — skip old logic
    if (document.getElementById('mnavDrawer')) return;

    const menuToggle = document.getElementById('menuToggle');
    const navMenu = document.getElementById('navMenu');
    const navLinks = document.querySelectorAll('.nav-link');

    if (!menuToggle || !navMenu) return;

    menuToggle.addEventListener('click', function() {
        menuToggle.classList.toggle('active');
        navMenu.classList.toggle('active');
        menuToggle.setAttribute('aria-expanded', navMenu.classList.contains('active'));
    });

    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            menuToggle.classList.remove('active');
            navMenu.classList.remove('active');
        });
    });

    document.addEventListener('click', function(event) {
        const isClickInsideMenu = navMenu.contains(event.target);
        const isClickOnToggle = menuToggle.contains(event.target);
        if (!isClickInsideMenu && !isClickOnToggle && navMenu.classList.contains('active')) {
            menuToggle.classList.remove('active');
            navMenu.classList.remove('active');
        }
    });
}

/**
 * Initialize Smooth Scroll Behavior
 */
function initializeScrollBehavior() {
    const links = document.querySelectorAll('a[href^="#"]');

    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // Skip if href is just "#"
            if (href === '#') return;

            const target = document.querySelector(href);
            
            if (target) {
                e.preventDefault();
                
                // Calculate offset for sticky header
                const headerHeight = document.querySelector('.header').offsetHeight;
                const targetPosition = target.offsetTop - headerHeight;

                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
}

/**
 * Initialize Responsive Design Adjustments
 */
function initializeResponsiveDesign() {
    const navMenu = document.getElementById('navMenu');
    const menuToggle = document.getElementById('menuToggle');

    // Only run if elements exist (not on admin pages)
    if (!navMenu || !menuToggle) {
        return;
    }

    // Close menu on resize to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1025) {
            navMenu.classList.remove('active');
            menuToggle.classList.remove('active');
        }
    });
}

/**
 * Add Active State to Navigation Links
 */
function updateActiveNavLink() {
    const sections = document.querySelectorAll('section');
    const navLinks = document.querySelectorAll('.nav-link');

    window.addEventListener('scroll', function() {
        let current = '';

        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            
            if (scrollY >= sectionTop - 200) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href').slice(1) === current) {
                link.classList.add('active');
            }
        });
    });
}

/**
 * Daily Word Rotation (Optional Enhancement)
 */
function initializeDailyWordRotation() {
    const dailyWords = [
        { tiv: 'Aondo', eng: 'God' },
        { tiv: 'Terna', eng: 'Father has given' },
        { tiv: 'Mngutyo', eng: 'Blessing' },
        { tiv: 'Ikyôm', eng: 'Tree' },
        { tiv: 'Kwase', eng: 'Woman' }
    ];

    const wordCard = document.querySelector('.word-card');
    if (!wordCard) return;

    let currentIndex = 0;

    // Update word every 10 seconds
    setInterval(function() {
        currentIndex = (currentIndex + 1) % dailyWords.length;
        const word = dailyWords[currentIndex];

        // Fade out effect
        wordCard.style.opacity = '0.5';

        setTimeout(function() {
            const tivValue = wordCard.querySelector('.word-value:first-of-type');
            const engValue = wordCard.querySelector('.word-value:last-of-type');

            if (tivValue && engValue) {
                tivValue.textContent = word.tiv;
                engValue.textContent = word.eng;
            }

            // Fade in effect
            wordCard.style.opacity = '1';
        }, 300);
    }, 10000);

    // Add transition for smooth fade
    wordCard.style.transition = 'opacity 0.3s ease';
}

/**
 * Lazy Loading for Images (Future Enhancement)
 */
function initializeLazyLoading() {
    if ('IntersectionObserver' in window) {
        const images = document.querySelectorAll('img[data-src]');

        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));
    }
}

/**
 * Accessibility: Keyboard Navigation
 */
function initializeKeyboardNavigation() {
    document.addEventListener('keydown', function(e) {
        // Skip if modifier keys are pressed
        if (e.ctrlKey || e.metaKey || e.altKey) return;

        // Close menu with Escape key
        if (e.key === 'Escape') {
            const menuToggle = document.getElementById('menuToggle');
            const navMenu = document.getElementById('navMenu');

            if (menuToggle && navMenu.classList.contains('active')) {
                menuToggle.classList.remove('active');
                navMenu.classList.remove('active');
            }
        }
    });
}


/**
 * Initialize Alert Dismiss Functionality
 */
function initializeAlertDismiss() {
    const alertCloseButtons = document.querySelectorAll('.alert-close');

    alertCloseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const alert = this.closest('.alert');
            if (alert) {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }
        });
    });

    // Auto-dismiss success alerts after 5 seconds
    const successAlerts = document.querySelectorAll('.alert-success');
    successAlerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
}

/**
 * Initialize AJAX Search
 */
function initializeAjaxSearch() {
    const searchForm = document.querySelector('.search-form');
    const searchInput = document.querySelector('.search-input');
    const searchResults = document.querySelector('.search-results');

    if (!searchInput) return;

    let searchTimeout;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 2) {
            if (searchResults) {
                searchResults.innerHTML = '';
                searchResults.classList.add('hidden');
            }
            return;
        }

        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    });

    async function performSearch(query) {
        const category = document.querySelector('[name="category"]')?.value || '';

        try {
            const response = await fetch(`${getSiteUrl()}/api/search?q=${encodeURIComponent(query)}&category=${encodeURIComponent(category)}`);
            const data = await response.json();

            if (data.success && searchResults) {
                displaySearchResults(data.results);
            }
        } catch (error) {
            console.error('Search error:', error);
        }
    }

    function displaySearchResults(results) {
        if (!searchResults) return;

        if (results.length === 0) {
            searchResults.innerHTML = '<div class="search-no-results">No results found</div>';
            searchResults.classList.remove('hidden');
            return;
        }

        let html = '';
        results.forEach(item => {
            html += `
                <a href="${item.url}" class="search-result-item">
                    <span class="search-result-title">${escapeHtml(item.title)}</span>
                    <span class="search-result-category">${escapeHtml(item.category)}</span>
                </a>
            `;
        });

        searchResults.innerHTML = html;
        searchResults.classList.remove('hidden');
    }
}

/**
 * Initialize Form Validation
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');

    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            // Clear previous errors
            form.querySelectorAll('.form-error').forEach(error => error.remove());
            form.querySelectorAll('.input-error').forEach(input => input.classList.remove('input-error'));

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    showFieldError(field, 'This field is required');
                }

                // Email validation
                if (field.type === 'email' && field.value) {
                    if (!isValidEmail(field.value)) {
                        isValid = false;
                        showFieldError(field, 'Please enter a valid email address');
                    }
                }

                // Password confirmation
                if (field.name === 'password_confirm') {
                    const password = form.querySelector('[name="password"]');
                    if (password && field.value !== password.value) {
                        isValid = false;
                        showFieldError(field, 'Passwords do not match');
                    }
                }
            });

            if (!isValid) {
                e.preventDefault();
                // Scroll to first error
                const firstError = form.querySelector('.form-error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    });

    function showFieldError(field, message) {
        field.classList.add('input-error');
        const error = document.createElement('div');
        error.className = 'form-error';
        error.textContent = message;
        field.parentNode.appendChild(error);
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
}

/**
 * Fetch Daily Word from API
 */
function initializeDailyWordFetch() {
    const wordCard = document.querySelector('.word-card[data-fetch]');
    if (!wordCard) return;

    fetchDailyWord();

    async function fetchDailyWord() {
        try {
            const response = await fetch(`${getSiteUrl()}/api/daily-word`);
            const data = await response.json();

            if (data.success) {
                const tivValue = wordCard.querySelector('.word-tiv');
                const engValue = wordCard.querySelector('.word-eng');
                const partOfSpeech = wordCard.querySelector('.word-pos');

                if (tivValue) tivValue.textContent = data.word.tiv_word;
                if (engValue) engValue.textContent = data.word.english_meaning;
                if (partOfSpeech) partOfSpeech.textContent = `(${data.word.part_of_speech})`;
            }
        } catch (error) {
            console.error('Error fetching daily word:', error);
        }
    }
}

/**
 * Confirm Delete Action
 */
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

/**
 * Show Loading State
 */
function showLoading(element) {
    const originalContent = element.innerHTML;
    element.dataset.originalContent = originalContent;
    element.innerHTML = '<span class="loading"></span>';
    element.disabled = true;
}

/**
 * Hide Loading State
 */
function hideLoading(element) {
    element.innerHTML = element.dataset.originalContent || element.innerHTML;
    element.disabled = false;
}

/**
 * Get Site URL
 */
function getSiteUrl() {
    return document.querySelector('meta[name="site-url"]')?.content || '';
}

/**
 * Escape HTML
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Format Date
 */
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

/**
 * Copy to Clipboard
 */
async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        return true;
    } catch (err) {
        console.error('Failed to copy:', err);
        return false;
    }
}

/**
 * Share Content (if supported)
 */
async function shareContent(title, text, url) {
    if (navigator.share) {
        try {
            await navigator.share({ title, text, url });
            return true;
        } catch (err) {
            if (err.name !== 'AbortError') {
                console.error('Share failed:', err);
            }
            return false;
        }
    } else {
        // Fallback: copy URL to clipboard
        return copyToClipboard(url);
    }
}

// PWA Service Worker Registration
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    var base = (document.querySelector('meta[name="site-url"]') || {}).content || '';
    var swPath = base.replace(/\/$/, '') + '/service-worker.js';
    navigator.serviceWorker.register(swPath)
      .catch(() => {});
  });
}

// ============================================
//  CONTENT PROTECTION
//  Disables copy, right-click, print, drag
//  on all public content (not admin forms)
// ============================================
(function () {
  var role    = document.body.getAttribute('data-role') || '';
  var isAdmin = document.body.classList.contains('admin-body') ||
                window.location.pathname.indexOf('/admin') !== -1 ||
                role === 'admin' || role === 'moderator';
  if (isAdmin) return; // leave admin/moderator unrestricted on all pages

  // Add class to body — CSS protection hooks onto this (avoids heavy * selector)
  document.body.classList.add('protected');

  // ── Disable right-click ──────────────────
  document.addEventListener('contextmenu', function (e) {
    e.preventDefault();
    return false;
  });

  // ── Disable text copy / cut ──────────────
  document.addEventListener('copy', function (e) {
    var tag = (e.target && e.target.tagName) ? e.target.tagName.toUpperCase() : '';
    // Allow copying inside form inputs / textareas (admin editing etc.)
    if (tag === 'INPUT' || tag === 'TEXTAREA') return;
    e.preventDefault();
    return false;
  });
  document.addEventListener('cut', function (e) {
    var tag = (e.target && e.target.tagName) ? e.target.tagName.toUpperCase() : '';
    if (tag === 'INPUT' || tag === 'TEXTAREA') return;
    e.preventDefault();
    return false;
  });

  // ── Disable image dragging ───────────────
  document.addEventListener('dragstart', function (e) {
    if (e.target && e.target.tagName === 'IMG') {
      e.preventDefault();
      return false;
    }
  });

  // ── Block print keyboard shortcuts ───────
  //    Ctrl/Cmd + P  →  print
  //    Ctrl/Cmd + S  →  save page
  //    Ctrl/Cmd + U  →  view source
  document.addEventListener('keydown', function (e) {
    var ctrl = e.ctrlKey || e.metaKey;
    if (!ctrl) return;
    var key = e.key ? e.key.toLowerCase() : String.fromCharCode(e.keyCode).toLowerCase();
    if (key === 'p' || key === 's' || key === 'u') {
      e.preventDefault();
      return false;
    }
  });

  // ── Block window.print() ─────────────────
  window.addEventListener('beforeprint', function (e) {
    e.stopImmediatePropagation();
  });
})();
