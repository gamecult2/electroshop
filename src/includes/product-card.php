<?php
// Canonical product-card component.
// Expected: $product, $productModel. Optional: $productCardVariant (grid|list|mini).
$productCardVariant = $productCardVariant ?? 'grid';
$images = $productModel->getProductImages($product['id']);
$imageUrl = !empty($images) ? $images[0]['image_url'] : 'img/product-placeholder.jpg';
$productId = (int)$product['id'];
$variantProductIds = $variantProductIds ?? array_flip($GLOBALS['pdo']->query('SELECT DISTINCT product_id FROM product_variants')->fetchAll(PDO::FETCH_COLUMN));
$hasVariants = isset($variantProductIds[$productId]);
$productName = localized_field($product, 'name', null, 'Product');
$stock = (int)($product['stock_quantity'] ?? $product['stock'] ?? 0);
$inStock = $stock > 0;
$price = (float)($product['price'] ?? 0);
$finalPrice = (float)($product['final_price'] ?? $price);
$discount = (int)($product['discount_percentage'] ?? 0);
$isBestSeller = !empty($product['is_best_seller']);
$isNewArrival = !empty($product['is_new_arrival']);
$isFlashSale = isset($product['start_date'], $product['end_date']);
$isWishlisted = isset($userWishlistIds) && in_array($productId, $userWishlistIds);
?>

<?php if ($productCardVariant === 'list'): ?>
<article class="card product-card product-card--list h-100 border-0 shadow-sm overflow-hidden mb-3">
    <div class="row g-0">
        <div class="col-md-3 col-xl-2 bg-light">
            <a href="product.php?id=<?php echo $productId; ?>" class="d-block h-100">
                <img src="<?php echo htmlspecialchars($imageUrl); ?>" class="product-card__image p-2" alt="<?php echo htmlspecialchars($productName); ?>" loading="lazy">
            </a>
        </div>
        <div class="col-md-9 col-xl-10">
            <div class="card-body h-100 p-3 p-md-4">
                <div class="row h-100 g-3">
                    <div class="col-md-8 d-flex flex-column">
                        <h2 class="h6 mb-2"><a href="product.php?id=<?php echo $productId; ?>" class="text-decoration-none text-dark fw-bold text-hover-primary"><?php echo htmlspecialchars($productName); ?></a></h2>
                        <?php if (localized_field($product, 'short_description') !== ''): ?><p class="text-muted small mb-3 line-clamp-2"><?php echo htmlspecialchars(localized_field($product, 'short_description')); ?></p><?php endif; ?>
                        <button type="button" class="btn btn-link text-muted p-0 text-decoration-none small add-to-wishlist-btn align-self-start mt-auto" data-product-id="<?php echo $productId; ?>" aria-label="<?php echo $isWishlisted ? 'Remove from' : 'Add to'; ?> wishlist">
                            <i class="<?php echo $isWishlisted ? 'fas text-danger' : 'far'; ?> fa-heart me-1" aria-hidden="true"></i><?php echo $isWishlisted ? 'Saved' : 'Add to Wishlist'; ?>
                        </button>
                        <button type="button" class="btn btn-link text-muted p-0 text-decoration-none small compare-product-btn align-self-start mt-2" data-product-id="<?php echo $productId; ?>">
                            <i class="fas fa-balance-scale me-1" aria-hidden="true"></i>Compare
                        </button>
                    </div>
                    <div class="col-md-4 border-start-md ps-md-4 d-flex flex-column justify-content-center">
                        <div class="mb-3"><span class="text-danger fw-bold fs-4"><?php echo format_price($finalPrice); ?></span><?php if ($price > $finalPrice): ?> <span class="text-muted text-decoration-line-through small"><?php echo format_price($price); ?></span><?php endif; ?></div>
                        <p class="small <?php echo $inStock ? 'text-success' : 'text-danger'; ?> fw-bold"><i class="fas <?php echo $inStock ? 'fa-check-circle' : 'fa-times-circle'; ?> me-1" aria-hidden="true"></i><?php echo $inStock ? 'In Stock' : 'Out of Stock'; ?></p>
                        <div class="d-grid gap-2">
                            <a href="product.php?id=<?php echo $productId; ?>" class="btn btn-outline-dark btn-sm rounded-pill fw-bold">View Details</a>
                            <?php if ($hasVariants): ?><a href="product.php?id=<?php echo $productId; ?>" class="app-icon-button btn btn-danger btn-sm rounded-circle mx-auto" aria-label="Choose options for <?php echo htmlspecialchars($productName, ENT_QUOTES); ?>" title="Choose options"><i class="fas fa-sliders-h" aria-hidden="true"></i></a><?php else: ?><button type="button" class="btn btn-danger btn-sm rounded-pill fw-bold add-to-cart-btn" data-product-id="<?php echo $productId; ?>" <?php echo !$inStock ? 'disabled' : ''; ?>><i class="fas fa-shopping-cart me-2" aria-hidden="true"></i>Add to Cart</button><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</article>

