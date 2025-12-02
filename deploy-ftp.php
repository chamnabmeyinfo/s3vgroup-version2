<?php
/**
 * FTP Deployment Module
 * Handles uploading non-Git files via FTP
 */

function deployFTP($config, $log) {
    $result = ['success' => false, 'message' => '', 'files_uploaded' => 0];
    
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
    
    $log->info("  Found " . count($filesToUpload) . " file(s) to upload");
    
    // Set remote path
    $remotePath = rtrim($ftpConfig['remote_path'] ?? '/public_html', '/');
    
    // Upload files
    $uploaded = 0;
    $failed = 0;
    
    foreach ($filesToUpload as $file) {
        $localPath = $file['local'];
        $remoteFile = $remotePath . '/' . $file['remote'];
        
        // Create remote directory if needed
        $remoteDir = dirname($remoteFile);
        createRemoteDirectory($ftp, $remoteDir);
        
        // Upload file
        $log->info("  Uploading: {$file['remote']}...");
        
        if (@ftp_put($ftp, $remoteFile, $localPath, FTP_BINARY)) {
            $uploaded++;
            $log->info("    ✓ Uploaded");
            
            // Set permissions if specified
            if (isset($file['permissions'])) {
                ftp_chmod($ftp, $file['permissions'], $remoteFile);
            }
        } else {
            $failed++;
            $log->error("    ✗ Failed to upload");
        }
    }
    
    ftp_close($ftp);
    
    if ($failed > 0) {
        $result['message'] = "Uploaded {$uploaded}, failed {$failed}";
    } else {
        $result['success'] = true;
        $result['files_uploaded'] = $uploaded;
        $result['message'] = "Uploaded {$uploaded} file(s)";
    }
    
    return $result;
}

function getFilesToUpload($config, $log) {
    $files = [];
    $uploadConfig = $config['upload'] ?? [];
    
    // Load exclude patterns from both config and deployment-exclude.txt
    $excludePatterns = $config['exclude'] ?? [];
    
    // Add standard excludes (always exclude these)
    $standardExcludes = [
        '.git',
        '.gitignore',
        'node_modules',
        'vendor',
        'composer.lock',
        'package-lock.json',
        'yarn.lock',
        '.DS_Store',
        'Thumbs.db',
        '*.swp',
        '*.swo',
        '*~',
        'deploy-log.txt',
        'deploy-config.json', // Don't upload config with sensitive data
        'storage/backups',
        'storage/logs',
        'storage/cache',
    ];
    
    $excludePatterns = array_merge($excludePatterns, $standardExcludes);
    
    // Also read from deployment-exclude.txt if it exists
    $excludeFile = __DIR__ . '/deployment-exclude.txt';
    if (file_exists($excludeFile)) {
        $excludeLines = file($excludeFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($excludeLines as $line) {
            $line = trim($line);
            if (!empty($line) && !in_array($line, $excludePatterns)) {
                $excludePatterns[] = $line;
            }
        }
    }
    
    $baseDir = __DIR__ . DIRECTORY_SEPARATOR;
    $baseDirLength = strlen($baseDir);
    
    // Recursively scan ALL files in the project directory
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }
        
        $filePath = $file->getPathname();
        $relativePath = str_replace('\\', '/', substr($filePath, $baseDirLength));
        
        // Skip if file doesn't exist (shouldn't happen, but safety check)
        if (!file_exists($filePath)) {
            continue;
        }
        
        // Check exclude patterns
        $excluded = false;
        $fileNormalized = str_replace('\\', '/', $relativePath);
        
        foreach ($excludePatterns as $pattern) {
            $patternNormalized = str_replace('\\', '/', $pattern);
            
            // Check if pattern matches the file path
            if (fnmatch($patternNormalized, $fileNormalized) || 
                fnmatch($pattern, $relativePath) || 
                fnmatch(str_replace('/', '\\', $pattern), $relativePath) ||
                strpos($fileNormalized, $patternNormalized) !== false) {
                $excluded = true;
                break;
            }
            
            // Also check if it's a directory pattern and file is inside that directory
            if (strpos($patternNormalized, '/') !== false || strpos($patternNormalized, '\\') !== false) {
                $patternDir = dirname($patternNormalized);
                if ($patternDir !== '.' && strpos($fileNormalized, $patternDir) === 0) {
                    $excluded = true;
                    break;
                }
            }
        }
        
        // Skip excluded files
        if ($excluded) {
            continue;
        }
        
        // Check upload rules based on file category
        $category = getFileCategory($relativePath);
        $shouldUpload = false;
        
        if ($category === 'image' && ($uploadConfig['images'] ?? true)) {
            $shouldUpload = true;
        } elseif ($category === 'config' && ($uploadConfig['configs'] ?? false)) {
            $shouldUpload = true;
        } elseif ($category === 'other' && ($uploadConfig['others'] ?? true)) { // Changed default to true
            $shouldUpload = true;
        } elseif ($category === 'code') {
            // Always upload code files (PHP, JS, CSS, etc.)
            $shouldUpload = true;
        }
        
        if ($shouldUpload) {
            $files[] = [
                'local' => $filePath,
                'remote' => $relativePath,
                'permissions' => getFilePermissions($filePath)
            ];
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
    
    $gitignore = file('.gitignore', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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
                
                // Skip image files (they should be deployed)
                $extension = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
                $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
                if (in_array($extension, $imageExtensions)) {
                    continue;
                }
                
                // Skip .gitkeep files
                if (basename($relativePath) === '.gitkeep') {
                    continue;
                }
                
                // Only add non-image files that aren't already in the list
                if (!in_array($relativePath, $files)) {
                    $files[] = $relativePath;
                }
            }
        }
    }
    
    return $files;
}

function getFileCategory($file) {
    // Check file extension for images
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico', 'bmp'];
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    
    if (in_array($extension, $imageExtensions)) {
        return 'image';
    }
    
    // Check for config files
    if (strpos($file, 'config/') !== false) {
        return 'config';
    }
    
    // Check for code files
    $codeExtensions = ['php', 'js', 'css', 'html', 'htm', 'json', 'xml', 'sql', 'md', 'txt'];
    if (in_array($extension, $codeExtensions)) {
        return 'code';
    }
    
    // Everything else
    return 'other';
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

