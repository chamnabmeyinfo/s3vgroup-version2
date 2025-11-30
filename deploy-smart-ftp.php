<?php
/**
 * Smart FTP Deployment Module
 * Enhanced with change detection, conflict detection, and backup
 */

function deployFTP($config, $log) {
    $result = ['success' => false, 'message' => '', 'files_uploaded' => 0, 'files_skipped' => 0];
    
    // Check FTP config
    $ftpConfig = $config['ftp'] ?? [];
    if (empty($ftpConfig['host']) || empty($ftpConfig['username'])) {
        $result['message'] = 'FTP configuration incomplete';
        return $result;
    }
    
    // Connect to FTP
    $log->info("  Connecting to FTP server...");
    $ftp = @ftp_connect($ftpConfig['host'], $ftpConfig['port'] ?? 21);
    
    if (!$ftp) {
        $result['message'] = 'Failed to connect to FTP server';
        return $result;
    }
    
    // Login
    $password = $ftpConfig['password'] ?? '';
    if (!@ftp_login($ftp, $ftpConfig['username'], $password)) {
        ftp_close($ftp);
        $result['message'] = 'FTP login failed';
        return $result;
    }
    
    // Enable passive mode
    ftp_pasv($ftp, true);
    $log->info("  ✓ Connected");
    
    // Get files to upload
    $filesToUpload = getFilesToUpload($config, $log);
    
    if (empty($filesToUpload)) {
        $log->info("  No files to upload");
        ftp_close($ftp);
        $result['success'] = true;
        $result['message'] = 'No files to upload';
        return $result;
    }
    
    $log->info("  Found " . count($filesToUpload) . " file(s) to check");
    
    // Set remote path
    $remotePath = rtrim($ftpConfig['remote_path'] ?? '/public_html', '/');
    
    // Smart analysis: Check which files need uploading
    $log->info("  Analyzing files (change detection)...");
    $filesNeedingUpload = [];
    $filesToSkip = [];
    $conflicts = [];
    
    foreach ($filesToUpload as $file) {
        $localPath = $file['local'];
        $remoteFile = $remotePath . '/' . $file['remote'];
        
        // Get local file info
        $localSize = filesize($localPath);
        $localTime = filemtime($localPath);
        
        // Check if remote file exists and get its info
        $remoteExists = false;
        $remoteSize = 0;
        $remoteTime = 0;
        
        // Try to get remote file size
        $remoteSize = @ftp_size($ftp, $remoteFile);
        if ($remoteSize >= 0) {
            $remoteExists = true;
            // Try to get modification time (if supported)
            $remoteTime = @ftp_mdtm($ftp, $remoteFile);
        }
        
        // Smart Decision Logic
        if (!$remoteExists) {
            // New file - always upload
            $filesNeedingUpload[] = [
                'file' => $file,
                'reason' => 'new',
                'local_size' => $localSize,
                'local_time' => $localTime
            ];
        } elseif ($remoteSize != $localSize) {
            // Size different - file changed
            $filesNeedingUpload[] = [
                'file' => $file,
                'reason' => 'changed (size)',
                'local_size' => $localSize,
                'remote_size' => $remoteSize,
                'local_time' => $localTime,
                'remote_time' => $remoteTime
            ];
        } elseif ($remoteTime > 0 && $localTime < $remoteTime) {
            // Remote is newer - conflict!
            $conflicts[] = [
                'file' => $file,
                'local_time' => date('Y-m-d H:i:s', $localTime),
                'remote_time' => date('Y-m-d H:i:s', $remoteTime),
                'local_size' => $localSize,
                'remote_size' => $remoteSize
            ];
        } elseif ($remoteTime > 0 && $localTime == $remoteTime && $remoteSize == $localSize) {
            // Files are identical - skip
            $filesToSkip[] = [
                'file' => $file,
                'reason' => 'unchanged'
            ];
        } else {
            // Default: upload if in doubt
            $filesNeedingUpload[] = [
                'file' => $file,
                'reason' => 'upload (uncertain)',
                'local_size' => $localSize,
                'local_time' => $localTime
            ];
        }
    }
    
    // Report analysis
    $log->info("  Analysis complete:");
    $log->info("    - New/changed: " . count($filesNeedingUpload));
    $log->info("    - Unchanged (skipped): " . count($filesToSkip));
    $log->info("    - Conflicts: " . count($conflicts));
    
    // Handle conflicts
    if (!empty($conflicts)) {
        $log->warning("  ⚠️  Conflicts detected!");
        foreach ($conflicts as $conflict) {
            $log->warning("    - {$conflict['file']['remote']}");
            $log->warning("      Local: {$conflict['local_time']} ({$conflict['local_size']} bytes)");
            $log->warning("      Remote: {$conflict['remote_time']} ({$conflict['remote_size']} bytes)");
        }
        
        // Auto-backup if enabled
        if ($config['upload']['create_backup'] ?? true) {
            $log->info("  Creating backups for conflicted files...");
            foreach ($conflicts as $conflict) {
                backupRemoteFile($ftp, $remotePath, $conflict['file']['remote'], $log);
            }
            // Add conflicts to upload list (after backup)
            foreach ($conflicts as $conflict) {
                $filesNeedingUpload[] = [
                    'file' => $conflict['file'],
                    'reason' => 'conflict (backed up)',
                    'local_size' => $conflict['local_size'],
                    'local_time' => strtotime($conflict['local_time'])
                ];
            }
        } else {
            $log->warning("  Skipping conflicted files (backup disabled)");
        }
    }
    
    // Upload files
    $uploaded = 0;
    $failed = 0;
    $totalFiles = count($filesNeedingUpload);
    
    if ($totalFiles > 0) {
        $log->info("  Uploading {$totalFiles} file(s)...");
        
        foreach ($filesNeedingUpload as $index => $fileInfo) {
            $file = $fileInfo['file'];
            $localPath = $file['local'];
            $remoteFile = $remotePath . '/' . $file['remote'];
            $reason = $fileInfo['reason'];
            
            // Create remote directory if needed
            $remoteDir = dirname($remoteFile);
            createRemoteDirectory($ftp, $remoteDir);
            
            // Show progress
            $progress = round((($index + 1) / $totalFiles) * 100);
            $log->info("  [{$progress}%] Uploading: {$file['remote']} ({$reason})...");
            
            // Upload file
            if (@ftp_put($ftp, $remoteFile, $localPath, FTP_BINARY)) {
                $uploaded++;
                
                // Set permissions if specified
                if (isset($file['permissions'])) {
                    ftp_chmod($ftp, $file['permissions'], $remoteFile);
                }
                
                $log->info("    ✓ Uploaded");
            } else {
                $failed++;
                $log->error("    ✗ Failed to upload");
            }
        }
    }
    
    ftp_close($ftp);
    
    // Final summary
    $result['files_uploaded'] = $uploaded;
    $result['files_skipped'] = count($filesToSkip);
    $result['files_conflicted'] = count($conflicts);
    
    if ($failed > 0) {
        $result['message'] = "Uploaded {$uploaded}, skipped " . count($filesToSkip) . ", failed {$failed}";
    } else {
        $result['success'] = true;
        $saved = count($filesToSkip);
        $result['message'] = "Uploaded {$uploaded} file(s)";
        if ($saved > 0) {
            $result['message'] .= ", skipped {$saved} unchanged";
        }
    }
    
    return $result;
}

