-- Seed data for Clothing E-Commerce Platform
-- This file populates the database with sample products, users, and test data

-- Sample products for Men's category
INSERT INTO products (category_id, name, slug, description, price, rating_avg, rating_count, season, gender_target, material, brand) VALUES
-- Men's Spring/Summer
(1, 'Classic Cotton T-Shirt', 'mens-classic-cotton-tshirt', 'Comfortable 100% cotton t-shirt perfect for everyday wear', 24.99, 4.5, 127, 'summer', 'men', 'Cotton', 'BasicWear'),
(1, 'Linen Button-Up Shirt', 'mens-linen-button-up-shirt', 'Breathable linen shirt ideal for warm weather', 59.99, 4.3, 89, 'summer', 'men', 'Linen', 'SummerStyle'),
(1, 'Cargo Shorts', 'mens-cargo-shorts', 'Practical cargo shorts with multiple pockets', 39.99, 4.2, 156, 'summer', 'men', 'Cotton Blend', 'OutdoorGear'),
(1, 'Polo Shirt', 'mens-polo-shirt', 'Classic polo shirt for casual and semi-formal occasions', 34.99, 4.4, 203, 'spring', 'men', 'Cotton Pique', 'ClassicFit'),
(1, 'Chino Pants', 'mens-chino-pants', 'Versatile chino pants suitable for work and casual wear', 49.99, 4.6, 178, 'spring', 'men', 'Cotton Twill', 'SmartCasual'),

-- Men's Autumn/Winter
(1, 'Wool Sweater', 'mens-wool-sweater', 'Warm merino wool sweater for cold weather', 89.99, 4.7, 94, 'winter', 'men', 'Merino Wool', 'WinterWarm'),
(1, 'Denim Jacket', 'mens-denim-jacket', 'Classic denim jacket for layering', 79.99, 4.5, 167, 'autumn', 'men', 'Denim', 'VintageStyle'),
(1, 'Flannel Shirt', 'mens-flannel-shirt', 'Cozy flannel shirt perfect for autumn', 44.99, 4.3, 134, 'autumn', 'men', 'Cotton Flannel', 'LumberJack'),
(1, 'Winter Coat', 'mens-winter-coat', 'Insulated winter coat for extreme cold', 149.99, 4.8, 76, 'winter', 'men', 'Polyester Fill', 'ArcticGear'),
(1, 'Thermal Underwear Set', 'mens-thermal-underwear-set', 'Base layer thermal set for winter activities', 39.99, 4.4, 112, 'winter', 'men', 'Merino Blend', 'ThermalTech'),

-- Women's Spring/Summer
(2, 'Floral Summer Dress', 'womens-floral-summer-dress', 'Light and airy floral dress perfect for summer days', 69.99, 4.6, 189, 'summer', 'women', 'Cotton Voile', 'FloralFashion'),
(2, 'Silk Blouse', 'womens-silk-blouse', 'Elegant silk blouse for professional and casual wear', 89.99, 4.5, 145, 'spring', 'women', 'Silk', 'ElegantStyle'),
(2, 'High-Waisted Jeans', 'womens-high-waisted-jeans', 'Trendy high-waisted jeans with stretch comfort', 79.99, 4.7, 234, 'all', 'women', 'Denim Stretch', 'ModernFit'),
(2, 'Crop Top', 'womens-crop-top', 'Stylish crop top for layering or standalone wear', 29.99, 4.2, 167, 'summer', 'women', 'Cotton Jersey', 'TrendyWear'),
(2, 'Maxi Skirt', 'womens-maxi-skirt', 'Flowing maxi skirt for elegant summer looks', 54.99, 4.4, 123, 'summer', 'women', 'Rayon', 'BohoChic'),

