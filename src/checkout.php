<?php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/models/Cart.php';
require_once __DIR__ . '/models/Customer.php';
require_once __DIR__ . '/models/Order.php';
require_once __DIR__ . '/includes/header.php';

$cart = new Cart();
$customerModel = new Customer();
$orderModel = new Order();

if ($cart->isEmpty()) {
    redirect('cart.php');
}

$cartItems = $cart->getItems();
$subtotal = $cart->getSubtotal();
$shippingCost = 500; // 500 DZD
$total = $subtotal + $shippingCost;

$isLoggedIn = is_logged_in();
$userId = $isLoggedIn ? get_current_user_id() : null;
$addresses = $userId ? $customerModel->getCustomerAddresses($userId) : [];
$user = $userId ? $customerModel->getById($userId) : null;

$selectedAddressId = 'new';
if ($isLoggedIn && !empty($addresses)) {
    foreach ($addresses as $address) {
        if ($address['is_default']) {
            $selectedAddressId = $address['id'];
            break;
        }
    }
    if ($selectedAddressId === 'new') {
        $selectedAddressId = $addresses[0]['id'];
    }
}
?>

<div class="container-xxl pb-4">
    <?php 
    $breadcrumb_items = [
        ['label' => t('home'), 'url' => 'index.php'],
        ['label' => t('shopping_cart'), 'url' => 'cart.php'],
        ['label' => t('checkout')]
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
                        <!-- Segment 1 (Cart to Checkout): Green (Completed & Animated) -->
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 50%; border-radius: 4px 0 0 4px;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                        <!-- Segment 2 (Checkout to Complete): Gray (Pending & Animated) -->
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-secondary opacity-25" role="progressbar" style="width: 50%; border-radius: 0 4px 4px 0;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                
                <!-- Step Icons -->
                <div class="d-flex justify-content-between position-relative" style="z-index: 2;">
                    <!-- Step 1: Cart -->
                    <div class="text-center" style="width: 80px;">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-sm position-relative" style="width: 40px; height: 40px; border: 4px solid #fff;">
                            <i class="fas fa-check small"></i>
                        </div>
                        <div class="mt-2 small fw-bold text-uppercase text-success" style="font-size: 11px; letter-spacing: 0.5px;"><?php echo t('cart'); ?></div>
                    </div>
                    
                    <!-- Step 2: Checkout -->
                    <div class="text-center" style="width: 80px;">
                        <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-sm" style="width: 40px; height: 40px; border: 4px solid #fff;">
                            <i class="fas fa-credit-card small"></i>
                        </div>
                        <div class="mt-2 small fw-bold text-uppercase text-danger" style="font-size: 11px; letter-spacing: 0.5px;"><?php echo t('checkout'); ?></div>
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
    
    <?php if (!$isLoggedIn): ?>
        <div class="alert alert-info border-0 shadow-sm rounded-3 py-3 mb-4 d-flex align-items-center">
            <i class="fas fa-info-circle me-3 fs-4 text-info"></i> 
            <div>
                <?php echo t('already_have_account'); ?> 
                <a href="login.php?redirect=checkout.php" class="fw-bold text-info text-decoration-none"><?php echo t('sign_in'); ?></a> 
                <?php echo t('for_faster_checkout'); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Guest Information -->
            <?php if (!$isLoggedIn): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark"><i class="fas fa-user text-danger me-2"></i> <?php echo t('contact_information'); ?></h2>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase"><?php echo t('first_name'); ?> *</label>
                            <input type="text" id="guest_first_name" class="form-control rounded-pill px-3" placeholder="John" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase"><?php echo t('last_name'); ?> *</label>
                            <input type="text" id="guest_last_name" class="form-control rounded-pill px-3" placeholder="Doe" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase"><?php echo t('email'); ?> *</label>
                            <input type="email" id="guest_email" class="form-control rounded-pill px-3" placeholder="john.doe@example.com" required>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Delivery Address -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark"><i class="fas fa-map-marker-alt text-danger me-2"></i> <?php echo t('delivery_address'); ?></h2>
                </div>
                <div class="card-body p-4">
                    <div class="row row-cols-1 row-cols-md-2 g-3" id="address-grid">
                        <?php if ($isLoggedIn && !empty($addresses)): ?>
                            <?php foreach ($addresses as $address): ?>
                                <div class="col">
                                    <div class="card h-100 border p-4 address-card cursor-pointer position-relative <?php echo ($selectedAddressId == $address['id']) ? 'border-danger shadow-sm' : 'border-light-subtle'; ?>" 
                                         onclick="selectAddress(this, <?php echo $address['id']; ?>)"
                                         style="transition: all 0.2s ease;"
                                         data-address-id="<?php echo $address['id']; ?>">
                                        
                                        <div class="d-flex gap-3">
                                            <div class="mt-1">
                                                <i class="far fa-circle text-muted fs-5 uncheck-icon <?php echo ($selectedAddressId == $address['id']) ? 'd-none' : ''; ?>"></i>
                                                <i class="fas fa-check-circle text-danger fs-5 check-icon <?php echo ($selectedAddressId == $address['id']) ? '' : 'd-none'; ?>"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <span class="fw-bold text-dark small text-uppercase">
                                                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                                    </span>
                                                    <?php if ($address['is_default']): ?>
                                                        <span class="badge bg-secondary-subtle text-secondary border" style="font-size: 0.6rem;">DEFAULT</span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="text-muted small mb-3" style="line-height: 1.4; font-size: 0.85rem;">
                                                    <div><?php echo htmlspecialchars($address['street_address']); ?></div>
                                                    <div><?php echo htmlspecialchars($address['commune'] . ', ' . $address['wilaya'] . ' ' . $address['postal_code']); ?></div>
                                                </div>
                                                
                                                <div class="d-flex align-items-center justify-content-between border-top pt-2">
                                                    <div class="d-flex align-items-center text-dark small fw-bold">
                                                        <i class="fas fa-phone-alt me-2 text-muted" style="font-size: 0.7rem;"></i>
                                                        <?php echo htmlspecialchars($address['phone_number']); ?>
                                                    </div>
                                                    <button class="btn btn-link btn-sm text-decoration-none p-0 text-muted fw-bold" 
                                                            style="font-size: 0.75rem;" 
                                                            onclick="event.stopPropagation(); openAddressModal(<?php echo $address['id']; ?>)">
                                                        <?php echo t('edit'); ?>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <div class="col">
                            <div class="card h-100 border border-2 border-light-subtle p-4 d-flex flex-column align-items-center justify-content-center text-center transition-all bg-light-subtle" 
                                 id="add-address-card-btn" 
                                 style="border-style: dashed !important; cursor: pointer; min-height: 160px;"
                                 onclick="openAddressModal()">
                                <div class="bg-white rounded-circle d-flex align-items-center justify-content-center mb-2 shadow-sm" style="width: 50px; height: 50px; flex-shrink: 0;">
                                    <i class="fas fa-plus text-muted fs-5"></i>
                                </div>
                                <span class="fw-bold text-muted small text-uppercase"><?php echo t('add_new_address'); ?></span>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="delivery_address_id" id="selected_address_id" value="<?php echo $selectedAddressId; ?>">
                    <div id="temp_address_data" class="d-none"></div>
                </div>
            </div>
            
            <!-- Delivery Options -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark"><i class="fas fa-truck text-danger me-2"></i> <?php echo t('delivery_option'); ?></h2>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex flex-column gap-3">
                        <label class="d-flex align-items-center gap-3 p-3 border rounded-4 h-100 transition-all" for="standard_delivery">
                            <div class="form-check mb-0 ms-2">
                                <input class="form-check-input focus-ring-danger" type="radio" id="standard_delivery" name="delivery_option" value="standard" checked>
                            </div>
                            <div class="flex-grow-1">
                                <span class="d-block fw-bold text-dark"><?php echo t('standard_delivery'); ?></span>
                                <span class="text-muted small">3-7 <?php echo t('days'); ?> • <?php echo format_price(500); ?></span>
                            </div>
                            <div class="me-2">
                                <i class="fas fa-truck text-muted opacity-50"></i>
                            </div>
                        </label>
                        
                        <label class="d-flex align-items-center gap-3 p-3 border rounded-4 h-100 transition-all" for="express_delivery">
                            <div class="form-check mb-0 ms-2">
                                <input class="form-check-input focus-ring-danger" type="radio" id="express_delivery" name="delivery_option" value="express">
                            </div>
                            <div class="flex-grow-1">
                                <span class="d-block fw-bold text-dark"><?php echo t('express_delivery'); ?></span>
                                <span class="text-muted small">1-2 <?php echo t('days'); ?> • <?php echo format_price(1000); ?></span>
                            </div>
                            <div class="me-2">
                                <i class="fas fa-bolt text-warning"></i>
                            </div>
                        </label>
                        
                        <label class="d-flex align-items-center gap-3 p-3 border rounded-4 h-100 transition-all" for="home_delivery">
                            <div class="form-check mb-0 ms-2">
                                <input class="form-check-input focus-ring-danger" type="radio" id="home_delivery" name="delivery_option" value="home_delivery">
                            </div>
                            <div class="flex-grow-1">
                                <span class="d-block fw-bold text-dark"><?php echo t('home_delivery'); ?></span>
                                <span class="text-muted small"><?php echo t('estimated_delivery'); ?>: 2-5 <?php echo t('days'); ?> • <?php echo format_price(600); ?></span>
                            </div>
                            <div class="me-2">
                                <i class="fas fa-home text-muted opacity-50"></i>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Payment Method -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark"><i class="fas fa-credit-card text-danger me-2"></i> <?php echo t('payment_method'); ?></h2>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="d-flex align-items-center gap-3 p-3 border rounded-4 h-100 transition-all" for="cod">
                                <div class="form-check mb-0">
                                    <input class="form-check-input focus-ring-danger" type="radio" id="cod" name="payment_method" value="cod" checked>
                                </div>
                                <div>
                                    <i class="fas fa-money-bill-wave text-danger fs-4 d-block mb-1"></i>
                                    <span class="fw-bold text-dark small"><?php echo t('cod'); ?></span>
                                </div>
                            </label>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="d-flex align-items-center gap-3 p-3 border rounded-4 h-100 transition-all" for="bank_transfer">
                                <div class="form-check mb-0">
                                    <input class="form-check-input focus-ring-danger" type="radio" id="bank_transfer" name="payment_method" value="bank_transfer">
                                </div>
                                <div>
                                    <i class="fas fa-university text-danger fs-4 d-block mb-1"></i>
                                    <span class="fw-bold text-dark small"><?php echo t('bank_transfer'); ?></span>
                                </div>
                            </label>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="d-flex align-items-center gap-3 p-3 border rounded-4 h-100 transition-all" for="baridimob">
                                <div class="form-check mb-0">
                                    <input class="form-check-input focus-ring-danger" type="radio" id="baridimob" name="payment_method" value="baridimob">
                                </div>
                                <div>
                                    <i class="fas fa-mobile-alt text-danger fs-4 d-block mb-1"></i>
                                    <span class="fw-bold text-dark small"><?php echo t('baridimob'); ?></span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Notes -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark"><i class="fas fa-sticky-note text-danger me-2"></i> <?php echo t('order_notes'); ?> <span class="text-muted fw-normal fs-7">(<?php echo t('optional'); ?>)</span></h2>
                </div>
                <div class="card-body p-4">
                    <textarea id="order_notes" class="form-control rounded-4 p-3" rows="3" placeholder="<?php echo t('order_notes_placeholder'); ?>"></textarea>
                </div>
            </div>
        </div>
        
        <aside class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 sticky-top overflow-hidden" style="top: 100px;">
                <div class="card-header bg-white py-3 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark"><i class="fas fa-receipt text-danger me-2"></i> <?php echo t('order_summary'); ?></h2>
                </div>
                <div class="card-body p-4">
                    <div class="checkout-items d-flex flex-column gap-3 mb-4 border-bottom pb-4">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-light rounded-3 p-1 border border-light-subtle" style="width: 50px; height: 50px;">
                                    <img src="<?php echo !empty($item['product_image']) ? htmlspecialchars($item['product_image']) : 'img/product-placeholder.jpg'; ?>" alt="" class="w-100 h-100 object-fit-contain">
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <h4 class="small fw-bold text-dark mb-1 text-truncate"><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                    <div class="d-flex justify-content-between">
                                        <span class="small text-muted">Qty: <?php echo $item['quantity']; ?></span>
                                        <span class="small fw-bold text-dark"><?php echo format_price($item['quantity'] * $item['price_at_time']); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="small fw-bold text-muted text-uppercase"><?php echo t('subtotal'); ?></span>
                        <span class="small fw-bold text-dark"><?php echo format_price($subtotal); ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-4 border-bottom pb-4">
                        <span class="small fw-bold text-muted text-uppercase"><?php echo t('shipping'); ?></span>
                        <span class="small fw-bold text-dark" id="shipping-cost"><?php echo format_price($shippingCost); ?></span>
                    </div>

                    <div id="discount-row" class="d-none justify-content-between mb-2">
                        <span class="small fw-bold text-success text-uppercase"><?php echo t('discount'); ?></span>
                        <span class="small fw-bold text-success" id="discount-amount"></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-5">
                        <span class="h5 fw-bold text-dark mb-0"><?php echo t('total'); ?></span>
                        <span class="h4 fw-bold text-danger mb-0" id="total-amount"><?php echo format_price($total); ?></span>
                    </div>

                    <!-- Promo Code -->
                    <div class="mb-4 pt-3 border-top">
                        <label class="form-label small fw-bold text-muted text-uppercase mb-2"><?php echo t('promo_code'); ?></label>
                        <div class="input-group input-group-sm rounded-pill overflow-hidden border border-light-subtle">
                            <input type="text" id="promo_code" class="form-control border-0 px-3" placeholder="<?php echo t('enter_code'); ?>">
                            <button class="btn btn-dark px-3 fw-bold" type="button" onclick="applyPromo()"><?php echo t('apply'); ?></button>
                        </div>
                    </div>

                    <div class="form-check small mb-4">
                        <input class="form-check-input focus-ring-danger" type="checkbox" id="terms_agree" required>
                        <label class="form-check-label text-muted" for="terms_agree">
                            <?php echo t('agree_to_terms'); ?> <a href="terms.php" target="_blank" class="text-danger text-decoration-none fw-bold"><?php echo t('terms_and_conditions'); ?></a>
                        </label>
                    </div>
                    
                    <button class="btn btn-danger btn-lg w-100 rounded-pill fw-bold shadow-sm py-3 mb-4 d-flex align-items-center justify-content-center gap-2" onclick="placeOrder()">
                        <i class="fas fa-lock"></i> <?php echo t('place_order'); ?>
                    </button>

                    <div class="row g-2 text-center text-muted opacity-75">
                        <div class="col-4">
                            <i class="fas fa-shield-alt d-block mb-1"></i>
                            <span class="small fw-bold text-uppercase" style="font-size: 8px;">Secure SSL</span>
                        </div>
                        <div class="col-4 border-start border-end">
                            <i class="fas fa-money-check-alt d-block mb-1"></i>
                            <span class="small fw-bold text-uppercase" style="font-size: 8px;">Protection</span>
                        </div>
                        <div class="col-4">
                            <i class="fas fa-check-circle d-block mb-1"></i>
                            <span class="small fw-bold text-uppercase small" style="font-size: 8px;">Authentic</span>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>

<!-- Address Modal -->
<div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h3 class="h5 fw-bold mb-0" id="addressModalLabel"><?php echo t('add_new_address'); ?></h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="addressForm">
                    <input type="hidden" name="address_id" id="modal_address_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase ls-1"><?php echo t('phone_number'); ?> *</label>
                            <input type="tel" name="phone_number" id="modal_phone" class="form-control rounded-pill px-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase ls-1"><?php echo t('wilaya'); ?> *</label>
                            <input type="text" name="wilaya" id="modal_wilaya" class="form-control rounded-pill px-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase ls-1"><?php echo t('daira'); ?> *</label>
                            <input type="text" name="daira" id="modal_daira" class="form-control rounded-pill px-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase ls-1"><?php echo t('commune'); ?> *</label>
                            <input type="text" name="commune" id="modal_commune" class="form-control rounded-pill px-3" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted text-uppercase ls-1"><?php echo t('street_address'); ?> *</label>
                            <input type="text" name="street_address" id="modal_street" class="form-control rounded-pill px-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase ls-1"><?php echo t('apartment_suite'); ?></label>
                            <input type="text" name="apartment_suite" id="modal_apartment" class="form-control rounded-pill px-3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-muted text-uppercase ls-1"><?php echo t('postal_code'); ?></label>
                            <input type="text" name="postal_code" id="modal_postal" class="form-control rounded-pill px-3">
                        </div>
                        <div class="col-12">
                            <div class="form-check small mt-2">
                                <input class="form-check-input focus-ring-danger" type="checkbox" name="save_for_future" id="modal_save" checked>
                                <label class="form-check-label text-muted" for="modal_save">
                                    <?php echo t('save_information_for_future'); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top-0 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal"><?php echo t('cancel'); ?></button>
                <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold" onclick="saveAddress()"><?php echo t('save'); ?></button>
            </div>
        </div>
    </div>
</div>



<script>
const cartSubtotal = <?php echo $subtotal; ?>;
const cartProductIds = <?php echo json_encode(array_column($cartItems, 'product_id')); ?>;
let appliedCoupon = null;
let currentDiscountAmount = 0;

let addressModal;
document.addEventListener('DOMContentLoaded', function() {
    addressModal = new bootstrap.Modal(document.getElementById('addressModal'));
});

function applyPromo() {
    const code = document.getElementById('promo_code').value.trim();
    if (!code) {
        showNotification('Please enter a promo code', 'error');
        return;
    }

    fetch('api/coupons.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            code: code,
            cart_amount: cartSubtotal,
            product_ids: cartProductIds
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            appliedCoupon = data.coupon;
            currentDiscountAmount = data.discount_amount;
            
            // Update UI
            document.getElementById('discount-row').classList.remove('d-none');
            document.getElementById('discount-row').classList.add('d-flex');
            document.getElementById('discount-amount').textContent = '-' + currentDiscountAmount.toLocaleString() + ' DA';
            
            // Update Total
            const shippingCost = parseInt(document.getElementById('shipping-cost').textContent.replace(/[^0-9]/g, ''));
            const newTotal = cartSubtotal + shippingCost - currentDiscountAmount;
            document.getElementById('total-amount').textContent = newTotal.toLocaleString() + ' DA';
            
            showNotification(data.message, 'success');
            
            // Disable input and button
            document.getElementById('promo_code').disabled = true;
            document.querySelector('button[onclick="applyPromo()"]').disabled = true;
            document.querySelector('button[onclick="applyPromo()"]').textContent = 'Applied';
        } else {
            showNotification(data.message, 'error');
            appliedCoupon = null;
            currentDiscountAmount = 0;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error validating coupon', 'error');
    });
}