function backupRemoteFile($ftp, $remotePath, $remoteFile, $log) {
    $backupDir = $remotePath . '/.deployment-backups/' . date('Y-m-d_H-i-s');
    
    // Create backup directory
    createRemoteDirectory($ftp, $backupDir);
    
    $backupFile = $backupDir . '/' . basename($remoteFile);
    
    // Check if remote file exists
    $remoteSize = @ftp_size($ftp, $remotePath . '/' . $remoteFile);
    if ($remoteSize < 0) {
        return false; // File doesn't exist, nothing to backup
    }
    
    // Download to temp, then upload to backup location
    $tempFile = sys_get_temp_dir() . '/' . basename($remoteFile) . '_' . time();
    
    if (@ftp_get($ftp, $tempFile, $remotePath . '/' . $remoteFile, FTP_BINARY)) {
        if (@ftp_put($ftp, $backupFile, $tempFile, FTP_BINARY)) {
            $log->info("    ✓ Backed up: {$remoteFile}");
            @unlink($tempFile);
            return true;
        }
        @unlink($tempFile);
    }
    
    return false;
}

function getFilesToUpload($config, $log) {
    $files = [];
    $uploadConfig = $config['upload'] ?? [];
    
    // Get ignored files from .gitignore
    $ignoredFiles = getIgnoredFiles();
    
    // Filter by category
    foreach ($ignoredFiles as $file) {
        $shouldUpload = false;
        $category = getFileCategory($file);
        
        // Check upload rules
        if ($category === 'image' && ($uploadConfig['images'] ?? true)) {
            $shouldUpload = true;
        } elseif ($category === 'config' && ($uploadConfig['configs'] ?? false)) {
            $shouldUpload = true;
        } elseif ($category === 'other' && ($uploadConfig['others'] ?? false)) {
            $shouldUpload = true;
        }
        
        if ($shouldUpload) {
            // Check exclude patterns
            $excludePatterns = $config['exclude'] ?? [];
            $excluded = false;
            
            foreach ($excludePatterns as $pattern) {
                if (fnmatch($pattern, $file)) {
                    $excluded = true;
                    break;
                }
            }
            
            if (!$excluded && file_exists($file)) {
                $files[] = [
                    'local' => $file,
                    'remote' => $file,
                    'permissions' => getFilePermissions($file)
                ];
            }
        }
    }
    
    return $files;
}

