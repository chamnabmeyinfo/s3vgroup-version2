<?php
/**
 * Advanced Features Setup Script
 * Run this to add advanced features tables
 */

require_once __DIR__ . '/config/database.php';
$dbConfig = require __DIR__ . '/config/database.php';

$errors = [];
$messages = [];
$pdo = null;

try {
    $pdo = new PDO("mysql:host={$dbConfig['host']}", $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("USE {$dbConfig['dbname']}");
} catch (PDOException $e) {
    $errors[] = "Database error: " . $e->getMessage();
}

if ($pdo) {
    $schemaFile = __DIR__ . '/database/advanced-features.sql';
    
    if (file_exists($schemaFile)) {
        $sql = file_get_contents($schemaFile);
        
        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        
        // Split into statements
        $statements = [];
        $current = '';
        $depth = 0;
        $inString = false;
        $stringChar = '';
        
        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];
            
            if (($char == '"' || $char == "'") && ($i == 0 || $sql[$i - 1] != '\\')) {
                if (!$inString) {
                    $inString = true;
                    $stringChar = $char;
                } elseif ($char == $stringChar) {
                    $inString = false;
                    $stringChar = '';
                }
            }
            
            if (!$inString) {
                if ($char == '(') $depth++;
                if ($char == ')') $depth--;
                
                if ($char == ';' && $depth == 0) {
                    $stmt = trim($current);
                    if (!empty($stmt) && strlen($stmt) > 10) {
                        $statements[] = $stmt;
                    }
                    $current = '';
                    continue;
                }
            }
            
            $current .= $char;
        }
        
        $executed = 0;
        foreach ($statements as $statement) {
            try {
                $pdo->exec($statement);
                $executed++;
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'already exists') === false) {
                    $errors[] = "Error: " . substr($statement, 0, 50) . "... - " . $e->getMessage();
                }
            }
        }
        
        $messages[] = "✓ Executed $executed SQL statements for advanced features";
    }
    
    // Verify tables
    $tables = ['product_reviews', 'newsletter_subscribers', 'wishlists', 'recently_viewed', 'product_tags', 'activity_logs'];
    foreach ($tables as $table) {
        try {
            $pdo->query("SELECT 1 FROM $table LIMIT 1");
            $messages[] = "✓ Table '$table' exists";
        } catch (PDOException $e) {
            $errors[] = "✗ Table '$table' missing";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Features Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-2xl w-full bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold mb-6 text-center">Advanced Features Setup</h1>
            
            <?php if (!empty($messages)): ?>
                <div class="mb-6 space-y-2">
                    <?php foreach ($messages as $msg): ?>
                        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
                            <?= htmlspecialchars($msg) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="mb-6 space-y-2">
                    <?php foreach ($errors as $error): ?>
                        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($errors)): ?>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                    <h2 class="font-bold mb-2">Advanced Features Ready!</h2>
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        <li>Product Reviews System</li>
                        <li>Newsletter Subscription</li>
                        <li>Wishlist Functionality</li>
                        <li>Recently Viewed Tracking</li>
                        <li>Advanced Analytics</li>
                        <li>And more!</li>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="flex space-x-4">
                <a href="<?= url('admin/index.php') ?>" class="flex-1 bg-blue-600 text-white text-center px-6 py-3 rounded hover:bg-blue-700">
                    Go to Admin
                </a>
                <a href="<?= url() ?>" class="flex-1 bg-gray-600 text-white text-center px-6 py-3 rounded hover:bg-gray-700">
                    View Website
                </a>
            </div>
        </div>
    </div>
</body>
</html>

