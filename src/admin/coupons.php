<?php
// admin/coupons.php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_coupon'])) {
        $code = strtoupper(sanitize_input($_POST['code']));
        $type = in_array($_POST['type'], ['percentage', 'fixed_amount']) ? $_POST['type'] : 'percentage';
        $value = (float)$_POST['value'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $max_uses = (int)$_POST['max_uses'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (!empty($code) && $value > 0) {
            try {
                $stmt = $pdo->prepare("INSERT INTO coupons (code, discount_type, discount_value, valid_from, valid_until, usage_limit, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$code, $type, $value, $start_date, $end_date, $max_uses, $is_active])) {
                    $message = 'Coupon created successfully.';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to create coupon.';
                    $messageType = 'error';
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $message = "Error: The coupon code '$code' already exists. Please use a unique code.";
                } else {
                    $message = "Error: " . $e->getMessage();
                }
                $messageType = 'error';
            }
        } else {
            $message = 'Please fill all fields correctly.';
            $messageType = 'error';
        }
    } elseif (isset($_POST['update_coupon'])) {
        $id = (int)$_POST['coupon_id'];
        $code = strtoupper(sanitize_input($_POST['code']));
        $type = in_array($_POST['type'], ['percentage', 'fixed_amount']) ? $_POST['type'] : 'percentage';
        $value = (float)$_POST['value'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $max_uses = (int)$_POST['max_uses'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if (!empty($code) && $value > 0) {
            try {
                $stmt = $pdo->prepare("UPDATE coupons SET code = ?, discount_type = ?, discount_value = ?, valid_from = ?, valid_until = ?, usage_limit = ?, is_active = ? WHERE id = ?");
                if ($stmt->execute([$code, $type, $value, $start_date, $end_date, $max_uses, $is_active, $id])) {
                    $message = 'Coupon updated successfully.';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to update coupon.';
                    $messageType = 'error';
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $message = "Error: The coupon code '$code' already exists. Please use a unique code.";
                } else {
                    $message = "Error: " . $e->getMessage();
                }
                $messageType = 'error';
            }
        } else {
            $message = 'Please fill all fields correctly.';
            $messageType = 'error';
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $couponId = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
    if ($stmt->execute([$couponId])) {
        $message = 'Coupon deleted successfully.';
        $messageType = 'success';
    } else {
        $message = 'Failed to delete coupon.';
        $messageType = 'error';
    }
}

$coupons = $pdo->query("SELECT * FROM coupons ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Set page title and heading variables for the template
$page_title = 'Manage Coupons';
$page_heading = 'Manage Coupons';

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

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-0">
                    <h5 class="mb-0 fw-bold px-2"><i class="fas fa-ticket-alt me-2 text-danger"></i> Create New Coupon</h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <form method="POST" action="coupons.php">
                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <label for="code" class="form-label small fw-bold text-muted text-uppercase">Coupon Code *</label>
                                <input type="text" id="code" name="code" class="form-control border-light-subtle shadow-none fw-bold text-uppercase" placeholder="SUMMER2024" required>
                            </div>
                            <div class="col-md-4">
                                <label for="type" class="form-label small fw-bold text-muted text-uppercase">Discount Type</label>
                                <select id="type" name="type" class="form-select border-light-subtle shadow-none">
                                    <option value="percentage">Percentage (%)</option>
                                    <option value="fixed_amount">Fixed Amount (<?php echo htmlspecialchars(get_setting('currency_code', 'DZD')); ?>)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="value" class="form-label small fw-bold text-muted text-uppercase">Discount Value *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light-subtle text-muted fw-bold"><?php echo htmlspecialchars(get_setting('currency_code', 'DZD')); ?> / %</span>
                                    <input type="number" id="value" name="value" step="0.01" class="form-control border-light-subtle shadow-none" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="start_date" class="form-label small fw-bold text-muted text-uppercase">Valid From</label>
                                <input type="datetime-local" id="start_date" name="start_date" class="form-control border-light-subtle shadow-none">
                            </div>
                            <div class="col-md-4">
                                <label for="end_date" class="form-label small fw-bold text-muted text-uppercase">Valid Until</label>
                                <input type="datetime-local" id="end_date" name="end_date" class="form-control border-light-subtle shadow-none">
                            </div>
                            <div class="col-md-4">
                                <label for="max_uses" class="form-label small fw-bold text-muted text-uppercase">Usage Limit</label>
                                <input type="number" id="max_uses" name="max_uses" value="100" class="form-control border-light-subtle shadow-none">
                            </div>
                            <div class="col-12 mt-3">
                                <div class="card border-light-subtle bg-light p-3 rounded-3 shadow-none">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active_add" checked>
                                        <label class="form-check-label fw-bold small text-dark" for="is_active_add">Active Coupon</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="add_coupon" class="btn btn-danger px-5 py-2 fw-bold rounded-pill shadow-sm">
                            <i class="fas fa-plus-circle me-1"></i> Generate Coupon
                        </button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h2 class="h5 fw-bold mb-0 text-dark"><i class="fas fa-ticket-alt me-2 text-danger"></i> Active Coupons <span class="badge bg-light text-muted border ms-2 small fw-normal"><?php echo count($coupons); ?> Total</span></h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3 border-0">Code</th>
                                <th class="border-0">Type</th>
                                <th class="border-0">Value</th>
                                <th class="border-0">Uses</th>
                                <th class="border-0">Validity Period</th>
                                <th class="border-0">Status</th>
                                <th class="border-0">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($coupons)): ?>
                                <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-ticket-alt fa-3x opacity-25 mb-3"></i><br>No coupons found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($coupons as $coupon): ?>
                                    <tr>
                                        <td class="px-3"><span class="badge bg-light text-dark border px-3 py-2 fw-bold" style="font-size: 13px; letter-spacing: 1px;"><?php echo htmlspecialchars($coupon['code']); ?></span></td>
                                        <td><?php echo ucfirst(htmlspecialchars($coupon['discount_type'])); ?></td>
                                        <td>
                                            <span class="fw-bold text-dark">
                                                <?php echo $coupon['discount_type'] === 'percentage' ? htmlspecialchars($coupon['discount_value']) . '%' : format_price($coupon['discount_value']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="small fw-bold <?php echo ($coupon['used_count'] >= $coupon['usage_limit']) ? 'text-danger' : 'text-muted'; ?>">
                                                <?php echo htmlspecialchars($coupon['used_count']); ?> / <?php echo htmlspecialchars($coupon['usage_limit']); ?>
                                            </div>
                                            <div class="progress mt-1 mx-auto" style="height: 4px; width: 60px;">
                                                <?php $percent = min(100, ($coupon['used_count'] / max(1, $coupon['usage_limit'])) * 100); ?>
                                                <div class="progress-bar <?php echo ($percent >= 90) ? 'bg-danger' : 'bg-success'; ?>" style="width: <?php echo $percent; ?>%;"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="x-small text-muted">
                                                <div><i class="fas fa-calendar-check me-1"></i> From: <?php echo $coupon['valid_from'] ? date('M d, Y H:i', strtotime($coupon['valid_from'])) : 'N/A'; ?></div>
                                                <div><i class="fas fa-calendar-times me-1"></i> Until: <?php echo $coupon['valid_until'] ? date('M d, Y H:i', strtotime($coupon['valid_until'])) : 'N/A'; ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill px-3 py-2 fw-bold <?php echo $coupon['is_active'] ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis'; ?>" style="font-size: 10px;">
                                                <?php echo $coupon['is_active'] ? 'ACTIVE' : 'INACTIVE'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group shadow-sm rounded">
                                                <button onclick='openCouponEditModal(<?php echo json_encode($coupon); ?>)' class="btn btn-white btn-sm border-light-subtle text-primary bg-white" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="coupons.php?action=delete&id=<?php echo $coupon['id']; ?>" class="btn btn-white btn-sm border-light-subtle text-danger bg-white" title="Delete" onclick="return confirm('Are you sure you want to delete this coupon?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
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

    <!-- Edit Coupon Modal -->
    <div class="modal fade" id="couponModal" tabindex="-1" aria-labelledby="couponModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 py-3 px-4 bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="couponModalLabel"><i class="fas fa-edit me-2 text-danger"></i>Edit Coupon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm" method="POST">
                    <div class="modal-body p-4">
                        <input type="hidden" name="update_coupon" value="1">
                        <input type="hidden" name="coupon_id" id="editId">
                        
                        <div class="row g-4 mb-3">
                            <div class="col-md-6">
                                <label for="editCode" class="form-label small fw-bold text-muted text-uppercase">Coupon Code *</label>
                                <input type="text" id="editCode" name="code" class="form-control border-light-subtle shadow-none fw-bold text-uppercase" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="editType" class="form-label small fw-bold text-muted text-uppercase">Discount Type</label>
                                <select id="editType" name="type" class="form-select border-light-subtle shadow-none" required>
                                    <option value="percentage">Percentage</option>
                                    <option value="fixed_amount">Fixed Amount</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="editValue" class="form-label small fw-bold text-muted text-uppercase">Discount Value</label>
                                <input type="number" id="editValue" name="value" step="0.01" class="form-control border-light-subtle shadow-none" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="editMaxUses" class="form-label small fw-bold text-muted text-uppercase">Usage Limit</label>
                                <input type="number" id="editMaxUses" name="max_uses" min="1" class="form-control border-light-subtle shadow-none" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="editStartDate" class="form-label small fw-bold text-muted text-uppercase">Valid From</label>
                                <input type="datetime-local" id="editStartDate" name="start_date" class="form-control border-light-subtle shadow-none">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="editEndDate" class="form-label small fw-bold text-muted text-uppercase">Valid Until</label>
                                <input type="datetime-local" id="editEndDate" name="end_date" class="form-control border-light-subtle shadow-none">
                            </div>

                            <div class="col-12">
                                <div class="card border-light-subtle bg-light p-3 rounded-3 shadow-none">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="editIsActive" value="1">
                                        <label class="form-check-label fw-bold small text-dark" for="editIsActive">Active Coupon</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold text-muted border shadow-xs" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    let couponModal;
    document.addEventListener('DOMContentLoaded', function() {
        couponModal = new bootstrap.Modal(document.getElementById('couponModal'));
    });

    function openCouponEditModal(coupon) {
        document.getElementById('editId').value = coupon.id;
        document.getElementById('editCode').value = coupon.code;
        document.getElementById('editType').value = coupon.discount_type;
        document.getElementById('editValue').value = coupon.discount_value;
        document.getElementById('editMaxUses').value = coupon.usage_limit;
        
        if (coupon.valid_from) {
            document.getElementById('editStartDate').value = coupon.valid_from.replace(' ', 'T').substring(0, 16);
        } else {
            document.getElementById('editStartDate').value = '';
        }
        
        if (coupon.valid_until) {
            document.getElementById('editEndDate').value = coupon.valid_until.replace(' ', 'T').substring(0, 16);
        } else {
            document.getElementById('editEndDate').value = '';
        }
        
        document.getElementById('editIsActive').checked = coupon.is_active == 1;
        couponModal.show();
    }
    </script>

    <!-- Include the shared footer template -->
    <?php include 'footer.php'; ?>


