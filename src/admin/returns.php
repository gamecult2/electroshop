<?php
// admin/returns.php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_return_status'])) {
    $returnId = (int)$_POST['return_id'];
    $status = sanitize_input($_POST['status']);
    $stmt = $pdo->prepare("UPDATE returns SET status = ? WHERE id = ?");
    if ($stmt->execute([$status, $returnId])) {
        $message = 'Return status updated successfully.';
        $messageType = 'success';
    } else {
        $message = 'Failed to update return status.';
        $messageType = 'error';
    }
}

$stmt = $pdo->query("
    SELECT r.id, r.order_id, r.reason, r.status, r.requested_at as created_at,
           CONCAT(u.first_name, ' ', u.last_name) AS username
    FROM returns r
    JOIN users u ON r.user_id = u.id
    ORDER BY r.requested_at DESC
");
$returns = $stmt->fetchAll(PDO::FETCH_ASSOC);
$return_statuses = ['pending', 'approved', 'rejected', 'processing', 'completed'];

$page_title = 'Manage Returns';
$page_heading = 'Return Requests';

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

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom-0">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-undo me-2 text-primary"></i> All Return Requests</h5>
                    <span class="badge bg-light text-muted fw-bold rounded-pill px-3 py-2 small border border-light-subtle shadow-xs">
                        <?php echo count($returns); ?> Total
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase" style="width: 120px;">Return ID</th>
                                    <th class="border-0 py-3 small fw-bold text-muted text-uppercase" style="width: 120px;">Order #</th>
                                    <th class="border-0 py-3 small fw-bold text-muted text-uppercase">Customer</th>
                                    <th class="border-0 py-3 small fw-bold text-muted text-uppercase">Reason for Return</th>
                                    <th class="border-0 py-3 small fw-bold text-muted text-uppercase">Request Date</th>
                                    <th class="border-0 px-4 py-3 small fw-bold text-muted text-uppercase text-end" style="width: 300px;">Current Status / Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($returns)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                                                <p class="mb-0 fw-bold">No return requests found</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($returns as $return): ?>
                                        <tr>
                                            <td class="px-4">
                                                <span class="fw-bold text-dark">#RET-<?php echo str_pad($return['id'], 5, '0', STR_PAD_LEFT); ?></span>
                                            </td>
                                            <td>
                                                <a href="admin_order_details.php?id=<?php echo $return['order_id']; ?>" class="text-decoration-none fw-bold text-primary small">
                                                    <i class="fas fa-shopping-bag me-1 small"></i>#<?php echo $return['order_id']; ?>
                                                </a>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-light text-muted p-2 rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                        <i class="fas fa-user small"></i>
                                                    </div>
                                                    <div class="fw-bold text-dark small"><?php echo htmlspecialchars($return['username']); ?></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="small text-muted py-1 bg-light rounded px-2 border border-light-subtle d-inline-block" style="max-width: 250px; white-space: normal; line-height: 1.4;">
                                                    <?php echo htmlspecialchars($return['reason']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="small fw-bold text-dark"><?php echo date('M j, Y', strtotime($return['created_at'])); ?></div>
                                                <div class="x-small text-muted"><?php echo date('H:i', strtotime($return['created_at'])); ?></div>
                                            </td>
                                            <td class="px-4 text-end">
                                                <form method="POST" action="returns.php" class="d-flex justify-content-end gap-2">
                                                    <input type="hidden" name="return_id" value="<?php echo $return['id']; ?>">
                                                    <div class="input-group input-group-sm w-auto">
                                                        <?php
                                                        $statusClass = 'bg-secondary';
                                                        switch ($return['status']) {
                                                            case 'pending': $statusClass = 'bg-warning'; break;
                                                            case 'approved': $statusClass = 'bg-info'; break;
                                                            case 'processing': $statusClass = 'bg-primary'; break;
                                                            case 'completed': $statusClass = 'bg-success'; break;
                                                            case 'rejected': $statusClass = 'bg-danger'; break;
                                                        }
                                                        ?>
                                                        <span class="input-group-text <?php echo $statusClass; ?> text-white border-0 px-2">
                                                            <i class="fas fa-circle x-small me-1"></i>
                                                        </span>
                                                        <select name="status" class="form-select form-select-sm border-light-subtle shadow-none fw-bold small" style="min-width: 120px;">
                                                            <?php foreach ($return_statuses as $status): ?>
                                                                <option value="<?php echo $status; ?>" <?php echo $return['status'] === $status ? 'selected' : ''; ?>>
                                                                    <?php echo ucfirst($status); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <button type="submit" name="update_return_status" class="btn btn-white border border-light-subtle fw-bold shadow-xs px-3">
                                                            Update
                                                        </button>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include the shared footer template -->
    <?php include 'footer.php'; ?>


