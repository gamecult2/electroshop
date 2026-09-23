<?php
require_once 'includes/init.php';

$rawToken = trim($_POST['token'] ?? $_GET['token'] ?? '');
$tokenHash = $rawToken !== '' ? hash('sha256', $rawToken) : '';
$customer = null;
$error = '';
$completed = false;
$csrfToken = generate_csrf_token();

if ($tokenHash !== '') {
    $stmt = $GLOBALS['pdo']->prepare('SELECT id FROM customers WHERE reset_token = ? AND reset_token_expires > NOW() AND is_active = 1 LIMIT 1');
    $stmt->execute([$tokenHash]);
    $customer = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $customer) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', (string)$_POST['csrf_token'])) {
        $error = 'Your session expired. Please try again.';
    } else {
        $password = (string)($_POST['password'] ?? '');
        $confirmation = (string)($_POST['password_confirmation'] ?? '');
        $minimumLength = max(8, (int)get_setting('min_password_length', 8));
        if (strlen($password) < $minimumLength) {
            $error = "Use at least {$minimumLength} characters.";
        } elseif (!hash_equals($password, $confirmation)) {
            $error = 'The password confirmation does not match.';
        } else {
            $update = $GLOBALS['pdo']->prepare('UPDATE customers SET password = ?, reset_token = NULL, reset_token_expires = NULL, updated_at = NOW() WHERE id = ?');
            $update->execute([password_hash($password, PASSWORD_DEFAULT), $customer['id']]);
            $completed = true;
            $customer = null;
        }
    }
}

$page_title = 'Reset password';
require_once 'includes/header.php';
?>
<div class="container-xxl pb-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">
            <section class="card app-card overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    <h1 class="app-page-title fw-bold text-center mb-3">Choose a new password</h1>
                    <?php if ($completed): ?>
                        <div class="alert alert-success" role="status">Your password has been updated securely.</div>
                        <a class="btn btn-danger w-100 rounded-pill fw-bold" href="login.php">Sign in</a>
                    <?php elseif (!$customer): ?>
                        <div class="alert alert-danger" role="alert">This reset link is invalid or has expired.</div>
                        <a class="btn btn-danger w-100 rounded-pill fw-bold" href="forgot_password.php">Request a new link</a>
                    <?php else: ?>
                        <?php if ($error): ?><div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($rawToken); ?>">
                            <div class="mb-3">
                                <label for="new-password" class="form-label fw-bold">New password</label>
                                <input id="new-password" name="password" type="password" class="form-control form-control-lg rounded-3" autocomplete="new-password" required>
                            </div>
                            <div class="mb-4">
                                <label for="confirm-password" class="form-label fw-bold">Confirm new password</label>
                                <input id="confirm-password" name="password_confirmation" type="password" class="form-control form-control-lg rounded-3" autocomplete="new-password" required>
                            </div>
                            <button class="btn btn-danger btn-lg w-100 rounded-pill fw-bold" type="submit">Update password</button>
                        </form>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
