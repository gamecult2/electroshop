<?php
require_once 'includes/init.php';
require_once 'models/Order.php';
require_once 'includes/header.php';

$status = $_GET['status'] ?? 'unknown';
$orderId = $_GET['id'] ?? null;
$order = null;

if ($status === 'success' && $orderId) {
    $orderModel = new Order();
    $order = $orderModel->getById($orderId);
    if (!$order) redirect('index.php');
    $isGuest = is_null($order['customer_id']);
    $orderItems = $orderModel->getItems($orderId);
    $shippingAddress = json_decode($order['shipping_address'], true);
} elseif ($status === 'failed') {
    $error = $_GET['error'] ?? 'unknown';
    $reason = $_GET['reason'] ?? '';
    $error_messages = [
        'payment_declined' => 'Your payment was declined by the provider.',
        'stock_error' => 'One or more items in your cart are no longer available.',
        'server_error' => 'An internal server error occurred.',
        'unknown' => 'An unexpected error occurred.'
    ];
    $message = $error_messages[$error] ?? $error_messages['unknown'];
}

$progressStep1Class = 'completed';
$progressStep2Class = 'completed';
$progressStep3Class = $status === 'success' ? 'active completed' : 'active error';
?>

<div class="container-xxl pb-4">
    <?php 
    $breadcrumb_items = [
        ['label' => t('home'), 'url' => 'index.php'],
        ['label' => t('order_status')]
    ];
    include 'includes/breadcrumb.php';
    ?>

    <!-- Checkout Progress -->
    <div class="row justify-content-center mb-5 mt-4">
        <div class="col-12 col-md-10 col-lg-8">
            <div class="position-relative">
                <!-- Background Line -->
                <div class="position-absolute top-0 start-0 w-100" style="margin-top: 20px;">
                    <div class="bg-light-subtle" style="height: 4px; border-radius: 4px; margin: 0 40px;"></div>
                </div>
                
                <!-- Animated Progress Line -->
                <div class="position-absolute top-0 start-0 w-100" style="margin-top: 20px; z-index: 1;">
                    <div class="progress" style="height: 4px; background-color: transparent; margin: 0 40px; overflow: visible;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated <?php echo $status === 'success' ? 'bg-success' : 'bg-danger'; ?>" role="progressbar" style="width: 100%; border-radius: 4px;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
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
                        <div class="<?php echo $status === 'success' ? 'bg-success' : 'bg-danger'; ?> text-white rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-sm" style="width: 40px; height: 40px; border: 4px solid #fff;">
                            <i class="fas fa-check small"></i>
                        </div>
                        <div class="mt-2 small fw-bold text-uppercase <?php echo $status === 'success' ? 'text-success' : 'text-danger'; ?>" style="font-size: 11px; letter-spacing: 0.5px;"><?php echo t('checkout'); ?></div>
                    </div>
                    
                    <!-- Step 3: Complete -->
                    <div class="text-center" style="width: 80px;">
                        <div class="<?php echo $status === 'success' ? 'bg-success' : 'bg-danger'; ?> text-white rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-sm" style="width: 40px; height: 40px; border: 4px solid #fff;">
                            <i class="<?php echo $status === 'success' ? 'fas fa-check' : 'fas fa-times'; ?> small"></i>
                        </div>
                        <div class="mt-2 small fw-bold text-uppercase <?php echo $status === 'success' ? 'text-success' : 'text-danger'; ?>" style="font-size: 11px; letter-spacing: 0.5px;"><?php echo t('complete'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($status === 'success' && $order): ?>
        <div class="card border-0 shadow-sm mb-4 border-top border-4 border-success rounded-4 overflow-hidden">
            <div class="card-body p-5 text-center">
                <div class="display-1 text-success mb-4"><i class="fas fa-check-circle"></i></div>
                <h1 class="fw-bold text-success mb-3"><?php echo t('order_placed'); ?></h1>
                <p class="lead mb-2"><?php echo t('thank_you_order'); ?></p>
                <p class="text-muted small"><?php echo t('confirmation_email_sent'); ?> <strong><?php echo htmlspecialchars($order['customer_email'] ?? ''); ?></strong></p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4 border-start border-5 border-success-subtle bg-light">
                    <div class="card-body">
                        <div class="row text-center align-items-center">
                            <div class="col-md-6 border-end border-light">
                                <span class="text-muted small text-uppercase fw-bold d-block mb-1" style="letter-spacing: 1px;"><?php echo t('order_number'); ?></span>
                                <h2 class="h3 fw-bold text-dark mb-0"><?php echo htmlspecialchars($order['order_number'] ?? ''); ?></h2>
                            </div>
                            <div class="col-md-6">
                                <span class="text-muted small text-uppercase fw-bold d-block mb-1" style="letter-spacing: 1px;">Status</span>
                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold">
                                    <i class="fas fa-clock me-1"></i> <?php echo t($order['status'] ?? 'pending'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-box-open me-2 text-danger"></i> <?php echo t('order_items'); ?></h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php foreach ($orderItems as $item): ?>
                                <div class="list-group-item py-3">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <div class="bg-light rounded p-1" style="width: 80px; height: 80px;">
                                                <img src="<?php echo !empty($item['product_image']) ? htmlspecialchars($item['product_image']) : 'img/product-placeholder.jpg'; ?>" 
                                                     class="img-fluid rounded object-fit-contain w-100 h-100" alt="">
                                            </div>
                                        </div>
                                        <div class="col">
                                            <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                            <p class="text-muted small mb-0"><?php echo t('quantity'); ?>: <span class="text-dark fw-bold"><?php echo $item['quantity']; ?></span></p>
                                        </div>
                                        <div class="col-auto text-end">
                                            <div class="text-muted small"><?php echo format_price($item['price_at_purchase']); ?></div>
                                            <div class="fw-bold text-danger"><?php echo format_price($item['total_price']); ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-tasks me-2 text-danger"></i> <?php echo t('what_happens_next'); ?></h5>
                    </div>
                    <div class="card-body pb-5 pt-4">
                        <div class="row text-center position-relative">
                            <div class="col-4 z-2">
                                <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 shadow-sm" style="width: 40px; height: 40px;">
                                    <i class="fas fa-box"></i>
                                </div>
                                <span class="small fw-bold d-block"><?php echo t('step_processing'); ?></span>
                            </div>
                            <div class="col-4 z-2">
                                <div class="bg-light text-muted rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 shadow-sm" style="width: 40px; height: 40px;">
                                    <i class="fas fa-truck"></i>
                                </div>
                                <span class="small text-muted d-block"><?php echo t('step_shipping'); ?></span>
                            </div>
                            <div class="col-4 z-2">
                                <div class="bg-light text-muted rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 shadow-sm" style="width: 40px; height: 40px;">
                                    <i class="fas fa-home"></i>
                                </div>
                                <span class="small text-muted d-block"><?php echo t('step_delivery'); ?></span>
                            </div>
                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center z-1" style="transform: translateY(-10px);">
                                <div class="w-50 bg-light-subtle shadow-xs border-bottom border-light" style="height: 4px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-map-marker-alt me-2 text-danger"></i> <?php echo t('shipping_address'); ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if ($shippingAddress): ?>
                            <div class="small leading-relaxed">
                                <p class="fw-bold text-dark fs-6 mb-2"><?php echo htmlspecialchars($order['customer_name'] ?? ''); ?></p>
                                <p class="text-muted mb-1"><i class="fas fa-road me-2"></i> <?php echo htmlspecialchars($shippingAddress['street_address'] ?? ''); ?></p>
                                <p class="text-muted mb-3"><i class="fas fa-city me-2"></i> <?php echo htmlspecialchars(($shippingAddress['commune'] ?? '') . ', ' . ($shippingAddress['daira'] ?? '') . ', ' . ($shippingAddress['wilaya'] ?? '')); ?></p>
                                <div class="bg-light rounded p-2 text-dark font-monospace">
                                    <i class="fas fa-phone-alt me-2 text-danger"></i> <?php echo htmlspecialchars($shippingAddress['phone_number'] ?? ''); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-calculator me-2 text-danger"></i> <?php echo t('order_summary'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted"><?php echo t('subtotal'); ?>:</span>
                            <span class="fw-bold"><?php echo format_price($order['subtotal'] ?? 0); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted"><?php echo t('shipping'); ?>:</span>
                            <span class="fw-bold"><?php echo format_price($order['shipping_cost'] ?? 0); ?></span>
                        </div>
                        <?php if (($order['tax_amount'] ?? 0) > 0): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted"><?php echo t('tax'); ?>:</span>
                                <span class="fw-bold"><?php echo format_price($order['tax_amount'] ?? 0); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (($order['discount_amount'] ?? 0) > 0): ?>
                            <div class="d-flex justify-content-between mb-2 text-danger">
                                <span><?php echo t('discount'); ?>:</span>
                                <span class="fw-bold">-<?php echo format_price($order['discount_amount'] ?? 0); ?></span>
                            </div>
                        <?php endif; ?>
                        <hr class="my-3 border-light opacity-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="h5 mb-0 fw-bold text-dark"><?php echo t('total'); ?>:</span>
                            <span class="h3 mb-0 fw-bold text-danger"><?php echo format_price($order['total_amount'] ?? 0); ?></span>
                        </div>
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small"><?php echo t('payment_method'); ?>:</span>
                                <span class="badge bg-white text-dark border shadow-sm fw-bold"><?php echo t($order['payment_method'] ?? 'cod'); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-0">
                                <span class="text-muted small"><?php echo t('order_date'); ?>:</span>
                                <span class="text-dark fw-bold small"><?php echo date('M j, Y', strtotime($order['created_at'] ?? 'now')); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <?php if (is_logged_in()): ?>
                        <a href="order_history.php?view=details&order_id=<?php echo $order['id'] ?? 0; ?>" class="btn btn-danger btn-lg py-3 rounded-pill shadow-sm fw-bold">
                            <i class="fas fa-eye me-2"></i> <?php echo t('view_order_details'); ?>
                        </a>
                    <?php endif; ?>
                    <a href="index.php" class="btn btn-dark btn-lg py-3 rounded-pill fw-bold">
                        <i class="fas fa-home me-2"></i> <?php echo t('back_to_home'); ?>
                    </a>
                    <a href="products.php" class="btn btn-outline-dark btn-lg py-3 rounded-pill fw-bold border-2">
                        <i class="fas fa-shopping-bag me-2"></i> <?php echo t('continue_shopping'); ?>
                    </a>
                </div>
            </div>
        </div>
    <?php elseif ($status === 'failed'): ?>
        <div class="card border-0 shadow-sm mb-4 border-top border-4 border-danger rounded-4 overflow-hidden">
            <div class="card-body p-5 text-center">
                <div class="display-1 text-danger mb-4"><i class="fas fa-exclamation-circle"></i></div>
                <h1 class="fw-bold text-danger mb-3">Order Placement Failed</h1>
                <p class="lead mb-0">We encountered an issue while processing your order</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden border-start border-5 border-danger">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-danger"><i class="fas fa-times-circle me-2"></i> What went wrong?</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger mb-0 border-0 shadow-sm bg-danger-subtle">
                            <p class="fw-bold mb-1"><i class="fas fa-info-circle me-2"></i> <?php echo htmlspecialchars($message); ?></p>
                            <?php if ($reason): ?>
                                <hr class="my-2 border-danger opacity-25">
                                <p class="small mb-0 opacity-75 font-monospace italic">Error details: <?php echo htmlspecialchars($reason); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-lightbulb me-2 text-warning"></i> Recommended Next Steps</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-4 border h-100">
                                    <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                                        <i class="fas fa-redo"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1">Try again</h6>
                                        <p class="text-muted small mb-0">Verify your details and try placing the order again.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-4 border h-100">
                                    <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                                        <i class="fas fa-credit-card"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1">Alternative Payment</h6>
                                        <p class="text-muted small mb-0">Try a different payment method like Cash on Delivery.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm text-center py-5 px-4 mb-4 rounded-4 overflow-hidden h-100">
                    <div class="mb-4">
                        <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 100px; height: 100px;">
                            <i class="fas fa-headset text-danger display-4"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-2">Need Help?</h3>
                    <p class="text-muted mb-4 small">Our support team is ready to assist you with any issues during checkout.</p>
                    <div class="p-3 bg-danger-subtle rounded-4 text-center mb-4">
                        <strong class="d-block small text-uppercase text-danger fw-bold mb-1" style="letter-spacing: 1px;">Email Support</strong>
                        <span class="fs-5 fw-bold text-dark font-monospace"><?php echo SITE_EMAIL; ?></span>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <a href="checkout.php" class="btn btn-danger btn-lg py-3 rounded-pill shadow-sm fw-bold">
                        <i class="fas fa-sync me-2"></i> Try Again
                    </a>
                    <a href="cart.php" class="btn btn-outline-dark btn-lg py-3 rounded-pill fw-bold border-2">
                        <i class="fas fa-arrow-left me-2"></i> Back to Cart
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
