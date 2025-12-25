<!-- Compact Modern Product Card Component -->
<?php
// Expected variables: $product, $productModel
$images = $productModel->getProductImages($product['id']);
$imageUrl = !empty($images) ? $images[0]['image_url'] : 'img/product-placeholder.jpg';
$isBestSeller = isset($product['is_best_seller']) && $product['is_best_seller'] == 1;
$isNewArrival = isset($product['is_new_arrival']) && $product['is_new_arrival'] == 1;
// Check stock_quantity first, fallback to stock (if aliased), default to 0
$stock = $product['stock_quantity'] ?? $product['stock'] ?? 0;
$inStock = $stock > 0;
$rating = isset($product['rating']) ? floatval($product['rating']) : 4.5;
$reviewCount = isset($product['review_count']) ? intval($product['review_count']) : 0;
?>

<div class="card product-card h-100 border-0 shadow-sm overflow-hidden position-relative">
    <a href="product.php?id=<?php echo $product['id']; ?>" class="stretched-link z-1"></a>
    
    <div class="position-relative bg-light">
        <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
             class="card-img-top object-fit-cover" 
             alt="<?php echo htmlspecialchars($product['name_en']); ?>"
             style="height: 180px;"
             loading="lazy">

        <!-- Status Badges -->
        <div class="position-absolute top-0 start-0 p-2 d-flex flex-column gap-1 z-2">
            <?php 
            $isFlashSale = isset($product['start_date']) && isset($product['end_date']);
            if ($isFlashSale): ?>
                <span class="badge bg-warning text-dark shadow-sm"><i class="fas fa-bolt me-1"></i> Flash Sale</span>
            <?php elseif ($isNewArrival): ?>
                <span class="badge bg-info text-white shadow-sm">New Arrival</span>
            <?php elseif ($isBestSeller): ?>
                <span class="badge bg-primary text-white shadow-sm">Best Seller</span>
            <?php endif; ?>

            <?php if ($product['discount_percentage'] > 0): ?>
                <span class="badge bg-danger shadow-sm">-<?php echo (int)$product['discount_percentage']; ?>%</span>
            <?php endif; ?>
        </div>

        <!-- Wishlist Button -->
        <?php $isWishlisted = isset($userWishlistIds) && in_array($product['id'], $userWishlistIds); ?>
        <button class="btn btn-white btn-sm rounded-circle shadow-sm position-absolute top-0 end-0 m-2 z-2 add-to-wishlist-btn" 
                data-product-id="<?php echo $product['id']; ?>"
                aria-label="Add to wishlist"
                style="width: 32px; height: 32px; padding: 0;">
            <i class="<?php echo $isWishlisted ? 'fas' : 'far'; ?> fa-heart <?php echo $isWishlisted ? 'text-danger' : 'text-muted'; ?>"></i>
        </button>
    </div>

    <div class="card-body p-3 d-flex flex-column">
        <h6 class="card-title mb-2 text-dark small fw-bold" style="height: 2.4em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
            <?php echo htmlspecialchars($product['name_en']); ?>
        </h6>

        <!-- Price and Cart Row -->
        <div class="mt-auto">
            <div class="d-flex align-items-center justify-content-between g-2">
                <div class="d-flex flex-column">
                    <span class="text-danger fw-bold fs-5"><?php echo format_price($product['final_price'] ?? $product['price']); ?></span>
                    <?php if (isset($product['price']) && isset($product['final_price']) && $product['price'] > $product['final_price']): ?>
                        <span class="text-muted text-decoration-line-through small" style="font-size: 0.7rem;"><?php echo format_price($product['price']); ?></span>
                    <?php endif; ?>
                </div>
                <button class="btn btn-danger btn-sm rounded-3 shadow-sm z-2 add-to-cart-btn" 
                        data-product-id="<?php echo $product['id']; ?>"
                        <?php echo !$inStock ? 'disabled' : ''; ?>
                        aria-label="Add to cart"
                        style="width: 30px; height: 30px; padding: 0;">
                    <i class="fas fa-shopping-cart fa-xs"></i>
                </button>
            </div>

            <!-- Stock Status -->
            <div class="mt-2 small <?php echo $inStock ? 'text-success' : 'text-danger'; ?> fw-bold" style="font-size: 0.7rem;">
                <i class="fas <?php echo $inStock ? 'fa-check-circle' : 'fa-times-circle'; ?> me-1"></i>
                <?php echo $inStock ? 'In Stock' : 'Out of Stock'; ?>
            </div>
        </div>
    </div>
</div>

<script>
if (typeof colorSwatchInitialized === 'undefined') {
    window.colorSwatchInitialized = true;
    document.addEventListener('click', function(e) {
        if (e.target.closest('.color-dot-compact')) {
            const dot = e.target.closest('.color-dot-compact');
            const parent = dot.closest('.color-swatches-compact');
            parent.querySelectorAll('.color-dot-compact').forEach(function(d) {
                d.classList.remove('active', 'border-primary');
            });
            dot.classList.add('active', 'border-primary');
        }
    });
}
</script>
