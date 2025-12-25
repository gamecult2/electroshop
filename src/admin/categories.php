<?php
/**
 * Enhanced Category Management Admin Page
 *
 * Features:
 * - Visual hierarchy display (tree view)
 * - Inline editing with modal
 * - Subcategory creation
 * - Product count indicators
 * - Validation (prevent circular refs, orphaned products)
 * - Search/filter functionality
 */

session_start();
require_once '../includes/functions.php';
require_once '../db_connect.php';
require_once '../models/Category.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

$categoryModel = new Category();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $name_en = sanitize_input($_POST['name_en']);
                $description_en = sanitize_input($_POST['description_en'] ?? '');
                $data = [
                    'name_en' => $name_en,
                    'slug' => sanitize_input($_POST['slug']),
                    'description_en' => $description_en,
                    'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
                    'icon_class' => sanitize_input($_POST['icon_class'] ?? ''),
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                    'sort_order' => (int)($_POST['sort_order'] ?? 0)
                ];

                if ($categoryModel->create($data)) {
                    $_SESSION['message'] = 'Category created successfully';
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = 'Failed to create category';
                    $_SESSION['message_type'] = 'error';
                }
                break;
                
            case 'update':
                $id = (int)$_POST['id'];
                $name_en = sanitize_input($_POST['name_en']);
                $description_en = sanitize_input($_POST['description_en'] ?? '');
                $data = [
                    'name_en' => $name_en,
                    'slug' => sanitize_input($_POST['slug']),
                    'description_en' => $description_en,
                    'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
                    'icon_class' => sanitize_input($_POST['icon_class'] ?? ''),
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                    'sort_order' => (int)($_POST['sort_order'] ?? 0)
                ];

                // Validate hierarchy
                if (!$categoryModel->validateHierarchy($id, $data['parent_id'])) {
                    $_SESSION['message'] = 'Invalid hierarchy: circular reference detected';
                    $_SESSION['message_type'] = 'error';
                } elseif ($categoryModel->update($id, $data)) {
                    $_SESSION['message'] = 'Category updated successfully';
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = 'Failed to update category';
                    $_SESSION['message_type'] = 'error';
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                if ($categoryModel->delete($id)) {
                    $_SESSION['message'] = 'Category deleted successfully';
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = 'Cannot delete category: has subcategories or products';
                    $_SESSION['message_type'] = 'error';
                }
                break;
        }
        
        header('Location: categories.php');
        exit;
    }
}

// Handle old-style delete (for backward compatibility)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $categoryId = (int)$_GET['id'];
    if ($categoryModel->delete($categoryId)) {
        $_SESSION['message'] = 'Category deleted successfully';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Cannot delete category: has subcategories or products';
        $_SESSION['message_type'] = 'error';
    }
    header('Location: categories.php');
    exit;
}

// Set page title and heading variables for the template
$page_title = 'Manage Categories';
$page_heading = 'Category Management';

// Include the shared header template
include 'header.php';

$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['message_type'] ?? '';
unset($_SESSION['message'], $_SESSION['message_type']);


// Get filter parameters
$searchTerm = $_GET['search'] ?? '';
$filterParent = $_GET['parent_id'] ?? '';

// Get all categories with hierarchy
$allCategories = $categoryModel->getAllWithHierarchy();
$mainCategories = $categoryModel->getAllMainCategories();

