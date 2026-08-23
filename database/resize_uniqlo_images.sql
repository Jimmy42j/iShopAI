-- Resize Uniqlo Images to Consistent Size
-- This script adds URL parameters to make all images the same size

-- Update all Uniqlo images to have consistent sizing parameters
UPDATE product_images 
SET url = CONCAT(
    SUBSTRING_INDEX(url, '?', 1), 
    '?w=400&h=400&fit=crop&crop=center&auto=format&q=80'
)
WHERE url LIKE '%uniqlo.com%' AND is_primary = 1;

-- Alternative: If your images are from other sources, you can use this template
-- UPDATE product_images 
-- SET url = 'https://images.unsplash.com/photo-YOUR_PHOTO_ID?w=400&h=400&fit=crop&crop=center&auto=format&q=80'
-- WHERE product_id = YOUR_PRODUCT_ID AND is_primary = 1;

-- Check the updated URLs
SELECT 
    p.id,
    p.name,
    pi.url
FROM products p
LEFT JOIN product_images pi ON p.id = pi.product_id
WHERE pi.is_primary = 1
ORDER BY p.id;

