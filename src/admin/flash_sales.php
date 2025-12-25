<?php
// admin/flash_sales.php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';
require_once '../models/Product.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$productModel = new Product();
$message = '';
$messageType = '';

// This feature requires a 'flash_sales' table
// CREATE TABLE flash_sales (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     product_id INT NOT NULL,
//     discount_percentage DECIMAL(5,2) NOT NULL,
//     start_date DATETIME NOT NULL,
//     end_date DATETIME NOT NULL,
//     is_active TINYINT(1) DEFAULT 1,
//     FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
// );

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_flash_sale'])) {
    $product_id = (int)$_POST['product_id'];
    $discount = (float)$_POST['discount_percentage'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    if ($product_id && $discount > 0 && !empty($start_date) && !empty($end_date)) {
        $stmt = $pdo->prepare("INSERT INTO flash_sales (product_id, discount_percentage, start_date, end_date) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$product_id, $discount, $start_date, $end_date])) {
            $message = 'Flash sale created successfully.';
            $messageType = 'success';
        } else {
            $message = 'Failed to create flash sale.';
            $messageType = 'error';
        }
    } else {
        $message = 'Please fill all fields correctly.';
        $messageType = 'error';
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $saleId = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM flash_sales WHERE id = ?");
    if ($stmt->execute([$saleId])) {
        $message = 'Flash sale deleted successfully.';
        $messageType = 'success';
    } else {
        $message = 'Failed to delete flash sale.';
        $messageType = 'error';
    }
}

$products = $productModel->getAll();

try {
    $stmt = $pdo->query("
        SELECT fs.*, p.name_en AS product_name, p.price AS original_price
        FROM flash_sales fs
        JOIN products p ON fs.product_id = p.id
        ORDER BY fs.start_date DESC
    ");
    $flash_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Check if the error is "table not found" (SQLSTATE 42S02)
    if ($e->getCode() === '42S02' || strpos($e->getMessage(), 'Unknown table') !== false) {
        // Create the table if it doesn't exist
        $createTableSql = "
            CREATE TABLE flash_sales (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                discount_percentage DECIMAL(5,2) NOT NULL,
                start_date DATETIME NOT NULL,
                end_date DATETIME NOT NULL,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            )
        ";

        try {
            $pdo->exec($createTableSql);

            // Now run the original query
            $stmt = $pdo->query("
                SELECT fs.*, p.name_en AS product_name, p.price AS original_price
                FROM flash_sales fs
                JOIN products p ON fs.product_id = p.id
                ORDER BY fs.start_date DESC
            ");
            $flash_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e2) {
            $flash_sales = [];
            $message = "Could not create table: " . $e2->getMessage();
            $messageType = 'error';
        }
    } else {
        // For other PDO errors, handle as needed
        $flash_sales = [];
        $message = "Database Error: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Set page title and heading variables for the template
$page_title = 'Manage Flash Sales';
$page_heading = 'Flash Sales';

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

            <div class="row g-4">
                <!-- Create Flash Sale Form -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3 border-0">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-plus-circle me-2 text-primary"></i> Create Flash Sale</h5>
                        </div>
                        <div class="card-body p-4 pt-0">
                            <form method="POST" action="flash_sales.php">
                                <div class="mb-3">
                                    <label for="product_id" class="form-label small fw-bold text-muted text-uppercase">Product</label>
                                    <select id="product_id" name="product_id" class="form-select border-light-subtle shadow-none py-2" required>
                                        <option value="">Select a product</option>
                                        <?php foreach ($products as $product): ?>
                                            <option value="<?php echo $product['id']; ?>"><?php echo htmlspecialchars($product['name_en']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="discount_percentage" class="form-label small fw-bold text-muted text-uppercase">Discount Percentage</label>
                                    <div class="input-group">
                                        <input type="number" id="discount_percentage" name="discount_percentage" class="form-control border-light-subtle shadow-none py-2" min="1" max="99" step="0.01" required>
                                        <span class="input-group-text bg-light border-light-subtle small fw-bold text-muted">%</span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="start_date" class="form-label small fw-bold text-muted text-uppercase">Start Date</label>
                                    <input type="datetime-local" id="start_date" name="start_date" class="form-control border-light-subtle shadow-none py-2" required>
                                </div>
                                <div class="mb-4">
                                    <label for="end_date" class="form-label small fw-bold text-muted text-uppercase">End Date</label>
                                    <input type="datetime-local" id="end_date" name="end_date" class="form-control border-light-subtle shadow-none py-2" required>
                                </div>
                                <button type="submit" name="add_flash_sale" class="btn btn-primary w-100 py-2 fw-bold rounded-pill shadow-sm">
                                    <i class="fas fa-bolt me-2"></i> Create Flash Sale
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Flash Sales List -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-0">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-list me-2 text-primary"></i> Current & Upcoming Flash Sales</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase">Product</th>
                                            <th class="border-0 py-3 small fw-bold text-muted text-uppercase">Prices</th>
                                            <th class="border-0 py-3 small fw-bold text-muted text-uppercase text-center">Discount</th>
                                            <th class="border-0 py-3 small fw-bold text-muted text-uppercase">Duration</th>
                                            <th class="border-0 py-3 small fw-bold text-muted text-uppercase text-center">Status</th>
                                            <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($flash_sales)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-5 text-muted">
                                                    <i class="fas fa-info-circle me-1"></i> No flash sales found.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($flash_sales as $sale):
                                                $now = new DateTime();
                                                $start = new DateTime($sale['start_date']);
                                                $end = new DateTime($sale['end_date']);
                                                $status = '';
                                                $badgeClass = '';
                                                if ($now < $start) {
                                                    $status = 'Upcoming';
                                                    $badgeClass = 'bg-info-subtle text-info';
                                                } elseif ($now > $end) {
                                                    $status = 'Expired';
                                                    $badgeClass = 'bg-secondary-subtle text-secondary';
                                                } else {
                                                    $status = 'Active';
                                                    $badgeClass = 'bg-success-subtle text-success';
                                                }
                                                $salePrice = $sale['original_price'] * (1 - $sale['discount_percentage'] / 100);
                                            ?>
                                                <tr>
                                                    <td class="px-4">
                                                        <div class="fw-bold text-dark small"><?php echo htmlspecialchars($sale['product_name']); ?></div>
                                                    </td>
                                                    <td>
                                                        <div class="small">
                                                            <div class="text-muted text-decoration-line-through x-small"><?php echo format_price($sale['original_price']); ?></div>
                                                            <div class="text-danger fw-bold"><?php echo format_price($salePrice); ?></div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1 fw-bold"><?php echo htmlspecialchars($sale['discount_percentage']); ?>%</span>
                                                    </td>
                                                    <td>
                                                        <div class="small text-muted">
                                                            <div><i class="far fa-calendar-alt me-1"></i> <?php echo date('M d, H:i', strtotime($sale['start_date'])); ?></div>
                                                            <div><i class="far fa-clock me-1"></i> <?php echo date('M d, H:i', strtotime($sale['end_date'])); ?></div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge <?php echo $badgeClass; ?> rounded-pill px-3 py-2 fw-bold small text-uppercase" style="letter-spacing: 0.5px;"><?php echo $status; ?></span>
                                                    </td>
                                                    <td class="px-4 text-end">
                                                        <a href="flash_sales.php?action=delete&id=<?php echo $sale['id']; ?>" class="btn btn-light btn-sm rounded-circle shadow-xs text-danger" onclick="return confirm('Are you sure you want to delete this flash sale?');" title="Delete">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include the shared footer template -->
    <?php include 'footer.php'; ?>


