<?php
require_once 'includes/header.php';
require_once 'models/Product.php';
require_once 'models/Category.php';
require_once 'config/layout_config.php';

$productModel = new Product();
$categoryModel = new Category();

// Get layout configuration
$layoutSections = getOrderedSections();
$carouselSlides = getCarouselSlides();
$carouselConfig = getCarouselConfig(); // Get carousel configuration

// Get all categories with hierarchy
$hierarchicalCategories = $categoryModel->getAllWithHierarchy();

// Fetch data for different sections
$flashSalesLimit = ($layoutSections['flash_sales']['rows'] ?? 1) * ($layoutSections['flash_sales']['cards_per_row'] ?? 6);
$flashSales = $productModel->getFlashSales($flashSalesLimit, 0);

// Get user's wishlist IDs if logged in
$userWishlistIds = [];
if (is_logged_in()) {
    require_once 'models/Wishlist.php';
    $wishlistModel = new Wishlist();
    // Optimized: We should probably just get IDs. 
    // Since Wishlist model doesn't have getIdsByUser, we'll do a raw query or add it.
    // For now, let's use a raw query here or reuse existing method if light.
    // Using PDO directly for performance to avoid hydration overhead of full objects
    $wStmt = $pdo->prepare("SELECT product_id FROM wishlists WHERE customer_id = ?");
    $wStmt->execute([get_current_user_id()]);
    $userWishlistIds = $wStmt->fetchAll(PDO::FETCH_COLUMN);
}

// Get best sellers for the first category to show initially
$bestSellersLimit = ($layoutSections['best_sellers']['rows'] ?? 1) * ($layoutSections['best_sellers']['cards_per_row'] ?? 6);
$firstCategory = !empty($hierarchicalCategories) ? $hierarchicalCategories[0] : null;
if ($firstCategory) {
    // Get best sellers from the first main category (including its subcategories)
    $bestSellers = $productModel->getAll($bestSellersLimit, 0, [
        'is_best_seller' => 1,
        'category_id' => $firstCategory['id'],
        'include_subcategories' => true
    ]);
} else {
    // Fallback to all best sellers if no categories exist
    $bestSellers = $productModel->getAll($bestSellersLimit, 0, ['is_best_seller' => 1]);
}

$newArrivalsLimit = ($layoutSections['new_arrivals']['rows'] ?? 1) * ($layoutSections['new_arrivals']['cards_per_row'] ?? 6);
$newArrivals = $productModel->getAll($newArrivalsLimit, 0, ['is_new_arrival' => 1, 'sort' => 'newest']);
$featured = $productModel->getAll(6, 0, ['is_featured' => 1]);
?>

