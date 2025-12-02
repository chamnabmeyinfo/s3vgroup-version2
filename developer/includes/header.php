<?php
/**
 * Developer Panel Header
 * Separate header for developer-only area
 */

// Ensure we're using developer session (completely separate from admin)
if (session_status() === PHP_SESSION_NONE) {
    session_name('developer_session');
    session_start();
}

// Check if developer is logged in
if (!isset($_SESSION['developer_logged_in']) || $_SESSION['developer_logged_in'] !== true) {
    header('Location: ' . url('developer/login.php'));
    exit;
}

$developerName = $_SESSION['developer_name'] ?? 'Developer';
$developerUsername = $_SESSION['developer_username'] ?? 'developer';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape($pageTitle ?? 'Developer Panel') ?> - S3VGroup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <nav class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <a href="<?= url('developer/index.php') ?>" class="text-xl font-bold flex items-center">
                    <i class="fas fa-code mr-2"></i>
                    Developer Panel
                </a>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <div class="bg-white/20 rounded-full p-2">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div>
                            <div class="text-sm font-semibold"><?= escape($developerName) ?></div>
                            <div class="text-xs opacity-75">Developer Access</div>
                        </div>
                    </div>
                    <a href="<?= url('developer/logout.php') ?>" class="hover:underline bg-white/20 px-4 py-2 rounded-lg transition-all hover:bg-white/30">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="flex">
        <aside class="w-72 bg-gradient-to-b from-gray-800 to-gray-900 text-white min-h-screen shadow-2xl border-r-2 border-purple-500/20">
            <div class="p-6 border-b border-gray-700">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="bg-gradient-to-br from-purple-600 to-indigo-600 rounded-lg p-3">
                        <i class="fas fa-code text-xl"></i>
                    </div>
                    <div>
                        <div class="font-bold text-lg">Developer</div>
                        <div class="text-xs text-gray-400">Panel</div>
                    </div>
                </div>
                <div class="text-xs text-gray-400 bg-gray-800/50 rounded px-3 py-2">
                    <i class="fas fa-user-shield mr-1"></i>
                    <?= escape($developerName) ?>
                </div>
            </div>
            
            <nav class="p-4 space-y-1">
                <a href="<?= url('developer/index.php') ?>" class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-700/50 transition-all group">
                    <i class="fas fa-dashboard mr-3 w-5 text-center"></i>
                    <span>Dashboard</span>
                    <i class="fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-100 transition-opacity text-xs"></i>
                </a>
                
                <div class="border-t border-gray-700 my-3"></div>
                <div class="px-4 py-2 text-xs text-gray-400 uppercase font-bold tracking-wider">Deployment</div>
                
                <a href="<?= url('developer/deployment.php') ?>" class="flex items-center px-4 py-3 rounded-lg hover:bg-blue-600/20 hover:text-blue-300 transition-all group">
                    <i class="fas fa-rocket mr-3 w-5 text-center"></i>
                    <span>Deployment</span>
                    <i class="fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-100 transition-opacity text-xs"></i>
                </a>
                <a href="<?= url('developer/deployment-logs.php') ?>" class="flex items-center px-4 py-3 rounded-lg hover:bg-blue-600/20 hover:text-blue-300 transition-all group">
                    <i class="fas fa-file-alt mr-3 w-5 text-center"></i>
                    <span>Deployment Logs</span>
                    <i class="fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-100 transition-opacity text-xs"></i>
                </a>
                
                <div class="border-t border-gray-700 my-3"></div>
                <div class="px-4 py-2 text-xs text-gray-400 uppercase font-bold tracking-wider">Import</div>
                
                <a href="<?= url('developer/import-unforklift.php') ?>" class="flex items-center px-4 py-3 rounded-lg hover:bg-teal-600/20 hover:text-teal-300 transition-all group">
                    <i class="fas fa-download mr-3 w-5 text-center"></i>
                    <span>Import from UN Forklift</span>
                    <i class="fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-100 transition-opacity text-xs"></i>
                </a>
                
                <div class="border-t border-gray-700 my-3"></div>
                <div class="px-4 py-2 text-xs text-gray-400 uppercase font-bold tracking-wider">Database</div>
                
                <a href="<?= url('developer/database-sync.php') ?>" class="flex items-center px-4 py-3 rounded-lg hover:bg-green-600/20 hover:text-green-300 transition-all group">
                    <i class="fas fa-sync-alt mr-3 w-5 text-center"></i>
                    <span>Database Sync</span>
                    <i class="fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-100 transition-opacity text-xs"></i>
                </a>
                <a href="<?= url('developer/database-upload.php') ?>" class="flex items-center px-4 py-3 rounded-lg hover:bg-cyan-600/20 hover:text-cyan-300 transition-all group">
                    <i class="fas fa-cloud-upload-alt mr-3 w-5 text-center"></i>
                    <span>Database Upload</span>
                    <i class="fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-100 transition-opacity text-xs"></i>
                </a>
                <a href="<?= url('developer/backup.php') ?>" class="flex items-center px-4 py-3 rounded-lg hover:bg-orange-600/20 hover:text-orange-300 transition-all group">
                    <i class="fas fa-database mr-3 w-5 text-center"></i>
                    <span>Database Backup</span>
                    <i class="fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-100 transition-opacity text-xs"></i>
                </a>
                
                <div class="border-t border-gray-700 my-3"></div>
                <div class="px-4 py-2 text-xs text-gray-400 uppercase font-bold tracking-wider">Settings</div>
                
                <a href="<?= url('developer/settings.php') ?>" class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-700/50 transition-all group">
                    <i class="fas fa-cog mr-3 w-5 text-center"></i>
                    <span>Developer Settings</span>
                    <i class="fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-100 transition-opacity text-xs"></i>
                </a>
                
                <div class="border-t border-gray-700 my-3"></div>
                
                <a href="<?= url('admin/index.php') ?>" class="flex items-center px-4 py-3 rounded-lg hover:bg-yellow-600/20 hover:text-yellow-300 transition-all group border border-yellow-600/30">
                    <i class="fas fa-arrow-left mr-3 w-5 text-center"></i>
                    <span>Admin Panel</span>
                    <i class="fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-100 transition-opacity text-xs"></i>
                </a>
                <a href="<?= url() ?>" target="_blank" class="flex items-center px-4 py-3 rounded-lg hover:bg-gray-700/50 transition-all group">
                    <i class="fas fa-external-link-alt mr-3 w-5 text-center"></i>
                    <span>View Website</span>
                    <i class="fas fa-chevron-right ml-auto opacity-0 group-hover:opacity-100 transition-opacity text-xs"></i>
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

