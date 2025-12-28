<?php
// admin/product_details.php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';
require_once '../models/Product.php';
require_once '../models/Category.php';
require_once '../models/Brand.php';

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$productModel = new Product();
$categoryModel = new Category();
$brandModel = new Brand();

// Fetch product data
$product = $productModel->getByIdIncludeInactive($productId);

if (!$product) {
    $_SESSION['message'] = 'Product not found.';
    $_SESSION['message_type'] = 'error';
    header('Location: products.php');
    exit;
}

// Fetch additional data
$variants = $productModel->getProductVariants($productId);
$images = $productModel->getProductImages($productId);

// Fetch reviews summary (custom query for now as Product model might not have it)
$stmt = $pdo->prepare("SELECT COUNT(*) as count, AVG(rating) as avg_rating FROM reviews WHERE product_id = ? AND is_approved = 1");
$stmt->execute([$productId]);
$reviewStats = $stmt->fetch();
$reviewCount = $reviewStats['count'];
$avgRating = number_format((float)$reviewStats['avg_rating'], 1);

$page_title = 'Product Details';
$page_heading = 'Product Details';

require_once 'header.php';
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="h4 mb-0 fw-bold">
                <i class="fas fa-box text-primary me-2"></i><?php echo htmlspecialchars($product['name_en']); ?>
            </h3>
            <p class="text-muted mb-0">SKU: <?php echo htmlspecialchars($product['sku']); ?> | ID: #<?php echo $productId; ?></p>
        </div>
        <div class="btn-group">
            <a href="../product.php?id=<?php echo $productId; ?>" target="_blank" class="btn btn-outline-info btn-sm rounded-pill shadow-sm me-2 px-3">
                <i class="fas fa-external-link-alt me-1"></i> View in Store
            </a>
            <a href="edit_product.php?id=<?php echo $productId; ?>" class="btn btn-primary btn-sm rounded-pill shadow-sm me-2 px-4">
                <i class="fas fa-edit me-1"></i> Edit Product
            </a>
            <a href="products.php" class="btn btn-outline-secondary btn-sm rounded-pill shadow-sm px-3">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Product Info & Stats -->
        <div class="col-lg-4">
            <!-- Main Image & Status -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center p-4">
                    <div class="position-relative d-inline-block mb-3">
                        <?php 
                        $mainImage = !empty($images) ? $images[0]['image_url'] : 'img/product-placeholder.jpg';
                        if (strpos($mainImage, 'uploads/') === 0 || strpos($mainImage, 'img/') === 0) {
                            $mainImage = '../' . $mainImage;
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($mainImage); ?>" alt="Product Image" class="img-fluid rounded-3 border shadow-sm" style="max-height: 250px;">
                        <?php if ($product['discount_percentage'] > 0): ?>
                            <span class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-danger">
                                -<?php echo (int)$product['discount_percentage']; ?>%
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <span class="badge <?php echo $product['is_active'] ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary'; ?> rounded-pill px-3">
                            <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                        <?php if ($product['is_featured']): ?>
                            <span class="badge bg-warning-subtle text-warning rounded-pill px-3">Featured</span>
                        <?php endif; ?>
                        <?php if ($product['is_new_arrival']): ?>
                            <span class="badge bg-info-subtle text-info rounded-pill px-3">New</span>
                        <?php endif; ?>
                        <?php if ($product['is_best_seller']): ?>
                            <span class="badge bg-danger-subtle text-danger rounded-pill px-3">Best Seller</span>
                        <?php endif; ?>
                    </div>

                    <div class="row text-center g-0 border-top pt-3">
                        <div class="col-4 border-end">
                            <h6 class="fw-bold mb-0 text-dark"><?php echo $product['stock_quantity']; ?></h6>
                            <small class="text-muted x-small text-uppercase">Stock</small>
                        </div>
                        <div class="col-4 border-end">
                            <h6 class="fw-bold mb-0 text-dark"><?php echo $reviewCount; ?></h6>
                            <small class="text-muted x-small text-uppercase">Reviews</small>
                        </div>
                        <div class="col-4">
                            <h6 class="fw-bold mb-0 text-warning"><i class="fas fa-star me-1"></i><?php echo $avgRating; ?></h6>
                            <small class="text-muted x-small text-uppercase">Rating</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pricing & Key Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-tag text-muted me-2"></i>Pricing & Identity</h6>
                </div>
                <div class="card-body pt-0">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted small">Base Price</span>
                        <span class="fw-bold"><?php echo format_price($product['price']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted small">Final Price</span>
                        <span class="fw-bold text-danger fs-5"><?php echo format_price($product['price'] * (1 - $product['discount_percentage'] / 100)); ?></span>
                    </div>
                    <hr class="border-light-subtle my-3">
                    <div class="mb-3">
                        <small class="text-muted text-uppercase fw-bold x-small d-block mb-1">Category</small>
                        <span class="fw-medium text-dark"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted text-uppercase fw-bold x-small d-block mb-1">Brand</small>
                        <span class="fw-medium text-dark"><?php echo htmlspecialchars($product['brand_name'] ?? 'Generic'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Media Gallery -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-images text-muted me-2"></i>Media Gallery</h6>
                    <span class="badge bg-light text-dark border"><?php echo count($images); ?></span>
                </div>
                <div class="card-body pt-0">
                    <div class="row g-2">
                        <?php foreach ($images as $img): 
                            $imgUrl = $img['image_url'];
                            if (strpos($imgUrl, 'uploads/') === 0 || strpos($imgUrl, 'img/') === 0) {
                                $imgUrl = '../' . $imgUrl;
                            }
                        ?>
                            <div class="col-3">
                                <a href="<?php echo htmlspecialchars($imgUrl); ?>" target="_blank">
                                    <img src="<?php echo htmlspecialchars($imgUrl); ?>" class="img-thumbnail object-fit-cover w-100" style="height: 60px;">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Details & Variants -->
        <div class="col-lg-8">
            <!-- Description -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-align-left text-muted me-2"></i>Product Description</h6>
                </div>
                <div class="card-body pt-0">
                    <div class="mb-4">
                        <h6 class="fw-bold small text-uppercase text-muted mb-2">Short Description</h6>
                        <p class="text-muted small"><?php echo nl2br(htmlspecialchars($product['short_description_en'] ?? 'No short description provided.')); ?></p>
                    </div>
                    <div>
                        <h6 class="fw-bold small text-uppercase text-muted mb-2">Full Description</h6>
                        <div class="form-control bg-white p-3" style="height: auto; max-height: 400px; overflow-y: auto; line-height: 1.6;">
                            <?php 
                            $description = $product['description_en'];
                            // Fix relative image paths for admin view (handling both double and single quotes)
                            $description = preg_replace('/src=["\']uploads\//i', 'src="../uploads/', $description);
                            echo $description; 
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technical Specs -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-cogs text-muted me-2"></i>Technical Specifications</h6>
                </div>
                <div class="card-body pt-0">
                    <?php 
                    $specs = json_decode($product['technical_specs_en'] ?? '[]', true);
                    if (empty($specs)): 
                    ?>
                        <p class="text-muted small fst-italic mb-0">No technical specifications defined.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless mb-0">
                                <tbody>
                                    <?php foreach ($specs as $key => $value): ?>
                                        <tr class="border-bottom border-light-subtle">
                                            <td class="text-muted small fw-bold py-2 w-25"><?php echo htmlspecialchars($key); ?></td>
                                            <td class="text-dark small py-2"><?php echo htmlspecialchars($value); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Variants -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-cubes text-muted me-2"></i>Variants</h6>
                    <span class="badge bg-light text-dark border"><?php echo count($variants); ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($variants)): ?>
                        <div class="text-center py-4 text-muted small">
                            This product has no variants.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-4 border-0 text-muted small text-uppercase">SKU</th>
                                        <th class="border-0 text-muted small text-uppercase">Name/Attributes</th>
                                        <th class="border-0 text-muted small text-uppercase">Price</th>
                                        <th class="border-0 text-muted small text-uppercase">Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($variants as $variant): ?>
                                        <tr>
                                            <td class="px-4 fw-bold small text-dark"><?php echo htmlspecialchars($variant['sku']); ?></td>
                                            <td>
                                                <div class="fw-bold small text-dark"><?php echo htmlspecialchars($variant['variant_name']); ?></div>
                                                <div class="text-muted x-small">
                                                    <?php 
                                                    $attrs = [];
                                                    foreach ($variant['attributes'] as $k => $v) {
                                                        $attrs[] = "$k: $v";
                                                    }
                                                    echo implode(' | ', $attrs);
                                                    ?>
                                                </div>
                                            </td>
                                            <td class="small"><?php echo format_price($variant['price']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $variant['stock_quantity'] > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?> rounded-pill border">
                                                    <?php echo $variant['stock_quantity']; ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>