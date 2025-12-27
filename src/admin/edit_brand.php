<?php
// admin/edit_brand.php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';
$brand = null;
$brandId = (int)($_GET['id'] ?? 0);

// Get brand details
if ($brandId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM brands WHERE id = ?");
    $stmt->execute([$brandId]);
    $brand = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$brand) {
        $message = 'Brand not found.';
        $messageType = 'error';
    }
} else {
    header('Location: brands.php');
    exit;
}

// Update brand
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name']);
    $description = sanitize_input($_POST['description']);
    $website_url = filter_var($_POST['website_url'], FILTER_SANITIZE_URL);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $logo_url = $brand['logo_url'];
    
    // Handle logo upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        // Delete old file if it exists
        if (!empty($brand['logo_url']) && file_exists('../' . $brand['logo_url'])) {
            unlink('../' . $brand['logo_url']);
        }
        
        $uploadDir = '../uploads/images/brands/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $filename = create_slug($name) . '-' . substr(md5(uniqid()), 0, 8) . '.' . $extension;
        
        $targetFile = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetFile)) {
            $logo_url = 'uploads/images/brands/' . $filename;
        }
    }
    
    $stmt = $pdo->prepare("UPDATE brands SET name=?, description=?, logo_url=?, website_url=?, is_active=? WHERE id=?");
    if ($stmt->execute([$name, $description, $logo_url, $website_url, $is_active, $brandId])) {
        $message = 'Brand updated successfully.';
        $messageType = 'success';
        
        // Refresh brand data
        $stmt = $pdo->prepare("SELECT * FROM brands WHERE id = ?");
        $stmt->execute([$brandId]);
        $brand = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $message = 'Failed to update brand.';
        $messageType = 'error';
    }
}

// Set page title and heading variables for the template
$page_title = 'Edit Brand';
$page_heading = 'Edit Brand';

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

            <?php if ($brand): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-0">
                    <h5 class="mb-0 fw-bold px-2"><i class="fas fa-edit me-2 text-danger"></i> Edit Brand</h5>
                    <a href="brands.php" class="btn btn-light btn-sm text-secondary rounded-pill px-3 fw-bold">
                        <i class="fas fa-arrow-left me-1"></i> Back to Brands
                    </a>
                </div>
                <div class="card-body p-4 pt-0">
                    <form method="POST" action="edit_brand.php?id=<?php echo $brandId; ?>" enctype="multipart/form-data">
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label small fw-bold text-muted text-uppercase">Brand Name *</label>
                                <input type="text" id="name" name="name" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars($brand['name']); ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="website_url" class="form-label small fw-bold text-muted text-uppercase">Website URL</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light-subtle text-muted"><i class="fas fa-globe"></i></span>
                                    <input type="url" id="website_url" name="website_url" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars($brand['website_url']); ?>" placeholder="https://example.com">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label for="description" class="form-label small fw-bold text-muted text-uppercase">Description</label>
                                <textarea id="description" name="description" class="form-control border-light-subtle shadow-none" rows="3"><?php echo htmlspecialchars($brand['description']); ?></textarea>
                            </div>

                            <div class="col-md-12">
                                <label for="logo" class="form-label small fw-bold text-muted text-uppercase">Brand Logo</label>
                                <input type="file" id="logo" name="logo" class="form-control border-light-subtle shadow-none" accept="image/*">
                                <?php if (!empty($brand['logo_url'])): ?>
                                    <div class="mt-3 p-3 border rounded-3 bg-light d-inline-block shadow-xs">
                                        <div class="small fw-bold text-muted x-small mb-2 text-uppercase">Current Logo</div>
                                        <img src="../<?php echo htmlspecialchars($brand['logo_url']); ?>" class="img-fluid rounded border shadow-sm" style="max-height: 80px;" alt="Brand Logo">
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-12">
                                <div class="card border-light-subtle bg-light p-3 rounded-3 shadow-none">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" <?php echo $brand['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label fw-bold small text-dark" for="is_active">Active Status (Brand will be visible to customers)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3 mt-2">
                            <button type="submit" class="btn btn-danger px-5 py-2 fw-bold rounded-pill shadow-sm">
                                <i class="fas fa-save me-1"></i> Update Brand
                            </button>
                            <a href="brands.php" class="btn btn-light px-4 py-2 fw-bold rounded-pill text-muted border shadow-xs">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
                <div class="alert alert-danger border-0 shadow-sm rounded-4 p-4 d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3 opacity-50"></i>
                    <div>
                        <h5 class="fw-bold mb-1">Brand Not Found</h5>
                        <p class="mb-0 small text-danger-emphasis">The brand you are trying to edit does not exist or has been deleted.</p>
                        <a href="brands.php" class="btn btn-danger btn-sm mt-3 rounded-pill px-3 fw-bold shadow-sm">Return to List</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Include the shared footer template -->
    <?php include 'footer.php'; ?>

