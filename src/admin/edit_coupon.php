<?php
// admin/edit_coupon.php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';
$coupon = null;
$couponId = (int)($_GET['id'] ?? 0);

// Get coupon details
if ($couponId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE id = ?");
    $stmt->execute([$couponId]);
    $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$coupon) {
        $message = 'Coupon not found.';
        $messageType = 'error';
    }
} else {
    header('Location: coupons.php');
    exit;
}

// Update coupon
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(sanitize_input($_POST['code']));
    $type = in_array($_POST['type'], ['percentage', 'fixed_amount']) ? $_POST['type'] : 'percentage';
    $value = (float)$_POST['value'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $max_uses = (int)$_POST['max_uses'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $stmt = $pdo->prepare("UPDATE coupons SET code=?, discount_type=?, discount_value=?, valid_from=?, valid_until=?, usage_limit=?, is_active=? WHERE id=?");
    if ($stmt->execute([$code, $type, $value, $start_date, $end_date, $max_uses, $is_active, $couponId])) {
        $message = 'Coupon updated successfully.';
        $messageType = 'success';
        
        // Refresh coupon data
        $stmt = $pdo->prepare("SELECT * FROM coupons WHERE id = ?");
        $stmt->execute([$couponId]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $message = 'Failed to update coupon.';
        $messageType = 'error';
    }
}

// Set page title and heading variables for the template
$page_title = 'Edit Coupon';
$page_heading = 'Edit Coupon';

// Include the shared header template
include 'header.php';

?>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                    <i class="fas fa-<?php echo $messageType === 'error' ? 'exclamation-circle' : 'check-circle'; ?> me-2"></i>
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($coupon): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-0">
                    <h5 class="mb-0 fw-bold px-2"><i class="fas fa-edit me-2 text-danger"></i> Edit Coupon</h5>
                    <a href="coupons.php" class="btn btn-light btn-sm text-secondary rounded-pill px-3 fw-bold">
                        <i class="fas fa-arrow-left me-1"></i> Back to Coupons
                    </a>
                </div>
                <div class="card-body p-4 pt-0">
                    <form method="POST" action="edit_coupon.php?id=<?php echo $couponId; ?>">
                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <label for="code" class="form-label small fw-bold text-muted text-uppercase">Coupon Code *</label>
                                <input type="text" id="code" name="code" class="form-control border-light-subtle shadow-none fw-bold text-uppercase" value="<?php echo htmlspecialchars($coupon['code']); ?>" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="type" class="form-label small fw-bold text-muted text-uppercase">Discount Type</label>
                                <select id="type" name="type" class="form-select border-light-subtle shadow-none">
                                    <option value="percentage" <?php echo $coupon['discount_type'] === 'percentage' ? 'selected' : ''; ?>>Percentage (%)</option>
                                    <option value="fixed_amount" <?php echo $coupon['discount_type'] === 'fixed_amount' ? 'selected' : ''; ?>>Fixed Amount (DZD)</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="value" class="form-label small fw-bold text-muted text-uppercase">Discount Value *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light-subtle text-muted fw-bold">DZD / %</span>
                                    <input type="number" id="value" name="value" step="0.01" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars($coupon['discount_value']); ?>" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="start_date" class="form-label small fw-bold text-muted text-uppercase">Valid From</label>
                                <input type="datetime-local" id="start_date" name="start_date" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars(substr($coupon['valid_from'], 0, 16)); ?>">
                            </div>

                            <div class="col-md-4">
                                <label for="end_date" class="form-label small fw-bold text-muted text-uppercase">Valid Until</label>
                                <input type="datetime-local" id="end_date" name="end_date" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars(substr($coupon['valid_until'], 0, 16)); ?>">
                            </div>

                            <div class="col-md-4">
                                <label for="max_uses" class="form-label small fw-bold text-muted text-uppercase">Usage Limit</label>
                                <input type="number" id="max_uses" name="max_uses" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars($coupon['usage_limit']); ?>">
                            </div>

                            <div class="col-12">
                                <div class="card border-light-subtle bg-light p-3 rounded-3 shadow-none">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" <?php echo $coupon['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label fw-bold small text-dark" for="is_active">Publish this coupon</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3 mt-2">
                            <button type="submit" class="btn btn-danger px-5 py-2 fw-bold rounded-pill shadow-sm">
                                <i class="fas fa-save me-1"></i> Update Coupon
                            </button>
                            <a href="coupons.php" class="btn btn-light px-4 py-2 fw-bold rounded-pill text-muted border shadow-xs">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
                <div class="alert alert-danger border-0 shadow-sm rounded-4 p-4 d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3 opacity-50"></i>
                    <div>
                        <h5 class="fw-bold mb-1">Coupon Not Found</h5>
                        <p class="mb-0 small text-danger-emphasis">The coupon you are trying to edit does not exist or has been deleted.</p>
                        <a href="coupons.php" class="btn btn-danger btn-sm mt-3 rounded-pill px-3 fw-bold shadow-sm">Return to List</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Include the shared footer template -->
    <?php include 'footer.php'; ?>

