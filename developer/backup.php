<?php
/**
 * Database Backup Management (Developer Only)
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/header.php';

use App\Core\Backup\BackupService;

$pageTitle = 'Database Backup';

$backupService = new BackupService();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'create_backup') {
        try {
            $backupFile = $backupService->backupDatabase();
            $message = 'Backup created successfully! File: ' . basename($backupFile);
        } catch (\Exception $e) {
            $error = 'Error creating backup: ' . $e->getMessage();
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete_backup') {
        $filename = $_POST['filename'] ?? '';
        if ($filename) {
            $backupPath = __DIR__ . '/../storage/backups/' . basename($filename);
            if (file_exists($backupPath)) {
                unlink($backupPath);
                $message = 'Backup deleted successfully!';
            } else {
                $error = 'Backup file not found!';
            }
        }
    }
}

$backups = $backupService->listBackups();

?>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl shadow-xl p-8 mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">
                    <i class="fas fa-database mr-3"></i>
                    Database Backup Management
                </h1>
                <p class="text-purple-100 text-lg">Create, download, and manage database backups</p>
            </div>
            <form method="POST" class="inline">
                <input type="hidden" name="action" value="create_backup">
                <button type="submit" class="bg-white text-purple-600 px-6 py-3 rounded-lg font-bold hover:bg-purple-50 transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Create Backup
                </button>
            </form>
        </div>
    </div>

    <?php if ($message): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <?= escape($message) ?>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?= escape($error) ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Backup List -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">
                <i class="fas fa-list mr-2 text-purple-600"></i>
                Available Backups
            </h2>
        </div>
        
        <?php if (empty($backups)): ?>
            <div class="p-12 text-center">
                <div class="inline-block bg-gray-100 rounded-full p-6 mb-4">
                    <i class="fas fa-database text-4xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Backups Found</h3>
                <p class="text-gray-500 mb-6">Create your first database backup to get started.</p>
                <form method="POST" class="inline">
                    <input type="hidden" name="action" value="create_backup">
                    <button type="submit" class="bg-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-purple-700 transition-all">
                        <i class="fas fa-plus-circle mr-2"></i>
                        Create First Backup
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Backup File</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Size</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Date Created</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($backups as $backup): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i class="fas fa-file-archive text-purple-600 mr-3"></i>
                                    <span class="text-sm font-medium text-gray-900"><?= escape($backup['file'] ?? $backup['filename'] ?? 'Unknown') ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-600">
                                    <?php
                                    $size = $backup['size'] ?? 0;
                                    if ($size > 1024 * 1024) {
                                        echo number_format($size / 1024 / 1024, 2) . ' MB';
                                    } else {
                                        echo number_format($size / 1024, 2) . ' KB';
                                    }
                                    ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-600"><?= escape($backup['date'] ?? $backup['created'] ?? 'Unknown') ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center space-x-3">
                                    <a href="<?= url('developer/backup-download.php?file=' . urlencode($backup['file'] ?? $backup['filename'] ?? '')) ?>" 
                                       class="text-blue-600 hover:text-blue-800 font-medium text-sm transition-colors">
                                        <i class="fas fa-download mr-1"></i>
                                        Download
                                    </a>
                                    <button onclick="confirmDelete('<?= escape($backup['file'] ?? $backup['filename'] ?? '') ?>')" 
                                            class="text-red-600 hover:text-red-800 font-medium text-sm transition-colors">
                                        <i class="fas fa-trash mr-1"></i>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Backup Information -->
    <div class="mt-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 rounded-xl p-6">
        <div class="flex items-start">
            <div class="bg-blue-500 rounded-lg p-3 mr-4">
                <i class="fas fa-info-circle text-white text-xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-800 mb-3 text-lg">Backup Information</h3>
                <ul class="text-sm text-gray-700 space-y-2">
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Backups are automatically compressed to save space
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Old backups (older than 30 days) are automatically deleted
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Backups include all database tables and data
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        Store backups in a secure location for disaster recovery
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(filename) {
    if (confirm('⚠️ Are you sure you want to delete this backup?\n\nFile: ' + filename + '\n\nThis action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="delete_backup">' +
                         '<input type="hidden" name="filename" value="' + filename + '">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