-- Women's Autumn/Winter
(2, 'Cashmere Cardigan', 'womens-cashmere-cardigan', 'Luxurious cashmere cardigan for layering', 129.99, 4.8, 87, 'autumn', 'women', 'Cashmere', 'LuxuryKnits'),
(2, 'Wool Coat', 'womens-wool-coat', 'Classic wool coat for professional and formal occasions', 199.99, 4.6, 156, 'winter', 'women', 'Wool Blend', 'ClassicCoats'),
(2, 'Turtleneck Sweater', 'womens-turtleneck-sweater', 'Cozy turtleneck sweater for cold weather', 64.99, 4.5, 198, 'winter', 'women', 'Wool Blend', 'CozyWear'),
(2, 'Leather Boots', 'womens-leather-boots', 'Stylish leather boots for autumn and winter', 149.99, 4.7, 167, 'autumn', 'women', 'Genuine Leather', 'FootwearPlus'),
(2, 'Plaid Scarf', 'womens-plaid-scarf', 'Warm plaid scarf to complete winter outfits', 34.99, 4.3, 145, 'winter', 'women', 'Wool Blend', 'AccessoryHub'),

-- Kids' Spring/Summer
(3, 'Dinosaur T-Shirt', 'kids-dinosaur-tshirt', 'Fun dinosaur print t-shirt for adventurous kids', 19.99, 4.6, 234, 'summer', 'kids', 'Cotton', 'KidsFun'),
(3, 'Rainbow Dress', 'kids-rainbow-dress', 'Colorful rainbow dress for special occasions', 39.99, 4.5, 167, 'spring', 'kids', 'Cotton Blend', 'HappyKids'),
(3, 'Denim Overalls', 'kids-denim-overalls', 'Classic denim overalls for play and adventure', 44.99, 4.4, 189, 'all', 'kids', 'Denim', 'PlayWear'),
(3, 'Swim Shorts', 'kids-swim-shorts', 'Quick-dry swim shorts for pool and beach', 24.99, 4.3, 145, 'summer', 'kids', 'Polyester', 'AquaKids'),
(3, 'Sandals', 'kids-sandals', 'Comfortable sandals for summer adventures', 29.99, 4.5, 178, 'summer', 'kids', 'Synthetic', 'ComfortFeet'),

-- Kids' Autumn/Winter
(3, 'Hoodie with Pockets', 'kids-hoodie-with-pockets', 'Cozy hoodie with kangaroo pocket', 34.99, 4.6, 198, 'autumn', 'kids', 'Cotton Fleece', 'CozyKids'),
(3, 'Winter Jacket', 'kids-winter-jacket', 'Warm winter jacket with fun patterns', 79.99, 4.7, 134, 'winter', 'kids', 'Polyester Fill', 'WarmKids'),
(3, 'Knit Beanie', 'kids-knit-beanie', 'Cute knit beanie to keep little heads warm', 14.99, 4.4, 167, 'winter', 'kids', 'Acrylic', 'HeadWarmers'),
(3, 'Snow Boots', 'kids-snow-boots', 'Waterproof snow boots for winter play', 59.99, 4.8, 123, 'winter', 'kids', 'Synthetic', 'SnowPlay'),
(3, 'Fleece Pajamas', 'kids-fleece-pajamas', 'Soft fleece pajamas for cozy nights', 29.99, 4.5, 189, 'winter', 'kids', 'Fleece', 'SleepyTime'),

-- Unisex items
(1, 'Baseball Cap', 'unisex-baseball-cap', 'Classic baseball cap suitable for everyone', 24.99, 4.4, 267, 'all', 'unisex', 'Cotton Twill', 'CapStyle'),
(2, 'Canvas Sneakers', 'unisex-canvas-sneakers', 'Comfortable canvas sneakers for daily wear', 54.99, 4.5, 345, 'all', 'unisex', 'Canvas', 'FootwearClassic'),
(3, 'Backpack', 'unisex-backpack', 'Durable backpack for school and travel', 49.99, 4.6, 234, 'all', 'unisex', 'Nylon', 'TravelGear');

-- Product images (sample URLs - in production these would be actual image URLs)
INSERT INTO product_images (product_id, url, alt_text, is_primary, sort_order) VALUES
-- Men's Classic Cotton T-Shirt
(1, '/images/products/mens-tshirt-1.jpg', 'Men''s Classic Cotton T-Shirt - Front View', TRUE, 1),
(1, '/images/products/mens-tshirt-1-back.jpg', 'Men''s Classic Cotton T-Shirt - Back View', FALSE, 2),
(1, '/images/products/mens-tshirt-1-detail.jpg', 'Men''s Classic Cotton T-Shirt - Fabric Detail', FALSE, 3),

