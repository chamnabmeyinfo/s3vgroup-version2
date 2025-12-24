-- Add Foreign Key Constraint for Category Parent-Child Relationship
-- This ensures data integrity for sub-categories

-- Drop existing foreign key if it exists
ALTER TABLE `categories` 
DROP FOREIGN KEY IF EXISTS `fk_categories_parent`;

-- Add foreign key constraint
ALTER TABLE `categories`
ADD CONSTRAINT `fk_categories_parent` 
FOREIGN KEY (`parent_id`) 
REFERENCES `categories` (`id`) 
ON DELETE SET NULL 
ON UPDATE CASCADE;
