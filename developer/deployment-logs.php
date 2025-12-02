<?php
/**
 * Deployment Logs Viewer (Developer Only)
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/header.php';

$pageTitle = 'Deployment Logs';

$logFile = __DIR__ . '/../deploy-log.txt';
$logContent = '';

if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $fileSize = filesize($logFile);
    $lastModified = filemtime($logFile);
} else {
    $logContent = 'No deployment logs found.';
    $fileSize = 0;
    $lastModified = null;
}

// Count lines and extract key information
$lines = explode("\n", $logContent);
$lineCount = count($lines);
$errorCount = substr_count($logContent, '[ERROR]');
$warningCount = substr_count($logContent, '[WARNING]');
$successCount = substr_count($logContent, 'Deployment Complete!');

?>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="bg-gradient-to-r from-gray-700 to-gray-900 rounded-xl shadow-xl p-8 mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">
                    <i class="fas fa-file-alt mr-3"></i>
                    Deployment Logs
                </h1>
                <p class="text-gray-300 text-lg">View detailed deployment history and status</p>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="location.reload()" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition-all">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Refresh
                </button>
                <a href="<?= url('developer/deployment.php') ?>" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition-all">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back
                </a>
            </div>
        </div>
    </div>

    <!-- Log Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Lines</p>
                    <p class="text-2xl font-bold text-gray-800"><?= number_format($lineCount) ?></p>
                </div>
                <i class="fas fa-list text-blue-500 text-2xl"></i>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Successful</p>
                    <p class="text-2xl font-bold text-gray-800"><?= $successCount ?></p>
                </div>
                <i class="fas fa-check-circle text-green-500 text-2xl"></i>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Warnings</p>
                    <p class="text-2xl font-bold text-gray-800"><?= $warningCount ?></p>
                </div>
                <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl"></i>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Errors</p>
                    <p class="text-2xl font-bold text-gray-800"><?= $errorCount ?></p>
                </div>
                <i class="fas fa-times-circle text-red-500 text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Log File Info -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center space-x-4">
                <div>
                    <p class="text-sm text-gray-600">File Size</p>
                    <p class="font-semibold text-gray-800">
                        <?php
                        if ($fileSize > 1024 * 1024) {
                            echo number_format($fileSize / 1024 / 1024, 2) . ' MB';
                        } elseif ($fileSize > 1024) {
                            echo number_format($fileSize / 1024, 2) . ' KB';
                        } else {
                            echo $fileSize . ' bytes';
                        }
                        ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Last Modified</p>
                    <p class="font-semibold text-gray-800">
                        <?= $lastModified ? date('M d, Y H:i:s', $lastModified) : 'Never' ?>
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="scrollToTop()" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm font-medium transition-all">
                    <i class="fas fa-arrow-up mr-1"></i>
                    Top
                </button>
                <button onclick="scrollToBottom()" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm font-medium transition-all">
                    <i class="fas fa-arrow-down mr-1"></i>
                    Bottom
                </button>
                <button onclick="clearLog()" class="bg-red-100 hover:bg-red-200 text-red-700 px-4 py-2 rounded-lg text-sm font-medium transition-all">
                    <i class="fas fa-trash mr-1"></i>
                    Clear
                </button>
            </div>
        </div>
    </div>

    <!-- Log Content -->
    <div class="bg-gray-900 rounded-xl shadow-lg overflow-hidden">
        <div class="bg-gray-800 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="flex space-x-2">
                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                    <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                </div>
                <span class="text-gray-300 font-mono text-sm">deploy-log.txt</span>
            </div>
            <div class="text-gray-400 text-sm">
                <i class="fas fa-code mr-1"></i>
                Log Viewer
            </div>
        </div>
        <div class="p-6">
            <pre id="logContent" class="text-green-400 font-mono text-sm leading-relaxed overflow-x-auto" style="max-height: 70vh; overflow-y: auto;"><?php
                // Color code the log content
                $coloredLog = $logContent;
                $coloredLog = preg_replace('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', '<span class="text-blue-400">[$1]</span>', $coloredLog);
                $coloredLog = preg_replace('/\[INFO\]/', '<span class="text-blue-400">[INFO]</span>', $coloredLog);
                $coloredLog = preg_replace('/\[WARNING\]/', '<span class="text-yellow-400">[WARNING]</span>', $coloredLog);
                $coloredLog = preg_replace('/\[ERROR\]/', '<span class="text-red-400">[ERROR]</span>', $coloredLog);
                $coloredLog = preg_replace('/Deployment Complete!/', '<span class="text-green-500 font-bold">Deployment Complete!</span>', $coloredLog);
                $coloredLog = preg_replace('/✓/', '<span class="text-green-400">✓</span>', $coloredLog);
                $coloredLog = preg_replace('/⚠️/', '<span class="text-yellow-400">⚠️</span>', $coloredLog);
                $coloredLog = preg_replace('/✗/', '<span class="text-red-400">✗</span>', $coloredLog);
                echo $coloredLog;
            ?></pre>
        </div>
    </div>
</div>

<style>
#logContent {
    scrollbar-width: thin;
    scrollbar-color: #4B5563 #1F2937;
}

#logContent::-webkit-scrollbar {
    width: 8px;
}

#logContent::-webkit-scrollbar-track {
    background: #1F2937;
}

#logContent::-webkit-scrollbar-thumb {
    background: #4B5563;
    border-radius: 4px;
}

#logContent::-webkit-scrollbar-thumb:hover {
    background: #6B7280;
}
</style>

<script>
function scrollToTop() {
    document.getElementById('logContent').scrollTo({ top: 0, behavior: 'smooth' });
}

function scrollToBottom() {
    const logContent = document.getElementById('logContent');
    logContent.scrollTo({ top: logContent.scrollHeight, behavior: 'smooth' });
}

function clearLog() {
    if (confirm('⚠️ Are you sure you want to clear the deployment log?\n\nThis action cannot be undone.')) {
        // This would require a backend endpoint to clear the log
        alert('Log clearing feature requires backend implementation.');
    }
}

// Auto-scroll to bottom on load
window.addEventListener('load', function() {
    setTimeout(() => {
        scrollToBottom();
    }, 100);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
