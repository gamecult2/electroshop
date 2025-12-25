<?php
// contact.php - Contact page
require_once 'includes/init.php';
require_once 'includes/header.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $subject = sanitize_input($_POST['subject']);
    $messageContent = sanitize_input($_POST['message']);
    
    // Validation
    if (empty($name) || empty($email) || empty($subject) || empty($messageContent)) {
        $message = t('please_fill_required_fields');
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = t('invalid_email_format');
        $messageType = 'error';
    } elseif (strlen($messageContent) < 10) {
        $message = t('message_minimum_chars');
        $messageType = 'error';
    } else {
        global $pdo;
        
        $sql = "INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([$name, $email, $phone, $subject, $messageContent]);
        
        if ($result) {
            $message = t('message_sent_successfully');
            $messageType = 'success';
            $_POST = [];
        } else {
            $message = t('message_send_failed');
            $messageType = 'error';
        }
    }
}
?>

<div class="container-xxl pb-4">
    <?php 
    $breadcrumb_items = [
        ['label' => t('home'), 'url' => 'index.php'],
        ['label' => t('contact_us')]
    ];
    include 'includes/breadcrumb.php';
    ?>
    
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-body text-center py-5">
            <h1 class="fw-bold text-dark mb-3"><?php echo t('contact_us'); ?></h1>
            <p class="text-muted fs-5 mx-auto mb-0" style="max-width: 700px;">
                <?php echo t('contact_intro') ?? "Have questions? We're here to help. Send us a message or reach out through our contact methods."; ?>
            </p>
        </div>
    </div>
    
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h2 class="h5 fw-bold mb-0 text-dark">
                        <i class="fas fa-envelope text-danger me-2"></i> <?php echo t('send_message'); ?>
                    </h2>
                </div>
                <div class="card-body p-4">
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show mb-4 rounded-3 border-0 shadow-sm" role="alert">
                            <i class="fas <?php echo $messageType === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'; ?> me-2"></i>
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="needs-validation">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase" for="name"><?php echo t('name'); ?> *</label>
                                <input type="text" id="name" name="name" class="form-control form-control-lg bg-light border-0 rounded-3" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required placeholder="Your full name">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase" for="email"><?php echo t('email'); ?> *</label>
                                <input type="email" id="email" name="email" class="form-control form-control-lg bg-light border-0 rounded-3" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required placeholder="Your email address">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase" for="phone"><?php echo t('phone_number'); ?></label>
                                <input type="tel" id="phone" name="phone" class="form-control form-control-lg bg-light border-0 rounded-3" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" placeholder="Your phone (optional)">
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted text-uppercase" for="subject"><?php echo t('subject'); ?> *</label>
                                <select id="subject" name="subject" class="form-select form-select-lg bg-light border-0 rounded-3" required>
                                    <option value=""><?php echo t('select_subject'); ?></option>
                                    <option value="general" <?php echo (($_POST['subject'] ?? '') === 'general') ? 'selected' : ''; ?>><?php echo t('general_inquiry'); ?></option>
                                    <option value="order" <?php echo (($_POST['subject'] ?? '') === 'order') ? 'selected' : ''; ?>><?php echo t('order_issue'); ?></option>
                                    <option value="product" <?php echo (($_POST['subject'] ?? '') === 'product') ? 'selected' : ''; ?>><?php echo t('product_question'); ?></option>
                                    <option value="technical" <?php echo (($_POST['subject'] ?? '') === 'technical') ? 'selected' : ''; ?>><?php echo t('technical_support'); ?></option>
                                    <option value="return" <?php echo (($_POST['subject'] ?? '') === 'return') ? 'selected' : ''; ?>><?php echo t('return_exchange'); ?></option>
                                    <option value="other" <?php echo (($_POST['subject'] ?? '') === 'other') ? 'selected' : ''; ?>><?php echo t('other'); ?></option>
                                </select>
                            </div>
                            
                            <div class="col-12 mt-3">
                                <label class="form-label fw-bold small text-muted text-uppercase" for="message"><?php echo t('message'); ?> *</label>
                                <textarea id="message" name="message" class="form-control bg-light border-0 rounded-3" rows="5" required placeholder="How can we help you?"></textarea>
                            </div>
                            
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-danger btn-lg w-100 rounded-pill py-3 fw-bold shadow-sm">
                                    <i class="fas fa-paper-plane me-2"></i> <?php echo t('send_message'); ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h2 class="h5 fw-bold mb-0 text-dark">
                        <i class="fas fa-address-book text-danger me-2"></i> <?php echo t('contact_info'); ?>
                    </h2>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex mb-4">
                        <div class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 48px; height: 48px;">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h4 class="h6 fw-bold mb-1"><?php echo t('address'); ?></h4>
                            <p class="text-muted small mb-0">123 Avenue Mohamed V,<br>1st Floor, Hydra District,<br>Algiers, Algeria</p>
                        </div>
                    </div>
                    
                    <div class="d-flex mb-4">
                        <div class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 48px; height: 48px;">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div>
                            <h4 class="h6 fw-bold mb-1"><?php echo t('phone'); ?></h4>
                            <p class="text-muted small mb-0">+213 21 00 00 00<br>+213 555 00 00 00</p>
                        </div>
                    </div>
                    
                    <div class="d-flex mb-4">
                        <div class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 48px; height: 48px;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h4 class="h6 fw-bold mb-1"><?php echo t('email'); ?></h4>
                            <p class="text-muted small mb-0">contact@qwenshop.dz<br>support@qwenshop.dz</p>
                        </div>
                    </div>
                    
                    <div class="d-flex">
                        <div class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 48px; height: 48px;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h4 class="h6 fw-bold mb-1"><?php echo t('business_hours'); ?></h4>
                            <p class="text-muted small mb-0">
                                <span class="d-block text-dark fw-medium"><?php echo t('monday_friday'); ?>:</span> 9:00 - 18:00<br>
                                <span class="d-block text-dark fw-medium mt-1"><?php echo t('saturday'); ?>:</span> 9:00 - 14:00
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social Links Card -->
            <div class="card border-0 shadow-sm rounded-4 text-center p-4">
                <h3 class="h6 fw-bold mb-3"><?php echo t('follow_us') ?? 'Follow Us'; ?></h3>
                <div class="d-flex justify-content-center gap-3">
                    <a href="#" class="btn btn-outline-danger btn-sm rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="btn btn-outline-danger btn-sm rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="btn btn-outline-danger btn-sm rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="btn btn-outline-danger btn-sm rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- WhatsApp Floating Button -->
<div class="whatsapp-float position-fixed bottom-0 end-0 m-4 z-3">
    <a href="https://wa.me/<?php echo WHATSAPP_BUSINESS_NUMBER; ?>" target="_blank" class="btn btn-success rounded-pill px-4 py-3 fw-bold shadow-lg d-flex align-items-center gap-2">
        <i class="fab fa-whatsapp fs-4"></i>
        <span class="d-none d-md-inline"><?php echo t('chat_with_us'); ?></span>
    </a>
</div>

<?php require_once 'includes/footer.php'; ?>
