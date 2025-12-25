<?php
// 404.php - Error page for QwenShop
require_once 'includes/header.php';
?>

<div class="container-xxl pb-5">
    <div class="card border-0 shadow-sm mx-auto text-center p-5" style="max-width: 800px;">
        <div class="mb-4">
            <i class="fas fa-exclamation-triangle text-danger" style="font-size: 80px;"></i>
        </div>
        <h1 class="display-1 fw-bold text-dark mb-2">404</h1>
        <h2 class="h4 text-secondary mb-4"><?php echo t('page_not_found'); ?></h2>
        <p class="text-muted fs-5 mb-5 mx-auto" style="max-width: 600px;">
            Oops! The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
        </p>
        <div class="d-flex gap-3 justify-content-center">
            <a href="index.php" class="btn btn-danger btn-lg rounded-pill px-4 fw-bold">
                <i class="fas fa-home me-2"></i> <?php echo t('back_to_home'); ?>
            </a>
            <a href="products.php" class="btn btn-outline-dark btn-lg rounded-pill px-4 fw-bold">
                <i class="fas fa-shopping-bag me-2"></i> <?php echo t('browse_products'); ?>
            </a>
        </div>
    </div>
    
    <!-- Recommended for you section -->
    <div class="mt-5">
        <div class="d-flex align-items-center mb-4 border-bottom pb-2">
            <h2 class="h5 fw-bold mb-0 text-dark"><i class="fas fa-thumbs-up text-danger me-2"></i> Recommended For You</h2>
        </div>
        <?php 
        require_once 'models/Product.php';
        $productModel = new Product();
        $recommendedProducts = $productModel->getAll(6, 0, ['is_featured' => 1]);
        ?>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3">
            <?php if (!empty($recommendedProducts)): ?>
                <?php foreach ($recommendedProducts as $product): ?>
                    <div class="col">
                        <?php include 'includes/product-card-jd.php'; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
