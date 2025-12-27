<?php
// Initialize session and common functions
require_once __DIR__ . '/init.php';
global $lang;
require_once __DIR__ . '/../models/Cart.php';
$headerCart = new Cart();
$cartCount = $headerCart->getItemCount();
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_setting('site_title', SITE_TITLE); ?></title>
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
    <style>
        :root {
            --header-height: 100px;
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
                    <button class="btn btn-outline-dark border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
                        <i class="fas fa-bars fs-4"></i>
                    </button>
                </div>

                <!-- Desktop Categories Dropdown -->
                <div class="col-auto d-none d-lg-block">
                    <div class="dropdown">
                        <button class="btn btn-outline-dark border-0 py-1 px-2 d-flex align-items-center justify-content-center" type="button" id="categoryDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="<?php echo t('categories'); ?>">
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
                                            <span><?php echo htmlspecialchars($mainCat['name_en']); ?></span>
                                        </div>
                                    </a>
                                    
                                    <?php if (!empty($mainCat['subcategories'])): ?>
                                    <div class="dropdown-menu shadow border-0 p-4" style="min-width: 600px;">
                                        <div class="container-fluid">
                                            <div class="row">
                                                <div class="col-12 mb-3 border-bottom pb-2">
                                                    <h5 class="fw-bold mb-0 text-danger"><?php echo htmlspecialchars($mainCat['name_en']); ?></h5>
                                                    <small class="text-muted"><?php echo $mainCat['product_count']; ?> products</small>
                                                </div>
                                                <div class="row">
                                                    <?php foreach (array_chunk($mainCat['subcategories'], ceil(count($mainCat['subcategories']) / 2)) as $chunk): ?>
                                                    <div class="col-6">
                                                        <?php foreach ($chunk as $subcat): ?>
                                                            <a href="products.php?category=<?php echo $subcat['id']; ?>" class="dropdown-item rounded py-2 d-flex align-items-center gap-2">
                                                                <i class="<?php echo $subcat['icon_class'] ?: 'fas fa-angle-right'; ?> small opacity-50"></i>
                                                                <span><?php echo htmlspecialchars($subcat['name_en']); ?></span>
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
                <div class="col-auto">
                    <a href="index.php" class="text-decoration-none h4 mb-0 fw-bold">
                        <?php 
                        $siteLogo = get_setting('site_logo');
                        $logoHeight = get_setting('site_logo_height', '40');
                        $siteTitle = get_setting('site_title', 'GameCult');
                        if ($siteLogo): ?>
                            <img src="<?php echo htmlspecialchars($siteLogo); ?>" alt="<?php echo htmlspecialchars($siteTitle); ?>" style="max-height: <?php echo $logoHeight; ?>px;">
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
                <div class="col-lg-5">
                    <form action="search.php" method="GET" class="input-group">
                        <input type="text" name="q" class="form-control border-danger border-2 py-1 px-3" placeholder="<?php echo t('search_products'); ?>" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                        <button class="btn btn-danger py-1 px-4" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>

                <!-- Header Actions -->
                <div class="col-auto">
                    <div class="d-flex align-items-center gap-1 gap-md-3">
                        <a href="compare.php" class="btn btn-light border-0 bg-transparent text-secondary d-flex flex-column align-items-center p-1" style="font-size: 0.7rem;">
                            <div class="position-relative">
                                <i class="fas fa-balance-scale mb-1" style="font-size: 0.9rem;"></i>
                                <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle" style="font-size: 0.6rem; margin-top: -2px; margin-left: -2px;">0</span>
                            </div>
                            <span class="d-none d-xl-inline"><?php echo t('compare'); ?></span>
                        </a>
                        
                        <a href="wishlist.php" class="btn btn-light border-0 bg-transparent text-secondary d-flex flex-column align-items-center p-1" style="font-size: 0.7rem;">
                            <div class="position-relative">
                                <i class="fas fa-heart mb-1" style="font-size: 0.9rem;"></i>
                                <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle" style="font-size: 0.6rem; margin-top: -2px; margin-left: -2px;">0</span>
                            </div>
                            <span class="d-none d-xl-inline"><?php echo t('wishlist'); ?></span>
                        </a>
                        
                        <a href="cart.php" class="btn btn-light border-0 bg-transparent text-secondary d-flex flex-column align-items-center p-1" style="font-size: 0.7rem;">
                            <div class="position-relative">
                                <i class="fas fa-shopping-cart mb-1" style="font-size: 0.9rem;"></i>
                                <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle" style="font-size: 0.6rem; margin-top: -2px; margin-left: -2px;"><?php echo $cartCount ?? 0; ?></span>
                            </div>
                            <span class="d-none d-xl-inline"><?php echo t('cart'); ?></span>
                        </a>

                        <?php if (is_logged_in()): 
                            require_once __DIR__ . '/../models/Customer.php';
                            $headerCustomerModel = new Customer();
                            $headerUser = $headerCustomerModel->getById(get_current_user_id());
                        ?>
                            <div class="dropdown ms-2">
                                <button class="btn btn-light border-0 bg-transparent d-flex flex-column align-items-center p-1" type="button" data-bs-toggle="dropdown" style="font-size: 0.7rem;">
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
                            <a href="login.php" class="btn btn-light border-0 bg-transparent text-secondary d-flex flex-column align-items-center p-1" title="<?php echo t('sign_in'); ?>" style="font-size: 0.7rem;">
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
        <button type="button" class="btn-close" data-bs-offcanvas="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <!-- Will be populated if needed, or stick to categories for now -->
        <div class="list-group list-group-flush">
            <?php foreach ($headerCategories as $mainCat): ?>
                <a href="products.php?category=<?php echo $mainCat['id']; ?>" class="list-group-item list-group-item-action py-3 d-flex align-items-center gap-3">
                    <i class="<?php echo isset($categoryIcons[$mainCat['id']]) ? $categoryIcons[$mainCat['id']] : 'fas fa-folder'; ?> text-secondary" style="width: 24px;"></i>
                    <?php echo htmlspecialchars($mainCat['name_en']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<main class="pt-3 pb-4">
