<!-- Product Card Component -->
<?php
// Expected variables: $product, $productModel
$images = $productModel->getProductImages($product['id']);
$imageUrl = !empty($images) ? $images[0]['image_url'] : 'img/product-placeholder.jpg';
?>
<div class="card product-card h-100 border-0 shadow-sm">
    <div class="position-relative overflow-hidden">
        <a href="product.php?id=<?php echo $product['id']; ?>" class="d-block">
            <img src="<?php echo htmlspecialchars($imageUrl); ?>" class="card-img-top object-fit-cover" alt="<?php echo htmlspecialchars($product['name_en']); ?>" style="height: 200px;">
        </a>
        <?php if ($product['discount_percentage'] > 0): ?>
            <div class="badge bg-danger position-absolute top-0 start-0 m-3 fs-6">-<?php echo (int)$product['discount_percentage']; ?>%</div>
        <?php endif; ?>
    </div>
    <div class="card-body d-flex flex-column p-4">
        <h5 class="card-title h6 mb-2">
            <a href="product.php?id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark hover-danger line-clamp-2">
                <?php echo htmlspecialchars($product['name_en']); ?>
            </a>
        </h5>
        
        <div class="mb-2">
            <div class="text-warning small mb-1">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="<?php echo $i <= (int)$product['rating'] ? 'fas' : 'far'; ?> fa-star"></i>
                <?php endfor; ?>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="text-danger fw-bold fs-5">
                    <?php echo format_price($product['discount_percentage'] > 0 ? $product['final_price'] : $product['price']); ?>
                </span>
                <?php if ($product['discount_percentage'] > 0): ?>
                    <span class="text-muted text-decoration-line-through small">
                        <?php echo format_price($product['price']); ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <button class="btn btn-danger mt-auto w-100 fw-bold add-to-cart" data-product-id="<?php echo $product['id']; ?>">
            <i class="fas fa-shopping-cart me-2"></i><?php echo t('add_to_cart'); ?>
        </button>
    </div>
</div>
