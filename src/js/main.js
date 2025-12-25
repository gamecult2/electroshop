// main.js - Centralized logic for QwenShop
document.addEventListener('DOMContentLoaded', function () {
    initCart();
    initWishlist();
    initApp();
    initCountdownTimers();
    initSearchAutocomplete();
    initCategoryMenu();
    initProductComparison();
});

function initApp() {
    setupResponsiveNav();
    setupLazyLoading();
}

function initCountdownTimers() {
    const timerElement = document.getElementById('nearest-flash-sale-end');
    if (!timerElement) return;
    const endTimeStr = timerElement.getAttribute('data-end-time');
    if (!endTimeStr) return;
    const endTime = new Date(endTimeStr).getTime();

    const updateTimer = () => {
        const now = new Date().getTime();
        const distance = endTime - now;
        if (distance < 0) {
            document.getElementById('fs-hours') && (document.getElementById('fs-hours').textContent = '00');
            document.getElementById('fs-minutes') && (document.getElementById('fs-minutes').textContent = '00');
            document.getElementById('fs-seconds') && (document.getElementById('fs-seconds').textContent = '00');
            return;
        }
        const hours = Math.floor(distance / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        if (document.getElementById('fs-hours')) document.getElementById('fs-hours').textContent = hours.toString().padStart(2, '0');
        if (document.getElementById('fs-minutes')) document.getElementById('fs-minutes').textContent = minutes.toString().padStart(2, '0');
        if (document.getElementById('fs-seconds')) document.getElementById('fs-seconds').textContent = seconds.toString().padStart(2, '0');
    };
    setInterval(updateTimer, 1000);
    updateTimer();
}

/**
 * Add to Cart Functionality
 */
function initCart() {
    document.addEventListener('click', function (e) {
        const button = e.target.closest('.add-to-cart-btn');
        if (!button) return;

        e.preventDefault();
        e.stopPropagation();

        const productId = button.getAttribute('data-product-id');
        if (!productId) return;

        // Visual feedback
        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;

        apiAddToCart(productId)
            .then(data => {
                setTimeout(() => {
                    button.innerHTML = originalContent;
                    button.disabled = false;

                    if (data.success) {
                        updateCartBadge();
                        showNotification(data.message || 'Added to cart!', 'success');
                    } else {
                        showNotification(data.message || 'Error', 'error');
                    }
                }, 800);
            })
            .catch(error => {
                console.error('Cart Error:', error);
                button.innerHTML = originalContent;
                button.disabled = false;
                showNotification('Connection error', 'error');
            });
    });
}

/**
 * Wishlist Functionality
 */
function initWishlist() {
    document.addEventListener('click', function (e) {
        const button = e.target.closest('.add-to-wishlist-btn') || e.target.closest('.wishlist-btn-overlay');
        if (!button) return;

        e.preventDefault();
        e.stopPropagation();

        const productId = button.getAttribute('data-product-id');
        if (!productId) {
            console.error('Wishlist Error: No product ID found on button');
            return;
        }

        const icon = button.querySelector('i');
        if (!icon) return;

        const originalIconClass = icon.className;
        const originalColor = icon.style.color;

        // Note: Actual toggle happens on server, but we can optimistically toggle visual state
        // based on current class if we tracked it, but for now we rely on the response.
        
        // Add loading state if desired, or just wait for response.
        // For immediate feedback, we could check the icon class.
        // detailed state management is better left to the response to ensure sync.

        addToWishlist(productId)
            .then(data => {
                if (data.success) {
                    showNotification(data.message || 'Wishlist updated', 'success');
                    
                    // Update Icon based on action
                    if (data.action === 'added') {
                        icon.className = 'fas fa-heart'; // Solid heart
                        icon.style.color = '#ef4444';    // Red
                        
                        // Optional: Add a small bounce animation
                        button.style.transform = 'scale(1.2)';
                        setTimeout(() => button.style.transform = '', 200);
                    } else if (data.action === 'removed') {
                        icon.className = 'far fa-heart'; // Outline heart
                        icon.style.color = '';           // Reset color
                    }

                    // Dispatch custom event for other components to react
                    document.dispatchEvent(new CustomEvent('wishlistUpdated', { 
                        detail: { productId, action: data.action, count: data.count } 
                    }));

                    // Update Badge with the exact count from server
                    updateWishlistBadgeCount(data.count);
                    
                } else if (data.status === 401) {
                    showNotification('Please login first', 'info');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1500);
                } else {
                    showNotification(data.error || 'Error updating wishlist', 'error');
                }
            })
            .catch(err => {
                console.error('Wishlist Error:', err);
                showNotification('Connection error', 'error');
            });
    });

    // Initial badge update
    updateWishlistBadge();
}

/**
 * API Calls
 */
