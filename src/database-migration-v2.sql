-- QwenShop Database Migration v2 - Complete Enhancement Package
-- Adds all new features: Search, Reviews, Wishlist, Order Tracking, etc.

-- New: Search History Table
CREATE TABLE IF NOT EXISTS search_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    search_term VARCHAR(255) NOT NULL,
    search_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    result_count INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- New: Review Images Table
CREATE TABLE IF NOT EXISTS review_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    alt_text VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE
);

-- New: Review Votes Table
CREATE TABLE IF NOT EXISTS review_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    user_id INT,
    session_id VARCHAR(255),
    vote ENUM('helpful', 'not_helpful') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_review_vote (user_id, review_id)
);

-- New: Wishlist Table
CREATE TABLE IF NOT EXISTS wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- New: Order Status History Table
CREATE TABLE IF NOT EXISTS order_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned') DEFAULT 'pending',
    admin_user_id INT,
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_user_id) REFERENCES admin_users(id) ON DELETE SET NULL
);

-- New: Product Views Table
CREATE TABLE IF NOT EXISTS product_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT,
    session_id VARCHAR(255),
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- New: Newsletter Subscribers Table
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(191) UNIQUE NOT NULL,
    is_confirmed TINYINT(1) DEFAULT 0,
    confirmation_token VARCHAR(255),
    confirmed_at TIMESTAMP NULL,
    unsubscribed_at TIMESTAMP NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- New: Newsletter Campaigns Table
CREATE TABLE IF NOT EXISTS newsletter_campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    sent_count INT DEFAULT 0,
    opened_count INT DEFAULT 0,
    clicked_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at TIMESTAMP NULL
);

-- New: Newsletter Logs Table
CREATE TABLE IF NOT EXISTS newsletter_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subscriber_id INT,
    campaign_id INT,
    type ENUM('sent', 'opened', 'clicked') NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    FOREIGN KEY (subscriber_id) REFERENCES newsletter_subscribers(id) ON DELETE SET NULL,
    FOREIGN KEY (campaign_id) REFERENCES newsletter_campaigns(id) ON DELETE SET NULL
);

-- New: Banners Table
CREATE TABLE IF NOT EXISTS banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    desktop_image VARCHAR(500) NOT NULL,
    mobile_image VARCHAR(500),
    link_url VARCHAR(500),
    link_target ENUM('_self', '_blank') DEFAULT '_self',
    position ENUM('homepage_hero', 'category_top', 'sidebar', 'footer', 'popup') DEFAULT 'homepage_hero',
    priority INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    starts_at DATETIME,
    expires_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- New: Banner Clicks Table
CREATE TABLE IF NOT EXISTS banner_clicks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    banner_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (banner_id) REFERENCES banners(id) ON DELETE CASCADE
);

-- New: Coupon Usage Table
CREATE TABLE IF NOT EXISTS coupon_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coupon_id INT NOT NULL,
    order_id INT NOT NULL,
    user_id INT,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_coupon_order (coupon_id, order_id)
);

-- New: Flash Sales Table
CREATE TABLE IF NOT EXISTS flash_sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    flash_sale_price DECIMAL(10, 2) NOT NULL,
    discount_percentage DECIMAL(5, 2) NOT NULL,
    quantity_limit INT DEFAULT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- New: Stock Notifications Table
CREATE TABLE IF NOT EXISTS stock_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    is_sent TINYINT(1) DEFAULT 0,
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at TIMESTAMP NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- New: Shipping Rates Table
CREATE TABLE IF NOT EXISTS shipping_rates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wilaya VARCHAR(100) NOT NULL,
    flat_rate DECIMAL(10, 2) DEFAULT 0,
    weight_tier_1 DECIMAL(10, 2) DEFAULT 0, -- Up to 1kg
    weight_tier_2 DECIMAL(10, 2) DEFAULT 0, -- Up to 5kg
    weight_tier_3 DECIMAL(10, 2) DEFAULT 0, -- Up to 10kg
    weight_tier_4 DECIMAL(10, 2) DEFAULT 0, -- Over 10kg
    free_shipping_threshold DECIMAL(10, 2) DEFAULT 0,
    delivery_days INT DEFAULT 7,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- New: Returns Table
CREATE TABLE IF NOT EXISTS returns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('requested', 'approved', 'rejected', 'picked_up', 'processed', 'refunded') DEFAULT 'requested',
    reason TEXT,
    images JSON,
    admin_note TEXT,
    refund_amount DECIMAL(10, 2),
    refund_method ENUM('original_payment', 'store_credit'),
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved_at TIMESTAMP NULL,
    processed_at TIMESTAMP NULL,
    admin_id INT,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE SET NULL
);

-- New: Return Items Table
CREATE TABLE IF NOT EXISTS return_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    return_id INT NOT NULL,
    order_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    FOREIGN KEY (return_id) REFERENCES returns(id) ON DELETE CASCADE,
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE
);

