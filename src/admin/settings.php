<?php
// admin/settings.php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';

// Load all settings from the database
$settings_db = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);

// Handle form submission
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    
    $groups = ['general', 'appearance', 'ecommerce', 'email', 'security', 'performance'];
    $success = true;
    
    $pdo->beginTransaction();
    try {
        foreach ($groups as $group) {
            // Get defaults for this group to handle unchecked boxes
            $defaults = get_default_settings($group);
            
            // Get data from POST for this group
            $settings_to_save = $_POST[$group] ?? [];
            
            // Handle unchecked checkboxes (merge defaults for missing keys)
            foreach ($defaults as $key => $value) {
                if (!isset($settings_to_save[$key])) {
                    // Check if it's a boolean/toggle type in defaults that is physically a checkbox
                    // (Value is '0' or '1' or boolean true/false)
                    if (is_bool($value) || (is_string($value) && in_array($value, ['0', '1']))) {
                         $settings_to_save[$key] = '0';
                    }
                }
            }
            
            foreach ($settings_to_save as $key => $value) {
                $key = trim($key);
                // Use trim() for values. Do NOT htmlspecialchars() here to avoid double-encoding on subsequent saves.
                // Output escaping is handled by htmlspecialchars() in the HTML values.
                $value = is_array($value) ? json_encode($value) : trim($value);
                
                $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = ?, setting_group = ?");
                $stmt->execute([$key, $value, $group, $value, $group]);
            }
        }

        // Handle logo removal
        if (isset($_POST['remove_site_logo']) && $_POST['remove_site_logo'] == '1') {
            $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = '' WHERE setting_key = 'site_logo'");
            $stmt->execute();
        }

        // Handle file uploads (e.g., site_logo)
        if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
            // Ensure upload_image function exists (it should be in functions.php)
            if (function_exists('upload_image')) {
                $logo_path = upload_image($_FILES['site_logo'], 'logo');
                if ($logo_path) {
                    $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, setting_group) VALUES ('site_logo', ?, 'general') ON DUPLICATE KEY UPDATE setting_value = ?");
                    $stmt->execute([$logo_path, $logo_path]);
                }
            }
        }
        
        $pdo->commit();
        $message = 'All settings saved successfully.';
        $messageType = 'success';

        // Refresh settings from DB
        $settings_db = $pdo->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);

    } catch (Exception $e) {
        $pdo->rollBack();
        $message = 'Failed to save settings: ' . $e->getMessage();
        $messageType = 'error';
    }
}

function get_default_settings($group) {
    $defaults = [
        'general' => [
            'site_title' => 'Online Store',
            'site_tagline' => 'Your one-stop shop',
            'contact_email' => 'contact@yourstore.dz',
            'contact_phone' => '+213 123 456 789',
            'contact_address' => '123 Street, Algiers, Algeria',
            'facebook_url' => '',
            'twitter_url' => '',
            'instagram_url' => '',
            'linkedin_url' => '',
            'youtube_url' => '',
            'maintenance_mode' => '0',
            'site_logo' => '',
            'site_logo_height' => '40',
            'logo_primary_color' => '#6c757d',
            'logo_secondary_color' => '#dc3545',
            'site_logo_font_size' => '24',
        ],
        'appearance' => [
            'theme' => 'default',
            'bootstrap_theme' => 'online',
            'primary_color' => '#6f42c1',
            'secondary_color' => '#007bff',
            'accent_color' => '#28a745',
            'homepage_layout' => 'featured_products',
            'footer_content' => '© ' . date('Y') . ' Online Store. All rights reserved.',
        ],
        'ecommerce' => [
            'currency_code' => 'DZD',
            'currency_symbol' => 'DA',
            'tax_rate' => '19.0',
            'tax_calculation_method' => 'exclusive',
            'shipping_cost_standard' => '500',
            'shipping_cost_express' => '1000',
            'shipping_free_threshold' => '10000',
            'paypal_enabled' => '0',
            'paypal_client_id' => '',
            'paypal_secret' => '',
            'stripe_enabled' => '0',
            'stripe_publishable_key' => '',
            'stripe_secret_key' => '',
            'inventory_tracking' => '1',
            'low_stock_threshold' => '10',
        ],
        'email' => [
            'smtp_host' => '',
            'smtp_port' => '587',
            'smtp_username' => '',
            'smtp_password' => '',
            'smtp_encryption' => 'tls',
            'order_confirmation_template' => 'Dear {customer_name}, your order #{order_id} has been confirmed. Thank you for shopping with us!',
            'order_shipped_template' => 'Dear {customer_name}, your order #{order_id} has been shipped. Tracking number: {tracking_number}',
            'admin_notification_email' => '',
        ],
        'security' => [
            'user_registration_enabled' => '1',
            'min_password_length' => '8',
            'max_login_attempts' => '5',
            'lockout_duration' => '900',
            'privacy_policy_url' => 'privacy.php',
            'terms_of_service_url' => 'terms.php',
            'cookie_consent_enabled' => '1',
        ],
        'performance' => [
            'cache_enabled' => '1',
            'cache_duration' => '3600',
            'meta_title' => 'Online Store - Electronics & Computer Parts',
            'meta_description' => 'Your one-stop shop for electronics, computer parts, and accessories',
            'meta_keywords' => 'electronics, computer, parts, shop',
            'google_analytics_id' => '',
            'sitemap_auto_generate' => '1',
        ]
    ];
    return $defaults[$group] ?? [];
}

