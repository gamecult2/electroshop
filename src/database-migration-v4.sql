-- Migration: Separate Users and Customers
-- Rename existing users table to customers (these are the shop clients)
RENAME TABLE users TO customers;

-- Rename admin_users table to users (these are the staff: admin, moderator, agent)
RENAME TABLE admin_users TO users;

-- Update roles in the new users table to include 'agent'
ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'manager', 'moderator', 'agent') DEFAULT 'moderator';

-- Update foreign keys and references to point to customers table
-- user_addresses
ALTER TABLE user_addresses RENAME COLUMN user_id TO customer_id;
ALTER TABLE user_addresses DROP FOREIGN KEY user_addresses_ibfk_1;
ALTER TABLE user_addresses ADD CONSTRAINT fk_user_addresses_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE;

-- shopping_cart
ALTER TABLE shopping_cart RENAME COLUMN user_id TO customer_id;
ALTER TABLE shopping_cart DROP FOREIGN KEY shopping_cart_ibfk_1;
ALTER TABLE shopping_cart ADD CONSTRAINT fk_shopping_cart_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE;

-- orders
ALTER TABLE orders RENAME COLUMN user_id TO customer_id;
ALTER TABLE orders DROP FOREIGN KEY orders_ibfk_1;
ALTER TABLE orders ADD CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES customers(id);

-- reviews
ALTER TABLE reviews RENAME COLUMN user_id TO customer_id;
ALTER TABLE reviews DROP FOREIGN KEY reviews_ibfk_2;
ALTER TABLE reviews ADD CONSTRAINT fk_reviews_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE;

-- wishlists
ALTER TABLE wishlists RENAME COLUMN user_id TO customer_id;
ALTER TABLE wishlists DROP FOREIGN KEY wishlists_ibfk_1;
ALTER TABLE wishlists ADD CONSTRAINT fk_wishlists_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE;

-- search_history
ALTER TABLE search_history RENAME COLUMN user_id TO customer_id;
ALTER TABLE search_history DROP FOREIGN KEY search_history_ibfk_1;
ALTER TABLE search_history ADD CONSTRAINT fk_search_history_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE;

-- user_notifications
ALTER TABLE user_notifications RENAME COLUMN user_id TO customer_id;
ALTER TABLE user_notifications DROP FOREIGN KEY user_notifications_ibfk_1;
ALTER TABLE user_notifications ADD CONSTRAINT fk_user_notifications_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE;

-- If there are indexes with names containing 'user', we might want to rename them too, but it's not strictly necessary.
