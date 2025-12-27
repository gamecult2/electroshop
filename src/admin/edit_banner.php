<?php
// admin/edit_banner.php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';
$banner = null;
$bannerId = (int)($_GET['id'] ?? 0);

// Get banner details
if ($bannerId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM banners WHERE id = ?");
    $stmt->execute([$bannerId]);
    $banner = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$banner) {
        $message = 'Banner not found.';
        $messageType = 'error';
    }
} else {
    header('Location: banners.php');
    exit;
}

// Update banner
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize_input($_POST['title']);
    $link_url = filter_var($_POST['link_url'], FILTER_SANITIZE_URL);
    $position = sanitize_input($_POST['position']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    $desktop_image = $banner['desktop_image'];
    $mobile_image = $banner['mobile_image'];
    
    // Handle desktop image upload
    if (isset($_FILES['desktop_image']) && $_FILES['desktop_image']['error'] === UPLOAD_ERR_OK) {
        // Delete old file if it exists
        if (!empty($banner['desktop_image']) && file_exists('../' . $banner['desktop_image'])) {
            unlink('../' . $banner['desktop_image']);
        }
        
        $uploadDir = '../uploads/images/banners/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $extension = strtolower(pathinfo($_FILES['desktop_image']['name'], PATHINFO_EXTENSION));
        $filename = create_slug($title) . '-desktop-' . substr(md5(uniqid()), 0, 8) . '.' . $extension;
        
        $targetFile = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['desktop_image']['tmp_name'], $targetFile)) {
            $desktop_image = 'uploads/images/banners/' . $filename;
        }
    }
    
    // Handle mobile image upload
    if (isset($_FILES['mobile_image']) && $_FILES['mobile_image']['error'] === UPLOAD_ERR_OK) {
        // Delete old file if it exists
        if (!empty($banner['mobile_image']) && file_exists('../' . $banner['mobile_image'])) {
            unlink('../' . $banner['mobile_image']);
        }
        
        $uploadDir = '../uploads/images/banners/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $extension = strtolower(pathinfo($_FILES['mobile_image']['name'], PATHINFO_EXTENSION));
        $filename = create_slug($title) . '-mobile-' . substr(md5(uniqid()), 0, 8) . '.' . $extension;
        
        $targetFile = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['mobile_image']['tmp_name'], $targetFile)) {
            $mobile_image = 'uploads/images/banners/' . $filename;
        }
    }
    
    $stmt = $pdo->prepare("UPDATE banners SET title=?, desktop_image=?, mobile_image=?, link_url=?, position=?, is_active=? WHERE id=?");
    if ($stmt->execute([$title, $desktop_image, $mobile_image, $link_url, $position, $is_active, $bannerId])) {
        $message = 'Banner updated successfully.';
        $messageType = 'success';
        
        // Refresh banner data
        $stmt = $pdo->prepare("SELECT * FROM banners WHERE id = ?");
        $stmt->execute([$bannerId]);
        $banner = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $message = 'Failed to update banner.';
        $messageType = 'error';
    }
}

// Set page title and heading variables for the template
$page_title = 'Edit Banner';
$page_heading = 'Edit Banner';

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

            <?php if ($banner): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-0">
                    <h5 class="mb-0 fw-bold px-2"><i class="fas fa-edit me-2 text-danger"></i> Edit Banner</h5>
                    <a href="banners.php" class="btn btn-light btn-sm text-secondary rounded-pill px-3 fw-bold">
                        <i class="fas fa-arrow-left me-1"></i> Back to Banners
                    </a>
                </div>
                <div class="card-body p-4 pt-0">
                    <form method="POST" action="edit_banner.php?id=<?php echo $bannerId; ?>" enctype="multipart/form-data">
                        <div class="row g-4 mb-4">
                            <div class="col-md-12">
                                <label for="title" class="form-label small fw-bold text-muted text-uppercase">Banner Title *</label>
                                <input type="text" id="title" name="title" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars($banner['title']); ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="desktop_image" class="form-label small fw-bold text-muted text-uppercase">Desktop Image</label>
                                <div class="input-group mb-2">
                                    <input type="file" id="desktop_image" name="desktop_image" class="form-control border-light-subtle shadow-none" accept="image/*">
                                </div>
                                <?php if (!empty($banner['desktop_image'])): ?>
                                    <div class="mt-2 p-2 border rounded-3 bg-light d-inline-block shadow-xs">
                                        <div class="small fw-bold text-muted x-small mb-1 text-uppercase">Current Desktop View</div>
                                        <img src="../<?php echo htmlspecialchars($banner['desktop_image']); ?>" class="img-fluid rounded border shadow-sm" style="max-height: 100px;" alt="Desktop Preview">
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label for="mobile_image" class="form-label small fw-bold text-muted text-uppercase">Mobile Image (Optional)</label>
                                <div class="input-group mb-2">
                                    <input type="file" id="mobile_image" name="mobile_image" class="form-control border-light-subtle shadow-none" accept="image/*">
                                </div>
                                <?php if (!empty($banner['mobile_image'])): ?>
                                    <div class="mt-2 p-2 border rounded-3 bg-light d-inline-block shadow-xs">
                                        <div class="small fw-bold text-muted x-small mb-1 text-uppercase">Current Mobile View</div>
                                        <img src="../<?php echo htmlspecialchars($banner['mobile_image']); ?>" class="img-fluid rounded border shadow-sm" style="max-height: 100px;" alt="Mobile Preview">
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label for="link_url" class="form-label small fw-bold text-muted text-uppercase">Link URL</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light-subtle text-muted"><i class="fas fa-link"></i></span>
                                    <input type="url" id="link_url" name="link_url" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars($banner['link_url']); ?>" placeholder="https://example.com/product/123">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="position" class="form-label small fw-bold text-muted text-uppercase">Display Position</label>
                                <select id="position" name="position" class="form-select border-light-subtle shadow-none">
                                    <option value="homepage_hero" <?php echo $banner['position'] === 'homepage_hero' ? 'selected' : ''; ?>>Homepage Hero</option>
                                    <option value="category_top" <?php echo $banner['position'] === 'category_top' ? 'selected' : ''; ?>>Category Top</option>
                                    <option value="sidebar" <?php echo $banner['position'] === 'sidebar' ? 'selected' : ''; ?>>Sidebar</option>
                                    <option value="footer" <?php echo $banner['position'] === 'footer' ? 'selected' : ''; ?>>Footer</option>
                                    <option value="popup" <?php echo $banner['position'] === 'popup' ? 'selected' : ''; ?>>Popup</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <div class="card border-light-subtle bg-light p-3 rounded-3 shadow-none">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" <?php echo $banner['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label fw-bold small text-dark" for="is_active">Publish this banner</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3 mt-2">
                            <button type="submit" class="btn btn-danger px-5 py-2 fw-bold rounded-pill shadow-sm">
                                <i class="fas fa-save me-1"></i> Update Banner
                            </button>
                            <a href="banners.php" class="btn btn-light px-4 py-2 fw-bold rounded-pill text-muted border shadow-xs">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
                <div class="alert alert-danger border-0 shadow-sm rounded-4 p-4 d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3 opacity-50"></i>
                    <div>
                        <h5 class="fw-bold mb-1">Banner Not Found</h5>
                        <p class="mb-0 small text-danger-emphasis">The banner you are trying to edit does not exist or has been deleted.</p>
                        <a href="banners.php" class="btn btn-danger btn-sm mt-3 rounded-pill px-3 fw-bold shadow-sm">Return to List</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Include the shared footer template -->
    <?php include 'footer.php'; ?>

