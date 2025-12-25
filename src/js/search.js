// js/search.js - Advanced Search & Filtering System

document.addEventListener('DOMContentLoaded', function() {
    initSearchSystem();
});

function initSearchSystem() {
    initSearchAutocomplete();
    initSearchFilters();
    initSearchHistory();
}

// Search Autocomplete with AJAX
function initSearchAutocomplete() {
    const searchInput = document.querySelector('.search-bar input[name="q"], .search-form input');
    if (!searchInput) return;
    
    const suggestionsContainer = createSuggestionsContainer();
    document.body.appendChild(suggestionsContainer);
    
    let timeout;
    let activeIndex = -1;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(timeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            hideSuggestions();
            return;
        }
        
        timeout = setTimeout(() => {
            fetchAutocompleteSuggestions(query);
        }, 300);
    });
    
    searchInput.addEventListener('keydown', function(e) {
        const suggestions = document.querySelectorAll('.search-suggestion');
        
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, suggestions.length - 1);
                updateActiveSuggestion(suggestions);
                break;
            case 'ArrowUp':
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, -1);
                updateActiveSuggestion(suggestions);
                break;
            case 'Enter':
                if (activeIndex >= 0) {
                    e.preventDefault();
                    suggestions[activeIndex].click();
                } else {
                    this.form.submit();
                }
                break;
        }
    });
    
    searchInput.addEventListener('blur', function() {
        // Delay hiding so click event can register
        setTimeout(() => hideSuggestions(), 200);
    });
}

function createSuggestionsContainer() {
    const container = document.createElement('div');
    container.className = 'search-suggestions';
    container.style.cssText = `
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        z-index: 1000;
        max-height: 300px;
        overflow-y: auto;
        display: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    `;
    return container;
}

