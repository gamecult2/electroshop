<?php
// admin/shipping_rates.php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';
$algerian_wilayas = get_algerian_wilayas(); // Assumes this function exists in functions.php

// Process rates for all wilayas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_rates'])) {
    foreach ($_POST['rates'] as $wilaya => $rate_data) {
        $rate = (float)($rate_data['rate'] ?? 0);
        $is_active = isset($rate_data['is_active']) ? 1 : 0;

        // Check if rate for this wilaya already exists
        $stmt = $pdo->prepare("SELECT id FROM shipping_rates WHERE wilaya = ?");
        $stmt->execute([$wilaya]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Update
            $stmt = $pdo->prepare("UPDATE shipping_rates SET flat_rate = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$rate, $is_active, $existing['id']]);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO shipping_rates (wilaya, flat_rate, is_active) VALUES (?, ?, ?)");
            $stmt->execute([$wilaya, $rate, $is_active]);
        }
    }
    $message = 'Shipping rates saved successfully.';
    $messageType = 'success';
}

$shipping_rates_db = $pdo->query("SELECT wilaya, flat_rate, is_active FROM shipping_rates")->fetchAll(PDO::FETCH_ASSOC);
$shipping_rates_assoc = [];
foreach ($shipping_rates_db as $rate) {
    $shipping_rates_assoc[$rate['wilaya']] = [
        'rate' => $rate['flat_rate'],
        'is_active' => $rate['is_active']
    ];
}

$shipping_rates = [];
foreach($algerian_wilayas as $code => $name) {
    $shipping_rates[$name] = [
        'code' => $code,
        'rate' => $shipping_rates_assoc[$name]['rate'] ?? 0,
        'is_active' => $shipping_rates_assoc[$name]['is_active'] ?? 0,
    ];
}

$page_title = 'Shipping Rates';
$page_heading = 'Shipping Rates';

// Include the shared header template
include 'header.php';
?>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo ($messageType === 'success') ? 'success' : 'danger'; ?> alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas <?php echo ($messageType === 'success') ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
                        <div><?php echo htmlspecialchars($message); ?></div>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <h5 class="fw-bold mb-1"><i class="fas fa-truck-loading me-2 text-primary"></i> Algerian Wilaya Rates</h5>
                            <p class="text-muted small mb-0">Set the shipping cost and availability for each Algerian wilaya.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <div class="bg-light px-3 py-2 rounded-3 border border-light-subtle text-center">
                                <div class="x-small text-muted text-uppercase fw-bold">Active Zones</div>
                                <div class="fw-bold text-primary">
                                    <?php 
                                    echo count(array_filter($shipping_rates, function($r) { return $r['is_active']; }));
                                    ?> / <?php echo count($algerian_wilayas); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="shipping_rates.php">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold small text-uppercase text-muted">Wilaya Configuration</h6>
                        <button type="submit" name="save_rates" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm fw-bold">
                            <i class="fas fa-save me-2"></i> Save Changes
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase" style="width: 80px;">No.</th>
                                        <th class="border-0 py-3 small fw-bold text-muted text-uppercase">Wilaya Name</th>
                                        <th class="border-0 py-3 small fw-bold text-muted text-uppercase" style="width: 200px;">Rate (<?php echo htmlspecialchars(get_setting('currency_code', 'DZD')); ?>)</th>
                                        <th class="border-0 py-3 small fw-bold text-muted text-uppercase text-center" style="width: 150px;">Status</th>
                                        <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase text-end" style="width: 150px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($algerian_wilayas as $code => $name): 
                                        $rate_info = $shipping_rates[$name] ?? ['rate' => 0, 'is_active' => 0];
                                    ?>
                                        <tr>
                                            <td class="px-4 fw-bold text-muted small"><?php echo $code; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-light text-primary p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                    </div>
                                                    <div class="fw-bold text-dark small text-uppercase"><?php echo htmlspecialchars($name); ?></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group input-group-sm">
                                                    <input type="number" name="rates[<?php echo htmlspecialchars($name); ?>][rate]" 
                                                           value="<?php echo htmlspecialchars($rate_info['rate']); ?>" 
                                                           class="form-control border-light-subtle shadow-none fw-bold" 
                                                           step="0.01">
                                                    <span class="input-group-text bg-light border-light-subtle text-muted"><?php echo htmlspecialchars(get_setting('currency_code', 'DZD')); ?></span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input shadow-none pointer" type="checkbox" 
                                                           name="rates[<?php echo htmlspecialchars($name); ?>][is_active]" 
                                                           value="1" <?php echo $rate_info['is_active'] ? 'checked' : ''; ?>>
                                                </div>
                                            </td>
                                            <td class="px-4 text-end">
                                                <button type="submit" name="save_rates" class="btn btn-white btn-xs border-light-subtle shadow-xs rounded-pill px-3 fw-bold">
                                                    Save
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Include the shared footer template -->
    <?php include 'footer.php'; ?>


