<?php
/**
 * Setup Orders Management System
 * Creates orders, order_items, and customers tables if they don't exist
 */
require_once __DIR__ . '/../bootstrap/app.php';

// Only require auth if admin_users table exists
$requireAuth = true;
try {
    db()->fetchOne("SELECT 1 FROM admin_users LIMIT 1");
} catch (Exception $e) {
    // admin_users doesn't exist - allow access for setup
    $requireAuth = false;
}

if ($requireAuth) {
    // Check if logged in
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];
    
    if (!$isLoggedIn) {
        header('Location: ' . url('admin/login.php'));
        exit;
    }
    
    // Load auth functions
    require_once __DIR__ . '/includes/auth.php';
} else {
    // Initialize session but don't require login
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Define stub functions to avoid errors in header
    if (!function_exists('hasPermission')) {
        function hasPermission($permissionSlug) {
            // During setup, allow all permissions
            return true;
        }
    }
    
    if (!function_exists('requirePermission')) {
        function requirePermission($permissionSlug) {
            // During setup, always allow
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
        
        $setupMessages = [];
        $setupMessages[] = "Setting up Orders Management System...\n\n";
        
        // Check if orders table exists
        $tablesCreated = 0;
        
        // Check if customers table exists first (for foreign key)
        $customersExists = false;
        try {
            $db->fetchOne("SELECT 1 FROM customers LIMIT 1");
            $customersExists = true;
        } catch (Exception $e) {
            // Customers table doesn't exist, create it first
            $pdo->exec("CREATE TABLE IF NOT EXISTS customers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                first_name VARCHAR(255),
                last_name VARCHAR(255),
                phone VARCHAR(50),
                company VARCHAR(255),
                address TEXT,
                city VARCHAR(100),
                state VARCHAR(100),
                zip_code VARCHAR(20),
                country VARCHAR(100) DEFAULT 'USA',
                is_active TINYINT(1) DEFAULT 1,
                email_verified TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                last_login TIMESTAMP NULL,
                INDEX idx_email (email),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $setupMessages[] = "✓ Created customers table";
            $tablesCreated++;
            $customersExists = true;
        }
        
        try {
            $db->fetchOne("SELECT 1 FROM orders LIMIT 1");
            $setupMessages[] = "✓ Orders table already exists";
        } catch (Exception $e) {
            // Create orders table
            $sql = "CREATE TABLE IF NOT EXISTS orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_number VARCHAR(50) NOT NULL UNIQUE,
                customer_id INT NULL,
                session_id VARCHAR(255),
                status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
                payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
                subtotal DECIMAL(10,2) NOT NULL,
                tax DECIMAL(10,2) DEFAULT 0,
                shipping DECIMAL(10,2) DEFAULT 0,
                total DECIMAL(10,2) NOT NULL,
                shipping_address TEXT,
                billing_address TEXT,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_order_number (order_number),
                INDEX idx_customer (customer_id),
                INDEX idx_status (status),
                INDEX idx_created (created_at)";
            
            if ($customersExists) {
                $sql .= ",
                FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL";
            }
            
            $sql .= "
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $pdo->exec($sql);
            $setupMessages[] = "✓ Created orders table";
            $tablesCreated++;
        }
        
        try {
            $db->fetchOne("SELECT 1 FROM order_items LIMIT 1");
            $setupMessages[] = "✓ Order items table already exists";
        } catch (Exception $e) {
            // Create order_items table
            $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                order_id INT NOT NULL,
                product_id INT NOT NULL,
                product_name VARCHAR(255) NOT NULL,
                product_sku VARCHAR(100),
                quantity INT NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                subtotal DECIMAL(10,2) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_order (order_id),
                INDEX idx_product (product_id),
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            $setupMessages[] = "✓ Created order_items table";
            $tablesCreated++;
        }
        
        // Customers table already created above if needed
        try {
            $db->fetchOne("SELECT 1 FROM customers LIMIT 1");
            $setupMessages[] = "✓ Customers table exists";
        } catch (Exception $e) {
            $setupMessages[] = "✗ Customers table missing (should have been created)";
        }
        
        $setupMessages[] = "\n✅ Setup Complete!";
        $setupMessages[] = "   - Tables created: {$tablesCreated}";
        $message = "Orders Management system setup completed successfully! Created {$tablesCreated} table(s).\n\n" . implode("\n", $setupMessages);
        
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

$pageTitle = 'Setup Orders Management';
include __DIR__ . '/includes/header.php';
?>

<div class="p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Setup Orders Management System</h1>
        <p class="text-gray-600 mt-2">This will create the necessary database tables for orders management.</p>
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
            'orders' => 'Orders Table',
            'order_items' => 'Order Items Table',
            'customers' => 'Customers Table'
        ];
        
        $existingTables = [];
        $missingTables = [];
        
        foreach ($tables as $table => $label) {
            try {
                $db->fetchOne("SELECT 1 FROM {$table} LIMIT 1");
                $existingTables[] = $table;
                echo "<div class='mb-2 text-green-600'>✓ {$label} - Exists</div>";
            } catch (Exception $e) {
                $missingTables[] = $table;
                echo "<div class='mb-2 text-red-600'>✗ {$label} - Missing</div>";
            }
        }
        ?>
    </div>
    
    <?php if (!empty($missingTables)): ?>
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
        <h3 class="font-bold text-blue-900 mb-2">
            <i class="fas fa-info-circle mr-2"></i>
            Missing Tables
        </h3>
        <p class="text-blue-800 mb-4">
            The following tables need to be created:
        </p>
        <ul class="list-disc list-inside text-blue-800 space-y-1">
            <?php foreach ($missingTables as $table): ?>
                <li><?= escape($tables[$table]) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    
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
            All required tables are already created. Orders Management is ready to use!
        </p>
        <div class="mt-4">
            <a href="<?= url('admin/orders.php') ?>" class="btn-primary inline-block">
                <i class="fas fa-shopping-cart mr-2"></i>
                Go to Orders
            </a>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Information -->
    <div class="bg-gray-50 rounded-lg p-6 mt-6">
        <h3 class="font-bold mb-3">What This Setup Does:</h3>
        <ul class="list-disc list-inside space-y-2 text-gray-700">
            <li>Creates <code>orders</code> table - Stores all order information</li>
            <li>Creates <code>order_items</code> table - Stores items in each order</li>
            <li>Creates <code>customers</code> table - Stores customer account information</li>
            <li>Sets up foreign keys and indexes for proper relationships</li>
        </ul>
        
        <div class="mt-4 p-4 bg-blue-50 rounded">
            <p class="text-sm text-blue-800">
                <strong>Note:</strong> This setup will not delete or modify existing data. 
                It only creates tables that don't already exist.
            </p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

