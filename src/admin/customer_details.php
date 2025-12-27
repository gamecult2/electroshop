<?php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';
require_once '../models/Customer.php';

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$customerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$customerModel = new Customer();
$customer = $customerModel->getById($customerId);

if (!$customer) {
    $_SESSION['message'] = 'Customer not found.';
    $_SESSION['message_type'] = 'error';
    header('Location: customers.php');
    exit;
}

// Get addresses
$addresses = $customerModel->getCustomerAddresses($customerId);

// Get orders
if (function_exists('get_user_orders')) {
    $orders = get_user_orders($customerId, 50); // Get last 50 orders
} else {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
    $stmt->execute([$customerId]);
    $orders = $stmt->fetchAll();
}

$page_title = 'Customer Details';
$page_heading = 'Customer Profile';

require_once 'header.php';
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="h4 mb-0 fw-bold">
                <i class="fas fa-user-circle text-primary me-2"></i><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>
            </h3>
            <p class="text-muted mb-0">Customer ID: #<?php echo $customerId; ?></p>
        </div>
        <div class="btn-group">
            <a href="messages.php?customer_id=<?php echo $customerId; ?>" class="btn btn-outline-primary btn-sm rounded-pill shadow-sm me-2">
                <i class="fas fa-comment-dots me-1"></i> Send Message
            </a>
            <a href="customers.php" class="btn btn-outline-secondary btn-sm rounded-pill shadow-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Profile & Contact -->
        <div class="col-lg-4">
            <!-- Basic Info Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-info-circle text-muted me-2"></i>Personal Information</h6>
                </div>
                <div class="card-body pt-0">
                    <div class="text-center mb-4">
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                            <span class="fs-2 fw-bold text-secondary"><?php echo strtoupper(substr($customer['first_name'], 0, 1)); ?></span>
                        </div>
                        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></h5>
                        <p class="text-muted small mb-0">Member since <?php echo date('M Y', strtotime($customer['created_at'])); ?></p>
                        <span class="badge <?php echo $customer['is_active'] ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?> rounded-pill mt-2">
                            <?php echo $customer['is_active'] ? 'Active Account' : 'Inactive'; ?>
                        </span>
                    </div>
                    
                    <hr class="border-light-subtle">
                    
                    <div class="mb-3">
                        <small class="text-muted text-uppercase fw-bold x-small d-block mb-1">Email Address</small>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-envelope text-muted me-2 opacity-50"></i>
                            <a href="mailto:<?php echo htmlspecialchars($customer['email']); ?>" class="text-dark text-decoration-none fw-medium"><?php echo htmlspecialchars($customer['email']); ?></a>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted text-uppercase fw-bold x-small d-block mb-1">Phone Number</small>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-phone text-muted me-2 opacity-50"></i>
                            <span class="fw-medium"><?php echo !empty($customer['phone']) ? htmlspecialchars($customer['phone']) : '<span class="text-muted fst-italic">Not provided</span>'; ?></span>
                        </div>
                    </div>

                    <div class="mb-0">
                        <small class="text-muted text-uppercase fw-bold x-small d-block mb-1">Date of Birth</small>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-birthday-cake text-muted me-2 opacity-50"></i>
                            <span class="fw-medium"><?php echo !empty($customer['date_of_birth']) ? htmlspecialchars($customer['date_of_birth']) : '<span class="text-muted fst-italic">Not provided</span>'; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Addresses Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-map-marker-alt text-muted me-2"></i>Addresses</h6>
                    <span class="badge bg-light text-dark border"><?php echo count($addresses); ?></span>
                </div>
                <div class="card-body pt-0">
                    <?php if (empty($addresses)): ?>
                        <div class="text-center text-muted py-3 small">No addresses found.</div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($addresses as $addr): ?>
                                <div class="list-group-item px-0 border-light-subtle">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <span class="badge bg-light text-dark border small"><?php echo htmlspecialchars($addr['wilaya']); ?></span>
                                        <?php if ($addr['is_default']): ?>
                                            <span class="badge bg-primary-subtle text-primary x-small">Default</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mb-1 small fw-bold text-dark"><?php echo htmlspecialchars($addr['street_address']); ?></p>
                                    <p class="mb-0 x-small text-muted">
                                        <?php echo htmlspecialchars($addr['commune'] . ', ' . $addr['daira']); ?>
                                        <br>Postal: <?php echo htmlspecialchars($addr['postal_code']); ?>
                                    </p>
                                    <p class="mb-0 x-small text-muted mt-1"><i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($addr['phone_number']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Column: Orders -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-shopping-bag text-muted me-2"></i>Order History</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4 border-0 text-muted small text-uppercase">Order ID</th>
                                    <th class="border-0 text-muted small text-uppercase">Date</th>
                                    <th class="border-0 text-muted small text-uppercase">Total</th>
                                    <th class="border-0 text-muted small text-uppercase">Status</th>
                                    <th class="border-0 text-muted small text-uppercase text-end px-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fas fa-box-open fa-2x mb-3 opacity-25"></i>
                                            <p class="mb-0">No orders found for this customer.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td class="px-4 fw-bold text-primary">#<?php echo $order['id']; ?></td>
                                            <td class="text-muted small"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                            <td class="fw-bold text-dark"><?php echo format_price($order['total_amount']); ?></td>
                                            <td>
                                                <?php
                                                $statusClass = match($order['status']) {
                                                    'completed', 'delivered' => 'success',
                                                    'pending', 'processing' => 'warning',
                                                    'cancelled' => 'danger',
                                                    'shipped' => 'info',
                                                    default => 'secondary'
                                                };
                                                ?>
                                                <span class="badge bg-<?php echo $statusClass; ?>-subtle text-<?php echo $statusClass; ?> rounded-pill px-3">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td class="text-end px-4">
                                                <a href="admin_order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-light border shadow-xs text-secondary rounded-pill">
                                                    View Details
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

<?php require_once 'footer.php'; ?>