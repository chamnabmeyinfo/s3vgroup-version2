-- Footer Management System
-- This table stores all footer content that can be managed from the admin panel

CREATE TABLE IF NOT EXISTS footer_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_type ENUM('company_info', 'quick_links', 'categories', 'contact', 'social_media', 'custom', 'bottom_text') NOT NULL,
    title VARCHAR(255) NULL,
    content TEXT NULL,
    link_url VARCHAR(500) NULL,
    link_text VARCHAR(255) NULL,
    icon VARCHAR(100) NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    display_order INT DEFAULT 0,
    extra_data JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_section (section_type),
    INDEX idx_active (is_active),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default footer content
INSERT INTO footer_content (section_type, title, content, sort_order, is_active, display_order) VALUES
('company_info', NULL, 'Premium industrial equipment for your business needs. Quality, reliability, and expert support.', 0, 1, 1),
('quick_links', 'Quick Links', NULL, 0, 1, 2),
('categories', 'Categories', NULL, 0, 1, 3),
('contact', 'Stay Connected', NULL, 0, 1, 4),
('social_media', NULL, NULL, 0, 1, 0),
('bottom_text', NULL, '© 2024 Forklift & Equipment Pro. All rights reserved.', 0, 1, 5);

-- Insert default quick links
INSERT INTO footer_content (section_type, title, link_url, link_text, icon, sort_order, is_active, display_order) VALUES
('quick_links', NULL, '/', 'Home', 'fas fa-home', 1, 1, 2),
('quick_links', NULL, '/products.php', 'Products', 'fas fa-box', 2, 1, 2),
('quick_links', NULL, '/blog.php', 'Blog', 'fas fa-blog', 3, 1, 2),
('quick_links', NULL, '/faq.php', 'FAQ', 'fas fa-question-circle', 4, 1, 2),
('quick_links', NULL, '/testimonials.php', 'Testimonials', 'fas fa-star', 5, 1, 2),
('quick_links', NULL, '/contact.php', 'Contact', 'fas fa-envelope', 6, 1, 2),
('quick_links', NULL, '/quote.php', 'Get Quote', 'fas fa-file-invoice', 7, 1, 2);

-- Insert default social media links
INSERT INTO footer_content (section_type, title, link_url, link_text, icon, sort_order, is_active, display_order) VALUES
('social_media', NULL, '#', 'Facebook', 'fab fa-facebook-f', 1, 1, 0),
('social_media', NULL, '#', 'Twitter', 'fab fa-twitter', 2, 1, 0),
('social_media', NULL, '#', 'Instagram', 'fab fa-instagram', 3, 1, 0),
('social_media', NULL, '#', 'LinkedIn', 'fab fa-linkedin-in', 4, 1, 0);

-- Insert default bottom links (Privacy Policy, Terms, etc.)
INSERT INTO footer_content (section_type, title, link_url, link_text, icon, sort_order, is_active, display_order) VALUES
('bottom_text', NULL, '#', 'Privacy Policy', NULL, 1, 1, 5),
('bottom_text', NULL, '#', 'Terms of Service', NULL, 2, 1, 5),
('bottom_text', NULL, '#', 'Cookie Policy', NULL, 3, 1, 5);
