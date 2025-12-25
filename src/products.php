<?php
require_once 'includes/header.php';
require_once 'models/Product.php';
require_once 'models/Category.php';

$productModel = new Product();
$categoryModel = new Category();

// Get filters from query parameters
$categoryId = (int)($_GET['category'] ?? 0);
$brandId = $_GET['brand'] ?? null;
$search = $_GET['search'] ?? null;
$sort = $_GET['sort'] ?? 'default';
$minPrice = $_GET['min_price'] ?? null;
$maxPrice = $_GET['max_price'] ?? null;
$page = (int)($_GET['page'] ?? 1);
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12; // Products per page
$offset = ($page - 1) * $limit;

// Get category information if an ID is provided
$currentCategory = null;
$breadcrumb = [];
if ($categoryId > 0) {
    $currentCategory = $categoryModel->getById($categoryId);
    if ($currentCategory) {
        $breadcrumb = $categoryModel->getBreadcrumb($categoryId);
    }
}

// Prepare filters array
$filters = [];
if ($categoryId > 0) {
    $filters['category_id'] = $categoryId;
    // Check if it's a main category to include products from subcategories
    if ($currentCategory && ($currentCategory['parent_id'] === null || $currentCategory['parent_id'] == 0)) {
        $filters['include_subcategories'] = true;
    }
}
if ($brandId) $filters['brand_id'] = $brandId;
if ($search) $filters['search'] = $search;
if ($minPrice) $filters['min_price'] = $minPrice;
if ($maxPrice) $filters['max_price'] = $maxPrice;
if ($sort) $filters['sort'] = $sort;

// Get products and count
$products = $productModel->getAll($limit, $offset, $filters);
$totalProducts = $productModel->getProductsCount($filters);
$totalPages = ceil($totalProducts / $limit);

// Get user's wishlist IDs if logged in
$userWishlistIds = [];
if (is_logged_in()) {
    $wStmt = $pdo->prepare("SELECT product_id FROM wishlists WHERE customer_id = ?");
    $wStmt->execute([get_current_user_id()]);
    $userWishlistIds = $wStmt->fetchAll(PDO::FETCH_COLUMN);
}

// Get all categories for filter sidebar
$allCategories = $categoryModel->getWithSubcategories();
?>