<!-- JD.com Style Homepage -->
<div class="pb-5 text-body">
    <!-- Hero Banner Section -->
    <div class="pb-2 bg-light mb-0">
        <div class="container-xxl">
            <div class="row g-2">
                <!-- Left: Category Mega Menu -->
                <div class="col-lg-3 d-none d-lg-block" style="position: relative; z-index: 1010;">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-danger text-white py-3 border-0 rounded-top-3">
                            <h3 class="h6 fw-bold mb-0 text-uppercase"><i class="fas fa-bars me-2"></i> <?php echo t('all_categories') ?? 'Categories'; ?></h3>
                        </div>
                        <style>
                            .category-item:hover .subcategory-popup {
                                display: block !important;
                            }
                            .list-group-flush > .list-group-item:last-child {
                                border-bottom-left-radius: var(--bs-border-radius-lg, .5rem) !important;
                                border-bottom-right-radius: var(--bs-border-radius-lg, .5rem) !important;
                            }
                            .subcategory-popup {
                                display: none;
                                position: absolute;
                                left: 100%;
                                top: 0;
                                width: 700px;
                                min-height: 100%;
                                height: auto;
                                z-index: 1015;
                                background: white;
                                box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
                                border-radius: 0 1rem 1rem 0;
                                padding: 1.5rem;
                                border-left: 1px solid rgba(0,0,0,.05);
                            }
                        </style>
                        <ul class="list-group list-group-flush position-relative">
                            <?php 
                            $categoryIcons = [
                                1 => 'fas fa-laptop',
                                2 => 'fas fa-microchip',
                                3 => 'fas fa-gamepad',
                                4 => 'fas fa-blender',
                                5 => 'fas fa-plug',
                                6 => 'fas fa-mobile-alt',
                                7 => 'fas fa-wifi',
                                8 => 'fas fa-headphones',
                                9 => 'fas fa-clock'
                            ];
                            foreach (array_slice($hierarchicalCategories, 0, 10) as $mainCat): 
                                $iconClass = isset($categoryIcons[$mainCat['id']]) ? $categoryIcons[$mainCat['id']] : ($mainCat['icon_class'] ?: 'fas fa-folder');
                                $hasSubs = !empty($mainCat['subcategories']);
                            ?>
                                <li class="list-group-item list-group-item-action border-0 p-0 position-relative category-item">
                                    <a href="products.php?category=<?php echo $mainCat['id']; ?>" class="d-flex align-items-center justify-content-between text-decoration-none text-dark py-2 px-3 w-100 h-100">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                <i class="<?php echo $iconClass; ?> text-danger"></i>
                                            </div>
                                            <span class="fw-semibold text-truncate" style="max-width: 180px;"><?php echo htmlspecialchars($mainCat['name_en']); ?></span>
                                        </div>
                                        <?php if ($hasSubs): ?>
                                            <i class="fas fa-chevron-right opacity-50 small" style="font-size: 0.75rem;"></i>
                                        <?php endif; ?>
                                    </a>

                                    <?php if ($hasSubs): ?>
                                        <div class="subcategory-popup">
                                            <div class="h-100">
                                                <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                                                    <h5 class="fw-bold mb-0 text-danger me-2"><?php echo htmlspecialchars($mainCat['name_en']); ?></h5>
                                                    <a href="products.php?category=<?php echo $mainCat['id']; ?>" class="small text-muted text-decoration-none">View All <i class="fas fa-arrow-right ms-1"></i></a>
                                                </div>
                                                <div class="row g-3">
                                                    <?php foreach ($mainCat['subcategories'] as $sub): ?>
                                                        <div class="col-6 col-md-4">
                                                            <a href="products.php?category=<?php echo $sub['id']; ?>" class="d-block text-decoration-none text-dark hover-text-danger mb-2">
                                                                <span class="fw-bold small"><?php echo htmlspecialchars($sub['name_en']); ?></span>
                                                            </a>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Center: Main Banner Carousel -->
                <div class="col-lg-6 col-md-8">
                    <?php
                    // Prepare dynamic classes and attributes
                    $carouselClasses = ['carousel', 'slide', 'rounded-3', 'shadow-sm', 'overflow-hidden'];
                    $zoomStyle = false;
                    
                    if (($carouselConfig['animation_type'] ?? 'slide') === 'fade') {
                        $carouselClasses[] = 'carousel-fade';
                    } elseif (($carouselConfig['animation_type'] ?? 'slide') === 'zoom') {
                        $carouselClasses[] = 'carousel-fade'; // Zoom implies fading for smoother transition
                        $zoomStyle = true;
                    }
                    
                    $isAutoplay = $carouselConfig['autoplay'] ?? true;
                    $interval = $isAutoplay ? ($carouselConfig['interval'] ?? 5000) : 'false';
                    $ride = $isAutoplay ? 'carousel' : 'false';
                    $pause = ($carouselConfig['pause_on_hover'] ?? true) ? 'hover' : 'false';
                    $wrap = ($carouselConfig['infinite_loop'] ?? true) ? 'true' : 'false';
                    $duration = $carouselConfig['animation_duration'] ?? 600;
                    ?>
                    
                    <style>
                        #mainBannerCarousel .carousel-item {
                            transition-duration: <?php echo $duration; ?>ms;
                        }
                        
                        #mainBannerCarousel .carousel-control-prev,
                        #mainBannerCarousel .carousel-control-next {
                            width: 60px !important;
                            height: 60px !important;
                            top: 50% !important;
                            transform: translateY(-50%) !important;
                            opacity: 1 !important;
                            z-index: 2000 !important;
                            display: flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            background: none !important;
                            border: none !important;
                        }
                        
                        #mainBannerCarousel .carousel-control-prev { left: 15px !important; }
                        #mainBannerCarousel .carousel-control-next { right: 15px !important; }
                        
                        .carousel-nav-btn {
                            width: 48px;
                            height: 48px;
                            background-color: rgba(0, 0, 0, 0.6) !important;
                            border: 2px solid rgba(255, 255, 255, 0.3) !important;
                            color: white !important;
                            border-radius: 50% !important;
                            display: flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3) !important;
                        }
                        
                        #mainBannerCarousel .carousel-control-prev:hover .carousel-nav-btn,
                        #mainBannerCarousel .carousel-control-next:hover .carousel-nav-btn {
                            background-color: #dc3545 !important;
                            border-color: white !important;
                            transform: scale(1.1) !important;
                            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.5) !important;
                        }

                        <?php if ($zoomStyle): ?>
                        #mainBannerCarousel .carousel-item.active .d-block {
                            animation: zoomInEffect <?php echo $duration + 4000; ?>ms ease-out forwards;
                        }
                        @keyframes zoomInEffect {
                            from { transform: scale(1); }
                            to { transform: scale(1.1); }
                        }
                        <?php endif; ?>
                    </style>

                    <div id="mainBannerCarousel" class="<?php echo implode(' ', $carouselClasses); ?>" 
                         data-bs-ride="<?php echo $ride; ?>" 
                         data-bs-interval="<?php echo $interval; ?>"
                         data-bs-pause="<?php echo $pause; ?>"
                         style="height: <?php echo $carouselConfig['height']; ?> !important; width: <?php echo $carouselConfig['width']; ?> !important; border-radius: var(--bs-border-radius-lg, .5rem) !important; position: relative;">
                        
                        <?php if (($carouselConfig['show_indicators'] ?? true) && !empty($carouselSlides)): ?>
                        <div class="carousel-indicators" style="z-index: 1000;">
                            <?php foreach ($carouselSlides as $i => $slide): ?>
                                <button type="button" data-bs-target="#mainBannerCarousel" data-bs-slide-to="<?php echo $i; ?>" class="<?php echo $i === 0 ? 'active' : ''; ?>" aria-current="<?php echo $i === 0 ? 'true' : 'false'; ?>"></button>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <div class="carousel-inner h-100">
                            <?php if (!empty($carouselSlides)): ?>
                                <?php foreach ($carouselSlides as $i => $slide): ?>
                                    <div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?> h-100">
                                        <div class="d-block w-100 h-100 position-relative <?php echo (!$zoomStyle) ? 'animate__animated animate__fadeIn' : ''; ?>" 
                                             style="background: linear-gradient(rgba(0,0,0,<?php echo $carouselConfig['overlay_opacity']; ?>), rgba(0,0,0,<?php echo $carouselConfig['overlay_opacity']; ?>)), url('<?php echo htmlspecialchars($slide['image']); ?>') center/cover no-repeat; height: <?php echo $carouselConfig['height']; ?> !important;">
                                            <div class="carousel-caption d-flex flex-column justify-content-center h-100 text-<?php echo $carouselConfig['cta_alignment'] ?? 'center'; ?> pb-5" style="color: <?php echo $carouselConfig['text_color'] ?? '#ffffff'; ?>;">
                                                <h2 class="display-5 fw-bold mb-3" style="color: inherit;"><?php echo htmlspecialchars($slide['title']); ?></h2>
                                                <p class="lead mb-4 d-none d-md-block" style="color: inherit; opacity: 0.9;"><?php echo htmlspecialchars($slide['description']); ?></p>
                                                <div>
                                                    <a href="<?php echo htmlspecialchars($slide['button_url']); ?>" class="btn btn-danger btn-lg rounded-pill px-4 fw-bold shadow">
                                                        <?php echo htmlspecialchars($slide['button_text']); ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="carousel-item active h-100">
                                    <div class="d-block w-100 h-100 bg-danger text-white d-flex align-items-center justify-content-center" style="height: <?php echo $carouselConfig['height']; ?> !important;">
                                        <div class="text-center">
                                            <h2 class="display-4 fw-bold mb-3"><?php echo t('welcome'); ?> <?php echo htmlspecialchars(get_setting('site_title', 'QwenShop')); ?></h2>
                                            <p class="lead mb-4">Discover premium electronics and accessories</p>
                                            <a href="products.php" class="btn btn-light btn-lg rounded-pill px-5 fw-bold text-danger shadow-sm">Shop Now</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($carouselConfig['show_navigation'] ?? true): ?>
                            <button class="carousel-control-prev" type="button" data-bs-target="#mainBannerCarousel" data-bs-slide="prev">
                                <div class="carousel-nav-btn">
                                    <i class="fas fa-chevron-left"></i>
                                </div>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#mainBannerCarousel" data-bs-slide="next">
                                <div class="carousel-nav-btn">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                                <span class="visually-hidden">Next</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right: Quick Access Cards -->
                <div class="col-lg-3 col-md-4 d-none d-md-block" style="height: <?php echo $carouselConfig['height']; ?> !important;">
                    <div class="d-flex flex-column gap-2 h-100">
                        <div class="flex-grow-1" style="min-height: 0;">
                            <div class="card border-0 shadow-sm rounded-3 transition-all h-100 overflow-hidden" style="background: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%);">
                                <a href="products.php?sort=discount" class="stretched-link z-1 text-decoration-none">
                                    <div class="card-body p-3 d-flex flex-column justify-content-between text-white h-100">
                                        <div>
                                            <i class="fas fa-bolt fa-2x mb-3 opacity-75"></i>
                                            <h4 class="fw-bold mb-1">Flash Sales</h4>
                                            <p class="small opacity-75 mb-0">Up to 70% OFF</p>
                                        </div>
                                        <div class="text-end">
                                            <i class="fas fa-arrow-right"></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="flex-grow-1" style="min-height: 0;">
                            <div class="card border-0 shadow-sm rounded-3 transition-all h-100 overflow-hidden" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                <a href="products.php?sort=newest" class="stretched-link z-1 text-decoration-none">
                                    <div class="card-body p-3 d-flex flex-column justify-content-between text-white h-100">
                                        <div>
                                            <i class="fas fa-fire fa-2x mb-3 opacity-75"></i>
                                            <h4 class="fw-bold mb-1">New Arrivals</h4>
                                            <p class="small opacity-75 mb-0">Latest Gadgets</p>
                                        </div>
                                        <div class="text-end">
                                            <i class="fas fa-arrow-right"></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="flex-grow-1" style="min-height: 0;">
                            <div class="card border-0 shadow-sm rounded-3 transition-all h-100 overflow-hidden" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                                <a href="products.php?sort=best_seller" class="stretched-link z-1 text-decoration-none">
                                    <div class="card-body p-3 d-flex flex-column justify-content-between text-white h-100">
                                        <div>
                                            <i class="fas fa-star fa-2x mb-3 opacity-75"></i>
                                            <h4 class="fw-bold mb-1">Best Sellers</h4>
                                            <p class="small opacity-75 mb-0">Customer Favorites</p>
                                        </div>
                                        <div class="text-end">
                                            <i class="fas fa-arrow-right"></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Render sections based on layout configuration -->
    <?php foreach ($layoutSections as $sectionKey => $section): ?>
        <?php if (!$section['enabled']) continue; ?>

        <?php if ($sectionKey === 'flash_sales'): ?>
        <!-- Flash Sales Section -->
        <section class="flash-sales pb-0 mb-0">
            <div class="container-xxl text-body">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-4 bg-white">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <h2 class="h4 fw-bold text-dark mb-0"><i class="fas fa-bolt text-danger me-2"></i> Flash Sales</h2>
                            <div class="d-flex align-items-center gap-2 bg-light rounded-pill px-3 py-1 shadow-sm">
                                <span class="small fw-bold text-muted">Ends in:</span>
                                <?php if (!empty($flashSales) && isset($flashSales[0]['end_date'])): ?>
                                    <?php
                                    $endDateTime = new DateTime($flashSales[0]['end_date']);
                                    $now = new DateTime();
                                    $diffSeconds = max(0, $endDateTime->getTimestamp() - $now->getTimestamp());
                                    $hours = floor($diffSeconds / 3600);
                                    $minutes = floor(($diffSeconds % 3600) / 60);
                                    $seconds = $diffSeconds % 60;
                                    ?>
                                    <div class="d-flex align-items-center gap-1">
                                        <span id="fs-hours" class="badge bg-danger rounded-1 shadow-sm"><?php echo str_pad($hours, 2, '0', STR_PAD_LEFT); ?></span>
                                        <span class="text-danger fw-bold">:</span>
                                        <span id="fs-minutes" class="badge bg-danger rounded-1 shadow-sm"><?php echo str_pad($minutes, 2, '0', STR_PAD_LEFT); ?></span>
                                        <span class="text-danger fw-bold">:</span>
                                        <span id="fs-seconds" class="badge bg-danger rounded-1 shadow-sm"><?php echo str_pad($seconds, 2, '0', STR_PAD_LEFT); ?></span>
                                    </div>
                                    <div id="nearest-flash-sale-end" style="display:none;" data-end-time="<?php echo $flashSales[0]['end_date']; ?>"></div>
                                <?php else: ?>
                                    <div class="d-flex align-items-center gap-1">
                                        <span id="fs-hours" class="badge bg-secondary rounded-1 shadow-sm">00</span>
                                        <span class="text-secondary fw-bold">:</span>
                                        <span id="fs-minutes" class="badge bg-secondary rounded-1 shadow-sm">00</span>
                                        <span class="text-secondary fw-bold">:</span>
                                        <span id="fs-seconds" class="badge bg-secondary rounded-1 shadow-sm">00</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <a href="products.php?sort=discount" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="card-body p-4 bg-light-subtle">
                        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-<?php echo $section['cards_per_row'] ?? 6; ?> g-3">
                            <?php if (!empty($flashSales)): ?>
                                <?php foreach ($flashSales as $product): ?>
                                    <div class="col">
                                        <?php include 'includes/product-card-jd.php'; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12 py-5 text-center">
                                    <p class="text-muted">No flash sales at the moment.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($sectionKey === 'best_sellers'): ?>
        <!-- Best Sellers by Category -->
        <section class="best-sellers pb-0 mb-0">
            <div class="container-xxl text-body">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-4 bg-white">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <h2 class="h4 fw-bold text-dark mb-0"><i class="fas fa-fire text-danger me-2"></i> Best Sellers</h2>
                        <ul class="nav nav-pills gap-2" id="bestSellersTabs" role="tablist">
                            <?php foreach (array_slice($hierarchicalCategories, 0, 5) as $index => $cat): ?>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link rounded-pill border-0 fw-bold small text-uppercase <?php echo $index === 0 ? 'active' : 'text-muted bg-light'; ?>" 
                                            id="cat-tab-<?php echo $cat['id']; ?>" 
                                            data-bs-toggle="pill" 
                                            type="button" 
                                            role="tab" 
                                            data-category="<?php echo $cat['id']; ?>"
                                            style="<?php echo $index === 0 ? 'background-color: var(--bs-danger); color: white;' : ''; ?>">
                                        <?php echo htmlspecialchars($cat['name_en']); ?>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="card-body p-4">
                        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-<?php echo $section['cards_per_row'] ?? 6; ?> g-3 bestsellers-grid">
                            <?php if (!empty($bestSellers)): ?>
                                <?php foreach ($bestSellers as $product): ?>
                                    <div class="col">
                                        <?php include 'includes/product-card-jd.php'; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12 text-center py-5">
                                    <p class="text-muted">No products found in this category.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($sectionKey === 'category_showcase'): ?>
        <!-- Category Showcase Blocks -->
        <section class="category-showcase pb-0 mb-0">
            <div class="container-xxl text-body">
                <div class="row g-4">
                    <?php
                    $showcaseCategories = array_slice($hierarchicalCategories, 0, 6);
                    foreach ($showcaseCategories as $cat):
                        $subcatIds = array_column($cat['subcategories'] ?? [], 'id');
                        if (empty($subcatIds)) $subcatIds = [$cat['id']];
                        $catProducts = $productModel->getAll(4, 0, ['category_ids' => $subcatIds]);
                    ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="card border-0 shadow-sm rounded-3 transition-all">
                                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                                    <h3 class="h6 fw-bold mb-0 text-dark"><?php echo htmlspecialchars($cat['name_en']); ?></h3>
                                    <a href="products.php?category=<?php echo $cat['id']; ?>" class="btn btn-link btn-sm text-danger text-decoration-none p-0 fw-bold">
                                        More <i class="fas fa-chevron-right ms-1"></i>
                                    </a>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-2">
                                        <?php foreach ($catProducts as $prod):
                                            $img = $productModel->getProductImages($prod['id']);
                                            $imgUrl = !empty($img) ? $img[0]['image_url'] : 'img/product-placeholder.jpg';
                                        ?>
                                            <div class="col-6">
                                                <a href="product.php?id=<?php echo $prod['id']; ?>" class="d-block text-decoration-none bg-light rounded-3 p-2 h-100 transition-all border">
                                                    <div class="text-center mb-2">
                                                        <img src="<?php echo htmlspecialchars($imgUrl); ?>" alt="<?php echo htmlspecialchars($prod['name_en']); ?>" class="img-fluid rounded object-fit-cover" style="height: 100px; width: 100%;">
                                                    </div>
                                                    <div class="text-center">
                                                        <p class="small text-dark text-truncate mb-1"><?php echo htmlspecialchars($prod['name_en']); ?></p>
                                                        <span class="fw-bold text-danger small"><?php echo format_price($prod['final_price']); ?></span>
                                                    </div>
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($sectionKey === 'official_brands'): ?>
        <!-- Brand Zone -->
        <section class="brand-zone pb-0 mb-0">
            <div class="container-xxl text-body">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-4 bg-white">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <h2 class="h4 fw-bold text-dark mb-0"><i class="fas fa-award text-danger me-2"></i> Official Brands</h2>
                    </div>
                    <div class="card-body p-4">
                        <div class="row row-cols-2 row-cols-md-4 row-cols-lg-<?php echo $section['cards_per_row'] ?? 6; ?> g-4 text-center">
                            <?php
                            $brands = ['Samsung', 'Apple', 'Sony', 'LG', 'NVIDIA', 'AMD', 'Intel', 'Asus', 'HP', 'Dell', 'Lenovo', 'Microsoft'];
                            foreach ($brands as $brand):
                            ?>
                                <div class="col">
                                    <div class="p-4 bg-light rounded-3 shadow-sm h-100 d-flex align-items-center justify-content-center transition-all border">
                                        <strong class="text-muted opacity-75 fs-5"><?php echo $brand; ?></strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if ($sectionKey === 'new_arrivals'): ?>
        <!-- New Arrivals -->
        <section class="new-arrivals pb-0 mb-0">
            <div class="container-xxl text-body">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-4 bg-white">
                    <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <h2 class="h4 fw-bold text-dark mb-0"><i class="fas fa-star text-warning me-2"></i> New Arrivals</h2>
                        <a href="products.php?sort=newest" class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-bold">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="card-body p-4 bg-light-subtle">
                        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-<?php echo $section['cards_per_row'] ?? 6; ?> g-3">
                            <?php if (!empty($newArrivals)): ?>
                                <?php foreach ($newArrivals as $product): ?>
                                    <div class="col">
                                        <?php include 'includes/product-card-jd.php'; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>

    <?php endforeach; ?>
