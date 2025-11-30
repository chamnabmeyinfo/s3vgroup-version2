<?php
/**
 * Role Management Setup Script
 * Run this once to set up the role management system
 * This script will also create admin_users table if it doesn't exist
 */
require_once __DIR__ . '/../bootstrap/app.php';

// Allow access for initial setup - don't require login if tables don't exist
$requireAuth = true;
try {
    db()->fetchOne("SELECT 1 FROM admin_users LIMIT 1");
} catch (\Exception $e) {
    // admin_users doesn't exist - allow access for setup
    $requireAuth = false;
}

if ($requireAuth) {
    require_once __DIR__ . '/includes/auth.php';
    if (function_exists('requirePermission')) {
        try {
            requirePermission('manage_settings');
        } catch (\Exception $e) {
            // Permission system not set up yet
        }
    }
}

$message = '';
$error = '';

// Check what tables exist
$tablesStatus = [
    'admin_users' => false,
    'roles' => false,
    'permissions' => false,
];

try {
    db()->fetchOne("SELECT 1 FROM admin_users LIMIT 1");
    $tablesStatus['admin_users'] = true;
} catch (\Exception $e) {
    $tablesStatus['admin_users'] = false;
}

try {
    db()->fetchOne("SELECT 1 FROM roles LIMIT 1");
    $tablesStatus['roles'] = true;
} catch (\Exception $e) {
    $tablesStatus['roles'] = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_setup'])) {
    try {
        // STEP 1: Create admin_users table if it doesn't exist
        if (!$tablesStatus['admin_users']) {
            db()->query("CREATE TABLE IF NOT EXISTS admin_users (
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            
            // Check if admin user exists
            $userCount = 0;
            try {
                $userCount = db()->fetchOne("SELECT COUNT(*) as count FROM admin_users")['count'] ?? 0;
            } catch (\Exception $e) {
                // Table just created
            }
            
            // Insert default admin if no users exist
            if ($userCount == 0) {
                // Generate password hash for "admin"
                $passwordHash = password_hash('admin', PASSWORD_DEFAULT);
                db()->query("INSERT INTO admin_users (username, email, password, name) VALUES (?, ?, ?, ?)",
                    ['admin', 'admin@example.com', $passwordHash, 'Administrator']);
                $message .= "Created admin_users table and default admin user. ";
            } else {
                $message .= "Created admin_users table. ";
            }
        }
        
        // STEP 2: Add role_id column to admin_users if it doesn't exist
        try {
            db()->fetchOne("SELECT role_id FROM admin_users LIMIT 1");
        } catch (\Exception $e) {
            // Column doesn't exist, add it
            try {
                db()->query("ALTER TABLE admin_users ADD COLUMN role_id INT NULL AFTER id");
                db()->query("ALTER TABLE admin_users ADD INDEX idx_role (role_id)");
                $message .= "Added role_id column to admin_users. ";
            } catch (\Exception $e2) {
                // Ignore if it fails
            }
        }
        
        // STEP 3: Create roles table
        if (!$tablesStatus['roles']) {
            db()->query("CREATE TABLE IF NOT EXISTS roles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL UNIQUE,
                slug VARCHAR(100) NOT NULL UNIQUE,
                description TEXT,
                is_system TINYINT(1) DEFAULT 0 COMMENT 'System roles cannot be deleted',
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_slug (slug),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            
            // Create permissions table
            db()->query("CREATE TABLE IF NOT EXISTS permissions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL UNIQUE,
                slug VARCHAR(100) NOT NULL UNIQUE,
                description TEXT,
                category VARCHAR(50) DEFAULT 'general',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_slug (slug),
                INDEX idx_category (category)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            
            // Create role_permissions table
            db()->query("CREATE TABLE IF NOT EXISTS role_permissions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                role_id INT NOT NULL,
                permission_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
                FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
                UNIQUE KEY unique_role_permission (role_id, permission_id),
                INDEX idx_role (role_id),
                INDEX idx_permission (permission_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            
            $message .= "Created roles, permissions, and role_permissions tables. ";
            
            // Insert default roles
            $roles = [
                ['Super Administrator', 'super_admin', 'Full system access - all permissions', 1],
                ['Administrator', 'admin', 'Full administrative access', 1],
                ['Manager', 'manager', 'Can manage products, orders, and content', 0],
                ['Editor', 'editor', 'Can edit products and content', 0],
                ['Viewer', 'viewer', 'Read-only access to dashboard and reports', 0],
                ['Support', 'support', 'Can manage quotes, messages, and customer support', 0],
            ];
            
            foreach ($roles as $role) {
                try {
                    db()->query("INSERT INTO roles (name, slug, description, is_system) VALUES (?, ?, ?, ?)",
                        $role);
                } catch (\Exception $e) {
                    // Already exists
                }
            }
            
            $message .= "Inserted default roles. ";
        }
        
        // STEP 4: Insert permissions from the SQL file
        $sqlFile = __DIR__ . '/../database/role-management.sql';
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            
            // Extract INSERT statements for permissions
            preg_match_all("/INSERT INTO permissions[^;]+;/is", $sql, $matches);
            
            foreach ($matches[0] as $insertStatement) {
                try {
                    db()->query($insertStatement);
                } catch (\Exception $e) {
                    // Already exists
                }
            }
            
            // Extract INSERT statements for role_permissions
            preg_match_all("/INSERT INTO role_permissions[^;]+;/is", $sql, $matches);
            
            foreach ($matches[0] as $insertStatement) {
                try {
                    db()->query($insertStatement);
                } catch (\Exception $e) {
                    // Already exists
                }
            }
            
            $message .= "Inserted permissions. ";
        }
        
        // STEP 5: Assign Super Admin role to default admin user
        try {
            $adminUser = db()->fetchOne("SELECT id, role_id FROM admin_users WHERE username = 'admin'");
            if ($adminUser && empty($adminUser['role_id'])) {
                $superAdminRole = db()->fetchOne("SELECT id FROM roles WHERE slug = 'super_admin'");
                if ($superAdminRole) {
                    db()->update('admin_users', ['role_id' => $superAdminRole['id']], 'id = :id', ['id' => $adminUser['id']]);
                    $message .= "Assigned Super Administrator role to admin user. ";
                }
            }
        } catch (\Exception $e) {
            // Ignore
        }
        
        $message = "✅ " . trim($message) ?: "Setup completed successfully!";
        
    } catch (\Exception $e) {
        $error = 'Error: ' . $e->getMessage();
        $error .= '<br><small>File: ' . $e->getFile() . ' Line: ' . $e->getLine() . '</small>';
    }
}

// Re-check status after setup
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        db()->fetchOne("SELECT 1 FROM admin_users LIMIT 1");
        $tablesStatus['admin_users'] = true;
    } catch (\Exception $e) {
        $tablesStatus['admin_users'] = false;
    }
    
    try {
        db()->fetchOne("SELECT 1 FROM roles LIMIT 1");
        $tablesStatus['roles'] = true;
    } catch (\Exception $e) {
        $tablesStatus['roles'] = false;
    }
}

$pageTitle = 'Setup Role Management';
include __DIR__ . '/includes/header.php';
?>

<div class="p-6">
    <h1 class="text-3xl font-bold mb-6">Role Management Setup</h1>
    
    <?php if ($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= $message ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= $error ?>
        </div>
    <?php endif; ?>
    
    <div class="grid md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">Current Status</h2>
            <ul class="space-y-2">
                <li class="flex items-center">
                    <span class="w-4 h-4 rounded-full mr-2 <?= $tablesStatus['admin_users'] ? 'bg-green-500' : 'bg-red-500' ?>"></span>
                    admin_users table: <?= $tablesStatus['admin_users'] ? '<span class="text-green-600">✓ Exists</span>' : '<span class="text-red-600">✗ Missing</span>' ?>
                </li>
                <li class="flex items-center">
                    <span class="w-4 h-4 rounded-full mr-2 <?= $tablesStatus['roles'] ? 'bg-green-500' : 'bg-gray-400' ?>"></span>
                    roles table: <?= $tablesStatus['roles'] ? '<span class="text-green-600">✓ Exists</span>' : '<span class="text-gray-600">Not set up</span>' ?>
                </li>
            </ul>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">What This Will Do:</h2>
            <ul class="list-disc list-inside space-y-2 text-sm">
                <?php if (!$tablesStatus['admin_users']): ?>
                <li>Create admin_users table</li>
                <li>Create default admin user (admin/admin)</li>
                <?php endif; ?>
                <li>Add role_id column to admin_users</li>
                <li>Create roles, permissions, role_permissions tables</li>
                <li>Insert 6 default roles</li>
                <li>Insert 40+ default permissions</li>
                <li>Assign permissions to roles</li>
                <li>Assign Super Admin role to admin user</li>
            </ul>
        </div>
    </div>
    
    <?php if (!$tablesStatus['admin_users']): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded p-4 mb-4">
            <p class="text-yellow-800">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <strong>Note:</strong> The admin_users table doesn't exist. This setup will create it.
            </p>
            <p class="text-sm text-yellow-700 mt-2">
                Default login credentials: <strong>admin</strong> / <strong>admin</strong>
            </p>
        </div>
    <?php endif; ?>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <form method="POST">
            <button type="submit" name="run_setup" class="btn-primary">
                <i class="fas fa-cog mr-2"></i> Run Setup
            </button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
