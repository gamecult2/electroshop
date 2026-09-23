<?php
require_once 'models/Product.php';
require_once 'models/Category.php';
require_once 'models/Review.php';
require_once 'includes/functions.php'; // Ensure functions are available if needed

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$productModel = new Product();
$categoryModel = new Category();
$reviewModel = new Review();

// Try to get the product, handle if it's not found or invalid
$product = null;
if ($productId > 0) {
    $product = $productModel->getById($productId);
}

if (!$product) {
    header('Location: products.php');
    exit;
}

// Fetch Reviews
$reviews = $reviewModel->getForProduct($productId, 50); // Get up to 50 approved reviews
$ratingStats = $reviewModel->getAverageRating($productId);
$avgRating = round((float)($ratingStats['average_rating'] ?? 0), 1);
$totalReviews = (int)($ratingStats['total_reviews'] ?? 0);

// ==========================================
// DATA NORMALIZATION & REAL DB FIELDS
// ==========================================

// Select localized product fields with a safe English fallback.
$lang = get_language();
$lang = in_array($lang, ['en', 'fr'], true) ? $lang : 'en';
$suffix = '_' . $lang;

// 1. Basic Fields (Localized)
$product['name'] = $product['name' . $suffix] ?? $product['name_en'] ?? $product['name'];

// Safe HTML rendering for descriptions (allow common formatting tags)
$allowedTags = '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6><a><img><table><thead><tbody><tr><th><td><blockquote><code><pre><hr><div><span>';
$rawDescription = $product['description' . $suffix] ?? $product['description_en'] ?? '';
$product['description'] = strip_tags($rawDescription, $allowedTags);

$product['subtitle'] = $product['short_description' . $suffix] ?? $product['short_description_en'] ?? '';

if (empty($product['subtitle'])) {
    // Fallback subtitle if empty
    $product['subtitle'] = "SKU: " . ($product['sku'] ?? 'N/A');
}

// 2. Pricing
$product['price'] = (float)$product['price'];
$product['discount_percentage'] = (float)$product['discount_percentage'];
if ($product['price'] > 0) {
    $product['final_price'] = $product['price'] * (1 - ($product['discount_percentage'] / 100));
} else {
    $product['final_price'] = 0;
}

// 3. Technical Specs (JSON)
$specsJson = $product['technical_specs' . $suffix] ?? $product['technical_specs_en'] ?? '[]';
$product['technical_specs'] = json_decode($specsJson, true) ?? [];
if (!is_array($product['technical_specs'])) $product['technical_specs'] = [];

// 4. Images (Fetch from related table)
$dbImages = $productModel->getProductImages($productId);
if (!empty($dbImages)) {
    $product['images'] = array_map(function($img) {
        return $img['image_url']; 
    }, $dbImages);
} else {
    // Legacy JSON support
    $legacyImages = isset($product['images']) && is_string($product['images']) ? json_decode($product['images'], true) : [];
    $product['images'] = !empty($legacyImages) ? $legacyImages : ['assets/images/placeholder.jpg'];
}

