<?php
require_once 'includes/init.php';

if (is_logged_in()) {
    header('Location: account.php');
    exit;
}

$submitted = false;
$error = '';
$csrfToken = generate_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', (string)$_POST['csrf_token'])) {
        $error = 'Your session expired. Please try again.';
    } else {
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        if (!$email) {
            $error = 'Enter a valid email address.';
        } else {
            $stmt = $GLOBALS['pdo']->prepare('SELECT id, email, first_name FROM customers WHERE email = ? AND is_active = 1 LIMIT 1');
            $stmt->execute([$email]);
            $customer = $stmt->fetch();

            if ($customer) {
                $rawToken = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $rawToken);
                $update = $GLOBALS['pdo']->prepare('UPDATE customers SET reset_token = ?, reset_token_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?');
                $update->execute([$tokenHash, $customer['id']]);
                $resetUrl = SITE_URL . '/reset_password.php?token=' . urlencode($rawToken);
                $body = '<p>Hello ' . htmlspecialchars($customer['first_name']) . ',</p>'
                    . '<p>Use the link below within one hour to reset your password:</p>'
                    . '<p><a href="' . htmlspecialchars($resetUrl) . '">Reset password</a></p>'
                    . '<p>If you did not request this, you can ignore this email.</p>';
                send_email($customer['email'], 'Reset your ' . get_setting('site_title', SITE_TITLE) . ' password', $body);
            }

            $submitted = true;
        }
    }
}

$page_title = 'Forgot password';
require_once 'includes/header.php';
?>
<div class="container-xxl pb-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">
            <section class="card app-card overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                            <i class="fas fa-key text-danger fs-3" aria-hidden="true"></i>
                        </div>
                        <h1 class="app-page-title fw-bold">Forgot your password?</h1>
                        <p class="text-muted mb-0">Enter your account email and we’ll send a secure reset link.</p>
                    </div>

                    <?php if ($submitted): ?>
                        <div class="alert alert-success" role="status">
                            If an active account matches that address, a reset link has been sent.
                        </div>
                        <a class="btn btn-outline-dark w-100 rounded-pill" href="login.php">Return to sign in</a>
                    <?php else: ?>
                        <?php if ($error): ?><div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                        <form method="post" novalidate>
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                            <div class="mb-4">
                                <label for="reset-email" class="form-label fw-bold">Email address</label>
                                <input id="reset-email" name="email" type="email" class="form-control form-control-lg rounded-3" autocomplete="email" required>
                            </div>
                            <button class="btn btn-danger btn-lg w-100 rounded-pill fw-bold" type="submit">Send reset link</button>
                        </form>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