<?php else: ?>
<article class="card product-card product-card--<?php echo $productCardVariant; ?> h-100 border-0 shadow-sm overflow-hidden position-relative">
    <a href="product.php?id=<?php echo $productId; ?>" class="stretched-link z-1" aria-label="View <?php echo htmlspecialchars($productName); ?>"></a>
    <div class="position-relative bg-light">
        <img src="<?php echo htmlspecialchars($imageUrl); ?>" class="card-img-top product-card__image" alt="<?php echo htmlspecialchars($productName); ?>" loading="lazy">
        <div class="position-absolute top-0 start-0 p-2 d-flex flex-column gap-1 z-2">
            <?php if ($isFlashSale): ?><span class="badge bg-warning text-dark"><i class="fas fa-bolt me-1" aria-hidden="true"></i><?php echo $productCardVariant === 'mini' ? '' : 'Flash Sale'; ?></span>
            <?php elseif ($isNewArrival): ?><span class="badge badge-info-safe">New<?php echo $productCardVariant === 'mini' ? '' : ' Arrival'; ?></span>
            <?php elseif ($isBestSeller): ?><span class="badge badge-info-safe">Best<?php echo $productCardVariant === 'mini' ? '' : ' Seller'; ?></span><?php endif; ?>
            <?php if ($discount > 0): ?><span class="badge bg-danger">-<?php echo $discount; ?>%</span><?php endif; ?>
        </div>
        <?php if ($productCardVariant !== 'mini'): ?>
            <button type="button" class="app-icon-button btn btn-white btn-sm rounded-circle shadow-sm position-absolute top-0 end-0 m-2 z-2 add-to-wishlist-btn" data-product-id="<?php echo $productId; ?>" aria-label="<?php echo $isWishlisted ? 'Remove from' : 'Add to'; ?> wishlist" aria-pressed="<?php echo $isWishlisted ? 'true' : 'false'; ?>"><i class="<?php echo $isWishlisted ? 'fas text-danger' : 'far text-muted'; ?> fa-heart" aria-hidden="true"></i></button>
            <button type="button" class="app-icon-button btn btn-white btn-sm rounded-circle shadow-sm position-absolute end-0 m-2 z-2 compare-product-btn" style="top: 3.25rem;" data-product-id="<?php echo $productId; ?>" aria-label="Compare <?php echo htmlspecialchars($productName); ?>"><i class="fas fa-balance-scale text-muted" aria-hidden="true"></i></button>
        <?php endif; ?>
    </div>
    <div class="card-body p-3 d-flex flex-column">
        <h2 class="card-title h6 mb-2 text-dark small fw-bold <?php echo $productCardVariant === 'mini' ? 'text-truncate' : 'line-clamp-2'; ?>"><?php echo htmlspecialchars($productName); ?></h2>
        <div class="mt-auto d-flex align-items-end justify-content-between gap-2">
            <div><span class="text-danger fw-bold <?php echo $productCardVariant === 'mini' ? 'small' : 'fs-5'; ?> d-block"><?php echo format_price($finalPrice); ?></span><?php if ($price > $finalPrice): ?><span class="text-muted text-decoration-line-through x-small"><?php echo format_price($price); ?></span><?php endif; ?></div>
            <?php if ($productCardVariant !== 'mini'): ?><?php if ($hasVariants): ?><a href="product.php?id=<?php echo $productId; ?>" class="app-icon-button btn btn-danger btn-sm rounded-circle shadow-sm z-2" aria-label="Choose options for <?php echo htmlspecialchars($productName, ENT_QUOTES); ?>" title="Choose options"><i class="fas fa-sliders-h fa-xs" aria-hidden="true"></i></a><?php else: ?><button type="button" class="app-icon-button btn btn-danger btn-sm rounded-circle shadow-sm z-2 add-to-cart-btn" data-product-id="<?php echo $productId; ?>" <?php echo !$inStock ? 'disabled' : ''; ?> aria-label="Add <?php echo htmlspecialchars($productName); ?> to cart"><i class="fas fa-shopping-cart fa-xs" aria-hidden="true"></i></button><?php endif; ?><?php endif; ?>
        </div>
        <?php if ($productCardVariant !== 'mini'): ?><div class="mt-2 x-small <?php echo $inStock ? 'text-success' : 'text-danger'; ?> fw-bold"><i class="fas <?php echo $inStock ? 'fa-check-circle' : 'fa-times-circle'; ?> me-1" aria-hidden="true"></i><?php echo $inStock ? 'In Stock' : 'Out of Stock'; ?></div><?php endif; ?>
    </div>
</article>
<?php endif; ?>