-- New: Admin Logs Table
CREATE TABLE IF NOT EXISTS admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_user_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_user_id) REFERENCES admin_users(id) ON DELETE SET NULL
);

-- New: Rate Limits Table
CREATE TABLE IF NOT EXISTS rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    request_count INT DEFAULT 1,
    last_request_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL
);

-- New: Blocked IPs Table
CREATE TABLE IF NOT EXISTS blocked_ips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    reason VARCHAR(255),
    blocked_until TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- New: Backups Table
CREATE TABLE IF NOT EXISTS backups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    type ENUM('database', 'files', 'full') NOT NULL,
    size_mb DECIMAL(8, 2),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admin_users(id) ON DELETE SET NULL
);

-- New: Contact Messages Table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_resolved TINYINT(1) DEFAULT 0,
    resolved_at TIMESTAMP NULL,
    resolved_by INT,
    responded_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resolved_by) REFERENCES admin_users(id) ON DELETE SET NULL
);

-- New: FAQ Categories Table
CREATE TABLE IF NOT EXISTS faq_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_en VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- New: FAQs Table
CREATE TABLE IF NOT EXISTS faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    question_en TEXT NOT NULL,
    answer_en TEXT NOT NULL,
    view_count INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES faq_categories(id) ON DELETE CASCADE
);

-- New: Pages Table
CREATE TABLE IF NOT EXISTS pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title_en VARCHAR(255) NOT NULL,
    slug VARCHAR(191) UNIQUE NOT NULL,
    content_en LONGTEXT NOT NULL,
    meta_title_en VARCHAR(255),
    meta_description_en TEXT,
    meta_keywords TEXT,
    is_published TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- New: Data Requests Table
CREATE TABLE IF NOT EXISTS data_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('export', 'delete') NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    requested_data JSON,
    exported_file VARCHAR(500),
    processed_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    admin_note TEXT,
    processed_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES admin_users(id) ON DELETE SET NULL
);

-- New: Product Comparison Sessions (for guests)
CREATE TABLE IF NOT EXISTS product_comparison_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    product_ids JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL
);

-- New: Couriers Table
CREATE TABLE IF NOT EXISTS couriers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    tracking_url VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1
);

-- Enhance Existing Tables
-- Add columns to existing tables where needed
ALTER TABLE products ADD COLUMN low_stock_threshold INT DEFAULT 5;
ALTER TABLE products ADD COLUMN flash_sale_badge TINYINT(1) DEFAULT 0;

-- Enhance Orders table for courier tracking
ALTER TABLE orders ADD COLUMN courier_name VARCHAR(100);
ALTER TABLE orders ADD COLUMN tracking_number VARCHAR(100);
ALTER TABLE orders ADD COLUMN tracking_url VARCHAR(500);
ALTER TABLE orders ADD COLUMN estimated_delivery DATE;

-- Enhance users table for 2FA
ALTER TABLE users ADD COLUMN two_fa_enabled TINYINT(1) DEFAULT 0;
ALTER TABLE users ADD COLUMN two_fa_secret VARCHAR(255);
ALTER TABLE users ADD COLUMN two_fa_backup_codes TEXT;

-- Enhance site_settings table
ALTER TABLE site_settings ADD COLUMN is_public TINYINT(1) DEFAULT 1;

-- For MySQL older than 8.0.29, we have to handle indexes carefully.
-- Creating indexes if they don't exist requires checking first or ignoring errors

-- Handle indexes creation with the older compatible syntax
-- The migrate.php script will skip these if they already exist

-- Indexes for search history
-- Note: These will be handled by migrate.php logic for duplicate prevention
CREATE INDEX IF NOT EXISTS idx_search_history_user ON search_history(user_id);
CREATE INDEX IF NOT EXISTS idx_search_history_date ON search_history(search_date);

-- Indexes for product views
CREATE INDEX IF NOT EXISTS idx_product_views_product ON product_views(product_id);
CREATE INDEX IF NOT EXISTS idx_product_views_user ON product_views(user_id);

-- Indexes for reviews
CREATE INDEX IF NOT EXISTS idx_reviews_product ON reviews(product_id);
CREATE INDEX IF NOT EXISTS idx_reviews_rating ON reviews(rating);
CREATE INDEX IF NOT EXISTS idx_reviews_approved ON reviews(is_approved);

-- Indexes for wishlist
CREATE INDEX IF NOT EXISTS idx_wishlist_user ON wishlist(user_id);

-- Indexes for order status history
CREATE INDEX IF NOT EXISTS idx_order_status_history_order ON order_status_history(order_id);

-- Indexes for orders
CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status);
CREATE INDEX IF NOT EXISTS idx_orders_user ON orders(user_id);
CREATE INDEX IF NOT EXISTS idx_orders_date ON orders(created_at);

-- Indexes for newsletter subscribers
CREATE INDEX IF NOT EXISTS idx_newsletter_subscriber_email ON newsletter_subscribers(email);

