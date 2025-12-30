<?php
require_once 'includes/header.php';
require_once 'models/Product.php';
require_once 'models/Category.php';

$productModel = new Product();
$categoryModel = new Category();

// Get filters from query parameters
$categoryId = (int)($_GET['category'] ?? 0);
$brandId = $_GET['brand'] ?? null;
$sort = $_GET['sort'] ?? 'default';
$viewMode = $_GET['view'] ?? 'grid'; // Default to grid view
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
    
    <!-- Combined Header & Filter Bar (Single Row) -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-2 px-3">
            <form id="filter-form" method="GET" action="products3.php">
                <input type="hidden" name="view" value="<?php echo htmlspecialchars($viewMode); ?>">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    
                    <!-- Category -->
                    <div class="me-2">
                        <select name="category" class="form-select form-select-sm border-light-subtle bg-light" style="width: 150px;" onchange="this.form.submit()">
                            <option value=""><?php echo t('all_categories'); ?></option>
                            <?php foreach ($allCategories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $categoryId == $cat['id'] ? 'selected' : ''; ?> class="fw-bold">
                                    <?php echo htmlspecialchars($cat['name_en']); ?>
                                </option>
                                <?php if (!empty($cat['subcategories'])): ?>
                                    <?php foreach ($cat['subcategories'] as $subcat): ?>
                                        <option value="<?php echo $subcat['id']; ?>" <?php echo $categoryId == $subcat['id'] ? 'selected' : ''; ?>>
                                            &nbsp;&nbsp;└ <?php echo htmlspecialchars($subcat['name_en']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Sort -->
                    <div class="me-2 d-flex align-items-center gap-2">
                        <span class="text-muted small fw-bold text-uppercase">Sort:</span>
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="sort" id="sort_default" value="default" <?php echo ($sort == 'default') ? 'checked' : ''; ?> onchange="this.form.submit()">
                            <label class="btn btn-outline-secondary border-light-subtle py-1 px-2" for="sort_default" title="Relevance">Relevance</label>

                            <input type="radio" class="btn-check" name="sort" id="sort_price_asc" value="price_low" <?php echo ($sort == 'price_low') ? 'checked' : ''; ?> onchange="this.form.submit()">
                            <label class="btn btn-outline-secondary border-light-subtle py-1 px-2" for="sort_price_asc" title="Price Low to High">Price <i class="fas fa-arrow-up fa-xs"></i></label>

                            <input type="radio" class="btn-check" name="sort" id="sort_price_desc" value="price_high" <?php echo ($sort == 'price_high') ? 'checked' : ''; ?> onchange="this.form.submit()">
                            <label class="btn btn-outline-secondary border-light-subtle py-1 px-2" for="sort_price_desc" title="Price High to Low">Price <i class="fas fa-arrow-down fa-xs"></i></label>

                            <input type="radio" class="btn-check" name="sort" id="sort_newest" value="newest" <?php echo ($sort == 'newest') ? 'checked' : ''; ?> onchange="this.form.submit()">
                            <label class="btn btn-outline-secondary border-light-subtle py-1 px-2" for="sort_newest" title="Newest">Newest</label>
                        </div>
                    </div>

                    <!-- View Mode -->
                    <div class="me-2 d-none d-md-block">
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="view_toggle" id="view_grid" autocomplete="off" <?php echo $viewMode === 'grid' ? 'checked' : ''; ?> onclick="document.querySelector('input[name=\'view\']').value='grid'; this.form.submit();">
                            <label class="btn btn-outline-secondary border-light-subtle py-1" for="view_grid" title="Grid View"><i class="fas fa-th-large"></i></label>

                            <input type="radio" class="btn-check" name="view_toggle" id="view_list" autocomplete="off" <?php echo $viewMode === 'list' ? 'checked' : ''; ?> onclick="document.querySelector('input[name=\'view\']').value='list'; this.form.submit();">
                            <label class="btn btn-outline-secondary border-light-subtle py-1" for="view_list" title="List View"><i class="fas fa-list"></i></label>
                        </div>
                    </div>

                    <!-- Per Page & Meta -->
                    <div class="ms-auto d-flex align-items-center gap-2">
                        <span class="text-muted d-none d-xl-inline" style="font-size: 0.7rem; font-weight: 600;">
                            <?php echo t('showing'); ?> <?php echo $totalProducts > 0 ? ($offset + 1) : 0; ?>-<?php echo min($offset + $limit, $totalProducts); ?>
                        </span>
                        
                        <select name="limit" id="limit" class="form-select form-select-sm border-light-subtle bg-light py-0 ps-2 pe-4" style="height: 28px; width: 65px; font-size: 0.75rem;" onchange="this.form.submit()">
                            <option value="12" <?php echo $limit == 12 ? 'selected' : ''; ?>>12</option>
                            <option value="24" <?php echo $limit == 24 ? 'selected' : ''; ?>>24</option>
                            <option value="48" <?php echo $limit == 48 ? 'selected' : ''; ?>>48</option>
                        </select>

                        <?php if ($categoryId || $sort != 'default'): ?>
                            <a href="products3.php" class="btn btn-outline-danger btn-sm border-0 px-1 py-0" title="Clear">
                                <i class="fas fa-times-circle"></i>
                            </a>
                        <?php endif; ?>
                    </div>

                </div>
            </form>
        </div>
    </div>
    
    <!-- Products Grid/List -->
    <?php if ($viewMode === 'list'): ?>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($products as $product): ?>
                <?php include 'includes/product-card-list.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-3">
            <?php foreach ($products as $product): ?>
                <div class="col">
                    <?php include 'includes/product-card-jd.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if (count($products) == 0): ?>
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="fas fa-box-open text-muted display-4"></i>
            </div>
            <h3>No products found</h3>
            <p class="text-muted">Try adjusting your filters.</p>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Product pagination" class="mt-5">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link shadow-none" href="?page=<?php echo $i; ?><?php echo $categoryId ? '&category=' . $categoryId : ''; ?><?php echo $brandId ? '&brand=' . $brandId : ''; ?><?php echo $sort ? '&sort=' . $sort : ''; ?><?php echo '&limit=' . $limit; ?><?php echo '&view=' . $viewMode; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
            
        </nav>
    <?php endif; ?>
</div>

<?php
require_once 'includes/footer.php';
?>
