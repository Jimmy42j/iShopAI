-- Fix Wool Sweater Image with a proper sweater photo

-- Update Wool Sweater (Product ID: 6) with a better image
UPDATE product_images 
SET url = 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?w=400&h=400&fit=crop&crop=center&auto=format&q=80'
WHERE product_id = 6 AND is_primary = 1;

-- Alternative sweater images you can use:
-- Cozy wool sweater: https://images.unsplash.com/photo-1544966503-7cc5ac882d5f?w=400&h=400&fit=crop&crop=center&auto=format&q=80
-- Gray sweater: https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=400&fit=crop&crop=center&auto=format&q=80
-- Men's sweater: https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=400&h=400&fit=crop&crop=center&auto=format&q=80

-- Check the updated image
SELECT 
    p.id,
    p.name,
    pi.url
FROM products p
LEFT JOIN product_images pi ON p.id = pi.product_id
WHERE p.id = 6 AND pi.is_primary = 1;
