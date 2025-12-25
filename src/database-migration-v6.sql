-- database-migration-v6.sql - Restructure Product Variants
SET FOREIGN_KEY_CHECKS=0;

-- 1. Clean up products table - REMOVE color and size
-- We use a stored procedure to safely drop columns if they exist, or just attempt it (ignoring errors in strict SQL scripts is hard, but for this env we assume they exist)
ALTER TABLE products DROP COLUMN color;
ALTER TABLE products DROP COLUMN size;

-- 2. Rename and restructure product_variants to represent actual SKUs
DROP TABLE IF EXISTS product_variants;

CREATE TABLE product_variants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    sku VARCHAR(100) UNIQUE NOT NULL,
    variant_name VARCHAR(255),  -- e.g., "Red, 128GB"
    price DECIMAL(10,2) NOT NULL,  -- Final price for this variant
    stock_quantity INT DEFAULT 0,
    barcode VARCHAR(100),
    weight DECIMAL(8,2),  -- Can override product weight
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_sku (sku)
);

-- 3. New table for variant attributes (the flexible part)
DROP TABLE IF EXISTS variant_attributes;

CREATE TABLE variant_attributes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_variant_id INT NOT NULL,
    attribute_name VARCHAR(100) NOT NULL,  -- 'Color', 'Memory', 'Warranty', etc.
    attribute_value VARCHAR(255) NOT NULL, -- 'Red', '128GB', '2 Years', etc.
    FOREIGN KEY (product_variant_id) REFERENCES product_variants(id) ON DELETE CASCADE,
    INDEX idx_variant (product_variant_id),
    INDEX idx_search (attribute_name, attribute_value)
);

-- 4. Optional: For managing available variant options per product
DROP TABLE IF EXISTS product_variant_options;

CREATE TABLE product_variant_options (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    attribute_name VARCHAR(100) NOT NULL,  -- 'Color', 'Memory', etc.
    attribute_value VARCHAR(255) NOT NULL, -- 'Red', '128GB', etc.
    display_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product_option (product_id, attribute_name, attribute_value)
);

SET FOREIGN_KEY_CHECKS=1;
