<?php
// admin/orders.php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';
require_once '../models/Order.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$orderModel = new Order();
$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['message_type'] ?? '';
unset($_SESSION['message'], $_SESSION['message_type']);

$view = $_GET['view'] ?? 'active';
$isArchived = ($view === 'archived') ? 1 : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status_filter']) ? trim($_GET['status_filter']) : '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : null;
        $orderIds = isset($_POST['order_ids']) ? $_POST['order_ids'] : []; // For bulk actions
        $adminId = $_SESSION['admin_id'] ?? null;
        
        switch ($_POST['action']) {
            case 'update_status':
                $status = sanitize_input($_POST['status']);
                if ($orderModel->updateStatus($orderId, $status, $adminId, 'Status updated from main list.')) {
                    $_SESSION['message'] = 'Order status updated successfully.';
                    $_SESSION['message_type'] = 'success';
                }
                break;
                
            case 'archive_order':
                if ($orderModel->archive($orderId, 1)) {
                    $_SESSION['message'] = 'Order archived successfully.';
                    $_SESSION['message_type'] = 'success';
                }
                break;
                
            case 'restore_order':
                if ($orderModel->archive($orderId, 0)) {
                    $_SESSION['message'] = 'Order restored successfully.';
                    $_SESSION['message_type'] = 'success';
                }
                break;
                
            case 'delete_order':
                if ($orderModel->delete($orderId)) {
                    $_SESSION['message'] = 'Order deleted permanently.';
                    $_SESSION['message_type'] = 'success';
                }
                break;

            // BULK ACTIONS
            case 'bulk_archive':
                $count = 0;
                foreach ($orderIds as $id) {
                    if ($orderModel->archive((int)$id, 1)) $count++;
                }
                $_SESSION['message'] = "$count orders archived successfully.";
                $_SESSION['message_type'] = 'success';
                break;

            case 'bulk_restore':
                $count = 0;
                foreach ($orderIds as $id) {
                    if ($orderModel->archive((int)$id, 0)) $count++;
                }
                $_SESSION['message'] = "$count orders restored successfully.";
                $_SESSION['message_type'] = 'success';
                break;

            case 'bulk_delete':
                $count = 0;
                foreach ($orderIds as $id) {
                    if ($orderModel->delete((int)$id)) $count++;
                }
                $_SESSION['message'] = "$count orders deleted permanently.";
                $_SESSION['message_type'] = 'success';
                break;

            case 'bulk_status':
                $status = sanitize_input($_POST['bulk_status_val']);
                $count = 0;
                foreach ($orderIds as $id) {
                    if ($orderModel->updateStatus((int)$id, $status, $adminId, 'Bulk status update.')) $count++;
                }
                $_SESSION['message'] = "Status updated for $count orders.";
                $_SESSION['message_type'] = 'success';
                break;

                break;
        }
        header("Location: orders.php?view=$view" . ($search ? "&search=".urlencode($search) : "") . ($status_filter ? "&status_filter=".urlencode($status_filter) : ""));
        exit;
    }
}

$orders = $orderModel->getAllOrdersWithUserDetails([
    'is_archived' => $isArchived,
    'search' => $search,
    'status' => $status_filter
]);

// Auto-Verify Pending Chargily Payments (Limit to first 5 to maintain performance)
$pendingVerificationCount = 0;
$logFile = __DIR__ . '/../debug_payment.log';

foreach ($orders as &$o) {
    if ($pendingVerificationCount >= 5) break;
    if ($o['payment_method'] === 'chargily' && $o['payment_status'] !== 'paid' && !empty($o['transaction_id'])) {
        try {
            if (!isset($chargilySvc)) {
                require_once '../services/ChargilyService.php';
                $chargilySvc = new ChargilyService();
            }
            
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Auto-Checking Order #{$o['id']} (Tx: {$o['transaction_id']})\n", FILE_APPEND);
            
            $checkout = $chargilySvc->getCheckout($o['transaction_id']);
            if ($checkout) {
                $remoteStatus = $checkout->getStatus();
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Order #{$o['id']} Remote Status: {$remoteStatus}\n", FILE_APPEND);
                
                if ($remoteStatus === 'paid') {
                    $orderModel->updatePaymentDetails($o['id'], 'paid', $o['transaction_id'], $checkout->toArray());
                    $orderModel->updateStatus($o['id'], 'processing', null, 'Verified on list view');
                    // Refresh local data for display
                    $o['payment_status'] = 'paid';
                    $o['status'] = 'processing';
                    $o['payment_gateway_response'] = json_encode($checkout->toArray());
                    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Order #{$o['id']} Sync SUCCESS\n", FILE_APPEND);
                }
            } else {
                file_put_contents($logFile, date('Y-m-d H:i:s') . " - Order #{$o['id']} API returned NULL\n", FILE_APPEND);
            }
        } catch (Exception $e) {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - Order #{$o['id']} Sync Error: " . $e->getMessage() . "\n", FILE_APPEND);
        }
        $pendingVerificationCount++;
    }
}
unset($o);

