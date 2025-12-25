<?php
/**
 * Breadcrumb Component
 * 
 * Logic:
 * Expects an array $breadcrumb_items where each item is:
 * ['label' => 'Translated Label', 'url' => 'optional_link.php']
 */

if (!isset($breadcrumb_items) || !is_array($breadcrumb_items)) {
    return; // Don't render anything if no items provided
}
?>
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb mb-0">
        <?php foreach ($breadcrumb_items as $index => $item): ?>
            <?php if (!empty($item['url'])): ?>
                <li class="breadcrumb-item"><a href="<?php echo htmlspecialchars($item['url']); ?>" class="text-decoration-none text-danger fw-bold"><?php echo htmlspecialchars($item['label']); ?></a></li>
            <?php else: ?>
                <li class="breadcrumb-item active text-muted" aria-current="page"><?php echo htmlspecialchars($item['label']); ?></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>
</nav>
