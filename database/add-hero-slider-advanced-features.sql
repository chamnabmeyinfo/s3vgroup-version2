-- Add Advanced Features to Hero Slider
-- This migration adds all the new fields for advanced hero slider features

ALTER TABLE `hero_slides` 
ADD COLUMN `transition_effect` VARCHAR(50) DEFAULT 'fade' AFTER `content_transparency`,
ADD COLUMN `video_background` VARCHAR(255) DEFAULT NULL AFTER `background_image`,
ADD COLUMN `video_poster` VARCHAR(255) DEFAULT NULL AFTER `video_background`,
ADD COLUMN `template` VARCHAR(50) DEFAULT 'default' AFTER `video_poster`,
ADD COLUMN `image_mobile` VARCHAR(255) DEFAULT NULL AFTER `template`,
ADD COLUMN `image_tablet` VARCHAR(255) DEFAULT NULL AFTER `image_mobile`,
ADD COLUMN `scheduled_start` DATETIME DEFAULT NULL AFTER `image_tablet`,
ADD COLUMN `scheduled_end` DATETIME DEFAULT NULL AFTER `scheduled_start`,
ADD COLUMN `text_animation` VARCHAR(50) DEFAULT 'fadeInUp' AFTER `scheduled_end`,
ADD COLUMN `parallax_enabled` TINYINT(1) DEFAULT 0 AFTER `text_animation`,
ADD COLUMN `content_layout` VARCHAR(50) DEFAULT 'center' AFTER `parallax_enabled`,
ADD COLUMN `overlay_pattern` VARCHAR(50) DEFAULT NULL AFTER `content_layout`,
ADD COLUMN `button1_style` VARCHAR(50) DEFAULT 'primary' AFTER `button2_url`,
ADD COLUMN `button2_style` VARCHAR(50) DEFAULT 'secondary' AFTER `button1_style`,
ADD COLUMN `social_sharing` TINYINT(1) DEFAULT 0 AFTER `button2_style`,
ADD COLUMN `countdown_enabled` TINYINT(1) DEFAULT 0 AFTER `social_sharing`,
ADD COLUMN `countdown_date` DATETIME DEFAULT NULL AFTER `countdown_enabled`,
ADD COLUMN `badge_text` VARCHAR(50) DEFAULT NULL AFTER `countdown_date`,
ADD COLUMN `badge_color` VARCHAR(50) DEFAULT 'blue' AFTER `badge_text`,
ADD COLUMN `mobile_title` VARCHAR(255) DEFAULT NULL AFTER `badge_color`,
ADD COLUMN `mobile_description` TEXT DEFAULT NULL AFTER `mobile_title`,
ADD COLUMN `custom_font` VARCHAR(100) DEFAULT NULL AFTER `mobile_description`,
ADD COLUMN `slide_group` VARCHAR(50) DEFAULT NULL AFTER `custom_font`,
ADD COLUMN `ab_test_variant` VARCHAR(50) DEFAULT NULL AFTER `slide_group`,
ADD COLUMN `auto_height` TINYINT(1) DEFAULT 0 AFTER `ab_test_variant`,
ADD COLUMN `dark_mode` TINYINT(1) DEFAULT 0 AFTER `auto_height`;

-- Add indexes for performance
ALTER TABLE `hero_slides`
ADD INDEX `idx_scheduled` (`scheduled_start`, `scheduled_end`),
ADD INDEX `idx_slide_group` (`slide_group`),
ADD INDEX `idx_template` (`template`);

-- Update existing slides with default values
UPDATE `hero_slides` SET 
    `transition_effect` = 'fade',
    `template` = 'default',
    `content_layout` = 'center',
    `button1_style` = 'primary',
    `button2_style` = 'secondary'
WHERE `transition_effect` IS NULL;

