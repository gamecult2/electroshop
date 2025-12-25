<?php
// admin/login.php
// Start session only if not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/functions.php';
require_once '../db_connect.php';

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $message = 'Please fill all required fields';
        $messageType = 'error';
    } else {
        // Enforce using the new 'users' table (formerly admin_users)
        require_once '../models/User.php';
        $userModel = new User();
        
        // Check if any admin exists, if not create default (only for demo)
        $checkAdminSql = "SELECT COUNT(*) FROM users WHERE role = 'admin'";
        $checkStmt = $pdo->prepare($checkAdminSql);
        $checkStmt->execute();
        if ($checkStmt->fetchColumn() == 0) {
            $userModel->create([
                'email' => 'admin@qwenshop.dz',
                'password' => 'admin123',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'role' => 'admin'
            ]);
        }
        
        $loginResult = $userModel->login($email, $password);
        
        if ($loginResult['success']) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $loginResult['user_id'];
            $_SESSION['admin_email'] = $loginResult['email'];
            $_SESSION['admin_role'] = $loginResult['role'];
            
            header('Location: dashboard.php');
            exit;
        } else {
            $message = $loginResult['message'] ?? 'Invalid credentials';
            $messageType = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo htmlspecialchars(get_setting('site_title', 'QwenShop')); ?></title>
    <!-- Bootstrap 5 -->
    <?php 
    $bsTheme = get_setting('bootstrap_theme', 'online');
    if ($bsTheme === 'online'): ?>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php else: ?>
        <link href="../assets/css/themes/<?php echo htmlspecialchars($bsTheme); ?>/bootstrap.css" rel="stylesheet">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container-xxl">
        <div class="row min-vh-100 align-items-center justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-header bg-dark py-4 text-center">
                        <h2 class="text-white fw-bold mb-0"><?php echo htmlspecialchars(get_setting('site_title', 'QwenShop')); ?> <span class="text-danger">Admin</span></h2>
                    </div>
                    <div class="card-body p-4 p-md-5 bg-white">
                        <div class="text-center mb-4">
                            <i class="fas fa-lock text-danger fa-2x mb-3"></i>
                            <h1 class="h4 fw-bold text-dark">Welcome Back</h1>
                            <p class="text-muted small">Please enter your credentials to continue</p>
                        </div>
                        
                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $messageType === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show small py-2" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo htmlspecialchars($message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="login.php">
                            <div class="mb-3">
                                <label for="email" class="form-label small fw-bold text-muted text-uppercase">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light-subtle"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" id="email" name="email" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="admin@qwenshop.dz" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label small fw-bold text-muted text-uppercase">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light-subtle"><i class="fas fa-key text-muted"></i></span>
                                    <input type="password" id="password" name="password" class="form-control border-light-subtle shadow-none" placeholder="••••••••" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-danger btn-lg w-100 rounded-pill fw-bold shadow-sm py-2">
                                <i class="fas fa-sign-in-alt me-2"></i> Sign In
                            </button>
                        </form>
                        
                        <div class="mt-4 pt-3 border-top text-center">
                            <p class="text-muted x-small mb-0" style="font-size: 0.75rem;">
                                <i class="fas fa-info-circle me-1"></i> Default: <strong>admin@qwenshop.dz</strong> / <strong>admin123</strong>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <a href="../index.php" class="text-decoration-none text-muted small fw-bold">
                        <i class="fas fa-arrow-left me-1"></i> Back to Store
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

