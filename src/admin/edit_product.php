<?php
// admin/edit_product.php
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

$productModel = new Product();
$categoryModel = new Category();
$brandModel = new Brand();
$skuGenerator = new SKUGenerator();
$mediaManager = new MediaManager();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';

// Check for session message
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['message_type'] ?? 'success';
    unset($_SESSION['message'], $_SESSION['message_type']);
}

// Get product ID
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$productId) {
    header('Location: products.php');
    exit;
}

// Fetch existing product data (including inactive products)
$product = $productModel->getByIdIncludeInactive($productId);

if (!$product) {
    header('Location: products.php');
    exit;
}

// Get existing variants and images
$existingVariants = $productModel->getProductVariants($productId);
$existingImages = $productModel->getProductImages($productId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for potential POST max size limit issue
    if (empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0) {
        $message = 'Error: File upload size exceeded the server limit (post_max_size). Please upload fewer or smaller images.';
        $messageType = 'error';
    } else {
        $action = $_POST['form_action'] ?? 'update_product';
        
        // SKU and Folder setup (shared across actions)
        $sku = sanitize_input($_POST['sku'] ?? $product['sku']);
        $productId = (int)$_GET['id'];

        if ($action === 'upload_media') {
            // Handle Media Uploads (both images and videos) using MediaManager
            if (isset($_FILES['product_media']) && !empty($_FILES['product_media']['name'][0])) {
                try {
                    $slug = create_slug($product['name_en']);
                    $uploadResult = $mediaManager->uploadMedia($productId, $_FILES['product_media'], false, $slug);
                    
                    $msg = '';
                    if (!empty($uploadResult['success']['images']) || !empty($uploadResult['success']['videos'])) {
                        $count = count($uploadResult['success']['images']) + count($uploadResult['success']['videos']);
                        $msg = $count . ' media file(s) uploaded successfully';
                        $_SESSION['message_type'] = 'success';
                    }
                    
                    if (!empty($uploadResult['errors'])) {
                        $msg .= (!empty($msg) ? '. ' : '') . 'Errors: ' . implode(', ', $uploadResult['errors']);
                        $_SESSION['message_type'] = 'error';
                    }
                    
                    $_SESSION['message'] = $msg;
                    header("Location: edit_product.php?id=" . $productId);
                    exit;
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'error';
                }
            } else {
                $message = 'Please select media files to upload';
                $messageType = 'error';
            }
        } elseif ($action === 'delete_video') {
            // Handle Video Deletion
            try {
                $mediaManager->deleteVideo($productId);
                $_SESSION['message'] = 'Video deleted successfully';
                $_SESSION['message_type'] = 'success';
                header("Location: edit_product.php?id=" . $productId);
                exit;
            } catch (Exception $e) {
                $message = 'Error: ' . $e->getMessage();
                $messageType = 'error';
            }
        } else {
            // DEFAULT: Update Product (Full form)
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
                'sku' => $sku,
                'weight' => (float)($_POST['weight'] ?? 0),
                'dimensions' => sanitize_input($_POST['dimensions'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                'is_new_arrival' => isset($_POST['is_new_arrival']) ? 1 : 0,
                'is_best_seller' => isset($_POST['is_best_seller']) ? 1 : 0,
                'technical_specs_en' => [],
                'video_url' => $product['video_url'] ?? null
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

            if (empty($productData['name_en']) || empty($productData['category_id']) || $productData['price'] <= 0) {
                $message = 'Please fill all required fields';
                $messageType = 'error';
            } else {
                try {
                    $productData['variants'] = CatalogRules::variantsFromForm($_POST['variants'] ?? [], $productData['price']);
                    if (isset($_POST['auto_generate_sku'])) $productData['sku'] = '';
                    if ($productModel->update($productId, $productData)) {
                        $message = 'Product updated successfully';
                        $messageType = 'success';
                    } else {
                        $message = 'Failed to update product';
                        $messageType = 'error';
                    }
                } catch (Exception $e) {
                    $message = 'Error: ' . $e->getMessage();
                    $messageType = 'error';
                }
            }
        }
        
        // Refresh product data
        $product = $productModel->getByIdIncludeInactive($productId);
        $existingVariants = $productModel->getProductVariants($productId);
        $existingImages = $productModel->getProductImages($productId);
    }
}

// Get categories and brands for select options
$categories = $categoryModel->getWithSubcategories();
$brands = $brandModel->getAll();

// Set page title and heading variables for the template
$page_title = 'Edit Product';
$page_heading = 'Edit Product';

// Include the shared header template
include 'header.php';

?>
    <!-- jQuery is required for Summernote -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Summernote Lite -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo ($messageType === 'error' || $messageType === 'danger') ? 'danger' : 'success'; ?> alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                    <i class="fas fa-<?php echo ($messageType === 'error' || $messageType === 'danger') ? 'exclamation-circle' : 'check-circle'; ?> me-2"></i>
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="edit_product.php?id=<?php echo $productId; ?>" enctype="multipart/form-data" id="productForm" data-product-id="<?php echo (int)($productId ?? 0); ?>">
                <input type="hidden" name="form_action" id="form_action" value="update_product">
                
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h4 class="fw-bold mb-0">Edit Product</h4>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item small"><a href="dashboard.php" class="text-decoration-none text-muted">Dashboard</a></li>
                                <li class="breadcrumb-item small"><a href="products.php" class="text-decoration-none text-muted">Products</a></li>
                                <li class="breadcrumb-item small active fw-bold text-danger" aria-current="page">#<?php echo $productId; ?></li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="product_details.php?id=<?php echo $productId; ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold shadow-xs">
                            <i class="fas fa-eye me-1"></i> View Product
                        </a>
                        <a href="products.php" class="btn btn-light btn-sm text-secondary rounded-pill px-3 fw-bold border shadow-xs">
                            <i class="fas fa-arrow-left me-1"></i> Back to Products
                        </a>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Left Column: Main Information -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white py-3 border-0">
                                <h5 class="mb-0 fw-bold px-2"><i class="fas fa-info-circle me-2 text-danger"></i> Product Information</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name_en" class="form-label small fw-bold text-muted text-uppercase">Product Name *</label>
                                        <input type="text" id="name_en" name="name_en" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars($product['name_en'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="sku" class="form-label small fw-bold text-muted text-uppercase">SKU / Item code</label>
                                        <div class="input-group">
                                            <input type="text" id="sku" name="sku" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars($product['sku'] ?? ''); ?>" placeholder="Enter SKU">
                                            <div class="input-group-text bg-light border-light-subtle">
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input" type="checkbox" name="auto_generate_sku" id="auto_generate_sku" onchange="toggleSKUField()">
                                                    <label class="form-check-label x-small fw-bold text-muted" for="auto_generate_sku">AUTO</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label for="short_description_en" class="form-label small fw-bold text-muted text-uppercase">Short Description</label>
                                        <input type="text" id="short_description_en" name="short_description_en" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars($product['short_description_en'] ?? ''); ?>">
                                    </div>

                                                                        <div class="col-12">

                                                                            <label for="description_en" class="form-label small fw-bold text-muted text-uppercase">Full Description</label>

                                                                            <textarea id="description_en" name="description_en" class="summernote-editor"><?php echo str_replace('src="uploads/', 'src="../uploads/', $product['description_en'] ?? ''); ?></textarea>

                                                                        </div>

                                                                    </div>

                                                                </div>

                                                            </div>

                                    

                                                            <!-- Media Card -->

                                                            <div class="card border-0 shadow-sm mb-4">

                                                                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-0">

                                                                    <h5 class="mb-0 fw-bold px-2"><i class="fas fa-images me-2 text-primary"></i> Product Media</h5>

                                                                </div>

                                                                <div class="card-body p-4">

                                                                    <?php if (!empty($existingImages) || !empty($product['video_url'])): ?>

                                                                        <h6 class="small fw-bold text-muted text-uppercase mb-3 px-2">Current Media</h6>

                                                                                                                <div class="row g-3 px-2 mb-4" id="image-gallery">

                                                                                                                    <?php foreach ($existingImages as $img): ?>

                                                                                                                        <div class="col-6 col-sm-4 col-md-3 col-xl-2 image-item" data-image-id="<?php echo $img['id']; ?>">

                                                                                                                            <div class="card border-light-subtle shadow-xs overflow-hidden position-relative group">

                                                                                                                                <div class="ratio ratio-1x1">

                                                                                                                                    <img src="../<?php echo htmlspecialchars($img['image_url'] ?? ''); ?>" class="card-img-top object-fit-cover" alt="Product">

                                                                                                                                </div>

                                                                                                                                

                                                                                                                                <div class="position-absolute top-0 start-0 m-1">

                                                                                                                                    <?php if ($img['is_primary']): ?>

                                                                                                                                        <span class="badge bg-danger rounded-pill shadow-sm x-small" >PRIMARY</span>

                                                                                                                                    <?php else: ?>

                                                                                                                                        <button type="button" class="btn btn-primary btn-xs rounded-pill shadow-sm fw-bold border-0" style=" padding: 2px 8px;" onclick="setPrimaryImage(<?php echo $img['id']; ?>)">SET</button>

                                                                                                                                    <?php endif; ?>

                                                                                                                                </div>

                                                                        

                                                                                                                                                                                                                                                <div class="position-absolute bottom-0 end-0 m-2">

                                                                        

                                                                                                                                                                                                                                                    <button type="button" class="btn btn-white btn-sm rounded-circle shadow-sm border-0 d-flex align-items-center justify-content-center" 

                                                                        

                                                                                                                                                                                                                                                            style="width: 28px; height: 28px; background: var(--admin-surface); color: var(--bs-danger);"

                                                                        

                                                                                                                                                                                                                                                            onclick="deleteImage(<?php echo $img['id']; ?>)" title="Delete Image">

                                                                        

                                                                                                                                                                                                                                                        <i class="fas fa-trash-alt" style="font-size: 0.8rem;"></i>

                                                                        

                                                                                                                                                                                                                                                    </button>

                                                                        

                                                                                                                                                                                                                                                </div>

                                                                        

                                                                                                                                                                                        

                                                                        

                                                                                                                                                                                                                                                <div class="drag-handle position-absolute top-0 end-0 m-2 cursor-move bg-dark bg-opacity-50 text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 24px; height: 24px; ">

                                                                        

                                                                                                                                                                                                                                                    <i class="fas fa-grip-vertical"></i>

                                                                        

                                                                                                                                                                                                                                                </div>

                                                                        

                                                                                                                                                                                                                                            </div>

                                                                        

                                                                                                                                                                                                                                        </div>

                                                                        

                                                                                                                                                                                                                                    <?php endforeach; ?>

                                                                        

                                                                                                                                                                                        

                                                                        

                                                                                                                                                                                                                                    <?php if (!empty($product['video_url'])): ?>

                                                                        

                                                                                                                                                                                                                                        <div class="col-6 col-sm-4 col-md-3 col-xl-2 video-item">

                                                                        

                                                                                                                                                                                                                                            <div class="card h-100 border-light-subtle shadow-xs overflow-hidden position-relative">

                                                                        

                                                                                                                                                                                                                                                <div class="ratio ratio-1x1">

                                                                        

                                                                                                                                                                                                                                                    <video class="card-img-top object-fit-cover" muted>

                                                                        

                                                                                                                                                                                                                                                        <source src="../<?php echo htmlspecialchars($product['video_url']); ?>" type="video/mp4">

                                                                        

                                                                                                                                                                                                                                                    </video>

                                                                        

                                                                                                                                                                                                                                                </div>

                                                                        

                                                                                                                                                                                                                                                <div class="position-absolute top-0 start-0 m-2">

                                                                        

                                                                                                                                                                                                                                                    <span class="badge bg-primary rounded-pill shadow-sm x-small" >VIDEO</span>

                                                                        

                                                                                                                                                                                                                                                </div>

                                                                        

                                                                                                                                                                                                                                                <div class="position-absolute bottom-0 end-0 m-2">

                                                                        

                                                                                                                                                                                                                                                    <button type="submit" data-confirm="Delete this video? This cannot be undone." onclick="document.getElementById('form_action').value='delete_video'"

                                                                        

                                                                                                                                                                                                                                                            class="btn btn-white btn-sm rounded-circle shadow-sm border-0 d-flex align-items-center justify-content-center" 

                                                                        

                                                                                                                                                                                                                                                            style="width: 28px; height: 28px; background: var(--admin-surface); color: var(--bs-danger);" title="Delete Video">

                                                                        

                                                                                                                                                                                                                                                        <i class="fas fa-trash-alt" style="font-size: 0.8rem;"></i>

                                                                        

                                                                                                                                                                                                                                                    </button>

                                                                        

                                                                                                                                                                                                                                                </div>

                                                                        

                                                                                                                                                                                                                                            </div>

                                                                        

                                                                                                                                                                                                                                        </div>

                                                                        

                                                                                                                                                                                                                                    <?php endif; ?>

                                                                                                                </div>


                                                                    <?php endif; ?>

                                    

                                                                    <!-- Media Upload -->

                                                                    <?php include __DIR__ . '/includes/product-media-upload.php'; ?>

                                                                </div>

                                                            </div>

                                    

                                                            <?php include __DIR__ . '/includes/product-options.php'; ?>
                    </div>

                    <!-- Right Column: Sidebar -->
                    <div class="col-lg-4">
                        <!-- Category Info -->
                        <div class="card border-0 shadow-sm mb-4 p-4">
                            <h6 class="fw-bold text-muted text-uppercase mb-4">Organization</h6>
                            <div class="mb-4">
                                <label for="category_id" class="form-label small fw-bold text-muted">Category *</label>
                                <select id="category_id" name="category_id" class="form-select border-light-subtle shadow-none py-2" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" class="fw-bold" <?php echo ($product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name_en']); ?>
                                        </option>
                                        <?php foreach ($category['subcategories'] as $subcategory): ?>
                                            <option value="<?php echo $subcategory['id']; ?>" <?php echo ($product['category_id'] == $subcategory['id']) ? 'selected' : ''; ?>>&nbsp;&nbsp;— <?php echo htmlspecialchars($subcategory['name_en']); ?></option>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="brand_id" class="form-label small fw-bold text-muted">Brand</label>
                                <select id="brand_id" name="brand_id" class="form-select border-light-subtle shadow-none py-2">
                                    <option value="">-- No Brand --</option>
                                    <?php foreach ($brands as $brand): ?>
                                        <option value="<?php echo $brand['id']; ?>" <?php echo ($product['brand_id'] == $brand['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($brand['name'] ?? ''); ?>
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
                                    <input type="number" id="price" name="price" class="form-control border-light-subtle shadow-none py-2 fw-bold text-danger" step="1" value="<?php echo round($product['price']); ?>" required>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="discount_percentage" class="form-label small fw-bold text-muted">Discount Percentage (%)</label>
                                <div class="input-group">
                                    <input type="number" id="discount_percentage" name="discount_percentage" class="form-control border-light-subtle shadow-none py-2 fw-bold" min="0" max="100" step="0.01" value="<?php echo $product['discount_percentage']; ?>">
                                    <span class="input-group-text bg-light border-light-subtle border-start-0 small fw-bold text-muted">%</span>
                                </div>
                            </div>
                            <div>
                                <?php $stockFromVariants = CatalogRules::hasVariants($pdo, $productId); ?>
                                <label for="stock_quantity" class="form-label small fw-bold text-muted"><?php echo $stockFromVariants ? 'Total Stock (active variants)' : 'Stock Quantity *'; ?></label>
                                <input type="number" id="stock_quantity" name="stock_quantity" min="0" class="form-control border-light-subtle shadow-none py-2 fw-bold" value="<?php echo (int)$product['stock_quantity']; ?>" <?php echo $stockFromVariants ? 'readonly aria-describedby="stock-help"' : ''; ?> required>
                                <p class="small text-muted mt-1" id="stock-help"><?php echo $stockFromVariants ? 'Update the stock for each variant below. This total is calculated automatically.' : 'Set the stock available for this product.'; ?></p>
                            </div>
                        </div>

                                                <!-- Logistics -->

                                                <div class="card border-0 shadow-sm mb-4 p-4">

                                                    <h6 class="fw-bold text-muted text-uppercase mb-4"><i class="fas fa-ruler-combined me-1"></i> Logistics</h6>

                                                    <div class="mb-3">

                                                        <label for="weight" class="form-label small fw-bold text-muted">Weight (kg)</label>

                                                        <div class="input-group">

                                                            <input type="number" id="weight" name="weight" class="form-control border-light-subtle shadow-none py-2" step="0.01" value="<?php echo $product['weight'] ?? 0; ?>">

                                                            <span class="input-group-text bg-light border-light-subtle border-start-0 small fw-bold text-muted">kg</span>

                                                        </div>

                                                    </div>

                                                    <div>

                                                        <label for="dimensions" class="form-label small fw-bold text-muted">Dimensions (LxWxH)</label>

                                                        <input type="text" id="dimensions" name="dimensions" class="form-control border-light-subtle shadow-none py-2" placeholder="e.g., 20x10x5 cm" value="<?php echo htmlspecialchars($product['dimensions'] ?? ''); ?>">

                                                    </div>

                                                </div>

                        

                                                <!-- Visibility Card -->
                        <div class="card border-0 shadow-sm mb-4 p-4">
                            <h6 class="fw-bold text-muted text-uppercase mb-4">Visibility & Status</h6>
                            
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <span class="small fw-bold text-muted">Active On Site</span>
                                <div class="form-check form-switch px-0 ms-auto">
                                    <input class="form-check-input" type="checkbox" name="is_active" <?php echo $product['is_active'] ? 'checked' : ''; ?>>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <span class="small fw-bold text-muted">Featured Product</span>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_featured" <?php echo $product['is_featured'] ? 'checked' : ''; ?>>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <span class="small fw-bold text-muted">New Arrival</span>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_new_arrival" <?php echo $product['is_new_arrival'] ? 'checked' : ''; ?>>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between">
                                <span class="small fw-bold text-muted">Best Seller</span>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_best_seller" <?php echo $product['is_best_seller'] ? 'checked' : ''; ?>>
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
                            <i class="fas fa-save me-2"></i> Update Product
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>


        // Media Management Functions
        async function deleteImage(imageId) {
            if (!await AdminUI.confirm('Are you sure you want to delete this image?')) {
                return;
            }
            
            fetch('ajax/media_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=delete_image&image_id=' + imageId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const imageItem = document.querySelector(`[data-image-id="${imageId}"]`);
                    if (imageItem) imageItem.remove();
                    location.reload(); 
                } else {
                    showToast('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Media update failed. Please retry.', 'error');
            });
        }
        
        function setPrimaryImage(imageId) {
            fetch('ajax/media_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=set_primary&image_id=' + imageId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); 
                } else {
                    showToast('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Media update failed. Please retry.', 'error');
            });
        }
        
        // Drag and Drop (Simplified BS5 style)
        let draggedElement = null;
        
        document.addEventListener('DOMContentLoaded', function() {
            const gallery = document.getElementById('image-gallery');
            if (gallery) {
                const items = gallery.querySelectorAll('.image-item');
                
                items.forEach(item => {
                    item.setAttribute('draggable', true);
                    
                    item.addEventListener('dragstart', function(e) {
                        draggedElement = this;
                        this.style.opacity = '0.5';
                    });
                    
                    item.addEventListener('dragend', function(e) {
                        this.style.opacity = '1';
                    });
                    
                    item.addEventListener('dragover', function(e) {
                        e.preventDefault();
                        return false;
                    });
                    
                    item.addEventListener('drop', function(e) {
                        e.preventDefault();
                        if (draggedElement && draggedElement !== this) {
                            const allItems = Array.from(gallery.querySelectorAll('.image-item'));
                            const draggedIndex = allItems.indexOf(draggedElement);
                            const targetIndex = allItems.indexOf(this);
                            
                            if (draggedIndex < targetIndex) {
                                this.parentNode.insertBefore(draggedElement, this.nextSibling);
                            } else {
                                this.parentNode.insertBefore(draggedElement, this);
                            }
                            saveImageOrder();
                        }
                        return false;
                    });
                });
            }
        });
        
        function saveImageOrder() {
            const gallery = document.getElementById('image-gallery');
            const items = gallery.querySelectorAll('.image-item');
            const imageIds = Array.from(items).map(item => item.getAttribute('data-image-id'));
            
            fetch('ajax/media_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=reorder_images&image_ids=' + JSON.stringify(imageIds)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Image order saved.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Media update failed. Please retry.', 'error');
            });
        }

        // Technical Specs
</script>
<script src="../assets/js/product-form.js"></script>

    <!-- Include the shared footer template -->
    <?php include 'footer.php'; ?>
