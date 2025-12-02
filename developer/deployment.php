<?php
/**
 * Deployment Management (Developer Only)
 * Trigger and manage deployments
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/header.php';

$pageTitle = 'Deployment Management';

$message = '';
$messageType = '';

// Handle deployment trigger
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['trigger_deployment'])) {
    $deployScript = __DIR__ . '/../deploy.bat';
    if (file_exists($deployScript)) {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $output = [];
            $returnCode = 0;
            exec('cd ' . escapeshellarg(__DIR__ . '/..') . ' && deploy.bat', $output, $returnCode);
            $message = "Deployment triggered. Check deployment logs for details.";
            $messageType = $returnCode === 0 ? 'success' : 'warning';
        } else {
            $output = [];
            $returnCode = 0;
            exec('cd ' . escapeshellarg(__DIR__ . '/..') . ' && php deploy-main.php', $output, $returnCode);
            $message = "Deployment triggered. Check deployment logs for details.";
            $messageType = $returnCode === 0 ? 'success' : 'warning';
        }
    } else {
        $message = "Deployment script not found.";
        $messageType = 'error';
    }
}

// Read deployment config
$configFile = __DIR__ . '/../deploy-config.json';
$config = [];
if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true);
}

// Read last deployment log
$logFile = __DIR__ . '/../deploy-log.txt';
$lastLog = '';
$lastDeploymentTime = null;
if (file_exists($logFile)) {
    $logLines = file($logFile);
    $lastLog = implode('', array_slice($logLines, -50));
    
    // Extract last deployment time
    preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $lastLog, $matches);
    if (!empty($matches[1])) {
        $lastDeploymentTime = $matches[1];
    }
}

?>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl shadow-xl p-4 md:p-6 lg:p-8 mb-4 md:mb-6 text-white">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-1 md:mb-2">
                    <i class="fas fa-rocket mr-2 md:mr-3"></i>
                    Deployment Management
                </h1>
                <p class="text-blue-100 text-sm md:text-lg">Deploy your code and database to production server</p>
            </div>
            <div class="bg-white/20 rounded-full px-4 md:px-6 py-2 md:py-3 backdrop-blur-sm">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-server"></i>
                    <span class="font-semibold text-sm md:text-base">s3vgroup.com</span>
                </div>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
    <div class="bg-<?= $messageType === 'success' ? 'green' : ($messageType === 'error' ? 'red' : 'yellow') ?>-100 border-l-4 border-<?= $messageType === 'success' ? 'green' : ($messageType === 'error' ? 'red' : 'yellow') ?>-500 text-<?= $messageType === 'success' ? 'green' : ($messageType === 'error' ? 'red' : 'yellow') ?>-700 p-4 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : ($messageType === 'error' ? 'exclamation-circle' : 'exclamation-triangle') ?> mr-2 text-xl"></i>
            <span class="font-semibold"><?= escape($message) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Deployment Status -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-4 md:mb-6">
        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg p-4 md:p-6 text-white">
            <div class="flex items-center justify-between mb-3 md:mb-4">
                <div class="bg-white/20 rounded-lg p-2 md:p-3">
                    <i class="fas fa-check-circle text-xl md:text-2xl"></i>
                </div>
                <div class="text-right">
                    <div class="text-base md:text-lg font-bold">
                        <?= $config['git']['enabled'] ?? false ? 'Enabled' : 'Disabled' ?>
                    </div>
                    <div class="text-green-100 text-xs">Git Push</div>
                </div>
            </div>
            <div class="text-green-100 text-xs md:text-sm font-medium">Git Integration</div>
        </div>
        
        <div class="bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl shadow-lg p-4 md:p-6 text-white">
            <div class="flex items-center justify-between mb-3 md:mb-4">
                <div class="bg-white/20 rounded-lg p-2 md:p-3">
                    <i class="fas fa-cloud text-xl md:text-2xl"></i>
                </div>
                <div class="text-right">
                    <div class="text-base md:text-lg font-bold">
                        <?= $config['ftp']['enabled'] ?? false ? 'Enabled' : 'Disabled' ?>
                    </div>
                    <div class="text-blue-100 text-xs">FTP Upload</div>
                </div>
            </div>
            <div class="text-blue-100 text-xs md:text-sm font-medium">FTP Integration</div>
        </div>
        
        <div class="bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl shadow-lg p-4 md:p-6 text-white">
            <div class="flex items-center justify-between mb-3 md:mb-4">
                <div class="bg-white/20 rounded-lg p-2 md:p-3">
                    <i class="fas fa-clock text-xl md:text-2xl"></i>
                </div>
                <div class="text-right">
                    <div class="text-base md:text-lg font-bold">
                        <?= $lastDeploymentTime ? date('M d, H:i', strtotime($lastDeploymentTime)) : 'Never' ?>
                    </div>
                    <div class="text-purple-100 text-xs">Last Deploy</div>
                </div>
            </div>
            <div class="text-purple-100 text-xs md:text-sm font-medium">Last Deployment</div>
        </div>
    </div>

    <!-- Deployment Trigger -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-4 md:mb-6 border-2 border-blue-200">
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-4 md:p-6 text-white">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold mb-1">
                        <i class="fas fa-play-circle mr-2"></i>
                        Trigger Deployment
                    </h2>
                    <p class="text-blue-100 text-xs md:text-sm">Execute the full deployment process to production</p>
                </div>
                <div class="bg-white/20 rounded-full p-3 md:p-4">
                    <i class="fas fa-rocket text-2xl md:text-3xl"></i>
                </div>
            </div>
        </div>
        <div class="p-4 md:p-6">
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
                <p class="text-sm text-gray-700 mb-3">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                    <strong>Deployment Process:</strong>
                </p>
                <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700">
                    <li>Pull database from remote (if enabled)</li>
                    <li>Clean unnecessary files (logs, cache, temp files)</li>
                    <li>Validate code and configuration</li>
                    <li>Push to GitHub (if enabled)</li>
                    <li>Upload files via FTP</li>
                    <li>Import database to remote (if enabled)</li>
                </ol>
            </div>
            
            <form method="POST" onsubmit="return confirmDeployment()">
                <input type="hidden" name="trigger_deployment" value="1">
                <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white py-5 rounded-lg font-bold text-xl hover:from-blue-600 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-2xl transform hover:scale-105">
                    <i class="fas fa-rocket mr-2"></i>
                    Deploy to Production
                </button>
            </form>
            
            <div class="mt-4 text-center">
                <a href="<?= url('developer/deployment-logs.php') ?>" class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                    <i class="fas fa-file-alt mr-1"></i>
                    View Deployment Logs
                </a>
            </div>
        </div>
    </div>

    <!-- Configuration Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6">
        <!-- Deployment Settings -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center mb-4">
                <div class="bg-purple-100 rounded-lg p-3 mr-3">
                    <i class="fas fa-cog text-purple-600 text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800">Deployment Settings</h3>
            </div>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">
                        <i class="fas fa-code-branch text-gray-400 mr-2"></i>
                        Git Branch
                    </span>
                    <span class="text-sm font-semibold text-gray-900"><?= escape($config['git']['branch'] ?? 'main') ?></span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">
                        <i class="fas fa-database text-gray-400 mr-2"></i>
                        Auto Pull Before Deploy
                    </span>
                    <span class="text-sm font-semibold <?= ($config['database_sync']['auto_pull_before_deploy'] ?? false) ? 'text-green-600' : 'text-gray-600' ?>">
                        <?= ($config['database_sync']['auto_pull_before_deploy'] ?? false) ? 'Yes' : 'No' ?>
                    </span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">
                        <i class="fas fa-upload text-gray-400 mr-2"></i>
                        Auto Import After Deploy
                    </span>
                    <span class="text-sm font-semibold <?= ($config['database_import']['auto_import'] ?? false) ? 'text-green-600' : 'text-gray-600' ?>">
                        <?= ($config['database_import']['auto_import'] ?? false) ? 'Yes' : 'No' ?>
                    </span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">
                        <i class="fas fa-broom text-gray-400 mr-2"></i>
                        Cleanup Enabled
                    </span>
                    <span class="text-sm font-semibold <?= ($config['cleanup']['enabled'] ?? false) ? 'text-green-600' : 'text-gray-600' ?>">
                        <?= ($config['cleanup']['enabled'] ?? false) ? 'Yes' : 'No' ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Remote Server Info -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center mb-4">
                <div class="bg-blue-100 rounded-lg p-3 mr-3">
                    <i class="fas fa-server text-blue-600 text-xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800">Remote Server</h3>
            </div>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">
                        <i class="fas fa-globe text-gray-400 mr-2"></i>
                        Website URL
                    </span>
                    <span class="text-sm font-semibold text-gray-900"><?= escape($config['website_url'] ?? 'Not set') ?></span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">
                        <i class="fas fa-folder text-gray-400 mr-2"></i>
                        Remote Path
                    </span>
                    <span class="text-sm font-semibold text-gray-900"><?= escape($config['ftp']['remote_path'] ?? 'Not set') ?></span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">
                        <i class="fas fa-database text-gray-400 mr-2"></i>
                        Database Name
                    </span>
                    <span class="text-sm font-semibold text-gray-900"><?= escape($config['database_remote']['dbname'] ?? 'Not set') ?></span>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-medium text-gray-700">
                        <i class="fas fa-edit text-gray-400 mr-2"></i>
                        Config File
                    </span>
                    <span class="text-sm font-semibold text-gray-900">deploy-config.json</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDeployment() {
    return confirm('⚠️ Are you sure you want to deploy to production?\n\nThis will:\n- Push code to GitHub\n- Upload files via FTP\n- Update database on server\n\nMake sure you\'ve tested everything locally first!');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
