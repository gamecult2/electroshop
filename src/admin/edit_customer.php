<?php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';
require_once '../models/Customer.php';

// Check if user is staff
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$customerModel = new Customer();
$error = '';
$success = '';

$customerId = $_GET['id'] ?? null;
if (!$customerId) {
    redirect('admin/customers.php');
}

$customer = $customerModel->getById($customerId);
if (!$customer) {
    redirect('admin/customers.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $isBanned = isset($_POST['is_banned']) ? 1 : 0;

    if (empty($firstName) || empty($lastName)) {
        $error = "First and Last Name are required.";
    } else {
        $customerData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone,
            'is_active' => $isActive,
            'is_banned' => $isBanned
        ];

        if ($customerModel->update($customerId, $customerData)) {
            $success = "Customer updated successfully.";
            $customer = $customerModel->getById($customerId); // Refresh data
        } else {
            $error = "Failed to update customer.";
        }
    }
}
$page_title = 'Edit Customer';
$page_heading = 'Edit Customer';

require_once 'header.php';
?>

<div class="admin-section">
    <h2>
        <span>Edit Customer</span>
        <a href="customers.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Customers List</a>
    </h2>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-0">
                    <h5 class="mb-0 fw-bold px-2"><i class="fas fa-user-edit me-2 text-danger"></i> Edit Customer</h5>
                    <a href="customers.php" class="btn btn-light btn-sm text-secondary rounded-pill px-3 fw-bold">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
                <div class="card-body p-4 pt-0">
                    <form method="POST" action="edit_customer.php?id=<?php echo $customerId; ?>">
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label small fw-bold text-muted text-uppercase">First Name *</label>
                                <input type="text" id="first_name" name="first_name" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars($customer['first_name']); ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="last_name" class="form-label small fw-bold text-muted text-uppercase">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars($customer['last_name']); ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label small fw-bold text-muted text-uppercase">Email Address (Read-only)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light-subtle text-muted"><i class="fas fa-envelope"></i></span>
                                    <input type="email" id="email" class="form-control border-light-subtle shadow-none bg-light" value="<?php echo htmlspecialchars($customer['email']); ?>" readonly>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label small fw-bold text-muted text-uppercase">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light-subtle text-muted"><i class="fas fa-phone"></i></span>
                                    <input type="text" id="phone" name="phone" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars($customer['phone']); ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card border-light-subtle bg-light p-3 rounded-3 shadow-none h-100">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?php echo $customer['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label fw-bold small text-dark" for="is_active">Active Account</label>
                                    </div>
                                    <div class="form-text x-small text-muted mt-1">Allows the customer to log in and place orders.</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card border-danger-subtle bg-danger-subtle bg-opacity-10 p-3 rounded-3 shadow-none h-100">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_banned" id="is_banned" <?php echo $customer['is_banned'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label fw-bold small text-danger" for="is_banned">Banned Status</label>
                                    </div>
                                    <div class="form-text x-small text-danger-emphasis mt-1">Prevents any access to the platform.</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <button type="submit" class="btn btn-danger px-5 py-2 fw-bold rounded-pill shadow-sm">
                                <i class="fas fa-save me-1"></i> Update Customer
                            </button>
                            <a href="customers.php" class="btn btn-light px-4 py-2 fw-bold rounded-pill text-muted border shadow-xs">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
</div>

<?php require_once 'footer.php'; ?>
