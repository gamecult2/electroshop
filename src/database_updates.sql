-- Database Updates for Advanced Product Management
-- Run this file to add new features for SKU generation and media management

-- Add video_url column to products table if it doesn't exist
ALTER TABLE products ADD COLUMN IF NOT EXISTS video_url VARCHAR(500) DEFAULT NULL AFTER is_best_seller;

-- Create category_abbreviations table for SKU generation
CREATE TABLE IF NOT EXISTS category_abbreviations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    abbreviation VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    UNIQUE KEY unique_category (category_id),
    UNIQUE KEY unique_abbreviation (abbreviation)
);

-- Create SKU counter table to track the next available number for each category combination
CREATE TABLE IF NOT EXISTS sku_counters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    subcategory_id INT NULL,
    next_number INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_category_combo (category_id, subcategory_id)
);

-- Insert default category abbreviations
INSERT INTO category_abbreviations (category_id, abbreviation) VALUES
(1, 'ELEC'),
(2, 'PCCP'),
(3, 'GAME'),
(4, 'HOME'),
(5, 'ACCS'),
(6, 'PHON'),
(7, 'NETW'),
(8, 'AUDI'),
(9, 'GADG')
ON DUPLICATE KEY UPDATE abbreviation = VALUES(abbreviation);

-- Add media_type column to product_images for better organization
ALTER TABLE product_images ADD COLUMN IF NOT EXISTS media_type ENUM('image', 'video') DEFAULT 'image' AFTER image_url;

-- Add file_size column to track media file sizes
ALTER TABLE product_images ADD COLUMN IF NOT EXISTS file_size INT DEFAULT 0 AFTER media_type;

-- Create index for faster SKU lookups
CREATE INDEX IF NOT EXISTS idx_products_sku_unique ON products(sku);

-- Create media upload log table for tracking and debugging
CREATE TABLE IF NOT EXISTS media_upload_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    media_type ENUM('image', 'video') NOT NULL,
    upload_status ENUM('success', 'failed') NOT NULL,
    error_message TEXT NULL,
    uploaded_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES admin_users(id) ON DELETE SET NULL
);
