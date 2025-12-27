<?php
require_once 'includes/init.php';
require_once 'models/Order.php';
require_once 'models/Customer.php';

require_login();

$orderModel = new Order();
$customerModel = new Customer();

$view = $_GET['view'] ?? 'list';
$orderId = $_GET['order_id'] ?? null;

// Handle Cancellation (BEFORE any output)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_order') {
    $idToCancel = (int)$_POST['order_id'];
    $reason = sanitize_input($_POST['cancel_reason'] ?? 'Cancelled by customer');
    
    $orderToCancel = $orderModel->getById($idToCancel);
    if ($orderToCancel && $orderToCancel['customer_id'] == get_current_user_id()) {
        $cancellableStatuses = ['pending', 'confirmed', 'processing'];
        if (in_array($orderToCancel['status'], $cancellableStatuses)) {
            if ($orderModel->cancel($idToCancel, $reason)) {
                // Add to history
                $historySql = "INSERT INTO order_status_history (order_id, status, admin_user_id, note) VALUES (?, 'cancelled', NULL, ?)";
                $hStmt = $GLOBALS['pdo']->prepare($historySql);
                $hStmt->execute([$idToCancel, $reason]);
                
                set_message('Order #'. $orderToCancel['order_number'] .' has been cancelled.', 'success');
            } else {
                set_message('Failed to cancel order.', 'danger');
            }
        } else {
            set_message('Order cannot be cancelled as it is already ' . $orderToCancel['status'], 'warning');
        }
    }
    header("Location: order_history.php" . ($view === 'details' ? "?view=details&order_id=$idToCancel" : ""));
    exit;
}

// Data fetching
$order = null;
if ($view === 'details' && $orderId) {
    $order = $orderModel->getById($orderId);
    if ($order && $order['customer_id'] != get_current_user_id()) {
        $order = null; // Security check
    }
    if ($order) {
        $orderItems = $orderModel->getItems($orderId);
        $user = $customerModel->getById(get_current_user_id());
    }
} else {
    $page = $_GET['page'] ?? 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    $allOrders = $orderModel->getUserOrders(get_current_user_id(), null);
    $totalOrders = count($allOrders);
    $orders = array_slice($allOrders, $offset, $limit);
    $totalPages = ceil($totalOrders / $limit);
}

require_once 'includes/header.php';
?>

<style>
    .ls-1 { letter-spacing: 0.5px; }
    .x-small { font-size: 0.75rem; }
</style>

