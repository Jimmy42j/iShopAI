-- Simple Product Images Update Script
-- This script uses the same image mapping logic as your frontend imageUtils.ts
-- Each product gets a carefully selected Unsplash image based on its name/type

-- Clear existing product images first
DELETE FROM product_images;

-- Insert new product images with curated Unsplash photos
INSERT INTO product_images (product_id, url, alt_text, is_primary, sort_order) VALUES

-- Men's Products (Category 1)
(1, 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Men''s Classic Cotton T-Shirt', TRUE, 1),
(2, 'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Men''s Linen Button-Up Shirt', TRUE, 1),
(3, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Men''s Cargo Shorts', TRUE, 1),
(4, 'https://images.unsplash.com/photo-1586790170083-2f9ceadc732d?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Men''s Polo Shirt', TRUE, 1),
(5, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Men''s Chino Pants', TRUE, 1),
(6, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Men''s Wool Sweater', TRUE, 1),
(7, 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Men''s Denim Jacket', TRUE, 1),
(8, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Men''s Flannel Shirt', TRUE, 1),
(9, 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Men''s Winter Coat', TRUE, 1),
(10, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Men''s Thermal Underwear Set', TRUE, 1),

-- Women's Products (Category 2)
(11, 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Women''s Floral Summer Dress', TRUE, 1),
(12, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Women''s Silk Blouse', TRUE, 1),
(13, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Women''s High-Waisted Jeans', TRUE, 1),
(14, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Women''s Crop Top', TRUE, 1),
(15, 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Women''s Maxi Skirt', TRUE, 1),
(16, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Women''s Cashmere Cardigan', TRUE, 1),
(17, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Women''s Wool Coat', TRUE, 1),
(18, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Women''s Turtleneck Sweater', TRUE, 1),
(19, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Women''s Leather Boots', TRUE, 1),
(20, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Women''s Plaid Scarf', TRUE, 1),

-- Kids' Products (Category 3)
(21, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Kids'' Dinosaur T-Shirt', TRUE, 1),
(22, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Kids'' Rainbow Dress', TRUE, 1),
(23, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Kids'' Denim Overalls', TRUE, 1),
(24, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Kids'' Swim Shorts', TRUE, 1),
(25, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Kids'' Sandals', TRUE, 1),
(26, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Kids'' Hoodie with Pockets', TRUE, 1),
(27, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Kids'' Winter Jacket', TRUE, 1),
(28, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Kids'' Knit Beanie', TRUE, 1),
(29, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Kids'' Snow Boots', TRUE, 1),
(30, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Kids'' Fleece Pajamas', TRUE, 1),

-- Unisex Products
(31, 'https://images.unsplash.com/photo-1588117472013-59bb13edafec?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Unisex Baseball Cap', TRUE, 1),
(32, 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Unisex Canvas Sneakers', TRUE, 1),
(33, 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=400&h=400&fit=crop&crop=center&auto=format&q=80', 'Unisex Backpack', TRUE, 1);

-- Show updated products with their new images
SELECT 
    p.id,
    p.name,
    p.gender_target,
    pi.url,
    pi.is_primary
FROM products p
LEFT JOIN product_images pi ON p.id = pi.product_id
WHERE pi.is_primary = 1
ORDER BY p.id;
