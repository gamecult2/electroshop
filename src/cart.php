<?php
// cart.php - Shopping cart page
require_once 'includes/init.php';
require_once 'models/Cart.php';
require_once 'models/Product.php';
require_once 'includes/header.php';

$cart = new Cart();
$productModel = new Product();

$cartItems = $cart->getItems();
$isEmpty = $cart->isEmpty();
$subtotal = $cart->getSubtotal();
$shippingCost = 500; // 500 DZD
$total = $subtotal + $shippingCost;
?>

<div class="container-xxl pb-4">
    <?php 
    $breadcrumb_items = [
        ['label' => t('home'), 'url' => 'index.php'],
        ['label' => t('shopping_cart')]
    ];
    include 'includes/breadcrumb.php';
    ?>

    <!-- Checkout Progress -->
    <div class="row justify-content-center mb-4 mt-4">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="position-relative">
                <!-- Background Line -->
                <div class="position-absolute top-0 start-0 w-100" style="margin-top: 20px;">
                    <div class="bg-light-subtle" style="height: 4px; border-radius: 4px; margin: 0 40px;"></div>
                </div>
                
                <!-- Animated Progress Line -->
                <div class="position-absolute top-0 start-0 w-100" style="margin-top: 20px; z-index: 1;">
                    <div class="progress" style="height: 4px; background-color: transparent; margin: 0 40px; overflow: visible;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" style="width: 0%; border-radius: 4px;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-secondary opacity-25" role="progressbar" style="width: 100%; border-radius: 4px;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                
                <!-- Step Icons -->
                <div class="d-flex justify-content-between position-relative" style="z-index: 2;">
                    <!-- Step 1: Cart -->
                    <div class="text-center" style="width: 80px;">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-sm" style="width: 40px; height: 40px; border: 4px solid #fff;">
                            <i class="fas fa-shopping-cart small"></i>
                        </div>
                        <div class="mt-2 small fw-bold text-uppercase text-danger" style="font-size: 11px; letter-spacing: 0.5px;"><?php echo t('cart'); ?></div>
                    </div>
                    
                    <!-- Step 2: Checkout -->
                    <div class="text-center" style="width: 80px;">
                        <div class="bg-light text-muted rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-sm border" style="width: 40px; height: 40px; border: 4px solid #fff !important;">
                            <i class="fas fa-credit-card small"></i>
                        </div>
                        <div class="mt-2 small fw-bold text-uppercase text-muted opacity-75" style="font-size: 11px; letter-spacing: 0.5px;"><?php echo t('checkout'); ?></div>
                    </div>
                    
                    <!-- Step 3: Complete -->
                    <div class="text-center" style="width: 80px;">
                        <div class="bg-light text-muted rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-sm border" style="width: 40px; height: 40px; border: 4px solid #fff !important;">
                            <i class="fas fa-check small"></i>
                        </div>
                        <div class="mt-2 small fw-bold text-uppercase text-muted opacity-75" style="font-size: 11px; letter-spacing: 0.5px;"><?php echo t('complete'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($isEmpty): ?>
        <div class="card border-0 shadow-sm rounded-4 py-5 mb-5">
            <div class="card-body text-center py-5">
                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 100px; height: 100px;">
                    <i class="fas fa-shopping-cart text-muted opacity-50" style="font-size: 40px;"></i>
                </div>
                <h2 class="fw-bold text-dark mb-3"><?php echo t('empty_cart'); ?></h2>
                <p class="text-muted fs-5 mb-5 mx-auto" style="max-width: 500px;"><?php echo t('empty_cart_description') ?? 'Your shopping cart is empty. Start adding some products to it!'; ?></p>
                <a href="products.php" class="btn btn-danger btn-lg rounded-pill px-5 fw-bold shadow-sm">
                    <i class="fas fa-search me-2"></i> <?php echo t('start_shopping'); ?>
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <h2 class="h5 fw-bold mb-0 text-dark"><i class="fas fa-shopping-cart text-danger me-2"></i> <?php echo t('shopping_cart'); ?></h2>
                        <span class="badge bg-light text-dark border rounded-pill px-3"><?php echo count($cartItems); ?> Items</span>
                    </div>
                    <div class="card-body p-0">
                        <!-- Bulk Actions Bar -->
                        <div id="bulk-actions-bar" class="bg-light px-4 py-2 border-bottom d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check mb-0">
                                    <input class="form-check-input " type="checkbox" id="select-all-cart" onclick="toggleAllCartItems(this)">
                                    <label class="form-check-label small fw-bold text-dark  d-none d-md-inline" for="select-all-cart">
                                        <?php echo t('select_all'); ?>
                                    </label>
                                </div>
                                <span id="selected-count" class="small text-muted fw-bold">0 Selected</span>
                            </div>
                            <button class="btn btn-link text-danger btn-sm p-0 fw-bold text-decoration-none d-none" id="bulk-remove-btn" onclick="bulkRemoveFromCart()">
                                <i class="fas fa-trash-alt me-1"></i> <?php echo t('remove_selected'); ?>
                            </button>
                        </div>

                        <!-- Column Headers -->
                        <div class="bg-white px-4 py-2 border-bottom d-none d-md-block">
                            <div class="row align-items-center g-3">
                                <div class="col-auto" style="width: 110px;"></div> <!-- Checkbox + Image Spacer -->
                                <div class="col">
                                    <span class="small fw-bold text-muted text-uppercase ls-1">Product</span>
                                </div>
                                <div class="col-md-auto text-center" style="width: 100px;">
                                    <span class="small fw-bold text-muted text-uppercase ls-1">Price</span>
                                </div>
                                <div class="col-md-auto text-center" style="width: 100px;">
                                    <span class="small fw-bold text-muted text-uppercase ls-1">Qty</span>
                                </div>
                                <div class="col-md-auto text-end" style="width: 100px;">
                                    <span class="small fw-bold text-muted text-uppercase ls-1">Total</span>
                                </div>
                                <div class="col-auto" style="width: 40px;"></div> <!-- Trash Spacer -->
                            </div>
                        </div>

                        <div class="cart-items">
                            <?php foreach ($cartItems as $index => $item): 
                                $product = $productModel->getById($item['product_id']);
                                $productImages = $productModel->getProductImages($item['product_id']);
                                $imageUrl = !empty($productImages) ? $productImages[0]['image_url'] : 'img/product-placeholder.jpg';
                            ?>
                                <div class="cart-item p-4 border-bottom position-relative <?php echo $index % 2 === 0 ? 'bg-white' : 'bg-light-subtle'; ?>" data-item-id="<?php echo $item['id']; ?>">
                                    <div class="row align-items-center g-3">
                                        <!-- Checkbox & Image -->
                                        <div class="col-auto" style="width: 110px;">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input cart-item-check " type="checkbox" value="<?php echo $item['id']; ?>" onchange="updateBulkBar()">
                                                </div>
                                                <a href="product.php?id=<?php echo $item['product_id']; ?>" class="d-block bg-light rounded-3 p-1 border border-light-subtle" style="width: 50px; height: 50px;">
                                                    <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="w-100 h-100 object-fit-contain">
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Details -->
                                        <div class="col">
                                            <h3 class="h6 mb-1">
                                                <a href="product.php?id=<?php echo $item['product_id']; ?>" class="text-dark text-decoration-none  fw-bold">
                                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                                </a>
                                            </h3>
                                            
                                            <!-- Variant Badges -->
                                            <div class="d-flex flex-wrap gap-2 mb-2">
                                                <?php if (!empty($item['variant_id'])): 
                                                    $variantIds = explode(',', $item['variant_id']);
                                                    foreach ($variantIds as $vId):
                                                        $vId = (int)trim($vId);
                                                        if ($vId <= 0) continue;
                                                        $vData = $productModel->getVariantById($vId);
                                                        if ($vData && !empty($vData['attributes'])):
                                                            foreach ($vData['attributes'] as $attrName => $attrValue):
                                                ?>
                                                    <span class="badge bg-light text-dark border rounded-pill small py-1 px-2">
                                                        <span class="text-muted fw-normal me-1"><?php echo htmlspecialchars($attrName); ?>:</span> 
                                                        <?php echo htmlspecialchars($attrValue); ?>
                                                    </span>
                                                <?php endforeach; endif; endforeach; endif; ?>
                                                
                                                <?php if (empty($item['variant_id'])): ?>
                                                    <?php if (!empty($product['color'])): ?>
                                                        <span class="badge bg-light text-dark border rounded-pill small py-1 px-2">
                                                            <span class="text-muted fw-normal me-1">Color:</span> <?php echo htmlspecialchars($product['color']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($product['size'])): ?>
                                                        <span class="badge bg-light text-dark border rounded-pill small py-1 px-2">
                                                            <span class="text-muted fw-normal me-1">Size:</span> <?php echo htmlspecialchars($product['size']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Unit Price -->
                                        <div class="col-md-auto text-md-center" style="width: 100px;">
                                            <div class="text-muted x-small text-uppercase fw-bold mb-1 d-md-none">Unit Price</div>
                                            <div class="text-danger fw-bold fs-6"><?php echo format_price($item['price_at_time']); ?></div>
                                        </div>

                                        <!-- Quantity -->
                                        <div class="col-md-auto text-md-center" style="width: 100px;">
                                            <?php 
                                                $availableStock = $item['variant_id'] ? $item['variant_stock'] : $item['product_stock'];
                                            ?>
                                            <div class="input-group input-group-sm rounded-pill overflow-hidden border border-light-subtle mx-auto">
                                                <button class="btn btn-light border-0 px-2" type="button" onclick="updateQuantity(<?php echo $item['id']; ?>, -1)"><i class="fas fa-minus small"></i></button>
                                                <input type="text" class="form-control border-0 text-center bg-white px-0 fw-bold" value="<?php echo $item['quantity']; ?>" readonly data-item-id="<?php echo $item['id']; ?>" data-max-stock="<?php echo $availableStock; ?>">
                                                <button class="btn btn-light border-0 px-2" type="button" onclick="updateQuantity(<?php echo $item['id']; ?>, 1)"><i class="fas fa-plus small"></i></button>
                                            </div>
                                            <div class="x-small text-muted mt-1"><?php echo $availableStock; ?> in stock</div>
                                        </div>

                                        <!-- Total -->
                                        <div class="col-md-auto text-md-end text-start" style="width: 100px;">
                                            <div class="text-muted x-small text-uppercase fw-bold mb-1 d-md-none">Total</div>
                                            <div class="text-dark fw-bold"><?php echo format_price($item['quantity'] * $item['price_at_time']); ?></div>
                                        </div>

                                        <!-- Remove -->
                                        <div class="col-auto ms-auto ms-md-0 d-flex justify-content-center" style="width: 40px;">
                                            <button class="btn btn-outline-light border-0 text-muted btn-sm rounded-circle " onclick="removeFromCart(<?php echo $item['id']; ?>)" title="<?php echo t('remove_item'); ?>">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-white py-3 border-top-0 px-4">
                        <a href="products.php" class="btn btn-link text-muted text-decoration-none btn-sm fw-bold  p-0">
                            <i class="fas fa-arrow-left me-2"></i> <?php echo t('continue_shopping'); ?>
                        </a>
                    </div>
                </div>
            </div>

            <aside class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 sticky-top overflow-hidden" style="top: 100px;">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h2 class="h6 fw-bold mb-0 text-dark"><i class="fas fa-receipt text-danger me-2"></i> <?php echo t('order_summary'); ?></h2>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between mb-3 text-muted">
                            <span class="small fw-bold text-uppercase "><?php echo t('subtotal'); ?></span>
                            <span class="small fw-bold text-dark"><?php echo format_price($subtotal); ?></span>
                        </div>

                        <div class="d-flex justify-content-between mb-4 text-muted border-bottom pb-4">
                            <span class="small fw-bold text-uppercase "><?php echo t('shipping'); ?></span>
                            <div class="text-end">
                                <span class="small fw-bold text-dark d-block"><?php echo format_price($shippingCost); ?></span>
                                <span class="small text-muted opacity-75">Flat rate shipping</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mb-5">
                            <span class="h5 fw-bold text-dark mb-0"><?php echo t('total'); ?></span>
                            <span class="h4 fw-bold text-danger mb-0"><?php echo format_price($total); ?></span>
                        </div>

                        <a href="checkout.php" class="btn btn-danger btn-lg w-100 rounded-pill fw-bold shadow-sm py-3 mb-4 d-flex align-items-center justify-content-center gap-2">
                            <i class="fas fa-lock"></i> <?php echo t('proceed_to_payment'); ?>
                        </a>

                        <div class="row g-2 text-center text-muted opacity-75">
                            <div class="col-4">
                                <i class="fas fa-shield-alt d-block mb-1"></i>
                                <span class="small fw-bold text-uppercase " style="font-size: 8px;">Secure</span>
                            </div>
                            <div class="col-4 border-start border-end">
                                <i class="fas fa-undo d-block mb-1"></i>
                                <span class="small fw-bold text-uppercase " style="font-size: 8px;">Returns</span>
                            </div>
                            <div class="col-4">
                                <i class="fas fa-lock d-block mb-1"></i>
                                <span class="small fw-bold text-uppercase " style="font-size: 8px;">SSL</span>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
function updateQuantity(itemId, change) {
    const input = document.querySelector(`input[data-item-id="${itemId}"]`);
    const maxStock = parseInt(input.getAttribute('data-max-stock'));
    let quantity = parseInt(input.value) + change;
    
    if (quantity < 1) quantity = 1;
    if (quantity > maxStock) {
        showNotification('Cannot exceed available stock (' + maxStock + ')', 'error');
        return;
    }
    
    input.value = quantity;

    fetch('api/cart/update.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ item_id: itemId, quantity: quantity })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            location.reload();
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating cart', 'error');
    });
}

function removeFromCart(itemId) {
    if (confirm('Are you sure you want to remove this item from your cart?')) {
        fetch('api/cart/remove.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ item_id: itemId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                location.reload();
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error removing item', 'error');
        });
    }
}

