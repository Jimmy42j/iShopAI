-- Custom Product Images Update
-- Replace these URLs with your favorite images!

-- Clear existing images first (optional)
-- DELETE FROM product_images;

-- Update specific products with your favorite images
-- Just replace the URLs with your preferred ones

-- Example: Update Men's Classic T-Shirt (Product ID: 1)
UPDATE product_images 
SET url = 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400&h=400&fit=crop&crop=center&auto=format&q=80'
WHERE product_id = 1 AND is_primary = 1;

-- Example: Update Women's Floral Dress (Product ID: 11)
UPDATE product_images 
SET url = 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=400&h=400&fit=crop&crop=center&auto=format&q=80'
WHERE product_id = 11 AND is_primary = 1;

-- Example: Update Kids' Dinosaur T-Shirt (Product ID: 21)
UPDATE product_images 
SET url = 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=400&h=400&fit=crop&crop=center&auto=format&q=80'
WHERE product_id = 21 AND is_primary = 1;

-- Add more updates as needed...
-- Template for adding new images:
-- UPDATE product_images 
-- SET url = 'YOUR_FAVORITE_IMAGE_URL_HERE'
-- WHERE product_id = PRODUCT_ID_NUMBER AND is_primary = 1;

-- To see all products and their current images:
SELECT 
    p.id,
    p.name,
    pi.url as current_image_url
FROM products p
LEFT JOIN product_images pi ON p.id = pi.product_id
WHERE pi.is_primary = 1
ORDER BY p.id;

