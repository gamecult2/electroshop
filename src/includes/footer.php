</main>

<div id="notification-region" class="notification-region" role="status" aria-live="polite" aria-atomic="true"></div>

<?php include __DIR__ . '/chat-widget.php'; ?>

<footer class="bg-dark text-white pt-5 pb-3">
    <div class="container-xxl pb-4 border-bottom border-secondary">
        <div class="row g-4">
            <!-- Customer Service -->
            <div class="col-12 col-sm-6 col-lg-3">
                <h5 class="fw-bold mb-4 text-uppercase small" style="letter-spacing: 1px;"><?php echo t('customer_service'); ?></h5>
                <ul class="nav flex-column gap-2 p-0">
                    <li class="nav-item"><a href="contact.php" class="nav-link p-0 text-white-50 text-decoration-none opacity-75"><?php echo t('contact_us'); ?></a></li>
                    <li class="nav-item"><a href="faq.php" class="nav-link p-0 text-white-50 text-decoration-none opacity-75">FAQ</a></li>
                    <li class="nav-item"><a href="page.php?slug=returns" class="nav-link p-0 text-white-50 text-decoration-none opacity-75"><?php echo t('return_policy'); ?></a></li>
                    <li class="nav-item"><a href="page.php?slug=shipping" class="nav-link p-0 text-white-50 text-decoration-none opacity-75"><?php echo t('shipping_policy'); ?></a></li>
                    <li class="nav-item"><a href="contact.php" class="nav-link p-0 text-white-50 text-decoration-none opacity-75"><?php echo t('support_center'); ?></a></li>
                </ul>
            </div>

            <!-- About Us -->
            <div class="col-12 col-sm-6 col-lg-3">
                <h5 class="fw-bold mb-4 text-uppercase small" style="letter-spacing: 1px;"><?php echo t('about_us'); ?></h5>
                <ul class="nav flex-column gap-2 p-0">
                    <li class="nav-item"><a href="about.php" class="nav-link p-0 text-white-50 text-decoration-none opacity-75"><?php echo t('about_us'); ?></a></li>
                    <li class="nav-item"><a href="page.php?slug=careers" class="nav-link p-0 text-white-50 text-decoration-none opacity-75">Careers</a></li>
                    <li class="nav-item"><a href="page.php?slug=terms" class="nav-link p-0 text-white-50 text-decoration-none opacity-75"><?php echo t('terms_conditions'); ?></a></li>
                    <li class="nav-item"><a href="page.php?slug=privacy" class="nav-link p-0 text-white-50 text-decoration-none opacity-75"><?php echo t('privacy_policy'); ?></a></li>
                    <li class="nav-item"><a href="page.php?slug=sitemap" class="nav-link p-0 text-white-50 text-decoration-none opacity-75">Sitemap</a></li>
                </ul>
            </div>

            <!-- My Account -->
            <div class="col-12 col-sm-6 col-lg-3">
                <h5 class="fw-bold mb-4 text-uppercase small" style="letter-spacing: 1px;"><?php echo t('my_account'); ?></h5>
                <ul class="nav flex-column gap-2 p-0">
                    <li class="nav-item"><a href="account.php" class="nav-link p-0 text-white-50 text-decoration-none opacity-75"><?php echo t('my_account'); ?></a></li>
                    <li class="nav-item"><a href="order_history.php" class="nav-link p-0 text-white-50 text-decoration-none opacity-75"><?php echo t('order_history'); ?></a></li>
                    <li class="nav-item"><a href="wishlist.php" class="nav-link p-0 text-white-50 text-decoration-none opacity-75"><?php echo t('wishlist'); ?></a></li>
                    <li class="nav-item"><a href="account.php#addresses" class="nav-link p-0 text-white-50 text-decoration-none opacity-75"><?php echo t('saved_addresses'); ?></a></li>
                    <li class="nav-item"><a href="account.php#messages" class="nav-link p-0 text-white-50 text-decoration-none opacity-75"><?php echo t('notifications'); ?></a></li>
                </ul>
            </div>

            <!-- Newsletter -->
            <div class="col-12 col-sm-6 col-lg-3">
                <h5 class="fw-bold mb-4 text-uppercase small" style="letter-spacing: 1px;"><?php echo t('newsletter'); ?></h5>
                <p class="text-white-50 mb-4"><?php echo t('subscribe_newsletter'); ?></p>
                <form class="newsletter-form input-group mb-4">
                    <label class="visually-hidden" for="newsletter-email"><?php echo t('enter_your_email'); ?></label>
                    <input type="email" id="newsletter-email" name="newsletter_email" class="form-control bg-dark border-secondary text-white py-2 px-3 rounded-0" placeholder="<?php echo t('enter_your_email'); ?>" required>
                    <button class="btn btn-danger py-2 px-3 rounded-0 fw-bold" type="submit"><?php echo t('subscribe'); ?></button>
                </form>
                <?php
                $footerSocials = [
                    'Facebook' => [get_setting('facebook_url', ''), 'fab fa-facebook-f'],
                    'Twitter' => [get_setting('twitter_url', ''), 'fab fa-twitter'],
                    'Instagram' => [get_setting('instagram_url', ''), 'fab fa-instagram'],
                    'LinkedIn' => [get_setting('linkedin_url', ''), 'fab fa-linkedin-in'],
                ];
                ?>
                <div class="d-flex gap-2">
                    <?php foreach ($footerSocials as $label => [$url, $icon]): if (!$url) continue; ?>
                        <a href="<?php echo htmlspecialchars($url); ?>" class="footer-social-link text-white-50 fs-5 transition-all" target="_blank" rel="noopener" aria-label="<?php echo $label; ?>"><i class="<?php echo $icon; ?>" aria-hidden="true"></i></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment & Copyright -->
    <div class="container-xxl pt-4">
        <div class="row align-items-center g-3">
            <div class="col-md-6 text-center text-md-start">
                <p class="text-white-50 mb-0 small">&copy; <?php echo date('Y'); ?> <span class="text-white fw-bold"><?php echo htmlspecialchars(get_setting('site_title', SITE_TITLE)); ?></span>. All rights reserved. Designed for Algeria.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <div class="d-flex align-items-center justify-content-center justify-content-md-end gap-3 text-white-50 small">
                    <span><?php echo t('accepted_payments'); ?>:</span>
                    <span aria-label="Cash on delivery"><i class="fas fa-money-bill-wave fs-4" aria-hidden="true"></i></span>
                    <span aria-label="Bank transfer"><i class="fas fa-university fs-4" aria-hidden="true"></i></span>
                    <span class="fw-bold border border-secondary px-2 py-0 rounded bg-secondary text-white small">BaridiMob</span>
                    <span class="fw-bold border border-secondary px-2 py-0 rounded bg-secondary text-white small">Edahabia</span>
                    <span class="fw-bold border border-secondary px-2 py-0 rounded bg-secondary text-white small">CIB</span>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/main.js"></script>

