<?php
/**
 * Add Hero Slider Options
 * Run this to add new customization options to hero sliders
 */

require_once __DIR__ . '/bootstrap/app.php';

use App\Database\Connection;

try {
    $db = Connection::getInstance();
    $pdo = $db->getPdo();
    
    // Read SQL file
    $sqlFile = __DIR__ . '/database/add-hero-slider-options.sql';
    
    if (!file_exists($sqlFile)) {
        die("Error: SQL file not found: $sqlFile\n");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Execute the ALTER TABLE statement
    try {
        $pdo->exec($sql);
        echo "✓ Hero slider options added successfully!\n";
        echo "✓ New columns: background_size, background_position, parallax_effect, animation_speed, slide_height, custom_height, content_animation\n";
    } catch (\Exception $e) {
        // Check if columns already exist
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "✓ Columns already exist. No changes needed.\n";
        } else {
            throw $e;
        }
    }
    
    // Verify columns exist
    $columns = $pdo->query("SHOW COLUMNS FROM hero_sliders LIKE 'background_size'")->fetchAll();
    if (count($columns) > 0) {
        echo "✓ Verification: New columns are present in the table.\n";
    }
    
    echo "\n✅ Hero Slider options setup completed!\n";
    
} catch (\Exception $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}

