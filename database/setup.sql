-- Database Setup Script for Clothing E-Commerce Platform
-- Run this script to create the database and set up initial structure

-- Create database
CREATE DATABASE IF NOT EXISTS clothing_ecommerce CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE clothing_ecommerce;

-- Source the schema file
SOURCE schema.sql;

-- Source the seeders file
SOURCE seeders.sql;

-- Create indexes for better performance
CREATE INDEX idx_products_search ON products(name, brand, description);
CREATE INDEX idx_products_price_range ON products(price, is_active);
CREATE INDEX idx_products_category_season ON products(category_id, season, gender_target);
CREATE INDEX idx_variants_availability ON variants(product_id, is_active, stock);
CREATE INDEX idx_orders_user_status ON orders(user_id, status, created_at);
CREATE INDEX idx_cart_items_cart_product ON cart_items(cart_id, product_id);
CREATE INDEX idx_recommendation_logs_analytics ON recommendation_logs(created_at, model_version, user_id);

-- Display setup completion message
SELECT 'Database setup completed successfully!' as message;
