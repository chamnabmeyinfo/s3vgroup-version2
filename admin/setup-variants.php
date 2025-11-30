<?php
/**
 * Setup Product Variants System
 * Creates product_variants and product_variant_attributes tables
 */
require_once __DIR__ . '/../bootstrap/app.php';

// Allow access for initial setup
$requireAuth = true;
try {
    db()->fetchOne("SELECT 1 FROM admin_users LIMIT 1");
} catch (Exception $e) {
    $requireAuth = false;
}

if ($requireAuth) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];
    
    if (!$isLoggedIn) {
        header('Location: ' . url('admin/login.php'));
        exit;
    }
    
    require_once __DIR__ . '/includes/auth.php';
} else {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!function_exists('hasPermission')) {
        function hasPermission($permissionSlug) {
            return true;
        }
    }
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_setup'])) {
    try {
        $db = db();
        $pdo = $db->getPdo();
        
        $tablesCreated = 0;
        
        // Create product_variants table
        try {
            $db->fetchOne("SELECT 1 FROM product_variants LIMIT 1");
            $message = "Product variants table already exists.";
        } catch (Exception $e) {
            $pdo->exec("CREATE TABLE IF NOT EXISTS product_variants (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                sku VARCHAR(100) UNIQUE,
                name VARCHAR(255),
                price DECIMAL(10,2),
                sale_price DECIMAL(10,2) NULL,
                stock_quantity INT DEFAULT 0,
                stock_status ENUM('in_stock', 'out_of_stock', 'on_order') DEFAULT 'in_stock',
                image VARCHAR(255),
                weight DECIMAL(10,2) NULL,
                is_active TINYINT(1) DEFAULT 1,
                sort_order INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_product (product_id),
                INDEX idx_sku (sku),
                INDEX idx_active (is_active),
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $message = "Product variants table created successfully!";
            $tablesCreated++;
        }
        
        // Create product_variant_attributes table
        try {
            $db->fetchOne("SELECT 1 FROM product_variant_attributes LIMIT 1");
            $message .= " Product variant attributes table already exists.";
        } catch (Exception $e) {
            $pdo->exec("CREATE TABLE IF NOT EXISTS product_variant_attributes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                variant_id INT NOT NULL,
                attribute_name VARCHAR(100) NOT NULL,
                attribute_value VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_variant (variant_id),
                FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $message .= " Product variant attributes table created successfully!";
            $tablesCreated++;
        }
        
        if ($tablesCreated > 0) {
            $message = "Setup complete! Created {$tablesCreated} table(s).";
        }
        
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

$pageTitle = 'Setup Product Variants';
include __DIR__ . '/includes/header.php';
?>

<div class="p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Setup Product Variants System</h1>
        <p class="text-gray-600 mt-2">This will create the necessary database tables for product variants.</p>
    </div>
    
    <?php if ($message): ?>
    <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
        <?= escape($message) ?>
    </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
        <?= escape($error) ?>
    </div>
    <?php endif; ?>
    
    <!-- Check Current Status -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Current Status</h2>
        
        <?php
        $db = db();
        $tables = [
            'product_variants' => 'Product Variants Table',
            'product_variant_attributes' => 'Variant Attributes Table'
        ];
        
        $missingTables = [];
        
        foreach ($tables as $table => $label) {
            try {
                $db->fetchOne("SELECT 1 FROM {$table} LIMIT 1");
                echo "<div class='mb-2 text-green-600'>✓ {$label} - Exists</div>";
            } catch (Exception $e) {
                $missingTables[] = $table;
                echo "<div class='mb-2 text-red-600'>✗ {$label} - Missing</div>";
            }
        }
        ?>
    </div>
    
    <?php if (!empty($missingTables)): ?>
    <form method="POST" class="mb-6">
        <button type="submit" name="run_setup" class="btn-primary">
            <i class="fas fa-database mr-2"></i>
            Create Missing Tables
        </button>
    </form>
    <?php else: ?>
    <div class="bg-green-50 border border-green-200 rounded-lg p-6">
        <h3 class="font-bold text-green-900 mb-2">
            <i class="fas fa-check-circle mr-2"></i>
            All Tables Exist
        </h3>
        <p class="text-green-800">
            Product variants system is ready to use!
        </p>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