-- Indexes for coupons
CREATE INDEX IF NOT EXISTS idx_coupons_code ON coupons(code);
CREATE INDEX IF NOT EXISTS idx_coupons_dates ON coupons(starts_at, expires_at);

-- Indexes for flash sales
CREATE INDEX IF NOT EXISTS idx_flash_sales_product ON flash_sales(product_id);
CREATE INDEX IF NOT EXISTS idx_flash_sales_dates ON flash_sales(start_time, end_time);

-- Indexes for products
CREATE INDEX IF NOT EXISTS idx_products_stock ON products(stock_quantity);
CREATE INDEX IF NOT EXISTS idx_products_low_stock ON products(stock_quantity, low_stock_threshold);
CREATE INDEX IF NOT EXISTS idx_products_featured ON products(is_featured);
CREATE INDEX IF NOT EXISTS idx_products_new_arrival ON products(is_new_arrival);
CREATE INDEX IF NOT EXISTS idx_products_best_seller ON products(is_best_seller);
CREATE INDEX IF NOT EXISTS idx_products_rating ON products(rating);
CREATE INDEX IF NOT EXISTS idx_products_price ON products(final_price);
CREATE INDEX IF NOT EXISTS idx_products_active ON products(is_active);
CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id);
CREATE INDEX IF NOT EXISTS idx_products_brand ON products(brand_id);

-- Indexes for users and admin
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_admin_users_email ON admin_users(email);

-- Indexes for other tables
CREATE INDEX IF NOT EXISTS idx_contact_messages_resolved ON contact_messages(is_resolved);
CREATE INDEX IF NOT EXISTS idx_return_status ON returns(status);
CREATE INDEX IF NOT EXISTS idx_rate_limits_ip ON rate_limits(ip_address);

-- Add fulltext indexes for search (with reduced field lengths to avoid key length issues)
ALTER TABLE products ADD FULLTEXT(name_en);
ALTER TABLE products ADD FULLTEXT(description_en);
ALTER TABLE products ADD FULLTEXT(short_description_en);
ALTER TABLE products ADD FULLTEXT(sku);

-- Add sample data for new tables
-- Add default FAQ categories
INSERT IGNORE INTO faq_categories (name_en, sort_order) VALUES
('Ordering', 1),
('Shipping', 2),
('Returns', 3),
('Payment', 4),
('Account', 5);

-- Add initial FAQ sample data
INSERT IGNORE INTO faqs (category_id, question_en, answer_en, sort_order) VALUES
(1, 'How do I place an order?', 'Simply browse our products, add items to cart, and proceed to checkout. Follow the prompts to provide shipping and payment information.', 1),
(2, 'What are the shipping costs?', 'Shipping costs vary by location. We offer free shipping on orders over 10,000 DA. You can calculate shipping costs in the cart.', 1),
(3, 'What is your return policy?', 'We offer a 30-day return policy. Items must be in original condition. Contact us to initiate a return request.', 1),
(4, 'Which payment methods do you accept?', 'We accept CIB Card, Edahabia, BaridiMob, Cash on Delivery (COD), and Bank Transfer.', 1);

-- Add default shipping rates
INSERT IGNORE INTO shipping_rates (wilaya, flat_rate, weight_tier_1, weight_tier_2, weight_tier_3, weight_tier_4, free_shipping_threshold, delivery_days) VALUES
('Alger', 500.00, 500.00, 600.00, 700.00, 800.00, 10000.00, 3),
('Oran', 600.00, 600.00, 700.00, 800.00, 900.00, 10000.00, 4),
('Constantine', 700.00, 700.00, 800.00, 900.00, 1000.00, 10000.00, 5);

-- Add sample banners
INSERT IGNORE INTO banners (title, desktop_image, mobile_image, link_url, position, priority, is_active) VALUES
('Summer Sale', '/img/banners/summer-sale-desktop.jpg', '/img/banners/summer-sale-mobile.jpg', '/products', 'homepage_hero', 1, 1),
('New Arrivals', '/img/banners/new-arrivals-desktop.jpg', '/img/banners/new-arrivals-mobile.jpg', '/products?new=true', 'category_top', 2, 1);

-- Add static pages
INSERT IGNORE INTO pages (title_en, slug, content_en, is_published) VALUES
('Terms of Service', 'terms-of-service', '<h2>Terms of Service</h2><p>Welcome to QwenShop. These terms govern your use of our services.</p>', 1),
('Privacy Policy', 'privacy-policy', '<h2>Privacy Policy</h2><p>We respect your privacy and are committed to protecting your personal data.</p>', 1);

-- Update site settings with new features
INSERT IGNORE INTO site_settings (setting_key, setting_value, setting_type, is_public) VALUES
('feature_search_enabled', '1', 'boolean', 1),
('feature_reviews_enabled', '1', 'boolean', 1),
('feature_wishlist_enabled', '1', 'boolean', 1),
('feature_newsletter_enabled', '1', 'boolean', 1),
('default_free_shipping_threshold', '10000.00', 'number', 1),
('whatsapp_business_number', '+213700000000', 'string', 1);