<?php
// admin/brands.php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';
require_once '../models/Brand.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$brandModel = new Brand();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_brand'])) {
        $name = sanitize_input($_POST['name']);
        $logo_url = null;

        if (!empty($name)) {
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/images/brands/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
                $filename = create_slug($name) . '-' . substr(md5(uniqid()), 0, 8) . '.' . $extension;
                $targetFile = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetFile)) {
                    $logo_url = 'uploads/images/brands/' . $filename;
                }
            }

            if ($brandModel->create(['name' => $name, 'logo_url' => $logo_url])) {
                $message = 'Brand added successfully.';
                $messageType = 'success';
            } else {
                $message = 'Failed to add brand.';
                $messageType = 'error';
            }
        } else {
            $message = 'Brand name is required.';
            $messageType = 'error';
        }
    } elseif (isset($_POST['update_brand'])) {
        $id = (int)$_POST['brand_id'];
        $name = sanitize_input($_POST['name']);
        $logo_url = null;

        if (!empty($name)) {
            // Handle logo upload for updates
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/images/brands/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
                $filename = create_slug($name) . '-' . substr(md5(uniqid()), 0, 8) . '.' . $extension;
                $targetFile = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetFile)) {
                    $logo_url = 'uploads/images/brands/' . $filename;
                }
            }

            $updateData = ['name' => $name];
            if ($logo_url) {
                $updateData['logo_url'] = $logo_url;
            }

            if ($brandModel->update($id, $updateData)) {
                $message = 'Brand updated successfully.';
                $messageType = 'success';
            } else {
                $message = 'Failed to update brand.';
                $messageType = 'error';
            }
        } else {
            $message = 'Brand name is required.';
            $messageType = 'error';
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $brandId = (int)$_GET['id'];
    if ($brandModel->delete($brandId)) {
        $message = 'Brand deleted successfully.';
        $messageType = 'success';
    } else {
        $message = 'Failed to delete brand. It might be linked to products.';
        $messageType = 'error';
    }
}

$brands = $brandModel->getAll();

// Set page title and heading variables for the template
$page_title = 'Manage Brands';
$page_heading = 'Manage Brands';

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

            <div class="row g-4 mb-4">
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 border-0">
                            <h5 class="mb-0 fw-bold px-2"><i class="fas fa-plus-circle me-2 text-danger"></i> Add New Brand</h5>
                        </div>
                        <div class="card-body p-4 pt-0">
                            <form method="POST" action="brands.php" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="name" class="form-label small fw-bold text-muted text-uppercase">Brand Name *</label>
                                    <input type="text" id="name" name="name" class="form-control border-light-subtle shadow-none" placeholder="e.g., Samsung, Apple" required>
                                </div>
                                <div class="mb-4">
                                    <label for="logo" class="form-label small fw-bold text-muted text-uppercase">Brand Logo</label>
                                    <input type="file" id="logo" name="logo" class="form-control border-light-subtle shadow-none" accept="image/*">
                                    <div class="form-text small">Transparent PNG recommended.</div>
                                </div>
                                <button type="submit" name="add_brand" class="btn btn-danger w-100 py-2 fw-bold rounded-pill shadow-sm">
                                    <i class="fas fa-check-circle me-1"></i> Create Brand
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm overflow-hidden mb-4">
                        <div class="card-header bg-white py-3 border-0">
                            <h2 class="h5 fw-bold mb-0 text-dark"><i class="fas fa-tags me-2 text-danger"></i> Brand List <span class="badge bg-light text-muted border ms-2 small fw-normal"><?php echo count($brands); ?> Total</span></h2>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-3 border-0">ID</th>
                                        <th class="border-0">Logo</th>
                                        <th class="border-0">Brand Name</th>
                                        <th class="border-0">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($brands)): ?>
                                        <tr><td colspan="4" class="text-center py-5 text-muted"><i class="fas fa-tag fa-3x opacity-25 mb-3"></i><br>No brands found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($brands as $brand): ?>
                                            <tr>
                                                <td class="px-3"><span class="text-muted small">#<?php echo $brand['id']; ?></span></td>
                                                <td>
                                                    <div class="bg-light rounded p-2 d-inline-block shadow-xs">
                                                        <?php if ($brand['logo_url']): ?>
                                                            <img src="../<?php echo htmlspecialchars($brand['logo_url']); ?>" alt="Brand Logo" class="object-fit-contain" style="width: 50px; height: 30px;">
                                                        <?php else: ?>
                                                            <div class="text-muted small" style="width: 50px; height: 30px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-image"></i></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td><div class="fw-bold text-dark"><?php echo htmlspecialchars($brand['name']); ?></div></td>
                                                <td>
                                                    <div class="btn-group shadow-sm rounded">
                                                        <button onclick='openBrandEditModal(<?php echo json_encode($brand); ?>)' class="btn btn-white btn-sm border-light-subtle text-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <a href="brands.php?action=delete&id=<?php echo $brand['id']; ?>" class="btn btn-white btn-sm border-light-subtle text-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this brand? This might fail if products are linked.');">
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

    <!-- Brand Edit Modal -->
    <div class="modal fade" id="brandModal" tabindex="-1" aria-labelledby="brandModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 py-3 px-4 bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="brandModalLabel"><i class="fas fa-edit me-2 text-danger"></i>Edit Brand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="brandForm">
                    <div class="modal-body p-4">
                        <input type="hidden" name="update_brand" value="1">
                        <input type="hidden" name="brand_id" id="brandId">
                        
                        <div class="mb-3">
                            <label for="edit_name" class="form-label small fw-bold text-muted text-uppercase">Brand Name *</label>
                            <input type="text" id="edit_name" name="name" class="form-control border-light-subtle shadow-none" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_logo" class="form-label small fw-bold text-muted text-uppercase">Brand Logo</label>
                            <div id="currentLogoPreview" class="mb-2 p-2 bg-light rounded text-center d-none"></div>
                            <input type="file" id="edit_logo" name="logo" class="form-control border-light-subtle shadow-none" accept="image/*">
                            <div class="form-text small">Leave empty to keep current logo</div>
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
        let brandModal;
        document.addEventListener('DOMContentLoaded', function() {
            brandModal = new bootstrap.Modal(document.getElementById('brandModal'));
        });

        function openBrandEditModal(brand) {
            document.getElementById('brandId').value = brand.id;
            document.getElementById('edit_name').value = brand.name;
            
            const currentLogoDiv = document.getElementById('currentLogoPreview');
            if (brand.logo_url) {
                currentLogoDiv.innerHTML = `<img src="../${brand.logo_url}" class="object-fit-contain" style="max-height: 40px; max-width: 100%;">`;
                currentLogoDiv.classList.remove('d-none');
            } else {
                currentLogoDiv.classList.add('d-none');
            }
            
            brandModal.show();
        }
    </script>

    <!-- Include the shared footer template -->
    <?php include 'footer.php'; ?>


