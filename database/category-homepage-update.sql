-- Add short_description field to categories table for homepage display
-- This allows separate short text for homepage category cards

ALTER TABLE categories 
ADD COLUMN IF NOT EXISTS short_description VARCHAR(255) NULL AFTER description;

-- Update existing categories to use description as short_description if short_description is empty
UPDATE categories 
SET short_description = SUBSTRING(description, 1, 100) 
WHERE short_description IS NULL AND description IS NOT NULL AND description != '';