function selectAddress(card, id) {
    document.querySelectorAll('.address-card').forEach(c => {
        c.classList.remove('border-danger', 'shadow-sm', 'bg-danger-subtle'); // Remove old logic class if any
        c.classList.remove('border-danger', 'shadow-sm'); // New logic
        c.classList.add('border-light-subtle');
        
        // Reset icons
        const checkIcon = c.querySelector('.check-icon');
        const uncheckIcon = c.querySelector('.uncheck-icon');
        if (checkIcon) checkIcon.classList.add('d-none');
        if (uncheckIcon) uncheckIcon.classList.remove('d-none');
    });
    
    // Also handle the Add New Address card specially if needed, but it doesn't have icons usually
    // The previous loop covers address-card class which Add button shouldn't have if it's separate style
    // But in HTML I gave it different structure.
    
    // Select the clicked card
    card.classList.remove('border-light-subtle');
    card.classList.add('border-danger', 'shadow-sm');
    
    // Toggle icons for this card
    const checkIcon = card.querySelector('.check-icon');
    const uncheckIcon = card.querySelector('.uncheck-icon');
    if (checkIcon) checkIcon.classList.remove('d-none');
    if (uncheckIcon) uncheckIcon.classList.add('d-none');

    document.getElementById('selected_address_id').value = id;
}


