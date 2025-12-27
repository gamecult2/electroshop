-- Messaging System Migration
-- Run this to add the messaging feature to QwenShop

SET FOREIGN_KEY_CHECKS=0;

-- Conversations table
CREATE TABLE IF NOT EXISTS `conversations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `product_id` INT DEFAULT NULL,
  `status` ENUM('open', 'pending', 'closed') DEFAULT 'open',
  `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
  `assigned_to` INT DEFAULT NULL COMMENT 'Admin user ID',
  `tags` JSON DEFAULT NULL COMMENT 'Categories/tags for filtering',
  `customer_unread_count` INT DEFAULT 0,
  `admin_unread_count` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_message_at` TIMESTAMP NULL DEFAULT NULL,
  `closed_at` TIMESTAMP NULL DEFAULT NULL,
  INDEX `idx_user` (`user_id`),
  INDEX `idx_product` (`product_id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_assigned` (`assigned_to`),
  INDEX `idx_last_message` (`last_message_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Messages table
CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `conversation_id` INT NOT NULL,
  `sender_type` ENUM('customer', 'admin') NOT NULL,
  `sender_id` INT NOT NULL COMMENT 'user_id or admin user_id',
  `message` TEXT NOT NULL,
  `attachments` JSON DEFAULT NULL COMMENT 'Array of file paths',
  `is_read` TINYINT(1) DEFAULT 0,
  `read_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_conversation` (`conversation_id`),
  INDEX `idx_sender` (`sender_type`, `sender_id`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Canned responses for quick replies
CREATE TABLE IF NOT EXISTS `canned_responses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL,
  `message` TEXT NOT NULL,
  `category` VARCHAR(50) DEFAULT NULL COMMENT 'shipping, pricing, support, etc',
  `usage_count` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_category` (`category`),
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Typing indicators (for real-time features)
CREATE TABLE IF NOT EXISTS `typing_indicators` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `conversation_id` INT NOT NULL,
  `user_type` ENUM('customer', 'admin') NOT NULL,
  `user_id` INT NOT NULL,
  `expires_at` TIMESTAMP NOT NULL,
  INDEX `idx_conversation` (`conversation_id`),
  INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Online status tracking
CREATE TABLE IF NOT EXISTS `user_online_status` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_type` ENUM('customer', 'admin') NOT NULL,
  `user_id` INT NOT NULL,
  `last_seen` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_online` TINYINT(1) DEFAULT 0,
  UNIQUE KEY `unique_user` (`user_type`, `user_id`),
  INDEX `idx_online` (`is_online`, `last_seen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Starred/bookmarked messages
CREATE TABLE IF NOT EXISTS `starred_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `message_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_star` (`user_id`, `message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add foreign keys after all tables are created
ALTER TABLE `conversations`
  ADD FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  ADD FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL,
  ADD FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL;

ALTER TABLE `messages`
  ADD FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE CASCADE;

ALTER TABLE `canned_responses`
  ADD FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL;

ALTER TABLE `typing_indicators`
  ADD FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE CASCADE;

ALTER TABLE `starred_messages`
  ADD FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  ADD FOREIGN KEY (`message_id`) REFERENCES `messages`(`id`) ON DELETE CASCADE;

SET FOREIGN_KEY_CHECKS=1;

-- Insert default canned responses
INSERT INTO `canned_responses` (`title`, `message`, `category`) VALUES
('Greeting', 'Hello! Thank you for contacting us. How can I help you today?', 'general'),
('Product Availability', 'This product is currently in stock and ready to ship within 24-48 hours.', 'availability'),
('Shipping Info', 'We offer free shipping on orders over 5000 DA. Standard delivery takes 3-5 business days.', 'shipping'),
('Return Policy', 'We accept returns within 7 days of delivery. The product must be unused and in original packaging.', 'support'),
('Price Inquiry', 'The current price displayed includes all applicable discounts. We also offer bulk purchase discounts for orders over 10 units.', 'pricing'),
('Thank You', 'Thank you for your inquiry! Is there anything else I can help you with?', 'general'),
('Closing', 'Thank you for contacting us. We are here if you need any further assistance!', 'general');
