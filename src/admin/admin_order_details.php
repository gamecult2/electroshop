<?php
// admin/admin_order_details.php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';
require_once '../models/Order.php';
require_once '../models/Customer.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$orderModel = new Order();
$customerModel = new Customer();

$message = '';
$messageType = '';

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $adminId = $_SESSION['admin_id'] ?? null;
        switch ($_POST['action']) {
            case 'update_status':
                $status = $_POST['status'];
                $notes = $_POST['status_notes'] ?? '';
                if ($orderModel->updateStatus($orderId, $status, $adminId, $notes)) {
                    $message = 'Order status updated successfully.';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to update order status.';
                    $messageType = 'danger';
                }
                break;
            case 'update_payment_status':
                $pStatus = $_POST['payment_status'];
                if ($orderModel->updatePaymentStatus($orderId, $pStatus)) {
                    $message = 'Payment status updated successfully.';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to update payment status.';
                    $messageType = 'danger';
                }
                break;
            case 'update_notes':
                $adminNotes = $_POST['admin_notes'];
                $internalNotes = $_POST['internal_notes'];
                if ($orderModel->updateNotes($orderId, $adminNotes, $internalNotes)) {
                    $message = 'Notes updated successfully.';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to update notes.';
                    $messageType = 'danger';
                }
                break;
            case 'update_tracking':
                $tracking = $_POST['tracking_number'];
                $estDate = !empty($_POST['estimated_delivery']) ? $_POST['estimated_delivery'] : null;
                if ($orderModel->updateTracking($orderId, $tracking, $estDate)) {
                    $message = 'Tracking information updated.';
                    $messageType = 'success';
                }
                break;
            case 'send_email':
                $subject = $_POST['email_subject'];
                $body = $_POST['email_body'];
                $to = $_POST['customer_email'];
                if (send_email($to, $subject, $body)) {
                    $message = 'Email sent to customer successfully.';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to send email.';
                    $messageType = 'danger';
                }
                break;
        }
    }
}

$order = $orderModel->getById($orderId);
if (!$order) {
    die("Order not found.");
}

$items = $orderModel->getItems($orderId);
$history = $orderModel->getOrderStatusHistory($orderId);
$customer = $customerModel->getById($order['customer_id']);

// Set page title and heading variables for the template
$page_title = 'Order Details #' . $orderId;
$page_heading = 'Order Details';

// Include the shared header template
include 'header.php';

// Helper to format addresses
function format_address_detailed($json) {
    $addr = is_string($json) ? json_decode($json, true) : $json;
    if (!$addr) return 'N/A';
    
    $output = '<strong>' . htmlspecialchars(($addr['first_name'] ?? '') . ' ' . ($addr['last_name'] ?? '')) . '</strong><br>';
    if (!empty($addr['street_address'])) $output .= htmlspecialchars($addr['street_address']) . '<br>';
    if (!empty($addr['apartment_suite'])) $output .= htmlspecialchars($addr['apartment_suite']) . '<br>';
    $loc = [];
    if (!empty($addr['commune'])) $loc[] = $addr['commune'];
    if (!empty($addr['daira'])) $loc[] = $addr['daira'];
    if (!empty($addr['wilaya'])) $loc[] = $addr['wilaya'];
    $output .= htmlspecialchars(implode(', ', $loc)) . '<br>';
    if (!empty($addr['postal_code'])) $output .= 'Postal Code: ' . htmlspecialchars($addr['postal_code']) . '<br>';
    if (!empty($addr['phone_number'])) $output .= '<i class="fas fa-phone-alt me-1 text-muted"></i> ' . htmlspecialchars($addr['phone_number']);
    
    return $output;
}
?>