// Apply filters if needed
$categories = [];
if ($searchTerm || $filterParent) {
    foreach ($allCategories as $mainCat) {
        // Check if we're filtering by parent
        if ($filterParent && $mainCat['id'] != $filterParent) {
            continue; // Skip this main category if it doesn't match the filter
        }
        
        // Check if main category matches search
        $mainMatches = empty($searchTerm) || 
                       stripos($mainCat['name_en'], $searchTerm) !== false || 
                       stripos($mainCat['slug'], $searchTerm) !== false;
        
        // Filter subcategories by search term
        $filteredSubs = [];
        if (!empty($mainCat['subcategories'])) {
            foreach ($mainCat['subcategories'] as $sub) {
                $subMatches = empty($searchTerm) || 
                             stripos($sub['name_en'], $searchTerm) !== false || 
                             stripos($sub['slug'], $searchTerm) !== false;
                
                if ($subMatches) {
                    $filteredSubs[] = $sub;
                }
            }
        }
        
        // Include main category if:
        // 1. Main category matches search, OR
        // 2. Any subcategory matches search
        if ($mainMatches || !empty($filteredSubs)) {
            $mainCat['subcategories'] = $filteredSubs;
            $categories[] = $mainCat;
        }
    }
} else {
    // No filters - show all
    $categories = $allCategories;
}
?>


            <!-- Filter Bar -->
            <form class="card border-0 shadow-sm p-3 mb-4 d-flex flex-row align-items-center gap-3 flex-wrap" method="GET" action="categories.php">
                <div class="input-group input-group-sm w-auto">
                    <span class="input-group-text bg-white border-light-subtle text-muted"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control border-light-subtle shadow-none" placeholder="Search categories..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                </div>
                
                <select name="parent_id" class="form-select form-select-sm border-light-subtle shadow-none w-auto">
                    <option value="">All Main Categories</option>
                    <?php foreach ($mainCategories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $filterParent == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name_en']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="btn btn-danger btn-sm px-4 rounded-pill fw-bold shadow-sm">Filter</button>
                <?php if ($searchTerm || $filterParent): ?>
                    <a href="categories.php" class="btn btn-light btn-sm rounded-pill px-4 fw-bold border text-muted">Clear</a>
                <?php endif; ?>
            </form>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-sitemap me-2 text-danger"></i> Category Hierarchy</h5>
                        <p class="text-muted x-small mb-0 mt-1">Manage your storefront hierarchy and organization.</p>
                    </div>
                    <button onclick="openCategoryModal('create')" class="btn btn-danger btn-sm rounded-pill px-3 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
                        <i class="fas fa-plus"></i> Add Main Category
                    </button>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="list-group list-group-flush rounded overflow-hidden">
                        <?php if (empty($categories)): ?>
                            <div class="text-center py-5 text-muted border rounded bg-light">
                                <i class="fas fa-folder-open fa-3x opacity-25 mb-3"></i><br>
                                No categories found matching your criteria.
                            </div>
                        <?php else: ?>
                            <?php foreach ($categories as $mainCat): ?>
                                <div class="list-group-item bg-light border-light-subtle py-3 px-3 d-flex justify-content-between align-items-center main-category border-start border-4 border-danger" onclick="toggleSubcategories(this, event)" style="cursor: pointer;">
                                    <div class="d-flex align-items-center gap-3 flex-grow-1">
                                        <span class="chevron text-muted" style="transition: transform 0.2s; width: 20px; display: inline-block;">
                                            <?php if (!empty($mainCat['subcategories'])): ?>
                                                <i class="fas fa-chevron-right"></i>
                                            <?php endif; ?>
                                        </span>
                                        <div class="rounded-circle bg-white shadow-xs d-flex align-items-center justify-content-center border" style="width: 36px; height: 36px; color: #dc3545;">
                                            <i class="<?php echo $mainCat['icon_class'] ?: 'fas fa-folder'; ?>"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark small"><?php echo htmlspecialchars($mainCat['name_en']); ?></div>
                                            <div class="text-muted x-small">/<?php echo $mainCat['slug']; ?></div>
                                        </div>
                                        <span class="badge bg-danger-subtle text-danger-emphasis rounded-pill px-2 py-1 small fw-bold ms-2" style="font-size: 10px;">
                                            <?php echo $mainCat['product_count']; ?> Products
                                        </span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button onclick='event.stopPropagation(); openCategoryModal("edit", <?php echo json_encode($mainCat); ?>)' 
                                                class="btn btn-white btn-sm rounded border-light-subtle shadow-xs text-primary bg-white" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick='event.stopPropagation(); openCategoryModal("createSub", <?php echo $mainCat['id']; ?>)' 
                                                class="btn btn-white btn-sm rounded border-light-subtle shadow-xs text-success bg-white" title="Add Subcategory">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                        <button onclick='event.stopPropagation(); deleteCategory(<?php echo $mainCat['id']; ?>)' 
                                                class="btn btn-white btn-sm rounded border-light-subtle shadow-xs text-danger bg-white" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <?php if (!empty($mainCat['subcategories'])): ?>
                                    <div class="subcategory-group d-none bg-white">
                                        <?php foreach ($mainCat['subcategories'] as $subCat): ?>
                                            <div class="list-group-item py-2 px-4 d-flex justify-content-between align-items-center border-light-subtle" style="margin-left: 30px; border-left: 2px dashed #dee2e6;">
                                                <div class="d-flex align-items-center gap-3 flex-grow-1">
                                                    <i class="<?php echo $subCat['icon_class'] ?: 'fas fa-arrow-right text-muted opacity-50'; ?> x-small"></i>
                                                    <div>
                                                        <div class="fw-medium text-dark small"><?php echo htmlspecialchars($subCat['name_en']); ?></div>
                                                        <div class="text-muted x-small">/<?php echo $mainCat['slug']; ?>/<?php echo $subCat['slug']; ?></div>
                                                    </div>
                                                    <span class="badge bg-light text-muted border rounded-pill px-2 py-1 small fw-bold ms-2" style="font-size: 9px;">
                                                        <?php echo $categoryModel->getProductCount($subCat['id']); ?> Products
                                                    </span>
                                                </div>
                                                <div class="btn-group shadow-xs rounded bg-white">
                                                    <button onclick='openCategoryModal("edit", <?php echo json_encode($subCat); ?>)' 
                                                            class="btn btn-white btn-sm border-light-subtle text-primary py-1 px-2" title="Edit">
                                                        <i class="fas fa-edit small"></i>
                                                    </button>
                                                    <button onclick='deleteCategory(<?php echo $subCat['id']; ?>)' 
                                                            class="btn btn-white btn-sm border-light-subtle text-danger py-1 px-2" title="Delete">
                                                        <i class="fas fa-trash small"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Form Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 py-3 px-4 bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="modalTitle"><i class="fas fa-edit me-2 text-danger"></i>Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" id="categoryForm">
                    <div class="modal-body p-4">
                        <input type="hidden" name="action" id="formAction" value="create">
                        <input type="hidden" name="id" id="categoryId">
                        
                        <div class="row g-4 mb-3">
                            <div class="col-md-6">
                                <label for="name_en" class="form-label small fw-bold text-muted text-uppercase">Category Name *</label>
                                <input type="text" class="form-control border-light-subtle shadow-none" name="name_en" id="name_en" required placeholder="e.g. Laptops & Computers">
                            </div>
                            
                            <div class="col-md-6">
                                <label for="slug" class="form-label small fw-bold text-muted text-uppercase">Slug *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light-subtle text-muted small px-2">/</span>
                                    <input type="text" class="form-control border-light-subtle shadow-none" name="slug" id="slug" required 
                                           pattern="[a-z0-9-]+" 
                                           title="Lowercase letters, numbers, and hyphens only">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="parent_id" class="form-label small fw-bold text-muted text-uppercase">Parent Category</label>
                                <select class="form-select border-light-subtle shadow-none" name="parent_id" id="parent_id">
                                    <option value="">None (Main Category)</option>
                                    <?php foreach ($mainCategories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>">
                                            <?php echo htmlspecialchars($cat['name_en']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="icon_class" class="form-label small fw-bold text-muted text-uppercase">FontAwesome Icon</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-light-subtle text-muted small"><i class="fas fa-icons"></i></span>
                                    <input type="text" class="form-control border-light-subtle shadow-none" name="icon_class" id="icon_class" 
                                           placeholder="fas fa-laptop">
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label for="description_en" class="form-label small fw-bold text-muted text-uppercase">Description</label>
                                <textarea class="form-control border-light-subtle shadow-none" name="description_en" id="description_en" rows="3" placeholder="Brief description for SEO..."></textarea>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="sort_order" class="form-label small fw-bold text-muted text-uppercase">Display Sort Order</label>
                                <input type="number" class="form-control border-light-subtle shadow-none" name="sort_order" id="sort_order" value="0">
                            </div>

                            <div class="col-md-6 d-flex align-items-end">
                                <div class="card border-light-subtle bg-light p-3 rounded-3 w-100 mb-1 shadow-none">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                                        <label class="form-check-label fw-bold small text-dark" for="is_active">Active Status</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold text-muted border shadow-xs" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let catModal;
        document.addEventListener('DOMContentLoaded', function() {
            catModal = new bootstrap.Modal(document.getElementById('categoryModal'));
        });

        function toggleSubcategories(element, event) {
            const chevron = element.querySelector('.chevron i');
            if (chevron) {
                const isRotated = chevron.style.transform === 'rotate(90deg)';
                chevron.style.transform = isRotated ? 'rotate(0deg)' : 'rotate(90deg)';
            }
            
            const group = element.nextElementSibling;
            if (group && group.classList.contains('subcategory-group')) {
                group.classList.toggle('d-none');
            }
        }

        function openCategoryModal(action, data = null) {
            const form = document.getElementById('categoryForm');
            const title = document.getElementById('modalTitle');
            
            form.reset();
            
            if (action === 'create') {
                title.innerHTML = '<i class="fas fa-plus-circle me-2 text-danger"></i>Add Main Category';
                document.getElementById('formAction').value = 'create';
                document.getElementById('parent_id').value = '';
            } else if (action === 'createSub') {
                title.innerHTML = '<i class="fas fa-plus-circle me-2 text-danger"></i>Add Subcategory';
                document.getElementById('formAction').value = 'create';
                document.getElementById('parent_id').value = data;
            } else if (action === 'edit') {
                title.innerHTML = '<i class="fas fa-edit me-2 text-danger"></i>Edit Category';
                document.getElementById('formAction').value = 'update';
                document.getElementById('categoryId').value = data.id;
                document.getElementById('name_en').value = data.name_en;
                document.getElementById('slug').value = data.slug;
                document.getElementById('description_en').value = data.description_en || '';
                document.getElementById('parent_id').value = data.parent_id || '';
                document.getElementById('icon_class').value = data.icon_class || '';
                document.getElementById('sort_order').value = data.sort_order || 0;
                document.getElementById('is_active').checked = data.is_active == 1;
            }
            
            catModal.show();
        }
        
        function deleteCategory(id) {
            if (confirm('Are you sure you want to delete this category? This will fail if it has subcategories or products.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        document.getElementById('name_en').addEventListener('input', function(e) {
            if (document.getElementById('formAction').value === 'create') {
                const slug = e.target.value
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
                document.getElementById('slug').value = slug;
            }
        });
    </script>

    <!-- Include the shared footer template -->
    <?php include 'footer.php'; ?>


