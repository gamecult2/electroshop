<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'qwenshop');

// Site Configuration
// Define site URL components
define('SITE_PROTOCOL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http'));
define('SITE_HOST', $_SERVER['HTTP_HOST'] ?? 'localhost');
define('SITE_BASE_PATH', '/QwenShop/src'); // Adjust this based on your deployment path
define('SITE_URL', SITE_PROTOCOL . '://' . SITE_HOST . SITE_BASE_PATH);
define('SITE_TITLE', 'Online Store');
define('SITE_EMAIL', 'contact@yourstore.dz');
define('EMAIL_FROM_ADDRESS', SITE_EMAIL);

// File Upload Configuration
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB default

// Currency Configuration
define('CURRENCY_CODE', 'DZD');
define('CURRENCY_SYMBOL', 'DA');

// Upload Directories
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('IMAGE_DIR', __DIR__ . '/uploads/images/');

// Language Configuration
define('DEFAULT_LANGUAGE', 'en');
define('SUPPORTED_LANGUAGES', ['en']);

// Email Verification Configuration
define('EMAIL_VERIFICATION_REQUIRED', false); // Set to true in production to require email verification

// WhatsApp Configuration
define('WHATSAPP_BUSINESS_NUMBER', ''); // Enter your WhatsApp business number without '+' or '00' prefix

// Rate Limiting Configuration
define('RATE_LIMIT_PER_HOUR', 60);
define('RATE_LIMIT_WINDOW_MINUTES', 60);

// Create directories if they don't exist
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

if (!is_dir(IMAGE_DIR)) {
    mkdir(IMAGE_DIR, 0755, true);
}
?>
