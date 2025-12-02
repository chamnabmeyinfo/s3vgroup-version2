<?php
/**
 * Database Sync Management (Developer Only)
 * Smart database synchronization between local and remote
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/header.php';

use App\Services\DatabaseSyncService;

$pageTitle = 'Database Sync';

$syncService = new DatabaseSyncService();
$message = '';
$messageType = '';

// Load deployment config for remote database settings
$configFile = __DIR__ . '/../deploy-config.json';
$config = [];
if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true);
}

$remoteDbConfig = $config['database_remote'] ?? [];

// Handle sync actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'pull':
                $options = [
                    'backup_before_pull' => isset($_POST['backup_before_pull']),
                    'merge_strategy' => $_POST['merge_strategy'] ?? 'remote_priority',
                ];
                
                $result = $syncService->pullFromRemote($remoteDbConfig, $options);
                
                if ($result['success']) {
                    $message = $result['message'];
                    $messageType = 'success';
                } else {
                    $message = $result['message'];
                    $messageType = 'error';
                }
                break;
                
            case 'push':
                $options = [
                    'backup_before_push' => isset($_POST['backup_before_push']),
                    'merge_strategy' => $_POST['merge_strategy'] ?? 'local_priority',
                ];
                
                $result = $syncService->pushToRemote($remoteDbConfig, $options);
                
                if ($result['success']) {
                    $message = $result['message'];
                    $messageType = 'success';
                } else {
                    $message = $result['message'];
                    $messageType = 'error';
                }
                break;
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Get sync status
$syncStatus = $syncService->getSyncStatus($remoteDbConfig);
$syncLog = $syncService->getLog();

?>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl shadow-xl p-4 md:p-6 lg:p-8 mb-4 md:mb-6 text-white">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-1 md:mb-2">
                    <i class="fas fa-sync-alt mr-2 md:mr-3 animate-spin-slow"></i>
                    Database Sync Management
                </h1>
                <p class="text-purple-100 text-sm md:text-lg">Smart synchronization between local and remote databases</p>
            </div>
            <div class="bg-white/20 rounded-full px-4 md:px-6 py-2 md:py-3 backdrop-blur-sm">
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 md:w-3 md:h-3 bg-green-400 rounded-full animate-pulse"></div>
                    <span class="font-semibold text-sm md:text-base">Smart Sync Enabled</span>
                </div>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
    <div class="bg-<?= $messageType === 'success' ? 'green' : ($messageType === 'error' ? 'red' : 'blue') ?>-100 border-l-4 border-<?= $messageType === 'success' ? 'green' : ($messageType === 'error' ? 'red' : 'blue') ?>-500 text-<?= $messageType === 'success' ? 'green' : ($messageType === 'error' ? 'red' : 'blue') ?>-700 p-4 rounded-lg mb-6 animate-slide-down">
        <div class="flex items-center">
            <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : ($messageType === 'error' ? 'exclamation-circle' : 'info-circle') ?> mr-2 text-xl"></i>
            <span class="font-semibold"><?= escape($message) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Sync Status Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-4 md:mb-6">
        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg p-4 md:p-6 text-white transform hover:scale-105 transition-all">
            <div class="flex items-center justify-between mb-3 md:mb-4">
                <div class="bg-white/20 rounded-lg p-2 md:p-3">
                    <i class="fas fa-database text-xl md:text-2xl"></i>
                </div>
                <div class="text-right">
                    <div class="text-2xl md:text-3xl font-bold"><?= $syncStatus['local_tables'] ?? 0 ?></div>
                    <div class="text-green-100 text-xs md:text-sm">Tables</div>
                </div>
            </div>
            <div class="text-green-100 text-xs md:text-sm font-medium">Local Database</div>
            <div class="text-green-200 text-xs mt-1">localhost:8080</div>
        </div>
        
        <div class="bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl shadow-lg p-4 md:p-6 text-white transform hover:scale-105 transition-all">
            <div class="flex items-center justify-between mb-3 md:mb-4">
                <div class="bg-white/20 rounded-lg p-2 md:p-3">
                    <i class="fas fa-cloud text-xl md:text-2xl"></i>
                </div>
                <div class="text-right">
                    <div class="text-base md:text-lg font-bold">s3vgroup.com</div>
                    <div class="text-blue-100 text-xs">Production</div>
                </div>
            </div>
            <div class="text-blue-100 text-xs md:text-sm font-medium">Remote Database</div>
            <div class="text-blue-200 text-xs mt-1">Priority Source</div>
        </div>
        
        <div class="bg-gradient-to-br from-orange-500 to-amber-600 rounded-xl shadow-lg p-4 md:p-6 text-white transform hover:scale-105 transition-all">
            <div class="flex items-center justify-between mb-3 md:mb-4">
                <div class="bg-white/20 rounded-lg p-2 md:p-3">
                    <i class="fas fa-clock text-xl md:text-2xl"></i>
                </div>
                <div class="text-right">
                    <div class="text-base md:text-lg font-bold">
                        <?= $syncStatus['last_sync'] ? date('M d', strtotime($syncStatus['last_sync'])) : 'Never' ?>
                    </div>
                    <div class="text-orange-100 text-xs">
                        <?= $syncStatus['last_sync'] ? date('H:i', strtotime($syncStatus['last_sync'])) : '' ?>
                    </div>
                </div>
            </div>
            <div class="text-orange-100 text-xs md:text-sm font-medium">Last Sync</div>
            <div class="text-orange-200 text-xs mt-1">Sync History</div>
        </div>
    </div>

    <!-- Sync Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-4 md:mb-6">
        <!-- Pull from Remote -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border-2 border-green-200 hover:border-green-400 transition-all">
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-4 md:p-6 text-white">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl md:text-2xl font-bold mb-1">
                            <i class="fas fa-download mr-2"></i>
                            Pull from Remote
                        </h2>
                        <p class="text-green-100 text-xs md:text-sm">Get latest data from s3vgroup.com</p>
                    </div>
                    <div class="bg-white/20 rounded-full p-3 md:p-4">
                        <i class="fas fa-arrow-down text-2xl md:text-3xl"></i>
                    </div>
                </div>
            </div>
            <div class="p-4 md:p-6">
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4 rounded">
                    <p class="text-sm text-gray-700">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        <strong>Priority: Remote Server</strong><br>
                        Downloads the latest database from production and updates your local database.
                    </p>
                </div>
                
                <form method="POST" class="space-y-4" onsubmit="return confirmSync('pull')">
                    <input type="hidden" name="action" value="pull">
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-code-branch mr-1"></i> Merge Strategy
                        </label>
                        <select name="merge_strategy" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all">
                            <option value="remote_priority" selected>Remote Priority (Recommended)</option>
                            <option value="newer_wins">Newer Wins</option>
                            <option value="manual">Manual Resolution</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">How to handle conflicts between local and remote data</p>
                    </div>
                    
                    <div class="flex items-center space-x-3 bg-gray-50 p-4 rounded-lg">
                        <input type="checkbox" name="backup_before_pull" id="backup_pull" checked class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                        <label for="backup_pull" class="text-sm font-medium text-gray-700 cursor-pointer">
                            <i class="fas fa-shield-alt text-green-600 mr-1"></i>
                            Create backup before pull (Recommended)
                        </label>
                    </div>
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white py-4 rounded-lg font-bold text-lg hover:from-green-600 hover:to-emerald-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                        <i class="fas fa-download mr-2"></i>
                        Pull from Remote
                    </button>
                </form>
            </div>
        </div>

        <!-- Push to Remote -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border-2 border-blue-200 hover:border-blue-400 transition-all">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold mb-1">
                            <i class="fas fa-upload mr-2"></i>
                            Push to Remote
                        </h2>
                        <p class="text-blue-100 text-sm">Deploy local changes to s3vgroup.com</p>
                    </div>
                    <div class="bg-white/20 rounded-full p-4">
                        <i class="fas fa-arrow-up text-3xl"></i>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-4 rounded">
                    <p class="text-sm text-gray-700">
                        <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                        <strong>Warning:</strong> This will overwrite the remote database with your local data.
                        Make sure you've tested everything locally first!
                    </p>
                </div>
                
                <form method="POST" class="space-y-4" onsubmit="return confirmSync('push')">
                    <input type="hidden" name="action" value="push">
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-code-branch mr-1"></i> Merge Strategy
                        </label>
                        <select name="merge_strategy" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="local_priority" selected>Local Priority (Recommended)</option>
                            <option value="newer_wins">Newer Wins</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">How to handle conflicts when pushing</p>
                    </div>
                    
                    <div class="flex items-center space-x-3 bg-gray-50 p-4 rounded-lg">
                        <input type="checkbox" name="backup_before_push" id="backup_push" checked class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="backup_push" class="text-sm font-medium text-gray-700 cursor-pointer">
                            <i class="fas fa-shield-alt text-blue-600 mr-1"></i>
                            Create remote backup before push (Recommended)
                        </label>
                    </div>
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white py-4 rounded-lg font-bold text-lg hover:from-blue-600 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                        <i class="fas fa-upload mr-2"></i>
                        Push to Remote
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Workflow Guide -->
    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-xl shadow-lg p-6 mb-6 border-l-4 border-purple-500">
        <div class="flex items-center mb-4">
            <div class="bg-purple-500 rounded-lg p-3 mr-4">
                <i class="fas fa-lightbulb text-white text-xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800">Recommended Workflow</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg p-5 text-center transform hover:scale-105 transition-all">
                <div class="bg-green-500 text-white rounded-full w-16 h-16 flex items-center justify-center text-2xl font-bold mx-auto mb-3">
                    1
                </div>
                <h4 class="font-bold text-gray-800 mb-2">Pull from Remote</h4>
                <p class="text-sm text-gray-600">Always pull the latest data from production before making changes</p>
            </div>
            <div class="bg-white rounded-lg p-5 text-center transform hover:scale-105 transition-all">
                <div class="bg-blue-500 text-white rounded-full w-16 h-16 flex items-center justify-center text-2xl font-bold mx-auto mb-3">
                    2
                </div>
                <h4 class="font-bold text-gray-800 mb-2">Make Changes Locally</h4>
                <p class="text-sm text-gray-600">Edit and test everything on localhost:8080 before deploying</p>
            </div>
            <div class="bg-white rounded-lg p-5 text-center transform hover:scale-105 transition-all">
                <div class="bg-purple-500 text-white rounded-full w-16 h-16 flex items-center justify-center text-2xl font-bold mx-auto mb-3">
                    3
                </div>
                <h4 class="font-bold text-gray-800 mb-2">Push to Remote</h4>
                <p class="text-sm text-gray-600">Once satisfied, push your changes to production</p>
            </div>
        </div>
    </div>

    <!-- Sync History -->
    <?php if (!empty($syncLog)): ?>
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-gray-800 to-gray-900 p-6 text-white">
            <h3 class="text-xl font-bold">
                <i class="fas fa-history mr-2"></i>
                Sync History
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase">Timestamp</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase">Message</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach (array_reverse($syncLog) as $logEntry): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <i class="fas fa-clock mr-2 text-purple-500"></i>
                            <?= escape($logEntry['timestamp']) ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900"><?= escape($logEntry['message']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.animate-spin-slow {
    animation: spin-slow 3s linear infinite;
}

@keyframes slide-down {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-slide-down {
    animation: slide-down 0.3s ease-out;
}
</style>

<script>
function confirmSync(action) {
    const actionText = action === 'pull' ? 'pull from remote' : 'push to remote';
    return confirm(`Are you sure you want to ${actionText}?\n\nThis will synchronize the database.`);
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
