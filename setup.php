<?php
/**
 * Setup Script - Run this once to set up the database
 * Access via: http://localhost:8080/setup.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database config
$host = 'localhost';
$dbname = 'forklift_equipment';
$username = 'root';
$password = '';

$errors = [];
$messages = [];
$pdo = null;

// Step 1: Create database
try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $messages[] = "✓ Database '$dbname' created successfully";
    
    // Use the database
    $pdo->exec("USE $dbname");
    
} catch (PDOException $e) {
    $errors[] = "Database connection error: " . $e->getMessage();
}

// Step 2: Import schema
if (empty($errors) && $pdo) {
    $schemaFile = __DIR__ . '/database/schema.sql';
    
    if (file_exists($schemaFile)) {
        $sql = file_get_contents($schemaFile);
        
        // Remove CREATE DATABASE and USE statements
        $sql = preg_replace('/CREATE DATABASE[^;]*;/i', '', $sql);
        $sql = preg_replace('/USE[^;]*;/i', '', $sql);
        
        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // Split into individual statements
        // Better approach: split by semicolon but keep track of parentheses
        $statements = [];
        $current = '';
        $depth = 0;
        $inString = false;
        $stringChar = '';
        
        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];
            $nextChar = ($i < strlen($sql) - 1) ? $sql[$i + 1] : '';
            
            // Track string boundaries
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
                    if (!empty($stmt)) {
                        $statements[] = $stmt;
                    }
                    $current = '';
                    continue;
                }
            }
            
            $current .= $char;
        }
        
        // Add last statement if any
        $last = trim($current);
        if (!empty($last)) {
            $statements[] = $last;
        }
        
        $executed = 0;
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement) || strlen($statement) < 10) {
                continue;
            }
            
            try {
                $pdo->exec($statement);
                $executed++;
            } catch (PDOException $e) {
                // Only ignore "already exists" errors, show others
                if (strpos($e->getMessage(), 'already exists') === false && 
                    strpos($e->getMessage(), 'Duplicate') === false) {
                    $errors[] = "SQL Error: " . $e->getMessage() . " (Statement: " . substr($statement, 0, 50) . "...)";
                }
            }
        }
        
        $messages[] = "✓ Executed $executed SQL statements";
        
    } else {
        $errors[] = "Schema file not found: $schemaFile";
    }
}

// Step 3: Verify tables
if (empty($errors) && $pdo) {
    $tables = ['categories', 'products', 'quote_requests', 'contact_messages', 'settings', 'admin_users'];
    $existingTables = [];
    
    try {
        $stmt = $pdo->query("SHOW TABLES");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $existingTables[] = $row[0];
        }
        
        foreach ($tables as $table) {
            if (in_array($table, $existingTables)) {
                $messages[] = "✓ Table '$table' exists";
            } else {
                $errors[] = "✗ Table '$table' is missing";
            }
        }
    } catch (PDOException $e) {
        $errors[] = "Error checking tables: " . $e->getMessage();
    }
}

// Step 4: Check admin user
if (empty($errors) && $pdo) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM admin_users");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            $messages[] = "✓ Admin user exists (username: admin, password: admin)";
        } else {
            $errors[] = "✗ Admin user not found";
        }
    } catch (PDOException $e) {
        $errors[] = "Error checking admin user: " . $e->getMessage();
    }
}

// Step 5: Check categories
if (empty($errors) && $pdo) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            $messages[] = "✓ Default categories created ($result[count] categories)";
        }
    } catch (PDOException $e) {
        // Ignore if table doesn't exist yet
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Forklift & Equipment Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-2xl w-full bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold mb-6 text-center">Setup <?= empty($errors) ? 'Complete!' : 'Status' ?></h1>
            
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
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-4">
                        <p class="text-sm text-yellow-800">
                            <strong>If tables are missing:</strong> Try running this setup page again, or manually import the SQL file through phpMyAdmin.
                        </p>
                        <p class="text-sm text-yellow-800 mt-2">
                            SQL file location: <code>database/schema.sql</code>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (empty($errors)): ?>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                    <h2 class="font-bold mb-2">Next Steps:</h2>
                    <ol class="list-decimal list-inside space-y-1 text-sm">
                        <li>Visit <a href="index.php" class="text-blue-600 underline">Homepage</a></li>
                        <li>Login to <a href="admin/login.php" class="text-blue-600 underline">Admin Panel</a> (admin / admin)</li>
                        <li>Change the admin password immediately!</li>
                        <li>Add products and customize your site</li>
                    </ol>
                </div>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-yellow-800">
                        <strong>⚠️ Security:</strong> Delete this setup.php file after setup is complete!
                    </p>
                </div>
            <?php endif; ?>
            
            <div class="flex space-x-4">
                <?php if (empty($errors)): ?>
                    <a href="index.php" class="flex-1 bg-blue-600 text-white text-center px-6 py-3 rounded hover:bg-blue-700">
                        Go to Homepage
                    </a>
                    <a href="admin/login.php" class="flex-1 bg-gray-600 text-white text-center px-6 py-3 rounded hover:bg-gray-700">
                        Admin Panel
                    </a>
                <?php else: ?>
                    <a href="setup.php" class="flex-1 bg-blue-600 text-white text-center px-6 py-3 rounded hover:bg-blue-700">
                        Try Setup Again
                    </a>
                    <a href="index.php" class="flex-1 bg-gray-300 text-gray-700 text-center px-6 py-3 rounded hover:bg-gray-400">
                        Go to Homepage (May Have Errors)
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
