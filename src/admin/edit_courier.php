<?php
// admin/edit_courier.php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';
$courier = null;
$courierId = (int)($_GET['id'] ?? 0);

// Get courier details
if ($courierId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM couriers WHERE id = ?");
    $stmt->execute([$courierId]);
    $courier = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$courier) {
        $message = 'Courier not found.';
        $messageType = 'error';
    }
} else {
    header('Location: couriers.php');
    exit;
}

// Update courier
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name']);
    $tracking_url = filter_var($_POST['tracking_url'], FILTER_SANITIZE_URL);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $stmt = $pdo->prepare("UPDATE couriers SET name=?, tracking_url=?, is_active=?, updated_at=NOW() WHERE id=?");
    if ($stmt->execute([$name, $tracking_url, $is_active, $courierId])) {
        $message = 'Courier updated successfully.';
        $messageType = 'success';
        
        // Refresh courier data
        $stmt = $pdo->prepare("SELECT * FROM couriers WHERE id = ?");
        $stmt->execute([$courierId]);
        $courier = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $message = 'Failed to update courier.';
        $messageType = 'error';
    }
}

// Set page title and heading variables for the template
$page_title = 'Edit Courier';
$page_heading = 'Edit Courier';

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

            <?php if ($courier): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-0">
                    <h5 class="mb-0 fw-bold px-2"><i class="fas fa-edit me-2 text-danger"></i> Edit Courier</h5>
                    <a href="couriers.php" class="btn btn-light btn-sm text-secondary rounded-pill px-3 fw-bold">
                        <i class="fas fa-arrow-left me-1"></i> Back to Couriers
                    </a>
                </div>
                <div class="card-body p-4 pt-0">
                    <form method="POST" action="edit_courier.php?id=<?php echo $courierId; ?>">
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label small fw-bold text-muted text-uppercase">Courier Name *</label>
                                <input type="text" id="name" name="name" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars($courier['name']); ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="tracking_url" class="form-label small fw-bold text-muted text-uppercase">Tracking URL Pattern</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light-subtle text-muted"><i class="fas fa-route"></i></span>
                                    <input type="url" id="tracking_url" name="tracking_url" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars($courier['tracking_url']); ?>" placeholder="https://example.com/track/{tracking_number}">
                                </div>
                                <div class="form-text x-small text-muted mt-1 italic">Use {tracking_number} as a placeholder for the actual number</div>
                            </div>

                            <div class="col-12">
                                <div class="card border-light-subtle bg-light p-3 rounded-3 shadow-none">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" <?php echo $courier['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label fw-bold small text-dark" for="is_active">Active Status (Available for shipping)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3 mt-2">
                            <button type="submit" class="btn btn-danger px-5 py-2 fw-bold rounded-pill shadow-sm">
                                <i class="fas fa-save me-1"></i> Update Courier
                            </button>
                            <a href="couriers.php" class="btn btn-light px-4 py-2 fw-bold rounded-pill text-muted border shadow-xs">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
                <div class="alert alert-danger border-0 shadow-sm rounded-4 p-4 d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3 opacity-50"></i>
                    <div>
                        <h5 class="fw-bold mb-1">Courier Not Found</h5>
                        <p class="mb-0 small text-danger-emphasis">The courier you are trying to edit does not exist or has been deleted.</p>
                        <a href="couriers.php" class="btn btn-danger btn-sm mt-3 rounded-pill px-3 fw-bold shadow-sm">Return to List</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Include the shared footer template -->
    <?php include 'footer.php'; ?>