function get_available_bootstrap_themes() {
    $themeDir = __DIR__ . '/../assets/css/themes';
    $themes = [];
    if (is_dir($themeDir)) {
        $items = scandir($themeDir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            if (is_dir($themeDir . '/' . $item) && file_exists($themeDir . '/' . $item . '/bootstrap.css')) {
                $themes[] = $item;
            }
        }
    }
    return $themes;
}

// Define settings for each section
$general_settings = array_merge(get_default_settings('general'), array_intersect_key($settings_db, get_default_settings('general')));
$appearance_settings = array_merge(get_default_settings('appearance'), array_intersect_key($settings_db, get_default_settings('appearance')));
$ecommerce_settings = array_merge(get_default_settings('ecommerce'), array_intersect_key($settings_db, get_default_settings('ecommerce')));
$email_settings = array_merge(get_default_settings('email'), array_intersect_key($settings_db, get_default_settings('email')));
$security_settings = array_merge(get_default_settings('security'), array_intersect_key($settings_db, get_default_settings('security')));
$performance_settings = array_merge(get_default_settings('performance'), array_intersect_key($settings_db, get_default_settings('performance')));

// Set page title and heading variables for the template
$page_title = 'Site Settings';
$page_heading = 'Site Settings';

// Include the shared header template
include 'header.php';

