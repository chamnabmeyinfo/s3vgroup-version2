<?php
/**
 * Deployment Cleanup Script
 * Removes unnecessary files to make the project lightweight before deployment
 */

class DeploymentCleanup {
    private $baseDir;
    private $removedFiles = [];
    private $removedDirs = [];
    private $totalSize = 0;
    private $log;
    
    public function __construct($baseDir = null) {
        $this->baseDir = $baseDir ?: __DIR__;
        $this->log = new class {
            public function info($msg) { echo "[INFO] " . $msg . "\n"; }
            public function warning($msg) { echo "[WARNING] " . $msg . "\n"; }
            public function error($msg) { echo "[ERROR] " . $msg . "\n"; }
        };
    }
    
    /**
     * Clean unnecessary files
     */
    public function clean($options = []) {
        $options = array_merge([
            'remove_logs' => true,
            'remove_cache' => true,
            'remove_test_files' => true,
            'remove_temp_files' => true,
            'remove_os_files' => true,
            'remove_old_backups' => false,
            'backup_days_to_keep' => 7,
            'keep_deploy_log' => true,
        ], $options);
        
        $this->log->info("Starting cleanup...");
        $this->log->info("========================================");
        
        if ($options['remove_logs']) {
            $this->cleanLogFiles($options['keep_deploy_log']);
        }
        
        if ($options['remove_cache']) {
            $this->cleanCacheFiles();
        }
        
        if ($options['remove_test_files']) {
            $this->cleanTestFiles();
        }
        
        if ($options['remove_temp_files']) {
            $this->cleanTempFiles();
        }
        
        if ($options['remove_os_files']) {
            $this->cleanOSFiles();
        }
        
        if ($options['remove_old_backups']) {
            $this->cleanOldBackups($options['backup_days_to_keep']);
        }
        
        $this->log->info("========================================");
        $this->log->info("Cleanup complete!");
        $this->log->info("Files removed: " . count($this->removedFiles));
        $this->log->info("Directories removed: " . count($this->removedDirs));
        $this->log->info("Total space freed: " . $this->formatBytes($this->totalSize));
        
        return [
            'files_removed' => count($this->removedFiles),
            'dirs_removed' => count($this->removedDirs),
            'space_freed' => $this->totalSize,
        ];
    }
    
    /**
     * Clean log files
     */
    private function cleanLogFiles($keepDeployLog = true) {
        $this->log->info("Cleaning log files...");
        
        $patterns = ['*.log'];
        $exclude = $keepDeployLog ? ['deploy-log.txt'] : [];
        
        foreach ($patterns as $pattern) {
            $files = glob($this->baseDir . '/' . $pattern);
            foreach ($files as $file) {
                $basename = basename($file);
                if (!in_array($basename, $exclude)) {
                    $this->removeFile($file);
                }
            }
        }
        
        // Clean storage/logs directory (keep .gitkeep)
        $logsDir = $this->baseDir . '/storage/logs';
        if (is_dir($logsDir)) {
            $files = glob($logsDir . '/*');
            foreach ($files as $file) {
                if (basename($file) !== '.gitkeep' && is_file($file)) {
                    $this->removeFile($file);
                }
            }
        }
    }
    
    /**
     * Clean cache files
     */
    private function cleanCacheFiles() {
        $this->log->info("Cleaning cache files...");
        
        // Clean *.cache files
        $files = glob($this->baseDir . '/*.cache');
        foreach ($files as $file) {
            $this->removeFile($file);
        }
        
        // Clean storage/cache directory (keep .gitkeep)
        $cacheDir = $this->baseDir . '/storage/cache';
        if (is_dir($cacheDir)) {
            $files = glob($cacheDir . '/*');
            foreach ($files as $file) {
                if (basename($file) !== '.gitkeep' && is_file($file)) {
                    $this->removeFile($file);
                }
            }
        }
    }
    
    /**
     * Clean test files
     */
    private function cleanTestFiles() {
        $this->log->info("Cleaning test files...");
        
        $patterns = [
            'test-*.php',
            '*test.php',
            'hello.php',
            'fix-*.php',
            'verify-*.php',
            'check-*.php',
        ];
        
        foreach ($patterns as $pattern) {
            $files = glob($this->baseDir . '/' . $pattern);
            foreach ($files as $file) {
                // Don't remove setup files or important files
                $basename = basename($file);
                if (strpos($basename, 'setup') === false && 
                    strpos($basename, 'test-connection') === false) {
                    $this->removeFile($file);
                }
            }
        }
    }
    
    /**
     * Clean temporary files
     */
    private function cleanTempFiles() {
        $this->log->info("Cleaning temporary files...");
        
        $patterns = ['*.tmp', '*.temp', '*.bak', '*.backup'];
        
        foreach ($patterns as $pattern) {
            $files = glob($this->baseDir . '/' . $pattern);
            foreach ($files as $file) {
                $this->removeFile($file);
            }
            
            // Also search in subdirectories (but not too deep)
            $files = glob($this->baseDir . '/*/' . $pattern);
            foreach ($files as $file) {
                $this->removeFile($file);
            }
        }
    }
    
    /**
     * Clean OS-specific files
     */
    private function cleanOSFiles() {
        $this->log->info("Cleaning OS files...");
        
        $patterns = [
            '.DS_Store',
            '.DS_Store?',
            '._*',
            'Thumbs.db',
            'desktop.ini',
            'ehthumbs.db',
        ];
        
        foreach ($patterns as $pattern) {
            $files = glob($this->baseDir . '/' . $pattern);
            foreach ($files as $file) {
                $this->removeFile($file);
            }
        }
    }
    
    /**
     * Clean old backup files
     */
    private function cleanOldBackups($daysToKeep = 7) {
        $this->log->info("Cleaning old backup files (keeping last {$daysToKeep} days)...");
        
        $backupDir = $this->baseDir . '/storage/backups';
        if (!is_dir($backupDir)) {
            return;
        }
        
        $cutoffTime = time() - ($daysToKeep * 24 * 60 * 60);
        $files = glob($backupDir . '/db_backup_*');
        
        foreach ($files as $file) {
            if (basename($file) !== '.gitkeep' && is_file($file)) {
                if (filemtime($file) < $cutoffTime) {
                    $this->removeFile($file);
                }
            }
        }
    }
    
    /**
     * Remove a file
     */
    private function removeFile($file) {
        if (file_exists($file) && is_file($file)) {
            $size = filesize($file);
            if (@unlink($file)) {
                $this->removedFiles[] = $file;
                $this->totalSize += $size;
                $this->log->info("  ✓ Removed: " . basename($file) . " (" . $this->formatBytes($size) . ")");
            }
        }
    }
    
    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

// Run cleanup if called directly
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $cleanup = new DeploymentCleanup();
    
    // Load config if exists
    $configFile = __DIR__ . '/deploy-config.json';
    $options = [];
    
    if (file_exists($configFile)) {
        $config = json_decode(file_get_contents($configFile), true);
        if (isset($config['cleanup'])) {
            $options = $config['cleanup'];
        }
    }
    
    $result = $cleanup->clean($options);
    exit($result['files_removed'] > 0 ? 0 : 1);
}