<div class="container-xxl pb-4">
    <?php 
    $breadcrumb_items = [
        ['label' => t('home'), 'url' => 'index.php'],
        ['label' => t('my_account'), 'url' => 'account.php'],
        ['label' => t('order_history')]
    ];
    include 'includes/breadcrumb.php';
    ?>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type'] ?? 'info'; ?> alert-dismissible fade show border-0 shadow-sm mb-4 mt-3" role="alert">
            <i class="fas fa-<?php echo ($_SESSION['message_type'] ?? 'info') === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
            <?php echo $_SESSION['message']; unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($view === 'details' && $order): ?>
        <div class="row g-3 mb-4 mt-4">
            <!-- Quick Summary Row -->
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
                        <label class="x-small fw-bold text-muted text-uppercase d-block mb-1 ls-1">Order Date</label>
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
                            <?php echo t($pStatus); ?>
                        </div>
                        <div class="text-muted x-small"><?php echo t($order['payment_method']); ?></div>
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
                            <?php echo t($sStatus); ?>
                        </div>
                        <div class="text-muted x-small text-truncate"><?php echo str_replace('_', ' ', t($order['delivery_option'])); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <?php
                            $statusClass = 'bg-secondary';
                            switch($order['status']) {
                                case 'pending': $statusClass = 'bg-warning text-dark'; break;
                                case 'confirmed': $statusClass = 'bg-primary text-white'; break;
                                case 'processing': $statusClass = 'bg-info text-white'; break;
                                case 'shipped': $statusClass = 'bg-primary'; break;
                                case 'delivered': $statusClass = 'bg-success'; break;
                                case 'cancelled': $statusClass = 'bg-danger'; break;
                            }
                            ?>
                            <span class="badge <?php echo $statusClass; ?> px-3 py-2 text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">
                                <?php echo t($order['status']); ?>
                            </span>
                            <h5 class="mb-0 fw-bold">Order #<?php echo $order['order_number']; ?></h5>
                        </div>
                        <span class="text-muted small"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php foreach ($orderItems as $item): ?>
                                <div class="list-group-item py-3 px-4 border-0 border-bottom">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 bg-light rounded border p-1" style="width: 80px; height: 80px;">
                                            <img src="<?php echo !empty($item['product_image']) ? htmlspecialchars($item['product_image']) : 'img/product-placeholder.jpg'; ?>" 
                                                 class="img-fluid rounded object-fit-contain w-100 h-100" alt="">
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($item['product_name']); ?></h6>
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
                                            <p class="text-muted small mb-0 mt-1">Quantity: <span class="text-dark fw-bold"><?php echo $item['quantity']; ?></span></p>
                                        </div>
                                        <div class="text-end">
                                            <div class="text-muted x-small"><?php echo format_price($item['price_at_purchase']); ?></div>
                                            <div class="fw-bold text-danger"><?php echo format_price($item['total_price']); ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Addresses Section -->
                <?php 
                $shipAddr = json_decode($order['shipping_address'], true);
                $billAddr = json_decode($order['billing_address'], true);
                $isSameAddress = empty($billAddr) || ($shipAddr == $billAddr);
                ?>
                <div class="row g-4 mb-4">
                    <div class="<?php echo $isSameAddress ? 'col-12' : 'col-md-6'; ?>">
                        <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                            <div class="card-header bg-white py-3">
                                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-truck me-2 text-primary"></i> Shipping Address <?php echo $isSameAddress ? '& Billing' : ''; ?></h6>
                            </div>
                            <div class="card-body p-4">
                                <h6 class="mb-2 text-dark fw-bold"><?php echo htmlspecialchars($order['customer_name']); ?></h6>
                                <p class="text-muted small mb-1"><?php echo htmlspecialchars($shipAddr['street_address'] ?? ''); ?></p>
                                <p class="text-muted small mb-1"><?php echo htmlspecialchars(($shipAddr['commune'] ?? '') . ', ' . ($shipAddr['wilaya'] ?? '')); ?></p>
                                <?php if (!empty($shipAddr['phone_number'])): ?>
                                    <p class="text-muted small mt-3 mb-0"><i class="fas fa-phone-alt me-2 text-muted"></i> <?php echo htmlspecialchars($shipAddr['phone_number']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php if (!$isSameAddress): ?>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                            <div class="card-header bg-white py-3">
                                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-file-invoice me-2 text-primary"></i> Billing Details</h6>
                            </div>
                            <div class="card-body p-4">
                                <h6 class="mb-2 text-dark fw-bold"><?php echo htmlspecialchars($order['customer_name']); ?></h6>
                                <p class="text-muted small mb-1"><?php echo htmlspecialchars($billAddr['street_address'] ?? ''); ?></p>
                                <p class="text-muted small mb-1"><?php echo htmlspecialchars(($billAddr['commune'] ?? '') . ', ' . ($billAddr['wilaya'] ?? '')); ?></p>
                                <?php if (!empty($billAddr['phone_number'])): ?>
                                    <p class="text-muted small mt-3 mb-0"><i class="fas fa-phone-alt me-2 text-muted"></i> <?php echo htmlspecialchars($billAddr['phone_number']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Customer Notes -->
                <?php if (!empty($order['admin_notes'])): ?>
                    <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden border-start border-5 border-warning">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-comment-dots me-2 text-warning"></i> Note from Shop</h6>
                        </div>
                        <div class="card-body p-4 bg-light-subtle">
                            <p class="mb-0 small text-dark lh-base"><?php echo nl2br(htmlspecialchars($order['admin_notes'])); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <!-- Logistics Card -->
                <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
                    <div class="card-header bg-dark text-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-truck-loading me-2"></i> Shipment Tracking</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="small fw-bold text-muted text-uppercase ls-1 d-block mb-2">Tracking Number</label>
                            <?php if (!empty($order['tracking_number'])): ?>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light border-light-subtle"><i class="fas fa-barcode text-muted"></i></span>
                                    <input type="text" class="form-control bg-white border-light-subtle fw-bold text-dark" value="<?php echo htmlspecialchars($order['tracking_number']); ?>" readonly>
                                </div>
                            <?php else: ?>
                                <span class="badge bg-light text-muted border fw-normal">Awaiting shipment...</span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="small fw-bold text-muted text-uppercase ls-1 d-block mb-2">Est. Delivery Date</label>
                            <?php if (!empty($order['estimated_delivery'])): ?>
                                <div class="fw-bold text-dark small mb-0">
                                    <i class="far fa-calendar-check me-2 text-primary"></i>
                                    <?php echo date('M d, Y', strtotime($order['estimated_delivery'])); ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted small">Not scheduled yet</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="fas fa-calculator me-2 text-primary"></i> <?php echo t('order_summary'); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal:</span>
                            <span class="fw-bold"><?php echo format_price($order['subtotal']); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                            <span class="text-muted">Shipping:</span>
                            <span class="fw-bold"><?php echo format_price($order['shipping_cost']); ?></span>
                        </div>
                        <?php if (isset($order['discount_amount']) && $order['discount_amount'] > 0): ?>
                            <div class="d-flex justify-content-between mb-3 border-bottom pb-3 text-success">
                                <span>Discount:</span>
                                <span class="fw-bold">-<?php echo format_price($order['discount_amount']); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between align-items-end">
                            <span class="h5 mb-0">Total:</span>
                            <span class="h3 mb-0 text-danger fw-bold"><?php echo format_price($order['total_amount']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="fas fa-credit-card me-2 text-primary"></i> Payment</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="text-muted small">Method:</span>
                            <span class="ms-2 fw-bold text-dark"><?php echo t($order['payment_method']); ?></span>
                        </div>
                        <div class="mb-0">
                            <span class="text-muted small">Status:</span>
                            <?php
                            $paymentStatusClass = 'bg-secondary';
                            switch($order['payment_status']) {
                                case 'paid': $paymentStatusClass = 'bg-success'; break;
                                case 'pending': $paymentStatusClass = 'bg-warning text-dark'; break;
                                case 'failed': $paymentStatusClass = 'bg-danger'; break;
                            }
                            ?>
                            <span class="badge <?php echo $paymentStatusClass; ?> ms-2"><?php echo t($order['payment_status']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <?php if (in_array($order['status'], ['pending', 'confirmed', 'processing'])): ?>
                        <button class="btn btn-outline-danger py-3 rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">
                            <i class="fas fa-times-circle me-2"></i> Cancel Order
                        </button>
                    <?php endif; ?>
                    <a href="order_tracking.php?order_id=<?php echo $order['id']; ?>" class="btn btn-danger py-3 shadow-sm rounded-3">
                        <i class="fas fa-map-marker-alt me-2"></i> Track Order
                    </a>
                    <a href="order_history.php" class="btn btn-outline-secondary py-3 rounded-3">
                        <i class="fas fa-arrow-left me-2"></i> Back to Orders
                    </a>
                </div>
            </div>
        </div>

        <!-- Cancel Order Modal -->
        <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4">
                    <div class="modal-header border-bottom py-3 px-4">
                        <h5 class="modal-title fw-bold text-dark">Cancel Order #<?php echo $order['order_number']; ?></h5>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="action" value="cancel_order">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <div class="modal-body p-4">
                            <p class="text-muted small">Are you sure you want to cancel this order? This action cannot be undone.</p>
                            <div class="mb-0">
                                <label class="form-label small fw-bold text-muted text-uppercase">Reason for cancellation</label>
                                <select name="cancel_reason" class="form-select border-light-subtle">
                                    <option value="Changed my mind">Changed my mind</option>
                                    <option value="Order taking too long">Order taking too long</option>
                                    <option value="Found a better price elsewhere">Found a better price elsewhere</option>
                                    <option value="Made a mistake in the order">Made a mistake in the order</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0 px-4 pb-4">
                            <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Keep Order</button>
                            <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">Confirm Cancellation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php elseif ($view === 'details'): ?>
        <div class="container-xxl text-center p-5">
            <div class="mb-4">
                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 100px; height: 100px;">
                    <i class="fas fa-search text-muted display-4"></i>
                </div>
            </div>
            <h2><?php echo t('order_not_found'); ?></h2>
            <p class="text-muted">The order you are looking for does not exist or you do not have permission to view it.</p>
            <a href="order_history.php" class="btn btn-danger mt-3 rounded-pill px-5 fw-bold"><?php echo t('back_to_orders'); ?></a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-9">
                <?php if (count($orders) > 0): ?>
                    <div class="row g-4">
                        <?php foreach ($orders as $order): ?>
                            <div class="col-12">
                                <div class="card border-0 shadow-sm overflow-hidden">
                                    <div class="card-header bg-light py-3 border-0">
                                        <div class="row align-items-center g-3">
                                            <div class="col-sm-3">
                                                <span class="text-muted small text-uppercase d-block mb-1">Order #</span>
                                                <span class="fw-bold text-dark"><?php echo $order['order_number']; ?></span>
                                            </div>
                                            <div class="col-sm-3">
                                                <span class="text-muted small text-uppercase d-block mb-1">Placed on</span>
                                                <span class="fw-bold text-dark"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                                            </div>
                                            <div class="col-sm-3">
                                                <span class="text-muted small text-uppercase d-block mb-1">Total</span>
                                                <div class="d-flex align-items-center">
                                                    <span class="fw-bold text-danger"><?php echo format_price($order['total_amount']); ?></span>
                                                    <?php if (isset($order['discount_amount']) && $order['discount_amount'] > 0): ?>
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle ms-2 px-2 py-1" style="font-size: 0.65rem;" title="Discount Applied">
                                                            <i class="fas fa-tag me-1"></i> DISCOUNT
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="col-sm-3 text-sm-end">
                                                <?php
                                                $statusClass = 'bg-secondary';
                                                switch($order['status']) {
                                                    case 'pending': $statusClass = 'bg-warning text-dark'; break;
                                                    case 'processing': $statusClass = 'bg-info text-white'; break;
                                                    case 'shipped': $statusClass = 'bg-primary'; break;
                                                    case 'delivered': $statusClass = 'bg-success'; break;
                                                    case 'cancelled': $statusClass = 'bg-danger'; break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?> px-3"><?php echo t($order['status']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body py-3">
                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                            <div class="text-muted small">
                                                <i class="fas fa-truck me-2"></i> Delivery to <strong><?php echo htmlspecialchars($order['wilaya'] ?? ''); ?></strong>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <a href="?view=details&order_id=<?php echo $order['id']; ?>" class="btn btn-outline-dark btn-sm rounded-pill px-3">View Details</a>
                                                <a href="order_tracking.php?order_id=<?php echo $order['id']; ?>" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm">Track</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Order history pagination" class="mt-5">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link shadow-none" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="card border-0 shadow-sm text-center py-5 px-4">
                        <div class="mb-4">
                            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 100px; height: 100px;">
                                <i class="fas fa-shopping-bag text-muted display-4"></i>
                            </div>
                        </div>
                        <h3 class="mb-2">No orders found</h3>
                        <p class="text-muted mb-4">You haven't placed any orders yet. Start exploring our amazing collections!</p>
                        <div class="d-flex justify-content-center">
                            <a href="products.php" class="btn btn-danger btn-lg px-5 py-3 rounded-3 shadow-sm">
                                Start Shopping
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-3">
                <div class="card border-0 shadow-sm overflow-hidden sticky-top" style="top: 20px;">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0">Account Menu</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="account.php" class="list-group-item list-group-item-action py-3">
                            <i class="fas fa-th-large me-2 text-muted"></i> Dashboard
                        </a>
                        <a href="order_history.php" class="list-group-item list-group-item-action py-3 active bg-danger border-danger">
                            <i class="fas fa-box-open me-2"></i> Order History
                        </a>
                        <a href="wishlist.php" class="list-group-item list-group-item-action py-3">
                            <i class="fas fa-heart me-2 text-muted"></i> Wishlist
                        </a>
                        <a href="logout.php" class="list-group-item list-group-item-action py-3 text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>


<style>
    .ls-1 { letter-spacing: 0.5px; }
    .x-small { font-size: 0.75rem; }
</style>

<div class="container-xxl pb-4">
    <?php 
    $breadcrumb_items = [
        ['label' => t('home'), 'url' => 'index.php'],
        ['label' => t('my_account'), 'url' => 'account.php'],
        ['label' => t('order_history')]
    ];
    include 'includes/breadcrumb.php';
    ?>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type'] ?? 'info'; ?> alert-dismissible fade show border-0 shadow-sm mb-4 mt-3" role="alert">
            <i class="fas fa-<?php echo ($_SESSION['message_type'] ?? 'info') === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
            <?php echo $_SESSION['message']; unset($_SESSION['message'], $_SESSION['message_type']); ?>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($view === 'details' && $order): ?>
        <div class="row g-3 mb-4 mt-4">
            <!-- Quick Summary Row -->
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
                        <label class="x-small fw-bold text-muted text-uppercase d-block mb-1 ls-1">Order Date</label>
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
                            <?php echo t($pStatus); ?>
                        </div>
                        <div class="text-muted x-small"><?php echo t($order['payment_method']); ?></div>
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
                            <?php echo t($sStatus); ?>
                        </div>
                        <div class="text-muted x-small text-truncate"><?php echo str_replace('_', ' ', t($order['delivery_option'])); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <?php
                            $statusClass = 'bg-secondary';
                            switch($order['status']) {
                                case 'pending': $statusClass = 'bg-warning text-dark'; break;
                                case 'confirmed': $statusClass = 'bg-primary text-white'; break;
                                case 'processing': $statusClass = 'bg-info text-white'; break;
                                case 'shipped': $statusClass = 'bg-primary'; break;
                                case 'delivered': $statusClass = 'bg-success'; break;
                                case 'cancelled': $statusClass = 'bg-danger'; break;
                            }
                            ?>
                            <span class="badge <?php echo $statusClass; ?> px-3 py-2 text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">
                                <?php echo t($order['status']); ?>
                            </span>
                            <h5 class="mb-0 fw-bold">Order #<?php echo $order['order_number']; ?></h5>
                        </div>
                        <span class="text-muted small"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></span>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php foreach ($orderItems as $item): ?>
                                <div class="list-group-item py-3 px-4 border-0 border-bottom">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 bg-light rounded border p-1" style="width: 80px; height: 80px;">
                                            <img src="<?php echo !empty($item['product_image']) ? htmlspecialchars($item['product_image']) : 'img/product-placeholder.jpg'; ?>" 
                                                 class="img-fluid rounded object-fit-contain w-100 h-100" alt="">
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($item['product_name']); ?></h6>
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
                                            <p class="text-muted small mb-0 mt-1">Quantity: <span class="text-dark fw-bold"><?php echo $item['quantity']; ?></span></p>
                                        </div>
                                        <div class="text-end">
                                            <div class="text-muted x-small"><?php echo format_price($item['price_at_purchase']); ?></div>
                                            <div class="fw-bold text-danger"><?php echo format_price($item['total_price']); ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Addresses Section -->
                <?php 
                $shipAddr = json_decode($order['shipping_address'], true);
                $billAddr = json_decode($order['billing_address'], true);
                $isSameAddress = empty($billAddr) || ($shipAddr == $billAddr);
                ?>
                <div class="row g-4 mb-4">
                    <div class="<?php echo $isSameAddress ? 'col-12' : 'col-md-6'; ?>">
                        <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                            <div class="card-header bg-white py-3">
                                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-truck me-2 text-primary"></i> Shipping Address <?php echo $isSameAddress ? '& Billing' : ''; ?></h6>
                            </div>
                            <div class="card-body p-4">
                                <h6 class="mb-2 text-dark fw-bold"><?php echo htmlspecialchars($order['customer_name']); ?></h6>
                                <p class="text-muted small mb-1"><?php echo htmlspecialchars($shipAddr['street_address'] ?? ''); ?></p>
                                <p class="text-muted small mb-1"><?php echo htmlspecialchars(($shipAddr['commune'] ?? '') . ', ' . ($shipAddr['wilaya'] ?? '')); ?></p>
                                <?php if (!empty($shipAddr['phone_number'])): ?>
                                    <p class="text-muted small mt-3 mb-0"><i class="fas fa-phone-alt me-2 text-muted"></i> <?php echo htmlspecialchars($shipAddr['phone_number']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php if (!$isSameAddress): ?>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100 rounded-4 overflow-hidden">
                            <div class="card-header bg-white py-3">
                                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-file-invoice me-2 text-primary"></i> Billing Details</h6>
                            </div>
                            <div class="card-body p-4">
                                <h6 class="mb-2 text-dark fw-bold"><?php echo htmlspecialchars($order['customer_name']); ?></h6>
                                <p class="text-muted small mb-1"><?php echo htmlspecialchars($billAddr['street_address'] ?? ''); ?></p>
                                <p class="text-muted small mb-1"><?php echo htmlspecialchars(($billAddr['commune'] ?? '') . ', ' . ($billAddr['wilaya'] ?? '')); ?></p>
                                <?php if (!empty($billAddr['phone_number'])): ?>
                                    <p class="text-muted small mt-3 mb-0"><i class="fas fa-phone-alt me-2 text-muted"></i> <?php echo htmlspecialchars($billAddr['phone_number']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Customer Notes -->
                <?php if (!empty($order['admin_notes'])): ?>
                    <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden border-start border-5 border-warning">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-comment-dots me-2 text-warning"></i> Note from Shop</h6>
                        </div>
                        <div class="card-body p-4 bg-light-subtle">
                            <p class="mb-0 small text-dark lh-base"><?php echo nl2br(htmlspecialchars($order['admin_notes'])); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <!-- Logistics Card -->
                <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
                    <div class="card-header bg-dark text-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-truck-loading me-2"></i> Shipment Tracking</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="small fw-bold text-muted text-uppercase ls-1 d-block mb-2">Tracking Number</label>
                            <?php if (!empty($order['tracking_number'])): ?>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light border-light-subtle"><i class="fas fa-barcode text-muted"></i></span>
                                    <input type="text" class="form-control bg-white border-light-subtle fw-bold text-dark" value="<?php echo htmlspecialchars($order['tracking_number']); ?>" readonly>
                                </div>
                            <?php else: ?>
                                <span class="badge bg-light text-muted border fw-normal">Awaiting shipment...</span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="small fw-bold text-muted text-uppercase ls-1 d-block mb-2">Est. Delivery Date</label>
                            <?php if (!empty($order['estimated_delivery'])): ?>
                                <div class="fw-bold text-dark small mb-0">
                                    <i class="far fa-calendar-check me-2 text-primary"></i>
                                    <?php echo date('M d, Y', strtotime($order['estimated_delivery'])); ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted small">Not scheduled yet</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="fas fa-calculator me-2 text-primary"></i> <?php echo t('order_summary'); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal:</span>
                            <span class="fw-bold"><?php echo format_price($order['subtotal']); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                            <span class="text-muted">Shipping:</span>
                            <span class="fw-bold"><?php echo format_price($order['shipping_cost']); ?></span>
                        </div>
                        <?php if (isset($order['discount_amount']) && $order['discount_amount'] > 0): ?>
                            <div class="d-flex justify-content-between mb-3 border-bottom pb-3 text-success">
                                <span>Discount:</span>
                                <span class="fw-bold">-<?php echo format_price($order['discount_amount']); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between align-items-end">
                            <span class="h5 mb-0">Total:</span>
                            <span class="h3 mb-0 text-danger fw-bold"><?php echo format_price($order['total_amount']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0"><i class="fas fa-credit-card me-2 text-primary"></i> Payment</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <span class="text-muted small">Method:</span>
                            <span class="ms-2 fw-bold text-dark"><?php echo t($order['payment_method']); ?></span>
                        </div>
                        <div class="mb-0">
                            <span class="text-muted small">Status:</span>
                            <?php
                            $paymentStatusClass = 'bg-secondary';
                            switch($order['payment_status']) {
                                case 'paid': $paymentStatusClass = 'bg-success'; break;
                                case 'pending': $paymentStatusClass = 'bg-warning text-dark'; break;
                                case 'failed': $paymentStatusClass = 'bg-danger'; break;
                            }
                            ?>
                            <span class="badge <?php echo $paymentStatusClass; ?> ms-2"><?php echo t($order['payment_status']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <?php if (in_array($order['status'], ['pending', 'confirmed', 'processing'])): ?>
                        <button class="btn btn-outline-danger py-3 rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">
                            <i class="fas fa-times-circle me-2"></i> Cancel Order
                        </button>
                    <?php endif; ?>
                    <a href="order_tracking.php?order_id=<?php echo $order['id']; ?>" class="btn btn-danger py-3 shadow-sm rounded-3">
                        <i class="fas fa-map-marker-alt me-2"></i> Track Order
                    </a>
                    <a href="order_history.php" class="btn btn-outline-secondary py-3 rounded-3">
                        <i class="fas fa-arrow-left me-2"></i> Back to Orders
                    </a>
                </div>
            </div>
        </div>

        <!-- Cancel Order Modal -->
        <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-4">
                    <div class="modal-header border-bottom py-3 px-4">
                        <h5 class="modal-title fw-bold text-dark">Cancel Order #<?php echo $order['order_number']; ?></h5>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="action" value="cancel_order">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <div class="modal-body p-4">
                            <p class="text-muted small">Are you sure you want to cancel this order? This action cannot be undone.</p>
                            <div class="mb-0">
                                <label class="form-label small fw-bold text-muted text-uppercase">Reason for cancellation</label>
                                <select name="cancel_reason" class="form-select border-light-subtle">
                                    <option value="Changed my mind">Changed my mind</option>
                                    <option value="Order taking too long">Order taking too long</option>
                                    <option value="Found a better price elsewhere">Found a better price elsewhere</option>
                                    <option value="Made a mistake in the order">Made a mistake in the order</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0 px-4 pb-4">
                            <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Keep Order</button>
                            <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">Confirm Cancellation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-9">
                <?php if (count($orders) > 0): ?>
                    <div class="row g-4">
                        <?php foreach ($orders as $order): ?>
                            <div class="col-12">
                                <div class="card border-0 shadow-sm overflow-hidden">
                                    <div class="card-header bg-light py-3 border-0">
                                        <div class="row align-items-center g-3">
                                            <div class="col-sm-3">
                                                <span class="text-muted small text-uppercase d-block mb-1">Order #</span>
                                                <span class="fw-bold text-dark"><?php echo $order['order_number']; ?></span>
                                            </div>
                                            <div class="col-sm-3">
                                                <span class="text-muted small text-uppercase d-block mb-1">Placed on</span>
                                                <span class="fw-bold text-dark"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                                            </div>
                                            <div class="col-sm-3">
                                                <span class="text-muted small text-uppercase d-block mb-1">Total</span>
                                                <div class="d-flex align-items-center">
                                                    <span class="fw-bold text-danger"><?php echo format_price($order['total_amount']); ?></span>
                                                    <?php if (isset($order['discount_amount']) && $order['discount_amount'] > 0): ?>
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle ms-2 px-2 py-1" style="font-size: 0.65rem;" title="Discount Applied">
                                                            <i class="fas fa-tag me-1"></i> DISCOUNT
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="col-sm-3 text-sm-end">
                                                <?php
                                                $statusClass = 'bg-secondary';
                                                switch($order['status']) {
                                                    case 'pending': $statusClass = 'bg-warning text-dark'; break;
                                                    case 'processing': $statusClass = 'bg-info text-white'; break;
                                                    case 'shipped': $statusClass = 'bg-primary'; break;
                                                    case 'delivered': $statusClass = 'bg-success'; break;
                                                    case 'cancelled': $statusClass = 'bg-danger'; break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?> px-3"><?php echo t($order['status']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body py-3">
                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                                            <div class="text-muted small">
                                                <i class="fas fa-truck me-2"></i> Delivery to <strong><?php echo htmlspecialchars($order['wilaya'] ?? ''); ?></strong>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <a href="?view=details&order_id=<?php echo $order['id']; ?>" class="btn btn-outline-dark btn-sm rounded-pill px-3">View Details</a>
                                                <a href="order_tracking.php?order_id=<?php echo $order['id']; ?>" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm">Track</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Order history pagination" class="mt-5">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link shadow-none" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="card border-0 shadow-sm text-center py-5 px-4">
                        <div class="mb-4">
                            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 100px; height: 100px;">
                                <i class="fas fa-shopping-bag text-muted display-4"></i>
                            </div>
                        </div>
                        <h3 class="mb-2">No orders found</h3>
                        <p class="text-muted mb-4">You haven't placed any orders yet. Start exploring our amazing collections!</p>
                        <div class="d-flex justify-content-center">
                            <a href="products.php" class="btn btn-danger btn-lg px-5 py-3 rounded-3 shadow-sm">
                                Start Shopping
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-3">
                <div class="card border-0 shadow-sm overflow-hidden sticky-top" style="top: 20px;">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0">Account Menu</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="account.php" class="list-group-item list-group-item-action py-3">
                            <i class="fas fa-th-large me-2 text-muted"></i> Dashboard
                        </a>
                        <a href="order_history.php" class="list-group-item list-group-item-action py-3 active bg-danger border-danger">
                            <i class="fas fa-box-open me-2"></i> Order History
                        </a>
                        <a href="wishlist.php" class="list-group-item list-group-item-action py-3">
                            <i class="fas fa-heart me-2 text-muted"></i> Wishlist
                        </a>
                        <a href="logout.php" class="list-group-item list-group-item-action py-3 text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
