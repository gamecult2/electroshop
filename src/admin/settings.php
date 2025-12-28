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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    
    $groups = ['general', 'appearance', 'ecommerce', 'email', 'security', 'performance'];
    
    $pdo->beginTransaction();
    try {
        foreach ($groups as $group) {
            $defaults = get_default_settings($group);
            $settings_to_save = $_POST[$group] ?? [];
            
            // Handle checkboxes
            foreach ($defaults as $key => $value) {
                if (!isset($settings_to_save[$key])) {
                    if (is_bool($value) || (is_string($value) && in_array($value, ['0', '1']))) {
                         $settings_to_save[$key] = '0';
                    }
                }
            }
            
            foreach ($settings_to_save as $key => $value) {
                $key = trim($key);
                $value = is_array($value) ? json_encode($value) : trim($value);
                
                $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = ?, setting_group = ?");
                $stmt->execute([$key, $value, $group, $value, $group]);
            }
        }

        if (isset($_POST['remove_site_logo']) && $_POST['remove_site_logo'] == '1') {
            $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = '' WHERE setting_key = 'site_logo'");
            $stmt->execute();
        }

        if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
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
            'paypal_enabled' => '0',
            'paypal_client_id' => '',
            'paypal_secret' => '',
            'stripe_enabled' => '0',
            'stripe_publishable_key' => '',
            'stripe_secret_key' => '',
            'baridimob_enabled' => '0',
            'baridimob_ccp' => '',
            'baridimob_rip' => '',
            'baridimob_holder' => '',
            'chargily_enabled' => '0',
            'chargily_api_key' => '',
            'chargily_secret_key' => ''
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

$general_settings = array_merge(get_default_settings('general'), array_intersect_key($settings_db, get_default_settings('general')));
$appearance_settings = array_merge(get_default_settings('appearance'), array_intersect_key($settings_db, get_default_settings('appearance')));
$ecommerce_settings = array_merge(get_default_settings('ecommerce'), array_intersect_key($settings_db, get_default_settings('ecommerce')));
$email_settings = array_merge(get_default_settings('email'), array_intersect_key($settings_db, get_default_settings('email')));
$security_settings = array_merge(get_default_settings('security'), array_intersect_key($settings_db, get_default_settings('security')));
$performance_settings = array_merge(get_default_settings('performance'), array_intersect_key($settings_db, get_default_settings('performance')));

$page_title = 'Site Settings';
$page_heading = 'Site Settings';

include 'header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show mb-4 border-0 shadow-sm rounded-3" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas <?php echo $messageType === 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'; ?> me-3 fs-4"></i>
            <div class="fw-medium"><?php echo htmlspecialchars($message); ?></div>
        </div>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<form method="POST" action="settings.php" enctype="multipart/form-data">
    <input type="hidden" id="settings_group_input" name="settings_group" value="general">
    
    <div class="card border-0 shadow-sm mb-4 p-2 bg-white rounded-3">
        <ul class="nav nav-pills nav-fill gap-1" id="settingsTabs" role="tablist">
            <li class="nav-item"><button class="nav-link active rounded-pill fw-bold py-2" id="general-tab" data-bs-toggle="pill" data-bs-target="#general" type="button" role="tab"><i class="fas fa-cog me-2"></i>General</button></li>
            <li class="nav-item"><button class="nav-link rounded-pill fw-bold py-2" id="appearance-tab" data-bs-toggle="pill" data-bs-target="#appearance" type="button" role="tab"><i class="fas fa-paint-brush me-2"></i>Appearance</button></li>
            <li class="nav-item"><button class="nav-link rounded-pill fw-bold py-2" id="ecommerce-tab" data-bs-toggle="pill" data-bs-target="#ecommerce" type="button" role="tab"><i class="fas fa-shopping-cart me-2"></i>E-commerce</button></li>
            <li class="nav-item"><button class="nav-link rounded-pill fw-bold py-2" id="email-tab" data-bs-toggle="pill" data-bs-target="#email" type="button" role="tab"><i class="fas fa-envelope me-2"></i>Email</button></li>
            <li class="nav-item"><button class="nav-link rounded-pill fw-bold py-2" id="security-tab" data-bs-toggle="pill" data-bs-target="#security" type="button" role="tab"><i class="fas fa-shield-alt me-2"></i>Security</button></li>
            <li class="nav-item"><button class="nav-link rounded-pill fw-bold py-2" id="performance-tab" data-bs-toggle="pill" data-bs-target="#performance" type="button" role="tab"><i class="fas fa-bolt me-2"></i>Performance</button></li>
        </ul>
    </div>

    <div class="tab-content" id="settingsTabContent">
        <!-- 1. GENERAL TAB -->
        <div id="general" class="tab-pane fade show active" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm p-4 h-100 rounded-3">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <div class="bg-light rounded p-2 d-flex align-items-center justify-content-center text-danger" style="width: 32px; height: 32px;"><i class="fas fa-info-circle"></i></div>
                            <h6 class="mb-0 fw-bold small text-uppercase">Site Information</h6>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Site Title</label>
                                <input type="text" class="form-control border-light-subtle shadow-none" name="general[site_title]" value="<?php echo htmlspecialchars($general_settings['site_title']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Site Tagline</label>
                                <input type="text" class="form-control border-light-subtle shadow-none" name="general[site_tagline]" value="<?php echo htmlspecialchars($general_settings['site_tagline']); ?>">
                            </div>
                        </div>

                        <div class="p-4 border border-2 border-dashed rounded-3 bg-light text-center">
                            <label class="form-label small fw-bold text-muted text-uppercase d-block mb-3">Site Logo & Branding</label>
                            <div id="logo_preview" class="mb-3">
                                <?php if (!empty($general_settings['site_logo'])): ?>
                                    <div class="position-relative d-inline-block p-2 bg-white rounded-3 shadow-sm border">
                                        <img src="../<?php echo $general_settings['site_logo']; ?>" alt="Logo" style="max-height: 60px;">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="remove_site_logo" value="1" id="remove_logo">
                                            <label class="form-check-label small text-danger fw-bold" for="remove_logo">Remove Logo</label>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="text-muted small py-3 bg-white rounded-3 border">No logo uploaded</div>
                                <?php endif; ?>
                            </div>
                            <div class="row g-2 align-items-center justify-content-center mb-3">
                                <div class="col-auto"><label class="small text-muted mb-0">Height (px)</label></div>
                                <div class="col-auto"><input type="number" class="form-control form-control-sm border-light-subtle shadow-none w-auto" name="general[site_logo_height]" value="<?php echo htmlspecialchars($general_settings['site_logo_height']); ?>" style="width: 70px;"></div>
                            </div>
                            <div class="row g-2 mb-3 border-top pt-3 mt-3 text-start">
                                <div class="col-4">
                                    <label class="small fw-bold text-muted d-block mb-1 text-uppercase" style="font-size: 0.65rem;">Primary</label>
                                    <input type="color" class="form-control form-control-color w-100 border-0 bg-transparent p-0" name="general[logo_primary_color]" value="<?php echo htmlspecialchars($general_settings['logo_primary_color']); ?>" style="height: 30px;">
                                </div>
                                <div class="col-4">
                                    <label class="small fw-bold text-muted d-block mb-1 text-uppercase" style="font-size: 0.65rem;">Secondary</label>
                                    <input type="color" class="form-control form-control-color w-100 border-0 bg-transparent p-0" name="general[logo_secondary_color]" value="<?php echo htmlspecialchars($general_settings['logo_secondary_color']); ?>" style="height: 30px;">
                                </div>
                                <div class="col-4">
                                    <label class="small fw-bold text-muted d-block mb-1 text-uppercase" style="font-size: 0.65rem;">Size (px)</label>
                                    <input type="number" class="form-control form-control-sm border-light-subtle shadow-none" name="general[site_logo_font_size]" value="<?php echo htmlspecialchars($general_settings['site_logo_font_size']); ?>">
                                </div>
                            </div>
                            <input type="file" class="form-control form-control-sm border-light-subtle" name="site_logo" accept="image/*">
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm p-4 h-100 rounded-3">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <div class="bg-light rounded p-2 d-flex align-items-center justify-content-center text-primary" style="width: 32px; height: 32px;"><i class="fas fa-address-book"></i></div>
                            <h6 class="mb-0 fw-bold small text-uppercase">Contact Information</h6>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Support Email</label>
                                <input type="email" class="form-control border-light-subtle shadow-none" name="general[contact_email]" value="<?php echo htmlspecialchars($general_settings['contact_email']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Phone</label>
                                <input type="tel" class="form-control border-light-subtle shadow-none" name="general[contact_phone]" value="<?php echo htmlspecialchars($general_settings['contact_phone']); ?>">
                            </div>
                        </div>
                        <div class="mb-0 flex-grow-1">
                            <label class="form-label small fw-bold text-muted text-uppercase">Office Address</label>
                            <textarea class="form-control border-light-subtle shadow-none" name="general[contact_address]" rows="5"><?php echo htmlspecialchars($general_settings['contact_address']); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-0 shadow-sm p-4 rounded-3">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <div class="bg-light rounded p-2 d-flex align-items-center justify-content-center text-success" style="width: 32px; height: 32px;"><i class="fas fa-share-alt"></i></div>
                            <h6 class="mb-0 fw-bold small text-uppercase">Social Media Ecosystem</h6>
                        </div>
                        <div class="row g-3">
                            <?php 
                            $socials = [
                                'facebook_url' => ['fab fa-facebook-f', 'text-primary'],
                                'twitter_url' => ['fab fa-twitter', 'text-info'],
                                'instagram_url' => ['fab fa-instagram', 'text-danger'],
                                'linkedin_url' => ['fab fa-linkedin-in', 'text-primary'],
                                'youtube_url' => ['fab fa-youtube', 'text-danger']
                            ];
                            foreach ($socials as $key => $meta): ?>
                                <div class="col-md-4 col-lg">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white border-light-subtle <?php echo $meta[1]; ?>"><i class="<?php echo $meta[0]; ?>"></i></span>
                                        <input type="url" class="form-control border-light-subtle shadow-none" name="general[<?php echo $key; ?>]" value="<?php echo htmlspecialchars($general_settings[$key]); ?>" placeholder="URL">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-0 shadow-sm p-4 bg-danger-subtle bg-opacity-10 border-start border-4 border-danger rounded-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-white rounded p-2 d-flex align-items-center justify-content-center text-danger border shadow-sm" style="width: 32px; height: 32px;"><i class="fas fa-tools"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-0">Maintenance Mode</h6>
                                    <p class="text-muted small mb-0 text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Strict Access Control</p>
                                </div>
                            </div>
                            <div class="form-check form-switch fs-4">
                                <input class="form-check-input shadow-none cursor-pointer" type="checkbox" name="general[maintenance_mode]" value="1" <?php echo $general_settings['maintenance_mode'] == '1' ? 'checked' : ''; ?>>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. APPEARANCE TAB -->
        <div id="appearance" class="tab-pane fade" role="tabpanel">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm p-4 h-100 rounded-3">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <div class="bg-light rounded p-2 d-flex align-items-center justify-content-center text-info" style="width: 32px; height: 32px;"><i class="fas fa-desktop"></i></div>
                            <h6 class="mb-0 fw-bold small text-uppercase">Theme & UI</h6>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">Bootstrap Framework</label>
                            <select class="form-select border-light-subtle shadow-none" name="appearance[bootstrap_theme]">
                                <option value="online" <?php echo ($appearance_settings['bootstrap_theme'] ?? 'online') === 'online' ? 'selected' : ''; ?>>Online CDN (Default)</option>
                                <?php foreach (get_available_bootstrap_themes() as $theme): ?>
                                    <option value="<?php echo htmlspecialchars($theme); ?>" <?php echo ($appearance_settings['bootstrap_theme'] ?? '') === $theme ? 'selected' : ''; ?>><?php echo ucfirst($theme); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-bold text-muted text-uppercase">Content Layout</label>
                            <select class="form-select border-light-subtle shadow-none" name="appearance[homepage_layout]">
                                <option value="featured_products" <?php echo $appearance_settings['homepage_layout'] === 'featured_products' ? 'selected' : ''; ?>>Featured Grid</option>
                                <option value="categories_grid" <?php echo $appearance_settings['homepage_layout'] === 'categories_grid' ? 'selected' : ''; ?>>Category Focus</option>
                                <option value="mixed_layout" <?php echo $appearance_settings['homepage_layout'] === 'mixed_layout' ? 'selected' : ''; ?>>Dynamic Mix</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 shadow-sm p-4 h-100 rounded-3">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <div class="bg-light rounded p-2 d-flex align-items-center justify-content-center text-primary" style="width: 32px; height: 32px;"><i class="fas fa-swatchbook"></i></div>
                            <h6 class="mb-0 fw-bold small text-uppercase">Visual Palette</h6>
                        </div>
                        <div class="row g-3">
                            <?php 
                            $colors = ['primary_color' => 'Primary', 'secondary_color' => 'Secondary', 'accent_color' => 'Accent'];
                            foreach ($colors as $key => $label): ?>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase"><?php echo $label; ?> Color</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color border-0 bg-transparent p-0" value="<?php echo htmlspecialchars($appearance_settings[$key]); ?>" onchange="document.getElementById('<?php echo $key; ?>_text').value=this.value" style="height: 38px;">
                                        <input type="text" id="<?php echo $key; ?>_text" name="appearance[<?php echo $key; ?>]" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars($appearance_settings[$key]); ?>">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card border-0 shadow-sm p-4 rounded-3">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <div class="bg-light rounded p-2 d-flex align-items-center justify-content-center text-dark" style="width: 32px; height: 32px;"><i class="fas fa-code"></i></div>
                            <h6 class="mb-0 fw-bold small text-uppercase">Footer Global Snippet</h6>
                        </div>
                        <textarea class="form-control border-light-subtle shadow-none bg-light font-monospace rounded-3" name="appearance[footer_content]" rows="4"><?php echo htmlspecialchars($appearance_settings['footer_content']); ?></textarea>
                        <div class="form-text small mt-2 text-muted"><i class="fas fa-info-circle me-1 text-info"></i>Supports HTML. Visible across all public pages.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. E-COMMERCE TAB -->
        <div id="ecommerce" class="tab-pane fade" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm p-4 h-100 rounded-3">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <div class="bg-light rounded p-2 d-flex align-items-center justify-content-center text-warning" style="width: 32px; height: 32px;"><i class="fas fa-coins"></i></div>
                            <h6 class="mb-0 fw-bold small text-uppercase">Localized Economy</h6>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">Currency</label>
                            <div class="row g-2">
                                <div class="col-8">
                                    <select class="form-select border-light-subtle shadow-none" name="ecommerce[currency_code]">
                                        <option value="DZD" <?php echo $ecommerce_settings['currency_code'] === 'DZD' ? 'selected' : ''; ?>>DZD (Algeria)</option>
                                        <option value="USD" <?php echo $ecommerce_settings['currency_code'] === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <input type="text" class="form-control border-light-subtle shadow-none text-center fw-bold" name="ecommerce[currency_symbol]" value="<?php echo htmlspecialchars($ecommerce_settings['currency_symbol']); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-bold text-muted text-uppercase">Taxation Rules</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="input-group input-group-sm">
                                        <input type="number" class="form-control border-light-subtle shadow-none" name="ecommerce[tax_rate]" value="<?php echo htmlspecialchars($ecommerce_settings['tax_rate']); ?>" step="0.1">
                                        <span class="input-group-text bg-light border-light-subtle">%</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <select class="form-select form-select-sm border-light-subtle shadow-none" name="ecommerce[tax_calculation_method]">
                                        <option value="exclusive" <?php echo $ecommerce_settings['tax_calculation_method'] === 'exclusive' ? 'selected' : ''; ?>>Excl.</option>
                                        <option value="inclusive" <?php echo $ecommerce_settings['tax_calculation_method'] === 'inclusive' ? 'selected' : ''; ?>>Incl.</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm p-4 rounded-3 h-100">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <div class="bg-light rounded p-2 d-flex align-items-center justify-content-center text-success" style="width: 32px; height: 32px;"><i class="fas fa-credit-card"></i></div>
                            <h6 class="mb-0 fw-bold small text-uppercase">Payment Rails</h6>
                        </div>
                        
                        <div class="row g-3">
                            <?php 
                            $gateways = [
                                'PayPal' => ['fab fa-paypal text-primary', 'paypal_enabled', ['client_id' => 'Client ID', 'secret' => 'Secret']],
                                'Stripe' => ['fab fa-stripe text-info', 'stripe_enabled', ['publishable_key' => 'Pub Key', 'secret_key' => 'Secret']],
                                'BaridiMob' => ['fas fa-mobile-alt text-danger', 'baridimob_enabled', ['ccp' => 'CCP', 'rip' => 'RIP', 'holder' => 'Name']],
                                'Chargily' => ['fas fa-bolt text-warning', 'chargily_enabled', ['api_key' => 'Pub Key', 'secret_key' => 'Secret']]
                            ];
                            foreach ($gateways as $name => $cfg): ?>
                                <div class="col-md-6">
                                    <div class="p-3 rounded-3 bg-light border border-light-subtle h-100">
                                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="<?php echo $cfg[0]; ?> fs-5"></i>
                                                <span class="fw-bold small"><?php echo $name; ?></span>
                                            </div>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input shadow-none cursor-pointer" type="checkbox" name="ecommerce[<?php echo $cfg[1]; ?>]" value="1" <?php echo $ecommerce_settings[$cfg[1]] == '1' ? 'checked' : ''; ?>>
                                            </div>
                                        </div>
                                        <?php foreach ($cfg[2] as $fieldKey => $label): ?>
                                            <div class="mb-2">
                                                <label class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.65rem;"><?php echo $label; ?></label>
                                                <?php $inputKey = strtolower($name) . '_' . $fieldKey; ?>
                                                <input type="<?php echo strpos($fieldKey, 'secret') !== false ? 'password' : 'text'; ?>" 
                                                       class="form-control form-control-sm border-light-subtle shadow-none rounded-pill px-3" 
                                                       name="ecommerce[<?php echo $inputKey; ?>]" 
                                                       value="<?php echo htmlspecialchars($ecommerce_settings[$inputKey] ?? ''); ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. EMAIL TAB -->
        <div id="email" class="tab-pane fade" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm p-4 h-100 rounded-3">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <div class="bg-light rounded p-2 d-flex align-items-center justify-content-center text-danger" style="width: 32px; height: 32px;"><i class="fas fa-at"></i></div>
                            <h6 class="mb-0 fw-bold small text-uppercase">Mailing Infrastructure</h6>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-8">
                                <label class="form-label small fw-bold text-muted text-uppercase">SMTP Host</label>
                                <input type="text" class="form-control border-light-subtle shadow-none" name="email[smtp_host]" value="<?php echo htmlspecialchars($email_settings['smtp_host']); ?>">
                            </div>
                            <div class="col-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Port</label>
                                <input type="number" class="form-control border-light-subtle shadow-none" name="email[smtp_port]" value="<?php echo htmlspecialchars($email_settings['smtp_port']); ?>">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6"><label class="form-label small fw-bold text-muted text-uppercase">User</label><input type="text" class="form-control border-light-subtle shadow-none" name="email[smtp_username]" value="<?php echo htmlspecialchars($email_settings['smtp_username']); ?>"></div>
                            <div class="col-md-6"><label class="form-label small fw-bold text-muted text-uppercase">Pass</label><input type="password" class="form-control border-light-subtle shadow-none" name="email[smtp_password]" value="<?php echo htmlspecialchars($email_settings['smtp_password']); ?>"></div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">Encryption</label>
                            <select class="form-select border-light-subtle shadow-none" name="email[smtp_encryption]">
                                <option value="tls" <?php echo $email_settings['smtp_encryption'] === 'tls' ? 'selected' : ''; ?>>TLS (Recommended)</option>
                                <option value="ssl" <?php echo $email_settings['smtp_encryption'] === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                <option value="">None</option>
                            </select>
                        </div>
                        <div class="p-3 bg-light rounded-3 border">
                            <label class="form-label small fw-bold text-muted text-uppercase">Admin Alerts</label>
                            <input type="email" class="form-control border-light-subtle shadow-none rounded-pill px-3" name="email[admin_notification_email]" value="<?php echo htmlspecialchars($email_settings['admin_notification_email']); ?>" placeholder="admin@domain.dz">
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm p-4 h-100 rounded-3">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <div class="bg-light rounded p-2 d-flex align-items-center justify-content-center text-primary" style="width: 32px; height: 32px;"><i class="fas fa-file-signature"></i></div>
                            <h6 class="mb-0 fw-bold small text-uppercase">Notification Blueprints</h6>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted text-uppercase">Confirmation Order</label>
                            <textarea class="form-control border-light-subtle shadow-none small bg-light rounded-3" name="email[order_confirmation_template]" rows="5"><?php echo htmlspecialchars($email_settings['order_confirmation_template']); ?></textarea>
                        </div>
                        <div class="mb-0">
                            <label class="form-label small fw-bold text-muted text-uppercase">Shipping Order</label>
                            <textarea class="form-control border-light-subtle shadow-none small bg-light rounded-3" name="email[order_shipped_template]" rows="5"><?php echo htmlspecialchars($email_settings['order_shipped_template']); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. SECURITY TAB -->
        <div id="security" class="tab-pane fade" role="tabpanel">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm p-4 h-100 rounded-3">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <div class="bg-light rounded p-2 d-flex align-items-center justify-content-center text-danger" style="width: 32px; height: 32px;"><i class="fas fa-user-lock"></i></div>
                            <h6 class="mb-0 fw-bold small text-uppercase">Auth & Policies</h6>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                            <label class="fw-bold small text-dark mb-0">Allow Registration</label>
                            <div class="form-check form-switch fs-5">
                                <input class="form-check-input shadow-none cursor-pointer" type="checkbox" name="security[user_registration_enabled]" value="1" <?php echo $security_settings['user_registration_enabled'] == '1' ? 'checked' : ''; ?>>
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Min Password Length</label>
                                <input type="number" class="form-control border-light-subtle shadow-none rounded-pill px-3" name="security[min_password_length]" value="<?php echo htmlspecialchars($security_settings['min_password_length']); ?>">
                            </div>
                        </div>
                        <div class="p-3 bg-warning-subtle bg-opacity-10 rounded-3 border border-warning-subtle">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="fw-bold small">Cookie Consent</span>
                                <div class="form-check form-switch"><input class="form-check-input shadow-none cursor-pointer" type="checkbox" name="security[cookie_consent_enabled]" value="1" <?php echo $security_settings['cookie_consent_enabled'] == '1' ? 'checked' : ''; ?>></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 shadow-sm p-4 h-100 rounded-3">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <div class="bg-light rounded p-2 d-flex align-items-center justify-content-center text-info" style="width: 32px; height: 32px;"><i class="fas fa-shield-virus"></i></div>
                            <h6 class="mb-0 fw-bold small text-uppercase">Brute Force Protection</h6>
                        </div>
                        <div class="row g-4 text-center mb-4">
                            <div class="col-6 border-end">
                                <div class="h4 fw-bold mb-0 text-dark"><?php echo $security_settings['max_login_attempts']; ?></div>
                                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Max Tries</small>
                            </div>
                            <div class="col-6">
                                <div class="h4 fw-bold mb-0 text-dark"><?php echo round($security_settings['lockout_duration']/60); ?>m</div>
                                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Lockout</small>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Limit</label>
                                <input type="number" class="form-control border-light-subtle shadow-none rounded-pill px-3" name="security[max_login_attempts]" value="<?php echo $security_settings['max_login_attempts']; ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Duration (s)</label>
                                <input type="number" class="form-control border-light-subtle shadow-none rounded-pill px-3" name="security[lockout_duration]" value="<?php echo $security_settings['lockout_duration']; ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. PERFORMANCE TAB -->
        <div id="performance" class="tab-pane fade" role="tabpanel">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm p-4 h-100 rounded-3">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <div class="bg-light rounded p-2 d-flex align-items-center justify-content-center text-danger" style="width: 32px; height: 32px;"><i class="fas fa-tachometer-alt"></i></div>
                            <h6 class="mb-0 fw-bold small text-uppercase">Optimization</h6>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                            <span class="fw-bold small text-dark">Enable Caching</span>
                            <div class="form-check form-switch"><input class="form-check-input shadow-none cursor-pointer" type="checkbox" name="performance[cache_enabled]" value="1" <?php echo $performance_settings['cache_enabled'] == '1' ? 'checked' : ''; ?>></div>
                        </div>
                        <label class="form-label small fw-bold text-muted text-uppercase">Cache TTL (Seconds)</label>
                        <input type="number" class="form-control border-light-subtle shadow-none mb-4 rounded-pill px-3" name="performance[cache_duration]" value="<?php echo $performance_settings['cache_duration']; ?>">
                        
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="fw-bold small text-dark">Auto Sitemap</span>
                                <div class="form-check form-switch"><input class="form-check-input shadow-none cursor-pointer" type="checkbox" name="performance[sitemap_auto_generate]" value="1" <?php echo $performance_settings['sitemap_auto_generate'] == '1' ? 'checked' : ''; ?>></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm p-4 h-100 rounded-3">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <div class="bg-light rounded p-2 d-flex align-items-center justify-content-center text-primary" style="width: 32px; height: 32px;"><i class="fas fa-search"></i></div>
                            <h6 class="mb-0 fw-bold small text-uppercase">Search Visibility (SEO)</h6>
                        </div>
                        <div class="row g-3">
                            <div class="col-12"><label class="form-label small fw-bold text-muted text-uppercase">Global Meta Title</label><input type="text" class="form-control border-light-subtle shadow-none rounded-3" name="performance[meta_title]" value="<?php echo htmlspecialchars($performance_settings['meta_title']); ?>"></div>
                            <div class="col-12"><label class="form-label small fw-bold text-muted text-uppercase">Meta Description</label><textarea class="form-control border-light-subtle shadow-none rounded-3" name="performance[meta_description]" rows="3"><?php echo htmlspecialchars($performance_settings['meta_description']); ?></textarea></div>
                            <div class="col-md-6"><label class="form-label small fw-bold text-muted text-uppercase">Keywords</label><input type="text" class="form-control border-light-subtle shadow-none rounded-pill px-3" name="performance[meta_keywords]" value="<?php echo htmlspecialchars($performance_settings['meta_keywords']); ?>"></div>
                            <div class="col-md-6"><label class="form-label small fw-bold text-muted text-uppercase">Analytics ID</label><input type="text" class="form-control border-light-subtle shadow-none rounded-pill px-3" name="performance[google_analytics_id]" value="<?php echo htmlspecialchars($performance_settings['google_analytics_id']); ?>" placeholder="G-XXXX"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- STICKY ACTION BAR -->
    <div class="sticky-bottom bg-white border-top p-4 mt-5 shadow-sm" style="z-index: 1020;">
        <div class="container-fluid d-flex justify-content-end align-items-center gap-3">
            <span class="text-muted small d-none d-md-inline"><i class="fas fa-info-circle me-1 text-info"></i>System settings update globally</span>
            <button type="submit" name="save_settings" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow">
                <i class="fas fa-save me-2"></i> Save All Settings
            </button>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('[data-bs-toggle="pill"]');
        tabButtons.forEach(button => {
            button.addEventListener('shown.bs.tab', (e) => {
                const targetId = e.target.getAttribute('data-bs-target').replace('#', '');
                document.getElementById('settings_group_input').value = targetId;
                localStorage.setItem('activeSettingsTab', targetId);
            });
        });

        const activeTab = localStorage.getItem('activeSettingsTab') || 'general';
        const trigger = document.getElementById(activeTab + '-tab');
        if (trigger) {
            const tab = bootstrap.Tab.getOrCreateInstance(trigger);
            tab.show();
        }

        document.getElementById('site_logo')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    const preview = document.getElementById('logo_preview');
                    preview.innerHTML = `<div class="p-2 bg-white rounded-3 shadow-sm border"><img src="${event.target.result}" style="max-height: 60px;"></div>`;
                }
                reader.readAsDataURL(file);
            }
        });
    });
</script>

<?php include 'footer.php'; ?>