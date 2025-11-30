<?php
/**
 * Pre-Deployment Validation Module
 * Validates code and configs before deployment
 */

function validateBeforeDeploy($config, $log) {
    $result = ['success' => true, 'errors' => [], 'warnings' => []];
    
    $log->info("  Running pre-deployment checks...");
    
    // 1. Check PHP syntax
    $log->info("  [1/5] Checking PHP syntax...");
    $syntaxErrors = checkPHPSyntax();
    if (!empty($syntaxErrors)) {
        $result['errors'] = array_merge($result['errors'], $syntaxErrors);
        $log->error("    ✗ Found " . count($syntaxErrors) . " syntax error(s)");
        foreach ($syntaxErrors as $error) {
            $log->error("      - {$error}");
        }
    } else {
        $log->info("    ✓ PHP syntax OK");
    }
    
    // 2. Validate config files
    $log->info("  [2/5] Validating config files...");
    $configErrors = validateConfigFiles();
    if (!empty($configErrors)) {
        $result['warnings'] = array_merge($result['warnings'], $configErrors);
        $log->warning("    ⚠️  Found " . count($configErrors) . " config warning(s)");
        foreach ($configErrors as $warning) {
            $log->warning("      - {$warning}");
        }
    } else {
        $log->info("    ✓ Config files OK");
    }
    
    // 3. Check required files exist
    $log->info("  [3/5] Checking required files...");
    $missingFiles = checkRequiredFiles();
    if (!empty($missingFiles)) {
        $result['errors'] = array_merge($result['errors'], $missingFiles);
        $log->error("    ✗ Missing " . count($missingFiles) . " required file(s)");
        foreach ($missingFiles as $file) {
            $log->error("      - {$file}");
        }
    } else {
        $log->info("    ✓ Required files present");
    }
    
    // 4. Check file permissions
    $log->info("  [4/5] Checking file permissions...");
    $permissionIssues = checkFilePermissions();
    if (!empty($permissionIssues)) {
        $result['warnings'] = array_merge($result['warnings'], $permissionIssues);
        $log->warning("    ⚠️  Found " . count($permissionIssues) . " permission issue(s)");
        foreach ($permissionIssues as $issue) {
            $log->warning("      - {$issue}");
        }
    } else {
        $log->info("    ✓ File permissions OK");
    }
    
    // 5. Check database connection (if config exists)
    $log->info("  [5/5] Checking database connection...");
    $dbIssues = checkDatabaseConnection();
    if (!empty($dbIssues)) {
        $result['warnings'] = array_merge($result['warnings'], $dbIssues);
        $log->warning("    ⚠️  Database check: " . implode(', ', $dbIssues));
    } else {
        $log->info("    ✓ Database connection OK");
    }
    
    // Summary
    if (!empty($result['errors'])) {
        $result['success'] = false;
        $log->error("  ✗ Validation failed with " . count($result['errors']) . " error(s)");
    } elseif (!empty($result['warnings'])) {
        $log->warning("  ⚠️  Validation passed with " . count($result['warnings']) . " warning(s)");
    } else {
        $log->info("  ✓ All validations passed!");
    }
    
    return $result;
}

function checkPHPSyntax() {
    $errors = [];
    $phpFiles = [];
    
    // Find all PHP files
    $directories = ['admin', 'app', 'api', 'includes', 'bootstrap'];
    $directories[] = '.'; // Root directory
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) continue;
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $phpFiles[] = $file->getPathname();
            }
        }
    }
    
    // Check syntax of each file
    foreach ($phpFiles as $file) {
        $output = [];
        $returnCode = 0;
        exec("php -l \"" . escapeshellarg($file) . "\"", $output, $returnCode);
        
        if ($returnCode !== 0) {
            $errorMsg = implode(' ', $output);
            $errors[] = basename($file) . ": " . $errorMsg;
        }
    }
    
    return $errors;
}

function validateConfigFiles() {
    $warnings = [];
    
    // Check deploy-config.json
    if (file_exists('deploy-config.json')) {
        $config = json_decode(file_get_contents('deploy-config.json'), true);
        if (!$config) {
            $warnings[] = "deploy-config.json: Invalid JSON format";
        } else {
            // Check required fields
            if (empty($config['ftp']['host'])) {
                $warnings[] = "deploy-config.json: FTP host not set";
            }
            if (empty($config['ftp']['username'])) {
                $warnings[] = "deploy-config.json: FTP username not set";
            }
        }
    }
    
    // Check config/database.php (if exists)
    if (file_exists('config/database.php')) {
        $dbConfig = @include 'config/database.php';
        if ($dbConfig && isset($dbConfig['password']) && empty($dbConfig['password'])) {
            $warnings[] = "config/database.php: Database password is empty";
        }
    }
    
    return $warnings;
}

function checkRequiredFiles() {
    $errors = [];
    $required = [
        'bootstrap/app.php',
        'config/database.php.example',
        'deploy-main.php',
        'deploy-git.php',
        'deploy-ftp.php',
        'deploy-utils.php'
    ];
    
    foreach ($required as $file) {
        if (!file_exists($file)) {
            $errors[] = "Missing required file: {$file}";
        }
    }
    
    return $errors;
}

function checkFilePermissions() {
    $warnings = [];
    
    // Check if storage directories are writable
    $storageDirs = ['storage/uploads', 'storage/cache', 'storage/logs'];
    
    foreach ($storageDirs as $dir) {
        if (is_dir($dir) && !is_writable($dir)) {
            $warnings[] = "{$dir} is not writable";
        }
    }
    
    return $warnings;
}

function checkDatabaseConnection() {
    $warnings = [];
    
    // Only check if config exists
    if (!file_exists('config/database.php')) {
        return $warnings; // Config doesn't exist, skip check
    }
    
    try {
        require_once __DIR__ . '/bootstrap/app.php';
        $db = db();
        $db->fetchOne("SELECT 1");
        // Connection successful
    } catch (Exception $e) {
        $warnings[] = "Database connection failed: " . $e->getMessage();
    }
    
    return $warnings;
}