</div>

<script>
    // Countdown timer
    function updateCountdown() {
        const hoursEl = document.getElementById('fs-hours');
        const minutesEl = document.getElementById('fs-minutes');
        const secondsEl = document.getElementById('fs-seconds');
        const endTimeElement = document.getElementById('nearest-flash-sale-end');

        if (!hoursEl || !minutesEl || !secondsEl || !endTimeElement) return;

        const endTimeStr = endTimeElement.getAttribute('data-end-time');
        if (!endTimeStr) return;

        const endTime = new Date(endTimeStr);
        const now = new Date();
        const diff = endTime - now;

        if (diff <= 0) {
            hoursEl.textContent = '00';
            minutesEl.textContent = '00';
            secondsEl.textContent = '00';
            return;
        }

        let hours = Math.floor(diff / (1000 * 60 * 60));
        let minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        let seconds = Math.floor((diff % (1000 * 60)) / 1000);

        hoursEl.textContent = String(hours).padStart(2, '0');
        minutesEl.textContent = String(minutes).padStart(2, '0');
        secondsEl.textContent = String(seconds).padStart(2, '0');
    }

    if (document.getElementById('fs-hours')) {
        updateCountdown();
        setInterval(updateCountdown, 1000);
    }

    // Best Sellers Tabs
    document.querySelectorAll('#bestSellersTabs button').forEach(btn => {
        btn.addEventListener('click', function() {
            // Update UI buttons
            document.querySelectorAll('#bestSellersTabs button').forEach(b => {
                b.classList.remove('active', 'bg-danger', 'text-white');
                b.classList.add('bg-light', 'text-muted');
            });
            this.classList.add('active', 'bg-danger', 'text-white');
            this.classList.remove('bg-light', 'text-muted');

            const categoryId = this.getAttribute('data-category');
            const grid = document.querySelector('.bestsellers-grid');
            
            // Show loading
            grid.classList.add('opacity-50');
            
            fetch(`api/products.php?category=${categoryId}&limit=<?php echo $bestSellersLimit; ?>&is_best_seller=1&include_subcategories=1`)
                .then(response => response.json())
                .then(data => {
                    grid.classList.remove('opacity-50');
                    if (data.success && data.products) {
                        grid.innerHTML = '';
                        data.products.forEach(product => {
                            const col = document.createElement('div');
                            col.className = 'col';
                            
                            let imageUrl = 'img/product-placeholder.jpg';
                            if (product.images && product.images.length > 0) {
                                imageUrl = product.images[0].image_url;
                            } else if (product.image_url) {
                                imageUrl = product.image_url;
                            }

                            const stock = (product.stock_quantity ?? product.stock ?? 0);
                            const isStock = stock > 0;
                            const badge = product.discount_percentage > 0 ? `<span class="badge bg-danger shadow-sm">-${Math.round(product.discount_percentage)}%</span>` : '';
                            const userWishlistIds = <?php echo json_encode($userWishlistIds); ?>;
                            const isWishlisted = userWishlistIds.some(id => id.toString() === product.id.toString());

                            col.innerHTML = `
                                <div class="card product-card h-100 border-0 shadow-sm transition-all overflow-hidden position-relative">
                                    <a href="product.php?id=${product.id}" class="stretched-link z-1"></a>
                                    <div class="position-relative bg-light text-center">
                                        <img src="${imageUrl}" class="card-img-top object-fit-cover" alt="${product.name_en}" style="height: 180px;" loading="lazy">
                                        <div class="position-absolute top-0 start-0 p-2 d-flex flex-column gap-1 z-2">
                                            <span class="badge bg-primary text-white shadow-sm">Best Seller</span>
                                            ${badge}
                                        </div>
                                        <button class="btn btn-white btn-sm rounded-circle shadow-sm position-absolute top-0 end-0 m-2 z-2 add-to-wishlist-btn" 
                                                data-product-id="${product.id}" style="width: 32px; height: 32px; padding: 0;">
                                            <i class="${isWishlisted ? 'fas text-danger' : 'far text-muted'} fa-heart"></i>
                                        </button>
                                    </div>
                                    <div class="card-body p-3 d-flex flex-column">
                                        <h6 class="card-title mb-2 text-dark small fw-bold" style="height: 2.4em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                            ${product.name_en}
                                        </h6>
                                        <div class="mt-auto">
                                            <div class="d-flex align-items-center justify-content-between g-2">
                                                <div class="d-flex flex-column">
                                                    <span class="text-danger fw-bold fs-5">${formatCurrency(product.final_price || product.price)}</span>
                                                    ${parseFloat(product.price) > parseFloat(product.final_price || product.price) ? `<span class="text-muted text-decoration-line-through small" style="font-size: 0.7rem;">${formatCurrency(product.price)}</span>` : ''}
                                                </div>
                                                <button class="btn btn-danger btn-sm rounded-3 shadow-sm z-2 add-to-cart-btn" 
                                                        data-product-id="${product.id}" ${!isStock ? 'disabled' : ''} style="width: 30px; height: 30px; padding: 0;">
                                                    <i class="fas fa-shopping-cart fa-xs"></i>
                                                </button>
                                            </div>
                                            <div class="mt-2 small ${isStock ? 'text-success' : 'text-danger'} fw-bold" style="font-size: 0.7rem;">
                                                <i class="fas ${isStock ? 'fa-check-circle' : 'fa-times-circle'} me-1"></i>
                                                ${isStock ? 'In Stock' : 'Out of Stock'}
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                            grid.appendChild(col);
                        });
                    } else {
                        grid.innerHTML = '<div class="col-12 py-5 text-center text-muted">No products found.</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    grid.classList.remove('opacity-50');
                    grid.innerHTML = '<div class="col-12 py-5 text-center text-danger">Failed to load products.</div>';
                });
        });
    });

    function formatCurrency(amount) {
        return new Intl.NumberFormat('en-DZ', { minimumFractionDigits: 0 }).format(amount) + ' DA';
    }
</script>

<?php require_once 'includes/footer.php'; ?>
