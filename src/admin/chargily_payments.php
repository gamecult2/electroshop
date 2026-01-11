<?php
// admin/chargily_payments.php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';
require_once '../services/ChargilyService.php';
require_once '../models/Order.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$chargily = new ChargilyService();
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$pagination = $chargily->listCheckouts(20, $page);
$checkouts = $pagination ? $pagination->getData() : [];

$page_title = 'Chargily Transactions';
include 'header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark">Chargily Transactions</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item small"><a href="dashboard.php" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item small active fw-bold text-danger">Chargily API History</li>
            </ol>
        </nav>
    </div>
    <a href="settings.php#chargily" class="btn btn-white border border-light-subtle rounded-pill px-3 fw-bold shadow-xs">
        <i class="fas fa-cog me-1 text-muted"></i> API Settings
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-history me-2 text-primary"></i> Live API Checkouts</h6>
        <span class="badge bg-light text-dark border fw-normal">Real-time data from Chargily Pay</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 px-4 py-3 small fw-bold text-uppercase ls-1">Checkout ID</th>
                        <th class="border-0 py-3 small fw-bold text-uppercase ls-1">Order #</th>
                        <th class="border-0 py-3 small fw-bold text-uppercase ls-1">Customer</th>
                        <th class="border-0 py-3 small fw-bold text-uppercase ls-1 text-center">Amount</th>
                        <th class="border-0 py-3 small fw-bold text-uppercase ls-1 text-center">Status</th>
                        <th class="border-0 py-3 small fw-bold text-uppercase ls-1 text-center">Method</th>
                        <th class="border-0 px-4 py-3 small fw-bold text-uppercase ls-1 text-end">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($checkouts)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">No transactions found in your API history.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($checkouts as $c): 
                            $meta = $c->getMetadata();
                            $orderId = $meta['order_id'] ?? 'N/A';
                         ?>
                            <tr>
                                <td class="px-4 py-3">
                                    <code class="x-small text-danger fw-bold"><?php echo $c->getId(); ?></code>
                                </td>
                                <td>
                                    <?php if ($orderId !== 'N/A'): ?>
                                        <a href="admin_order_details.php?id=<?php echo $orderId; ?>" class="badge bg-dark text-decoration-none">#<?php echo $orderId; ?></a>
                                    <?php else: ?>
                                        <span class="text-muted small">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="small fw-bold text-dark"><?php echo htmlspecialchars($c->getCustomerId() ?? 'Guest'); ?></div>
                                    <div class="x-small text-muted"><?php echo $c->getInvoiceId(); ?></div>
                                </td>
                                <td class="text-center">
                                    <div class="fw-bold text-dark small"><?php echo number_format($c->getAmount(), 2); ?> DA</div>
                                </td>
                                <td class="text-center">
                                    <?php 
                                    $status = $c->getStatus();
                                    $badgeClass = 'bg-secondary';
                                    if ($status === 'paid') $badgeClass = 'bg-success';
                                    elseif ($status === 'failed') $badgeClass = 'bg-danger';
                                    elseif ($status === 'pending') $badgeClass = 'bg-warning text-dark';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?> rounded-pill text-uppercase x-small px-2">
                                        <?php echo $status; ?>
                                    </span>
                                </td>
                                <td class="text-center small">
                                    <span class="text-muted text-uppercase x-small fw-bold"><?php echo $c->getPaymentMethod() ?: 'ANY'; ?></span>
                                </td>
                                <td class="text-end px-4">
                                    <div class="small text-dark"><?php echo $c->getCreatedAt()->format('M d, H:i'); ?></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($pagination && $pagination->getLastPage() > 1): ?>
    <div class="card-footer bg-white border-top py-3">
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm justify-content-center mb-0">
                <?php for ($i = 1; $i <= $pagination->getLastPage(); $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<div class="mt-4 text-center">
    <div class="alert alert-light border small text-muted d-inline-block px-4 rounded-pill">
        <i class="fas fa-info-circle me-1 text-primary"></i> This page shows data directly from <strong>Chargily's Servers</strong>. If an order isn't marked as Paid in your local DB, check the status here.
    </div>
</div>

<?php include 'footer.php'; ?>
