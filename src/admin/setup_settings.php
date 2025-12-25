<?php
// src/admin/setup_settings.php
require_once '../db_connect.php';

try {
    // 1. Create table if not exists
    $sql = "CREATE TABLE IF NOT EXISTS site_settings (
        setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
        setting_value TEXT
    )";
    $pdo->exec($sql);

    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM site_settings LIKE 'setting_group'");
    $col = $stmt->fetch();
    if (!$col) {
        try {
            $pdo->exec("ALTER TABLE site_settings ADD COLUMN setting_group VARCHAR(50) NOT NULL DEFAULT 'general'");
            echo "Column 'setting_group' added.<br>";
        } catch(Exception $e) {
             echo "Alter failed: " . $e->getMessage() . "<br>";
        }
    }
    
    // Debug: show columns
    $stmt = $pdo->query("DESCRIBE site_settings");
    echo "<pre>";
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    echo "</pre>";
    
    echo "Table 'site_settings' checked/created.<br>";

    // 2. Define defaults
    $defaults = [
        // General
        ['site_title', 'Online Store', 'general'],
        ['site_tagline', 'Your one-stop shop', 'general'],
        ['contact_email', 'contact@yourstore.dz', 'general'],
        ['contact_phone', '+213 123 456 789', 'general'],
        ['contact_address', '123 Street, Algiers, Algeria', 'general'],
        ['facebook_url', '', 'general'],
        ['twitter_url', '', 'general'],
        ['instagram_url', '', 'general'],
        ['linkedin_url', '', 'general'],
        ['youtube_url', '', 'general'],
        ['maintenance_mode', '0', 'general'],
        ['site_logo', '', 'general'],

        // Appearance
        ['theme', 'default', 'appearance'],
        ['primary_color', '#6f42c1', 'appearance'],
        ['secondary_color', '#007bff', 'appearance'],
        ['accent_color', '#28a745', 'appearance'],
        ['homepage_layout', 'featured_products', 'appearance'],
        ['footer_content', '© ' . date('Y') . ' Online Store. All rights reserved.', 'appearance'],

        // E-commerce
        ['currency_code', 'DZD', 'ecommerce'],
        ['currency_symbol', 'DA', 'ecommerce'],
        ['tax_rate', '19.0', 'ecommerce'],
        ['tax_calculation_method', 'exclusive', 'ecommerce'],
        ['shipping_cost_standard', '500', 'ecommerce'],
        ['shipping_cost_express', '1000', 'ecommerce'],
        ['shipping_free_threshold', '10000', 'ecommerce'],
        ['paypal_enabled', '0', 'ecommerce'],
        ['paypal_client_id', '', 'ecommerce'],
        ['paypal_secret', '', 'ecommerce'],
        ['stripe_enabled', '0', 'ecommerce'],
        ['stripe_publishable_key', '', 'ecommerce'],
        ['stripe_secret_key', '', 'ecommerce'],
        ['inventory_tracking', '1', 'ecommerce'],
        ['low_stock_threshold', '10', 'ecommerce'],

        // Email
        ['smtp_host', '', 'email'],
        ['smtp_port', '587', 'email'],
        ['smtp_username', '', 'email'],
        ['smtp_password', '', 'email'],
        ['smtp_encryption', 'tls', 'email'],
        ['order_confirmation_template', 'Dear {customer_name}, your order #{order_id} has been confirmed. Thank you for shopping with us!', 'email'],
        ['order_shipped_template', 'Dear {customer_name}, your order #{order_id} has been shipped. Tracking number: {tracking_number}', 'email'],
        ['admin_notification_email', '', 'email'],

        // Security
        ['user_registration_enabled', '1', 'security'],
        ['min_password_length', '8', 'security'],
        ['max_login_attempts', '5', 'security'],
        ['lockout_duration', '900', 'security'],
        ['privacy_policy_url', 'privacy.php', 'security'],
        ['terms_of_service_url', 'terms.php', 'security'],
        ['cookie_consent_enabled', '1', 'security'],

        // Performance
        ['cache_enabled', '1', 'performance'],
        ['cache_duration', '3600', 'performance'],
        ['meta_title', 'Online Store - Electronics & Computer Parts', 'performance'],
        ['meta_description', 'Your one-stop shop for electronics, computer parts, and accessories', 'performance'],
        ['meta_keywords', 'electronics, computer, parts, shop', 'performance'],
        ['google_analytics_id', '', 'performance'],
        ['sitemap_auto_generate', '1', 'performance']
    ];

    // 3. Insert if not exists (using INSERT IGNORE is simplest, or ON DUPLICATE KEY UPDATE nothing)
    // Actually, we want to keep existing values if they exist, only insert new ones.
    $stmt = $pdo->prepare("INSERT IGNORE INTO site_settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?)");
    
    foreach ($defaults as $setting) {
        $stmt->execute($setting);
    }
    
    echo "Default settings verified/inserted.<br>";
    echo "Setup Complete.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>


