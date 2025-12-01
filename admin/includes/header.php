<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape($pageTitle ?? 'Admin Panel') ?> - ForkliftPro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 text-white">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <a href="<?= url('admin/index.php') ?>" class="text-xl font-bold">Admin Panel</a>
                <div class="flex items-center space-x-4">
                    <?php if (session('admin_username')): ?>
                        <span>Welcome, <?= escape(session('admin_username')) ?></span>
                        <?php if (session('admin_role_name')): ?>
                            <span class="text-sm opacity-75">Role: <?= escape(session('admin_role_name')) ?></span>
                        <?php endif; ?>
                        <a href="<?= url('admin/logout.php') ?>" class="hover:underline">Logout</a>
                    <?php else: ?>
                        <a href="<?= url('admin/login.php') ?>" class="hover:underline">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="flex">
        <aside class="w-64 bg-gray-800 text-white min-h-screen">
            <nav class="p-4 space-y-2">
                <a href="<?= url('admin/index.php') ?>" class="block px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-dashboard mr-2"></i> Dashboard
                </a>
                <a href="<?= url('admin/products.php') ?>" class="block px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-box mr-2"></i> Products
                </a>
                <a href="<?= url('admin/import-unforklift.php') ?>" class="block px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-download mr-2"></i> Import from UN Forklift
                </a>
                <a href="<?= url('admin/categories.php') ?>" class="block px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-tags mr-2"></i> Categories
                </a>
                <a href="<?= url('admin/orders.php') ?>" class="block px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-shopping-cart mr-2"></i> Orders
                </a>
                <a href="<?= url('admin/quotes.php') ?>" class="block px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-calculator mr-2"></i> Quote Requests
                </a>
                <a href="<?= url('admin/messages.php') ?>" class="block px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-envelope mr-2"></i> Messages
                </a>
                <a href="<?= url('admin/reviews.php') ?>" class="block px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-star mr-2"></i> Reviews
                </a>
                <a href="<?= url('admin/newsletter.php') ?>" class="block px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-paper-plane mr-2"></i> Newsletter
                </a>
                <a href="<?= url('admin/analytics.php') ?>" class="block px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-chart-line mr-2"></i> Analytics
                </a>
                <a href="<?= url('admin/advanced-analytics.php') ?>" class="block px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-chart-bar mr-2"></i> Advanced Analytics
                </a>
                <a href="<?= url('admin/backup.php') ?>" class="block px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-database mr-2"></i> Backup
                </a>
                <a href="<?= url('admin/logs.php') ?>" class="block px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-file-alt mr-2"></i> System Logs
                </a>
                <a href="<?= url('admin/images.php') ?>" class="block px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-images mr-2"></i> Images
                </a>
                <div class="border-t border-gray-700 my-2"></div>
                <div class="px-4 py-2 text-xs text-gray-400 uppercase">User Management</div>
                <?php 
                // Only show user management if role system is set up and hasPermission function exists
                if (function_exists('hasPermission')) {
                    try {
                        db()->fetchOne("SELECT 1 FROM roles LIMIT 1");
                        if (hasPermission('view_users')): 
                    ?>
                    <a href="<?= url('admin/users.php') ?>" class="block px-4 py-2 rounded hover:bg-gray-700">
                        <i class="fas fa-users mr-2"></i> Users
                    </a>
                    <?php 
                        endif;
                        if (hasPermission('view_roles')): 
                    ?>
                    <a href="<?= url('admin/roles.php') ?>" class="block px-4 py-2 rounded hover:bg-gray-700">
                        <i class="fas fa-user-shield mr-2"></i> Roles & Permissions
                    </a>
                    <?php 
                        endif;
                    } catch (\Exception $e) {
                        // Roles table doesn't exist - don't show menu items
                    }
                }
                ?>
                <div class="border-t border-gray-700 my-2"></div>
                <div class="px-4 py-2 text-xs text-gray-400 uppercase">Advanced</div>
                <?php if (function_exists('hasPermission') && hasPermission('use_api')): ?>
                <a href="<?= url('admin/api-test.php') ?>" class="block px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-code mr-2"></i> API Testing
                </a>
                <?php endif; ?>
                <div class="border-t border-gray-700 my-2"></div>
                <a href="<?= url('admin/under-construction.php') ?>" class="block px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-hard-hat mr-2"></i> Under Construction
                </a>
                <a href="<?= url('admin/tools.php') ?>" class="block px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-tools mr-2"></i> Optional Tools
                </a>
                <a href="<?= url('admin/settings.php') ?>" class="block px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-cog mr-2"></i> Settings
                </a>
                <a href="<?= url('admin/change-password.php') ?>" class="block px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-key mr-2"></i> Change Password
                </a>
                <a href="<?= url() ?>" target="_blank" class="block px-4 py-2 rounded hover:bg-gray-700">
                    <i class="fas fa-external-link-alt mr-2"></i> View Website
                </a>
            </nav>
        </aside>
        
        <main class="flex-1 p-8">
            <?php if (isset($message) && $message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?= escape($message) ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error) && $error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= escape($error) ?>
                </div>
            <?php endif; ?>

