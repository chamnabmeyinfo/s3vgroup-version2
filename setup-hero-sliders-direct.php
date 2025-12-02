<?php
/**
 * Direct Setup Hero Sliders Table
 * Creates the table directly using PDO
 */

require_once __DIR__ . '/bootstrap/app.php';

use App\Database\Connection;

try {
    $db = Connection::getInstance();
    
    // Create table SQL
    $createTableSQL = "
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
    ";
    
    // Execute create table using PDO directly
    $pdo = $db->getPdo();
    $pdo->exec($createTableSQL);
    echo "✓ Table 'hero_sliders' created successfully!\n";
    
    // Check if table is empty, insert sample data
    $count = $db->fetchOne("SELECT COUNT(*) as count FROM hero_sliders")['count'] ?? 0;
    
    if ($count == 0) {
        echo "✓ Table is empty, inserting sample data...\n";
        
        $sampleData = [
            [
                'title' => 'Premium Forklifts & Industrial Equipment',
                'subtitle' => 'Quality equipment for your warehouse and factory needs',
                'description' => 'Trusted by industry leaders worldwide',
                'image' => 'https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=1920',
                'button_text_1' => 'Browse Products',
                'button_link_1' => '/products.php',
                'button_text_2' => 'Get a Quote',
                'button_link_2' => '/quote.php',
                'overlay_color' => 'rgba(30, 58, 138, 0.9)',
                'text_alignment' => 'center',
                'sort_order' => 1,
                'is_active' => 1
            ],
            [
                'title' => 'Industrial Solutions for Every Need',
                'subtitle' => 'From forklifts to pallet trucks',
                'description' => 'We have the equipment to power your operations',
                'image' => 'https://images.unsplash.com/photo-1565793298595-6a879b1d9492?w=1920',
                'button_text_1' => 'Shop Now',
                'button_link_1' => '/products.php',
                'button_text_2' => 'Contact Us',
                'button_link_2' => '/contact.php',
                'overlay_color' => 'rgba(30, 58, 138, 0.9)',
                'text_alignment' => 'center',
                'sort_order' => 2,
                'is_active' => 1
            ],
            [
                'title' => 'Expert Service & Support',
                'subtitle' => 'Our team of experts is here to help',
                'description' => 'Find the perfect equipment for your business',
                'image' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=1920',
                'button_text_1' => 'Explore Products',
                'button_link_1' => '/products.php',
                'button_text_2' => 'Request Quote',
                'button_link_2' => '/quote.php',
                'overlay_color' => 'rgba(30, 58, 138, 0.9)',
                'text_alignment' => 'center',
                'sort_order' => 3,
                'is_active' => 1
            ]
        ];
        
        foreach ($sampleData as $data) {
            $db->insert('hero_sliders', $data);
        }
        
        echo "✓ Sample data inserted successfully!\n";
    } else {
        echo "✓ Table already has $count slider(s).\n";
    }
    
    echo "\n✅ Hero Sliders setup completed successfully!\n";
    echo "You can now access: admin/hero-sliders.php\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

