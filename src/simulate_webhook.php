<?php
// src/simulate_webhook.php
// A helper script to simulate Chargily Webhooks for specific orders.

require_once 'includes/init.php';
require_once 'includes/functions.php';
require_once 'models/Order.php';

// 1. Get Secret Key
$secret = get_setting('chargily_secret_key');
if (!$secret) $secret = defined('CHARGILY_SECRET_KEY') ? CHARGILY_SECRET_KEY : '';

if (empty($secret)) {
    die("Error: Chargily Secret Key not found.");
}

$message = "";
$orderModel = new Order();

// 2. Handle HTTP POST Trigger
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $checkoutId = trim($_POST['checkout_id'] ?? '');
    $type = $_POST['event_type'] ?? 'checkout.paid';
    $method = $_POST['payment_method'] ?? 'edahabia';
    
    // Fetch order to get real amount if possible
    $order = $orderModel->getById($orderId);
    $amount = $order ? $order['total_amount'] : 1000; // Fallback to 1000 if order not found
    
    // Construct Status based on type
    $status = 'pending';
    if ($type === 'checkout.paid') $status = 'paid';
    if ($type === 'checkout.failed' || $type === 'checkout.expired') $status = 'failed';

    $payload = [
        "id" => "evt_" . uniqid(),
        "entity" => "event",
        "livemode" => "false",
        "type" => $type,
        "data" => [
            "id" => $checkoutId ?: ("chk_" . uniqid()),
            "entity" => "checkout",
            "status" => $status,
            "amount" => (float)$amount, 
            "currency" => "dzd",
            "payment_method" => $method,
            "metadata" => [
                "order_id" => (string)$orderId
            ],
            "customer_id" => "cust_" . uniqid(),
            "updated_at" => time(),
            "created_at" => time()
        ],
        "created_at" => time()
    ];

    $jsonPayload = json_encode($payload);
    $signature = hash_hmac('sha256', $jsonPayload, $secret);
    
    // DEBUG LOG
    $logFile = __DIR__ . '/debug_payment.log';
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - SIMULATOR: Payload: " . $jsonPayload . "\n", FILE_APPEND);
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - SIMULATOR: Calculated Sig: " . $signature . "\n", FILE_APPEND);

    $webhookUrl = SITE_URL . "/webhook_chargily.php";
    // Fix spaces for cURL
    $webhookUrl = str_replace(' ', '%20', $webhookUrl);

    $ch = curl_init($webhookUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'signature: ' . $signature
    ]);
    
    // Handle SSL for local WAMP
    $cacert = __DIR__ . '/includes/cacert.pem';
    if (file_exists($cacert)) {
        curl_setopt($ch, CURLOPT_CAINFO, $cacert);
    } else {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode == 200) {
        $message = "<div class='alert alert-success mt-3 shadow-sm border-0'><i class='fas fa-check-circle me-2'></i> <strong>Webhook Sent!</strong><br>Target Order: #$orderId<br>Event: $type<br>Response: <pre class='mt-2 small mb-0'>$response</pre></div>";
    } else {
        $errorMsg = $curlError ? " (cURL Error: $curlError)" : "";
        $message = "<div class='alert alert-danger mt-3 shadow-sm border-0'><i class='fas fa-exclamation-triangle me-2'></i> <strong>Simulation Failed!</strong><br>HTTP Code: $httpCode$errorMsg<br>Target URL: <code>$webhookUrl</code><br>Response: <pre class='mt-2 small mb-0'>$response</pre></div>";
    }
}

// Set recent checkouts from database for easier testing
$recentOrders = $pdo->query("SELECT id, order_number, total_amount, transaction_id FROM orders WHERE payment_method = 'chargily' ORDER BY created_at DESC LIMIT 10")->fetchAll();

include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-dark text-white py-3 px-4 d-flex align-items-center justify-content-between">
                    <h4 class="mb-0 fw-bold"><i class="fas fa-microchip me-2 text-danger"></i> Chargily Webhook Simulator</h4>
                    <span class="badge bg-danger rounded-pill px-3">Localhost Mode</span>
                </div>
                <div class="card-body p-4 bg-light-subtle">
                    <div class="alert alert-info border-0 shadow-xs mb-4">
                        <i class="fas fa-info-circle me-2"></i> Use this tool to test webhooks on your local computer. It signs the request with your <strong>real secret key</strong> so <code>webhook_chargily.php</code> will accept it as authentic.
                    </div>

                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted text-uppercase ls-1">QwenShop Order ID</label>
                            <input type="number" name="order_id" class="form-control border-0 shadow-sm rounded-3" placeholder="e.g. 80" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted text-uppercase ls-1">Chargily Checkout ID (Optional)</label>
                            <input type="text" name="checkout_id" class="form-control border-0 shadow-sm rounded-3" placeholder="Auto-generated if empty">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted text-uppercase ls-1">Event Type</label>
                            <select name="event_type" class="form-select border-0 shadow-sm rounded-3">
                                <option value="checkout.paid" class="text-success">checkout.paid (User finished payment)</option>
                                <option value="checkout.failed" class="text-danger">checkout.failed (Payment rejected)</option>
                                <option value="checkout.expired" class="text-warning">checkout.expired (Time ran out)</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted text-uppercase ls-1">Simulated Method</label>
                            <select name="payment_method" class="form-select border-0 shadow-sm rounded-3">
                                <option value="edahabia">Edahabia (Poste Algérie)</option>
                                <option value="cib">CIB Card</option>
                            </select>
                        </div>

                        <div class="col-12 mt-4 text-center">
                            <button type="submit" class="btn btn-danger btn-lg px-5 rounded-pill fw-bold shadow">
                                <i class="fas fa-paper-plane me-2"></i> Fire Simulated Webhook
                            </button>
                        </div>
                    </form>

                    <?php echo $message; ?>
                </div>

                <?php if ($recentOrders): ?>
                <div class="card-footer bg-white p-0">
                    <div class="p-3 border-bottom bg-light">
                        <h6 class="mb-0 fw-bold small text-muted text-uppercase ls-1"><i class="fas fa-history me-1"></i> Quick Test: Recent Online Orders</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 py-2">Order #</th>
                                    <th>Ref</th>
                                    <th>Checkout ID</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $ro): ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-dark">#<?php echo $ro['id']; ?></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($ro['order_number']); ?></span></td>
                                    <td><code class="x-small text-muted"><?php echo $ro['transaction_id'] ?: 'N/A'; ?></code></td>
                                    <td class="text-end pe-3 py-2">
                                        <button type="button" class="btn btn-xs btn-outline-danger py-0 px-2 fw-bold" style="font-size: 10px;" 
                                                onclick="fillForm(<?php echo $ro['id']; ?>, '<?php echo $ro['transaction_id']; ?>')">
                                            Select
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="text-center mt-4">
                <a href="admin/orders.php" class="btn btn-link text-decoration-none text-muted small">
                    <i class="fas fa-arrow-left me-1"></i> Back to Orders Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function fillForm(id, tx) {
    document.querySelector('input[name="order_id"]').value = id;
    document.querySelector('input[name="checkout_id"]').value = tx;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

<style>
    .ls-1 { letter-spacing: 1px; }
    .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .btn-xs { padding: 1px 5px; font-size: 12px; }
</style>

<?php include 'includes/footer.php'; ?>
