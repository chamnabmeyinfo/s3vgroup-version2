-- Create Hero Slider Table
CREATE TABLE IF NOT EXISTS `hero_slides` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `button1_text` VARCHAR(100) DEFAULT NULL,
    `button1_url` VARCHAR(255) DEFAULT NULL,
    `button2_text` VARCHAR(100) DEFAULT NULL,
    `button2_url` VARCHAR(255) DEFAULT NULL,
    `background_image` VARCHAR(255) DEFAULT NULL,
    `background_gradient_start` VARCHAR(50) DEFAULT NULL,
    `background_gradient_end` VARCHAR(50) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `display_order` INT(11) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_active` (`is_active`),
    INDEX `idx_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default slides
INSERT INTO `hero_slides` (`title`, `description`, `button1_text`, `button1_url`, `button2_text`, `button2_url`, `background_gradient_start`, `background_gradient_end`, `is_active`, `display_order`) VALUES
('Premium Forklifts & Industrial Equipment', 'Discover our extensive range of high-quality forklifts, material handling equipment, and industrial solutions designed to power your business.', 'Shop Now', 'products.php', 'Get Quote', 'quote.php', 'rgba(37, 99, 235, 0.9)', 'rgba(79, 70, 229, 0.9)', 1, 1),
('Expert Support & Maintenance', '24/7 customer support and professional maintenance services to keep your equipment running at peak performance.', 'Contact Us', 'contact.php', 'Browse Products', 'products.php', 'rgba(16, 185, 129, 0.9)', 'rgba(5, 150, 105, 0.9)', 1, 2),
('Quality You Can Trust', 'All our equipment is thoroughly inspected and certified to meet the highest industry standards for safety and performance.', 'Explore Quality', 'products.php', 'Read Reviews', 'testimonials.php', 'rgba(139, 92, 246, 0.9)', 'rgba(124, 58, 237, 0.9)', 1, 3),
('Fast Delivery & Installation', 'Quick shipping and professional installation services to get your equipment up and running when you need it most.', 'Shop Now', 'products.php', 'Learn More', 'contact.php', 'rgba(236, 72, 153, 0.9)', 'rgba(219, 39, 119, 0.9)', 1, 4);

