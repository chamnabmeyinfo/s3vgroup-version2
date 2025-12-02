<?php
/**
 * Developer Dashboard
 * Main dashboard for developer tools
 */

// Set developer session name BEFORE loading bootstrap
// Session name must be set before session_start() is called
if (session_status() === PHP_SESSION_NONE) {
    session_name('developer_session');
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . '/../bootstrap/app.php';
} catch (Exception $e) {
    die("Bootstrap Error: " . $e->getMessage() . "<br>File: " . $e->getFile() . "<br>Line: " . $e->getLine());
}

try {
    require_once __DIR__ . '/includes/header.php';
} catch (Exception $e) {
    die("Header Error: " . $e->getMessage() . "<br>File: " . $e->getFile() . "<br>Line: " . $e->getLine());
}

use App\Services\DatabaseSyncService;

$pageTitle = 'Developer Dashboard';

// Load deployment config
$configFile = __DIR__ . '/../deploy-config.json';
$config = [];
if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true);
}

// Get sync status
try {
    $syncService = new DatabaseSyncService();
    $syncStatus = $syncService->getSyncStatus($config['database_remote'] ?? []);
} catch (Exception $e) {
    // If DatabaseSyncService fails, use default values
    $syncStatus = [
        'local_tables' => 0,
        'remote_tables' => 0,
        'last_sync' => null,
        'needs_pull' => false,
        'needs_push' => false,
        'differences' => []
    ];
}

// Get deployment status
$deployLogFile = __DIR__ . '/../deploy-log.txt';
$lastDeployment = null;
if (file_exists($deployLogFile)) {
    $logContent = file_get_contents($deployLogFile);
    preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\].*Deployment Complete!/', $logContent, $matches);
    if (!empty($matches[1])) {
        $lastDeployment = $matches[1];
    }
}

?>

