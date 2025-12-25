<!-- Mini Product Card Component (Ultra Compact) -->
<?php
// Expected variables: $product, $productModel
$images = $productModel->getProductImages($product['id']);
$imageUrl = !empty($images) ? $images[0]['image_url'] : 'img/product-placeholder.jpg';
$isBestSeller = isset($product['is_best_seller']) && $product['is_best_seller'] == 1;
$isNewArrival = isset($product['is_new_arrival']) && $product['is_new_arrival'] == 1;
?>

<div class="card product-card h-100 border-0 shadow-sm overflow-hidden position-relative">
    <a href="product.php?id=<?php echo $product['id']; ?>" class="stretched-link z-1"></a>
    
    <div class="position-relative bg-light">
        <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
             class="card-img-top object-fit-cover" 
             alt="<?php echo htmlspecialchars($product['name_en'] ?? ''); ?>"
             style="height: 100px;"
             loading="lazy">

        <!-- Compact Badges -->
        <div class="position-absolute top-0 start-0 p-1 d-flex flex-column gap-1 z-2">
            <?php 
            $isFlashSale = isset($product['start_date']) && isset($product['end_date']);
            if ($isFlashSale): ?>
                <span class="badge bg-warning p-1" title="Flash Sale"><i class="fas fa-bolt"></i></span>
            <?php elseif ($isNewArrival): ?>
                <span class="badge bg-info p-1 small" style="font-size: 0.65rem;">New</span>
            <?php elseif ($isBestSeller): ?>
                <span class="badge bg-primary p-1 small" style="font-size: 0.65rem;">Best</span>
            <?php endif; ?>

            <?php if (isset($product['discount_percentage']) && $product['discount_percentage'] > 0): ?>
                <span class="badge bg-danger p-1 small" style="font-size: 0.65rem;">-<?php echo (int)$product['discount_percentage']; ?>%</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-body p-2 d-flex flex-column">
        <h6 class="card-title mb-1 text-dark text-truncate small fw-bold">
            <?php echo htmlspecialchars($product['name_en'] ?? ''); ?>
        </h6>

        <div class="mt-auto">
            <span class="text-danger fw-bold d-block small"><?php echo format_price($product['final_price'] ?? $product['price']); ?></span>
            <?php if (isset($product['price']) && isset($product['final_price']) && $product['price'] > $product['final_price']): ?>
                <span class="text-muted text-decoration-line-through d-block" style="font-size: 0.7rem;"><?php echo format_price($product['price']); ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>
