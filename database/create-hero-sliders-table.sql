-- Hero Sliders Table
CREATE TABLE IF NOT EXISTS `hero_sliders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `subtitle` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `image_mobile` varchar(255) DEFAULT NULL,
  `button_text_1` varchar(100) DEFAULT NULL,
  `button_link_1` varchar(255) DEFAULT NULL,
  `button_text_2` varchar(100) DEFAULT NULL,
  `button_link_2` varchar(255) DEFAULT NULL,
  `overlay_color` varchar(50) DEFAULT 'rgba(30, 58, 138, 0.9)',
  `overlay_gradient` varchar(255) DEFAULT NULL,
  `text_alignment` enum('left','center','right') DEFAULT 'center',
  `text_color` varchar(50) DEFAULT '#ffffff',
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data
INSERT INTO `hero_sliders` (`title`, `subtitle`, `description`, `image`, `button_text_1`, `button_link_1`, `button_text_2`, `button_link_2`, `overlay_color`, `text_alignment`, `sort_order`, `is_active`) VALUES
('Premium Forklifts & Industrial Equipment', 'Quality equipment for your warehouse and factory needs', 'Trusted by industry leaders worldwide', 'https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=1920', 'Browse Products', '/products.php', 'Get a Quote', '/quote.php', 'rgba(30, 58, 138, 0.9)', 'center', 1, 1),
('Industrial Solutions for Every Need', 'From forklifts to pallet trucks', 'We have the equipment to power your operations', 'https://images.unsplash.com/photo-1565793298595-6a879b1d9492?w=1920', 'Shop Now', '/products.php', 'Contact Us', '/contact.php', 'rgba(30, 58, 138, 0.9)', 'center', 2, 1),
('Expert Service & Support', 'Our team of experts is here to help', 'Find the perfect equipment for your business', 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=1920', 'Explore Products', '/products.php', 'Request Quote', '/quote.php', 'rgba(30, 58, 138, 0.9)', 'center', 3, 1);