<div class="max-w-7xl mx-auto">
    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg shadow-xl p-4 md:p-6 lg:p-8 mb-4 md:mb-6 text-white">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-1 md:mb-2">
                    <i class="fas fa-code mr-2 md:mr-3"></i>
                    Developer Dashboard
                </h1>
                <p class="text-purple-100 text-sm md:text-lg">Project development tools and deployment management</p>
            </div>
            <div class="bg-white/20 rounded-full p-4 md:p-6">
                <i class="fas fa-user-shield text-2xl md:text-3xl lg:text-4xl"></i>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-4 md:mb-6">
        <div class="bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl shadow-lg p-4 md:p-6 text-white transform hover:scale-105 transition-transform">
            <div class="flex items-center justify-between mb-3 md:mb-4">
                <div class="bg-white/20 rounded-lg p-2 md:p-3">
                    <i class="fas fa-database text-xl md:text-2xl"></i>
                </div>
                <div class="text-right">
                    <div class="text-2xl md:text-3xl font-bold"><?= $syncStatus['local_tables'] ?? 0 ?></div>
                    <div class="text-purple-100 text-xs md:text-sm">Tables</div>
                </div>
            </div>
            <div class="text-purple-100 text-xs md:text-sm font-medium">Local Database</div>
        </div>
        
        <div class="bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl shadow-lg p-4 md:p-6 text-white transform hover:scale-105 transition-transform">
            <div class="flex items-center justify-between mb-3 md:mb-4">
                <div class="bg-white/20 rounded-lg p-2 md:p-3">
                    <i class="fas fa-rocket text-xl md:text-2xl"></i>
                </div>
                <div class="text-right">
                    <div class="text-base md:text-lg font-bold">
                        <?= $lastDeployment ? date('M d', strtotime($lastDeployment)) : 'Never' ?>
                    </div>
                    <div class="text-blue-100 text-xs">
                        <?= $lastDeployment ? date('H:i', strtotime($lastDeployment)) : '' ?>
                    </div>
                </div>
            </div>
            <div class="text-blue-100 text-xs md:text-sm font-medium">Last Deployment</div>
        </div>
        
        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg p-4 md:p-6 text-white transform hover:scale-105 transition-transform">
            <div class="flex items-center justify-between mb-3 md:mb-4">
                <div class="bg-white/20 rounded-lg p-2 md:p-3">
                    <i class="fas fa-sync-alt text-xl md:text-2xl"></i>
                </div>
                <div class="text-right">
                    <div class="text-base md:text-lg font-bold">
                        <?= $syncStatus['last_sync'] ? date('M d', strtotime($syncStatus['last_sync'])) : 'Never' ?>
                    </div>
                    <div class="text-green-100 text-xs">
                        <?= $syncStatus['last_sync'] ? date('H:i', strtotime($syncStatus['last_sync'])) : '' ?>
                    </div>
                </div>
            </div>
            <div class="text-green-100 text-xs md:text-sm font-medium">Last Sync</div>
        </div>
        
        <div class="bg-gradient-to-br from-orange-500 to-red-600 rounded-xl shadow-lg p-4 md:p-6 text-white transform hover:scale-105 transition-transform">
            <div class="flex items-center justify-between mb-3 md:mb-4">
                <div class="bg-white/20 rounded-lg p-2 md:p-3">
                    <i class="fas fa-cloud text-xl md:text-2xl"></i>
                </div>
                <div class="text-right">
                    <div class="text-base md:text-lg font-bold">s3vgroup.com</div>
                    <div class="text-orange-100 text-xs">Production</div>
                </div>
            </div>
            <div class="text-orange-100 text-xs md:text-sm font-medium">Remote Server</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-lg p-4 md:p-6 mb-4 md:mb-6">
        <div class="flex items-center mb-4 md:mb-6">
            <div class="bg-purple-100 rounded-lg p-2 md:p-3 mr-2 md:mr-3">
                <i class="fas fa-bolt text-purple-600 text-lg md:text-xl"></i>
            </div>
            <h2 class="text-xl md:text-2xl font-bold text-gray-800">Quick Actions</h2>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
            <a href="<?= url('developer/database-sync.php') ?>" class="group bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg p-4 md:p-6 text-white hover:shadow-xl transform hover:scale-105 transition-all">
                <div class="flex items-center justify-between mb-2 md:mb-3">
                    <i class="fas fa-download text-2xl md:text-3xl"></i>
                    <i class="fas fa-arrow-right opacity-0 group-hover:opacity-100 transition-opacity"></i>
                </div>
                <h3 class="font-bold text-base md:text-lg mb-1">Pull from Remote</h3>
                <p class="text-green-100 text-xs md:text-sm">Get latest from s3vgroup.com</p>
            </a>
            
            <a href="<?= url('developer/deployment.php') ?>" class="group bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg p-4 md:p-6 text-white hover:shadow-xl transform hover:scale-105 transition-all">
                <div class="flex items-center justify-between mb-2 md:mb-3">
                    <i class="fas fa-rocket text-2xl md:text-3xl"></i>
                    <i class="fas fa-arrow-right opacity-0 group-hover:opacity-100 transition-opacity"></i>
                </div>
                <h3 class="font-bold text-base md:text-lg mb-1">Deploy to Server</h3>
                <p class="text-blue-100 text-xs md:text-sm">Push changes to production</p>
            </a>
            
            <a href="<?= url('developer/database-sync.php') ?>" class="group bg-gradient-to-br from-cyan-500 to-teal-600 rounded-lg p-4 md:p-6 text-white hover:shadow-xl transform hover:scale-105 transition-all">
                <div class="flex items-center justify-between mb-2 md:mb-3">
                    <i class="fas fa-sync-alt text-2xl md:text-3xl"></i>
                    <i class="fas fa-arrow-right opacity-0 group-hover:opacity-100 transition-opacity"></i>
                </div>
                <h3 class="font-bold text-base md:text-lg mb-1">Sync Database</h3>
                <p class="text-cyan-100 text-xs md:text-sm">Sync local ↔ remote</p>
            </a>
            
            <a href="<?= url('developer/backup.php') ?>" class="group bg-gradient-to-br from-orange-500 to-amber-600 rounded-lg p-4 md:p-6 text-white hover:shadow-xl transform hover:scale-105 transition-all">
                <div class="flex items-center justify-between mb-2 md:mb-3">
                    <i class="fas fa-database text-2xl md:text-3xl"></i>
                    <i class="fas fa-arrow-right opacity-0 group-hover:opacity-100 transition-opacity"></i>
                </div>
                <h3 class="font-bold text-base md:text-lg mb-1">Create Backup</h3>
                <p class="text-orange-100 text-xs md:text-sm">Backup database</p>
            </a>
            
            <a href="<?= url('developer/design-versions.php') ?>" class="group bg-gradient-to-br from-pink-500 to-rose-600 rounded-lg p-4 md:p-6 text-white hover:shadow-xl transform hover:scale-105 transition-all">
                <div class="flex items-center justify-between mb-2 md:mb-3">
                    <i class="fas fa-history text-2xl md:text-3xl"></i>
                    <i class="fas fa-arrow-right opacity-0 group-hover:opacity-100 transition-opacity"></i>
                </div>
                <h3 class="font-bold text-base md:text-lg mb-1">Design Versions</h3>
                <p class="text-pink-100 text-xs md:text-sm">Manage design snapshots</p>
            </a>
        </div>
    </div>

    <!-- Developer Tools & Info -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
        <!-- Development Tools -->
        <div class="bg-white rounded-xl shadow-lg p-4 md:p-6">
            <div class="flex items-center mb-4 md:mb-6">
                <div class="bg-purple-100 rounded-lg p-2 md:p-3 mr-2 md:mr-3">
                    <i class="fas fa-tools text-purple-600 text-lg md:text-xl"></i>
                </div>
                <h2 class="text-xl md:text-2xl font-bold text-gray-800">Development Tools</h2>
            </div>
            <div class="space-y-3">
                <a href="<?= url('developer/deployment.php') ?>" class="flex items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors group">
                    <div class="bg-blue-500 rounded-lg p-3 mr-4">
                        <i class="fas fa-rocket text-white"></i>
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold text-gray-800 group-hover:text-blue-600">Deployment Management</div>
                        <div class="text-sm text-gray-600">Manage and trigger deployments</div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 group-hover:text-blue-600"></i>
                </a>
                
                <a href="<?= url('developer/database-sync.php') ?>" class="flex items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors group">
                    <div class="bg-green-500 rounded-lg p-3 mr-4">
                        <i class="fas fa-sync-alt text-white"></i>
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold text-gray-800 group-hover:text-green-600">Database Sync</div>
                        <div class="text-sm text-gray-600">Sync between local and remote</div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 group-hover:text-green-600"></i>
                </a>
                
                <a href="<?= url('developer/database-upload.php') ?>" class="flex items-center p-4 bg-cyan-50 hover:bg-cyan-100 rounded-lg transition-colors group">
                    <div class="bg-cyan-500 rounded-lg p-3 mr-4">
                        <i class="fas fa-cloud-upload-alt text-white"></i>
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold text-gray-800 group-hover:text-cyan-600">Database Upload</div>
                        <div class="text-sm text-gray-600">Upload database to server</div>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 group-hover:text-cyan-600"></i>
                </a>
            </div>
        </div>
        
        <!-- System Information -->
        <div class="bg-white rounded-xl shadow-lg p-4 md:p-6">
            <div class="flex items-center mb-4 md:mb-6">
                <div class="bg-indigo-100 rounded-lg p-2 md:p-3 mr-2 md:mr-3">
                    <i class="fas fa-info-circle text-indigo-600 text-lg md:text-xl"></i>
                </div>
                <h2 class="text-xl md:text-2xl font-bold text-gray-800">System Information</h2>
            </div>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-server text-gray-400 mr-3"></i>
                        <span class="font-medium text-gray-700">PHP Version</span>
                    </div>
                    <span class="font-semibold text-gray-900"><?= PHP_VERSION ?></span>
                </div>
                
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-server text-gray-400 mr-3"></i>
                        <span class="font-medium text-gray-700">Server</span>
                    </div>
                    <span class="font-semibold text-gray-900 text-sm"><?= substr($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown', 0, 20) ?></span>
                </div>
                
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-home text-gray-400 mr-3"></i>
                        <span class="font-medium text-gray-700">Local URL</span>
                    </div>
                    <span class="font-semibold text-gray-900">localhost:8080</span>
                </div>
                
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-cloud text-gray-400 mr-3"></i>
                        <span class="font-medium text-gray-700">Remote URL</span>
                    </div>
                    <span class="font-semibold text-gray-900">s3vgroup.com</span>
                </div>
                
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-database text-gray-400 mr-3"></i>
                        <span class="font-medium text-gray-700">Database Tables</span>
                    </div>
                    <span class="font-semibold text-gray-900"><?= $syncStatus['local_tables'] ?? 0 ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

