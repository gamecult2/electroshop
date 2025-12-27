<?php
// admin/couriers.php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';

// Requires 'couriers' table
// CREATE TABLE couriers (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     name VARCHAR(100) NOT NULL,
//     tracking_url VARCHAR(255),
//     is_active TINYINT(1) DEFAULT 1
// );

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_courier'])) {
    $name = sanitize_input($_POST['name']);
    $tracking_url = filter_var($_POST['tracking_url'], FILTER_SANITIZE_URL);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO couriers (name, tracking_url, is_active) VALUES (?, ?, ?)");
        if ($stmt->execute([$name, $tracking_url, $is_active])) {
            $message = 'Courier added successfully.';
            $messageType = 'success';
        } else {
            $message = 'Failed to add courier.';
            $messageType = 'error';
        }
    } else {
        $message = 'Courier name is required.';
        $messageType = 'error';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = (int)$_POST['id'];
    $name = sanitize_input($_POST['name']);
    $tracking_url = filter_var($_POST['tracking_url'], FILTER_SANITIZE_URL);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!empty($name)) {
        $stmt = $pdo->prepare("UPDATE couriers SET name = ?, tracking_url = ?, is_active = ? WHERE id = ?");
        if ($stmt->execute([$name, $tracking_url, $is_active, $id])) {
            $message = 'Courier updated successfully.';
            $messageType = 'success';
        } else {
            $message = 'Failed to update courier.';
            $messageType = 'error';
        }
    } else {
        $message = 'Courier name is required.';
        $messageType = 'error';
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $courierId = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM couriers WHERE id = ?");
    $stmt->execute([$courierId]);
    $message = 'Courier deleted.';
    $messageType = 'success';
}

$couriers = $pdo->query("SELECT * FROM couriers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Set page title and heading variables for the template
$page_title = 'Manage Couriers';
$page_heading = 'Manage Couriers';

// Include the shared header template
include 'header.php';

?>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType === 'error' ? 'danger' : 'success'; ?> alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                    <i class="fas fa-<?php echo $messageType === 'error' ? 'exclamation-circle' : 'check-circle'; ?> me-2"></i>
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 border-0">
                            <h5 class="mb-0 fw-bold px-2"><i class="fas fa-truck me-2 text-danger"></i> Add New Courier</h5>
                        </div>
                        <div class="card-body p-4 pt-0">
                            <form method="POST" action="couriers.php">
                                <div class="mb-3">
                                    <label for="name" class="form-label small fw-bold text-muted text-uppercase">Courier Name *</label>
                                    <input type="text" id="name" name="name" class="form-control border-light-subtle shadow-none" placeholder="e.g. Yalidine, DHL" required>
                                </div>
                                <div class="mb-4">
                                    <label for="tracking_url" class="form-label small fw-bold text-muted text-uppercase">Tracking URL Pattern</label>
                                    <input type="url" id="tracking_url" name="tracking_url" class="form-control border-light-subtle shadow-none" placeholder="https://courier.com/track?id=%s">
                                    <div class="form-text x-small text-muted mt-2">Use <code>%s</code> as a placeholder for the tracking ID.</div>
                                </div>
                                <div class="card border-light-subtle bg-light p-3 rounded-3 mb-4 shadow-none">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active_add" checked>
                                        <label class="form-check-label fw-bold small text-dark" for="is_active_add">Active Courier</label>
                                    </div>
                                </div>
                                <button type="submit" name="add_courier" class="btn btn-danger w-100 py-2 fw-bold rounded-pill shadow-sm">
                                    <i class="fas fa-plus-circle me-1"></i> Add Courier
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm overflow-hidden mb-4">
                        <div class="card-header bg-white py-3 border-0">
                            <h2 class="h5 fw-bold mb-0 text-dark"><i class="fas fa-shipping-fast me-2 text-danger"></i> Courier List <span class="badge bg-light text-muted border ms-2 small fw-normal"><?php echo count($couriers); ?> Total</span></h2>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-3 border-0">Courier Name</th>
                                        <th class="border-0">Tracking URL Pattern</th>
                                        <th class="border-0">Status</th>
                                        <th class="border-0">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($couriers)): ?>
                                        <tr><td colspan="4" class="text-center py-5 text-muted"><i class="fas fa-truck fa-3x opacity-25 mb-3"></i><br>No couriers found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($couriers as $courier): ?>
                                            <tr>
                                                <td class="px-3 fw-bold text-dark"><?php echo htmlspecialchars($courier['name']); ?></td>
                                                <td><code class="x-small text-muted"><?php echo htmlspecialchars($courier['tracking_url'] ?: 'N/A'); ?></code></td>
                                                <td>
                                                    <span class="badge rounded-pill px-3 py-2 fw-bold <?php echo $courier['is_active'] ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis'; ?>" style="font-size: 10px;">
                                                        <?php echo $courier['is_active'] ? 'ACTIVE' : 'INACTIVE'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group shadow-sm rounded">
                                                        <button onclick='openCourierEditModal(<?php echo json_encode($courier); ?>)' class="btn btn-white btn-sm border-light-subtle text-primary bg-white" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <a href="couriers.php?action=delete&id=<?php echo $courier['id']; ?>" class="btn btn-white btn-sm border-light-subtle text-danger bg-white" title="Delete" onclick="return confirm('Are you sure you want to delete this courier?');">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
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
    </div>

    <!-- Edit Courier Modal -->
    <div class="modal fade" id="courierModal" tabindex="-1" aria-labelledby="courierModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 py-3 px-4 bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="courierModalLabel"><i class="fas fa-edit me-2 text-danger"></i>Edit Courier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm" method="POST">
                    <div class="modal-body p-4">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="editId">
                        
                        <div class="mb-3">
                            <label for="editName" class="form-label small fw-bold text-muted text-uppercase">Courier Name *</label>
                            <input type="text" id="editName" name="name" class="form-control border-light-subtle shadow-none" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="editTrackingUrl" class="form-label small fw-bold text-muted text-uppercase">Tracking URL Pattern</label>
                            <input type="url" id="editTrackingUrl" name="tracking_url" class="form-control border-light-subtle shadow-none" placeholder="https://example.com/track?id=%s">
                        </div>
                        
                        <div class="card border-light-subtle bg-light p-3 rounded-3 shadow-none">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" name="is_active" id="editIsActive" value="1">
                                <label class="form-check-label fw-bold small text-dark" for="editIsActive">Active Courier</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold text-muted border shadow-xs" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    let courierModal;
    document.addEventListener('DOMContentLoaded', function() {
        courierModal = new bootstrap.Modal(document.getElementById('courierModal'));
    });

    function openCourierEditModal(courier) {
        document.getElementById('editId').value = courier.id;
        document.getElementById('editName').value = courier.name;
        document.getElementById('editTrackingUrl').value = courier.tracking_url || '';
        document.getElementById('editIsActive').checked = courier.is_active == 1;
        courierModal.show();
    }
    </script>

    <!-- Include the shared footer template -->
    <?php include 'footer.php'; ?>


