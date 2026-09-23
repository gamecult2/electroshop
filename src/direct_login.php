<?php
// Alternative login that doesn't use redirect to test session persistence
require_once 'includes/init.php';
require_diagnostic_access();
require_once 'models/User.php';

// Initialize variables
$message = '';
$messageType = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Direct login form submitted - processing...");
    
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $message = t('please_fill_all_fields');
        $messageType = 'error';
        error_log("Direct login failed: empty email or password");
    } else {
        $userModel = new User();
        // Let's debug if user exists in DB
        error_log("Attempting to log in user: " . $email);
        $stmt = $GLOBALS['pdo']->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $userCount = $stmt->fetch();
        error_log("User with email '{$email}' found in DB: " . $userCount['count']);
        
        $result = $userModel->login($email, $password);

        if ($result['success']) {
            // Set session variables
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['user_email'] = $result['email'];
            $_SESSION['user_first_name'] = $result['first_name'];
            $_SESSION['user_last_name'] = $result['last_name'];

            // DEBUG: Log successful login
            error_log("Direct login successful for user ID: " . $result['user_id'] . ", email: " . $result['email']);
            error_log("Direct login - Session variables set, about to show success");
            
            // Instead of redirect, just set a flag
            $_SESSION['login_success'] = true;
            $loginSuccess = true;
        } else {
            $message = $result['message'];
            $messageType = 'error';
            error_log("Direct login failed: " . $result['message']);
        }
    }
}

// Check if login was successful (for showing success message after form processing)
$loginSuccess = isset($_SESSION['login_success']) && $_SESSION['login_success'];
if ($loginSuccess) {
    // Clear the success flag so it doesn't persist
    unset($_SESSION['login_success']);
}

$page_title = 'Direct login diagnostic';
require_once 'includes/header.php';
?>
<div class="container-xxl pb-5">
    <div class="diagnostic-shell">
        <div class="alert alert-warning border-0 shadow-sm" role="note"><i class="fas fa-flask me-2" aria-hidden="true"></i>Local diagnostic utility</div>
        <section class="card app-card">
            <div class="card-body p-4 p-md-5">
            <h1 class="app-page-title fw-bold">Direct login test</h1>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType === 'error' ? 'danger' : 'success'; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($loginSuccess): ?>
                <div class="alert alert-success">
                    <p>LOGIN SUCCESSFUL!</p>
                    <p>User ID: <?php echo (int)($_SESSION['customer_id'] ?? 0); ?></p>
                    <p>Email: <?php echo $_SESSION['user_email']; ?></p>
                    <p>Name: <?php echo $_SESSION['user_first_name'] . ' ' . $_SESSION['user_last_name']; ?></p>
                    <p>Session ID: <?php echo session_id(); ?></p>
                    <p><a href="account.php">Go to Account Page</a> - Check if session persists</p>
                </div>
            <?php else: ?>
                <form method="POST" action="" class="mt-4">
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-bold">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-danger rounded-pill px-4">Sign in without redirect</button>
                </form>
            <?php endif; ?>

            <div class="mt-4 d-flex gap-3 flex-wrap">
                <p>Don't have an account? <a href="register.php">Register</a></p>
                <a href="login.php">Back to Normal Login</a>
            </div>
            </div>
        </section>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
