<?php
// faq.php - Frequently Asked Questions page

require_once 'includes/header.php';
require_once 'models/Faq.php';

$faqModel = new Faq();

// Get FAQs by category
$categories = $faqModel->getCategories();
$faqs = $faqModel->getAll();

// Get specific category if requested
$categoryId = $_GET['category'] ?? null;
if ($categoryId) {
    $faqs = $faqModel->getByCategory($categoryId);
}
?>

<div class="container-xxl pb-4">
    <?php 
    $breadcrumb_items = [
        ['label' => t('home'), 'url' => 'index.php'],
        ['label' => 'FAQ']
    ];
    include 'includes/breadcrumb.php';
    ?>
    
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
        <div class="card-body text-center py-5">
            <h1 class="fw-bold text-dark mb-3"><?php echo t('frequently_asked_questions') ?? 'Frequently Asked Questions'; ?></h1>
            <p class="text-muted fs-5 mx-auto mb-0" style="max-width: 700px;">
                Find answers to common questions about our products, services, and policies. If you can't find what you're looking for, feel free to contact us.
            </p>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Sidebar: Categories -->
        <aside class="col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 100px;">
                <div class="card-header bg-white py-3 border-bottom">
                    <h2 class="h6 fw-bold mb-0 text-dark">
                        <i class="fas fa-th-list text-danger me-2"></i> <?php echo t('faq_categories'); ?>
                    </h2>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush rounded-bottom-4">
                        <a href="faq.php" class="list-group-item list-group-item-action border-0 px-4 py-3 <?php echo !$categoryId ? 'active bg-danger' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-layer-group me-2"></i> <?php echo t('all_faq'); ?></span>
                                <?php if (!$categoryId): ?>
                                    <i class="fas fa-chevron-right small opacity-50"></i>
                                <?php endif; ?>
                            </div>
                        </a>
                        
                        <?php foreach ($categories as $category): ?>
                            <a href="faq.php?category=<?php echo $category['id']; ?>" 
                               class="list-group-item list-group-item-action border-0 px-4 py-3 <?php echo $categoryId == $category['id'] ? 'active bg-danger' : ''; ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-folder me-2"></i> <?php echo htmlspecialchars($category['name_en']); ?></span>
                                    <?php if ($category['faq_count'] > 0): ?>
                                        <span class="badge <?php echo $categoryId == $category['id'] ? 'bg-white text-danger' : 'bg-danger-subtle text-danger'; ?> rounded-pill small">
                                            <?php echo $category['faq_count']; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Main: Questions -->
        <div class="col-lg-9">
            <div class="mb-4 d-flex align-items-center justify-content-between">
                <h2 class="h4 fw-bold text-dark mb-0">
                    <?php if ($categoryId): ?>
                        <?php 
                        $selectedCategory = array_filter($categories, function($cat) use ($categoryId) {
                            return $cat['id'] == $categoryId;
                        });
                        $selectedCategory = reset($selectedCategory);
                        echo htmlspecialchars($selectedCategory['name_en']);
                        ?>
                    <?php else: ?>
                        <?php echo t('all_questions') ?? 'All Questions'; ?>
                    <?php endif; ?>
                </h2>
                <span class="text-muted small"><?php echo count($faqs); ?> <?php echo t('results_found') ?? 'results found'; ?></span>
            </div>
            
            <?php if (count($faqs) > 0): ?>
                <div class="accordion accordion-flush bg-white rounded-4 shadow-sm overflow-hidden" id="faqAccordion">
                    <?php foreach ($faqs as $index => $faq): ?>
                        <div class="accordion-item border-0 <?php echo $index < count($faqs) - 1 ? 'border-bottom' : ''; ?>">
                            <h2 class="accordion-header" id="heading<?php echo $faq['id']; ?>">
                                <button class="accordion-button collapsed fw-bold text-dark py-4 px-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $faq['id']; ?>" aria-expanded="false" aria-controls="collapse<?php echo $faq['id']; ?>">
                                    <?php echo htmlspecialchars($faq['question_en']); ?>
                                </button>
                            </h2>
                            <div id="collapse<?php echo $faq['id']; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $faq['id']; ?>" data-bs-parent="#faqAccordion">
                                <div class="accordion-body px-4 py-4 text-muted border-top bg-light-subtle">
                                    <?php echo nl2br(htmlspecialchars($faq['answer_en'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card border-0 shadow-sm rounded-4 py-5 text-center">
                    <div class="card-body py-5">
                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4" style="width: 80px; height: 80px;">
                            <i class="fas fa-question-circle text-muted opacity-50 fs-2"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-2"><?php echo t('no_faqs_available'); ?></h3>
                        <p class="text-muted mb-0"><?php echo t('no_faqs_description'); ?></p>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="card border-0 shadow-sm rounded-4 mt-5 bg-dark text-white overflow-hidden">
                <div class="card-body p-5">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h3 class="fw-bold mb-2">Still have questions?</h3>
                            <p class="text-white-50 mb-0 mb-lg-0">If you can't find a solution in our FAQs, you can always contact us. We'll respond as soon as possible.</p>
                        </div>
                        <div class="col-lg-4 text-lg-end">
                            <a href="contact.php" class="btn btn-light btn-lg rounded-pill px-4 fw-bold">
                                <?php echo t('contact_us'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
