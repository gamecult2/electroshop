<?php
// Initialize session and common functions
require_once __DIR__ . '/init.php';
global $lang;
require_once __DIR__ . '/../models/Cart.php';
$headerCart = new Cart();
$cartCount = $headerCart->getItemCount();
$compareCount = count($_SESSION['product_comparison'] ?? []);

if (!function_exists('app_safe_color')) {
    function app_safe_color($value, $fallback) {
        return preg_match('/^#[0-9a-f]{6}$/i', (string)$value) ? $value : $fallback;
    }
}
$appPrimary = app_safe_color(get_setting('primary_color', '#6f42c1'), '#6f42c1');
$appSecondary = app_safe_color(get_setting('secondary_color', '#007bff'), '#007bff');
$appAccent = app_safe_color(get_setting('accent_color', '#28a745'), '#28a745');
$appBrand = app_safe_color(get_setting('logo_secondary_color', '#dc3545'), '#dc3545');

if (!function_exists('app_contrast_color')) {
    function app_contrast_color($hex) {
        $hex = ltrim((string)$hex, '#');
        if (!preg_match('/^[0-9a-f]{6}$/i', $hex)) return '#ffffff';
        $channels = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
        $luminance = 0;
        foreach ($channels as $index => $channel) {
            $value = $channel / 255;
            $value = $value <= .04045 ? $value / 12.92 : (($value + .055) / 1.055) ** 2.4;
            $luminance += $value * [0.2126, 0.7152, 0.0722][$index];
        }
        return $luminance > .179 ? '#111111' : '#ffffff';
    }
}
if (!function_exists('app_label')) {
    function app_label($key, $fallback) {
        $value = t($key);
        return $value === $key ? $fallback : $value;
    }
}