function fetchAutocompleteSuggestions(query) {
    fetch(`api/search/autocomplete.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            displayAutocompleteSuggestions(data.suggestions);
        })
        .catch(error => {
            console.error('Error fetching autocomplete suggestions:', error);
        });
}

function displayAutocompleteSuggestions(suggestions) {
    const container = document.querySelector('.search-suggestions');
    container.innerHTML = '';
    
    if (suggestions.length === 0) {
        hideSuggestions();
        return;
    }
    
    suggestions.forEach((suggestion, index) => {
        const item = document.createElement('div');
        item.className = 'search-suggestion';
        item.textContent = suggestion;
        item.style.cssText = `
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        `;
        
        item.addEventListener('mousedown', function(e) {
            e.preventDefault(); // Prevent blur event
            document.querySelector('.search-bar input[name="q"], .search-form input').value = suggestion;
            document.querySelector('.search-bar form, .search-form').submit();
            hideSuggestions();
        });
        
        container.appendChild(item);
    });
    
    container.style.display = 'block';
}

function updateActiveSuggestion(suggestions) {
    // Remove active class from all
    suggestions.forEach(s => s.classList.remove('active'));
    
    // Add active class to current
    if (activeIndex >= 0) {
        suggestions[activeIndex].classList.add('active');
        suggestions[activeIndex].scrollIntoView({block: 'nearest'});
    }
}

function hideSuggestions() {
    const container = document.querySelector('.search-suggestions');
    if (container) {
        container.style.display = 'none';
    }
}

// Search Filters
function initSearchFilters() {
    // Price range sliders
    initPriceRange();
    
    // Brand and category filters
    initCheckboxFilters();
    
    // Rating filters
    initRatingFilters();
    
    // Sort by
    initSortBy();
    
    // Apply filters button
    const applyBtn = document.querySelector('.apply-filters-btn');
    if (applyBtn) {
        applyBtn.addEventListener('click', applyFilters);
    }
    
    // Clear filters
    const clearBtn = document.querySelector('.clear-filters-btn');
    if (clearBtn) {
        clearBtn.addEventListener('click', clearFilters);
    }
}

function initPriceRange() {
    const minPriceInput = document.querySelector('#min_price');
    const maxPriceInput = document.querySelector('#max_price');
    
    if (minPriceInput && maxPriceInput) {
        minPriceInput.addEventListener('change', applyFilters);
        maxPriceInput.addEventListener('change', applyFilters);
    }
}

function initCheckboxFilters() {
    const checkboxes = document.querySelectorAll('.filter-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', applyFilters);
    });
}

function initRatingFilters() {
    const ratingInputs = document.querySelectorAll('input[name="min_rating"]');
    ratingInputs.forEach(input => {
        input.addEventListener('change', applyFilters);
    });
}

function initSortBy() {
    const sortBySelect = document.querySelector('#sort_by');
    if (sortBySelect) {
        sortBySelect.addEventListener('change', applyFilters);
    }
}

function applyFilters() {
    // Collect all filter values
    const formData = new FormData();
    
    // Search term
    const searchTerm = document.querySelector('.search-bar input[name="q"], .search-form input');
    if (searchTerm && searchTerm.value) {
        formData.append('q', searchTerm.value);
    }
    
    // Price range
    const minPrice = document.querySelector('#min_price');
    const maxPrice = document.querySelector('#max_price');
    if (minPrice && minPrice.value) formData.append('min_price', minPrice.value);
    if (maxPrice && maxPrice.value) formData.append('max_price', maxPrice.value);
    
    // Brand filters
    document.querySelectorAll('.filter-checkbox[name="brand_id[]"]').forEach(checkbox => {
        if (checkbox.checked) {
            formData.append('brand_id[]', checkbox.value);
        }
    });
    
    // Category filters
    document.querySelectorAll('.filter-checkbox[name="category_id[]"]').forEach(checkbox => {
        if (checkbox.checked) {
            formData.append('category_id[]', checkbox.value);
        }
    });
    
    // Rating
    const selectedRating = document.querySelector('input[name="min_rating"]:checked');
    if (selectedRating) {
        formData.append('min_rating', selectedRating.value);
    }
    
    // In stock
    const inStock = document.querySelector('#in_stock');
    if (inStock && inStock.checked) {
        formData.append('in_stock', '1');
    }
    
    // Sort by
    const sortBy = document.querySelector('#sort_by');
    if (sortBy) {
        formData.append('sort', sortBy.value);
    }
    
    // Page
    const page = document.querySelector('#page');
    if (page) {
        formData.append('page', page.value);
    }
    
    // Make AJAX request to apply filters
    fetch('search.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(html => {
        // Update results area
        const resultsContainer = document.querySelector('.search-results, .product-grid');
        if (resultsContainer) {
            resultsContainer.innerHTML = html;
        }
    })
    .catch(error => {
        console.error('Error applying filters:', error);
    });
}

function clearFilters() {
    // Reset all filter inputs
    document.querySelectorAll('.filter-checkbox').forEach(checkbox => checkbox.checked = false);
    document.querySelectorAll('input[name="min_rating"]').forEach(radio => radio.checked = false);
    document.querySelector('#min_price').value = '';
    document.querySelector('#max_price').value = '';
    document.querySelector('#in_stock').checked = false;
    document.querySelector('#sort_by').value = 'relevance';
    
    // Apply filters to clear
    applyFilters();
}

// Search History
function initSearchHistory() {
    const historyBtn = document.querySelector('#show-search-history');
    if (historyBtn) {
        historyBtn.addEventListener('click', toggleSearchHistory);
    }
}

function toggleSearchHistory() {
    const historyContainer = document.querySelector('.search-history');
    if (historyContainer) {
        historyContainer.style.display = historyContainer.style.display === 'none' ? 'block' : 'none';
    }
}

// Real-time filtering as user types/changes filters
function initRealTimeFiltering() {
    // Implement real-time filtering if needed
    // This would update results as filters change without requiring "Apply" button
}

// Export functions for use in other modules
window.QwenShopSearch = {
    initSearchSystem,
    fetchAutocompleteSuggestions,
    applyFilters,
    clearFilters
};