function apiAddToCart(productId, quantity = 1, variantId = null) {
    return fetch('api/cart/add.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            product_id: productId, 
            quantity: quantity,
            variant_id: variantId 
        })
    }).then(res => res.json());
}

function addToWishlist(productId) {
    return fetch('api/wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId })
    }).then(res => {
        if (res.status === 401) return { status: 401, success: false };
        return res.json();
    });
}

/**
 * UI Updates
 */
function updateCartBadge() {
    fetch('api/cart/count.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const badge = document.querySelector('.header-actions .badge') ||
                    document.querySelector('.action-btn[href="cart.php"] .badge');
                if (badge) {
                    badge.textContent = data.count;
                    badge.style.display = data.count > 0 ? 'flex' : 'none';
                }
            }
        });
}

function updateWishlistBadgeCount(count) {
    const badge = document.querySelector('.action-btn[href="wishlist.php"] .badge');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    }
}

function updateWishlistBadge() {
    // We can fetch the wishlist items to get the count
    fetch('api/wishlist.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const count = data.items ? data.items.length : 0;
                updateWishlistBadgeCount(count);
            }
        })
        .catch(() => { }); // Fail silently if not logged in
}

/**
 * Search Autocomplete
 */
function initSearchAutocomplete() {
    const searchWrapper = document.querySelector('.search-bar');
    if (!searchWrapper) return;

    const input = searchWrapper.querySelector('input');
    if (!input) return;

    input.addEventListener('input', function () {
        const query = this.value.trim();
        if (query.length < 2) return hideAutocomplete();
        fetch(`api/search/autocomplete.php?q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => showAutocomplete(data, searchWrapper, input));
    });

    document.addEventListener('click', e => {
        if (!e.target.closest('.search-bar')) hideAutocomplete();
    });
}

function showAutocomplete(results, wrapper, input) {
    hideAutocomplete();
    if (!results.length) return;

    const div = document.createElement('div');
    div.className = 'autocomplete-results';
    results.forEach(r => {
        const item = document.createElement('div');
        item.className = 'autocomplete-item';
        item.innerHTML = `<strong>${r.name}</strong> <span style="color:#e4393c">${r.price}</span>`;
        item.onclick = () => {
            input.value = r.name;
            wrapper.querySelector('form').submit();
        };
        div.appendChild(item);
    });
    wrapper.appendChild(div);
}

function hideAutocomplete() {
    const el = document.querySelector('.autocomplete-results');
    if (el) el.remove();
}

/**
 * Other Components
 */
function initCategoryMenu() {
    const toggles = document.querySelectorAll('.menu-toggle');
    const list = document.querySelector('.categories-list');
    if (toggles.length && list) {
        toggles.forEach(toggle => {
            toggle.onclick = (e) => {
                e.preventDefault();
                list.style.display = (list.style.display === 'block') ? 'none' : 'block';
                // Close nav links if open
                const navLinks = document.querySelector('.nav-links');
                if (navLinks) navLinks.classList.remove('active');
            };
        });
    }
}

function setupResponsiveNav() {
    const navToggle = document.querySelector('.nav-toggle');
    const navLinks = document.querySelector('.nav-links');
    const categoryList = document.querySelector('.categories-list');

    if (navToggle && navLinks) {
        navToggle.onclick = (e) => {
            e.preventDefault();
            navLinks.classList.toggle('active');
            // Close category list if open
            if (categoryList) categoryList.style.display = 'none';
        };
    }
}
function setupLazyLoading() { }

/**
 * Notifications
 */
function showNotification(message, type = 'info') {
    const div = document.createElement('div');
    div.className = `notification notification-${type}`;
    div.textContent = message;
    div.style.cssText = `
        position: fixed; top: 20px; right: 20px; padding: 15px 20px;
        background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8'};
        color: white; border-radius: 4px; z-index: 10000; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transition: opacity 0.5s;
    `;
    document.body.appendChild(div);
    setTimeout(() => { div.style.opacity = '0'; setTimeout(() => div.remove(), 500); }, 3000);
}

const showToast = showNotification;

/**
 * Comparison
 */
function initProductComparison() {
    document.querySelectorAll('.compare-product-btn').forEach(btn => {
        btn.onclick = () => {
            let list = JSON.parse(localStorage.getItem('productComparison')) || [];
            if (!list.includes(btn.dataset.productId)) {
                list.push(btn.dataset.productId);
                localStorage.setItem('productComparison', JSON.stringify(list));
                updateComparisonBadge();
                showNotification('Added to comparison', 'success');
            } else {
                showNotification('Already in comparison', 'info');
            }
        };
    });
}

function updateComparisonBadge() {
    const list = JSON.parse(localStorage.getItem('productComparison')) || [];
    const badge = document.querySelector('.action-btn[href="compare.php"] .badge');
    if (badge) badge.textContent = list.length;
}
