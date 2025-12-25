-- database-migration-v5.sql - Add support for multiple variants in cart
ALTER TABLE shopping_cart ADD COLUMN selected_variants JSON NULL AFTER variant_id;
ALTER TABLE order_items ADD COLUMN selected_variants JSON NULL AFTER variant_id;
