<?php
// admin/customers.php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';
require_once '../models/Customer.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$customerModel = new Customer();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_customer') {
    $customerId = (int)$_POST['id'];
    $customerData = [
        'first_name' => sanitize_input($_POST['first_name']),
        'last_name' => sanitize_input($_POST['last_name']),
        'phone' => sanitize_input($_POST['phone']),
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'is_banned' => isset($_POST['is_banned']) ? 1 : 0
    ];

    if ($customerModel->update($customerId, $customerData)) {
        $message = 'Customer updated successfully.';
        $messageType = 'success';
    } else {
        $message = 'Failed to update customer.';
        $messageType = 'error';
    }
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $customerId = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action === 'ban') {
        if ($customerModel->update($customerId, ['is_active' => 0, 'is_banned' => 1])) {
            $message = 'Customer has been banned.';
            $messageType = 'success';
        } else {
            $message = 'Failed to ban customer.';
            $messageType = 'error';
        }
    } elseif ($action === 'unban') {
        if ($customerModel->update($customerId, ['is_active' => 1, 'is_banned' => 0])) {
            $message = 'Customer has been unbanned.';
            $messageType = 'success';
        } else {
            $message = 'Failed to unban customer.';
            $messageType = 'error';
        }
    }
}

$customers = $customerModel->getAll();

// Set page title and heading variables for the template
$page_title = 'Manage Customers';
$page_heading = 'Manage Customers';

// Include the shared header template
include 'header.php';
?>

<div class="card border-0 shadow-sm overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-0">
        <h2 class="h5 fw-bold mb-0 text-dark"><i class="fas fa-users me-2 text-danger"></i> All Customers <span class="badge bg-light text-muted border ms-2 small fw-normal"><?php echo count($customers); ?> Total</span></h2>
    </div>
    
    <?php if ($message): ?>
        <div class="px-4 pt-3">
            <div class="alert alert-<?php echo $messageType === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show border-0 shadow-sm mb-0" role="alert">
                <i class="fas fa-<?php echo $messageType === 'error' ? 'exclamation-circle' : 'check-circle'; ?> me-2"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="px-3 border-0">ID</th>
                    <th class="border-0">Customer Info</th>
                    <th class="border-0">Email Address</th>
                    <th class="border-0">Joined Date</th>
                    <th class="border-0">Status</th>
                    <th class="border-0 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted"><i class="fas fa-users-slash fa-3x opacity-25 mb-3"></i><br>No customers found.</td></tr>
                <?php else: ?>
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td class="px-3 fw-bold text-muted x-small">#<?php echo htmlspecialchars($customer['id']); ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($customer['username']); ?></div>
                                <div class="x-small text-muted"><?php echo htmlspecialchars($customer['phone'] ?? 'No phone'); ?></div>
                            </td>
                            <td class="small fw-medium"><?php echo htmlspecialchars($customer['email']); ?></td>
                            <td>
                                <div class="small text-dark fw-medium"><?php echo date('Y-m-d', strtotime($customer['created_at'])); ?></div>
                                <div class="x-small text-muted"><?php echo date('H:i', strtotime($customer['created_at'])); ?></div>
                            </td>
                            <td>
                                <span class="badge rounded-pill px-3 py-2 fw-bold <?php echo $customer['is_active'] ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis'; ?>" style="font-size: 10px;">
                                    <?php echo $customer['is_active'] ? 'ACTIVE' : 'BANNED'; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group shadow-sm rounded">
                                    <a href="customer_details.php?id=<?php echo $customer['id']; ?>" class="btn btn-white btn-sm border-light-subtle text-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button onclick='openEditModal(<?php echo json_encode($customer); ?>)' class="btn btn-white btn-sm border-light-subtle text-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($customer['is_active']): ?>
                                        <a href="customers.php?action=ban&id=<?php echo $customer['id']; ?>" class="btn btn-white btn-sm border-light-subtle text-danger" title="Ban Customer" onclick="return confirm('Are you sure you want to ban this customer?');">
                                            <i class="fas fa-ban"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="customers.php?action=unban&id=<?php echo $customer['id']; ?>" class="btn btn-white btn-sm border-light-subtle text-success" title="Unban Customer">
                                            <i class="fas fa-undo"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 py-3 px-4 bg-light">
                <h5 class="modal-title fw-bold text-dark" id="editCustomerModalLabel"><i class="fas fa-user-edit me-2 text-danger"></i>Edit Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="customers.php">
                <div class="modal-body p-4">
                    <input type="hidden" name="action" value="update_customer">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="edit_first_name" class="form-label small fw-bold text-muted text-uppercase">First Name</label>
                            <input type="text" class="form-control border-light-subtle shadow-none" id="edit_first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_last_name" class="form-label small fw-bold text-muted text-uppercase">Last Name</label>
                            <input type="text" class="form-control border-light-subtle shadow-none" id="edit_last_name" name="last_name" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="edit_email" class="form-label small fw-bold text-muted text-uppercase">Email Address (Read-only)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-light-subtle"><i class="fas fa-envelope text-muted"></i></span>
                            <input type="email" class="form-control bg-light border-light-subtle shadow-none" id="edit_email" readonly>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="edit_phone" class="form-label small fw-bold text-muted text-uppercase">Phone Number</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-light-subtle"><i class="fas fa-phone text-muted"></i></span>
                            <input type="text" class="form-control border-light-subtle shadow-none" id="edit_phone" name="phone">
                        </div>
                    </div>

                    <div class="card border-0 bg-light p-3 rounded-3 shadow-none">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active">
                            <label class="form-check-label fw-bold small text-dark" for="edit_is_active">Active Account</label>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="is_banned" id="edit_is_banned">
                            <label class="form-check-label fw-bold small text-danger" for="edit_is_banned">Banned Status</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold text-muted border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let editModal;
document.addEventListener('DOMContentLoaded', function() {
    editModal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
});

function openEditModal(customer) {
    document.getElementById('edit_id').value = customer.id;
    document.getElementById('edit_first_name').value = customer.first_name || '';
    document.getElementById('edit_last_name').value = customer.last_name || '';
    document.getElementById('edit_email').value = customer.email || '';
    document.getElementById('edit_phone').value = customer.phone || '';
    document.getElementById('edit_is_active').checked = customer.is_active == 1;
    document.getElementById('edit_is_banned').checked = customer.is_banned == 1;
    
    editModal.show();
}
</script>

<?php include 'footer.php'; ?>
