<?php
// includes/functions.php - Enhanced functions file for QwenShop
error_log("Loaded functions.php");

// Note: Session management is handled in individual pages that require it

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function create_slug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9 -]+/', '', $string); // Remove unwanted characters
    $string = preg_replace('/ /', '-', $string); // Replace spaces with hyphens
    $string = preg_replace('/-+/', '-', $string); // Replace multiple hyphens with a single hyphen
    return $string;
}

function format_price($price) {
    $symbol = get_setting('currency_symbol', 'DA');
    return number_format($price, 0, '', ',') . ' ' . $symbol;
}

function redirect($location) {
    // Check if headers have already been sent
    if (headers_sent($file, $line)) {
        error_log("Headers already sent in {$file} on line {$line}. Cannot redirect to {$location}");

        // Fallback method: JavaScript redirect
        echo "<script> window.location.href = '" . addslashes(SITE_URL . "/" . $location) . "'; </script>";
        echo "<noscript><meta http-equiv='refresh' content='0;url=" . SITE_URL . "/" . $location . "'></noscript>";
        exit();
    }

    // If location is a simple page name (like 'account.php'), use relative redirect
    // This ensures session is maintained properly
    if (strpos($location, '://') === false && strpos($location, '/') !== 0 && strpos($location, 'http') !== 0) {
        // Internal page redirect - use relative path
        header("Location: {$location}");
    } else if (strpos($location, 'http') === 0) {
        // If location is already a full URL, redirect directly
        header("Location: {$location}");
    } else {
        // Construct full URL using SITE_URL
        header("Location: " . SITE_URL . "/{$location}");
    }
    exit();
}

function is_logged_in() {
    return isset($_SESSION['user_id']) || isset($_SESSION['customer_id']);
}

function is_admin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin';
}

function is_staff() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function get_current_user_id() {
    return $_SESSION['user_id'] ?? $_SESSION['customer_id'] ?? null;
}

function require_login() {
    $isLoggedIn = is_logged_in();
    error_log("require_login() called - is_logged_in() result: " . ($isLoggedIn ? 'YES' : 'NO'));
    if (!$isLoggedIn) {
        error_log("User not logged in, redirecting to login.php");
        redirect('login.php');
    }
    error_log("User is logged in, proceeding to page content");
}

function check_csrf_token() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token mismatch');
    }
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function set_message($message, $type = 'info') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

function get_setting($key, $default = null) {
    global $pdo;
    static $settings = null;
    
    if ($settings === null) {
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (PDOException $e) {
            $settings = [];
        }
    }
    
    return isset($settings[$key]) ? $settings[$key] : $default;
}

function get_message() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info';
        
        echo '<div class="alert alert-' . $type . '">' . $message . '</div>';
        
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}

function get_language() {
    return 'en';
}

function load_language_strings($lang = null) {
    $lang = DEFAULT_LANGUAGE;
    
    $lang_file = __DIR__ . '/../languages/' . $lang . '.json';
    if (file_exists($lang_file)) {
        $strings = json_decode(file_get_contents($lang_file), true);
        return $strings ?: [];
    }
    
    return [];
}

function t($key, $params = []) {
    $strings = load_language_strings();
    $text = isset($strings[$key]) ? $strings[$key] : $key;
    
    // Replace placeholders with values
    foreach ($params as $placeholder => $value) {
        $text = str_replace('%{' . $placeholder . '}', $value, $text);
    }
    
    return $text;
}

// New Functions for Enhanced Features

// Search History Functions
function save_search_history($customerId, $searchTerm, $resultCount) {
    global $pdo;
    
    $sql = "INSERT INTO search_history (customer_id, search_term, result_count) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$customerId, $searchTerm, $resultCount]);
}

function get_search_history($customerId, $limit = 5) {
    global $pdo;
    
    $sql = "SELECT search_term, search_date, result_count 
            FROM search_history 
            WHERE customer_id = ? 
            ORDER BY search_date DESC 
            LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customerId, $limit]);
    return $stmt->fetchAll();
}

// Wishlist Functions
function is_wishlisted($userId, $productId) {
    global $pdo;
    
    $sql = "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $productId]);
    return $stmt->fetch() !== false;
}

function toggle_wishlist($userId, $productId) {
    global $pdo;
    
    if (is_wishlisted($userId, $productId)) {
        // Remove from wishlist
        $sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$userId, $productId]);
    } else {
        // Add to wishlist
        $sql = "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$userId, $productId]);
    }
}

function get_wishlist_count($customerId) {
    global $pdo;
    
    $sql = "SELECT COUNT(*) FROM wishlist WHERE customer_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customerId]);
    return $stmt->fetchColumn();
}

