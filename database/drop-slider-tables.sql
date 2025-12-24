-- Drop Slider System Tables
-- Run this SQL to completely remove slider system from database

-- Drop foreign key constraint first
ALTER TABLE `slider_slides` DROP FOREIGN KEY IF EXISTS `fk_slider_slides_slider`;

-- Drop tables
DROP TABLE IF EXISTS `slider_slides`;
DROP TABLE IF EXISTS `sliders`;

-- Remove slider-related settings (optional - uncomment if you want to remove global settings too)
-- DELETE FROM `settings` WHERE `key` LIKE 'slider_%';
