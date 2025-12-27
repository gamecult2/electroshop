<?php
// admin/banners.php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$message = '';
$messageType = '';

// Requires 'banners' table
// CREATE TABLE banners (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     title VARCHAR(255) NOT NULL,
//     image_url VARCHAR(255) NOT NULL,
//     link_url VARCHAR(255),
//     position VARCHAR(100), -- e.g., 'homepage_slider', 'sidebar'
//     is_active TINYINT(1) DEFAULT 1,
//     start_date DATETIME,
//     end_date DATETIME
// );

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_banner'])) {
    $title = sanitize_input($_POST['title']);
    $link_url = filter_var($_POST['link_url'], FILTER_SANITIZE_URL);
    $position = sanitize_input($_POST['position']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $desktop_image = '';
    $mobile_image = '';

    // Handle desktop image upload
    if (isset($_FILES['desktop_image']) && $_FILES['desktop_image']['error'] === UPLOAD_ERR_OK) {
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
        $uploadDir = '../uploads/images/banners/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $extension = strtolower(pathinfo($_FILES['mobile_image']['name'], PATHINFO_EXTENSION));
        $filename = create_slug($title) . '-mobile-' . substr(md5(uniqid()), 0, 8) . '.' . $extension;
        
        $targetFile = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['mobile_image']['tmp_name'], $targetFile)) {
            $mobile_image = 'uploads/images/banners/' . $filename;
        }
    }

    if (!empty($title) && !empty($desktop_image)) {
        $stmt = $pdo->prepare("INSERT INTO banners (title, desktop_image, mobile_image, link_url, position, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$title, $desktop_image, $mobile_image, $link_url, $position, $is_active])) {
            $message = 'Banner uploaded successfully.';
            $messageType = 'success';
        } else {
            $message = 'Failed to upload banner.';
            $messageType = 'error';
        }
    } else {
        $message = 'Title and desktop image are required.';
        $messageType = 'error';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = (int)$_POST['id'];
    $title = sanitize_input($_POST['title']);
    $link_url = filter_var($_POST['link_url'], FILTER_SANITIZE_URL);
    $position = sanitize_input($_POST['position']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $desktop_image = sanitize_input($_POST['desktop_image']);
    $mobile_image = sanitize_input($_POST['mobile_image']);

    // Handle desktop image upload if new file provided
    if (isset($_FILES['desktop_image']) && $_FILES['desktop_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/images/banners/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $extension = strtolower(pathinfo($_FILES['desktop_image']['name'], PATHINFO_EXTENSION));
        $filename = create_slug($title) . '-desktop-' . substr(md5(uniqid()), 0, 8) . '.' . $extension;
        
        $targetFile = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['desktop_image']['tmp_name'], $targetFile)) {
            $desktop_image = 'uploads/images/banners/' . $filename;
        }
    }

    // Handle mobile image upload if new file provided
    if (isset($_FILES['mobile_image']) && $_FILES['mobile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/images/banners/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $extension = strtolower(pathinfo($_FILES['mobile_image']['name'], PATHINFO_EXTENSION));
        $filename = create_slug($title) . '-mobile-' . substr(md5(uniqid()), 0, 8) . '.' . $extension;
        
        $targetFile = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['mobile_image']['tmp_name'], $targetFile)) {
            $mobile_image = 'uploads/images/banners/' . $filename;
        }
    }

    if (!empty($title)) {
        $stmt = $pdo->prepare("UPDATE banners SET title = ?, desktop_image = ?, mobile_image = ?, link_url = ?, position = ?, is_active = ? WHERE id = ?");
        if ($stmt->execute([$title, $desktop_image, $mobile_image, $link_url, $position, $is_active, $id])) {
            $message = 'Banner updated successfully.';
            $messageType = 'success';
        } else {
            $message = 'Failed to update banner.';
            $messageType = 'error';
        }
    } else {
        $message = 'Title is required.';
        $messageType = 'error';
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $bannerId = (int)$_GET['id'];
    // First, get the image URL to delete the file
    $stmt = $pdo->prepare("SELECT desktop_image, mobile_image FROM banners WHERE id = ?");
    $stmt->execute([$bannerId]);
    $banner = $stmt->fetch();

    if ($banner) {
        $stmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
        if ($stmt->execute([$bannerId])) {
            if (file_exists('../' . $banner['desktop_image']) && !empty($banner['desktop_image'])) {
                unlink('../' . $banner['desktop_image']);
            }
            if (file_exists('../' . $banner['mobile_image']) && !empty($banner['mobile_image'])) {
                unlink('../' . $banner['mobile_image']);
            }
            $message = 'Banner deleted successfully.';
            $messageType = 'success';
        } else {
            $message = 'Failed to delete banner.';
            $messageType = 'error';
        }
    }
}