// Bulk Selection Functions
function toggleAllCartItems(mainCheckbox) {
    const itemCheckboxes = document.querySelectorAll('.cart-item-check');
    itemCheckboxes.forEach(checkbox => {
        checkbox.checked = mainCheckbox.checked;
    });
    updateBulkBar();
}

function updateBulkBar() {
    const itemCheckboxes = document.querySelectorAll('.cart-item-check');
    const checkedCheckboxes = document.querySelectorAll('.cart-item-check:checked');
    const selectedCountSpan = document.getElementById('selected-count');
    const selectAllCheckbox = document.getElementById('select-all-cart');
    const bulkRemoveBtn = document.getElementById('bulk-remove-btn');

    // Update select all checkbox state
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = checkedCheckboxes.length === itemCheckboxes.length && itemCheckboxes.length > 0;
    }

    if (checkedCheckboxes.length > 0) {
        selectedCountSpan.textContent = `${checkedCheckboxes.length} Selected`;
        selectedCountSpan.classList.remove('text-muted');
        selectedCountSpan.classList.add('text-danger');
        bulkRemoveBtn.classList.remove('d-none');
    } else {
        selectedCountSpan.textContent = '0 Selected';
        selectedCountSpan.classList.remove('text-danger');
        selectedCountSpan.classList.add('text-muted');
        bulkRemoveBtn.classList.add('d-none');
    }
}

async function bulkRemoveFromCart() {
    const selectedIds = Array.from(document.querySelectorAll('.cart-item-check:checked')).map(cb => cb.value);
    
    if (selectedIds.length === 0) return;

    if (confirm(`Are you sure you want to remove ${selectedIds.length} item${selectedIds.length > 1 ? 's' : ''} from your cart?`)) {
        let successCount = 0;

        for (const itemId of selectedIds) {
            try {
                const response = await fetch('api/cart/remove.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ item_id: itemId })
                });
                const data = await response.json();
                if (data.success) successCount++;
            } catch (err) {
                console.error(`Error removing item ${itemId}:`, err);
            }
        }

        if (successCount > 0) {
            showNotification(`${successCount} items removed from cart`, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification('Failed to remove selected items', 'error');
        }
    }
}
</script>
