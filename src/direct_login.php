<?php
// Alternative login that doesn't use redirect to test session persistence
require_once 'includes/init.php';
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
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direct Login Test - QwenShop</title>
</head>
<body>
<div class="auth-page">
    <div class="container-xxl">
        <div class="auth-container">
            <h1>Direct Login Test (No Redirect)</h1>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($loginSuccess): ?>
                <div class="alert alert-success">
                    <p>LOGIN SUCCESSFUL!</p>
                    <p>User ID: <?php echo $_SESSION['user_id']; ?></p>
                    <p>Email: <?php echo $_SESSION['user_email']; ?></p>
                    <p>Name: <?php echo $_SESSION['user_first_name'] . ' ' . $_SESSION['user_last_name']; ?></p>
                    <p>Session ID: <?php echo session_id(); ?></p>
                    <p><a href="account.php">Go to Account Page</a> - Check if session persists</p>
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Sign In (No Redirect)</button>
                </form>
            <?php endif; ?>

            <div class="auth-links">
                <p>Don't have an account? <a href="register.php">Register</a></p>
                <a href="login.php">Back to Normal Login</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
</content>