function getIgnoredFiles() {
    $files = [];
    
    // Read .gitignore
    if (!file_exists('.gitignore')) {
        return $files;
    }
    
    $baseDir = __DIR__ . '/';
    
    // Get actual ignored files
    exec('git status --ignored --porcelain', $output);
    
    foreach ($output as $line) {
        if (preg_match('/^!!\s+(.+)$/', $line, $matches)) {
            $file = trim($matches[1]);
            if (file_exists($file) && is_file($file)) {
                $files[] = $file;
            }
        }
    }
    
    // Also check storage/uploads directory
    $uploadsDir = __DIR__ . '/storage/uploads/';
    if (is_dir($uploadsDir)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($uploadsDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relativePath = str_replace($baseDir, '', $file->getPathname());
                $relativePath = str_replace('\\', '/', $relativePath);
                if (!in_array($relativePath, $files)) {
                    $files[] = $relativePath;
                }
            }
        }
    }
    
    return $files;
}

function getFileCategory($file) {
    if (strpos($file, 'storage/uploads/') !== false) {
        return 'image';
    } elseif (strpos($file, 'config/') !== false) {
        return 'config';
    } else {
        return 'other';
    }
}

function getFilePermissions($file) {
    // Default permissions
    if (is_dir($file)) {
        return 0755;
    } else {
        // Images and PHP files
        if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
            return 0644;
        } elseif (preg_match('/\.php$/', $file)) {
            return 0644;
        } else {
            return 0644;
        }
    }
}

function createRemoteDirectory($ftp, $dir) {
    $parts = explode('/', trim($dir, '/'));
    $currentDir = '';
    
    foreach ($parts as $part) {
        if (empty($part)) continue;
        $currentDir .= '/' . $part;
        
        // Try to create directory (will fail if exists, that's ok)
        @ftp_mkdir($ftp, $currentDir);
    }
}