?>


            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas <?php echo $messageType === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'; ?> me-3 fs-4"></i>
                        <div><?php echo htmlspecialchars($message); ?></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="settings.php" enctype="multipart/form-data">
                <input type="hidden" id="settings_group_input" name="settings_group" value="general">
                
                <div class="card border-0 shadow-sm mb-4 p-2 bg-white rounded-4">
                    <ul class="nav nav-pills nav-fill gap-2" id="settingsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active rounded-pill fw-bold py-2" id="general-tab" data-bs-toggle="pill" data-bs-target="#general" type="button" role="tab">
                                <i class="fas fa-cog me-2"></i> General
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill fw-bold py-2" id="appearance-tab" data-bs-toggle="pill" data-bs-target="#appearance" type="button" role="tab">
                                <i class="fas fa-paint-brush me-2"></i> Appearance
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill fw-bold py-2" id="ecommerce-tab" data-bs-toggle="pill" data-bs-target="#ecommerce" type="button" role="tab">
                                <i class="fas fa-shopping-cart me-2"></i> E-commerce
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill fw-bold py-2" id="email-tab" data-bs-toggle="pill" data-bs-target="#email" type="button" role="tab">
                                <i class="fas fa-envelope me-2"></i> Email
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill fw-bold py-2" id="security-tab" data-bs-toggle="pill" data-bs-target="#security" type="button" role="tab">
                                <i class="fas fa-shield-alt me-2"></i> Security
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill fw-bold py-2" id="performance-tab" data-bs-toggle="pill" data-bs-target="#performance" type="button" role="tab">
                                <i class="fas fa-bolt me-2"></i> Performance
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content" id="settingsTabContent">

                <!-- General Settings Tab -->
                <div id="general" class="tab-pane fade show active" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm p-4 h-100">
                                <h3 class="h6 fw-bold text-uppercase text-muted mb-4 ls-1">
                                    <i class="fas fa-info-circle me-2 text-danger"></i> Site Information
                                </h3>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="site_title" class="form-label small fw-bold text-muted">Site Title</label>
                                        <input type="text" class="form-control" id="site_title" name="general[site_title]" value="<?php echo htmlspecialchars($general_settings['site_title']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="site_tagline" class="form-label small fw-bold text-muted">Site Tagline</label>
                                        <input type="text" class="form-control" id="site_tagline" name="general[site_tagline]" value="<?php echo htmlspecialchars($general_settings['site_tagline']); ?>">
                                    </div>
                                </div>

                                <div class="p-4 border-2 border-dashed rounded-3 bg-light text-center">
                                    <label class="form-label d-block small fw-bold text-muted mb-3">Site Logo</label>
                                    <div id="logo_preview" class="mb-3">
                                        <?php if (isset($settings_db['site_logo']) && $settings_db['site_logo']): ?>
                                            <div class="position-relative d-inline-block">
                                                <img src="../<?php echo $settings_db['site_logo']; ?>" alt="Current Logo" class="img-fluid rounded border shadow-sm" style="max-height: 80px;">
                                                <div class="form-check mt-2">
                                                    <input class="form-check-input" type="checkbox" name="remove_site_logo" value="1" id="remove_site_logo">
                                                    <label class="form-check-label small text-danger fw-bold" for="remove_site_logo">
                                                        Remove Logo
                                                    </label>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-muted small">No logo uploaded</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="row g-2 align-items-center justify-content-center mb-3">
                                        <div class="col-auto">
                                            <label for="site_logo_height" class="form-label small fw-bold text-muted mb-0">Logo Height (px)</label>
                                        </div>
                                        <div class="col-auto">
                                            <input type="number" class="form-control form-control-sm" id="site_logo_height" name="general[site_logo_height]" value="<?php echo htmlspecialchars($general_settings['site_logo_height']); ?>" style="width: 80px;">
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-3 border-top pt-3">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold text-muted">Primary Color</label>
                                            <input type="color" class="form-control form-control-color w-100 border-light-subtle shadow-none" name="general[logo_primary_color]" value="<?php echo htmlspecialchars($general_settings['logo_primary_color']); ?>" title="Choose primary color">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold text-muted">Secondary Color</label>
                                            <input type="color" class="form-control form-control-color w-100 border-light-subtle shadow-none" name="general[logo_secondary_color]" value="<?php echo htmlspecialchars($general_settings['logo_secondary_color']); ?>" title="Choose secondary color">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold text-muted">Font Size (px)</label>
                                            <input type="number" class="form-control form-control-sm" name="general[site_logo_font_size]" value="<?php echo htmlspecialchars($general_settings['site_logo_font_size']); ?>">
                                        </div>
                                    </div>

                                    <input type="file" class="form-control form-control-sm" id="site_logo" name="site_logo" accept="image/*">
                                    <div class="form-text mt-2 small">Recommended: 200x80px, max 2MB</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm p-4 h-100">
                                <h3 class="h6 fw-bold text-uppercase text-muted mb-4 ls-1">
                                    <i class="fas fa-address-book me-2 text-primary"></i> Contact Information
                                </h3>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label for="contact_email" class="form-label small fw-bold text-muted">Contact Email</label>
                                        <input type="email" class="form-control" id="contact_email" name="general[contact_email]" value="<?php echo htmlspecialchars($general_settings['contact_email']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="contact_phone" class="form-label small fw-bold text-muted">Contact Phone</label>
                                        <input type="tel" class="form-control" id="contact_phone" name="general[contact_phone]" value="<?php echo htmlspecialchars($general_settings['contact_phone']); ?>">
                                    </div>
                                </div>
                                <div class="mb-0">
                                    <label for="contact_address" class="form-label small fw-bold text-muted">Address</label>
                                    <textarea class="form-control" id="contact_address" name="general[contact_address]" rows="4"><?php echo htmlspecialchars($general_settings['contact_address']); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card border-0 shadow-sm p-4">
                                <h3 class="h6 fw-bold text-uppercase text-muted mb-4 ls-1">
                                    <i class="fas fa-share-alt me-2 text-success"></i> Social Media Links
                                </h3>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light text-primary border-light-subtle"><i class="fab fa-facebook-f"></i></span>
                                            <input type="url" class="form-control border-light-subtle" name="general[facebook_url]" value="<?php echo htmlspecialchars($general_settings['facebook_url']); ?>" placeholder="Facebook URL">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light text-info border-light-subtle"><i class="fab fa-twitter"></i></span>
                                            <input type="url" class="form-control border-light-subtle" name="general[twitter_url]" value="<?php echo htmlspecialchars($general_settings['twitter_url']); ?>" placeholder="Twitter/X URL">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light text-danger border-light-subtle"><i class="fab fa-instagram"></i></span>
                                            <input type="url" class="form-control border-light-subtle" name="general[instagram_url]" value="<?php echo htmlspecialchars($general_settings['instagram_url']); ?>" placeholder="Instagram URL">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light text-primary border-light-subtle"><i class="fab fa-linkedin-in"></i></span>
                                            <input type="url" class="form-control border-light-subtle" name="general[linkedin_url]" value="<?php echo htmlspecialchars($general_settings['linkedin_url']); ?>" placeholder="LinkedIn URL">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light text-danger border-light-subtle"><i class="fab fa-youtube"></i></span>
                                            <input type="url" class="form-control border-light-subtle" name="general[youtube_url]" value="<?php echo htmlspecialchars($general_settings['youtube_url']); ?>" placeholder="YouTube URL">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card border-0 shadow-sm p-4 bg-light">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="fw-bold mb-1">Maintenance Mode</h6>
                                        <p class="text-muted small mb-0">When enabled, only administrators will be able to access the site.</p>
                                    </div>
                                    <div class="form-check form-switch fs-4">
                                        <input class="form-check-input" type="checkbox" name="general[maintenance_mode]" value="1" id="maintenanceMode" <?php echo $general_settings['maintenance_mode'] == '1' ? 'checked' : ''; ?>>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appearance Settings Tab -->
                <div id="appearance" class="tab-pane fade" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-4 h-100">
                                <h3 class="h6 fw-bold text-uppercase text-muted mb-4 ls-1">
                                    <i class="fas fa-palette me-2 text-danger"></i> Theme Selection
                                </h3>
                                <div class="mb-3">
                                    <label for="bootstrap_theme" class="form-label small fw-bold text-muted">Bootstrap CSS Theme</label>
                                    <select class="form-select" id="bootstrap_theme" name="appearance[bootstrap_theme]">
                                        <option value="online" <?php echo ($appearance_settings['bootstrap_theme'] ?? 'online') === 'online' ? 'selected' : ''; ?>>Online (CDN)</option>
                                        <optgroup label="Local Themes">
                                            <?php 
                                            $localThemes = get_available_bootstrap_themes();
                                            foreach ($localThemes as $theme): ?>
                                                <option value="<?php echo htmlspecialchars($theme); ?>" <?php echo ($appearance_settings['bootstrap_theme'] ?? '') === $theme ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars(ucfirst($theme)); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    </select>
                                    <div class="form-text x-small">Upload Bootstrap CSS files to <code>assets/css/themes/</code> to see them here.</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-4 h-100">
                                <h3 class="h6 fw-bold text-uppercase text-muted mb-4 ls-1">
                                    <i class="fas fa-fill-drip me-2 text-primary"></i> Color Scheme
                                </h3>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted">Primary Color</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" value="<?php echo htmlspecialchars($appearance_settings['primary_color']); ?>" onchange="document.getElementById('primary_color_text').value=this.value">
                                            <input type="text" id="primary_color_text" name="appearance[primary_color]" class="form-control" value="<?php echo htmlspecialchars($appearance_settings['primary_color']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Secondary Color</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" value="<?php echo htmlspecialchars($appearance_settings['secondary_color']); ?>" onchange="document.getElementById('secondary_color_text').value=this.value">
                                            <input type="text" id="secondary_color_text" name="appearance[secondary_color]" class="form-control" value="<?php echo htmlspecialchars($appearance_settings['secondary_color']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Accent Color</label>
                                        <div class="input-group">
                                            <input type="color" class="form-control form-control-color" value="<?php echo htmlspecialchars($appearance_settings['accent_color']); ?>" onchange="document.getElementById('accent_color_text').value=this.value">
                                            <input type="text" id="accent_color_text" name="appearance[accent_color]" class="form-control" value="<?php echo htmlspecialchars($appearance_settings['accent_color']); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-4 h-100">
                                <h3 class="h6 fw-bold text-uppercase text-muted mb-4 ls-1">
                                    <i class="fas fa-th-large me-2 text-success"></i> Homepage Layout
                                </h3>
                                <div class="mb-3">
                                    <label for="homepage_layout" class="form-label small fw-bold text-muted">Select Layout</label>
                                    <select class="form-select" id="homepage_layout" name="appearance[homepage_layout]">
                                        <option value="featured_products" <?php echo $appearance_settings['homepage_layout'] === 'featured_products' ? 'selected' : ''; ?>>Featured Products</option>
                                        <option value="categories_grid" <?php echo $appearance_settings['homepage_layout'] === 'categories_grid' ? 'selected' : ''; ?>>Categories Grid</option>
                                        <option value="deals_promotions" <?php echo $appearance_settings['homepage_layout'] === 'deals_promotions' ? 'selected' : ''; ?>>Deals & Promotions</option>
                                        <option value="mixed_layout" <?php echo $appearance_settings['homepage_layout'] === 'mixed_layout' ? 'selected' : ''; ?>>Mixed Layout</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="card border-0 shadow-sm p-4">
                                <h3 class="h6 fw-bold text-uppercase text-muted mb-4 ls-1">
                                    <i class="fas fa-shoe-prints me-2 text-dark"></i> Footer Content
                                </h3>
                                <div class="mb-0">
                                    <label for="footer_content" class="form-label small fw-bold text-muted">Footer HTML/Text</label>
                                    <textarea class="form-control" id="footer_content" name="appearance[footer_content]" rows="4"><?php echo htmlspecialchars($appearance_settings['footer_content']); ?></textarea>
                                    <div class="form-text mt-2 small">HTML tags are supported. This content will appear at the bottom of all public pages.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- E-commerce Settings Tab -->
                <div id="ecommerce" class="tab-pane fade" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-4 h-100">
                                <h3 class="h6 fw-bold text-uppercase text-muted mb-4 ls-1">
                                    <i class="fas fa-coins me-2 text-warning"></i> Currency & Tax
                                </h3>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="currency_code" class="form-label small fw-bold text-muted">Currency Code</label>
                                        <select class="form-select" id="currency_code" name="ecommerce[currency_code]">
                                            <option value="DZD" <?php echo $ecommerce_settings['currency_code'] === 'DZD' ? 'selected' : ''; ?>>DZD (Algerian Dinar)</option>
                                            <option value="USD" <?php echo $ecommerce_settings['currency_code'] === 'USD' ? 'selected' : ''; ?>>USD (US Dollar)</option>
                                            <option value="EUR" <?php echo $ecommerce_settings['currency_code'] === 'EUR' ? 'selected' : ''; ?>>EUR (Euro)</option>
                                            <option value="GBP" <?php echo $ecommerce_settings['currency_code'] === 'GBP' ? 'selected' : ''; ?>>GBP (British Pound)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="currency_symbol" class="form-label small fw-bold text-muted">Symbol</label>
                                        <input type="text" class="form-control" id="currency_symbol" name="ecommerce[currency_symbol]" value="<?php echo htmlspecialchars($ecommerce_settings['currency_symbol']); ?>">
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="tax_rate" class="form-label small fw-bold text-muted">Tax Rate (%)</label>
                                        <input type="number" class="form-control" id="tax_rate" name="ecommerce[tax_rate]" value="<?php echo htmlspecialchars($ecommerce_settings['tax_rate']); ?>" step="0.01" min="0">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="tax_calculation_method" class="form-label small fw-bold text-muted">Calculation</label>
                                        <select class="form-select" id="tax_calculation_method" name="ecommerce[tax_calculation_method]">
                                            <option value="exclusive" <?php echo $ecommerce_settings['tax_calculation_method'] === 'exclusive' ? 'selected' : ''; ?>>Exclusive</option>
                                            <option value="inclusive" <?php echo $ecommerce_settings['tax_calculation_method'] === 'inclusive' ? 'selected' : ''; ?>>Inclusive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-4 h-100">
                                <h3 class="h6 fw-bold text-uppercase text-muted mb-4 ls-1">
                                    <i class="fas fa-truck me-2 text-primary"></i> Shipping Options
                                </h3>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Standard Cost (<?php echo $ecommerce_settings['currency_symbol']; ?>)</label>
                                        <input type="number" class="form-control" name="ecommerce[shipping_cost_standard]" value="<?php echo htmlspecialchars($ecommerce_settings['shipping_cost_standard']); ?>" min="0" step="0.01">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Express Cost (<?php echo $ecommerce_settings['currency_symbol']; ?>)</label>
                                        <input type="number" class="form-control" name="ecommerce[shipping_cost_express]" value="<?php echo htmlspecialchars($ecommerce_settings['shipping_cost_express']); ?>" min="0" step="0.01">
                                    </div>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small fw-bold text-muted">Free Shipping Threshold (<?php echo $ecommerce_settings['currency_symbol']; ?>)</label>
                                    <input type="number" class="form-control" name="ecommerce[shipping_free_threshold]" value="<?php echo htmlspecialchars($ecommerce_settings['shipping_free_threshold']); ?>" min="0" step="0.01">
                                    <div class="form-text small">Orders above this amount qualify for free shipping.</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm p-4 h-100">
                                <h3 class="h6 fw-bold text-uppercase text-muted mb-4 ls-1">
                                    <i class="fas fa-credit-card me-2 text-success"></i> Payment Gateways
                                </h3>
                                <div class="p-3 border rounded-3 bg-light mb-4">
                                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fab fa-cc-paypal fs-3 text-primary"></i>
                                            <h6 class="fw-bold mb-0">PayPal Payments</h6>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="ecommerce[paypal_enabled]" value="1" <?php echo $ecommerce_settings['paypal_enabled'] == '1' ? 'checked' : ''; ?>>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-muted">Client ID</label>
                                            <input type="text" class="form-control form-control-sm" name="ecommerce[paypal_client_id]" value="<?php echo htmlspecialchars($ecommerce_settings['paypal_client_id']); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-muted">Client Secret</label>
                                            <input type="password" class="form-control form-control-sm" name="ecommerce[paypal_secret]" value="<?php echo htmlspecialchars($ecommerce_settings['paypal_secret']); ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="p-3 border rounded-3 bg-light">
                                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fab fa-cc-stripe fs-3 text-info"></i>
                                            <h6 class="fw-bold mb-0">Stripe Payments</h6>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="ecommerce[stripe_enabled]" value="1" <?php echo $ecommerce_settings['stripe_enabled'] == '1' ? 'checked' : ''; ?>>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-muted">Publishable Key</label>
                                            <input type="text" class="form-control form-control-sm" name="ecommerce[stripe_publishable_key]" value="<?php echo htmlspecialchars($ecommerce_settings['stripe_publishable_key']); ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-muted">Secret Key</label>
                                            <input type="password" class="form-control form-control-sm" name="ecommerce[stripe_secret_key]" value="<?php echo htmlspecialchars($ecommerce_settings['stripe_secret_key']); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm p-4 h-100">
                                <h3 class="h6 fw-bold text-uppercase text-muted mb-4 ls-1">
                                    <i class="fas fa-boxes me-2 text-info"></i> Inventory
                                </h3>
                                <div class="mb-4 pb-3 border-bottom">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <label class="small fw-bold text-muted">Tracking</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="ecommerce[inventory_tracking]" value="1" <?php echo $ecommerce_settings['inventory_tracking'] == '1' ? 'checked' : ''; ?>>
                                        </div>
                                    </div>
                                    <div class="form-text small">Enable automatic stock deduction.</div>
                                </div>
                                <div>
                                    <label class="form-label small fw-bold text-muted">Low Stock Alert</label>
                                    <input type="number" class="form-control" name="ecommerce[low_stock_threshold]" value="<?php echo htmlspecialchars($ecommerce_settings['low_stock_threshold']); ?>" min="0">
                                    <div class="form-text small">Notify when stock is below this.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email & Notifications Tab -->
                <div id="email" class="tab-pane fade" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm p-4 h-100">
                                <h3 class="h6 fw-bold text-uppercase text-muted mb-4 ls-1">
                                    <i class="fas fa-server me-2 text-danger"></i> SMTP Configuration
                                </h3>
                                <div class="row g-3 mb-3">
                                    <div class="col-8">
                                        <label for="smtp_host" class="form-label small fw-bold text-muted">SMTP Host</label>
                                        <input type="text" class="form-control" id="smtp_host" name="email[smtp_host]" value="<?php echo htmlspecialchars($email_settings['smtp_host']); ?>" placeholder="smtp.example.com">
                                    </div>
                                    <div class="col-4">
                                        <label for="smtp_port" class="form-label small fw-bold text-muted">Port</label>
                                        <input type="number" class="form-control" id="smtp_port" name="email[smtp_port]" value="<?php echo htmlspecialchars($email_settings['smtp_port']); ?>" placeholder="587">
                                    </div>
                                </div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label for="smtp_username" class="form-label small fw-bold text-muted">Username</label>
                                        <input type="text" class="form-control" id="smtp_username" name="email[smtp_username]" value="<?php echo htmlspecialchars($email_settings['smtp_username']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="smtp_password" class="form-label small fw-bold text-muted">Password</label>
                                        <input type="password" class="form-control" id="smtp_password" name="email[smtp_password]" value="<?php echo htmlspecialchars($email_settings['smtp_password']); ?>">
                                    </div>
                                </div>
                                <div class="mb-0">
                                    <label for="smtp_encryption" class="form-label small fw-bold text-muted">Encryption</label>
                                    <select class="form-select" id="smtp_encryption" name="email[smtp_encryption]">
                                        <option value="tls" <?php echo $email_settings['smtp_encryption'] === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                        <option value="ssl" <?php echo $email_settings['smtp_encryption'] === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                        <option value="" <?php echo $email_settings['smtp_encryption'] === '' ? 'selected' : ''; ?>>None</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card border-0 shadow-sm p-4 h-100">
                                <h3 class="h6 fw-bold text-uppercase text-muted mb-4 ls-1">
                                    <i class="fas fa-bell me-2 text-primary"></i> Notifications
                                </h3>
                                <div class="mb-0">
                                    <label for="admin_notification_email" class="form-label small fw-bold text-muted">Admin Alert Email</label>
                                    <input type="email" class="form-control" id="admin_notification_email" name="email[admin_notification_email]" value="<?php echo htmlspecialchars($email_settings['admin_notification_email']); ?>" placeholder="admin@qwenshop.dz">
                                    <div class="form-text small">Receive alerts for new orders and inquiries.</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-4 h-100">
                                <h3 class="h6 fw-bold text-uppercase text-muted mb-4 ls-1">
                                    <i class="fas fa-file-invoice me-2 text-success"></i> Order Confirmation
                                </h3>
                                <div class="mb-0">
                                    <label class="form-label small fw-bold text-muted">Email Body Template</label>
                                    <textarea class="form-control" name="email[order_confirmation_template]" rows="6"><?php echo htmlspecialchars($email_settings['order_confirmation_template']); ?></textarea>
                                    <div class="form-text small">Placeholders: {customer_name}, {order_id}, {order_total}</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-4 h-100">
                                <h3 class="h6 fw-bold text-uppercase text-muted mb-4 ls-1">
                                    <i class="fas fa-truck-loading me-2 text-info"></i> Shipping Alert
                                </h3>
                                <div class="mb-0">
                                    <label class="form-label small fw-bold text-muted">Email Body Template</label>
                                    <textarea class="form-control" name="email[order_shipped_template]" rows="6"><?php echo htmlspecialchars($email_settings['order_shipped_template']); ?></textarea>
                                    <div class="form-text small">Placeholders: {customer_name}, {order_id}, {tracking_number}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security & Privacy Tab -->
                <div id="security" class="tab-pane fade" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-4 h-100">
                                <h3 class="h6 fw-bold text-uppercase text-muted mb-4 ls-1">
                                    <i class="fas fa-user-shield me-2 text-danger"></i> Access Control
                                </h3>
                                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                                    <div>
                                        <h6 class="fw-bold mb-1">User Registration</h6>
                                        <p class="text-muted small mb-0">Allow new customers to create accounts.</p>
                                    </div>
                                    <div class="form-check form-switch fs-4">
                                        <input class="form-check-input" type="checkbox" name="security[user_registration_enabled]" value="1" <?php echo $security_settings['user_registration_enabled'] == '1' ? 'checked' : ''; ?>>
                                    </div>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small fw-bold text-muted">Minimum Password Length</label>
                                    <input type="number" class="form-control" name="security[min_password_length]" value="<?php echo htmlspecialchars($security_settings['min_password_length']); ?>" min="6" max="50">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm p-4 h-100">
                                <h3 class="h6 fw-bold text-uppercase text-muted mb-4 ls-1">
                                    <i class="fas fa-lock me-2 text-primary"></i> Login Security
                                </h3>
                                <div class="row g-3 mb-4 text-center">
                                    <div class="col-6 border-end">
                                        <div class="h3 fw-bold mb-0"><?php echo htmlspecialchars($security_settings['max_login_attempts']); ?></div>
                                        <div class="text-muted small text-uppercase fw-bold">Max Attempts</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="h3 fw-bold mb-0"><?php echo round($security_settings['lockout_duration'] / 60); ?>m</div>
                                        <div class="text-muted small text-uppercase fw-bold">Lockout Time</div>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-muted">Max Attempts</label>
                                        <input type="number" class="form-control" name="security[max_login_attempts]" value="<?php echo htmlspecialchars($security_settings['max_login_attempts']); ?>" min="1" max="20">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-muted">Lockout (sec)</label>
                                        <input type="number" class="form-control" name="security[lockout_duration]" value="<?php echo htmlspecialchars($security_settings['lockout_duration']); ?>" min="60">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="card border-0 shadow-sm p-4">
                                <h3 class="h6 fw-bold text-uppercase text-muted mb-4 ls-1">
                                    <i class="fas fa-gavel me-2 text-dark"></i> Legal & Privacy
                                </h3>
                                <div class="row g-4 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Privacy Policy URL</label>
                                        <input type="text" class="form-control" name="security[privacy_policy_url]" value="<?php echo htmlspecialchars($security_settings['privacy_policy_url']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Terms of Service URL</label>
                                        <input type="text" class="form-control" name="security[terms_of_service_url]" value="<?php echo htmlspecialchars($security_settings['terms_of_service_url']); ?>">
                                    </div>
                                </div>
                                <div class="p-3 border rounded-3 bg-light d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="fw-bold mb-1">Cookie Consent Banner</h6>
                                        <p class="text-muted small mb-0">Show a banner to users for cookie acceptance (GDPR/APPI compliance).</p>
                                    </div>
                                    <div class="form-check form-switch fs-4">
                                        <input class="form-check-input" type="checkbox" name="security[cookie_consent_enabled]" value="1" <?php echo $security_settings['cookie_consent_enabled'] == '1' ? 'checked' : ''; ?>>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance & SEO Tab -->
                <div id="performance" class="tab-pane fade" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm p-4 h-100">
                                <h3 class="h6 fw-bold text-uppercase text-muted mb-4 ls-1">
                                    <i class="fas fa-tachometer-alt me-2 text-danger"></i> Performance
                                </h3>
                                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                                    <div>
                                        <h6 class="fw-bold mb-1">Page Caching</h6>
                                        <p class="text-muted small mb-0">Speeds up page loading.</p>
                                    </div>
                                    <div class="form-check form-switch fs-4">
                                        <input class="form-check-input" type="checkbox" name="performance[cache_enabled]" value="1" <?php echo $performance_settings['cache_enabled'] == '1' ? 'checked' : ''; ?>>
                                    </div>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label small fw-bold text-muted">Cache TTL (sec)</label>
                                    <input type="number" class="form-control" name="performance[cache_duration]" value="<?php echo htmlspecialchars($performance_settings['cache_duration']); ?>" min="60">
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm p-4 h-100">
                                <h3 class="h6 fw-bold text-uppercase text-muted mb-4 ls-1">
                                    <i class="fas fa-search me-2 text-primary"></i> SEO Settings
                                </h3>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted">Global Meta Title</label>
                                        <input type="text" class="form-control" name="performance[meta_title]" value="<?php echo htmlspecialchars($performance_settings['meta_title']); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-muted">Global Meta Description</label>
                                        <textarea class="form-control" name="performance[meta_description]" rows="3"><?php echo htmlspecialchars($performance_settings['meta_description']); ?></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Keywords (Comma separated)</label>
                                        <input type="text" class="form-control" name="performance[meta_keywords]" value="<?php echo htmlspecialchars($performance_settings['meta_keywords']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Google Analytics ID</label>
                                        <input type="text" class="form-control" name="performance[google_analytics_id]" value="<?php echo htmlspecialchars($performance_settings['google_analytics_id']); ?>" placeholder="G-XXXXXXXXX">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card border-0 shadow-sm p-4 bg-light">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="fw-bold mb-1">Automatic Sitemap</h6>
                                        <p class="text-muted small mb-0">Regenerate `sitemap.xml` automatically when products change.</p>
                                    </div>
                                    <div class="form-check form-switch fs-4">
                                        <input class="form-check-input" type="checkbox" name="performance[sitemap_auto_generate]" value="1" <?php echo $performance_settings['sitemap_auto_generate'] == '1' ? 'checked' : ''; ?>>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- End Tab Content -->

            <div class="sticky-bottom bg-white border-top p-4 mt-5 mx-n4 mb-n4 shadow-sm z-1">
                <div class="d-flex justify-content-end align-items-center gap-3">
                    <span class="text-muted small d-none d-md-inline">
                        <i class="fas fa-info-circle me-1"></i> Settings are applied globally
                    </span>
                    <button type="submit" name="save_settings" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow">
                        <i class="fas fa-save me-2"></i> Save All Settings
                    </button>
                </div>
            </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update hidden input when tab changes
            const tabButtons = document.querySelectorAll('[data-bs-toggle="pill"]');
            tabButtons.forEach(button => {
                button.addEventListener('shown.bs.tab', function (event) {
                    const targetId = event.target.getAttribute('data-bs-target').replace('#', '');
                    document.getElementById('settings_group_input').value = targetId;
                });
            });

            // Logo upload preview
            document.getElementById('site_logo').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const previewDiv = document.getElementById('logo_preview');
                        previewDiv.innerHTML = '<img src="' + event.target.result + '" alt="Logo Preview" class="img-fluid rounded border shadow-sm" style="max-height: 80px;">';
                    }
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>

    <!-- Include the shared footer template -->
    <?php include 'footer.php'; ?>