// Review Functions
function submit_review($userId, $productId, $rating, $title, $comment) {
    global $pdo;
    
    // Check if user purchased this product
    $purchaseCheckSql = "SELECT COUNT(*) FROM order_items oi 
                         JOIN orders o ON oi.order_id = o.id 
                         WHERE o.user_id = ? AND oi.product_id = ? 
                         AND o.status = 'delivered'";
    $stmt = $pdo->prepare($purchaseCheckSql);
    $stmt->execute([$userId, $productId]);
    $isVerifiedPurchase = $stmt->fetchColumn() > 0;
    
    $sql = "INSERT INTO reviews (product_id, user_id, rating, title, review_text, is_verified_purchase, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $productId, $userId, $rating, $title, $comment, $isVerifiedPurchase
    ]);
}

function get_product_reviews($productId, $limit = 10) {
    global $pdo;
    
    $sql = "SELECT r.*, c.first_name, c.last_name 
            FROM reviews r
            JOIN customers c ON r.customer_id = c.id
            WHERE r.product_id = ? AND r.is_approved = 1
            ORDER BY r.created_at DESC 
            LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$productId, $limit]);
    return $stmt->fetchAll();
}

function get_average_rating($productId) {
    global $pdo;
    
    $sql = "SELECT AVG(rating) as average_rating, COUNT(*) as total_reviews 
            FROM reviews 
            WHERE product_id = ? AND is_approved = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$productId]);
    return $stmt->fetch();
}

// Newsletter Functions
function subscribe_newsletter($email) {
    global $pdo;
    
    // Check if already subscribed
    $checkSql = "SELECT id FROM newsletter_subscribers WHERE email = ?";
    $stmt = $pdo->prepare($checkSql);
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => t('already_subscribed')];
    }
    
    // Generate confirmation token
    $token = bin2hex(random_bytes(32));
    
    $sql = "INSERT INTO newsletter_subscribers (email, confirmation_token) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$email, $token]);
    
    if ($result) {
        // In a real application, send confirmation email
        // send_confirmation_email($email, $token);
        return ['success' => true, 'message' => t('subscription_confirmed')];
    }
    
    return ['success' => false, 'message' => t('subscription_failed')];
}

function send_newsletter($subject, $content) {
    global $pdo;
    
    // Insert newsletter campaign
    $sql = "INSERT INTO newsletter_campaigns (subject, content) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$subject, $content]);
    $campaignId = $pdo->lastInsertId();
    
    // Get all confirmed subscribers
    $subscribersSql = "SELECT email FROM newsletter_subscribers WHERE is_confirmed = 1 AND unsubscribed_at IS NULL";
    $subscribersStmt = $pdo->prepare($subscribersSql);
    $subscribersStmt->execute();
    $subscribers = $subscribersStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // In a real app, this would send emails
    $sentCount = count($subscribers);
    
    // Update sent count
    $updateSql = "UPDATE newsletter_campaigns SET sent_count = ? WHERE id = ?";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([$sentCount, $campaignId]);
    
    return ['success' => true, 'sent_count' => $sentCount];
}

// Coupon Functions
function validate_coupon($code, $userId = null, $cartAmount = 0, $productIds = []) {
    global $pdo;

    // Validate input parameters
    if (empty($code)) {
        return ['valid' => false, 'message' => t('coupon_invalid')];
    }

    $sql = "SELECT * FROM coupons
            WHERE code = ?
            AND is_active = 1
            AND (valid_until IS NULL OR valid_until >= CURDATE())
            AND (valid_from IS NULL OR valid_from <= CURDATE())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();

    if (!$coupon) {
        return ['valid' => false, 'message' => t('coupon_invalid')];
    }

    // Check minimum order amount
    $minimumOrderAmount = $coupon['minimum_order_amount'] ?? 0;
    if ($minimumOrderAmount > 0 && $cartAmount < $minimumOrderAmount) {
        return ['valid' => false, 'message' => t('min_order_not_met', ['amount' => format_price($minimumOrderAmount)])];
    }

    // Check usage limits
    $usageLimit = $coupon['usage_limit'] ?? 0;
    $usedCount = $coupon['used_count'] ?? 0;
    if ($usageLimit > 0 && $usedCount >= $usageLimit) {
        return ['valid' => false, 'message' => t('coupon_usage_limit_reached')];
    }

    if ($userId) {
        $usageLimitPerUser = $coupon['usage_limit_per_user'] ?? 0;
        if ($usageLimitPerUser > 0) {
            $userUsageSql = "SELECT COUNT(*) FROM coupon_usage WHERE coupon_id = ? AND user_id = ?";
            $userUsageStmt = $pdo->prepare($userUsageSql);
            $userUsageStmt->execute([$coupon['id'], $userId]);
            $userUsageCount = $userUsageStmt->fetchColumn();

            if ($userUsageCount >= $usageLimitPerUser) {
                return ['valid' => false, 'message' => t('coupon_usage_limit_per_user_reached')];
            }
        }
    }

    return ['valid' => true, 'coupon' => $coupon];
}