$banners = $pdo->query("SELECT * FROM banners ORDER BY position, id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Set page title and heading variables for the template
$page_title = 'Manage Banners';
$page_heading = 'Manage Banners';

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

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-0">
                    <h5 class="mb-0 fw-bold px-2"><i class="fas fa-image me-2 text-danger"></i> Upload New Banner</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="banners.php" enctype="multipart/form-data">
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="title" class="form-label small fw-bold text-muted text-uppercase">Banner Title *</label>
                                <input type="text" id="title" name="title" class="form-control border-light-subtle shadow-none" placeholder="Enter banner title" required>
                            </div>
                            <div class="col-md-6">
                                <label for="position" class="form-label small fw-bold text-muted text-uppercase">Display Position</label>
                                <select id="position" name="position" class="form-select border-light-subtle shadow-none">
                                    <option value="homepage_hero">Homepage Hero</option>
                                    <option value="category_top">Category Top</option>
                                    <option value="sidebar">Sidebar</option>
                                    <option value="footer">Footer</option>
                                    <option value="popup">Popup</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="desktop_image" class="form-label small fw-bold text-muted text-uppercase">Desktop Image *</label>
                                <input type="file" id="desktop_image" name="desktop_image" class="form-control border-light-subtle shadow-none" accept="image/*" required>
                                <div class="form-text small">Recommended: 1920x600px</div>
                            </div>
                            <div class="col-md-6">
                                <label for="mobile_image" class="form-label small fw-bold text-muted text-uppercase">Mobile Image</label>
                                <input type="file" id="mobile_image" name="mobile_image" class="form-control border-light-subtle shadow-none" accept="image/*">
                                <div class="form-text small">Recommended: 800x400px (Optional)</div>
                            </div>
                            <div class="col-md-8">
                                <label for="link_url" class="form-label small fw-bold text-muted text-uppercase">Destination Link (URL)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light-subtle text-muted"><i class="fas fa-link"></i></span>
                                    <input type="url" id="link_url" name="link_url" class="form-control border-light-subtle shadow-none" placeholder="https://qwenshop.com/product/123">
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="card border-light-subtle bg-light p-3 rounded-3 w-100 mb-1">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active_add" checked>
                                        <label class="form-check-label fw-bold small text-dark" for="is_active_add">Active Banner</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="add_banner" class="btn btn-danger px-5 py-2 fw-bold rounded-pill shadow-sm">
                            <i class="fas fa-upload me-2"></i> Upload Banner
                        </button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm overflow-hidden mb-4">
                <div class="card-header bg-white py-3 border-0">
                    <h2 class="h5 fw-bold mb-0 text-dark"><i class="fas fa-images me-2 text-danger"></i> Active Banners <span class="badge bg-light text-muted border ms-2 small fw-normal"><?php echo count($banners); ?> Total</span></h2>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3 border-0">Preview</th>
                                <th class="border-0">Banner Details</th>
                                <th class="border-0">Position</th>
                                <th class="border-0">Target Link</th>
                                <th class="border-0">Status</th>
                                <th class="border-0 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($banners)): ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted"><i class="fas fa-images fa-3x opacity-25 mb-3"></i><br>No banners found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($banners as $banner): ?>
                                    <tr>
                                        <td class="px-3">
                                            <div class="position-relative d-inline-block">
                                                <img src="../<?php echo htmlspecialchars($banner['desktop_image'] ?? $banner['image_url'] ?? 'img/placeholder.jpg'); ?>" alt="Banner" class="rounded border shadow-xs object-fit-cover" style="width: 120px; height: 50px;">
                                                <?php if (!empty($banner['mobile_image'])): ?>
                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-info border border-white" title="Has mobile version">M</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($banner['title']); ?></div>
                                            <div class="x-small text-muted">ID: #<?php echo $banner['id']; ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border small"><?php echo htmlspecialchars(str_replace('_', ' ', $banner['position'])); ?></span>
                                        </td>
                                        <td>
                                            <?php if ($banner['link_url']): ?>
                                                <a href="<?php echo htmlspecialchars($banner['link_url']); ?>" target="_blank" class="text-primary small text-truncate d-inline-block" style="max-width: 150px;">
                                                    <i class="fas fa-external-link-alt me-1 x-small"></i> <?php echo htmlspecialchars($banner['link_url']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small">No link</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill px-3 py-2 fw-bold <?php echo $banner['is_active'] ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis'; ?>" style="font-size: 10px;">
                                                <?php echo $banner['is_active'] ? 'ACTIVE' : 'INACTIVE'; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group shadow-sm rounded">
                                                <button onclick='openEditModal(<?php echo json_encode($banner); ?>)' class="btn btn-white btn-sm border-light-subtle text-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="banners.php?action=delete&id=<?php echo $banner['id']; ?>" class="btn btn-white btn-sm border-light-subtle text-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this banner?');">
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

    <!-- Edit Banner Modal -->
    <div class="modal fade" id="editBannerModal" tabindex="-1" aria-labelledby="editBannerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 py-3 px-4 bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="editBannerModalLabel"><i class="fas fa-edit me-2 text-danger"></i>Edit Banner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm" method="POST" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="editId">
                        
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="editTitle" class="form-label small fw-bold text-muted text-uppercase">Banner Title *</label>
                                <input type="text" id="editTitle" name="title" class="form-control border-light-subtle shadow-none" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editPosition" class="form-label small fw-bold text-muted text-uppercase">Display Position</label>
                                <select id="editPosition" name="position" class="form-select border-light-subtle shadow-none">
                                    <option value="homepage_hero">Homepage Hero</option>
                                    <option value="category_top">Category Top</option>
                                    <option value="sidebar">Sidebar</option>
                                    <option value="footer">Footer</option>
                                    <option value="popup">Popup</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="editLinkUrl" class="form-label small fw-bold text-muted text-uppercase">Destination Link (URL)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light-subtle text-muted"><i class="fas fa-link"></i></span>
                                    <input type="url" id="editLinkUrl" name="link_url" class="form-control border-light-subtle shadow-none">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Desktop Image</label>
                                <div id="currentDesktopImage" class="mb-2"></div>
                                <input type="file" id="editDesktopImage" name="desktop_image" class="form-control border-light-subtle shadow-none" accept="image/*">
                                <input type="hidden" id="editDesktopImageHidden" name="desktop_image">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Mobile Image</label>
                                <div id="currentMobileImage" class="mb-2"></div>
                                <input type="file" id="editMobileImage" name="mobile_image" class="form-control border-light-subtle shadow-none" accept="image/*">
                                <input type="hidden" id="editMobileImageHidden" name="mobile_image">
                            </div>
                            <div class="col-12">
                                <div class="card border-light-subtle bg-light p-3 rounded-3 shadow-none">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="editIsActive" value="1">
                                        <label class="form-check-label fw-bold small text-dark" for="editIsActive">Active Banner</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold text-muted border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    let editBannerModal;
    document.addEventListener('DOMContentLoaded', function() {
        editBannerModal = new bootstrap.Modal(document.getElementById('editBannerModal'));
    });

    function openEditModal(banner) {
        document.getElementById('editId').value = banner.id;
        document.getElementById('editTitle').value = banner.title;
        document.getElementById('editLinkUrl').value = banner.link_url || '';
        document.getElementById('editPosition').value = banner.position || 'homepage_hero';
        document.getElementById('editDesktopImageHidden').value = banner.desktop_image || banner.image_url || '';
        document.getElementById('editMobileImageHidden').value = banner.mobile_image || '';
        document.getElementById('editIsActive').checked = banner.is_active == 1;
        
        // Show current images
        const currentDesktopDiv = document.getElementById('currentDesktopImage');
        const currentMobileDiv = document.getElementById('currentMobileImage');
        
        currentDesktopDiv.innerHTML = banner.desktop_image || banner.image_url ? 
            `<div class="position-relative d-inline-block">
                <img src="../${banner.desktop_image || banner.image_url}" alt="Current Desktop" class="rounded border shadow-xs" style="width: 100%; height: 80px; object-fit: cover;">
                <span class="position-absolute top-0 start-0 badge rounded-pill bg-dark opacity-75 m-1">Desktop</span>
             </div>` : '';
        
        currentMobileDiv.innerHTML = banner.mobile_image ? 
            `<div class="position-relative d-inline-block">
                <img src="../${banner.mobile_image}" alt="Current Mobile" class="rounded border shadow-xs" style="width: 100%; height: 80px; object-fit: cover;">
                <span class="position-absolute top-0 start-0 badge rounded-pill bg-dark opacity-75 m-1">Mobile</span>
             </div>` : '<div class="small text-muted py-4 text-center border rounded bg-light">No mobile version</div>';
        
        editBannerModal.show();
    }
    </script>

    <!-- Include the shared footer template -->
    <?php include 'footer.php'; ?>


