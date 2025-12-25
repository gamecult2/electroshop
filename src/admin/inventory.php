<?php
// admin/inventory.php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';
require_once '../models/Product.php';
require_once '../models/Category.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$productModel = new Product();
$categoryModel = new Category();

$message = '';
$messageType = '';
$low_stock_threshold = 10;

// Handle Stock Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $productId = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];

    if ($productModel->updateStock($productId, $quantity)) {
        $message = 'Stock updated successfully.';
        $messageType = 'success';
    } else {
        $message = 'Failed to update stock.';
        $messageType = 'error';
    }
}

// Pagination & Filtering
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$stock_filter = $_GET['stock_filter'] ?? 'all';
$category_filter = $_GET['category_id'] ?? '';

$filters = [
    'search' => $search,
    'include_inactive' => true
];

if ($stock_filter === 'low_stock') {
    $filters['max_stock'] = $low_stock_threshold;
} elseif ($stock_filter === 'out_of_stock') {
    $filters['max_stock'] = 0;
}

if ($category_filter) {
    $filters['category_id'] = $category_filter;
}

// Get Data
$total_products = $productModel->getProductsCount($filters);
$total_pages = ceil($total_products / $limit);
$products = $productModel->getAll($limit, $offset, $filters);
$categories = $categoryModel->getAllFlat();

// Set page title and heading variables for the template
$page_title = 'Inventory Management';
$page_heading = 'Inventory Management';

// Include the shared header template
include 'header.php';

?>
            <?php if ($message): ?>
                <div class="alert alert-<?php echo ($messageType === 'success') ? 'success' : 'danger'; ?> alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas <?php echo ($messageType === 'success') ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
                        <div><?php echo htmlspecialchars($message); ?></div>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Filter Bar -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-3">
                    <form method="GET" class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-light-subtle text-muted px-3"><i class="fas fa-search"></i></span>
                                <input type="text" name="search" class="form-control border-light-subtle shadow-none py-2" placeholder="Search product name or SKU..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="category_id" class="form-select form-select-sm border-light-subtle shadow-none py-2">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name_en']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="stock_filter" class="form-select form-select-sm border-light-subtle shadow-none py-2">
                                <option value="all" <?php echo $stock_filter === 'all' ? 'selected' : ''; ?>>All Stock</option>
                                <option value="low_stock" <?php echo $stock_filter === 'low_stock' ? 'selected' : ''; ?>>Low Stock (<= <?php echo $low_stock_threshold; ?>)</option>
                                <option value="out_of_stock" <?php echo $stock_filter === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock (0)</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill fw-bold rounded-pill shadow-sm py-2">Filter</button>
                            <?php if ($search || $stock_filter !== 'all' || $category_filter): ?>
                                <a href="inventory.php" class="btn btn-light btn-sm flex-fill fw-bold rounded-pill border border-light-subtle py-2 text-muted">Clear</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-0">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-boxes me-2 text-primary"></i> Inventory Levels</h5>
                    <span class="badge bg-light text-muted fw-bold rounded-pill px-3 py-2 small border border-light-subtle shadow-xs">
                        Showing <?php echo count($products); ?> of <?php echo $total_products; ?>
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase" style="width: 80px;">ID</th>
                                    <th class="border-0 py-3 small fw-bold text-muted text-uppercase">Product</th>
                                    <th class="border-0 py-3 small fw-bold text-muted text-uppercase">Category</th>
                                    <th class="border-0 py-3 small fw-bold text-muted text-uppercase">SKU</th>
                                    <th class="border-0 py-3 small fw-bold text-muted text-uppercase text-center">Stock</th>
                                    <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase text-end" style="width: 200px;">Update Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-info-circle me-1"></i> No products found using current filters.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($products as $product): 
                                        $isLowStock = $product['stock_quantity'] <= $low_stock_threshold;
                                        $isOutOfStock = $product['stock_quantity'] <= 0;
                                        $rowClass = '';
                                        $badgeClass = 'bg-success-subtle text-success';
                                        
                                        if ($isOutOfStock) {
                                            $badgeClass = 'bg-danger text-white shadow-sm';
                                            $rowClass = 'table-danger-subtle';
                                        } elseif ($isLowStock) {
                                            $badgeClass = 'bg-warning-subtle text-warning-emphasis';
                                            $rowClass = 'table-warning-subtle';
                                        }
                                    ?>
                                        <tr class="<?php echo $rowClass; ?>">
                                            <td class="px-4"><span class="text-muted fw-bold small">#<?php echo htmlspecialchars($product['id']); ?></span></td>
                                            <td><div class="fw-bold text-dark small"><?php echo htmlspecialchars($product['name_en']); ?></div></td>
                                            <td><span class="badge bg-light text-muted border border-light-subtle rounded-pill px-2 py-1 x-small fw-bold"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></span></td>
                                            <td><code class="x-small text-primary fw-bold"><?php echo htmlspecialchars($product['sku']); ?></code></td>
                                            <td class="text-center">
                                                <span class="badge <?php echo $badgeClass; ?> rounded-pill px-3 py-1 fw-bold">
                                                    <?php echo htmlspecialchars($product['stock_quantity']); ?>
                                                </span>
                                            </td>
                                            <td class="px-4 text-end">
                                                <form method="POST" action="inventory.php?page=<?php echo $page; ?>&search=<?php echo urlencode($search); ?>&stock_filter=<?php echo urlencode($stock_filter); ?>&category_id=<?php echo urlencode($category_filter); ?>" class="d-flex justify-content-end align-items-center">
                                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                    <div class="input-group input-group-sm rounded-pill overflow-hidden border border-light-subtle shadow-xs" style="max-width: 140px;">
                                                        <input type="number" name="quantity" class="form-control border-0 shadow-none text-center fw-bold py-1" value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" min="0">
                                                        <button type="submit" name="update_stock" class="btn btn-primary border-0 px-3 py-1" title="Save">
                                                            <i class="fas fa-check small"></i>
                                                        </button>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="card-footer bg-white py-3 border-0">
                            <nav aria-label="Inventory navigation">
                                <ul class="pagination pagination-sm justify-content-center mb-0">
                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link border-light-subtle rounded-start-pill px-3 shadow-none" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&stock_filter=<?php echo urlencode($stock_filter); ?>&category_id=<?php echo urlencode($category_filter); ?>">
                                            <i class="fas fa-chevron-left x-small"></i>
                                        </a>
                                    </li>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link border-light-subtle shadow-none <?php echo $i === $page ? 'bg-primary border-primary' : 'text-muted'; ?>" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&stock_filter=<?php echo urlencode($stock_filter); ?>&category_id=<?php echo urlencode($category_filter); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link border-light-subtle rounded-end-pill px-3 shadow-none" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&stock_filter=<?php echo urlencode($stock_filter); ?>&category_id=<?php echo urlencode($category_filter); ?>">
                                            <i class="fas fa-chevron-right x-small"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Include the shared footer template -->
    <?php include 'footer.php'; ?>