// 5. Variants (Fetch from related table)
// Fetch available options for UI (Color: Red, Blue; Memory: 64GB, 128GB)
$stmt = $pdo->prepare("
    SELECT DISTINCT va.attribute_name, va.attribute_value 
    FROM variant_attributes va
    JOIN product_variants pv ON va.product_variant_id = pv.id
    WHERE pv.product_id = ? AND pv.is_active = 1
    ORDER BY va.attribute_name, va.attribute_value
");
$stmt->execute([$productId]);
$optionsRaw = $stmt->fetchAll(PDO::FETCH_ASSOC);

$product['options'] = [];
foreach ($optionsRaw as $opt) {
    $product['options'][$opt['attribute_name']][] = $opt['attribute_value'];
}

// Fetch all actual SKUs and their attributes for matching
$dbVariants = $productModel->getProductVariants($productId);
$product['variants'] = !empty($dbVariants) ? $dbVariants : [];
foreach ($product['variants'] as &$variant) $variant['final_price'] = CatalogRules::price($variant['price'], $product['discount_percentage']);
unset($variant);

// 6. Other Fields
$product['brand'] = $product['brand_name'] ?? 'Generic';
$product['category'] = $product['category_name'] ?? 'Uncategorized';
$product['stock_quantity'] = (int)($product['stock_quantity'] ?? 0);
$product['rating_count'] = (int)($product['rating_count'] ?? 0);

// Mockup Data for missing JD Style elements (Promotions)
// These fields are not yet in the DB schema, so we keep mocks or use generic logic
$product['promotions'] = [];
if ($product['discount_percentage'] > 0) {
    $product['promotions'][] = [
        'type' => 'badge', 
        'label' => 'Discount', 
        'text' => sprintf("Save %s%% now", $product['discount_percentage'])
    ];
}
if ($product['stock_quantity'] < 10 && $product['stock_quantity'] > 0) {
    $product['promotions'][] = [
        'type' => 'tag', 
        'label' => 'Low Stock', 
        'text' => 'Order soon, few items left!'
    ];
}

// Related Products (Logic already exists in Model, let's use it)
$related_products = [];
if (!empty($product['category_id'])) {
    $related_products = $productModel->getRelated($productId, $product['category_id'], 5);
}

// Get breadcrumb
$breadcrumb = [];
if (!empty($product['category_id'])) {
    $breadcrumb = $categoryModel->getBreadcrumb($product['category_id']);
}

// Check wishlist status
$isWishlisted = false;
if (is_logged_in()) {
    $wStmt = $pdo->prepare("SELECT id FROM wishlists WHERE customer_id = ? AND product_id = ?");
    $wStmt->execute([get_current_user_id(), $productId]);
    $isWishlisted = $wStmt->fetch() !== false;
}

$salesCount = 0;
try {
    $salesStmt = $pdo->prepare("SELECT COALESCE(SUM(oi.quantity), 0) FROM order_items oi JOIN orders o ON o.id = oi.order_id WHERE oi.product_id = ? AND o.status <> 'cancelled'");
    $salesStmt->execute([$productId]);
    $salesCount = (int)$salesStmt->fetchColumn();
} catch (PDOException $e) {
    $salesCount = 0;
}

$page_title = $product['name'];
require_once 'includes/header.php';
?>

<div class="container-xxl pb-4">
    <?php 
    $breadcrumb_items = [
        ['label' => t('home'), 'url' => 'index.php'],
        ['label' => t('products'), 'url' => 'products.php']
    ];
    foreach ($breadcrumb as $bc) {
        $breadcrumb_items[] = ['label' => localized_field($bc, 'name'), 'url' => "products.php?category=" . $bc['id']];
    }
    $breadcrumb_items[] = ['label' => $product['name'] ?? ''];
    include 'includes/breadcrumb.php';
    ?>
    <h1 class="visually-hidden"><?php echo htmlspecialchars($product['name'] ?? ''); ?></h1>
    
    <div class="row g-3">
        <!-- Main Content Area (Media + Tabs) -->
        <div class="col-lg-8 col-12">
            <div class="row g-3">
                <!-- Media Viewer -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Thumbnails -->
                                <div class="col-2 col-md-2 order-2 order-md-1">
                                    <div class="d-flex flex-column gap-2 overflow-auto product-thumbnails" style="max-height: 500px; scrollbar-width: none;">
                                        <?php if (!empty($product['video_url'])): ?>
                                            <button type="button" class="thumbnail w-100 p-0 border rounded overflow-hidden d-flex align-items-center justify-content-center bg-light video-thumb"
                                                 onclick="toggleVideo(true)" 
                                                 style="aspect-ratio: 1; min-height: 50px;" aria-label="Play product video">
                                                <i class="fas fa-play-circle text-danger fs-4" aria-hidden="true"></i>
                                            </button>
                                        <?php endif; ?>

                                        <?php foreach ($product['images'] as $index => $image): ?>
                                            <button type="button" class="thumbnail w-100 p-0 bg-white border rounded overflow-hidden <?php echo $index === 0 && empty($product['video_url']) ? 'border-danger border-2' : ''; ?>"
                                                 onclick="toggleVideo(false); changeMainImage('<?php echo htmlspecialchars($image); ?>', this)"
                                                 style="aspect-ratio: 1; min-height: 50px;" aria-label="Show product image <?php echo $index + 1; ?>">
                                                <img src="<?php echo htmlspecialchars($image); ?>" class="img-fluid object-fit-contain w-100 h-100" alt="">
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <!-- Main Image/Video -->
                                <div class="col-10 col-md-10 order-1 order-md-2">
                                    <div class="position-relative border rounded bg-white overflow-hidden" style="aspect-ratio: 1/1;">
                                        <?php if (!empty($product['video_url'])): ?>
                                            <div id="product-video-wrapper" class="w-100 h-100 d-none align-items-center justify-content-center bg-black">
                                                <video id="main-product-video" class="w-100 h-100" controls>
                                                    <source src="<?php echo htmlspecialchars($product['video_url'] ?? ''); ?>" type="video/mp4">
                                                </video>
                                            </div>
                                        <?php endif; ?>

                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center" id="main-product-image-wrapper">
                                            <img src="<?php echo htmlspecialchars($product['images'][0] ?? ''); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name'] ?? 'Product'); ?>"
                                                 id="main-product-image"
                                                 class="img-fluid object-fit-cover w-100 h-100">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Price Card (Mobile Only) -->
                <div class="col-12 d-lg-none">
                     <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h2 class="app-page-title fw-bold mb-2 text-dark"><?php echo htmlspecialchars($product['name'] ?? ''); ?></h2>
                            <p class="text-muted small mb-4 product-sku" aria-live="polite"><?php echo htmlspecialchars($product['subtitle'] ?? ''); ?></p>
        
                            <div class="d-flex align-items-center gap-4 mb-4 pb-3 border-bottom overflow-auto">
                                <div class="text-center border-end pe-4">
                                    <div class="text-warning small mb-1"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                                    <div class="fw-bold h5 mb-0"><?php echo number_format($avgRating, 1); ?></div>
                                    <div class="text-muted small"><?php echo number_format($totalReviews); ?> Reviews</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-muted small mb-1">Sales</div>
                                    <div class="fw-bold h5 mb-0"><?php echo number_format($salesCount); ?></div>
                                    <div class="text-muted small">Orders</div>
                                </div>
                            </div>
        
                            <div class="bg-light p-3 rounded-3 mb-4">
                                <div class="d-flex align-items-baseline gap-2 mb-2">
                                    <span class="text-danger h2 fw-bold mb-0 product-final-price-mobile"><?php echo format_price($product['final_price']); ?></span>
                                    <div class="product-discount-display-mobile <?php echo $product['discount_percentage'] > 0 ? 'd-flex' : 'd-none'; ?> align-items-baseline gap-2">
                                        <span class="text-muted text-decoration-line-through x-small product-base-price-mobile"><?php echo format_price($product['price']); ?></span>
                                        <span class="badge bg-danger rounded-pill x-small product-discount-badge-mobile">-<?php echo (int)$product['discount_percentage']; ?>%</span>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <?php foreach ($product['promotions'] as $promo): ?>
                                        <span class="badge bg-white text-danger border border-danger fw-normal"><?php echo htmlspecialchars($promo['text'] ?? ''); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
        
                            <!-- Options -->
                            <?php if (!empty($product['options'])): ?>
                                <div class="mb-4">
                                    <?php foreach ($product['options'] as $optionName => $values): ?>
                                        <div class="option-row mb-3" data-option-name="<?php echo htmlspecialchars($optionName); ?>">
                                            <label class="form-label small fw-bold text-muted text-uppercase mb-2"><?php echo htmlspecialchars($optionName); ?>:</label>
                                            <div class="d-flex flex-wrap gap-2">
                                                <?php foreach ($values as $val): ?>
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-secondary px-3 rounded-pill option-item shadow-none"
                                                            aria-pressed="false" onclick="selectOption(this, this.closest('.option-row').dataset.optionName, this.textContent)">
                                                        <?php echo htmlspecialchars($val); ?>
                                                    </button>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
        
                            <div class="row align-items-center g-3 mb-4">
                                <div class="col-auto">
                                    <div class="input-group overflow-hidden rounded-3 shadow-none border" style="width: 130px;">
                                        <button type="button" class="btn btn-white border-0 px-3" onclick="changeQuantity(-1, 'quantity-mobile')" aria-label="Decrease quantity"><i class="fas fa-minus small" aria-hidden="true"></i></button>
                                        <input type="number" class="form-control border-0 text-center fw-bold p-0" value="1" min="1" id="quantity-mobile" aria-label="Quantity">
                                        <button type="button" class="btn btn-white border-0 px-3" onclick="changeQuantity(1, 'quantity-mobile')" aria-label="Increase quantity"><i class="fas fa-plus small" aria-hidden="true"></i></button>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="small <?php echo $product['stock_quantity'] > 0 ? 'text-success' : 'text-danger'; ?> fw-bold product-stock-status-mobile">
                                        <i class="fas <?php echo $product['stock_quantity'] > 0 ? 'fa-check-circle' : 'fa-times-circle'; ?> me-1"></i>
                                        <?php echo $product['stock_quantity'] > 0 ? $product['stock_quantity'] . ' in stock' : 'Out of Stock'; ?>
                                    </div>
                                </div>
                            </div>
        
                            <div class="d-grid gap-3 mb-4">
                                <button type="button" class="btn btn-danger btn-lg py-3 rounded-3 shadow-sm btn-add-cart-mobile d-flex align-items-center justify-content-center gap-2" onclick="addToCart(<?php echo $productId; ?>)">
                                    <i class="fas fa-shopping-cart"></i> <span><?php echo t('add_to_cart'); ?></span>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-lg py-3 rounded-3 btn-buy-now d-flex align-items-center justify-content-center gap-2" onclick="buyNow(<?php echo $productId; ?>)">
                                    <i class="fas fa-bolt"></i> <span><?php echo t('buy_now'); ?></span>
                                </button>
                            </div>
        
                            <div class="row g-2 mb-4">
                                <?php 
                                $promises = [
                                    ['icon' => 'fa-shield-alt', 'text' => 'Authentic Guarantee'],
                                    ['icon' => 'fa-truck-loading', 'text' => 'Fast Delivery'],
                                    ['icon' => 'fa-undo', 'text' => '7-Day Returns'],
                                    ['icon' => 'fa-check-circle', 'text' => 'Warranty Included']
                                ];
                                foreach ($promises as $p): ?>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center gap-2 text-muted small">
                                            <i class="fas <?php echo $p['icon']; ?> text-success"></i>
                                            <span><?php echo $p['text']; ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
        
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top flex-wrap gap-2">
                                <button type="button" class="btn btn-link text-decoration-none text-muted p-0 small add-to-wishlist-btn" data-product-id="<?php echo $productId; ?>">
                                    <i class="<?php echo $isWishlisted ? 'fas text-danger' : 'far'; ?> fa-heart me-1"></i> <?php echo t('wishlist'); ?>
                                </button>
                                <button type="button" class="btn btn-link text-decoration-none text-muted p-0 small" onclick="openProductChat(<?php echo $productId; ?>, '<?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($product['images'][0] ?? ''); ?>', '<?php echo htmlspecialchars($product['sku'] ?? ''); ?>')">
                                    <i class="fas fa-comment-dots me-1"></i> Ask about this product
                                </button>
                                <button type="button" class="btn btn-link text-decoration-none text-muted p-0 small" onclick="shareProduct(this)"><i class="fas fa-share-alt me-1" aria-hidden="true"></i> Share</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Details / Tabs -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0 p-0">
                            <ul class="nav nav-tabs border-0" id="productTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active rounded-0 border-0 py-3 px-4 fw-bold text-dark" id="intro-tab" data-bs-toggle="tab" data-bs-target="#intro" type="button" role="tab">About Product</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link rounded-0 border-0 py-3 px-4 fw-bold text-dark" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs" type="button" role="tab">Specifications</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link rounded-0 border-0 py-3 px-4 fw-bold text-dark" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">Reviews (<?php echo $totalReviews; ?>)</button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="productTabsContent">
                                <div class="tab-pane fade show active" id="intro" role="tabpanel">
                                    <div class="lh-lg text-muted">
                                        <?php echo ($product['description'] ?? ''); ?>
                                    </div>
                                </div>
                                
                                <div class="tab-pane fade" id="specs" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle">
                                            <tbody>
                                                <?php if (!empty($product['technical_specs'])): ?>
                                                    <?php foreach ($product['technical_specs'] as $spec => $value): ?>
                                                        <tr>
                                                            <th class="bg-light w-25"><?php echo htmlspecialchars($spec ?? ''); ?></th>
                                                            <td><?php echo htmlspecialchars($value ?? ''); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                                <?php if(!empty($product['brand'])): ?>
                                                    <tr><th class="bg-light">Brand</th><td><?php echo htmlspecialchars($product['brand'] ?? ''); ?></td></tr>
                                                <?php endif; ?>
                                                <?php if(!empty($product['sku'])): ?>
                                                    <tr><th class="bg-light">SKU</th><td><?php echo htmlspecialchars($product['sku'] ?? ''); ?></td></tr>
                                                <?php endif; ?>
                                                <?php if(!empty($product['weight']) && $product['weight'] > 0): ?>
                                                    <tr><th class="bg-light">Weight</th><td><?php echo htmlspecialchars($product['weight'] ?? ''); ?> kg</td></tr>
                                                <?php endif; ?>
                                                <?php if(!empty($product['dimensions'])): ?>
                                                    <tr><th class="bg-light">Dimensions</th><td><?php echo htmlspecialchars($product['dimensions'] ?? ''); ?></td></tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <div class="tab-pane fade" id="reviews" role="tabpanel">
                                    <div class="row g-4 mb-5">
                                        <div class="col-md-4">
                                            <div class="text-center p-4 bg-light rounded-3">
                                                <h2 class="display-4 fw-bold text-dark mb-1"><?php echo $avgRating; ?></h2>
                                                <div class="text-warning mb-2">
                                                    <?php for($i=1; $i<=5; $i++): ?>
                                                        <i class="<?php echo $i <= $avgRating ? 'fas' : 'far'; ?> fa-star"></i>
                                                    <?php endfor; ?>
                                                </div>
                                                <p class="text-muted small mb-0">Based on <?php echo $totalReviews; ?> reviews</p>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="d-flex flex-column gap-2">
                                                <?php 
                                                $stars = [5, 4, 3, 2, 1];
                                                foreach($stars as $star): 
                                                    $count = (int)($ratingStats[match($star) {
                                                        5 => 'five_star',
                                                        4 => 'four_star',
                                                        3 => 'three_star',
                                                        2 => 'two_star',
                                                        1 => 'one_star'
                                                    }] ?? 0);
                                                    $percent = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0;
                                                ?>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="text-muted small fw-bold" style="width: 50px;"><?php echo $star; ?> Stars</div>
                                                        <div class="progress flex-grow-1" style="height: 8px;">
                                                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $percent; ?>%" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                        <div class="text-muted small" style="width: 30px;"><?php echo $count; ?></div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="mb-4 opacity-50">

                                    <?php if (empty($reviews)): ?>
                                        <div class="text-center py-5 text-muted">
                                            <i class="fas fa-comment-slash fa-3x mb-3 opacity-25"></i>
                                            <p>No reviews yet for this product.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="d-flex flex-column gap-4">
                                            <?php foreach ($reviews as $review): ?>
                                                <div class="border-bottom pb-4">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <div>
                                                            <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($review['title'] ?: 'Review'); ?></h6>
                                                            <div class="text-warning small">
                                                                <?php for($i=1; $i<=5; $i++): ?>
                                                                    <i class="<?php echo $i <= $review['rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                                                                <?php endfor; ?>
                                                            </div>
                                                        </div>
                                                        <span class="text-muted small"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></span>
                                                    </div>
                                                    <p class="text-muted small mb-2"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                                                    
                                                    <?php if (!empty($review['reply_text'])): ?>
                                                        <div class="bg-light p-3 rounded-3 mt-3 border-start border-4 border-danger">
                                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                                <div class="fw-bold small text-danger">
                                                                    <i class="fas fa-store me-1"></i> Store Response
                                                                </div>
                                                                <span class="text-muted x-small"><?php echo date('M d, Y', strtotime($review['replied_at'])); ?></span>
                                                            </div>
                                                            <p class="mb-0 text-dark small fst-italic"><?php echo nl2br(htmlspecialchars($review['reply_text'])); ?></p>
                                                        </div>
                                                    <?php endif; ?>

                                                    <div class="d-flex align-items-center gap-2 mt-3">
                                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 24px; height: 24px; font-size: 10px;">
                                                            <?php echo strtoupper(substr($review['first_name'], 0, 1)); ?>
                                                        </div>
                                                        <span class="text-dark fw-bold small"><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></span>
                                                        <?php if ($review['is_verified_purchase']): ?>
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill x-small px-2 py-1">
                                                                <i class="fas fa-check-circle me-1"></i>Verified Purchase
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Products -->
                <div class="col-12 mt-4">
                    <h4 class="mb-4 d-flex align-items-center">
                        <span class="bg-danger text-white p-2 rounded me-3 d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-layer-group"></i>
                        </span>
                        Related Products
                    </h4>
                    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
                        <?php if (!empty($related_products)): ?>
                            <?php 
                            $mainProductData = $product; 
                            foreach ($related_products as $related): 
                                $product = $related; 
                                echo '<div class="col">';
                                include 'includes/product-card-mini.php'; 
                                echo '</div>';
                            endforeach; 
                            $product = $mainProductData; 
                            $productId = (int)$mainProductData['id'];
                            ?>
                        <?php else: ?>
                            <div class="col-12 text-muted fst-italic">No related products found.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Price Card Column (Desktop Sidebar) -->
        <div class="col-lg-4 d-none d-lg-block">
            <div class="card border-0 shadow-sm sticky-top sticky-below-header">
                <div class="card-body p-4">
                    <h2 class="app-page-title fw-bold mb-2 text-dark"><?php echo htmlspecialchars($product['name'] ?? ''); ?></h2>
                    <p class="text-muted small mb-4 product-sku" aria-live="polite"><?php echo htmlspecialchars($product['subtitle'] ?? ''); ?></p>

                    <div class="d-flex align-items-center gap-4 mb-4 pb-3 border-bottom overflow-auto">
                        <div class="text-center border-end pe-4">
                            <div class="text-warning small mb-1"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                            <div class="fw-bold h5 mb-0"><?php echo number_format($avgRating, 1); ?></div>
                            <div class="text-muted small"><?php echo number_format($totalReviews); ?> Reviews</div>
                        </div>
                        <div class="text-center">
                            <div class="text-muted small mb-1">Sales</div>
                            <div class="fw-bold h5 mb-0"><?php echo number_format($salesCount); ?></div>
                            <div class="text-muted small">Orders</div>
                        </div>
                    </div>

                    <div class="bg-light p-3 rounded-3 mb-4">
                        <div class="d-flex align-items-baseline gap-2 mb-2">
                            <span class="text-danger h2 fw-bold mb-0 product-final-price"><?php echo format_price($product['final_price']); ?></span>
                            <div class="product-discount-display <?php echo $product['discount_percentage'] > 0 ? 'd-flex' : 'd-none'; ?> align-items-baseline gap-2">
                                <span class="text-muted text-decoration-line-through x-small product-base-price"><?php echo format_price($product['price']); ?></span>
                                <span class="badge bg-danger rounded-pill x-small product-discount-badge">-<?php echo (int)$product['discount_percentage']; ?>%</span>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php foreach ($product['promotions'] as $promo): ?>
                                <span class="badge bg-white text-danger border border-danger fw-normal"><?php echo htmlspecialchars($promo['text'] ?? ''); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Options -->
                    <?php if (!empty($product['options'])): ?>
                        <div class="mb-4">
                            <?php foreach ($product['options'] as $optionName => $values): ?>
                                <div class="option-row mb-3" data-option-name="<?php echo htmlspecialchars($optionName); ?>">
                                    <label class="form-label small fw-bold text-muted text-uppercase mb-2"><?php echo htmlspecialchars($optionName); ?>:</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach ($values as $val): ?>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary px-3 rounded-pill option-item shadow-none"
                                                    aria-pressed="false" onclick="selectOption(this, this.closest('.option-row').dataset.optionName, this.textContent)">
                                                <?php echo htmlspecialchars($val); ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="row align-items-center g-3 mb-4">
                        <div class="col-auto">
                            <div class="input-group overflow-hidden rounded-3 shadow-none border" style="width: 130px;">
                                <button type="button" class="btn btn-white border-0 px-3" onclick="changeQuantity(-1, 'quantity')" aria-label="Decrease quantity"><i class="fas fa-minus small" aria-hidden="true"></i></button>
                                <input type="number" class="form-control border-0 text-center fw-bold p-0" value="1" min="1" id="quantity" aria-label="Quantity">
                                <button type="button" class="btn btn-white border-0 px-3" onclick="changeQuantity(1, 'quantity')" aria-label="Increase quantity"><i class="fas fa-plus small" aria-hidden="true"></i></button>
                            </div>
                        </div>
                        <div class="col">
                            <div class="small <?php echo $product['stock_quantity'] > 0 ? 'text-success' : 'text-danger'; ?> fw-bold product-stock-status">
                                <i class="fas <?php echo $product['stock_quantity'] > 0 ? 'fa-check-circle' : 'fa-times-circle'; ?> me-1"></i>
                                <?php echo $product['stock_quantity'] > 0 ? $product['stock_quantity'] . ' in stock' : 'Out of Stock'; ?>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-3 mb-4">
                        <button type="button" class="btn btn-danger btn-lg py-3 rounded-3 shadow-sm btn-add-cart d-flex align-items-center justify-content-center gap-2" onclick="addToCart(<?php echo $productId; ?>)">
                            <i class="fas fa-shopping-cart"></i> <span><?php echo t('add_to_cart'); ?></span>
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-lg py-3 rounded-3 btn-buy-now d-flex align-items-center justify-content-center gap-2" onclick="buyNow(<?php echo $productId; ?>)">
                            <i class="fas fa-bolt"></i> <span><?php echo t('buy_now'); ?></span>
                        </button>
                    </div>

                    <div class="row g-2 mb-4">
                        <?php 
                        $promises = [
                            ['icon' => 'fa-shield-alt', 'text' => 'Authentic Guarantee'],
                            ['icon' => 'fa-truck-loading', 'text' => 'Fast Delivery'],
                            ['icon' => 'fa-undo', 'text' => '7-Day Returns'],
                            ['icon' => 'fa-check-circle', 'text' => 'Warranty Included']
                        ];
                        foreach ($promises as $p): ?>
                            <div class="col-6">
                                <div class="d-flex align-items-center gap-2 text-muted small">
                                    <i class="fas <?php echo $p['icon']; ?> text-success"></i>
                                    <span><?php echo $p['text']; ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top flex-wrap gap-2">
                        <button type="button" class="btn btn-link text-decoration-none text-muted p-0 small add-to-wishlist-btn" data-product-id="<?php echo $productId; ?>">
                            <i class="<?php echo $isWishlisted ? 'fas text-danger' : 'far'; ?> fa-heart me-1"></i> <?php echo t('wishlist'); ?>
                        </button>
                        <button type="button" class="btn btn-link text-decoration-none text-muted p-0 small" onclick="openProductChat(<?php echo $productId; ?>, '<?php echo htmlspecialchars($product['name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($product['images'][0] ?? ''); ?>', '<?php echo htmlspecialchars($product['sku'] ?? ''); ?>')">
                            <i class="fas fa-comment-dots me-1"></i> Ask about this product
                        </button>
                        <button type="button" class="btn btn-link text-decoration-none text-muted p-0 small" onclick="shareProduct(this)"><i class="fas fa-share-alt me-1" aria-hidden="true"></i> Share</button>
                    </div>
                </div>
            </div>
        </div>


    </div>
</div>



<script>
async function shareProduct() {
    const shareData = {
        title: <?php echo json_encode($product['name']); ?>,
        text: <?php echo json_encode($product['subtitle']); ?>,
        url: window.location.href
    };
    try {
        if (navigator.share) {
            await navigator.share(shareData);
            return;
        }
        await navigator.clipboard.writeText(shareData.url);
        showNotification('Product link copied to your clipboard.', 'success');
    } catch (error) {
        if (error.name !== 'AbortError') showNotification('Unable to share this product right now.', 'error');
    }
}

// Product logic state
// Track available stock based on the most restrictive selected variant
let availableStock = <?php echo $product['stock_quantity']; ?>;
const basePrice = <?php echo $product['price']; ?>;
const discountPercentage = <?php echo $product['discount_percentage']; ?>;
const baseFinalPrice = <?php echo $product['final_price']; ?>;

function formatPrice(price) {
    return Math.round(price).toLocaleString('en-US') + ' ' + (typeof CURRENCY_SYMBOL !== 'undefined' ? CURRENCY_SYMBOL : 'DZD');
}

function toggleVideo(show) {
    const videoWrapper = document.getElementById('product-video-wrapper');
    const imageWrapper = document.getElementById('main-product-image-wrapper');
    const video = document.getElementById('main-product-video');
    
    if (show) {
        if (videoWrapper) {
            videoWrapper.classList.remove('d-none');
            videoWrapper.classList.add('d-flex');
            if (video) video.play();
        }
        if (imageWrapper) {
            imageWrapper.classList.remove('d-flex');
            imageWrapper.classList.add('d-none');
        }
        // Remove active class from other thumbnails
        document.querySelectorAll('.thumbnail').forEach(t => {
            t.classList.remove('active', 'border-danger', 'border-2');
        });
        const videoThumb = document.querySelector('.video-thumb');
        if (videoThumb) {
            videoThumb.classList.add('active', 'border-danger', 'border-2');
        }
    } else {
        if (videoWrapper) {
            videoWrapper.classList.add('d-none');
            videoWrapper.classList.remove('d-flex');
            if (video) video.pause();
        }
        if (imageWrapper) {
            imageWrapper.classList.add('d-flex');
            imageWrapper.classList.remove('d-none');
        }
        document.querySelector('.video-thumb')?.classList.remove('active', 'border-danger', 'border-2');
    }
}

function changeMainImage(src, thumb) {
    document.getElementById('main-product-image').src = src;
    document.querySelectorAll('.thumbnail').forEach(t => {
        t.classList.remove('active');
        t.classList.remove('border-danger');
        t.classList.remove('border-2');
    });
    thumb.classList.add('active');
    thumb.classList.add('border-danger');
    thumb.classList.add('border-2');
}

function zoomImage(e) {
    // Zoom logic placeholder
}
function resetZoom(e) {
    // Reset zoom placeholder
}

// New Variant Selection Logic
const allVariants = <?php echo json_encode($product['variants'], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;
let currentSelection = {}; // Stores { 'Color': 'Red', 'Size': 'XL' }
let selectedVariant = null; // The matched SKU object
document.addEventListener('DOMContentLoaded',()=>{ findMatchingVariant(); updateOptionAvailability(); });

function selectOption(btn, name, value) {
    name=name.trim(); value=value.trim();
    if (currentSelection[name]===value) delete currentSelection[name]; else currentSelection[name]=value;
    document.querySelectorAll('.option-row').forEach(row => {
        row.querySelectorAll('.option-item').forEach(option => {
            const selected=currentSelection[row.dataset.optionName]===option.textContent.trim();
            ['selected','bg-danger','text-white','border-danger'].forEach(cls=>option.classList.toggle(cls,selected));
            option.setAttribute('aria-pressed',String(selected));
        });
    });
    findMatchingVariant();
    updateOptionAvailability();
}

function findMatchingVariant() {
    const required=new Set();
    document.querySelectorAll('.option-row').forEach(row=>required.add(row.dataset.optionName));
    const complete=required.size>0 && [...required].every(name=>currentSelection[name]!==undefined);
    const matches=complete ? allVariants.filter(v=>Object.keys(v.attributes||{}).length===required.size && Object.entries(currentSelection).every(([key,value])=>v.attributes[key]===value)) : [];
    selectedVariant=matches.length===1 ? matches[0] : null;
    availableStock=selectedVariant ? Number(selectedVariant.stock_quantity) : (allVariants.length ? 0 : <?php echo (int)$product['stock_quantity']; ?>);
    const price=selectedVariant ? Number(selectedVariant.price) : basePrice;
    const finalPrice=selectedVariant ? Number(selectedVariant.final_price) : baseFinalPrice;
    document.querySelectorAll('.product-final-price,.product-final-price-mobile').forEach(el=>el.textContent=formatPrice(finalPrice));
    document.querySelectorAll('.product-base-price,.product-base-price-mobile').forEach(el=>el.textContent=formatPrice(price));
    document.querySelectorAll('.product-discount-display,.product-discount-display-mobile').forEach(el=>{
        el.classList.toggle('d-none',discountPercentage<=0);
        el.classList.toggle('d-flex',discountPercentage>0);
    });
    document.querySelectorAll('.product-sku').forEach(el=>el.textContent=selectedVariant ? 'SKU: '+selectedVariant.sku : <?php echo json_encode($product['subtitle'], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>);
    const message=allVariants.length && !selectedVariant ? (complete ? 'This combination is unavailable' : 'Choose all options') : (availableStock>0 ? availableStock+' in stock' : 'Out of Stock');
    document.querySelectorAll('.product-stock-status,.product-stock-status-mobile').forEach(el=>{
        el.textContent=message;
        el.classList.toggle('text-success',availableStock>0);
        el.classList.toggle('text-danger',availableStock<=0);
    });
    document.querySelectorAll('.btn-add-cart,.btn-add-cart-mobile,.btn-buy-now').forEach(el=>el.disabled=availableStock<=0);
    document.querySelectorAll('#quantity,#quantity-mobile').forEach(el=>{
        el.max=Math.max(1,availableStock);
        if (Number(el.value)>availableStock) el.value=Math.max(1,availableStock);
    });
}



function changeQuantity(delta, inputId = 'quantity') {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    let val = (parseInt(input.value) || 1) + delta;

    // Ensure quantity doesn't go below 1
    if (val < 1) val = 1;

    // Limit quantity to available stock
    if (val > availableStock && availableStock > 0) {
        val = availableStock;
        notify(`Maximum quantity available is ${availableStock}`, 'info');
    }

    document.querySelectorAll('#quantity,#quantity-mobile').forEach(el => el.value = val);
}

/**
 * Standardized Notification System
 */
const notify = (msg, type) => {
    if (typeof showNotification === 'function') {
        showNotification(msg, type);
    } else {
        showNotification(msg, 'info');
    }
};

/**
 * Add to Cart logic for Single Product Page
 */
async function addToCart(id, redirect = false) {
    let quantity = 1;
    // Check which quantity input is visible/active
    const desktopInput = document.getElementById('quantity');
    const mobileInput = document.getElementById('quantity-mobile');
    
    // Simple visibility check (offsetParent is null if hidden)
    if (desktopInput && desktopInput.offsetParent !== null) {
        quantity = parseInt(desktopInput.value) || 1;
    } else if (mobileInput) {
        quantity = parseInt(mobileInput.value) || 1;
    }

    // Check if quantity exceeds available stock
    if (quantity > availableStock) {
        notify(`Quantity cannot exceed available stock (${availableStock})`, 'error');
        return;
    }

    // Check if all options are selected
    // Note: Option Row logic uses currentSelection which is shared
    const rowCounts = document.querySelectorAll('.d-lg-block .option-row').length > 0 ? 
                     document.querySelectorAll('.d-lg-block .option-row').length : 
                     (document.querySelectorAll('.option-row').length / 2); // Approximate if duplicated without unique identifiers
                     
    // Better way: Count unique option names from data attributes
    const uniqueOptions = new Set();
    document.querySelectorAll('.option-row').forEach(row => uniqueOptions.add(row.dataset.optionName));
    const requiredOptions = uniqueOptions.size;
    
    const selectedOptionsCount = Object.keys(currentSelection).length;
    
    if (requiredOptions > 0 && selectedOptionsCount < requiredOptions) {
        notify('Please select all product options', 'info');
        // Scroll to visible options
        const visibleOptions = document.querySelector('.option-row:not(.d-none)');
        if (visibleOptions) visibleOptions.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    
    if (requiredOptions > 0 && !selectedVariant) {
        notify('This combination is currently unavailable', 'error');
        return;
    }
    
    // Select button based on visibility or class (mobile vs desktop)
    let btn = null;
    if (redirect) {
         btn = document.querySelector('.btn-buy-now:not(.d-none)') || document.querySelector('.btn-buy-now');
         // Check visibility
         document.querySelectorAll('.btn-buy-now').forEach(b => {
             if (b.offsetParent !== null) btn = b;
         });
    } else {
         // Try to find the visible one
         document.querySelectorAll('.btn-add-cart, .btn-add-cart-mobile').forEach(b => {
             if (b.offsetParent !== null) btn = b;
         });
    }
    
    if (!btn) return;

    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';

    try {
        const response = await fetch('api/cart/add.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                product_id: id,
                quantity: quantity,
                variant_id: selectedVariant ? selectedVariant.id : null
            })
        });

        const data = await response.json();

        if (data.success) {
            if (redirect) {
                window.location.href = 'checkout.php';
            } else {
                notify(data.message || 'Product added to cart!', 'success');

                if (typeof updateCartBadge === 'function') {
                    updateCartBadge();
                } else {
                    const badge = document.querySelector('.header-actions .badge') || document.querySelector('.action-btn[href="cart.php"] .badge');
                    if (badge) {
                        let currentCount = parseInt(badge.textContent) || 0;
                        badge.textContent = currentCount + 1;
                    }
                }

                btn.classList.add('btn-success');
                btn.innerHTML = '<i class="fas fa-check"></i> Added!';

                const headerCartIcon = document.querySelector('.action-btn[href="cart.php"] i');
                if (headerCartIcon) {
                    headerCartIcon.style.transition = 'transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
                    headerCartIcon.style.transform = 'scale(1.5)';
                    setTimeout(() => headerCartIcon.style.transform = 'scale(1)', 300);
                }

                setTimeout(() => {
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                    btn.classList.remove('btn-success');
                }, 2000);
            }
        } else {
            notify(data.message || 'Failed to add item', 'error');
            btn.innerHTML = originalContent;
            btn.disabled = false;
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        notify('Connection error. Please try again.', 'error');
        btn.innerHTML = originalContent;
        btn.disabled = false;
    }
}

function updateOptionAvailability() {
    // Iterate over all option rows
    document.querySelectorAll('.option-row').forEach(row => {
        const optionName = row.dataset.optionName;
        
        row.querySelectorAll('.option-item').forEach(btn => {
            const value = btn.textContent.trim();
            
            // Create a potential selection state merging current selection with this button
            const testSelection = { ...currentSelection };
            testSelection[optionName] = value;
            
            // Check if any variant matches this subset
            const isAvailable = allVariants.some(v => {
                if (!v.attributes) return false;
                if (Number(v.stock_quantity)<=0) return false;
                
                // Check if variant matches ALL criteria in testSelection
                for (const [key, val] of Object.entries(testSelection)) {
                    if (v.attributes[key] !== val) return false;
                }
                
                return true;
            });
            
            if (isAvailable) {
                btn.classList.remove('disabled');
                btn.disabled = false;
            } else {
                btn.classList.add('disabled');
                btn.disabled = true;
            }
        });
    });
}

// Function to open chat about a product
// openProductChat is now handled by chat-widget.php

async function buyNow(id) {
    await addToCart(id, true);
}

// Tab Switcher
document.addEventListener('DOMContentLoaded', function() {
    // Initialize option availability
    updateOptionAvailability();

    const tabs = document.querySelectorAll('.detail-tab');
    const panes = document.querySelectorAll('.tab-pane-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active
            tabs.forEach(t => t.classList.remove('active'));
            panes.forEach(p => p.classList.remove('active'));

            // Add active
            this.classList.add('active');
            const target = this.getAttribute('data-tab');
            document.getElementById(target).classList.add('active');
        });
    });

    // Add event listener to quantity input to validate against stock when manually changed
    document.querySelectorAll('#quantity,#quantity-mobile').forEach(quantityInput => {
        quantityInput.addEventListener('change', function() {
            const normalized = Math.max(1, Math.min(parseInt(this.value) || 1, Math.max(1, availableStock)));
            document.querySelectorAll('#quantity,#quantity-mobile').forEach(el => el.value = normalized);
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
