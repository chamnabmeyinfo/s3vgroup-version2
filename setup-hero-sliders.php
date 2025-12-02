<?php
/**
 * Setup Hero Sliders Table
 * Run this file once to create the hero_sliders table
 */

require_once __DIR__ . '/bootstrap/app.php';

use App\Database\Connection;

try {
    $db = Connection::getInstance();
    
    // Read SQL file
    $sqlFile = __DIR__ . '/database/create-hero-sliders-table.sql';
    
    if (!file_exists($sqlFile)) {
        die("Error: SQL file not found: $sqlFile\n");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $db->exec($statement);
            $successCount++;
        } catch (\Exception $e) {
            // Ignore "table already exists" errors
            if (strpos($e->getMessage(), 'already exists') === false) {
                echo "Error executing statement: " . $e->getMessage() . "\n";
                $errorCount++;
            }
        }
    }
    
    echo "Hero Sliders setup completed!\n";
    echo "Successfully executed: $successCount statements\n";
    if ($errorCount > 0) {
        echo "Errors: $errorCount\n";
    }
    
    // Verify table exists
    try {
        $count = $db->fetchOne("SELECT COUNT(*) as count FROM hero_sliders")['count'] ?? 0;
        echo "Hero sliders in database: $count\n";
    } catch (\Exception $e) {
        echo "Warning: Could not verify table: " . $e->getMessage() . "\n";
    }
    
} catch (\Exception $e) {
    die("Fatal error: " . $e->getMessage() . "\n");
}

