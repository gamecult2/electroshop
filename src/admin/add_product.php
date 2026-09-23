<?php
// admin/add_product.php
// Start session only if not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/functions.php';
require_once '../db_connect.php';
require_once '../models/Product.php';
require_once '../models/Category.php';
require_once '../models/Brand.php';
require_once '../helpers/SKUGenerator.php';
require_once '../helpers/MediaManager.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$productModel = new Product();
$categoryModel = new Category();
$brandModel = new Brand();
$skuGenerator = new SKUGenerator();
$mediaManager = new MediaManager();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['form_action'] ?? 'add_product';
    
    // Sanitize and validate input
    $name_en = sanitize_input($_POST['name_en'] ?? '');
    $description_en = str_replace('../uploads/', 'uploads/', $_POST['description_en'] ?? '');
    $short_description_en = sanitize_input($_POST['short_description_en'] ?? '');

    $productData = [
        'name_en' => $name_en,
        'description_en' => $description_en,
        'short_description_en' => $short_description_en,
        'category_id' => (int)($_POST['category_id'] ?? 0),
        'brand_id' => !empty($_POST['brand_id']) ? (int)$_POST['brand_id'] : null,
        'price' => (float)($_POST['price'] ?? 0),
        'discount_percentage' => (float)($_POST['discount_percentage'] ?? 0),
        'stock_quantity' => $_POST['stock_quantity'] ?? 0,
        'sku' => sanitize_input($_POST['sku'] ?? ''),
        'weight' => (float)($_POST['weight'] ?? 0),
        'dimensions' => sanitize_input($_POST['dimensions'] ?? ''),
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'is_new_arrival' => isset($_POST['is_new_arrival']) ? 1 : 0,
        'is_best_seller' => isset($_POST['is_best_seller']) ? 1 : 0,
        'technical_specs_en' => []
    ];

    // Process technical specs from arrays
    if (!empty($_POST['tech_spec_keys'])) {
        foreach ($_POST['tech_spec_keys'] as $index => $key) {
            $key = trim($key);
            $val = trim($_POST['tech_spec_values'][$index] ?? '');
            if (!empty($key) && !empty($val)) {
                $productData['technical_specs_en'][$key] = $val;
            }
        }
    }
    
    // Validate required fields
    if (empty($productData['name_en']) || 
        empty($productData['category_id']) || 
        ($action === 'add_product' && $productData['price'] <= 0)) {
        $message = 'Please fill all required fields';
        $messageType = 'error';
    } else {
        try {
            // Allocate within Product::create's transaction.
            if (isset($_POST['auto_generate_sku'])) $productData['sku'] = '';
            
            $productData['variants'] = CatalogRules::variantsFromForm($_POST['variants'] ?? [], $productData['price']);
            // Save product and variants together before uploading media.
            $productId = $productModel->create($productData);
            
            if (!$productId) {
                throw new Exception('Failed to create product');
            }
            
            // Handle Media Uploads (both images and videos) using MediaManager
            if (isset($_FILES['product_media']) && !empty($_FILES['product_media']['name'][0])) {
                try {
                    $slug = create_slug($name_en);
                    $uploadResult = $mediaManager->uploadMedia($productId, $_FILES['product_media'], true, $slug);
                    
                    if (!empty($uploadResult['errors'])) {
                        $message .= ' Media upload warnings: ' . implode(', ', $uploadResult['errors']);
                    }
                } catch (Exception $e) {
                    $message .= ' Media upload error: ' . $e->getMessage();
                }
            }

            
            $_SESSION['message'] = 'Product created successfully';
            $_SESSION['message_type'] = 'success';
            
            if ($action === 'upload_media') {
                // If they just wanted to upload media, redirect to edit page to see it
                header('Location: edit_product.php?id=' . $productId);
            } else {
                // Default: Redirect back to list
                header('Location: products.php');
            }
            exit;
            
        } catch (Exception $e) {
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
            error_log("Product creation error: " . $e->getMessage());
        }
    }
}

// Get categories and brands for select options
$categories = $categoryModel->getWithSubcategories();
$brands = $brandModel->getAll();

// Set page title and heading variables for the template
$page_title = 'Add Product';
$page_heading = 'Add Product';

