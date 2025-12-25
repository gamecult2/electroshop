<?php
// wishlist.php - User wishlist page
require_once 'includes/init.php';
require_once 'models/Wishlist.php';
require_once 'models/Product.php';

require_login();

require_once 'includes/header.php';

$userId = get_current_user_id();
$wishlistModel = new Wishlist();
$productModel = new Product();

// Handle actions
if (isset($_POST['remove_item'])) {
    $productId = (int)$_POST['product_id'];
    $wishlistModel->remove($userId, $productId);
    set_message(t('item_removed_from_wishlist'), 'success');
}

// Get wishlist items
$wishlistItems = $wishlistModel->getByUser($userId);
$userWishlistIds = array_column($wishlistItems, 'product_id');
?>

<div class="container-xxl pb-4">
    <?php 
    $breadcrumb_items = [
        ['label' => t('home'), 'url' => 'index.php'],
        ['label' => t('my_account'), 'url' => 'account.php'],
        ['label' => t('wishlist')]
    ];
    include 'includes/breadcrumb.php';
    ?>
    
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h2 class="h5 fw-bold mb-0 text-dark"><i class="fas fa-heart text-danger me-2"></i> <?php echo t('my_wishlist'); ?></h2>
            <?php if (count($wishlistItems) > 0): ?>
                <form method="POST" class="m-0">
                    <input type="hidden" name="move_all_to_cart" value="1">
                    <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3 shadow-none">
                        <i class="fas fa-shopping-cart me-1"></i> <?php echo t('move_all_to_cart'); ?>
                    </button>
                </form>
            <?php endif; ?>
        </div>
        <div class="card-body p-4">
            <?php get_message(); ?>
            
            <?php if (count($wishlistItems) > 0): ?>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-3">
                    <?php foreach ($wishlistItems as $item):
                        $product = $productModel->getById($item['product_id']);
                        if (!$product) continue;
                        echo '<div class="col">';
                        include 'includes/product-card-jd.php';
                        echo '</div>';
                    endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 100px; height: 100px;">
                        <i class="fas fa-heart text-muted opacity-50" style="font-size: 40px;"></i>
                    </div>
                    <h2 class="fw-bold mb-3"><?php echo t('wishlist_empty'); ?></h2>
                    <p class="text-muted mb-4"><?php echo t('wishlist_empty_description'); ?></p>
                    <a href="products.php" class="btn btn-danger rounded-pill px-5"><?php echo t('start_shopping'); ?></a>
                </div>
            <?php endif; ?>
        </div>
        <?php if (count($wishlistItems) > 0): ?>
            <div class="card-footer bg-white py-3 border-top d-flex justify-content-end">
                <a href="products.php" class="btn btn-outline-secondary rounded-pill px-4"><?php echo t('continue_shopping'); ?></a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
document.addEventListener('wishlistUpdated', function(e) {
    if (e.detail.action === 'removed') {
        const productId = e.detail.productId;
        const card = document.querySelector(`.product-card-jd .add-to-wishlist-btn[data-product-id="${productId}"]`)?.closest('.product-card-jd') 
                    || document.querySelector(`.product-card-compact .add-to-wishlist-btn[data-product-id="${productId}"]`)?.closest('.product-card-compact');
        
        if (card) {
            card.style.opacity = '0';
            card.style.transform = 'scale(0.8)';
            card.style.transition = 'all 0.3s ease';
            setTimeout(() => {
                card.remove();
                if (document.querySelectorAll('.product-card-jd, .product-card-compact').length === 0) {
                    location.reload();
                }
            }, 300);
        }
    }
});
</script>
