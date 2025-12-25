<?php
require_once 'includes/init.php';
require_once 'models/Customer.php';

if (isset($_SESSION['customer_id'])) {
    redirect('account.php');
}

require_once 'includes/header.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerData = [
        'email' => sanitize_input($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'first_name' => sanitize_input($_POST['first_name'] ?? ''),
        'last_name' => sanitize_input($_POST['last_name'] ?? ''),
        'phone' => sanitize_input($_POST['phone'] ?? ''),
        'date_of_birth' => $_POST['date_of_birth'] ?? null,
        'gender' => $_POST['gender'] ?? null
    ];
    
    // Validate input
    if (empty($customerData['email']) || empty($customerData['password']) || empty($customerData['first_name']) || empty($customerData['last_name'])) {
        $message = t('please_fill_all_fields');
        $messageType = 'error';
    } elseif (!filter_var($customerData['email'], FILTER_VALIDATE_EMAIL)) {
        $message = t('invalid_email_format');
        $messageType = 'error';
    } elseif ($customerData['password'] !== $customerData['confirm_password']) {
        $message = t('passwords_do_not_match');
        $messageType = 'error';
    } elseif (strlen($customerData['password']) < 6) {
        $message = t('password_too_short');
        $messageType = 'error';
    } else {
        $customerModel = new Customer();
        $result = $customerModel->register($customerData);
        
        if ($result['success']) {
            $message = $result['message'];
            $messageType = 'success';
            // Clear form data
            $userData = array_fill_keys(array_keys($userData), '');
        } else {
            $message = $result['message'];
            $messageType = 'error';
        }
    }
}
?>

<div class="container-xxl pb-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-5">
                        <h1 class="h3 fw-bold text-dark mb-1"><?php echo t('create_account'); ?></h1>
                        <p class="text-muted small">Join us to experience the best electronics shopping!</p>
                    </div>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType === 'error' ? 'danger' : 'success'; ?> border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center" role="alert">
                            <i class="fas <?php echo $messageType === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'; ?> me-3"></i>
                            <div><?php echo $message; ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="register.php">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label fw-bold small text-muted text-uppercase"><?php echo t('first_name'); ?> *</label>
                                <input type="text" id="first_name" name="first_name" class="form-control form-control-lg bg-light border-0 rounded-3" 
                                       value="<?php echo htmlspecialchars($customerData['first_name'] ?? ''); ?>" required placeholder="John">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="last_name" class="form-label fw-bold small text-muted text-uppercase"><?php echo t('last_name'); ?> *</label>
                                <input type="text" id="last_name" name="last_name" class="form-control form-control-lg bg-light border-0 rounded-3" 
                                       value="<?php echo htmlspecialchars($customerData['last_name'] ?? ''); ?>" required placeholder="Doe">
                            </div>
                            
                            <div class="col-12">
                                <label for="email" class="form-label fw-bold small text-muted text-uppercase"><?php echo t('email'); ?> *</label>
                                <input type="email" id="email" name="email" class="form-control form-control-lg bg-light border-0 rounded-3" 
                                       value="<?php echo htmlspecialchars($customerData['email'] ?? ''); ?>" required placeholder="name@example.com">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="phone" class="form-label fw-bold small text-muted text-uppercase"><?php echo t('phone_number'); ?></label>
                                <input type="tel" id="phone" name="phone" class="form-control form-control-lg bg-light border-0 rounded-3" 
                                       value="<?php echo htmlspecialchars($customerData['phone'] ?? ''); ?>" placeholder="+213 5XX XX XX XX">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="date_of_birth" class="form-label fw-bold small text-muted text-uppercase"><?php echo t('date_of_birth'); ?></label>
                                <input type="date" id="date_of_birth" name="date_of_birth" class="form-control form-control-lg bg-light border-0 rounded-3" 
                                       value="<?php echo htmlspecialchars($customerData['date_of_birth'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="gender" class="form-label fw-bold small text-muted text-uppercase"><?php echo t('gender'); ?></label>
                                <select id="gender" name="gender" class="form-select form-select-lg bg-light border-0 rounded-3">
                                    <option value=""><?php echo t('select'); ?></option>
                                    <option value="male" <?php echo ($customerData['gender'] ?? '') === 'male' ? 'selected' : ''; ?>><?php echo t('male'); ?></option>
                                    <option value="female" <?php echo ($customerData['gender'] ?? '') === 'female' ? 'selected' : ''; ?>><?php echo t('female'); ?></option>
                                    <option value="other" <?php echo ($customerData['gender'] ?? '') === 'other' ? 'selected' : ''; ?>><?php echo t('other'); ?></option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-bold small text-muted text-uppercase"><?php echo t('password'); ?> *</label>
                                <input type="password" id="password" name="password" class="form-control form-control-lg bg-light border-0 rounded-3" 
                                       required placeholder="••••••••">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label fw-bold small text-muted text-uppercase"><?php echo t('confirm_password'); ?> *</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control form-control-lg bg-light border-0 rounded-3" 
                                       required placeholder="••••••••">
                            </div>
                            
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-danger btn-lg w-100 rounded-pill py-3 fw-bold shadow-sm">
                                    <i class="fas fa-user-plus me-2"></i> <?php echo t('create_account'); ?>
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="text-muted small mb-0">
                            <?php echo t('already_have_account'); ?> 
                            <a href="login.php" class="text-danger fw-bold text-decoration-none"><?php echo t('sign_in'); ?></a>
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
