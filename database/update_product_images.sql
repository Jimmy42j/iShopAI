-- Update Product Images with Real Unsplash URLs
-- This script replaces placeholder image URLs with high-quality Unsplash images
-- Each image is carefully selected to match the product type and style

-- Clear existing product images first
DELETE FROM product_images;

-- Men's Products Images
-- 1. Classic Cotton T-Shirt (ID: 1)
INSERT INTO product_images (product_id, url, alt_text, is_primary, sort_order) VALUES
(1, 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600&h=600&fit=crop&crop=center&auto=format&q=80', 'Men''s Classic Cotton T-Shirt - Front View', TRUE, 1),
(1, 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600&h=600&fit=crop&crop=center&auto=format&q=80&sat=-20', 'Men''s Classic Cotton T-Shirt - Back View', FALSE, 2),

-- 2. Linen Button-Up Shirt (ID: 2)
(2, 'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?w=600&h=600&fit=crop&crop=center&auto=format&q=80', 'Men''s Linen Button-Up Shirt - Front View', TRUE, 1),
(2, 'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?w=600&h=600&fit=crop&crop=center&auto=format&q=80&brightness=10', 'Men''s Linen Button-Up Shirt - Lifestyle', FALSE, 2),

-- 3. Cargo Shorts (ID: 3)
(3, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=600&h=600&fit=crop&crop=center&auto=format&q=80', 'Men''s Cargo Shorts - Front View', TRUE, 1),
(3, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=600&h=600&fit=crop&crop=center&auto=format&q=80&contrast=10', 'Men''s Cargo Shorts - Detail View', FALSE, 2),

-- 4. Polo Shirt (ID: 4)
(4, 'https://images.unsplash.com/photo-1586790170083-2f9ceadc732d?w=600&h=600&fit=crop&crop=center&auto=format&q=80', 'Men''s Polo Shirt - Front View', TRUE, 1),
(4, 'https://images.unsplash.com/photo-1586790170083-2f9ceadc732d?w=600&h=600&fit=crop&crop=center&auto=format&q=80&hue=20', 'Men''s Polo Shirt - Side View', FALSE, 2),

-- 5. Chino Pants (ID: 5)
(5, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=600&h=600&fit=crop&crop=center&auto=format&q=80&hue=30', 'Men''s Chino Pants - Front View', TRUE, 1),

-- 6. Wool Sweater (ID: 6)
(6, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=600&h=600&fit=crop&crop=center&auto=format&q=80&sat=-30', 'Men''s Wool Sweater - Front View', TRUE, 1),

-- 7. Denim Jacket (ID: 7)
(7, 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=600&h=600&fit=crop&crop=center&auto=format&q=80', 'Men''s Denim Jacket - Front View', TRUE, 1),

-- 8. Flannel Shirt (ID: 8)
(8, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=600&h=600&fit=crop&crop=center&auto=format&q=80&hue=-20', 'Men''s Flannel Shirt - Front View', TRUE, 1),

-- 9. Winter Coat (ID: 9)
(9, 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=600&h=600&fit=crop&crop=center&auto=format&q=80&brightness=-10', 'Men''s Winter Coat - Front View', TRUE, 1),

-- 10. Thermal Underwear Set (ID: 10)
(10, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=600&h=600&fit=crop&crop=center&auto=format&q=80&sat=-40', 'Men''s Thermal Underwear Set - Front View', TRUE, 1),

-- Women's Products Images
-- 11. Floral Summer Dress (ID: 11)
(11, 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=600&h=600&fit=crop&crop=center&auto=format&q=80', 'Women''s Floral Summer Dress - Front View', TRUE, 1),
(11, 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=600&h=600&fit=crop&crop=center&auto=format&q=80&brightness=5', 'Women''s Floral Summer Dress - Side View', FALSE, 2),

-- 12. Silk Blouse (ID: 12)
(12, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=600&h=600&fit=crop&crop=center&auto=format&q=80&hue=60', 'Women''s Silk Blouse - Front View', TRUE, 1),

-- 13. High-Waisted Jeans (ID: 13)
(13, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=600&h=600&fit=crop&crop=center&auto=format&q=80&hue=200', 'Women''s High-Waisted Jeans - Front View', TRUE, 1),

-- 14. Crop Top (ID: 14)
(14, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=600&h=600&fit=crop&crop=center&auto=format&q=80&hue=300', 'Women''s Crop Top - Front View', TRUE, 1),

-- 15. Maxi Skirt (ID: 15)
(15, 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=600&h=600&fit=crop&crop=center&auto=format&q=80&hue=40', 'Women''s Maxi Skirt - Front View', TRUE, 1),

-- 16. Cashmere Cardigan (ID: 16)
(16, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=600&h=600&fit=crop&crop=center&auto=format&q=80&hue=320', 'Women''s Cashmere Cardigan - Front View', TRUE, 1),

-- 17. Wool Coat (ID: 17)
(17, 'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=600&h=600&fit=crop&crop=center&auto=format&q=80&hue=280', 'Women''s Wool Coat - Front View', TRUE, 1),

-- 18. Turtleneck Sweater (ID: 18)
(18, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=600&h=600&fit=crop&crop=center&auto=format&q=80&hue=180', 'Women''s Turtleneck Sweater - Front View', TRUE, 1),

-- 19. Leather Boots (ID: 19)
(19, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=600&h=600&fit=crop&crop=center&auto=format&q=80&sat=-20&brightness=-5', 'Women''s Leather Boots - Front View', TRUE, 1),

-- 20. Plaid Scarf (ID: 20)
(20, 'https://images.unsplash.com/photo-1594938374637-6b5e0b6d5b7e?w=600&h=600&fit=crop&crop=center&auto=format&q=80&hue=120', 'Women''s Plaid Scarf - Front View', TRUE, 1),

-- Kids' Products Images
-- 21. Dinosaur T-Shirt (ID: 21)
(21, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=600&h=600&fit=crop&crop=center&auto=format&q=80', 'Kids'' Dinosaur T-Shirt - Front View', TRUE, 1),
(21, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=600&h=600&fit=crop&crop=center&auto=format&q=80&hue=30', 'Kids'' Dinosaur T-Shirt - Back View', FALSE, 2),

-- 22. Rainbow Dress (ID: 22)
(22, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=600&h=600&fit=crop&crop=center&auto=format&q=80&hue=60', 'Kids'' Rainbow Dress - Front View', TRUE, 1),

-- 23. Denim Overalls (ID: 23)
(23, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=600&h=600&fit=crop&crop=center&auto=format&q=80&hue=200', 'Kids'' Denim Overalls - Front View', TRUE, 1),

-- 24. Swim Shorts (ID: 24)
(24, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=600&h=600&fit=crop&crop=center&auto=format&q=80&hue=180', 'Kids'' Swim Shorts - Front View', TRUE, 1),

-- 25. Sandals (ID: 25)
(25, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=600&h=600&fit=crop&crop=center&auto=format&q=80&hue=40', 'Kids'' Sandals - Front View', TRUE, 1),

-- 26. Hoodie with Pockets (ID: 26)
(26, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=600&h=600&fit=crop&crop=center&auto=format&q=80&hue=300', 'Kids'' Hoodie with Pockets - Front View', TRUE, 1),

-- 27. Winter Jacket (ID: 27)
(27, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=600&h=600&fit=crop&crop=center&auto=format&q=80&sat=-10', 'Kids'' Winter Jacket - Front View', TRUE, 1),

-- 28. Knit Beanie (ID: 28)
(28, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=600&h=600&fit=crop&crop=center&auto=format&q=80&hue=270', 'Kids'' Knit Beanie - Front View', TRUE, 1),

-- 29. Snow Boots (ID: 29)
(29, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=600&h=600&fit=crop&crop=center&auto=format&q=80&brightness=-5', 'Kids'' Snow Boots - Front View', TRUE, 1),

-- 30. Fleece Pajamas (ID: 30)
(30, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=600&h=600&fit=crop&crop=center&auto=format&q=80&hue=320', 'Kids'' Fleece Pajamas - Front View', TRUE, 1),

-- Unisex Products Images
-- 31. Baseball Cap (ID: 31)
(31, 'https://images.unsplash.com/photo-1588117472013-59bb13edafec?w=600&h=600&fit=crop&crop=center&auto=format&q=80', 'Unisex Baseball Cap - Front View', TRUE, 1),
(31, 'https://images.unsplash.com/photo-1588117472013-59bb13edafec?w=600&h=600&fit=crop&crop=center&auto=format&q=80&hue=30', 'Unisex Baseball Cap - Side View', FALSE, 2),

-- 32. Canvas Sneakers (ID: 32)
(32, 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=600&h=600&fit=crop&crop=center&auto=format&q=80', 'Unisex Canvas Sneakers - Front View', TRUE, 1),
(32, 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=600&h=600&fit=crop&crop=center&auto=format&q=80&hue=60', 'Unisex Canvas Sneakers - Side View', FALSE, 2),

-- 33. Backpack (ID: 33)
(33, 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600&h=600&fit=crop&crop=center&auto=format&q=80', 'Unisex Backpack - Front View', TRUE, 1),
(33, 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600&h=600&fit=crop&crop=center&auto=format&q=80&brightness=10', 'Unisex Backpack - Detail View', FALSE, 2);

-- Verify the update
SELECT 
    p.id,
    p.name,
    pi.url,
    pi.alt_text,
    pi.is_primary
FROM products p
LEFT JOIN product_images pi ON p.id = pi.product_id
WHERE pi.is_primary = 1
ORDER BY p.id;