// Cart Functions
function get_cart_total() {
    global $pdo;
    
    $customerId = get_current_user_id();
    $sessionId = session_id();
    
    $sql = "SELECT SUM(quantity * price_at_time) as total 
            FROM shopping_cart 
            WHERE " . ($customerId ? "customer_id = ?" : "session_id = ?");
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($customerId ? [$customerId] : [$sessionId]);
    $result = $stmt->fetch();
    
    return $result['total'] ? $result['total'] : 0;
}

// Order Functions
function get_user_orders($customerId, $limit = 10) {
    global $pdo;
    
    $sql = "SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customerId, $limit]);
    return $stmt->fetchAll();
}

function get_order_status_history($orderId) {
    global $pdo;
    
    $sql = "SELECT osh.*, u.first_name, u.last_name 
            FROM order_status_history osh
            LEFT JOIN users u ON osh.admin_user_id = u.id
            WHERE osh.order_id = ?
            ORDER BY osh.created_at ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}

// Location Functions (Algeria-specific)
function get_wilayas() {
    return [
        'Alger', 'Oran', 'Constantine', 'Blida', 'Batna', 'Sétif', 'Djelfa', 'Annaba', 
        'Sidi Bel Abbès', 'Biskra', 'Tiaret', 'Tébessa', 'Bejaia', 'Bouira', 'Tlemcen', 
        'El Oued', 'Jijel', 'Setif', 'Saïda', 'Skikda', 'Msila', 'Mostaganem', 'Mascara', 
        'Collo', 'Tizi Ouzou', 'Béchar', 'I-n-Salah', 'Tamanghasset', 'El Bayadh', 'Djanet', 
        'Ghardaïa', 'Relizane', 'Ouargla', 'Barika', 'Tindouf', 'Tissemsilt', 'El Tarf', 
        'Tamanrasset', 'Bordj Bou Arreridj', 'Boumerdes', 'El M\'ghair', 'El Menia', 
        'Ain Defla', 'Naama', 'Ain Temouchent', 'Guelma', 'Djelfa', 'Laghouat', 
        'Oum El Bouaghi', 'Khenchela', 'Souk Ahras', 'Tipaza', 'Mila', 'Aïn El Hadjel'
    ];
}

// Email Functions (Simplified)
function send_email($to, $subject, $body) {
    // In a real application, implement proper SMTP email sending
    // Using PHPMailer or similar
    
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . EMAIL_FROM_ADDRESS,
        'Reply-To: ' . EMAIL_FROM_ADDRESS
    ];
    
    return mail($to, $subject, $body, implode("\r\n", $headers));
}

// File Upload Functions
function upload_file($file, $uploadDir, $allowedTypes = [], $maxSize = MAX_FILE_SIZE) {
    // Check if file was uploaded without errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload error'];
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File too large'];
    }
    
    // Get file extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Check file type
    if (!empty($allowedTypes) && !in_array($ext, $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }
    
    // Generate unique filename
    $fileName = uniqid() . '.' . $ext;
    $destination = $uploadDir . $fileName;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'filename' => $fileName];
    } else {
        return ['success' => false, 'error' => 'Failed to move uploaded file'];
    }
}

function upload_image($file, $subDir = '') {
    // Define base upload path relative to src/
    // UPLOAD_DIR is absolute path to src/uploads/
    
    $targetDir = UPLOAD_DIR;
    $relativePath = 'uploads/';
    
    if ($subDir) {
        $targetDir .= $subDir . '/';
        $relativePath .= $subDir . '/';
    }
    
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'ico', 'svg'];
    $result = upload_file($file, $targetDir, $allowedTypes);
    
    if ($result['success']) {
        return $relativePath . $result['filename'];
    }
    
    return null;
}

// Rate Limiting Functions
function is_rate_limited($ip, $endpoint) {
    global $pdo;
    
    $sql = "SELECT COUNT(*) FROM rate_limits 
            WHERE ip_address = ? 
            AND endpoint = ? 
            AND expires_at > NOW()";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$ip, $endpoint]);
    
    return $stmt->fetchColumn() >= RATE_LIMIT_PER_HOUR;
}

function record_api_request($ip, $endpoint) {
    global $pdo;
    
    $sql = "INSERT INTO rate_limits (ip_address, endpoint, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE))
            ON DUPLICATE KEY UPDATE 
            request_count = request_count + 1,
            last_request_at = NOW(),
            expires_at = DATE_ADD(NOW(), INTERVAL ? MINUTE)";
    
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$ip, $endpoint, RATE_LIMIT_WINDOW_MINUTES, RATE_LIMIT_WINDOW_MINUTES]);
}