$pageTitles = [
    'index.php' => get_setting('site_tagline', 'Home'),
    'products.php' => t('products'), 'search.php' => app_label('search', 'Search'), 'product.php' => app_label('product', 'Product'),
    'cart.php' => t('cart'), 'checkout.php' => t('checkout'), 'wishlist.php' => t('wishlist'),
    'compare.php' => app_label('product_comparison', 'Product comparison'), 'account.php' => t('my_account'),
    'order_history.php' => t('order_history'), 'order_tracking.php' => t('track_order'),
    'login.php' => t('sign_in'), 'register.php' => t('create_account'),
    'forgot_password.php' => t('forgot_password'), 'reset_password.php' => app_label('reset_password', 'Reset password'),
    'contact.php' => t('contact_us'), 'about.php' => t('about_us'),
    'faq.php' => app_label('frequently_asked_questions', 'Frequently asked questions'), 'page.php' => app_label('information', 'Information'),
    'order_status.php' => app_label('order_status', 'Order status'), 'privacy.php' => app_label('privacy_policy', 'Privacy policy'),
    'returns.php' => app_label('returns', 'Returns'), 'shipping.php' => app_label('shipping', 'Shipping'),
    'support.php' => app_label('support', 'Support'), 'terms.php' => app_label('terms_and_conditions', 'Terms and conditions'),
    '404.php' => app_label('page_not_found', 'Page not found'),
];
$currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$documentTitle = trim(($page_title ?? ($pageTitles[$currentPage] ?? '')) . ' | ' . get_setting('site_title', SITE_TITLE), ' |');
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($documentTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars(get_setting('meta_description', 'Shop electronics, components, and accessories with secure checkout and reliable delivery.')); ?>">
    <!-- Bootstrap 5 -->
    <?php 
    $bsTheme = get_setting('bootstrap_theme', 'online');
    if ($bsTheme === 'online'): ?>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php else: ?>
        <link href="assets/css/themes/<?php echo htmlspecialchars($bsTheme); ?>/bootstrap.css" rel="stylesheet">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        :root {
            --header-height: 100px;
            --app-primary: <?php echo htmlspecialchars($appPrimary); ?>;
            --app-secondary: <?php echo htmlspecialchars($appSecondary); ?>;
            --app-accent: <?php echo htmlspecialchars($appAccent); ?>;
            --app-brand: <?php echo htmlspecialchars($appBrand); ?>;
            --app-primary-contrast: <?php echo app_contrast_color($appPrimary); ?>;
            --app-brand-contrast: <?php echo app_contrast_color($appBrand); ?>;
        }
        @media (min-width: 768px) {
            :root {
                --header-height: 140px;
            }
        }
    </style>
    <script>
        // Make PHP constants available to JavaScript
        const CURRENCY_SYMBOL = '<?php echo get_setting('currency_symbol', 'DA'); ?>';
    </script>
</head>
<body class="bg-light">
<a class="skip-link" href="#main-content">Skip to main content</a>
<header class="bg-white shadow-sm sticky-top" style="z-index: 1030;">
    <!-- Top Bar -->
    <div class="bg-dark text-white py-1 d-none d-md-block" style="font-size: 0.7rem;">
        <div class="container-xxl d-flex justify-content-between align-items-center">
            <div class="d-flex gap-4">
                <span><i class="fas fa-map-marker-alt me-1"></i> <?php echo t('fast_delivery'); ?></span>
                <span><i class="fas fa-shield-alt me-1"></i> <?php echo t('secure_payment'); ?></span>
                <span><i class="fas fa-exchange-alt me-1"></i> <?php echo t('easy_returns'); ?></span>
            </div>
            <div>
                <a href="contact.php" class="text-white text-decoration-none"><i class="fas fa-headset me-1"></i> <?php echo t('contact_us'); ?></a>
            </div>
        </div>
    </div>
    
    <!-- Main Header -->
    <div class="py-3 border-bottom border-danger border-3" style="box-shadow: 0 8px 0 #252525ff;">
        <div class="container-xxl">
            <div class="row align-items-center g-2 justify-content-between">
                <!-- Mobile Menu Toggle -->
                <div class="col-auto d-lg-none">
                    <button class="btn btn-outline-dark border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu" aria-label="Open navigation menu">
                        <i class="fas fa-bars fs-4"></i>
                    </button>
                </div>

                <!-- Desktop Categories Dropdown -->
                <div class="col-auto d-none d-lg-block">
                    <div class="dropdown">
                        <button class="btn btn-outline-dark border-0 py-1 px-2 d-flex align-items-center justify-content-center" type="button" id="categoryDropdown" data-bs-toggle="dropdown" aria-expanded="false" aria-label="<?php echo t('categories'); ?>">
                            <i class="fas fa-bars"></i>
                        </button>
                        <ul class="dropdown-menu shadow border-0 py-0" aria-labelledby="categoryDropdown" style="min-width: 250px;">
                            <?php 
                            require_once __DIR__ . '/../models/Category.php';
                            $headerCategoryModel = new Category();
                            $headerCategories = $headerCategoryModel->getAllWithHierarchy();
                            
                            $categoryIcons = [
                                1 => 'fas fa-laptop', 2 => 'fas fa-microchip', 3 => 'fas fa-gamepad',
                                4 => 'fas fa-blender', 5 => 'fas fa-plug', 6 => 'fas fa-mobile-alt',
                                7 => 'fas fa-wifi', 8 => 'fas fa-headphones', 9 => 'fas fa-clock'
                            ];

                            foreach ($headerCategories as $mainCat): 
                                $iconClass = isset($categoryIcons[$mainCat['id']]) ? $categoryIcons[$mainCat['id']] : ($mainCat['icon_class'] ?: 'fas fa-folder');
                            ?>
                                <li class="dropend">
                                    <a href="products.php?category=<?php echo $mainCat['id']; ?>" class="dropdown-item py-3 d-flex align-items-center justify-content-between <?php echo !empty($mainCat['subcategories']) ? 'dropdown-toggle' : ''; ?>" <?php echo !empty($mainCat['subcategories']) ? 'data-bs-toggle="dropdown" data-bs-auto-close="outside"' : ''; ?>>
                                        <div class="d-flex align-items-center gap-3">
                                            <i class="<?php echo $iconClass; ?> text-secondary opacity-75" style="width: 20px;"></i>
                                            <span><?php echo htmlspecialchars(localized_field($mainCat, 'name')); ?></span>
                                        </div>
                                    </a>
                                    
                                    <?php if (!empty($mainCat['subcategories'])): ?>
                                    <div class="dropdown-menu shadow border-0 p-4" style="min-width: 600px;">
                                        <div class="container-fluid">
                                            <div class="row">
                                                <div class="col-12 mb-3 border-bottom pb-2">
                                                    <h5 class="fw-bold mb-0 text-danger"><?php echo htmlspecialchars(localized_field($mainCat, 'name')); ?></h5>
                                                    <small class="text-muted"><?php echo $mainCat['product_count']; ?> products</small>
                                                </div>
                                                <div class="row">
                                                    <?php foreach (array_chunk($mainCat['subcategories'], ceil(count($mainCat['subcategories']) / 2)) as $chunk): ?>
                                                    <div class="col-6">
                                                        <?php foreach ($chunk as $subcat): ?>
                                                            <a href="products.php?category=<?php echo $subcat['id']; ?>" class="dropdown-item rounded py-2 d-flex align-items-center gap-2">
                                                                <i class="<?php echo $subcat['icon_class'] ?: 'fas fa-angle-right'; ?> small opacity-50"></i>
                                                                <span><?php echo htmlspecialchars(localized_field($subcat, 'name')); ?></span>
                                                                <?php if (isset($subcat['product_count'])): ?>
                                                                    <span class="badge bg-light text-dark ms-auto border small fw-normal"><?php echo $subcat['product_count']; ?></span>
                                                                <?php endif; ?>
                                                            </a>
                                                        <?php endforeach; ?>
                                                    </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                         </ul>
                    </div>
                </div>

                <!-- Logo -->
                <div class="col text-center col-lg-auto text-lg-start">
                    <a href="index.php" class="text-decoration-none h4 mb-0 fw-bold">
                        <?php 
                        $siteLogo = get_setting('site_logo');
                        $logoHeight = get_setting('site_logo_height', '40');
                        $siteTitle = get_setting('site_title', 'GameCult');
                        if ($siteLogo): ?>
                            <img src="<?php echo htmlspecialchars($siteLogo); ?>" alt="<?php echo htmlspecialchars($siteTitle); ?>" style="max-height: <?php echo min((int)$logoHeight, 64); ?>px; width: auto;">
                        <?php else: 
                            $primaryColor = get_setting('logo_primary_color', '#6c757d');
                            $secondaryColor = get_setting('logo_secondary_color', '#dc3545');
                            $fontSize = get_setting('site_logo_font_size', '24');
                            
                            // Logic to split the title for two-tone coloring
                            // If title has a space, split by space. Otherwise split in middle.
                            if (strpos($siteTitle, ' ') !== false) {
                                $parts = explode(' ', $siteTitle, 2);
                                $part1 = $parts[0];
                                $part2 = $parts[1];
                            } else {
                                // Split CamelCase or just half
                                $len = strlen($siteTitle);
                                $mid = ceil($len / 2);
                                // Try to find the first uppercase letter after the first char
                                $splitPos = $mid;
                                for ($i = 1; $i < $len; $i++) {
                                    if (ctype_upper($siteTitle[$i])) {
                                        $splitPos = $i;
                                        break;
                                    }
                                }
                                $part1 = substr($siteTitle, 0, $splitPos);
                                $part2 = substr($siteTitle, $splitPos);
                            }
                        ?>
                            <span style="color: <?php echo $primaryColor; ?>; font-size: <?php echo $fontSize; ?>px;"><?php echo htmlspecialchars($part1); ?></span><span style="color: <?php echo $secondaryColor; ?>; font-size: <?php echo $fontSize; ?>px;"><?php echo htmlspecialchars($part2); ?></span>
                        <?php endif; ?>
                    </a>
                </div>

                <!-- Search Bar -->
                <div class="col-12 col-lg-5 order-last order-lg-0 mt-2 mt-lg-0 search-bar">
                    <form action="search.php" method="GET" class="input-group" role="search">
                        <label class="visually-hidden" for="site-search"><?php echo t('search_products'); ?></label>
                        <input type="search" id="site-search" name="q" class="form-control border-danger border-2 py-1 px-3" placeholder="<?php echo t('search_products'); ?>" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="site-search-results">
                        <button class="btn btn-danger py-1 px-4" type="submit" aria-label="Search">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>

                <!-- Header Actions -->
                <div class="col-auto">
                    <div class="d-flex align-items-center gap-1 gap-md-3">

                        <a href="wishlist.php" class="action-btn app-icon-button btn btn-light border-0 bg-transparent text-secondary d-flex flex-column align-items-center p-1" aria-label="<?php echo t('wishlist'); ?>" style="font-size: 0.75rem;">
                            <div class="position-relative">
                                <i class="fas fa-heart mb-1" style="font-size: 0.9rem;"></i>
                                <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle" style="font-size: 0.6rem; margin-top: -2px; margin-left: -2px; display: none;">0</span>
                            </div>
                            <span class="d-none d-xl-inline"><?php echo t('wishlist'); ?></span>
                        </a>

                        <a href="compare.php" class="action-btn app-icon-button btn btn-light border-0 bg-transparent text-secondary d-none d-sm-flex flex-column align-items-center p-1" aria-label="<?php echo app_label('product_comparison', 'Compare products'); ?>" style="font-size: 0.75rem;">
                            <div class="position-relative">
                                <i class="fas fa-balance-scale mb-1" style="font-size: 0.9rem;" aria-hidden="true"></i>
                                <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle" style="<?php echo $compareCount > 0 ? 'display: flex;' : 'display: none;'; ?>"><?php echo $compareCount; ?></span>
                            </div>
                            <span class="d-none d-xl-inline"><?php echo app_label('compare', 'Compare'); ?></span>
                        </a>
                        
                        <a href="cart.php" class="action-btn app-icon-button btn btn-light border-0 bg-transparent text-secondary d-flex flex-column align-items-center p-1" aria-label="<?php echo t('cart'); ?>" style="font-size: 0.75rem;">
                            <div class="position-relative">
                                <i class="fas fa-shopping-cart mb-1" style="font-size: 0.9rem;"></i>
                                <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle" style="font-size: 0.6rem; margin-top: -2px; margin-left: -2px; <?php echo ($cartCount > 0) ? '' : 'display: none;'; ?>"><?php echo $cartCount ?? 0; ?></span>
                            </div>
                            <span class="d-none d-xl-inline"><?php echo t('cart'); ?></span>
                        </a>

                        <?php if (is_logged_in()): 
                            require_once __DIR__ . '/../models/Customer.php';
                            $headerCustomerModel = new Customer();
                            $headerUser = $headerCustomerModel->getById(get_current_user_id());
                        ?>
                            <div class="dropdown ms-2">
                                <button class="app-icon-button btn btn-light border-0 bg-transparent d-flex flex-column align-items-center p-1" type="button" data-bs-toggle="dropdown" aria-label="Open account menu" style="font-size: 0.75rem;">
                                    <i class="fas fa-user mb-1" style="font-size: 0.9rem;"></i>
                                    <span class="d-none d-xl-inline dropdown-toggle"><?php echo t('my_account'); ?></span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end shadow border-0 p-0 mt-2 overflow-hidden" style="width: 250px;">
                                    <div class="bg-light p-3 border-bottom">
                                        <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Welcome Back</small>
                                        <span class="fw-bold text-danger fs-6"><?php echo htmlspecialchars($headerUser['first_name'] . ' ' . $headerUser['last_name']); ?></span>
                                    </div>
                                    <div class="p-2">
                                        <a href="account.php" class="dropdown-item py-2 rounded-2 d-flex align-items-center gap-2">
                                            <i class="fas fa-user-circle text-muted" style="width: 20px;"></i> <?php echo t('my_account'); ?>
                                        </a>
                                        <a href="wishlist.php" class="dropdown-item py-2 rounded-2 d-flex align-items-center gap-2">
                                            <i class="fas fa-heart text-muted" style="width: 20px;"></i> <?php echo t('wishlist'); ?>
                                        </a>
                                        <div class="dropdown-divider mx-2"></div>
                                        <a href="logout.php" class="dropdown-item py-2 rounded-2 d-flex align-items-center gap-2 text-danger fw-bold">
                                            <i class="fas fa-sign-out-alt" style="width: 20px;"></i> <?php echo t('logout'); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="login.php" class="app-icon-button btn btn-light border-0 bg-transparent text-secondary d-flex flex-column align-items-center p-1" aria-label="<?php echo t('sign_in'); ?>" style="font-size: 0.75rem;">
                                <i class="fas fa-sign-in-alt mb-1" style="font-size: 0.9rem;"></i>
                                <span class="d-none d-xl-inline"><?php echo t('sign_in'); ?></span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Mobile Offcanvas Menu (Placeholder for Step 4/remaining files) -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold">
            <?php 
            if ($siteLogo): ?>
                <img src="<?php echo htmlspecialchars($siteLogo); ?>" alt="Logo" style="max-height: 30px;">
            <?php else: ?>
                <?php echo htmlspecialchars($siteTitle); ?>
            <?php endif; ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="list-group list-group-flush">
            <a href="index.php" class="list-group-item list-group-item-action py-3"><i class="fas fa-home text-secondary me-3" aria-hidden="true"></i><?php echo t('home'); ?></a>
            <a href="products.php" class="list-group-item list-group-item-action py-3"><i class="fas fa-store text-secondary me-3" aria-hidden="true"></i><?php echo t('products'); ?></a>
            <a href="compare.php" class="list-group-item list-group-item-action py-3"><i class="fas fa-balance-scale text-secondary me-3" aria-hidden="true"></i><?php echo app_label('product_comparison', 'Compare products'); ?><span class="badge rounded-pill bg-danger ms-2" style="<?php echo $compareCount > 0 ? 'display: inline-flex;' : 'display: none;'; ?>"><?php echo $compareCount; ?></span></a>
            <?php foreach ($headerCategories as $mainCat): ?>
                <a href="products.php?category=<?php echo $mainCat['id']; ?>" class="list-group-item list-group-item-action py-3 d-flex align-items-center gap-3">
                    <i class="<?php echo isset($categoryIcons[$mainCat['id']]) ? $categoryIcons[$mainCat['id']] : 'fas fa-folder'; ?> text-secondary" style="width: 24px;"></i>
                    <?php echo htmlspecialchars(localized_field($mainCat, 'name')); ?>
                </a>
            <?php endforeach; ?>
            <a href="account.php" class="list-group-item list-group-item-action py-3"><i class="fas fa-user text-secondary me-3" aria-hidden="true"></i><?php echo t('my_account'); ?></a>
            <a href="contact.php" class="list-group-item list-group-item-action py-3"><i class="fas fa-headset text-secondary me-3" aria-hidden="true"></i><?php echo t('contact_us'); ?></a>
        </div>
    </div>
</div>

<main id="main-content" class="pt-3 pb-4">
