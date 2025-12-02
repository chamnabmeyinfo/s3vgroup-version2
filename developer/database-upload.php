<?php
/**
 * Database Upload (Developer Only)
 * Upload and import database to remote server
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/header.php';

use App\Core\Backup\BackupService;

$pageTitle = 'Database Upload';

$message = '';
$messageType = '';
$backupService = new BackupService();

// Handle upload request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_database'])) {
    try {
        $configFile = __DIR__ . '/../deploy-config.json';
        if (!file_exists($configFile)) {
            throw new Exception('deploy-config.json not found');
        }
        
        $config = json_decode(file_get_contents($configFile), true);
        if (!$config) {
            throw new Exception('Invalid deploy-config.json format');
        }
        
        $config['database_upload']['compress'] = isset($_POST['compress']) && $_POST['compress'] === '1';
        $config['database_upload']['keep_local_copy'] = isset($_POST['keep_local']) && $_POST['keep_local'] === '1';
        $config['database_upload']['remote_path'] = $_POST['remote_path'] ?? '/backups';
        $config['database_upload']['enabled'] = true;

        require_once __DIR__ . '/../deploy-database-upload.php';
        $tempLog = new class extends \DeploymentLogger {
            public $outputBuffer = '';
            public function __construct() { 
                parent::__construct('php://memory'); 
                $this->consoleOutput = false; 
            }
            protected function log($level, $message) { 
                $this->outputBuffer .= "[{$level}] {$message}\n"; 
            }
        };
        
        $uploadResult = uploadDatabaseToCPanel($config, $tempLog);

        if ($uploadResult['success']) {
            $message = $uploadResult['message'];
            $messageType = 'success';
        } else {
            $message = $uploadResult['message'];
            $messageType = 'error';
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

$backups = $backupService->listBackups();

?>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="bg-gradient-to-r from-cyan-600 to-teal-600 rounded-xl shadow-xl p-8 mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">
                    <i class="fas fa-cloud-upload-alt mr-3"></i>
                    Database Upload
                </h1>
                <p class="text-cyan-100 text-lg">Upload your local database to the remote server</p>
            </div>
            <div class="bg-white/20 rounded-full px-6 py-3 backdrop-blur-sm">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-server"></i>
                    <span class="font-semibold">s3vgroup.com</span>
                </div>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
    <div class="bg-<?= $messageType === 'success' ? 'green' : 'red' ?>-100 border-l-4 border-<?= $messageType === 'success' ? 'green' : 'red' ?>-500 text-<?= $messageType === 'success' ? 'green' : 'red' ?>-700 p-4 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?> mr-2 text-xl"></i>
            <span class="font-semibold"><?= escape($message) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Upload Form -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6 border-2 border-cyan-200">
        <div class="bg-gradient-to-r from-cyan-500 to-teal-600 p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold mb-1">
                        <i class="fas fa-upload mr-2"></i>
                        Upload Database to Remote Server
                    </h2>
                    <p class="text-cyan-100 text-sm">Create backup and upload to production</p>
                </div>
                <div class="bg-white/20 rounded-full p-4">
                    <i class="fas fa-cloud-upload-alt text-3xl"></i>
                </div>
            </div>
        </div>
        <div class="p-6">
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded">
                <p class="text-sm text-gray-700">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                    This will create a backup of your local database and upload it to s3vgroup.com via FTP.
                    The backup will be stored on the remote server for future use.
                </p>
            </div>
            
            <form method="POST" onsubmit="return confirmUpload()" class="space-y-5">
                <input type="hidden" name="upload_database" value="1">
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-folder text-cyan-600 mr-1"></i> Remote Path
                    </label>
                    <input type="text" name="remote_path" value="/backups" placeholder="/backups" 
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all">
                    <p class="text-xs text-gray-500 mt-1">Path on remote server where backup will be stored</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center space-x-3 bg-gray-50 p-4 rounded-lg">
                        <input type="checkbox" name="compress" id="compress" checked 
                               class="w-5 h-5 text-cyan-600 border-gray-300 rounded focus:ring-cyan-500">
                        <label for="compress" class="text-sm font-medium text-gray-700 cursor-pointer">
                            <i class="fas fa-compress text-cyan-600 mr-1"></i>
                            Compress backup file (Recommended)
                        </label>
                    </div>
                    
                    <div class="flex items-center space-x-3 bg-gray-50 p-4 rounded-lg">
                        <input type="checkbox" name="keep_local" id="keep_local" checked 
                               class="w-5 h-5 text-cyan-600 border-gray-300 rounded focus:ring-cyan-500">
                        <label for="keep_local" class="text-sm font-medium text-gray-700 cursor-pointer">
                            <i class="fas fa-save text-cyan-600 mr-1"></i>
                            Keep local copy of backup
                        </label>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-cyan-500 to-teal-600 text-white py-5 rounded-lg font-bold text-xl hover:from-cyan-600 hover:to-teal-700 transition-all duration-300 shadow-lg hover:shadow-2xl transform hover:scale-105">
                    <i class="fas fa-cloud-upload-alt mr-2"></i>
                    Upload Database to Remote Server
                </button>
            </form>
        </div>
    </div>

    <!-- Available Backups -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-gray-800 to-gray-900 p-6 text-white">
            <h3 class="text-xl font-bold">
                <i class="fas fa-database mr-2"></i>
                Available Local Backups
            </h3>
        </div>
        <div class="p-6">
            <?php if (empty($backups)): ?>
                <div class="text-center py-12">
                    <div class="inline-block bg-gray-100 rounded-full p-6 mb-4">
                        <i class="fas fa-database text-4xl text-gray-400"></i>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-700 mb-2">No Backups Found</h4>
                    <p class="text-gray-500 mb-4">Create a backup first to upload it to the remote server.</p>
                    <a href="<?= url('developer/backup.php') ?>" class="inline-block bg-cyan-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-cyan-700 transition-all">
                        <i class="fas fa-plus-circle mr-2"></i>
                        Create Backup
                    </a>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase">File Name</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase">Size</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase">Created</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($backups as $backup): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <i class="fas fa-file-archive text-cyan-600 mr-3"></i>
                                        <span class="text-sm font-medium text-gray-900"><?= escape($backup['filename'] ?? $backup['file'] ?? 'Unknown') ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?= escape($backup['size'] ?? 'Unknown') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?= escape($backup['created'] ?? $backup['date'] ?? 'Unknown') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="<?= url('developer/backup-download.php?file=' . urlencode($backup['filename'] ?? $backup['file'] ?? '')) ?>" 
                                       class="text-cyan-600 hover:text-cyan-800 font-medium text-sm transition-colors">
                                        <i class="fas fa-download mr-1"></i>
                                        Download
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function confirmUpload() {
    return confirm('⚠️ Are you sure you want to upload the database to the remote server?\n\nThis will:\n- Create a backup of your local database\n- Upload it to s3vgroup.com via FTP\n- Store it on the remote server');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