// Logging Functions
function log_admin_action($adminId, $action, $entityType, $entityId, $oldValues = null, $newValues = null) {
    global $pdo;
    
    $sql = "INSERT INTO admin_logs (admin_user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    return $pdo->prepare($sql)->execute([
        $adminId, $action, $entityType, $entityId, 
        $oldValues ? json_encode($oldValues) : null, 
        $newValues ? json_encode($newValues) : null,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null
    ]);
}

// Security Functions
function generate_two_fa_secret() {
    return bin2hex(random_bytes(10));
}

function generate_backup_codes() {
    $codes = [];
    for ($i = 0; $i < 10; $i++) {
        $codes[] = bin2hex(random_bytes(4)); // 8 character codes
    }
    return json_encode($codes);
}

// Cookie Consent Functions
function user_accepted_cookies() {
    return isset($_COOKIE['cookie_consent']);
}

// Data Export Functions
function export_user_data($userId) {
    global $pdo;
    
    // Get user account data
    $userSql = "SELECT * FROM users WHERE id = ?";
    $userStmt = $pdo->prepare($userSql);
    $userStmt->execute([$userId]);
    $user = $userStmt->fetch();
    
    // Get user orders
    $ordersSql = "SELECT * FROM orders WHERE user_id = ?";
    $ordersStmt = $pdo->prepare($ordersSql);
    $ordersStmt->execute([$userId]);
    $orders = $ordersStmt->fetchAll();
    
    // Get user addresses
    $addressesSql = "SELECT * FROM user_addresses WHERE user_id = ?";
    $addressesStmt = $pdo->prepare($addressesSql);
    $addressesStmt->execute([$userId]);
    $addresses = $addressesStmt->fetchAll();
    
    $data = [
        'user_profile' => $user,
        'orders' => $orders,
        'addresses' => $addresses
    ];
    
    return $data;
}

function time_elapsed_string($datetime, $full = false) {
    $time = $datetime instanceof DateTime ? $datetime->getTimestamp() : strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    $days = floor($diff / 86400);

    if ($days > 0) {
        if ($days == 1) return '1 day ago';
        if ($days < 7) return $days . ' days ago';
        if ($days < 30) return ceil($days / 7) . ' weeks ago';
        if ($days < 365) return ceil($days / 30) . ' months ago';
        return ceil($days / 365) . ' years ago';
    } else {
        $hours = floor($diff / 3600);
        if ($hours > 0) return $hours . ($hours == 1 ? ' hour' : ' hours') . ' ago';

        $minutes = floor($diff / 60);
        if ($minutes > 0) return $minutes . ($minutes == 1 ? ' minute' : ' minutes') . ' ago';

        if ($diff < 60) return 'Just now';
        return 'Just now';
    }
}

function get_algerian_wilayas() {
    return [
        '01' => 'Adrar',
        '02' => 'Chlef',
        '03' => 'Laghouat',
        '04' => 'Oum El Bouaghi',
        '05' => 'Batna',
        '06' => 'Béjaïa',
        '07' => 'Biskra',
        '08' => 'Béchar',
        '09' => 'Blida',
        '10' => 'Bouira',
        '11' => 'Tamanrasset',
        '12' => 'Tébessa',
        '13' => 'Tlemcen',
        '14' => 'Tiaret',
        '15' => 'Tizi Ouzou',
        '16' => 'Alger',
        '17' => 'Djelfa',
        '18' => 'Jijel',
        '19' => 'Sétif',
        '20' => 'Saïda',
        '21' => 'Skikda',
        '22' => 'Sidi Bel Abbès',
        '23' => 'Annaba',
        '24' => 'Guelma',
        '25' => 'Constantine',
        '26' => 'Médéa',
        '27' => 'Mostaganem',
        '28' => 'M\'sila',
        '29' => 'Mascara',
        '30' => 'Ouargla',
        '31' => 'Oran',
        '32' => 'El Bayadh',
        '33' => 'Illizi',
        '34' => 'Bordj Bou Arreridj',
        '35' => 'Boumerdès',
        '36' => 'El Tarf',
        '37' => 'Tindouf',
        '38' => 'Tissemsilt',
        '39' => 'El Oued',
        '40' => 'Khenchela',
        '41' => 'Souk Ahras',
        '42' => 'Tipaza',
        '43' => 'Mila',
        '44' => 'Aïn Defla',
        '45' => 'Naâma',
        '46' => 'Aïn Témouchent',
        '47' => 'Ghardaïa',
        '48' => 'Relizane',
        '49' => 'El M\'ghair',
        '50' => 'El Menia',
        '51' => 'Ouled Djellal',
        '52' => 'Bordj Badji Mokhtar',
        '53' => 'Béni Abbès',
        '54' => 'Timimoun',
        '55' => 'Touggourt',
        '56' => 'Djanet',
        '57' => 'In Salah',
        '58' => 'In Guezzam'
    ];
}
