-- Add transparency/opacity field to hero_slides table
ALTER TABLE `hero_slides` 
ADD COLUMN `content_transparency` DECIMAL(3,2) DEFAULT 0.10 AFTER `background_gradient_end`;

-- Update existing slides to have default transparency
UPDATE `hero_slides` SET `content_transparency` = 0.10 WHERE `content_transparency` IS NULL;

