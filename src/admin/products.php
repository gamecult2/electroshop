<?php
// admin/products.php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';
require_once '../models/Product.php';
require_once '../models/Category.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$productModel = new Product();
$categoryModel = new Category();

// Handle Delete Action
if ((isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) || (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id']))) {
    $productId = isset($_POST['id']) ? (int)$_POST['id'] : (int)$_GET['id'];
    try {
        if ($productModel->delete($productId)) {
            $_SESSION['message'] = 'Product deleted successfully.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Failed to delete product.';
            $_SESSION['message_type'] = 'error';
        }
    } catch (Exception $e) {
        $_SESSION['message'] = 'Error: ' . $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }
    // Redirect to avoid resubmission and clean URL
    header('Location: products.php');
    exit;
}

// Set page title and heading variables for the template
$page_title = 'Manage Products';
$page_heading = 'Manage Products';

// Include the shared header template
include 'header.php';

$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['message_type'] ?? '';
unset($_SESSION['message']);
unset($_SESSION['message_type']);


// --- Filtering & Pagination ---

// 1. Get Filters
$categoryId = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int)$_GET['category_id'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$filters = [
    'include_inactive' => true
];
if ($categoryId) {
    $filters['category_id'] = $categoryId;

    // Get the selected category to determine if it's a main category or subcategory
    $selectedCategory = $categoryModel->getById($categoryId);

    // This is a main category if parent_id is NULL, so include subcategories
    if ($selectedCategory && $selectedCategory['parent_id'] === null) {
        $filters['include_subcategories'] = true;
    }
    // For subcategories, filter only by exact category_id (no include_subcategories)
}
if ($search) {
    $filters['search'] = $search;
}

// 2. Pagination Configuration
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 20; // Items per page
$offset = ($page - 1) * $limit;

// 3. Fetch Data
$products = $productModel->getAll($limit, $offset, $filters);
$totalProducts = $productModel->getProductsCount($filters);
$totalPages = ceil($totalProducts / $limit);

// 4. Get categories for filter dropdown
$categories = $categoryModel->getWithSubcategories();

?>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                    <i class="fas fa-<?php echo $messageType === 'error' ? 'exclamation-circle' : 'check-circle'; ?> me-2"></i>
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="card border-0 shadow-sm p-3 mb-4">
                <form class="row g-3 align-items-center" method="GET" action="products.php">
                    <div class="col-auto">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-light-subtle text-muted"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control border-light-subtle shadow-none" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>" style="width: 200px;">
                        </div>
                    </div>
                    
                    <div class="col-auto">
                        <select name="category_id" class="form-select form-select-sm border-light-subtle shadow-none" style="width: 180px;">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <optgroup label="<?php echo htmlspecialchars($cat['name_en']); ?>">
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $categoryId == $cat['id'] ? 'selected' : ''; ?>>
                                        ├── All <?php echo htmlspecialchars($cat['name_en']); ?>
                                    </option>
                                    <?php if (!empty($cat['subcategories'])): ?>
                                        <?php foreach ($cat['subcategories'] as $subcat): ?>
                                            <option value="<?php echo $subcat['id']; ?>" <?php echo $categoryId == $subcat['id'] ? 'selected' : ''; ?>>
                                                &nbsp;&nbsp;&nbsp;&nbsp;└── <?php echo htmlspecialchars($subcat['name_en']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-auto">
                        <button type="submit" class="btn btn-danger btn-sm px-4 rounded-pill fw-bold">Filter</button>
                        <?php if ($search || $categoryId): ?>
                            <a href="products.php" class="btn btn-outline-secondary btn-sm rounded-pill px-4 fw-bold">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
                    <h2 class="h5 fw-bold mb-0 text-dark"><i class="fas fa-box me-2 text-danger"></i> Product Listings <span class="badge bg-light text-muted border ms-2 small fw-normal"><?php echo $totalProducts; ?> Total</span></h2>
                    <a href="add_product.php" class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-sm d-inline-flex align-items-center gap-2"><i class="fas fa-plus"></i> Add New Product</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3 border-0">ID</th>
                                <th class="border-0">Media</th>
                                <th class="border-0">Product Details</th>
                                <th class="border-0">Price (<?php echo htmlspecialchars(get_setting('currency_code', 'DZD')); ?>)</th>
                                <th class="border-0">Stock</th>
                                <th class="border-0">Status & Features</th>
                                <th class="border-0 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted mb-2"><i class="fas fa-box-open fa-3x opacity-25"></i></div>
                                        <p class="mb-0">No products found matching your criteria.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                    <?php 
                                        $topImage = '../img/product-placeholder.jpg';
                                        $imgs = $productModel->getProductImages($product['id']);
                                        if(!empty($imgs)) {
                                            $topImage = $imgs[0]['image_url'];
                                            if (strpos($topImage, 'uploads/') === 0 || strpos($topImage, 'img/') === 0) {
                                                $topImage = '../' . $topImage;
                                            }
                                        }
                                    ?>
                                    <tr>
                                        <td class="px-3 fw-bold text-muted x-small">#<?php echo $product['id']; ?></td>
                                        <td>
                                            <div class="position-relative d-inline-block">
                                                <img src="<?php echo htmlspecialchars($topImage); ?>" alt="Product" class="rounded-3 border object-fit-cover shadow-xs" style="width: 48px; height: 48px;">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark text-truncate" style="max-width: 250px;"><?php echo htmlspecialchars($product['name_en']); ?></div>
                                            <div class="text-muted x-small"><?php echo htmlspecialchars($product['sku'] ?? 'NO SKU'); ?></div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm" style="width: 100px;">
                                                <input type="number" step="1" value="<?php echo round($product['price']); ?>" 
                                                    class="form-control border-light-subtle text-center fw-bold text-danger shadow-none" 
                                                    data-id="<?php echo $product['id']; ?>"
                                                    onchange="updateProduct(this, <?php echo $product['id']; ?>, 'price')">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm" style="width: 80px;">
                                                <input type="number" value="<?php echo $product['stock_quantity']; ?>" 
                                                    class="form-control border-light-subtle text-center fw-bold shadow-none <?php echo $product['stock_quantity'] <= 5 ? 'bg-danger-subtle text-danger' : 'text-dark'; ?>" 
                                                    data-id="<?php echo $product['id']; ?>"
                                                    onchange="updateProduct(this, <?php echo $product['id']; ?>, 'stock_quantity')">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1 flex-wrap align-items-center">
                                                <span class="badge rounded-pill px-3 py-2 fw-bold clickable <?php echo $product['is_active'] ? 'bg-success' : 'bg-secondary'; ?>" 
                                                      onclick="toggleStatus(this, <?php echo $product['id']; ?>, 'is_active')"
                                                      data-field="is_active"
                                                      data-current="<?php echo $product['is_active'] ? '1' : '0'; ?>"
                                                      style="cursor: pointer; min-width: 65px; font-size: 10px;">
                                                    <?php echo $product['is_active'] ? 'ACTIVE' : 'INACTIVE'; ?>
                                                </span>

                                                <span class="badge rounded-pill px-2 py-2 fw-bold clickable <?php echo $product['is_featured'] ? 'bg-warning text-dark' : 'bg-light text-muted border opacity-50'; ?>" 
                                                      onclick="toggleStatus(this, <?php echo $product['id']; ?>, 'is_featured')"
                                                      data-field="is_featured"
                                                      data-current="<?php echo $product['is_featured'] ? '1' : '0'; ?>"
                                                      style="cursor: pointer; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; font-size: 10px;" title="Featured">F</span>

                                                <span class="badge rounded-pill px-2 py-2 fw-bold clickable <?php echo $product['is_new_arrival'] ? 'bg-info text-white' : 'bg-light text-muted border opacity-50'; ?>" 
                                                      onclick="toggleStatus(this, <?php echo $product['id']; ?>, 'is_new_arrival')"
                                                      data-field="is_new_arrival"
                                                      data-current="<?php echo $product['is_new_arrival'] ? '1' : '0'; ?>"
                                                      style="cursor: pointer; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; font-size: 10px;" title="New Arrival">N</span>

                                                <span class="badge rounded-pill px-2 py-2 fw-bold clickable <?php echo $product['is_best_seller'] ? 'bg-danger text-white' : 'bg-light text-muted border opacity-50'; ?>" 
                                                      onclick="toggleStatus(this, <?php echo $product['id']; ?>, 'is_best_seller')"
                                                      data-field="is_best_seller"
                                                      data-current="<?php echo $product['is_best_seller'] ? '1' : '0'; ?>"
                                                      style="cursor: pointer; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; font-size: 10px;" title="Best Seller">B</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group shadow-sm rounded">
                                                <a href="../product.php?id=<?php echo $product['id']; ?>" target="_blank" class="btn btn-white btn-sm border-light-subtle text-info" title="View Product">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-white btn-sm border-light-subtle text-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button onclick="deleteProduct(<?php echo $product['id']; ?>)" class="btn btn-white btn-sm border-light-subtle text-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="card-footer bg-white py-3 border-0 border-top">
                        <nav>
                            <ul class="pagination pagination-sm justify-content-center mb-0">
                                <?php 
                                $queryParams = $_GET;
                                unset($queryParams['page']);
                                $queryString = http_build_query($queryParams);
                                $baseUrl = "products.php?" . ($queryString ? $queryString . '&' : '');
                                ?>

                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link shadow-none rounded-pill px-3 me-2" href="<?php echo $baseUrl; ?>page=<?php echo $page - 1; ?>">Prev</a>
                                </li>

                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <?php if ($i == $page): ?>
                                        <li class="page-item active"><span class="page-link bg-danger border-danger rounded-circle mx-1" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><?php echo $i; ?></span></li>
                                    <?php else: ?>
                                        <?php if ($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                                            <li class="page-item"><a class="page-link text-danger shadow-none border-0 bg-transparent rounded-circle mx-1" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;" href="<?php echo $baseUrl; ?>page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                                        <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                                            <li class="page-item disabled"><span class="page-link border-0 bg-transparent">...</span></li>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link shadow-none rounded-pill px-3 ms-2" href="<?php echo $baseUrl; ?>page=<?php echo $page + 1; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function updateProduct(element, id, field) {
            let value;
            if (element.type === 'checkbox') {
                value = element.checked ? 1 : 0;
            } else if (element.tagName === 'SPAN') {
                value = element.dataset.current === '1' ? 0 : 1;
            } else {
                value = element.value;
            }

            // Visual feedback - opacity
            element.style.opacity = '0.5';

            fetch('api/update_product_quick.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}&field=${field}&value=${value}`
            })
            .then(response => response.json())
            .then(data => {
                element.style.opacity = '1';
                
                if (data.success) {
                    showToast('Updated successfully', 'success');
                    
                    // Update badge style
                    if (element.tagName === 'SPAN') {
                        const newValue = value;
                        element.dataset.current = newValue;
                        
                        // Base classes
                        element.className = 'badge rounded-pill px-2 py-2 fw-bold clickable';
                        
                        if (field === 'is_active') {
                            element.className = 'badge rounded-pill px-3 py-2 fw-bold clickable ' + (newValue ? 'bg-success' : 'bg-secondary');
                            element.textContent = newValue ? 'ACTIVE' : 'INACTIVE';
                            element.style.minWidth = '65px';
                            element.style.fontSize = '10px';
                        } else {
                            element.style.width = '28px';
                            element.style.height = '28px';
                            element.style.display = 'inline-flex';
                            element.style.alignItems = 'center';
                            element.style.justifyContent = 'center';
                            element.style.fontSize = '10px';
                            
                            if (!newValue) {
                                element.classList.add('bg-light', 'text-muted', 'border', 'opacity-50');
                            } else {
                                const activeClass = field === 'is_featured' ? 'bg-warning text-dark' : field === 'is_new_arrival' ? 'bg-info text-white' : 'bg-danger text-white';
                                activeClass.split(' ').forEach(c => element.classList.add(c));
                            }
                        }
                    }
                } else {
                    showToast('Update failed: ' + (data.message || 'Unknown error'), 'danger');
                }
            })
            .catch(error => {
                element.style.opacity = '1';
                showToast('Error connecting to server', 'error');
                console.error('Error:', error);
            });
        }

        function toggleStatus(element, id, field) {
            updateProduct(element, id, field);
        }

        function deleteProduct(id) {
            if (confirm('Are you sure you want to delete this product?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'products.php';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function showToast(message, type = 'success') {
            if (typeof window.showToast === 'function') {
                window.showToast(message, type);
            } else {
                console.log('Toast:', message);
            }
        }
    </script>

    <!-- Include the shared footer template -->
    <?php include 'footer.php'; ?>


