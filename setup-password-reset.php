<?php
/**
 * Setup Password Reset Feature
 * Run this file once to create the password_reset_tokens table
 */

require_once __DIR__ . '/bootstrap/app.php';

echo "Setting up Password Reset Feature...\n\n";

try {
    // Read SQL file
    $sqlFile = __DIR__ . '/database/password-reset.sql';
    
    if (!file_exists($sqlFile)) {
        die("Error: SQL file not found at: {$sqlFile}\n");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split by semicolon and execute each statement
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    foreach ($statements as $statement) {
        if (!empty(trim($statement))) {
            try {
                db()->query($statement);
                echo "✓ Executed SQL statement\n";
            } catch (\Exception $e) {
                // Check if table already exists
                if (strpos($e->getMessage(), 'already exists') !== false) {
                    echo "ℹ Table already exists, skipping...\n";
                } else {
                    throw $e;
                }
            }
        }
    }
    
    echo "\n✅ Password reset feature setup completed successfully!\n";
    echo "\nYou can now:\n";
    echo "1. Use 'Forgot Password' link on login page\n";
    echo "2. Send password reset emails from Users page\n";
    echo "3. Reset passwords via email link\n\n";
    
} catch (\Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "\nPlease check your database connection and try again.\n";
    exit(1);
}

