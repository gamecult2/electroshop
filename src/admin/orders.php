<?php
// admin/orders.php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';
require_once '../models/Order.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$orderModel = new Order();
$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['message_type'] ?? '';
unset($_SESSION['message']);
unset($_SESSION['message_type']);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $orderId = (int)$_POST['order_id'];
    $status = sanitize_input($_POST['status']);
    $adminId = $_SESSION['admin_id'] ?? null;
    
    if ($orderModel->updateStatus($orderId, $status, $adminId, 'Status updated from main list.')) {
        $_SESSION['message'] = 'Order status updated successfully.';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Failed to update order status.';
        $_SESSION['message_type'] = 'error';
    }
    // Redirect to avoid form resubmission
    header('Location: orders.php');
    exit;
}

$orders = $orderModel->getAllOrdersWithUserDetails();
$order_statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'];

// Set page title and heading variables for the template
$page_title = 'Order Management';
$page_heading = 'Order Management';

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

            <div class="card border-0 shadow-sm overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h2 class="h5 fw-bold mb-0 text-dark"><i class="fas fa-shopping-bag me-2 text-danger"></i> All Orders <span class="badge bg-light text-muted border ms-2 small fw-normal"><?php echo count($orders); ?> Total</span></h2>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3 border-0">Order ID</th>
                                <th class="border-0">Reference</th>
                                <th class="border-0">Customer</th>
                                <th class="border-0">Date & Time</th>
                                <th class="border-0">Total Amount</th>
                                <th class="border-0">Status</th>
                                <th class="border-0 text-center">Actions</th>
                            </tr>
                        </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-receipt fa-3x opacity-25 mb-3"></i><br>No orders found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td class="px-3 fw-bold text-muted x-small">#<?php echo htmlspecialchars($order['id'] ?? ''); ?></td>
                                    <td class="fw-bold text-dark small"><?php echo htmlspecialchars($order['order_number'] ?? 'N/A'); ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($order['username'] ?? 'Guest'); ?></div>
                                    </td>
                                    <td>
                                        <div class="small text-dark fw-medium"><?php echo date('Y-m-d', strtotime($order['created_at'] ?? 'now')); ?></div>
                                        <div class="x-small text-muted"><?php echo date('H:i', strtotime($order['created_at'] ?? 'now')); ?></div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="fw-bold text-danger"><?php echo format_price($order['total_amount']); ?></span>
                                            <?php if (isset($order['discount_amount']) && $order['discount_amount'] > 0): ?>
                                                <i class="fas fa-tag text-success small ms-2" title="Discount Applied: <?php echo format_price($order['discount_amount']); ?>"></i>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <form method="POST" action="orders.php" class="d-flex gap-2 align-items-center">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="status" class="form-select form-select-sm rounded-pill fw-bold text-uppercase px-3 shadow-none <?php 
                                                echo match($order['status']) {
                                                    'pending' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                                    'processing' => 'bg-info-subtle text-info-emphasis border-info-subtle',
                                                    'shipped' => 'bg-primary-subtle text-primary-emphasis border-primary-subtle',
                                                    'delivered' => 'bg-success-subtle text-success-emphasis border-success-subtle',
                                                    'cancelled' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
                                                    default => 'bg-secondary-subtle'
                                                };
                                            ?>" style="font-size: 10px; width: 125px;" onchange="this.form.submit()">
                                                <?php foreach ($order_statuses as $status): ?>
                                                    <option value="<?php echo $status; ?>" <?php echo $order['status'] === $status ? 'selected' : ''; ?>>
                                                        <?php echo ucfirst($status); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-dark btn-sm rounded-pill px-3 fw-bold shadow-sm d-none" style="font-size: 10px;">Update</button>
                                        </form>
                                    </td>
                                    <td class="text-center">
                                        <a href="admin_order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-light rounded-circle text-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
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

    <!-- Include the shared footer template -->
    <?php include 'footer.php'; ?>


