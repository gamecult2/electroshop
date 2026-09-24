<?php
require_once 'includes/init.php';
require_once 'models/Order.php';

$orderId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
$order = $orderId ? (new Order())->getById($orderId) : null;
if (!$order || !in_array($order['payment_method'], ['bank_transfer', 'baridimob'], true)) {
    http_response_code(404);
    exit('Order not found.');
}
$customerId = get_current_user_id();
$authorized = $customerId
    ? (int)$order['customer_id'] === (int)$customerId
    : $order['customer_id'] === null && !empty($_SESSION['recent_guest_order_ids'][(int)$orderId]);
if (!$authorized) {
    http_response_code(403);
    exit('You cannot view this order.');
}

$isBaridiMob = $order['payment_method'] === 'baridimob';
$methodLabel = $isBaridiMob ? 'BaridiMob transfer' : 'Bank transfer';
$bankName = $isBaridiMob ? 'Algérie Poste' : trim(get_setting('bank_transfer_bank_name', ''));
$holder = trim(get_setting($isBaridiMob ? 'baridimob_holder' : 'bank_transfer_holder', ''));
$rib = trim(get_setting($isBaridiMob ? 'baridimob_rip' : 'bank_transfer_rib', ''));
$ccp = $isBaridiMob ? trim(get_setting('baridimob_ccp', '')) : '';
$detailsReady = $holder !== '' && ($isBaridiMob ? ($rib !== '' || $ccp !== '') : ($bankName !== '' && $rib !== ''));
$page_title = $methodLabel . ' | GameCult';
require_once 'includes/header.php';
?>
<main class="container-xxl py-4 py-md-5" id="main-content">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span class="bg-danger-subtle text-danger rounded-3 p-3"><i class="fas <?php echo $isBaridiMob ? 'fa-mobile-alt' : 'fa-university'; ?> fa-lg" aria-hidden="true"></i></span>
                        <div>
                            <h1 class="h3 fw-bold mb-1"><?php echo $methodLabel; ?></h1>
                            <p class="text-muted mb-0">Order #<?php echo htmlspecialchars($order['order_number']); ?></p>
                        </div>
                    </div>

                    <?php if ($order['status'] === 'cancelled'): ?>
                        <div class="alert alert-secondary">This order was cancelled. Do not transfer payment for it.</div>
                    <?php elseif ($order['payment_status'] === 'paid'): ?>
                        <div class="alert alert-success"><i class="fas fa-check-circle me-2" aria-hidden="true"></i>Payment received and confirmed.</div>
                    <?php else: ?>
                        <div class="alert alert-warning mb-4"><i class="fas fa-clock me-2" aria-hidden="true"></i>Your order is placed, but payment is pending. We will confirm it after verifying the transfer.</div>
                        <div class="d-flex justify-content-between align-items-center border rounded-3 p-3 mb-4">
                            <span class="fw-semibold">Amount to transfer</span>
                            <strong class="fs-4 text-danger"><?php echo format_price($order['total_amount']); ?></strong>
                        </div>

                        <?php if ($detailsReady): ?>
                            <h2 class="h5 fw-bold mb-3">Transfer to this account</h2>
                            <dl class="row border rounded-3 p-3 mb-4">
                                <dt class="col-sm-4 text-muted">Bank</dt><dd class="col-sm-8 fw-semibold"><?php echo htmlspecialchars($bankName); ?></dd>
                                <dt class="col-sm-4 text-muted">Account holder</dt><dd class="col-sm-8 fw-semibold"><?php echo htmlspecialchars($holder); ?></dd>
                                <?php if ($ccp !== ''): ?><dt class="col-sm-4 text-muted">CCP</dt><dd class="col-sm-8 fw-semibold font-monospace text-break"><?php echo htmlspecialchars($ccp); ?></dd><?php endif; ?>
                                <?php if ($rib !== ''): ?><dt class="col-sm-4 text-muted"><?php echo $isBaridiMob ? 'RIP' : 'RIB / account number'; ?></dt><dd class="col-sm-8 fw-semibold font-monospace text-break"><?php echo htmlspecialchars($rib); ?></dd><?php endif; ?>
                                <dt class="col-sm-4 text-muted">Payment reference</dt><dd class="col-sm-8 fw-semibold font-monospace mb-0"><?php echo htmlspecialchars($order['order_number']); ?></dd>
                            </dl>
                            <p class="text-muted small">Include the order number as the transfer reference. Your payment status stays pending until the store verifies receipt.</p>
                        <?php else: ?>
                            <div class="alert alert-info"><?php echo $isBaridiMob ? 'BaridiMob' : 'Bank'; ?> details are not configured yet. Please contact the store before making a transfer. Quote order #<?php echo htmlspecialchars($order['order_number']); ?>.</div>
                            <a href="contact.php" class="btn btn-outline-danger">Contact support</a>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($customerId): ?>
                        <a href="order_history.php?view=details&amp;order_id=<?php echo (int)$orderId; ?>" class="btn btn-danger mt-3">View order details</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require_once 'includes/footer.php'; ?>
