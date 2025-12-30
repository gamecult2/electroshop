<!-- List View Product Card Component -->
<?php
// Expected variables: $product, $productModel
$images = $productModel->getProductImages($product['id']);
$imageUrl = !empty($images) ? $images[0]['image_url'] : 'img/product-placeholder.jpg';
$isBestSeller = isset($product['is_best_seller']) && $product['is_best_seller'] == 1;
$isNewArrival = isset($product['is_new_arrival']) && $product['is_new_arrival'] == 1;
$stock = $product['stock_quantity'] ?? $product['stock'] ?? 0;
$inStock = $stock > 0;
?>

<div class="card product-card h-100 border-0 shadow-sm overflow-hidden mb-3">
    <div class="row g-0">
        <div class="col-md-3 col-xl-2">
            <div class="position-relative h-100 bg-light">
                <a href="product.php?id=<?php echo $product['id']; ?>" class="d-block h-100">
                    <img src="<?php echo htmlspecialchars($imageUrl); ?>" 
                         class="img-fluid object-fit-contain w-100 h-100 p-2" 
                         alt="<?php echo htmlspecialchars($product['name_en']); ?>"
                         style="min-height: 160px; max-height: 200px;">
                </a>
                
                <!-- Status Badges -->
                <div class="position-absolute top-0 start-0 p-2 d-flex flex-column gap-1 z-2">
                    <?php if ($isNewArrival): ?>
                        <span class="badge bg-info text-white shadow-sm x-small">New</span>
                    <?php elseif ($isBestSeller): ?>
                        <span class="badge bg-primary text-white shadow-sm x-small">Best Seller</span>
                    <?php endif; ?>
                    
                    <?php if ($product['discount_percentage'] > 0): ?>
                        <span class="badge bg-danger shadow-sm x-small">-<?php echo (int)$product['discount_percentage']; ?>%</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-9 col-xl-10">
            <div class="card-body h-100 d-flex flex-column p-3 p-md-4">
                <div class="row h-100">
                    <div class="col-md-8 d-flex flex-column">
                        <h5 class="card-title h6 mb-2">
                            <a href="product.php?id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark fw-bold text-hover-primary">
                                <?php echo htmlspecialchars($product['name_en']); ?>
                            </a>
                        </h5>
                        
                        <!-- Short Description -->
                        <?php if (!empty($product['short_description_en'])): ?>
                            <p class="text-muted small mb-3 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <?php echo htmlspecialchars($product['short_description_en']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="mt-auto">
                            <?php $isWishlisted = isset($userWishlistIds) && in_array($product['id'], $userWishlistIds); ?>
                            <button class="btn btn-link text-muted p-0 text-decoration-none small add-to-wishlist-btn" data-product-id="<?php echo $product['id']; ?>">
                                <i class="<?php echo $isWishlisted ? 'fas text-danger' : 'far'; ?> fa-heart me-1"></i> Add to Wishlist
                            </button>
                        </div>
                    </div>
                    
                    <div class="col-md-4 border-start-md ps-md-4 d-flex flex-column justify-content-center">
                        <div class="mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-danger fw-bold fs-4"><?php echo format_price($product['final_price'] ?? $product['price']); ?></span>
                                <?php if (isset($product['price']) && isset($product['final_price']) && $product['price'] > $product['final_price']): ?>
                                    <span class="text-muted text-decoration-line-through small"><?php echo format_price($product['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Stock Status -->
                            <div class="small <?php echo $inStock ? 'text-success' : 'text-danger'; ?> fw-bold mt-1">
                                <i class="fas <?php echo $inStock ? 'fa-check-circle' : 'fa-times-circle'; ?> me-1"></i>
                                <?php echo $inStock ? 'In Stock' : 'Out of Stock'; ?>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-dark btn-sm rounded-pill fw-bold">View Details</a>
                            <button class="btn btn-danger btn-sm rounded-pill fw-bold add-to-cart-btn" 
                                    data-product-id="<?php echo $product['id']; ?>"
                                    <?php echo !$inStock ? 'disabled' : ''; ?>>
                                <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