$order_statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'];

// Set page title and heading variables for the template
$page_title = 'Order Management';
$page_heading = 'Order Management';

// Include the shared header template
include 'header.php';
?>

            <?php if ($message): ?>
                <!-- ... existing alert ... -->
            <?php endif; ?>

            <!-- Filters -->
            <div class="card border-0 shadow-sm p-3 mb-4">
                <form class="row g-3 align-items-center" method="GET" action="orders.php">
                    <input type="hidden" name="view" value="<?php echo htmlspecialchars($view); ?>">
                    
                    <div class="col-auto">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-light-subtle text-muted"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control border-light-subtle shadow-none" placeholder="Search orders..." value="<?php echo htmlspecialchars($search); ?>" style="width: 200px;">
                        </div>
                    </div>
                    
                    <div class="col-auto">
                        <select name="status_filter" class="form-select form-select-sm border-light-subtle shadow-none" style="width: 180px;">
                            <option value="">All Statuses</option>
                            <?php foreach ($order_statuses as $status): ?>
                                <option value="<?php echo $status; ?>" <?php echo $status_filter === $status ? 'selected' : ''; ?>>
                                    <?php echo ucfirst($status); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-auto">
                        <button type="submit" class="btn btn-danger btn-sm px-4 rounded-pill fw-bold">Filter</button>
                        <?php if ($search || $status_filter): ?>
                            <a href="orders.php?view=<?php echo $view; ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-4 fw-bold">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="card border-0 shadow-sm overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h2 class="h5 fw-bold mb-0 text-dark">
                        <i class="fas fa-shopping-bag me-2 text-danger"></i> 
                        <?php echo ($isArchived) ? 'Archived Orders' : 'Active Orders'; ?> 
                        <span class="badge bg-light text-muted border ms-2 small fw-normal"><?php echo count($orders); ?> Total</span>
                    </h2>
                    <div class="btn-group rounded-pill overflow-hidden border shadow-xs">
                        <a href="orders.php?view=active" class="btn btn-sm <?php echo (!$isArchived) ? 'btn-danger' : 'btn-light'; ?> px-3 fw-bold" style="font-size: 11px;">Active</a>
                        <a href="orders.php?view=archived" class="btn btn-sm <?php echo ($isArchived) ? 'btn-danger' : 'btn-light'; ?> px-3 fw-bold" style="font-size: 11px;">Archived</a>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <form id="bulkForm" method="POST" action="orders.php?view=<?php echo $view; ?>">
                        <input type="hidden" name="action" id="bulkActionInput" value="">
                        <input type="hidden" name="bulk_status_val" id="bulkStatusInput" value="">
                        
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-3 border-0" style="width: 40px;">
                                        <div class="form-check">
                                            <input class="form-check-input shadow-none cursor-pointer" type="checkbox" id="selectAll" onclick="toggleAll(this)">
                                        </div>
                                    </th>
                                    <th class="border-0">Order ID</th>
                                    <th class="border-0">Reference</th>
                                    <th class="border-0">Customer</th>
                                    <th class="border-0">Date & Time</th>
                                    <th class="border-0">Total Amount</th>
                                    <th class="border-0">Payment</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0 text-center">Actions</th>
                                </tr>
                            </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr><td colspan="9" class="text-center py-5 text-muted"><i class="fas fa-receipt fa-3x opacity-25 mb-3"></i><br>No orders found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td class="px-3">
                                            <div class="form-check">
                                                <input class="form-check-input shadow-none cursor-pointer order-checkbox" type="checkbox" name="order_ids[]" value="<?php echo $order['id']; ?>" onclick="updateBulkToolbar()">
                                            </div>
                                        </td>
                                        <td class="fw-bold text-muted x-small">#<?php echo htmlspecialchars($order['id'] ?? ''); ?></td>
                                        <td class="fw-bold text-dark small"><?php echo htmlspecialchars($order['order_number'] ?? 'N/A'); ?></td>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($order['username'] ?? 'Guest'); ?></div>
                                        </td>
                                        <td>
                                            <div class="small text-dark fw-medium"><?php echo date('Y-m-d', strtotime($order['created_at'] ?? 'now')); ?></div>
                                            <div class="x-small text-muted"><?php echo date('H:i', strtotime($order['created_at'] ?? 'now')); ?></div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="fw-bold text-danger"><?php echo format_price($order['total_amount']); ?></span>
                                                <?php if (isset($order['discount_amount']) && $order['discount_amount'] > 0): ?>
                                                    <i class="fas fa-tag text-success small ms-2" title="Discount Applied: <?php echo format_price($order['discount_amount']); ?>"></i>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php 
                                            $pStatus = $order['payment_status'] ?? 'pending';
                                            $pClass = match($pStatus) {
                                                'paid' => 'bg-success',
                                                'failed' => 'bg-danger',
                                                'refunded' => 'bg-info',
                                                default => 'bg-warning text-dark'
                                            };
                                            ?>
                                            <div class="d-flex flex-column align-items-start">
                                                <span class="badge <?php echo $pClass; ?> rounded-pill mb-1 fw-bold text-uppercase" style="font-size: 8px; letter-spacing: 0.5px; padding: 3px 8px;">
                                                    <?php echo $pStatus; ?>
                                                </span>
                                                <div class="x-small text-muted fw-bold" style="font-size: 10px;">
                                                    <?php 
                                                    if (!empty($order['payment_gateway_response'])) {
                                                        $resp = json_decode($order['payment_gateway_response'], true);
                                                        $subMethod = $resp['payment_method'] ?? $order['payment_method'];
                                                        $formattedSubMethod = (strtolower($subMethod) === 'cib') ? 'CIB' : ucfirst($subMethod);
                                                        echo "Chargily (" . $formattedSubMethod . ")";
                                                    } else if (($order['payment_method'] ?? '') === 'chargily') {
                                                        echo "Chargily (Waiting for Payment)";
                                                    } else {
                                                        echo strtoupper($order['payment_method'] ?? 'COD');
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <!-- Single status update form (keep existing logic) -->
                                            <div class="d-flex gap-2 align-items-center">
                                                <select class="form-select form-select-sm rounded-pill fw-bold text-uppercase px-3 shadow-none <?php 
                                                    echo match($order['status']) {
                                                        'pending' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                                        'processing' => 'bg-info-subtle text-info-emphasis border-info-subtle',
                                                        'shipped' => 'bg-primary-subtle text-primary-emphasis border-primary-subtle',
                                                        'delivered' => 'bg-success-subtle text-success-emphasis border-success-subtle',
                                                        'cancelled' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle',
                                                        default => 'bg-secondary-subtle'
                                                    };
                                                ?>" style="font-size: 10px; width: 125px;" onchange="updateSingleStatus(<?php echo $order['id']; ?>, this.value)">
                                                    <?php foreach ($order_statuses as $status): ?>
                                                        <option value="<?php echo $status; ?>" <?php echo $order['status'] === $status ? 'selected' : ''; ?>>
                                                            <?php echo ucfirst($status); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="admin_order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-white border shadow-xs rounded-circle text-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <!-- Single Archive/Restore -->
                                                <button type="button" class="btn btn-sm btn-white border shadow-xs rounded-circle text-warning" 
                                                        onclick="submitSingleAction(<?php echo $order['id']; ?>, '<?php echo ($isArchived) ? 'restore_order' : 'archive_order'; ?>')"
                                                        title="<?php echo ($isArchived) ? 'Restore Order' : 'Archive Order'; ?>">
                                                    <i class="fas fa-<?php echo ($isArchived) ? 'undo' : 'archive'; ?>"></i>
                                                </button>

                                                <!-- Single Delete -->
                                                <button type="button" class="btn btn-sm btn-white border shadow-xs rounded-circle text-danger" 
                                                        onclick="if(confirm('Delete order?')) submitSingleAction(<?php echo $order['id']; ?>, 'delete_order')"
                                                        title="Delete Permanently">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions Toolbar -->
    <div id="bulkToolbar" class="fixed-bottom bg-dark text-white p-3 shadow-lg d-none animate__animated animate__slideInUp" style="z-index: 1040;">
        <div class="container-fluid d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <span class="fw-bold text-danger"><i class="fas fa-check-square me-2"></i> <span id="selectedCount">0</span> selected</span>
                <div class="vr"></div>
                <div class="d-flex align-items-center gap-2">
                    <label class="small fw-bold text-muted text-uppercase mb-0">Update Status:</label>
                    <select id="bulkStatusSelect" class="form-select form-select-sm rounded-pill bg-secondary text-white border-0 px-3" style="width: 150px;">
                        <option value="">Choose...</option>
                        <?php foreach ($order_statuses as $status): ?>
                            <option value="<?php echo $status; ?>"><?php echo ucfirst($status); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary btn-sm rounded-pill px-3 fw-bold" onclick="applyBulkStatus()">Apply</button>
                </div>
            </div>
            <div class="d-flex gap-2">
                <?php if ($isArchived): ?>
                    <button class="btn btn-warning btn-sm rounded-pill px-4 fw-bold" onclick="applyBulkAction('bulk_restore')"><i class="fas fa-undo me-1"></i> Restore All</button>
                <?php else: ?>
                    <button class="btn btn-warning btn-sm rounded-pill px-4 fw-bold" onclick="applyBulkAction('bulk_archive')"><i class="fas fa-archive me-1"></i> Archive Selected</button>
                <?php endif; ?>
                <button class="btn btn-danger btn-sm rounded-pill px-4 fw-bold" onclick="applyBulkAction('bulk_delete')"><i class="fas fa-trash me-1"></i> Delete Permanent</button>
                <button class="btn btn-light btn-sm rounded-pill px-3" onclick="cancelSelection()">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Hidden form for single actions -->
    <form id="singleActionForm" method="POST" action="orders.php?view=<?php echo $view; ?>" style="display:none;">
        <input type="hidden" name="action" id="sa_action">
        <input type="hidden" name="order_id" id="sa_order_id">
        <input type="hidden" name="status" id="sa_status">
    </form>

    <script>
        function toggleAll(master) {
            const checkboxes = document.querySelectorAll('.order-checkbox');
            checkboxes.forEach(cb => cb.checked = master.checked);
            updateBulkToolbar();
        }

        function updateBulkToolbar() {
            const checkboxes = document.querySelectorAll('.order-checkbox:checked');
            const toolbar = document.getElementById('bulkToolbar');
            const countSpan = document.getElementById('selectedCount');
            const selectAll = document.getElementById('selectAll');
            const allCount = document.querySelectorAll('.order-checkbox').length;

            countSpan.textContent = checkboxes.length;
            
            if (checkboxes.length > 0) {
                toolbar.classList.remove('d-none');
            } else {
                toolbar.classList.add('d-none');
            }

            // Sync master checkbox
            if (checkboxes.length === 0) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            } else if (checkboxes.length === allCount) {
                selectAll.checked = true;
                selectAll.indeterminate = false;
            } else {
                selectAll.checked = false;
                selectAll.indeterminate = true;
            }
        }

        function cancelSelection() {
            document.getElementById('selectAll').checked = false;
            toggleAll(document.getElementById('selectAll'));
        }

        function applyBulkAction(action) {
            if (action === 'bulk_delete' && !confirm('Permanently delete all selected orders?')) return;
            
            document.getElementById('bulkActionInput').value = action;
            document.getElementById('bulkForm').submit();
        }

        function applyBulkStatus() {
            const status = document.getElementById('bulkStatusSelect').value;
            if (!status) {
                alert('Please select a status');
                return;
            }
            document.getElementById('bulkStatusInput').value = status;
            document.getElementById('bulkActionInput').value = 'bulk_status';
            document.getElementById('bulkForm').submit();
        }

        function submitSingleAction(id, action) {
            document.getElementById('sa_order_id').value = id;
            document.getElementById('sa_action').value = action;
            document.getElementById('singleActionForm').submit();
        }

        function updateSingleStatus(id, status) {
            document.getElementById('sa_order_id').value = id;
            document.getElementById('sa_status').value = status;
            document.getElementById('sa_action').value = 'update_status';
            document.getElementById('singleActionForm').submit();
        }
    </script>
        </div>
    </div>

    <!-- Include the shared footer template -->
    <?php include 'footer.php'; ?>


