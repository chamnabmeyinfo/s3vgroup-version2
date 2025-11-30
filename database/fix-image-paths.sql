-- Fix Image Paths - Remove localhost URLs from database
-- Run this in phpMyAdmin after importing database to cPanel
-- 
-- This updates all image paths to remove localhost URLs and keep only filenames
-- Example: http://localhost:8080/storage/uploads/img_692b6e850a1386.91083690.png
-- Becomes: img_692b6e850a1386.91083690.png

-- 1. Fix products.image field
UPDATE products 
SET image = TRIM(LEADING '/' FROM 
    REPLACE(
        REPLACE(
            REPLACE(
                REPLACE(image, 'http://localhost:8080/storage/uploads/', ''),
                'http://localhost:8080/', ''),
            'https://localhost:8080/storage/uploads/', ''),
        'storage/uploads/', '')
    )
WHERE image LIKE '%localhost%' 
   OR image LIKE '%storage/uploads%';

-- 2. Fix categories.image field
UPDATE categories 
SET image = TRIM(LEADING '/' FROM 
    REPLACE(
        REPLACE(
            REPLACE(
                REPLACE(image, 'http://localhost:8080/storage/uploads/', ''),
                'http://localhost:8080/', ''),
            'https://localhost:8080/storage/uploads/', ''),
        'storage/uploads/', '')
    )
WHERE image LIKE '%localhost%' 
   OR image LIKE '%storage/uploads%';

-- 3. Fix product_variants.image field (if table exists)
UPDATE product_variants 
SET image = TRIM(LEADING '/' FROM 
    REPLACE(
        REPLACE(
            REPLACE(
                REPLACE(image, 'http://localhost:8080/storage/uploads/', ''),
                'http://localhost:8080/', ''),
            'https://localhost:8080/storage/uploads/', ''),
        'storage/uploads/', '')
    )
WHERE image LIKE '%localhost%' 
   OR image LIKE '%storage/uploads%';

-- Note: For products.gallery (JSON field), you'll need to use the PHP script
-- as JSON requires more complex processing