function openAddressModal(addressId = null) {
    const title = document.getElementById('addressModalLabel');
    const form = document.getElementById('addressForm');
    form.reset();
    document.getElementById('modal_address_id').value = addressId || '';
    
    if (addressId && addressId !== 'new') {
        title.textContent = '<?php echo t('modify_address'); ?>';
        fetch(`api/addresses.php?id=${addressId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const a = data.address;
                    document.getElementById('modal_phone').value = a.phone_number || '';
                    document.getElementById('modal_wilaya').value = a.wilaya || '';
                    document.getElementById('modal_daira').value = a.daira || '';
                    document.getElementById('modal_commune').value = a.commune || '';
                    document.getElementById('modal_street').value = a.street_address || '';
                    document.getElementById('modal_apartment').value = a.apartment_suite || '';
                    document.getElementById('modal_postal').value = a.postal_code || '';
                }
            });
    } else {
        title.textContent = '<?php echo t('add_new_address'); ?>';
    }
    addressModal.show();
}

function closeAddressModal() {
    addressModal.hide();
}

function saveAddress() {
    const form = document.getElementById('addressForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    if (!data.phone_number || !data.wilaya || !data.street_address) {
        showNotification('Please fill in all required fields', 'error');
        return;
    }

    // Check if we should save to DB (Save for future checked)
    if (document.getElementById('modal_save').checked) {
        const addressId = document.getElementById('modal_address_id').value;
        const method = (addressId && addressId !== 'new') ? 'PUT' : 'POST';
        
        // Add ID to data if updating
        if (method === 'PUT') {
            data.id = addressId;
        }

        fetch('api/addresses.php', {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification(result.message || 'Address saved successfully', 'success');
                setTimeout(() => location.reload(), 500);
            } else {
                showNotification(result.error || 'Failed to save address', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while saving the address', 'error');
        });
        
        return; // Exit here, page reload handles the UI update
    }

    // Existing logic for temporary/guest address usage
    const tempDiv = document.getElementById('temp_address_data');
    tempDiv.dataset.json = JSON.stringify(data);
    const addCard = document.getElementById('add-address-card-btn');
    
    // Transform the Add Card into an Address Card look-alike
    addCard.classList.remove('align-items-center', 'justify-content-center', 'text-center', 'bg-light-subtle');
    
    addCard.innerHTML = `
        <div class="d-flex gap-3">
            <div class="mt-1">
                <i class="far fa-circle text-muted fs-5 uncheck-icon d-none"></i>
                <i class="fas fa-check-circle text-danger fs-5 check-icon"></i>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="fw-bold text-dark small text-uppercase">New Address</span>
                </div>
                
                <div class="text-muted small mb-3" style="line-height: 1.4; font-size: 0.85rem;">
                    <div>${data.street_address}</div>
                    <div>${data.commune}, ${data.wilaya} ${data.postal_code || ''}</div>
                </div>
                
                <div class="d-flex align-items-center justify-content-between border-top pt-2">
                    <div class="d-flex align-items-center text-dark small fw-bold">
                        <i class="fas fa-phone-alt me-2 text-muted" style="font-size: 0.7rem;"></i>
                        ${data.phone_number}
                    </div>
                    <button class="btn btn-link btn-sm text-decoration-none p-0 text-muted fw-bold" 
                            style="font-size: 0.75rem;" 
                            onclick="event.stopPropagation(); openAddressModal()">
                        Edit
                    </button>
                </div>
            </div>
        </div>
    `;
    
    selectAddress(addCard, 'new');
    closeAddressModal();
}

document.querySelectorAll('input[name="delivery_option"]').forEach(radio => {
    radio.addEventListener('change', function() {
        let shippingCost = 500;
        if (this.value === 'express') shippingCost = 1000;
        else if (this.value === 'home_delivery') shippingCost = 600;
        document.getElementById('shipping-cost').textContent = shippingCost.toLocaleString() + ' DA';
        const subtotal = <?php echo $subtotal; ?>;
        document.getElementById('total-amount').textContent = (subtotal + shippingCost - currentDiscountAmount).toLocaleString() + ' DA';
    });
});

function placeOrder() {
    if (!document.getElementById('terms_agree').checked) {
        showNotification('<?php echo addslashes(t('please_agree_to_terms')); ?>', 'error');
        return;
    }
    const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
    let guestInfo = null;
    if (!isLoggedIn) {
        guestInfo = {
            first_name: document.getElementById('guest_first_name').value,
            last_name: document.getElementById('guest_last_name').value,
            email: document.getElementById('guest_email').value
        };
        if (!guestInfo.first_name || !guestInfo.email) {
            showNotification('Please fill in all contact information', 'error');
            return;
        }
    }
    const selectedAddressId = document.getElementById('selected_address_id').value;
    let addressData = null;
    if (selectedAddressId === 'new') {
        const tempAddress = document.getElementById('temp_address_data').dataset.json;
        if (!tempAddress) {
            showNotification('Please provide a delivery address', 'error');
            return;
        }
        addressData = JSON.parse(tempAddress);
    }
    const deliveryOption = document.querySelector('input[name="delivery_option"]:checked').value;
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
    const orderData = {
        delivery_address_id: selectedAddressId,
        address_data: addressData,
        guest_info: guestInfo,
        delivery_option: deliveryOption,
        payment_method: paymentMethod,
        order_notes: document.getElementById('order_notes').value,
        promo_code: appliedCoupon ? appliedCoupon.code : null
    };
    fetch('api/orders/place.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('<?php echo addslashes(t('order_placed')); ?>', 'success');
            setTimeout(() => { window.location.href = 'order_status.php?status=success&id=' + data.order_id; }, 1500);
        } else {
            window.location.href = `order_status.php?status=failed&error=${data.error_type || 'server_error'}&reason=${encodeURIComponent(data.message || '')}`;
        }
    });
}
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>