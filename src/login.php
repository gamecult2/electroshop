<?php
require_once 'includes/init.php';
require_once 'models/Customer.php';

// Initialize variables
$message = '';
$messageType = '';

// Process login form BEFORE including header to avoid headers already sent error
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $message = t('please_fill_all_fields');
        $messageType = 'error';
    } else {
        $customerModel = new Customer();
        $result = $customerModel->login($email, $password);

        if ($result['success']) {
            // Set session variables
            $_SESSION['customer_id'] = $result['customer_id'];
            $_SESSION['user_email'] = $result['email'];
            $_SESSION['user_first_name'] = $result['first_name'];
            $_SESSION['user_last_name'] = $result['last_name'];

            // Ensure session data is written before redirect
            session_write_close();

            // Use JavaScript redirect to ensure session persistence
            $redirectUrl = $_POST['redirect_to'] ?? 'account.php';
            echo "<script>setTimeout(function(){window.location.href = '" . addslashes($redirectUrl) . "';}, 100);</script>";
            echo "Login successful! Redirecting to your account...";
            exit();
        } else {
            $message = $result['message'];
            $messageType = 'error';
        }
    }
}

require_once 'includes/header.php';

$redirectUrl = $_GET['redirect_to'] ?? '';

// Check if there are any customers in the system
$stmt = $GLOBALS['pdo']->query("SELECT COUNT(*) as count FROM customers");
$customerCount = $stmt->fetch();
$noCustomers = $customerCount['count'] == 0;
?>

<div class="container-xxl pb-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <h1 class="h3 fw-bold text-dark mb-1"><?php echo t('sign_in'); ?></h1>
                        <p class="text-muted small">Welcome back! Please enter your details.</p>
                    </div>

                    <?php if ($noCustomers): ?>
                        <div class="alert alert-info border-0 shadow-sm rounded-3 mb-4" role="alert">
                            <i class="fas fa-info-circle me-2"></i> Note: No customers found. <a href="register.php" class="alert-link text-decoration-none">Register first</a>.
                        </div>
                    <?php endif; ?>

                    <?php if ($message): ?>
                        <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-circle me-3"></i>
                            <div><?php echo $message; ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="login.php<?php echo $redirectUrl ? '?redirect_to=' . urlencode($redirectUrl) : ''; ?>">
                        <?php if ($redirectUrl): ?>
                            <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($redirectUrl); ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold small text-muted text-uppercase"><?php echo t('email'); ?></label>
                            <input type="email" id="email" name="email" class="form-control form-control-lg bg-light border-0 rounded-3" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required placeholder="name@example.com">
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label for="password" class="form-label fw-bold small text-muted text-uppercase mb-0"><?php echo t('password'); ?></label>
                                <a href="forgot_password.php" class="small text-danger text-decoration-none fw-bold"><?php echo t('forgot_password'); ?></a>
                            </div>
                            <input type="password" id="password" name="password" class="form-control form-control-lg bg-light border-0 rounded-3" 
                                   required placeholder="••••••••">
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="remember_me" name="remember_me">
                                <label class="form-check-label small text-muted" for="remember_me"><?php echo t('remember_me'); ?></label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-danger btn-lg w-100 rounded-pill py-3 fw-bold shadow-sm mb-4">
                            <i class="fas fa-sign-in-alt me-2"></i> <?php echo t('sign_in'); ?>
                        </button>
                    </form>
                    
                    <div class="text-center">
                        <p class="text-muted small mb-0">
                            <?php echo t('not_have_account'); ?> 
                            <a href="register.php" class="text-danger fw-bold text-decoration-none"><?php echo t('register'); ?></a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
