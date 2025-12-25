<?php
// compare.php - Product comparison page

require_once 'includes/header.php';
require_once 'models/Product.php';

$productModel = new Product();

// Get product IDs to compare from session or URL
$productIds = [];
if (isset($_GET['ids'])) {
    $productIds = array_slice(explode(',', $_GET['ids']), 0, 4); // Max 4 products
} elseif (isset($_SESSION['compare_list'])) {
    $productIds = $_SESSION['compare_list'];
}

// Limit to 4 products for comparison
$productIds = array_slice($productIds, 0, 4);

$products = [];
if (!empty($productIds)) {
    foreach ($productIds as $id) {
        $product = $productModel->getById($id);
        if ($product) {
            $product['images'] = $productModel->getProductImages($id);
            $products[] = $product;
        }
    }
}
?>

<div class="container-xxl pb-4">
    <?php 
    $breadcrumb_items = [
        ['label' => t('home'), 'url' => 'index.php'],
        ['label' => t('product_compare')]
    ];
    include 'includes/breadcrumb.php';
    ?>
    
    <div class="mb-5 mt-2">
        <h1 class="fw-bold text-dark mb-2"><?php echo t('product_comparison'); ?></h1>
        <p class="text-muted fs-5"><?php echo t('compare_up_to_4_products'); ?></p>
    </div>
    
    <?php if (count($products) > 1): ?>
        <!-- Comparison table -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="table-responsive">
                <table class="table table-bordered mb-0 align-middle">
                    <thead>
                        <tr class="bg-light">
                            <th class="py-3 px-4 border-end" style="min-width: 200px; width: 200px;"><?php echo t('product'); ?></th>
                            <?php foreach ($products as $product): ?>
                                <th class="py-3 px-4 text-center">
                                    <div class="position-relative">
                                        <button class="btn btn-outline-danger btn-sm rounded-circle position-absolute top-0 end-0 m-n2 shadow-sm z-3" onclick="removeFromCompare(<?php echo $product['id']; ?>)" title="<?php echo t('remove'); ?>">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <a href="product.php?id=<?php echo $product['id']; ?>" class="d-block mb-3">
                                            <?php 
                                            $imageUrl = !empty($product['images']) ? $product['images'][0]['image_url'] : 'img/product-placeholder.jpg';
                                            ?>
                                            <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($product['name_en']); ?>" class="object-fit-contain rounded" style="height: 120px; width: 100%;">
                                        </a>
                                        <h3 class="h6 mb-2">
                                            <a href="product.php?id=<?php echo $product['id']; ?>" class="link-dark text-decoration-none fw-bold" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                <?php echo htmlspecialchars($product['name_en']); ?>
                                            </a>
                                        </h3>
                                        <div class="current-price fw-bold text-danger fs-5">
                                            <?php if ($product['discount_percentage'] > 0): ?>
                                                <span class="d-block"><?php echo format_price($product['final_price']); ?></span>
                                                <div class="d-flex justify-content-center align-items-center gap-2">
                                                    <span class="text-muted text-decoration-line-through small fw-normal"><?php echo format_price($product['price']); ?></span>
                                                    <span class="badge bg-danger-subtle text-danger small">-<?php echo $product['discount_percentage']; ?>%</span>
                                                </div>
                                            <?php else: ?>
                                                <?php echo format_price($product['price']); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold bg-light ps-4"><?php echo t('availability'); ?></td>
                            <?php foreach ($products as $product): ?>
                                <td class="text-center">
                                    <?php if ($product['stock_quantity'] > 0): ?>
                                        <span class="badge bg-success-subtle text-success rounded-pill px-3">
                                            <i class="fas fa-check-circle me-1"></i> <?php echo t('in_stock'); ?> (<?php echo $product['stock_quantity']; ?>)
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-3">
                                            <i class="fas fa-times-circle me-1"></i> <?php echo t('out_of_stock'); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        
                        <tr>
                            <td class="fw-bold bg-light ps-4"><?php echo t('rating'); ?></td>
                            <?php foreach ($products as $product): ?>
                                <td class="text-center">
                                    <div class="text-warning small mb-1">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= floor($product['rating'])): ?>
                                                <i class="fas fa-star"></i>
                                            <?php elseif ($i == ceil($product['rating']) && $i != floor($product['rating'])): ?>
                                                <i class="fas fa-star-half-alt"></i>
                                            <?php else: ?>
                                                <i class="far fa-star"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="small text-muted">(<?php echo $product['rating_count']; ?>)</span>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        
                        <tr>
                            <td class="fw-bold bg-light ps-4"><?php echo t('brand'); ?></td>
                            <?php foreach ($products as $product): ?>
                                <td class="text-center text-dark"><?php echo htmlspecialchars($product['brand_name'] ?? ''); ?></td>
                            <?php endforeach; ?>
                        </tr>
                        
                        <tr>
                            <td class="fw-bold bg-light ps-4"><?php echo t('category'); ?></td>
                            <?php foreach ($products as $product): ?>
                                <td class="text-center text-muted"><?php echo htmlspecialchars($product['category_name'] ?? ''); ?></td>
                            <?php endforeach; ?>
                        </tr>
                        
                        <?php if (isset($product['weight']) && $product['weight']): ?>
                            <tr>
                                <td class="fw-bold bg-light ps-4"><?php echo t('weight'); ?></td>
                                <?php foreach ($products as $product): ?>
                                    <td class="text-center"><?php echo $product['weight']; ?> kg</td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endif; ?>
                        
                        <?php if (isset($product['dimensions']) && $product['dimensions']): ?>
                            <tr>
                                <td class="fw-bold bg-light ps-4"><?php echo t('dimensions'); ?></td>
                                <?php foreach ($products as $product): ?>
                                    <td class="text-center"><?php echo htmlspecialchars($product['dimensions']); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endif; ?>
                        
                        <?php if (isset($product['color']) && $product['color']): ?>
                            <tr>
                                <td class="fw-bold bg-light ps-4"><?php echo t('color'); ?></td>
                                <?php foreach ($products as $product): ?>
                                    <td class="text-center"><?php echo htmlspecialchars($product['color']); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endif; ?>
                        
                        <?php if (isset($product['size']) && $product['size']): ?>
                            <tr>
                                <td class="fw-bold bg-light ps-4"><?php echo t('size'); ?></td>
                                <?php foreach ($products as $product): ?>
                                    <td class="text-center"><?php echo htmlspecialchars($product['size']); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endif; ?>
                        
                        <!-- Technical specifications -->
                        <?php
                        $allSpecs = [];
                        foreach ($products as $product) {
                            $specs = json_decode($product['technical_specs_en'], true) ?: [];
                            $allSpecs = array_merge($allSpecs, array_keys($specs));
                        }
                        $allSpecs = array_unique($allSpecs);
                        
                        foreach ($allSpecs as $specName) {
                            echo "<tr><td class=\"fw-bold bg-light ps-4 text-capitalize\">" . htmlspecialchars(str_replace('_', ' ', $specName)) . "</td>";
                            foreach ($products as $product) {
                                $specs = json_decode($product['technical_specs_en'], true) ?: [];
                                $specValue = $specs[$specName] ?? '-';
                                echo "<td class=\"text-center font-monospace small\">" . htmlspecialchars($specValue) . "</td>";
                            }
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mb-5">
            <a href="products.php" class="btn btn-outline-dark rounded-pill px-4 fw-bold shadow-sm">
                <i class="fas fa-arrow-left me-2"></i> <?php echo t('continue_shopping'); ?>
            </a>
            <button class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm" onclick="clearComparison()">
                <i class="fas fa-broom me-2"></i> <?php echo t('clear_comparison'); ?>
            </button>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm rounded-4 py-5 mb-5">
            <div class="card-body text-center py-5">
                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 100px; height: 100px;">
                    <i class="fas fa-balance-scale text-muted opacity-50" style="font-size: 40px;"></i>
                </div>
                <h2 class="fw-bold text-dark mb-3"><?php echo t('no_products_to_compare'); ?></h2>
                <p class="text-muted fs-5 mb-5 mx-auto" style="max-width: 500px;"><?php echo t('add_products_to_compare') ?? 'You haven\'t added any products to compare yet. Start browsing and pick up to 4 items!'; ?></p>
                <a href="products.php" class="btn btn-danger btn-lg rounded-pill px-5 fw-bold shadow-sm">
                    <i class="fas fa-search me-2"></i> <?php echo t('browse_products'); ?>
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'includes/footer.php';
?>

<script>
function removeFromCompare(productId) {
    fetch('api/compare.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'remove',
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Refresh to update comparison
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('<?php echo addslashes(t('error_removing_from_comparison')); ?>', 'error');
    });
}

function clearComparison() {
    if (confirm('<?php echo addslashes(t('confirm_clear_comparison')); ?>')) {
        fetch('api/compare.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'clear'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.href = 'products.php';
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('<?php echo addslashes(t('error_clearing_comparison')); ?>', 'error');
        });
    }
}

// Add to comparison function for product pages
function addToComparison(productId) {
    fetch('api/compare.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'add',
            product_id: productId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('<?php echo addslashes(t('error_adding_to_comparison')); ?>', 'error');
    });
}
</script>
