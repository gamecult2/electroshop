<?php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';
require_once '../models/User.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

// Check if user is admin
if ($_SESSION['admin_role'] !== 'admin') {
    die('Unauthorized: Only administrators can manage staff.');
}

$userModel = new User();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $role = $_POST['role'] ?? 'moderator';
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
        $error = "All fields are required.";
    } else {
        $userData = [
            'email' => $email,
            'password' => $password,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => $role,
            'is_active' => $isActive
        ];

        if ($userModel->create($userData)) {
            $success = "Staff member added successfully.";
            // Clear form
            $_POST = array();
        } else {
            $error = "Failed to add staff member. Email might already exist.";
        }
    }
}
$page_title = 'Add New Staff';
$page_heading = 'Add New Staff';

require_once 'header.php';
?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-0">
        <h5 class="mb-0 fw-bold px-2"><i class="fas fa-user-plus me-2 text-danger"></i> Add New Staff Member</h5>
        <a href="users.php" class="btn btn-light btn-sm text-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Staff List
        </a>
    </div>
    <div class="card-body p-4">
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="add_user.php">
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label for="first_name" class="form-label small fw-bold text-muted text-uppercase">First Name *</label>
                    <input type="text" id="first_name" name="first_name" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" placeholder="Enter first name" required>
                </div>
                <div class="col-md-6">
                    <label for="last_name" class="form-label small fw-bold text-muted text-uppercase">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" placeholder="Enter last name" required>
                </div>
                
                <div class="col-md-6">
                    <label for="email" class="form-label small fw-bold text-muted text-uppercase">Email Address *</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-light-subtle text-muted"><i class="fas fa-envelope"></i></span>
                        <input type="email" id="email" name="email" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="staff@qwenshop.com" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label for="password" class="form-label small fw-bold text-muted text-uppercase">Password *</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-light-subtle text-muted"><i class="fas fa-lock"></i></span>
                        <input type="password" id="password" name="password" class="form-control border-light-subtle shadow-none" placeholder="Enter secure password" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label for="role" class="form-label small fw-bold text-muted text-uppercase">Access Role</label>
                    <select id="role" name="role" class="form-select border-light-subtle shadow-none">
                        <option value="admin">Administrator (Full Access)</option>
                        <option value="manager">Manager</option>
                        <option value="moderator" selected>Moderator</option>
                        <option value="agent">Support Agent</option>
                    </select>
                </div>

                <div class="col-md-6 d-flex align-items-end">
                    <div class="card border-light-subtle bg-light p-3 rounded-3 w-100 mb-1">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                            <label class="form-check-label fw-bold small text-dark" for="is_active">Active Account</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn btn-danger px-5 py-2 fw-bold rounded-pill shadow-sm">
                    <i class="fas fa-user-plus me-2"></i> Create Staff Member
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>
