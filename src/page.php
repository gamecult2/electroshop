<?php
require_once 'includes/init.php';
$slug = strtolower(trim($_GET['slug'] ?? ''));
$allowedSlugs = ['returns', 'shipping', 'careers', 'terms', 'privacy', 'sitemap'];

if (!in_array($slug, $allowedSlugs, true)) {
    http_response_code(404);
    require '404.php';
    exit;
}

require_once 'models/Page.php';
$pageModel = new Page();
$page = $pageModel->getBySlug($slug);

$fallbacks = [
    'returns' => ['Returns & Refunds', 'Need to return an item? Contact our support team with your order number and the reason for the return. We will confirm eligibility, the return address, and the next steps before you send anything back.'],
    'shipping' => ['Shipping Information', 'Delivery times and fees depend on your wilaya, courier availability, and the products in your order. The final shipping cost is shown during checkout. Contact support if you need help with an existing delivery.'],
    'careers' => ['Careers', 'We are always interested in meeting people who care about electronics, customer service, and thoughtful online retail. Send a short introduction through our contact page to start a conversation.'],
    'terms' => ['Terms & Conditions', 'These terms describe the general use of this store. Product availability, prices, delivery estimates, and payment confirmation are finalized when an order is accepted. Contact us before ordering if you need clarification about a product or policy.'],
    'privacy' => ['Privacy Policy', 'We use the information you provide to manage your account, process orders, deliver purchases, provide support, and improve the store. Contact us to ask about your personal information or request an update or deletion where applicable.'],
    'sitemap' => ['Sitemap', ''],
];

$title = $page ? localized_field($page, 'title', null, $fallbacks[$slug][0]) : $fallbacks[$slug][0];
$content = $page ? localized_field($page, 'content', null, $fallbacks[$slug][1]) : $fallbacks[$slug][1];
$page_title = $title;
require_once 'includes/header.php';
?>
<div class="container-xxl pb-5">
    <?php
    $breadcrumb_items = [
        ['label' => t('home'), 'url' => 'index.php'],
        ['label' => $title],
    ];
    include 'includes/breadcrumb.php';
    ?>
    <article class="card border-0 shadow-sm rounded-4 overflow-hidden mx-auto" style="max-width: 900px;">
        <div class="card-body p-4 p-md-5">
            <h1 class="app-page-title fw-bold mb-4"><?php echo htmlspecialchars($title); ?></h1>
            <?php if ($slug === 'sitemap'): ?>
                <nav aria-label="Site map" class="row g-3">
                    <?php foreach ([
                        'Shop' => [['Home', 'index.php'], ['Products', 'products.php'], ['Cart', 'cart.php'], ['Wishlist', 'wishlist.php']],
                        'Help' => [['Contact', 'contact.php'], ['FAQ', 'faq.php'], ['Returns', 'page.php?slug=returns'], ['Shipping', 'page.php?slug=shipping']],
                        'Company' => [['About', 'about.php'], ['Careers', 'page.php?slug=careers'], ['Privacy', 'page.php?slug=privacy'], ['Terms', 'page.php?slug=terms']],
                    ] as $group => $links): ?>
                        <section class="col-md-4">
                            <h2 class="h5 fw-bold"><?php echo $group; ?></h2>
                            <ul class="list-unstyled d-grid gap-2">
                                <?php foreach ($links as [$label, $url]): ?><li><a href="<?php echo $url; ?>"><?php echo $label; ?></a></li><?php endforeach; ?>
                            </ul>
                        </section>
                    <?php endforeach; ?>
                </nav>
            <?php elseif ($page): ?>
                <div class="content-page lh-lg"><?php echo $content; ?></div>
            <?php else: ?>
                <p class="lead text-muted lh-lg"><?php echo htmlspecialchars($content); ?></p>
                <a href="contact.php" class="btn btn-danger rounded-pill px-4 mt-3">Contact support</a>
            <?php endif; ?>
        </div>
    </article>
</div>
<?php require_once 'includes/footer.php'; ?>
