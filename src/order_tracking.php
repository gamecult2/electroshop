<?php
require_once 'includes/init.php';
require_once 'models/Order.php';
require_login();

$orderId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
$orderModel = new Order();
$order = $orderId ? $orderModel->getById($orderId) : null;
if ($order && (int)$order['customer_id'] !== (int)get_current_user_id()) $order = null;

$history = [];
if ($order) {
    $stmt = $GLOBALS['pdo']->prepare('SELECT status, note, created_at FROM order_status_history WHERE order_id = ? ORDER BY created_at ASC');
    $stmt->execute([$orderId]);
    $history = $stmt->fetchAll();
}

$page_title = 'Order tracking';
require_once 'includes/header.php';
?>
<div class="container-xxl pb-5">
    <?php
    $breadcrumb_items = [
        ['label' => t('home'), 'url' => 'index.php'],
        ['label' => t('order_history'), 'url' => 'order_history.php'],
        ['label' => t('track_order')],
    ];
    include 'includes/breadcrumb.php';
    ?>

    <?php if (!$order): ?>
        <section class="card app-card text-center p-4 p-md-5">
            <div class="card-body">
                <i class="fas fa-box-open text-muted display-4 mb-3" aria-hidden="true"></i>
                <h1 class="app-page-title fw-bold">Tracking unavailable</h1>
                <p class="text-muted">The order does not exist or is not associated with your account.</p>
                <a href="order_history.php" class="btn btn-danger rounded-pill px-4">Return to order history</a>
            </div>
        </section>
    <?php else: ?>
        <header class="customer-page-header d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-3">
            <div>
                <h1 class="app-page-title fw-bold">Track order #<?php echo htmlspecialchars($order['order_number']); ?></h1>
                <p class="text-muted">Placed <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
            </div>
            <span class="badge bg-danger rounded-pill px-3 py-2 text-uppercase"><?php echo htmlspecialchars($order['status']); ?></span>
        </header>

        <div class="row g-4">
            <div class="col-lg-8">
                <section class="card app-card h-100">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="app-section-heading mb-4">Shipment progress</h2>
                        <?php if (!$history): ?>
                            <div class="d-flex gap-3 align-items-start">
                                <span class="bg-danger text-white rounded-circle app-icon-button flex-shrink-0"><i class="fas fa-receipt" aria-hidden="true"></i></span>
                                <div><strong>Order received</strong><p class="text-muted mb-0">We are preparing the first tracking update.</p></div>
                            </div>
                        <?php else: ?>
                            <ol class="list-unstyled m-0 d-grid gap-4">
                                <?php foreach ($history as $event): ?>
                                    <li class="d-flex gap-3 align-items-start">
                                        <span class="bg-danger text-white rounded-circle app-icon-button flex-shrink-0"><i class="fas fa-check" aria-hidden="true"></i></span>
                                        <div>
                                            <strong class="d-block text-capitalize"><?php echo htmlspecialchars(str_replace('_', ' ', $event['status'])); ?></strong>
                                            <?php if (!empty($event['note'])): ?><p class="text-muted mb-1"><?php echo htmlspecialchars($event['note']); ?></p><?php endif; ?>
                                            <time class="app-meta text-muted" datetime="<?php echo htmlspecialchars($event['created_at']); ?>"><?php echo date('M j, Y g:i A', strtotime($event['created_at'])); ?></time>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
            <div class="col-lg-4">
                <aside class="card app-card">
                    <div class="card-body p-4">
                        <h2 class="app-section-heading mb-3">Delivery details</h2>
                        <dl class="mb-0 d-grid gap-3">
                            <div><dt class="app-meta text-muted text-uppercase">Tracking number</dt><dd class="fw-bold mb-0"><?php echo htmlspecialchars($order['tracking_number'] ?: 'Not assigned yet'); ?></dd></div>
                            <div><dt class="app-meta text-muted text-uppercase">Estimated delivery</dt><dd class="fw-bold mb-0"><?php echo !empty($order['estimated_delivery']) ? date('F j, Y', strtotime($order['estimated_delivery'])) : 'To be confirmed'; ?></dd></div>
                        </dl>
                        <a href="order_history.php?view=details&amp;order_id=<?php echo (int)$orderId; ?>" class="btn btn-outline-dark rounded-pill w-100 mt-4">View order details</a>
                    </div>
                </aside>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?>
