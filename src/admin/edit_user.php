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

$userId = $_GET['id'] ?? null;
if (!$userId) {
    redirect('admin/users.php');
}

$user = $userModel->getById($userId);
if (!$user) {
    redirect('admin/users.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $role = $_POST['role'] ?? $user['role'];
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($firstName) || empty($lastName)) {
        $error = "Name and Email are required.";
    } else {
        $userData = [
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => $role,
            'is_active' => $isActive
        ];

        if (!empty($password)) {
            $userData['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if ($userModel->update($userId, $userData)) {
            $success = "Staff member updated successfully.";
            $user = $userModel->getById($userId); // Refresh data
        } else {
            $error = "Failed to update staff member.";
        }
    }
}
$page_title = 'Edit Staff Member';
$page_heading = 'Edit Staff Member';

require_once 'header.php';
?>

<div class="admin-section">
    <h2>
        <span>Edit Staff Member</span>
        <a href="users.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Staff List</a>
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
                    <h5 class="mb-0 fw-bold px-2"><i class="fas fa-user-shield me-2 text-danger"></i> Edit Staff Member</h5>
                    <a href="users.php" class="btn btn-light btn-sm text-secondary rounded-pill px-3 fw-bold">
                        <i class="fas fa-arrow-left me-1"></i> Back to Staff List
                    </a>
                </div>
                <div class="card-body p-4 pt-0">
                    <form method="POST" action="edit_user.php?id=<?php echo $userId; ?>">
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label small fw-bold text-muted text-uppercase">First Name *</label>
                                <input type="text" id="first_name" name="first_name" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="last_name" class="form-label small fw-bold text-muted text-uppercase">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label small fw-bold text-muted text-uppercase">Email Address *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light-subtle text-muted"><i class="fas fa-envelope"></i></span>
                                    <input type="email" id="email" name="email" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label small fw-bold text-muted text-uppercase">New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light-subtle text-muted"><i class="fas fa-key"></i></span>
                                    <input type="password" id="password" name="password" class="form-control border-light-subtle shadow-none" placeholder="Leave blank to keep current">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="role" class="form-label small fw-bold text-muted text-uppercase">Staff Role</label>
                                <select id="role" name="role" class="form-select border-light-subtle shadow-none">
                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin (Full Access)</option>
                                    <option value="manager" <?php echo $user['role'] === 'manager' ? 'selected' : ''; ?>>Manager</option>
                                    <option value="moderator" <?php echo $user['role'] === 'moderator' ? 'selected' : ''; ?>>Moderator</option>
                                    <option value="agent" <?php echo $user['role'] === 'agent' ? 'selected' : ''; ?>>Agent (Support)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <div class="card border-light-subtle bg-light p-3 rounded-3 shadow-none h-100">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label fw-bold small text-dark" for="is_active">Active Account Status</label>
                                    </div>
                                    <div class="form-text x-small text-muted mt-1">Allows this staff member to access the admin panel.</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3 mt-2">
                            <button type="submit" class="btn btn-danger px-5 py-2 fw-bold rounded-pill shadow-sm">
                                <i class="fas fa-save me-1"></i> Update Staff Member
                            </button>
                            <a href="users.php" class="btn btn-light px-4 py-2 fw-bold rounded-pill text-muted border shadow-xs">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
</div>

<?php require_once 'footer.php'; ?>
