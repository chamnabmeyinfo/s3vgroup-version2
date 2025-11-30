<?php
/**
 * Direct Setup Script - Creates tables directly in PHP (More Reliable)
 * Access via: http://localhost:8080/setup-direct.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';
$dbConfig = require __DIR__ . '/config/database.php';

$errors = [];
$messages = [];
$pdo = null;

// Step 1: Create database and connect
try {
    $pdo = new PDO("mysql:host={$dbConfig['host']}", $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS {$dbConfig['dbname']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $messages[] = "✓ Database '{$dbConfig['dbname']}' created/verified";
    
    $pdo->exec("USE {$dbConfig['dbname']}");
    
} catch (PDOException $e) {
    $errors[] = "Database error: " . $e->getMessage();
    die(json_encode(['error' => $e->getMessage()]));
}

if ($pdo) {
    // Create tables directly
    $tables = [
        'categories' => "
            CREATE TABLE IF NOT EXISTS categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                description TEXT,
                image VARCHAR(255),
                parent_id INT NULL,
                sort_order INT DEFAULT 0,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_slug (slug),
                INDEX idx_parent (parent_id),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'products' => "
            CREATE TABLE IF NOT EXISTS products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                sku VARCHAR(100),
                description TEXT,
                short_description TEXT,
                price DECIMAL(10,2),
                sale_price DECIMAL(10,2) NULL,
                category_id INT,
                image VARCHAR(255),
                gallery TEXT,
                specifications JSON,
                features TEXT,
                stock_status ENUM('in_stock', 'out_of_stock', 'on_order') DEFAULT 'in_stock',
                weight DECIMAL(10,2),
                dimensions VARCHAR(100),
                is_featured TINYINT(1) DEFAULT 0,
                is_active TINYINT(1) DEFAULT 1,
                view_count INT DEFAULT 0,
                meta_title VARCHAR(255),
                meta_description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_slug (slug),
                INDEX idx_category (category_id),
                INDEX idx_featured (is_featured),
                INDEX idx_active (is_active),
                INDEX idx_sku (sku),
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'quote_requests' => "
            CREATE TABLE IF NOT EXISTS quote_requests (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                phone VARCHAR(50),
                company VARCHAR(255),
                product_id INT NULL,
                message TEXT,
                status ENUM('pending', 'contacted', 'quoted', 'closed') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_status (status),
                INDEX idx_product (product_id),
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'contact_messages' => "
            CREATE TABLE IF NOT EXISTS contact_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                phone VARCHAR(50),
                subject VARCHAR(255),
                message TEXT NOT NULL,
                is_read TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_read (is_read)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'settings' => "
            CREATE TABLE IF NOT EXISTS settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                `key` VARCHAR(255) NOT NULL UNIQUE,
                value TEXT,
                type VARCHAR(50) DEFAULT 'text',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_key (`key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'admin_users' => "
            CREATE TABLE IF NOT EXISTS admin_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(100) NOT NULL UNIQUE,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                name VARCHAR(255),
                is_active TINYINT(1) DEFAULT 1,
                last_login TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_username (username),
                INDEX idx_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        "
    ];
    
    // Create tables
    foreach ($tables as $tableName => $sql) {
        try {
            $pdo->exec($sql);
            $messages[] = "✓ Table '$tableName' created";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') === false) {
                $errors[] = "Error creating table '$tableName': " . $e->getMessage();
            } else {
                $messages[] = "✓ Table '$tableName' already exists";
            }
        }
    }
    
    // Insert default admin user
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM admin_users WHERE username = 'admin'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] == 0) {
            $passwordHash = password_hash('admin', PASSWORD_DEFAULT);
            $pdo->exec("INSERT INTO admin_users (username, email, password, name) 
                       VALUES ('admin', 'admin@example.com', '$passwordHash', 'Administrator')");
            $messages[] = "✓ Admin user created (username: admin, password: admin)";
        } else {
            $messages[] = "✓ Admin user already exists";
        }
    } catch (PDOException $e) {
        $errors[] = "Error creating admin user: " . $e->getMessage();
    }
    
    // Insert default categories
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] == 0) {
            $categories = [
                ['Forklifts', 'forklifts', 'Industrial forklifts for material handling'],
                ['Pallet Trucks', 'pallet-trucks', 'Manual and electric pallet trucks'],
                ['Stackers', 'stackers', 'Stacking equipment for warehouses'],
                ['Reach Trucks', 'reach-trucks', 'Reach trucks for narrow aisles'],
                ['Trolleys', 'trolleys', 'Transport trolleys and carts'],
                ['Lifting Equipment', 'lifting-equipment', 'Cranes and lifting solutions']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
            foreach ($categories as $cat) {
                $stmt->execute($cat);
            }
            $messages[] = "✓ Default categories created";
        } else {
            $messages[] = "✓ Categories already exist";
        }
    } catch (PDOException $e) {
        // Ignore if categories already exist
    }
    
    // Insert default settings
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM settings");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] == 0) {
            $settings = [
                ['site_name', 'Forklift & Equipment Pro', 'text'],
                ['site_email', 'info@example.com', 'text'],
                ['site_phone', '+1 (555) 123-4567', 'text'],
                ['site_address', '123 Industrial Way, City, State 12345', 'text'],
                ['footer_text', '© 2024 Forklift & Equipment Pro. All rights reserved.', 'text']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO settings (`key`, value, type) VALUES (?, ?, ?)");
            foreach ($settings as $setting) {
                $stmt->execute($setting);
            }
            $messages[] = "✓ Default settings created";
        }
    } catch (PDOException $e) {
        // Ignore
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
            <h1 class="text-3xl font-bold mb-6 text-center">Setup Complete!</h1>
            
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
                    <h2 class="font-bold mb-2">Next Steps:</h2>
                    <ol class="list-decimal list-inside space-y-1 text-sm">
                        <li>Visit <a href="index.php" class="text-blue-600 underline">Homepage</a></li>
                        <li>Login to <a href="admin/login.php" class="text-blue-600 underline">Admin Panel</a> (admin / admin)</li>
                        <li>Change the admin password immediately!</li>
                        <li>Add products and customize your site</li>
                    </ol>
                </div>
            <?php endif; ?>
            
            <div class="flex space-x-4">
                <a href="index.php" class="flex-1 bg-blue-600 text-white text-center px-6 py-3 rounded hover:bg-blue-700">
                    Go to Homepage
                </a>
                <a href="admin/login.php" class="flex-1 bg-gray-600 text-white text-center px-6 py-3 rounded hover:bg-gray-700">
                    Admin Panel
                </a>
            </div>
        </div>
    </div>
</body>
</html>

