<?php
// admin/edit_category.php
session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';
require_once '../models/Category.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$categoryModel = new Category();
$message = '';
$messageType = '';

$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$category = $categoryModel->getById($categoryId);

if (!$category) {
    $_SESSION['message'] = 'Category not found.';
    $_SESSION['message_type'] = 'error';
    header('Location: categories.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name_en = sanitize_input($_POST['name_en']);
    $description_en = sanitize_input($_POST['description_en'] ?? '');
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
    $slug = sanitize_input($_POST['slug'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    if (empty($slug)) {
        $slug = create_slug($name_en); // Assuming create_slug function exists or will be created
    }

    if (!empty($name_en)) {
        $updateData = [
            'name_en' => $name_en,
            'description_en' => $description_en,
            'parent_id' => $parent_id,
            'slug' => $slug,
            'is_active' => $is_active,
            'sort_order' => $sort_order
        ];

        if ($categoryModel->update($categoryId, $updateData)) {
            $_SESSION['message'] = 'Category updated successfully.';
            $_SESSION['message_type'] = 'success';
            header('Location: categories.php');
            exit;
        } else {
            $message = 'Failed to update category.';
            $messageType = 'error';
        }
    } else {
        $message = 'Category name is required.';
        $messageType = 'error';
    }
}

$mainCategories = $categoryModel->getAllMainCategories(); // Using the newly added method

// Set page title and heading variables for the template
$page_title = 'Edit Category';
$page_heading = 'Edit Category';

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
                    <h5 class="mb-0 fw-bold px-2"><i class="fas fa-edit me-2 text-danger"></i> Edit Category</h5>
                    <a href="categories.php" class="btn btn-light btn-sm text-secondary rounded-pill px-3 fw-bold">
                        <i class="fas fa-arrow-left me-1"></i> Back to Categories
                    </a>
                </div>
                <div class="card-body p-4 pt-0">
                    <form method="POST" action="edit_category.php?id=<?php echo $categoryId; ?>">
                        <div class="row g-4 mb-4">
                            <div class="col-md-12">
                                <label for="name_en" class="form-label small fw-bold text-muted text-uppercase">Category Name *</label>
                                <input type="text" id="name_en" name="name_en" class="form-control border-light-subtle shadow-none fw-bold" value="<?php echo htmlspecialchars($category['name_en']); ?>" required>
                            </div>

                            <div class="col-md-12">
                                <label for="description_en" class="form-label small fw-bold text-muted text-uppercase">Description</label>
                                <textarea id="description_en" name="description_en" class="form-control border-light-subtle shadow-none" rows="3"><?php echo htmlspecialchars($category['description_en']); ?></textarea>
                            </div>

                            <div class="col-md-6">
                                <label for="parent_id" class="form-label small fw-bold text-muted text-uppercase">Parent Category</label>
                                <select id="parent_id" name="parent_id" class="form-select border-light-subtle shadow-none">
                                    <option value="">None (Main Category)</option>
                                    <?php foreach ($mainCategories as $mainCat): ?>
                                        <?php if ($mainCat['id'] != $categoryId): ?>
                                            <option value="<?php echo $mainCat['id']; ?>" <?php echo ($category['parent_id'] == $mainCat['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($mainCat['name_en']); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="slug" class="form-label small fw-bold text-muted text-uppercase">Slug URL</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light-subtle text-muted"><i class="fas fa-link"></i></span>
                                    <input type="text" id="slug" name="slug" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars($category['slug'] ?? ''); ?>" placeholder="category-slug">
                                </div>
                                <div class="form-text x-small text-muted mt-1 italic">Leave empty to auto-generate from name</div>
                            </div>

                            <div class="col-md-6">
                                <label for="sort_order" class="form-label small fw-bold text-muted text-uppercase">Sort Order</label>
                                <input type="number" id="sort_order" name="sort_order" class="form-control border-light-subtle shadow-none" value="<?php echo htmlspecialchars($category['sort_order'] ?? '0'); ?>">
                            </div>

                            <div class="col-12">
                                <div class="card border-light-subtle bg-light p-3 rounded-3 shadow-none">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?php echo $category['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label fw-bold small text-dark" for="is_active">Active Status (Visible in navigation)</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <button type="submit" class="btn btn-danger px-5 py-2 fw-bold rounded-pill shadow-sm">
                                <i class="fas fa-save me-1"></i> Update Category
                            </button>
                            <a href="categories.php" class="btn btn-light px-4 py-2 fw-bold rounded-pill text-muted border shadow-xs">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Include the shared footer template -->
    <?php include 'footer.php'; ?>