<div class="container-xxl pb-4">
    <?php 
    $breadcrumb_items = [['label' => t('home'), 'url' => 'index.php']];
    if (!empty($breadcrumb)) {
        $breadcrumb_items[] = ['label' => t('products'), 'url' => 'products.php'];
        foreach ($breadcrumb as $index => $bc) {
            $item = ['label' => $bc['name_en'] ?? ''];
            if ($index < count($breadcrumb) - 1) {
                $item['url'] = "products.php?category=" . $bc['id'];
            }
            $breadcrumb_items[] = $item;
        }
    } else {
        $breadcrumb_items[] = ['label' => t('products')];
    }
    include 'includes/breadcrumb.php';
    ?>
    
    <div class="row g-3">
        <!-- Filters sidebar -->
        <aside class="col-lg-3">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-filter me-2 text-primary"></i> <?php echo t('filters'); ?></h5>
                </div>
                <div class="card-body">
                    <form id="filter-form" method="GET" action="products.php">
                        <!-- Search filter -->
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase"><?php echo t('search'); ?></label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" value="<?php echo htmlspecialchars($search ?? ''); ?>" placeholder="<?php echo t('search_products'); ?>">
                                <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                        
                        <!-- Category filter -->
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase"><?php echo t('category'); ?></label>
                            <select name="category" class="form-select" onchange="this.form.submit()">
                                <option value=""><?php echo t('all_categories'); ?></option>
                                <?php foreach ($allCategories as $cat): ?>
                                    <optgroup label="<?php echo htmlspecialchars($cat['name_en']); ?>">
                                        <option value="<?php echo $cat['id']; ?>" <?php echo $categoryId == $cat['id'] ? 'selected' : ''; ?>>
                                            All <?php echo htmlspecialchars($cat['name_en']); ?>
                                        </option>
                                        <?php if (!empty($cat['subcategories'])): ?>
                                            <?php foreach ($cat['subcategories'] as $subcat): ?>
                                                <option value="<?php echo $subcat['id']; ?>" <?php echo $categoryId == $subcat['id'] ? 'selected' : ''; ?>>
                                                    &nbsp;&nbsp;└─ <?php echo htmlspecialchars($subcat['name_en']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Price range filter -->
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase"><?php echo t('price_range'); ?></label>
                            <div class="row g-2 align-items-center">
                                <div class="col">
                                    <input type="number" name="min_price" class="form-control form-control-sm" value="<?php echo htmlspecialchars($minPrice ?? ''); ?>" placeholder="<?php echo t('min'); ?>">
                                </div>
                                <div class="col-auto text-muted small">-</div>
                                <div class="col">
                                    <input type="number" name="max_price" class="form-control form-control-sm" value="<?php echo htmlspecialchars($maxPrice ?? ''); ?>" placeholder="<?php echo t('max'); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sort filter -->
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase"><?php echo t('sort_by'); ?></label>
                            <select name="sort" class="form-select shadow-none">
                                <option value="default" <?php echo ($sort == 'default') ? 'selected' : ''; ?>><?php echo t('relevance'); ?></option>
                                <option value="price_low" <?php echo ($sort == 'price_low') ? 'selected' : ''; ?>><?php echo t('price_low_high'); ?></option>
                                <option value="price_high" <?php echo ($sort == 'price_high') ? 'selected' : ''; ?>><?php echo t('price_high_low'); ?></option>
                                <option value="newest" <?php echo ($sort == 'newest') ? 'selected' : ''; ?>><?php echo t('newest'); ?></option>
                                <option value="best_selling" <?php echo ($sort == 'best_selling') ? 'selected' : ''; ?>><?php echo t('best_selling'); ?></option>
                                <option value="rating" <?php echo ($sort == 'rating') ? 'selected' : ''; ?>><?php echo t('top_rated'); ?></option>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger"><?php echo t('apply_filters'); ?></button>
                            <a href="products.php" id="clear-filters" class="btn btn-outline-secondary"><?php echo t('clear_filters'); ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Products content -->
        <main class="col-lg-9">
            <div class="card border-0 shadow-sm mb-4 bg-light overflow-hidden">
                <div class="card-body p-4 d-md-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="h3 mb-1 fw-bold"><?php echo $currentCategory ? htmlspecialchars($currentCategory['name_en']) : t('all_products'); ?></h1>
                        <?php if ($currentCategory && !empty($currentCategory['description_en'])): ?>
                            <p class="text-muted mb-0 small"><?php echo htmlspecialchars($currentCategory['description_en']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="mt-3 mt-md-0 d-flex align-items-center gap-3 bg-white p-2 rounded-3 shadow-sm border">
                        <span class="text-muted small">
                            <?php echo t('showing'); ?> <span class="fw-bold text-dark"><?php echo $totalProducts > 0 ? ($offset + 1) : 0; ?>-<?php echo min($offset + $limit, $totalProducts); ?></span> <?php echo t('of'); ?> <span class="fw-bold text-dark"><?php echo $totalProducts; ?></span>
                        </span>
                        <div class="vr"></div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small"><?php echo t('per_page'); ?>:</span>
                            <select id="items-per-page" class="form-select form-select-sm border-0 bg-light ps-3 pe-5" style="width: auto;">
                                <option value="12" <?php echo $limit == 12 ? 'selected' : ''; ?>>12</option>
                                <option value="24" <?php echo $limit == 24 ? 'selected' : ''; ?>>24</option>
                                <option value="48" <?php echo $limit == 48 ? 'selected' : ''; ?>>48</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-4 row-cols-xl-5 g-3">
                <?php foreach ($products as $product): ?>
                    <div class="col">
                        <?php include 'includes/product-card-jd.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($products) == 0): ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-search text-muted display-4"></i>
                    </div>
                    <h3>No products found</h3>
                    <p class="text-muted">Try adjusting your filters or search keywords.</p>
                </div>
            <?php endif; ?>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Product pagination" class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link shadow-none" href="?page=<?php echo $i; ?><?php echo $categoryId ? '&category=' . $categoryId : ''; ?><?php echo $brandId ? '&brand=' . $brandId : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $sort ? '&sort=' . $sort : ''; ?><?php echo $minPrice ? '&min_price=' . $minPrice : ''; ?><?php echo $maxPrice ? '&max_price=' . $maxPrice : ''; ?><?php echo '&limit=' . $limit; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Items per page selector
    document.getElementById('items-per-page').addEventListener('change', function() {
        const params = new URLSearchParams(window.location.search);
        params.set('limit', this.value);
        params.delete('page'); // Reset to first page
        window.location.href = `products.php?${params.toString()}`;
    });
});
</script>
