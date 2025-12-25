<!-- 
    Hierarchical Navigation Component
    
    Features:
    - Two-tier category display (main → subcategories)
    - Mega menu on desktop (hover to reveal subcategories)
    - Collapsible accordion on mobile
    - Product counts per category
    - Smooth animations
-->

<?php
require_once __DIR__ . '/../models/Category.php';

$categoryModel = new Category();
$navCategories = $categoryModel->getAllWithHierarchy();
$lang = 'en';
$nameField = 'name_en';
?>

<nav class="bg-white border-bottom shadow-sm overflow-hidden d-none d-lg-block">
    <div class="container-xxl">
        <div class="d-flex justify-content-between align-items-center">
            <?php foreach ($navCategories as $mainCat): ?>
                <div class="dropdown flex-grow-1 text-center border-start border-light-subtle position-static">
                    <a href="category.php?slug=<?php echo $mainCat['slug']; ?>" 
                       class="d-block py-3 px-2 text-decoration-none text-dark fw-bold border-bottom border-3 border-transparent hover-danger transition-all fs-6 dropdown-toggle-no-caret"
                       style="border-bottom-color: transparent;"
                       data-bs-toggle="dropdown"
                       aria-expanded="false">
                        <?php echo htmlspecialchars($mainCat[$nameField]); ?>
                        <?php if (!empty($mainCat['subcategories'])): ?>
                            <i class="fas fa-chevron-down ms-1 small opacity-50"></i>
                        <?php endif; ?>
                    </a>
                    
                    <?php if (!empty($mainCat['subcategories'])): ?>
                        <div class="dropdown-menu w-100 border-0 shadow-lg rounded-0 mt-0 py-4 px-5">
                            <div class="container-xxl px-0">
                                <div class="row g-4">
                                    <div class="col-12">
                                        <h5 class="fw-bold text-danger mb-4 border-bottom pb-2">
                                            <i class="fas fa-list me-2"></i><?php echo htmlspecialchars($mainCat[$nameField]); ?> Subcategories
                                        </h5>
                                        <div class="row row-cols-md-4 row-cols-lg-5 g-3">
                                            <?php foreach ($mainCat['subcategories'] as $subCat): ?>
                                                <div class="col">
                                                    <a href="category.php?slug=<?php echo $subCat['slug']; ?>" 
                                                       class="d-block py-2 px-3 text-decoration-none text-muted transition-all rounded hover-bg-light fs-6">
                                                        <i class="fas fa-angle-right me-2 opacity-50"></i>
                                                        <?php echo htmlspecialchars($subCat[$nameField]); ?>
                                                        <span class="badge bg-light text-muted fw-normal ms-1 rounded-pill" style="font-size: 10px;">
                                                            <?php echo $categoryModel->getProductCount($subCat['id']); ?>
                                                        </span>
                                                    </a>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</nav>

<!-- Mobile Category Menu (Accordion) -->
<div class="d-lg-none bg-white border-bottom shadow-sm">
    <div class="accordion accordion-flush" id="mobileCategoryAccordion">
        <?php foreach ($navCategories as $index => $mainCat): ?>
            <div class="accordion-item py-1">
                <h2 class="accordion-header" id="heading-<?php echo $index; ?>">
                    <button class="accordion-button collapsed py-3 fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $index; ?>" aria-expanded="false" aria-controls="collapse-<?php echo $index; ?>">
                        <?php echo htmlspecialchars($mainCat[$nameField]); ?>
                    </button>
                    <a href="category.php?slug=<?php echo $mainCat['slug']; ?>" class="position-absolute end-0 top-0 mt-3 me-5 px-3 py-1 bg-light rounded-pill small text-danger fw-bold text-decoration-none z-3" style="font-size: 11px;">View All</a>
                </h2>
                <div id="collapse-<?php echo $index; ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?php echo $index; ?>" data-bs-parent="#mobileCategoryAccordion">
                    <div class="accordion-body p-0">
                        <div class="list-group list-group-flush bg-light">
                            <?php foreach ($mainCat['subcategories'] as $subCat): ?>
                                <a href="category.php?slug=<?php echo $subCat['slug']; ?>" class="list-group-item list-group-item-action bg-transparent py-3 border-0 ps-4">
                                    <i class="fas fa-angle-right me-2 text-danger"></i>
                                    <?php echo htmlspecialchars($subCat[$nameField]); ?>
                                    <span class="float-end text-muted small">(<?php echo $categoryModel->getProductCount($subCat['id']); ?>)</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// Mega menu hover trigger for desktop
if (window.innerWidth >= 992) {
    document.querySelectorAll('.dropdown').forEach(function(dd) {
        dd.addEventListener('mouseenter', function() {
            let toggle = this.querySelector('.dropdown-toggle-no-caret');
            let menu = this.querySelector('.dropdown-menu');
            if (toggle && menu) {
                bootstrap.Dropdown.getOrCreateInstance(toggle).show();
            }
        });
        dd.addEventListener('mouseleave', function() {
            let toggle = this.querySelector('.dropdown-toggle-no-caret');
            let menu = this.querySelector('.dropdown-menu');
            if (toggle && menu) {
                bootstrap.Dropdown.getOrCreateInstance(toggle).hide();
            }
        });
    });
}
</script>
