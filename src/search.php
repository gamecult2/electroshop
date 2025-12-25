<?php
require_once 'includes/header.php';
require_once 'models/Search.php';
require_once 'models/Product.php';

$searchModel = new Search();
$productModel = new Product();

// Get parameters
$query = $_GET['q'] ?? '';
$categoryId = $_GET['category_id'] ?? null;
$brandId = $_GET['brand_id'] ?? null;
$minPrice = $_GET['min_price'] ?? null;
$maxPrice = $_GET['max_price'] ?? null;
$minRating = $_GET['min_rating'] ?? null;
$inStock = $_GET['in_stock'] ?? null;
$sort = $_GET['sort'] ?? 'relevance';
$page = (int)($_GET['page'] ?? 1);
$limit = (int)($_GET['limit'] ?? 20);

// Prepare filters array
$filters = [];
if ($categoryId) $filters['category_id'] = $categoryId;
if ($brandId) $filters['brand_id'] = $brandId;
if ($minPrice) $filters['price_min'] = $minPrice;
if ($maxPrice) $filters['price_max'] = $maxPrice;
if ($minRating) $filters['min_rating'] = $minRating;
if ($inStock) $filters['in_stock'] = 1;

// Perform search
$searchResults = $searchModel->searchProducts($query, $filters, $sort, $page, $limit);

// Save search history if user is logged in
if (is_logged_in() && !empty($query)) {
    $searchModel->saveSearchHistory(get_current_user_id(), $query, $searchResults['total']);
}

// Get available filters for the current search
$filterData = $searchModel->getFilters($query);

// Get user's search history if logged in
$searchHistory = [];
if (is_logged_in()) {
    $searchHistory = $searchModel->getSearchHistory(get_current_user_id(), 5);
}
?>

