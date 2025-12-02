<?php
/**
 * Database SQL Upload to cPanel via FTP
 * Function to automatically create database backup and upload to cPanel
 */

function uploadDatabaseToCPanel($config, $log) {
    $result = ['success' => false, 'message' => '', 'file' => ''];
    
    $ftpConfig = $config['ftp'] ?? [];
    if (empty($ftpConfig['host']) || empty($ftpConfig['username'])) {
        $result['message'] = 'FTP configuration incomplete';
        return $result;
    }
    
    // Database upload settings
    $dbUploadConfig = $config['database_upload'] ?? [
        'enabled' => true,
        'remote_path' => '/backups',
        'keep_local_copy' => true,
        'compress' => true
    ];
    
    if (!($dbUploadConfig['enabled'] ?? true)) {
        $result['message'] = 'Database upload is disabled';
        return $result;
    }
    
    try {
        // Step 1: Create database backup
        $log->info("  [1/3] Creating database backup...");
        $backupService = new \App\Core\Backup\BackupService();
        $backupFile = $backupService->backupDatabase();
        
        if (!$backupFile || !file_exists($backupFile)) {
            throw new Exception("Failed to create database backup");
        }
        
        $backupSize = filesize($backupFile);
        $backupSizeMB = round($backupSize / 1024 / 1024, 2);
        $log->info("    ✓ Backup created: " . basename($backupFile) . " ({$backupSizeMB} MB)");
        
        // Step 2: Compress if enabled
        $fileToUpload = $backupFile;
        $compressedFile = null;
        if ($dbUploadConfig['compress'] ?? true) {
            $log->info("  [2/3] Compressing backup...");
            $compressedFile = $backupFile . '.gz';
            
            $fp_in = fopen($backupFile, 'rb');
            $fp_out = gzopen($compressedFile, 'wb9');
            
            if (!$fp_in || !$fp_out) {
                throw new Exception("Failed to open files for compression");
            }
            
            while (!feof($fp_in)) {
                gzwrite($fp_out, fread($fp_in, 8192));
            }
            
            fclose($fp_in);
            gzclose($fp_out);
            
            $compressedSize = filesize($compressedFile);
            $compressedSizeMB = round($compressedSize / 1024 / 1024, 2);
            $compressionRatio = round((1 - $compressedSize / $backupSize) * 100, 1);
            
            $log->info("    ✓ Compressed: " . basename($compressedFile) . " ({$compressedSizeMB} MB, {$compressionRatio}% reduction)");
            $fileToUpload = $compressedFile;
        }
        
        // Step 3: Upload to cPanel via FTP
        $log->info("  [3/3] Uploading to cPanel via FTP...");
        
        // Connect to FTP
        $ftp = @ftp_connect($ftpConfig['host'], $ftpConfig['port'] ?? 21);
        if (!$ftp) {
            throw new Exception("Failed to connect to FTP server: " . $ftpConfig['host']);
        }
        
        // Login
        $password = $ftpConfig['password'] ?? '';
        if (!@ftp_login($ftp, $ftpConfig['username'], $password)) {
            ftp_close($ftp);
            throw new Exception("FTP login failed");
        }
        
        // Enable passive mode
        ftp_pasv($ftp, true);
        $log->info("    ✓ Connected to FTP server");
        
        // Set remote path
        $remotePath = rtrim($ftpConfig['remote_path'] ?? '/public_html', '/');
        $dbRemotePath = $remotePath . '/' . ltrim($dbUploadConfig['remote_path'] ?? '/backups', '/');
        
        // Create remote directory if it doesn't exist
        $pathParts = explode('/', trim($dbRemotePath, '/'));
        $currentPath = $remotePath;
        foreach ($pathParts as $part) {
            if (empty($part)) continue;
            $currentPath .= '/' . $part;
            @ftp_mkdir($ftp, $currentPath);
        }
        
        // Upload file
        $remoteFile = $dbRemotePath . '/' . basename($fileToUpload);
        $log->info("    Uploading to: {$remoteFile}");
        
        $uploadSuccess = @ftp_put($ftp, $remoteFile, $fileToUpload, FTP_BINARY);
        
        if (!$uploadSuccess) {
            ftp_close($ftp);
            throw new Exception("Failed to upload file to FTP server");
        }
        
        // Get remote file size for verification
        $remoteSize = @ftp_size($ftp, $remoteFile);
        $localSize = filesize($fileToUpload);
        
        ftp_close($ftp);
        
        if ($remoteSize > 0 && $remoteSize == $localSize) {
            $log->info("    ✓ Upload successful! Remote size: " . round($remoteSize / 1024 / 1024, 2) . " MB");
        } else {
            $log->warning("    ⚠️  Upload completed but size verification failed (Local: {$localSize}, Remote: {$remoteSize})");
        }
        
        // Clean up compressed file if we're keeping local copy
        if ($compressedFile && file_exists($compressedFile)) {
            if (!($dbUploadConfig['keep_local_copy'] ?? true)) {
                unlink($compressedFile);
                $log->info("    ✓ Removed local compressed file");
            }
        }
        
        // Clean up original SQL if compressed and not keeping local copy
        if ($compressedFile && !($dbUploadConfig['keep_local_copy'] ?? true)) {
            if (file_exists($backupFile)) {
                unlink($backupFile);
                $log->info("    ✓ Removed local SQL file");
            }
        }
        
        $result['success'] = true;
        $result['message'] = "Database uploaded successfully to: {$remoteFile}";
        $result['file'] = $remoteFile;
        
    } catch (Exception $e) {
        $result['message'] = "Error: " . $e->getMessage();
        $log->error("  ✗ " . $result['message']);
    }
    
    return $result;
}
