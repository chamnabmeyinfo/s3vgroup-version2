-- Add new options columns to hero_sliders table
ALTER TABLE `hero_sliders` 
ADD COLUMN `background_size` enum('cover','contain','fill','stretch','auto') DEFAULT 'cover' AFTER `image_mobile`,
ADD COLUMN `background_position` varchar(50) DEFAULT 'center' AFTER `background_size`,
ADD COLUMN `parallax_effect` tinyint(1) DEFAULT 0 AFTER `background_position`,
ADD COLUMN `animation_speed` enum('slow','normal','fast') DEFAULT 'normal' AFTER `parallax_effect`,
ADD COLUMN `slide_height` enum('auto','full','custom') DEFAULT 'auto' AFTER `animation_speed`,
ADD COLUMN `custom_height` varchar(20) DEFAULT NULL AFTER `slide_height`,
ADD COLUMN `content_animation` enum('none','fade','slide-up','slide-down','zoom') DEFAULT 'fade' AFTER `custom_height`;