<div class="container-xxl pb-4">
    <?php 
    $breadcrumb_items = [
        ['label' => t('home'), 'url' => 'index.php'],
        ['label' => t('search_results_for') . ' "' . ($query) . '"']
    ];
    include 'includes/breadcrumb.php';
    ?>
    
    <div class="row g-4">
        <!-- Filters sidebar -->
        <aside class="col-lg-3">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-filter me-2 text-primary"></i> <?php echo t('filters'); ?></h5>
                </div>
                <div class="card-body">
                    <form id="search-filters-form" method="GET" action="search.php">
                        <!-- Search term (hidden) -->
                        <input type="hidden" name="q" value="<?php echo htmlspecialchars($query); ?>">
                        
                        <!-- Price Range Filter -->
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase"><?php echo t('price_range'); ?></label>
                            <div class="row g-2 align-items-center">
                                <div class="col">
                                    <input type="number" id="min_price" name="min_price" class="form-control form-control-sm" placeholder="<?php echo t('min'); ?>" value="<?php echo htmlspecialchars($minPrice ?? ''); ?>" min="0">
                                </div>
                                <div class="col-auto text-muted small">-</div>
                                <div class="col">
                                    <input type="number" id="max_price" name="max_price" class="form-control form-control-sm" placeholder="<?php echo t('max'); ?>" value="<?php echo htmlspecialchars($maxPrice ?? ''); ?>" min="0">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Brand Filter -->
                        <?php if (!empty($filterData['brands'])): ?>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase"><?php echo t('brand'); ?></label>
                            <div class="d-flex flex-column gap-2 overflow-auto" style="max-height: 200px;">
                                <?php foreach ($filterData['brands'] as $brand): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="brand_id[]" value="<?php echo $brand['id']; ?>" id="brand_<?php echo $brand['id']; ?>" <?php echo (isset($_GET['brand_id']) && in_array($brand['id'], (array)$_GET['brand_id'])) ? 'checked' : ''; ?>>
                                        <label class="form-check-label small" for="brand_<?php echo $brand['id']; ?>">
                                            <?php echo htmlspecialchars($brand['name']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Category Filter -->
                        <?php if (!empty($filterData['categories'])): ?>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase"><?php echo t('category'); ?></label>
                            <div class="d-flex flex-column gap-2 overflow-auto" style="max-height: 200px;">
                                <?php foreach ($filterData['categories'] as $category): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="category_id[]" value="<?php echo $category['id']; ?>" id="cat_<?php echo $category['id']; ?>" <?php echo (isset($_GET['category_id']) && in_array($category['id'], (array)$_GET['category_id'])) ? 'checked' : ''; ?>>
                                        <label class="form-check-label small" for="cat_<?php echo $category['id']; ?>">
                                            <?php echo htmlspecialchars($category['name_en']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Rating Filter -->
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase"><?php echo t('rating'); ?></label>
                            <div class="d-flex flex-column gap-2">
                                <?php for ($i = 4; $i >= 1; $i--): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="min_rating" value="<?php echo $i; ?>" id="rating_<?php echo $i; ?>" <?php echo ($minRating == $i) ? 'checked' : ''; ?>>
                                        <label class="form-check-label small" for="rating_<?php echo $i; ?>">
                                            <?php for ($j = 0; $j < $i; $j++) echo '<i class="fas fa-star text-warning"></i>'; ?>
                                            <?php for ($j = $i; $j < 5; $j++) echo '<i class="far fa-star text-muted"></i>'; ?>
                                            & <?php echo t('up'); ?>
                                        </label>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <!-- Availability Filter -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="in_stock" name="in_stock" value="1" <?php echo $inStock ? 'checked' : ''; ?>>
                                <label class="form-check-label small fw-bold" for="in_stock"><?php echo t('in_stock_only'); ?></label>
                            </div>
                        </div>
                        
                        <!-- Sort By -->
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase"><?php echo t('sort_by'); ?></label>
                            <select id="sort_by" name="sort" class="form-select shadow-none">
                                <option value="relevance" <?php echo ($sort == 'relevance') ? 'selected' : ''; ?>><?php echo t('relevance'); ?></option>
                                <option value="price_low" <?php echo ($sort == 'price_low') ? 'selected' : ''; ?>><?php echo t('price_low_high'); ?></option>
                                <option value="price_high" <?php echo ($sort == 'price_high') ? 'selected' : ''; ?>><?php echo t('price_high_low'); ?></option>
                                <option value="newest" <?php echo ($sort == 'newest') ? 'selected' : ''; ?>><?php echo t('newest'); ?></option>
                                <option value="rating" <?php echo ($sort == 'rating') ? 'selected' : ''; ?>><?php echo t('top_rated'); ?></option>
                                <option value="popularity" <?php echo ($sort == 'popularity') ? 'selected' : ''; ?>><?php echo t('most_popular'); ?></option>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger"><?php echo t('apply_filters'); ?></button>
                            <a href="search.php?q=<?php echo urlencode($query); ?>" class="btn btn-outline-secondary"><?php echo t('clear_filters'); ?></a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Search History for logged-in users -->
            <?php if (is_logged_in() && !empty($searchHistory)): ?>
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 fw-bold"><?php echo t('recent_searches'); ?></h6>
                    </div>
                    <div class="list-group list-group-flush small">
                        <?php foreach ($searchHistory as $history): ?>
                            <a href="search.php?q=<?php echo urlencode($history['search_term']); ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-history me-2 text-muted"></i> <?php echo htmlspecialchars($history['search_term']); ?></span>
                                <span class="badge bg-light text-dark border"><?php echo $history['result_count']; ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </aside>
        
        <!-- Search results -->
        <main class="col-lg-9">
            <div class="card border-0 shadow-sm mb-4 bg-light overflow-hidden">
                <div class="card-body p-4 d-md-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="h3 mb-1 fw-bold"><?php echo t('search_results_for'); ?> "<?php echo htmlspecialchars($query); ?>"</h1>
                        <p class="text-muted mb-0 small"><?php echo $searchResults['total']; ?> <?php echo t('results_found'); ?></p>
                    </div>
                    <div class="mt-3 mt-md-0 d-flex align-items-center gap-3 bg-white p-2 rounded-3 shadow-sm border">
                        <span class="text-muted small">
                            <?php echo t('showing'); ?> <span class="fw-bold text-dark"><?php echo $searchResults['total'] > 0 ? (($page - 1) * $limit + 1) : 0; ?>-<?php echo min($page * $limit, $searchResults['total']); ?></span> <?php echo t('of'); ?> <span class="fw-bold text-dark"><?php echo $searchResults['total']; ?></span>
                        </span>
                        <div class="vr"></div>
                        <div class="d-flex align-items-center gap-2 d-none d-md-flex">
                            <span class="text-muted x-small"><?php echo t('limit'); ?>:</span>
                            <select id="items-per-page" class="form-select form-select-sm border-0 bg-light py-0 px-2" style="width: auto;" onchange="window.location.href = 'search.php?q=<?php echo urlencode($query); ?>&limit=' + this.value">
                                <option value="20" <?php echo $limit == 20 ? 'selected' : ''; ?>>20</option>
                                <option value="40" <?php echo $limit == 40 ? 'selected' : ''; ?>>40</option>
                                <option value="60" <?php echo $limit == 60 ? 'selected' : ''; ?>>60</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($searchResults['results'])): ?>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-3">
                    <?php foreach ($searchResults['results'] as $product): ?>
                        <div class="col">
                            <?php include 'includes/product-card-jd.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($searchResults['pages'] > 1): ?>
                    <nav aria-label="Search results pagination" class="mt-5">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $searchResults['pages']; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link shadow-none" href="search.php?q=<?php echo urlencode($query); ?>&page=<?php echo $i; ?><?php echo $categoryId ? '&category_id=' . $categoryId : ''; ?><?php echo $brandId ? '&brand_id=' . $brandId : ''; ?><?php echo $minPrice ? '&min_price=' . $minPrice : ''; ?><?php echo $maxPrice ? '&max_price=' . $maxPrice : ''; ?><?php echo $minRating ? '&min_rating=' . $minRating : ''; ?><?php echo $inStock ? '&in_stock=1' : ''; ?><?php echo $sort ? '&sort=' . $sort : ''; ?>&limit=<?php echo $limit; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-search text-muted display-4"></i>
                    </div>
                    <h3><?php echo t('no_results_found'); ?></h3>
                    <p class="text-muted"><?php echo t('try_different_keywords'); ?></p>
                    <a href="products.php" class="btn btn-danger rounded-pill px-5"><?php echo t('browse_all_products'); ?></a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>

<script>
// Add search filtering functionality
function applyFilters() {
    const formData = new FormData(document.getElementById('search-filters-form'));
    const searchParams = new URLSearchParams(formData);
    
    // Preserve page parameter
    searchParams.set('page', 1);
    
    window.location.href = 'search.php?' + searchParams.toString();
}
</script>