-- Linen Button-Up Shirt
(2, '/images/products/mens-linen-shirt-1.jpg', 'Men''s Linen Button-Up Shirt - Front View', TRUE, 1),
(2, '/images/products/mens-linen-shirt-1-worn.jpg', 'Men''s Linen Button-Up Shirt - Lifestyle', FALSE, 2),

-- Continue with a few more key products...
(3, '/images/products/mens-cargo-shorts-1.jpg', 'Men''s Cargo Shorts - Front View', TRUE, 1),
(4, '/images/products/mens-polo-shirt-1.jpg', 'Men''s Polo Shirt - Front View', TRUE, 1),
(5, '/images/products/mens-chino-pants-1.jpg', 'Men''s Chino Pants - Front View', TRUE, 1),

-- Women's products
(11, '/images/products/womens-floral-dress-1.jpg', 'Women''s Floral Summer Dress - Front View', TRUE, 1),
(12, '/images/products/womens-silk-blouse-1.jpg', 'Women''s Silk Blouse - Front View', TRUE, 1),
(13, '/images/products/womens-jeans-1.jpg', 'Women''s High-Waisted Jeans - Front View', TRUE, 1),

-- Kids' products
(21, '/images/products/kids-dinosaur-tshirt-1.jpg', 'Kids'' Dinosaur T-Shirt - Front View', TRUE, 1),
(22, '/images/products/kids-rainbow-dress-1.jpg', 'Kids'' Rainbow Dress - Front View', TRUE, 1),
(23, '/images/products/kids-overalls-1.jpg', 'Kids'' Denim Overalls - Front View', TRUE, 1);

-- Product variants (sizes and colors)
INSERT INTO variants (product_id, sku, color, size, stock, extra_price) VALUES
-- Men's Classic Cotton T-Shirt variants
(1, 'MCT-BLK-S', 'Black', 'S', 25, 0.00),
(1, 'MCT-BLK-M', 'Black', 'M', 30, 0.00),
(1, 'MCT-BLK-L', 'Black', 'L', 20, 0.00),
(1, 'MCT-BLK-XL', 'Black', 'XL', 15, 0.00),
(1, 'MCT-WHT-S', 'White', 'S', 28, 0.00),
(1, 'MCT-WHT-M', 'White', 'M', 35, 0.00),
(1, 'MCT-WHT-L', 'White', 'L', 22, 0.00),
(1, 'MCT-WHT-XL', 'White', 'XL', 18, 0.00),
(1, 'MCT-GRY-S', 'Gray', 'S', 20, 0.00),
(1, 'MCT-GRY-M', 'Gray', 'M', 25, 0.00),
(1, 'MCT-GRY-L', 'Gray', 'L', 18, 0.00),
(1, 'MCT-GRY-XL', 'Gray', 'XL', 12, 0.00),

-- Linen Button-Up Shirt variants
(2, 'LBS-BLU-S', 'Light Blue', 'S', 15, 0.00),
(2, 'LBS-BLU-M', 'Light Blue', 'M', 20, 0.00),
(2, 'LBS-BLU-L', 'Light Blue', 'L', 18, 0.00),
(2, 'LBS-BLU-XL', 'Light Blue', 'XL', 10, 0.00),
(2, 'LBS-WHT-S', 'White', 'S', 12, 0.00),
(2, 'LBS-WHT-M', 'White', 'M', 16, 0.00),
(2, 'LBS-WHT-L', 'White', 'L', 14, 0.00),
(2, 'LBS-WHT-XL', 'White', 'XL', 8, 0.00),

-- Women's High-Waisted Jeans variants
(13, 'WHJ-BLU-26', 'Dark Blue', '26', 15, 0.00),
(13, 'WHJ-BLU-28', 'Dark Blue', '28', 20, 0.00),
(13, 'WHJ-BLU-30', 'Dark Blue', '30', 18, 0.00),
(13, 'WHJ-BLU-32', 'Dark Blue', '32', 12, 0.00),
(13, 'WHJ-BLK-26', 'Black', '26', 14, 5.00),
(13, 'WHJ-BLK-28', 'Black', '28', 18, 5.00),
(13, 'WHJ-BLK-30', 'Black', '30', 16, 5.00),
(13, 'WHJ-BLK-32', 'Black', '32', 10, 5.00),