// Include the shared header template
include 'header.php';
?>
    <!-- jQuery is required for Summernote -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Summernote Lite -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas <?php echo $messageType === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'; ?> me-3 fs-4"></i>
                        <div><?php echo $message; ?></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="add_product.php" enctype="multipart/form-data" id="productForm" data-product-id="<?php echo (int)($productId ?? 0); ?>">
                <input type="hidden" name="form_action" id="form_action" value="add_product">
                
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h4 class="fw-bold mb-0">Add Product</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item small"><a href="dashboard.php" class="text-decoration-none text-muted">Dashboard</a></li>
                                <li class="breadcrumb-item small"><a href="products.php" class="text-decoration-none text-muted">Products</a></li>
                                <li class="breadcrumb-item small active fw-bold text-danger" aria-current="page">New Product</li>
                            </ol>
                        </nav>
                    </div>
                    <a href="products.php" class="btn btn-light btn-sm text-secondary rounded-pill px-3 fw-bold border shadow-xs">
                        <i class="fas fa-arrow-left me-1"></i> Back to Products
                    </a>
                </div>

                <div class="row g-4">
                    <!-- Left Column: Main Info -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3 border-0">
                                <h5 class="mb-0 fw-bold px-2"><i class="fas fa-info-circle me-2 text-danger"></i> Product Information</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name_en" class="form-label small fw-bold text-muted text-uppercase">Product Name *</label>
                                        <input type="text" id="name_en" name="name_en" class="form-control border-light-subtle shadow-none" placeholder="Enter product name" value="<?php echo htmlspecialchars($_POST['name_en'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="sku" class="form-label small fw-bold text-muted text-uppercase">SKU / Item code</label>
                                        <div class="input-group">
                                            <input type="text" id="sku" name="sku" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars($_POST['sku'] ?? ''); ?>" placeholder="Will be auto-generated" readonly>
                                            <div class="input-group-text bg-light border-light-subtle">
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox" name="auto_generate_sku" id="auto_generate_sku" checked onchange="toggleSKUField()">
                                                    <label class="form-check-label x-small fw-bold text-muted" for="auto_generate_sku">AUTO</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label for="short_description_en" class="form-label small fw-bold text-muted text-uppercase">Short Description</label>
                                        <input type="text" id="short_description_en" name="short_description_en" class="form-control border-light-subtle shadow-none" placeholder="Brief summary for list view" value="<?php echo htmlspecialchars($_POST['short_description_en'] ?? ''); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label for="description_en" class="form-label small fw-bold text-muted text-uppercase">Full Description</label>
                                        <textarea id="description_en" name="description_en" class="summernote-editor"><?php echo str_replace('src="uploads/', 'src="../uploads/', $_POST['description_en'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Media Card (Upload Only) -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3 border-0">
                                <h5 class="mb-0 fw-bold px-2"><i class="fas fa-images me-2 text-primary"></i> Product Media</h5>
                            </div>
                            <div class="card-body p-4">
                                <?php include __DIR__ . '/includes/product-media-upload.php'; ?>
                            </div>
                        </div>
                
                        <?php include __DIR__ . '/includes/product-options.php'; ?>
                    </div>

                    <!-- Right Column: Sidebar -->
                    <div class="col-lg-4">
                        <!-- Category/Brand Info -->
                        <div class="card border-0 shadow-sm mb-4 p-4">
                            <h6 class="fw-bold text-muted text-uppercase mb-4">Organization</h6>
                            <div class="mb-4">
                                <label for="category_id" class="form-label small fw-bold text-muted">Category *</label>
                                <select id="category_id" name="category_id" class="form-select border-light-subtle shadow-none py-2" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" class="fw-bold" <?php echo (($_POST['category_id'] ?? 0) == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name_en']); ?>
                                        </option>
                                        <?php foreach ($category['subcategories'] as $subcategory): ?>
                                            <option value="<?php echo $subcategory['id']; ?>" <?php echo (($_POST['category_id'] ?? 0) == $subcategory['id']) ? 'selected' : ''; ?>>&nbsp;&nbsp;— <?php echo htmlspecialchars($subcategory['name_en']); ?></option>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="brand_id" class="form-label small fw-bold text-muted">Brand</label>
                                <select id="brand_id" name="brand_id" class="form-select border-light-subtle shadow-none py-2">
                                    <option value="">-- No Brand --</option>
                                    <?php foreach ($brands as $brand): ?>
                                        <option value="<?php echo $brand['id']; ?>" <?php echo (($_POST['brand_id'] ?? 0) == $brand['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($brand['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Pricing & Inventory -->
                        <div class="card border-0 shadow-sm mb-4 p-4">
                            <h6 class="fw-bold text-muted text-uppercase mb-4 text-warning"><i class="fas fa-coins me-1"></i> Pricing & Stock</h6>
                            <div class="mb-4">
                                <label for="price" class="form-label small fw-bold text-muted">Base Price (<?php echo htmlspecialchars(get_setting('currency_code', 'DZD')); ?>) *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light-subtle border-end-0 small fw-bold text-muted"><?php echo htmlspecialchars(get_setting('currency_code', 'DZD')); ?></span>
                                    <input type="number" id="price" name="price" class="form-control border-light-subtle shadow-none py-2 fw-bold text-danger" step="1" value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="discount_percentage" class="form-label small fw-bold text-muted">Discount Percentage (%)</label>
                                <div class="input-group">
                                    <input type="number" id="discount_percentage" name="discount_percentage" class="form-control border-light-subtle shadow-none py-2 fw-bold" min="0" max="100" step="0.01" value="<?php echo htmlspecialchars($_POST['discount_percentage'] ?? '0'); ?>">
                                    <span class="input-group-text bg-light border-light-subtle border-start-0 small fw-bold text-muted">%</span>
                                </div>
                            </div>
                            <div>
                                <label for="stock_quantity" class="form-label small fw-bold text-muted">Stock Quantity *</label>
                                <input type="number" id="stock_quantity" name="stock_quantity" min="0" class="form-control border-light-subtle shadow-none py-2 fw-bold" value="<?php echo htmlspecialchars($_POST['stock_quantity'] ?? '0'); ?>" required>
                                <p class="small text-muted mt-1">If variants are added, total stock is calculated from their quantities.</p>
                            </div>
                        </div>

                        <!-- Logistics -->
                        <div class="card border-0 shadow-sm mb-4 p-4">
                            <h6 class="fw-bold text-muted text-uppercase mb-4"><i class="fas fa-ruler-combined me-1"></i> Logistics</h6>
                            <div class="mb-3">
                                <label for="weight" class="form-label small fw-bold text-muted">Weight (kg)</label>
                                <div class="input-group">
                                    <input type="number" id="weight" name="weight" class="form-control border-light-subtle shadow-none py-2" step="0.01" value="<?php echo htmlspecialchars($_POST['weight'] ?? ''); ?>">
                                    <span class="input-group-text bg-light border-light-subtle border-start-0 small fw-bold text-muted">kg</span>
                                </div>
                            </div>
                            <div>
                                <label for="dimensions" class="form-label small fw-bold text-muted">Dimensions (LxWxH)</label>
                                <input type="text" id="dimensions" name="dimensions" class="form-control border-light-subtle shadow-none py-2" placeholder="e.g., 20x10x5 cm" value="<?php echo htmlspecialchars($_POST['dimensions'] ?? ''); ?>">
                            </div>
                        </div>

                        <!-- Visibility & Status -->
                        <div class="card border-0 shadow-sm mb-4 p-4">
                            <h6 class="fw-bold text-muted text-uppercase mb-4">Visibility & Status</h6>
                            
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <span class="small fw-bold text-muted">Active On Site</span>
                                <div class="form-check form-switch px-0 ms-auto">
                                    <input class="form-check-input" type="checkbox" name="is_active" <?php echo !isset($_POST['name_en']) || isset($_POST['is_active']) ? 'checked' : ''; ?>>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <span class="small fw-bold text-muted">Featured Product</span>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_featured" <?php echo isset($_POST['is_featured']) ? 'checked' : ''; ?>>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <span class="small fw-bold text-muted">New Arrival</span>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_new_arrival" <?php echo isset($_POST['is_new_arrival']) ? 'checked' : ''; ?>>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between">
                                <span class="small fw-bold text-muted">Best Seller</span>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_best_seller" <?php echo isset($_POST['is_best_seller']) ? 'checked' : ''; ?>>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- Shared sticky form actions -->
                <div class="admin-form-actions">
                    <div class="container-fluid d-flex justify-content-end align-items-center gap-3">
                        <a href="products.php" class="btn btn-light btn-lg rounded-pill px-4 fw-bold text-muted border border-light-subtle">Cancel</a>
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow-sm">
                            <i class="fas fa-save me-2"></i> Save Product
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <script>


        // Add Technical Specification
</script><script src="../assets/js/product-form.js"></script>

    <!-- Include the shared footer template -->
    <?php include 'footer.php'; ?>