<style>
    .ls-1 { letter-spacing: 0.5px; }
    .x-small { font-size: 0.75rem; }
    .timeline { position: relative; padding-left: 1.5rem; }
    .timeline::before { content: ''; position: absolute; left: 0.25rem; top: 0; bottom: 0; width: 2px; background: #f1f3f5; }
    .timeline-item { position: relative; margin-bottom: 1.5rem; }
    .timeline-marker { position: absolute; left: -1.5rem; width: 12px; height: 12px; border-radius: 50%; background: #adb5bd; border: 2px solid #fff; z-index: 1; }
    .timeline-marker.active { background: #dc3545; box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.2); }
    .card-title-icon { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; margin-right: 10px; flex-shrink: 0; }
</style>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark">Order #<?php echo $order['order_number']; ?></h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item small"><a href="dashboard.php" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item small"><a href="orders.php" class="text-decoration-none text-muted">Orders</a></li>
                <li class="breadcrumb-item small active fw-bold text-danger" aria-current="page">Details</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <div class="dropdown">
            <button class="btn btn-white border border-light-subtle rounded-pill px-3 fw-bold shadow-xs dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-print me-1"></i> Documents
            </button>
            <ul class="dropdown-menu shadow border-0">
                <li><a class="dropdown-item py-2" href="javascript:window.print()"><i class="fas fa-file-invoice me-2 text-muted"></i> Print Invoice</a></li>
                <li><a class="dropdown-item py-2" href="#"><i class="fas fa-file-pdf me-2 text-muted"></i> Download PDF</a></li>
                <li><a class="dropdown-item py-2" href="#"><i class="fas fa-box me-2 text-muted"></i> Packing Slip</a></li>
            </ul>
        </div>
        <button class="btn btn-danger btn-sm rounded-pill px-3 fw-bold shadow-sm" onclick="openCustomerChat(<?php echo $order['customer_id']; ?>, '<?php echo addslashes(($order['user_first_name'] ?? '') . ' ' . ($order['user_last_name'] ?? '')); ?>')">
            <i class="fas fa-comments me-1"></i> Message Customer
        </button>
        <a href="orders.php" class="btn btn-light btn-sm text-secondary rounded-pill px-3 fw-bold border shadow-xs">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Quick Summary Banner -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100 bg-white rounded-4 overflow-hidden">
            <div class="card-body p-3">
                <label class="x-small fw-bold text-muted text-uppercase d-block mb-1 ls-1">Reference</label>
                <div class="fw-bold text-dark h6 mb-0">#<?php echo htmlspecialchars($order['order_number']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100 bg-white rounded-4 overflow-hidden">
            <div class="card-body p-3">
                <label class="x-small fw-bold text-muted text-uppercase d-block mb-1 ls-1">Date</label>
                <div class="fw-bold text-dark mb-0 small"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                <div class="text-muted x-small"><?php echo date('H:i', strtotime($order['created_at'])); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <?php 
        $pStatus = $order['payment_status'];
        $pBg = 'bg-white';
        $pBorder = 'border-secondary';
        if ($pStatus === 'paid') { $pBg = 'bg-success-subtle'; $pBorder = 'border-success'; }
        elseif ($pStatus === 'pending') { $pBg = 'bg-warning-subtle'; $pBorder = 'border-warning'; }
        elseif ($pStatus === 'failed') { $pBg = 'bg-danger-subtle'; $pBorder = 'border-danger'; }
        elseif ($pStatus === 'refunded') { $pBg = 'bg-info-subtle'; $pBorder = 'border-info'; }
        ?>
        <div class="card border-0 shadow-sm h-100 <?php echo $pBg; ?> rounded-4 overflow-hidden">
            <div class="card-body p-3 border-start <?php echo $pBorder; ?> border-5">
                <label class="x-small fw-bold text-muted text-uppercase d-block mb-1 ls-1">Payment</label>
                <div class="fw-bold <?php echo str_replace('border-', 'text-', $pBorder); ?> text-uppercase x-small mb-0">
                    <i class="fas <?php echo $pStatus === 'paid' ? 'fa-check-circle' : ($pStatus === 'failed' ? 'fa-times-circle' : 'fa-clock'); ?> me-1"></i>
                    <?php echo htmlspecialchars($pStatus); ?>
                </div>
                <div class="text-muted x-small"><?php echo strtoupper($order['payment_method']); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <?php
        $sStatus = $order['status'];
        $sBg = 'bg-white';
        $sBorder = 'border-secondary';
        switch($sStatus) {
            case 'pending': $sBg = 'bg-warning-subtle'; $sBorder = 'border-warning'; break;
            case 'confirmed': $sBg = 'bg-primary-subtle'; $sBorder = 'border-primary'; break;
            case 'processing': $sBg = 'bg-info-subtle'; $sBorder = 'border-info'; break;
            case 'shipped': $sBg = 'bg-primary-subtle'; $sBorder = 'border-primary'; break;
            case 'delivered': $sBg = 'bg-success-subtle'; $sBorder = 'border-success'; break;
            case 'cancelled': $sBg = 'bg-danger-subtle'; $sBorder = 'border-danger'; break;
            case 'returned': $sBg = 'bg-dark-subtle'; $sBorder = 'border-dark'; break;
        }
        ?>
        <div class="card border-0 shadow-sm h-100 <?php echo $sBg; ?> rounded-4 overflow-hidden">
            <div class="card-body p-3 border-start <?php echo $sBorder; ?> border-5">
                <label class="x-small fw-bold text-muted text-uppercase d-block mb-1 ls-1">Process Status</label>
                <div class="fw-bold <?php echo str_replace('border-', 'text-', $sBorder); ?> text-uppercase x-small mb-0">
                    <i class="fas fa-sync-alt me-1"></i>
                    <?php echo htmlspecialchars($sStatus); ?>
                </div>
                <div class="text-muted x-small text-truncate"><?php echo str_replace('_', ' ', $order['delivery_option']); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Main Content (Left) -->
    <div class="col-lg-8">
        <!-- Ordered Items Table -->
        <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-bold d-flex align-items-center">
                    <span class="card-title-icon bg-danger-subtle text-danger"><i class="fas fa-shopping-basket fa-sm"></i></span>
                    Ordered Items
                </h5>
                <span class="badge bg-light text-dark border fw-normal"><?php echo count($items); ?> Items</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 small fw-bold px-4 py-3 text-uppercase ls-1">Product</th>
                                <th class="border-0 small fw-bold text-center py-3 text-uppercase ls-1">Qty</th>
                                <th class="border-0 small fw-bold text-center py-3 text-uppercase ls-1">Price</th>
                                <th class="border-0 small fw-bold text-end px-4 py-3 text-uppercase ls-1">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-light rounded-3 overflow-hidden border border-light-subtle p-1 flex-shrink-0" style="width: 50px; height: 50px;">
                                                <img src="../<?php echo !empty($item['product_image']) ? htmlspecialchars($item['product_image']) : 'img/product-placeholder.jpg'; ?>" 
                                                     class="w-100 h-100 object-fit-contain" alt="">
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark small mb-1"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <span class="badge bg-light text-muted border fw-normal x-small">SKU: <?php echo htmlspecialchars($item['product_sku'] ?? 'N/A'); ?></span>
                                                    <?php 
                                                    $displayVariant = '';
                                                    if (!empty($item['attributes_json'])) {
                                                        $attrs = json_decode($item['attributes_json'], true);
                                                        if ($attrs) {
                                                            $displayParts = [];
                                                            foreach ($attrs as $k => $v) {
                                                                $displayParts[] = htmlspecialchars($k) . ': ' . htmlspecialchars($v);
                                                            }
                                                            $displayVariant = implode(', ', $displayParts);
                                                        }
                                                    }
                                                    
                                                    if (empty($displayVariant) && !empty($item['variant_name'])) {
                                                        $displayVariant = htmlspecialchars($item['variant_name']);
                                                    }

                                                    if (!empty($displayVariant)): ?>
                                                        <span class="badge bg-light text-danger border border-danger-subtle fw-normal x-small"><?php echo $displayVariant; ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-dark rounded-pill px-2"><?php echo $item['quantity']; ?></span>
                                    </td>
                                    <td class="text-center small fw-medium"><?php echo format_price($item['price_at_purchase']); ?></td>
                                    <td class="text-end fw-bold text-dark px-4"><?php echo format_price($item['total_price']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light-subtle border-0 p-4">
                <div class="row justify-content-end">
                    <div class="col-md-5">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Subtotal:</span>
                            <span class="fw-bold small"><?php echo format_price($order['subtotal']); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                            <span class="text-muted small">Shipping:</span>
                            <span class="fw-bold small text-info">+ <?php echo format_price($order['shipping_cost']); ?></span>
                        </div>
                        <?php if ($order['discount_amount'] > 0): ?>
                            <div class="d-flex justify-content-between mb-2 pb-2 border-bottom text-success">
                                <span class="small">Discount:</span>
                                <span class="fw-bold small">- <?php echo format_price($order['discount_amount']); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between align-items-end mt-3">
                            <span class="h6 mb-0 fw-bold">Total:</span>
                            <span class="h4 mb-0 text-danger fw-bold"><?php echo format_price($order['total_amount']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Destination & Billing Section -->
        <?php 
        $shipAddr = is_string($order['shipping_address']) ? json_decode($order['shipping_address'], true) : $order['shipping_address'];
        $billAddr = is_string($order['billing_address']) ? json_decode($order['billing_address'], true) : $order['billing_address'];
        $isSameAddress = empty($billAddr) || ($shipAddr == $billAddr);
        ?>
        <div class="row g-4 mb-4">
            <div class="<?php echo $isSameAddress ? 'col-12' : 'col-md-6'; ?>">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-truck me-2 text-primary"></i> Shipping Address <?php echo $isSameAddress ? '& Billing' : ''; ?></h6>
                        <?php if (!empty($order['tracking_number'])): ?>
                            <span class="badge bg-dark-subtle text-dark border-0 rounded-pill px-2 x-small fw-bold">
                                <i class="fas fa-barcode me-1"></i> <?php echo htmlspecialchars($order['tracking_number']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="<?php echo !empty($order['tracking_number']) ? 'col-md-7' : 'col-12'; ?>">
                                <h6 class="mb-2 text-dark fw-bold"><?php echo htmlspecialchars(($order['user_first_name'] ?? '') . ' ' . ($order['user_last_name'] ?? '')); ?></h6>
                                <p class="text-muted small mb-1"><?php echo htmlspecialchars($shipAddr['street_address'] ?? ''); ?></p>
                                <p class="text-muted small mb-1"><?php echo htmlspecialchars(($shipAddr['commune'] ?? '') . ', ' . ($shipAddr['wilaya'] ?? '')); ?></p>
                                <?php if (!empty($shipAddr['phone_number'])): ?>
                                    <p class="text-muted small mt-3 mb-0"><i class="fas fa-phone-alt me-1 text-muted"></i> <?php echo htmlspecialchars($shipAddr['phone_number']); ?></p>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($order['tracking_number'])): ?>
                            <div class="col-md-5 border-start">
                                <label class="x-small fw-bold text-muted text-uppercase d-block mb-2 ls-1">Logistic Info</label>
                                <div class="bg-light p-2 rounded-3 border-start border-primary border-3 mb-2">
                                    <div class="x-small text-muted">Tracking ID</div>
                                    <div class="small fw-bold text-dark"><?php echo htmlspecialchars($order['tracking_number']); ?></div>
                                </div>
                                <?php if ($order['estimated_delivery']): ?>
                                <div class="bg-light p-2 rounded-3 border-start border-info border-3">
                                    <div class="x-small text-muted">Est. Delivery</div>
                                    <div class="small fw-bold text-dark"><?php echo date('M d, Y', strtotime($order['estimated_delivery'])); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php if (!$isSameAddress): ?>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-file-invoice me-2 text-primary"></i> Billing Details</h6>
                    </div>
                    <div class="card-body p-4">
                        <h6 class="mb-2 text-dark fw-bold"><?php echo htmlspecialchars($order['user_first_name'] . ' ' . $order['user_last_name']); ?></h6>
                        <p class="text-muted small mb-1"><?php echo htmlspecialchars($billAddr['street_address'] ?? ''); ?></p>
                        <p class="text-muted small mb-1"><?php echo htmlspecialchars(($billAddr['commune'] ?? '') . ', ' . ($billAddr['wilaya'] ?? '')); ?></p>
                        <?php if (!empty($billAddr['phone_number'])): ?>
                            <p class="text-muted small mt-3 mb-0"><i class="fas fa-phone-alt me-1 text-muted"></i> <?php echo htmlspecialchars($billAddr['phone_number']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Documentation Card -->
        <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                <span class="card-title-icon bg-warning-subtle text-warning"><i class="fas fa-sticky-note fa-sm"></i></span>
                <h6 class="mb-0 fw-bold text-dark">Order Documentation</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <input type="hidden" name="action" value="update_notes">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted text-uppercase ls-1 d-block mb-2">Customer Visible Notes</label>
                            <textarea name="admin_notes" class="form-control border-light-subtle small" rows="4" placeholder="Messages for the customer..."><?php echo htmlspecialchars($order['admin_notes'] ?? ''); ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted text-uppercase ls-1 d-block mb-2 text-danger">Internal Staff Comments</label>
                            <textarea name="internal_notes" class="form-control border-danger-subtle bg-danger-subtle bg-opacity-10 small" rows="4" placeholder="Staff only notes..."><?php echo htmlspecialchars($order['internal_notes'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-dark rounded-pill px-4 fw-bold shadow-sm">Save Notes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar Column (Right) -->
    <div class="col-lg-4">
        <!-- Fulfillment Management -->
        <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
            <div class="card-header bg-dark text-white py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-tasks me-2"></i> Fulfillment Control</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" class="mb-4">
                    <input type="hidden" name="action" value="update_status">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase ls-1">Process Status</label>
                        <div class="d-flex gap-2">
                            <select name="status" class="form-select border-light-subtle fw-bold shadow-none">
                                <?php 
                                $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'];
                                foreach ($statuses as $s): ?>
                                    <option value="<?php echo $s; ?>" <?php echo $order['status'] === $s ? 'selected' : ''; ?>><?php echo strtoupper($s); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-danger px-3"><i class="fas fa-save"></i></button>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted text-uppercase ls-1">Change Reason</label>
                        <textarea name="status_notes" class="form-control border-light-subtle x-small" rows="2" placeholder="Brief explanation..."></textarea>
                    </div>
                </form>

                <div class="border-top pt-4 mt-2">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_tracking">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase ls-1">Logistics Tracking</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-light-subtle"><i class="fas fa-barcode text-muted"></i></span>
                                <input type="text" name="tracking_number" class="form-control border-light-subtle" value="<?php echo htmlspecialchars($order['tracking_number'] ?? ''); ?>" placeholder="Tracking ID">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase ls-1">Est. Delivery</label>
                            <input type="date" name="estimated_delivery" class="form-control border-light-subtle" value="<?php echo htmlspecialchars($order['estimated_delivery'] ?? ''); ?>">
                        </div>
                        <button type="submit" class="btn btn-outline-dark w-100 fw-bold rounded-pill shadow-xs py-2">
                            Update Logistics
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Payment Management -->
        <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-credit-card me-2 text-primary"></i> Payment Management</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <input type="hidden" name="action" value="update_payment_status">
                    <label class="form-label small fw-bold text-muted text-uppercase ls-1">Payment Status</label>
                    <div class="d-flex gap-2">
                        <select name="payment_status" class="form-select border-light-subtle fw-bold shadow-none">
                            <?php 
                            $p_statuses = ['pending', 'paid', 'failed', 'refunded'];
                            foreach ($p_statuses as $ps): ?>
                                <option value="<?php echo $ps; ?>" <?php echo $order['payment_status'] === $ps ? 'selected' : ''; ?>><?php echo strtoupper($ps); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary px-3"><i class="fas fa-check"></i></button>
                    </div>
                    <div class="mt-2 text-muted x-small">Method: <strong><?php echo strtoupper($order['payment_method']); ?></strong></div>
                </form>
            </div>
        </div>

        <!-- Customer Card -->
        <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom text-center">
                <h6 class="mb-0 fw-bold text-dark">Customer Insight</h6>
            </div>
            <div class="card-body p-4 text-center">
                <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-3 mx-auto mb-3 shadow-sm flex-shrink-0" style="width: 60px; height: 60px; border: 4px solid #fff; box-shadow: 0 0 0 1px #eee;">
                    <?php echo strtoupper(substr(($order['user_first_name'] ?? 'G'), 0, 1)); ?>
                </div>
                <div class="fw-bold text-dark fs-5 mb-1"><?php echo htmlspecialchars(($order['user_first_name'] ?? '') . ' ' . ($order['user_last_name'] ?? '')); ?></div>
                <div class="text-muted small mb-4"><?php echo htmlspecialchars($order['user_email'] ?? ''); ?></div>
                <div class="row g-2">
                    <div class="col-6"><a href="edit_customer.php?id=<?php echo $order['customer_id']; ?>" class="btn btn-light btn-sm border w-100 fw-bold rounded-pill shadow-xs" <?php if(empty($order['customer_id'])) echo 'onclick="return false;" style="pointer-events: none; opacity: 0.5;"'; ?>>Profile</a></div>
                    <div class="col-6"><a href="orders.php?customer_id=<?php echo $order['customer_id']; ?>" class="btn btn-light btn-sm border w-100 fw-bold rounded-pill shadow-xs" <?php if(empty($order['customer_id'])) echo 'onclick="return false;" style="pointer-events: none; opacity: 0.5;"'; ?>>Orders</a></div>
                </div>
                <div class="mt-3">
                    <button class="btn btn-outline-primary btn-sm w-100 rounded-pill shadow-xs"
                            onclick="openCustomerChat(<?php echo $order['customer_id']; ?>, '<?php echo addslashes(($order['user_first_name'] ?? '') . ' ' . ($order['user_last_name'] ?? '')); ?>')"
                            <?php if(empty($order['customer_id'])) echo 'disabled'; ?>>
                        <i class="fas fa-comments me-1"></i> Message Customer
                    </button>
                </div>
            </div>
        </div>

        <!-- Activity Log -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-history me-2 text-muted"></i> Operational Log</h6>
                <span class="badge bg-light text-muted border fw-normal x-small"><?php echo count($history); ?> events</span>
            </div>
            <div class="card-body p-4">
                <div id="timeline-container" class="timeline small">
                    <?php if (empty($history)): ?>
                        <div class="text-muted py-2 text-center italic">No operations logged.</div>
                    <?php else: ?>
                        <?php 
                        $reversedHistory = array_reverse($history);
                        foreach ($reversedHistory as $index => $h): 
                        ?>
                            <div class="timeline-item timeline-row" data-page="<?php echo floor($index / 5); ?>" style="<?php echo $index >= 5 ? 'display: none;' : ''; ?>">
                                <div class="timeline-marker <?php echo $index === 0 ? 'active' : ''; ?>"></div>
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <div class="fw-bold text-dark text-uppercase x-small ls-1"><?php echo htmlspecialchars($h['status']); ?></div>
                                    <div class="text-muted" style="font-size: 10px;"><?php echo date('M d, H:i', strtotime($h['created_at'])); ?></div>
                                </div>
                                <div class="text-muted x-small mb-2">By: <strong><?php echo htmlspecialchars(($h['first_name'] ?? 'System') . ' ' . ($h['last_name'] ?? '')); ?></strong></div>
                                <?php if ($h['note']): ?>
                                    <div class="p-2 bg-light border-start border-danger border-3 rounded x-small text-dark italic">"<?php echo htmlspecialchars($h['note']); ?>"</div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <?php if (count($history) > 5): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 fw-bold text-muted" id="prev-timeline" disabled onclick="changeTimelinePage(-1)"><i class="fas fa-chevron-left"></i> Newer</button>
                        <span class="x-small fw-bold text-muted" id="timeline-page-info">1 / <?php echo ceil(count($history) / 5); ?></span>
                        <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 fw-bold text-danger" id="next-timeline" onclick="changeTimelinePage(1)">Older <i class="fas fa-chevron-right"></i></button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
let currentTimelinePage = 0;
const totalTimelinePages = <?php echo ceil(count($history) / 5); ?>;

function changeTimelinePage(direction) {
    const newPage = currentTimelinePage + direction;
    if (newPage < 0 || newPage >= totalTimelinePages) return;
    currentTimelinePage = newPage;
    document.querySelectorAll('.timeline-row').forEach(row => { row.style.display = parseInt(row.dataset.page) === currentTimelinePage ? 'block' : 'none'; });
    document.getElementById('timeline-page-info').textContent = `${currentTimelinePage + 1} / ${totalTimelinePages}`;
    document.getElementById('prev-timeline').disabled = currentTimelinePage === 0;
    document.getElementById('next-timeline').disabled = currentTimelinePage === totalTimelinePages - 1;
    document.getElementById('prev-timeline').classList.toggle('text-danger', currentTimelinePage !== 0);
    document.getElementById('prev-timeline').classList.toggle('text-muted', currentTimelinePage === 0);
    document.getElementById('next-timeline').classList.toggle('text-danger', currentTimelinePage !== totalTimelinePages - 1);
    document.getElementById('next-timeline').classList.toggle('text-muted', currentTimelinePage === totalTimelinePages - 1);
}
</script>

<!-- Email Modal -->
<div class="modal fade" id="emailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark">Send Message</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="send_email">
                <input type="hidden" name="customer_email" value="<?php echo htmlspecialchars($order['user_email']); ?>">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted text-uppercase ls-1">Subject</label>
                        <input type="text" name="email_subject" class="form-control border-light-subtle" value="Update regarding Order #<?php echo $order['order_number']; ?>" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-muted text-uppercase ls-1">Message</label>
                        <textarea name="email_body" class="form-control border-light-subtle" rows="6" required>Dear <?php echo htmlspecialchars($order['user_first_name']); ?>,

We are writing to provide an update on your order #<?php echo $order['order_number']; ?>.

[Your message here]

Best regards,
The Customer Service Team</textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">Send Email</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
@media print {
    .navbar, .bg-dark.flex-column, .btn, .breadcrumb, .alert, .card-header i, .sticky-bottom, hr, .timeline-marker, .timeline::before, .dropdown, .card-header span, .card-footer { display: none !important; }
    .col-lg-8, .col-lg-4 { width: 100% !important; }
    .card { border: 1px solid #eee !important; box-shadow: none !important; margin-bottom: 20px !important; border-radius: 0 !important; }
    body { background: white !important; }
    .table-responsive { overflow: visible !important; }
}
</style>

<script>
function openCustomerChat(customerId, customerName) {
    // Redirect to messages page with customer filter
    window.location.href = `messages.php?customer_id=${customerId}&customer_name=${encodeURIComponent(customerName)}`;
}
</script>

<?php include 'footer.php'; ?>