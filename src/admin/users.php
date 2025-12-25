<?php
// admin/users.php - Manage Staff
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';
require_once '../models/User.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$userModel = new User();
$message = '';
$messageType = '';

// Check if user has permission (only admin can manage staff)
if ($_SESSION['admin_role'] !== 'admin') {
    die('Unauthorized: Only administrators can manage staff.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_user') {
    $userId = (int)$_POST['id'];
    $userData = [
        'email' => sanitize_input($_POST['email']),
        'first_name' => sanitize_input($_POST['first_name']),
        'last_name' => sanitize_input($_POST['last_name']),
        'role' => sanitize_input($_POST['role']),
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];

    if (!empty($_POST['password'])) {
        $userData['password'] = $_POST['password'];
    }

    if ($userModel->update($userId, $userData)) {
        $message = 'Staff member updated successfully.';
        $messageType = 'success';
    } else {
        $message = 'Failed to update staff member.';
        $messageType = 'error';
    }
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $userId = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action === 'delete' && $userId != $_SESSION['admin_id']) {
        if ($userModel->delete($userId)) {
            $message = 'Staff member deleted successfully.';
            $messageType = 'success';
        } else {
            $message = 'Failed to delete staff member.';
            $messageType = 'error';
        }
    }
}

$staff = $userModel->getAll();

$page_title = 'Manage Staff';
$page_heading = 'Staff Management';

include 'header.php';
?>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo ($messageType === 'success') ? 'success' : 'danger'; ?> alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas <?php echo ($messageType === 'success') ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
                        <div><?php echo htmlspecialchars($message); ?></div>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom-0">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-users-cog me-2 text-primary"></i> Staff Members</h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-muted fw-bold rounded-pill px-3 py-2 small border border-light-subtle shadow-xs">
                            <?php echo count($staff); ?> Total
                        </span>
                        <a href="add_user.php" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm fw-bold">
                            <i class="fas fa-plus me-2"></i> Add Staff Member
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase" style="width: 80px;">ID</th>
                                    <th class="border-0 py-3 small fw-bold text-muted text-uppercase">Full Name</th>
                                    <th class="border-0 py-3 small fw-bold text-muted text-uppercase">Email Address</th>
                                    <th class="border-0 py-3 small fw-bold text-muted text-uppercase">Role</th>
                                    <th class="border-0 py-3 small fw-bold text-muted text-uppercase text-center">Status</th>
                                    <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($staff as $user): ?>
                                    <tr>
                                        <td class="px-4 text-muted small">#<?php echo $user['id']; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light text-primary p-2 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-weight: 800; font-size: 0.8rem;">
                                                    <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                                                </div>
                                                <div class="fw-bold text-dark small"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small text-muted"><i class="far fa-envelope me-1"></i> <?php echo htmlspecialchars($user['email']); ?></div>
                                        </td>
                                        <td>
                                            <?php
                                            $roleColor = 'bg-secondary';
                                            switch($user['role']) {
                                                case 'admin': $roleColor = 'bg-danger-subtle text-danger'; break;
                                                case 'manager': $roleColor = 'bg-primary-subtle text-primary'; break;
                                                case 'moderator': $roleColor = 'bg-info-subtle text-info'; break;
                                                case 'agent': $roleColor = 'bg-success-subtle text-success'; break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $roleColor; ?> rounded-pill px-3 py-1 fw-bold x-small text-uppercase">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?php echo $user['is_active'] ? 'bg-success' : 'bg-light text-muted border border-light-subtle'; ?> rounded-pill px-3 py-1 fw-bold x-small text-uppercase">
                                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td class="px-4 text-end">
                                            <div class="d-flex justify-content-end gap-1">
                                                <button type="button" 
                                                        class="btn btn-white btn-xs border border-light-subtle rounded-pill px-3 fw-bold shadow-xs text-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editUserModal" 
                                                        data-user='<?php echo json_encode($user); ?>'>
                                                    <i class="fas fa-edit me-1"></i> Edit
                                                </button>
                                                <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                                                    <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" 
                                                       class="btn btn-white btn-xs border border-light-subtle rounded-pill px-3 fw-bold shadow-xs text-danger" 
                                                       onclick="return confirm('Are you sure you want to delete this staff member?');">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Staff Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-light p-4">
                    <h5 class="modal-title fw-bold" id="editUserModalLabel"><i class="fas fa-user-edit me-2 text-primary"></i> Edit Staff Member</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="users.php">
                    <div class="modal-body p-4">
                        <input type="hidden" name="action" value="update_user">
                        <input type="hidden" name="id" id="edit_id">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted text-uppercase mb-1">First Name</label>
                                <input type="text" id="edit_first_name" name="first_name" class="form-control border-light-subtle shadow-none" required>
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold text-muted text-uppercase mb-1">Last Name</label>
                                <input type="text" id="edit_last_name" name="last_name" class="form-control border-light-subtle shadow-none" required>
                            </div>
                            <div class="col-12">
                                <label class="small fw-bold text-muted text-uppercase mb-1">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light-subtle"><i class="far fa-envelope"></i></span>
                                    <input type="email" id="edit_email" name="email" class="form-control border-light-subtle shadow-none" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="small fw-bold text-muted text-uppercase mb-1">New Password</label>
                                <input type="password" id="edit_password" name="password" class="form-control border-light-subtle shadow-none" placeholder="Leave blank to keep current">
                            </div>
                            <div class="col-12">
                                <label class="small fw-bold text-muted text-uppercase mb-1">User Role</label>
                                <select id="edit_role" name="role" class="form-select border-light-subtle shadow-none fw-bold">
                                    <option value="admin">Admin</option>
                                    <option value="manager">Manager</option>
                                    <option value="moderator">Moderator</option>
                                    <option value="agent">Agent</option>
                                </select>
                            </div>
                            <div class="col-12 pt-2">
                                <div class="p-3 bg-light rounded-3 border border-light-subtle d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="fw-bold small">Active Account</div>
                                        <div class="x-small text-muted">Allow this user to log in</div>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input shadow-none pointer" type="checkbox" name="is_active" id="edit_is_active">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold border-light-subtle" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Update Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const editUserModal = document.getElementById('editUserModal');
        if (editUserModal) {
            editUserModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const user = JSON.parse(button.getAttribute('data-user'));
                
                editUserModal.querySelector('#edit_id').value = user.id;
                editUserModal.querySelector('#edit_first_name').value = user.first_name;
                editUserModal.querySelector('#edit_last_name').value = user.last_name;
                editUserModal.querySelector('#edit_email').value = user.email;
                editUserModal.querySelector('#edit_role').value = user.role;
                editUserModal.querySelector('#edit_is_active').checked = user.is_active == 1;
                editUserModal.querySelector('#edit_password').value = '';
            });
        }
    });
    </script>

    <?php include 'footer.php'; ?>