-- Kids' Dinosaur T-Shirt variants
(21, 'KDT-GRN-2T', 'Green', '2T', 20, 0.00),
(21, 'KDT-GRN-3T', 'Green', '3T', 25, 0.00),
(21, 'KDT-GRN-4T', 'Green', '4T', 22, 0.00),
(21, 'KDT-GRN-5T', 'Green', '5T', 18, 0.00),
(21, 'KDT-BLU-2T', 'Blue', '2T', 18, 0.00),
(21, 'KDT-BLU-3T', 'Blue', '3T', 23, 0.00),
(21, 'KDT-BLU-4T', 'Blue', '4T', 20, 0.00),
(21, 'KDT-BLU-5T', 'Blue', '5T', 15, 0.00);

-- Sample users for testing
INSERT INTO users (name, email, password_hash, gender, birthdate) VALUES
('John Doe', 'john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'male', '1990-05-15'),
('Jane Smith', 'jane.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'female', '1988-09-22'),
('Bob Johnson', 'bob.johnson@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'male', '1985-12-03'),
('Alice Brown', 'alice.brown@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'female', '1992-07-18'),
('Test User', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'other', '1995-03-10');

-- Sample addresses
INSERT INTO addresses (user_id, line1, line2, city, state, postal_code, country, is_default) VALUES
(1, '123 Main St', 'Apt 4B', 'New York', 'NY', '10001', 'US', TRUE),
(1, '456 Oak Ave', NULL, 'Brooklyn', 'NY', '11201', 'US', FALSE),
(2, '789 Pine Rd', 'Suite 200', 'Los Angeles', 'CA', '90210', 'US', TRUE),
(3, '321 Elm St', NULL, 'Chicago', 'IL', '60601', 'US', TRUE),
(4, '654 Maple Dr', NULL, 'Miami', 'FL', '33101', 'US', TRUE);

-- Sample wishlists
INSERT INTO wishlists (user_id) VALUES (1), (2), (3), (4), (5);

-- Sample wishlist items
INSERT INTO wishlist_items (wishlist_id, product_id) VALUES
(1, 2), (1, 5), (1, 11), (1, 13),
(2, 1), (2, 12), (2, 15), (2, 22),
(3, 3), (3, 6), (3, 21), (3, 23),
(4, 4), (4, 14), (4, 16), (4, 24);

-- Sample carts
INSERT INTO carts (user_id, session_id) VALUES 
(1, NULL),
(2, NULL),
(NULL, 'guest_session_123'),
(NULL, 'guest_session_456');

-- Sample cart items
INSERT INTO cart_items (cart_id, product_id, variant_id, qty, price_at_add) VALUES
(1, 1, 2, 2, 24.99),
(1, 13, 18, 1, 79.99),
(2, 11, NULL, 1, 69.99),
(2, 21, 25, 2, 19.99),
(3, 2, 13, 1, 59.99),
(4, 3, NULL, 3, 39.99);

-- Sample orders
INSERT INTO orders (user_id, total, status, shipping_address_id, payment_method) VALUES
(1, 129.97, 'delivered', 1, 'credit_card'),
(2, 109.97, 'shipped', 3, 'paypal'),
(3, 84.98, 'paid', 4, 'credit_card'),
(4, 199.98, 'pending', 5, 'credit_card');

-- Sample order items
INSERT INTO order_items (order_id, product_id, variant_id, qty, unit_price) VALUES
(1, 1, 2, 2, 24.99),
(1, 13, 18, 1, 79.99),
(2, 11, NULL, 1, 69.99),
(2, 21, 25, 2, 19.99),
(3, 2, 13, 1, 59.99),
(3, 3, NULL, 1, 24.99),
(4, 6, NULL, 1, 89.99),
(4, 17, NULL, 1, 109.99);