<script>
// Newsletter subscription
document.addEventListener('DOMContentLoaded', function() {
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = newsletterForm.querySelector('input[name="newsletter_email"]').value;
            
            fetch('api/newsletter.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email: email
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    newsletterForm.reset();
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('<?php echo addslashes(t('error_subscription')); ?>', 'error');
            });
        });
    }
});

// Cookie consent banner functionality
function showCookieConsent() {
    const cookieBanner = document.getElementById('cookie-consent-banner');
    if (cookieBanner) return; // Already shown
    
    const banner = document.createElement('div');
    // Using Bootstrap classes for cookie banner
    banner.className = 'cookie-consent fixed-bottom bg-dark text-white p-4 shadow-lg border-top border-danger';
    banner.id = 'cookie-consent-banner';
    banner.style.zIndex = '9999';
    banner.innerHTML = `
        <div class="container-xxl d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
            <p class="mb-0 text-light small"><?php echo addslashes(t('cookie_consent_text')); ?></p>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-danger btn-sm px-4 fw-bold" onclick="acceptCookies()"><?php echo addslashes(t('accept')); ?></button>
                <button type="button" class="btn btn-outline-light btn-sm px-4" onclick="declineCookies()"><?php echo addslashes(t('decline')); ?></button>
                <a href="page.php?slug=privacy" class="btn btn-link text-white-50 btn-sm text-decoration-none"><?php echo addslashes(t('learn_more')); ?></a>
            </div>
        </div>
    `;
    
    document.body.appendChild(banner);
}

function acceptCookies() {
    document.cookie = "cookie_consent=accepted; max-age=31536000; path=/; SameSite=Lax";
    document.getElementById('cookie-consent-banner').remove();
}

function declineCookies() {
    document.cookie = "cookie_consent=declined; max-age=31536000; path=/; SameSite=Lax";
    document.getElementById('cookie-consent-banner').remove();
}

// Show cookie consent if not already accepted
document.addEventListener('DOMContentLoaded', function() {
    const cookieValue = document.cookie.split('; ').find(row => row.startsWith('cookie_consent='));
    if (!cookieValue && <?php echo get_setting('cookie_consent_enabled', '1') === '1' ? 'true' : 'false'; ?>) {
        setTimeout(showCookieConsent, 2000); // Show after 2 seconds
    }
});

// WhatsApp widget
function initWhatsAppWidget() {
    const phoneNumber = '<?php echo preg_replace('/\D+/', '', WHATSAPP_BUSINESS_NUMBER); ?>';
    if (!phoneNumber) return;
    const widget = document.createElement('div');
    // Using Bootstrap classes for WhatsApp button
    widget.className = 'whatsapp-widget fixed-bottom p-4 d-flex justify-content-end pointer-events-none';
    widget.style.zIndex = '9000';
    widget.innerHTML = `
        <a href="https://wa.me/${phoneNumber}?text=Hello,%20I%20have%20a%20question%20about%20your%20products"
           class="btn btn-success rounded-circle shadow-lg d-flex align-items-center justify-content-center pointer-events-auto" 
           style="width: 60px; height: 60px; font-size: 32px;"
           target="_blank" rel="noopener" aria-label="Contact us on WhatsApp">
            <i class="fab fa-whatsapp"></i>
        </a>
    `;
    document.body.appendChild(widget);
}

// Initialize WhatsApp widget
document.addEventListener('DOMContentLoaded', function() {
    initWhatsAppWidget();

});
</script>
</body>
</html>
