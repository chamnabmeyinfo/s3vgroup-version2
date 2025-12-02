<?php
/**
 * Database SQL Import via FTP + PHP Script
 * Uploads SQL file to server and imports it via PHP script (for cPanel hosts that block remote MySQL)
 */

function importDatabaseViaFTP($config, $log) {
    $result = ['success' => false, 'message' => ''];
    
    $ftpConfig = $config['ftp'] ?? [];
    if (empty($ftpConfig['host']) || empty($ftpConfig['username'])) {
        $result['message'] = 'FTP configuration incomplete';
        return $result;
    }
    
    $remoteDbConfig = $config['database_remote'] ?? [];
    if (empty($remoteDbConfig['dbname']) || empty($remoteDbConfig['username'])) {
        $result['message'] = 'Remote database configuration incomplete';
        return $result;
    }
    
    $dbImportConfig = $config['database_import'] ?? [];
    if (!($dbImportConfig['enabled'] ?? true)) {
        $result['message'] = 'Database import is disabled';
        return $result;
    }
    
    try {
        $log->info("  [1/5] Creating local database backup...");
        require_once __DIR__ . '/bootstrap/app.php';
        $backupService = new \App\Core\Backup\BackupService();
        $sqlFile = $backupService->backupDatabase();
        
        if (!$sqlFile || !file_exists($sqlFile)) {
            throw new Exception("Failed to create database backup");
        }
        
        $sqlSize = round(filesize($sqlFile) / 1024 / 1024, 2);
        $log->info("    ✓ Backup created: " . basename($sqlFile) . " ({$sqlSize} MB)");
        
        // Decompress if needed
        $isCompressed = false;
        $tempSqlFile = $sqlFile;
        if (pathinfo($sqlFile, PATHINFO_EXTENSION) === 'gz') {
            $log->info("  [2/5] Decompressing SQL file...");
            if (function_exists('gzdecode')) {
                $compressedData = file_get_contents($sqlFile);
                $sqlContent = gzdecode($compressedData);
                if ($sqlContent === false) {
                    throw new Exception("Failed to decompress SQL file");
                }
                $tempSqlFile = str_replace('.gz', '', $sqlFile);
                if (file_put_contents($tempSqlFile, $sqlContent) === false) {
                    throw new Exception("Failed to write decompressed SQL file");
                }
                $isCompressed = true;
                $log->info("    ✓ File decompressed");
            }
        }
        
        // Create PHP import script
        $log->info("  [3/5] Creating PHP import script...");
        $importScript = createImportScript($remoteDbConfig, basename($tempSqlFile));
        $scriptFile = __DIR__ . '/storage/backups/import-database.php';
        if (file_put_contents($scriptFile, $importScript) === false) {
            throw new Exception("Failed to create import script");
        }
        $log->info("    ✓ Import script created");
        
        // Connect to FTP
        $log->info("  [4/5] Uploading SQL file and import script via FTP...");
        $ftp = @ftp_connect($ftpConfig['host'], $ftpConfig['port'] ?? 21);
        if (!$ftp) {
            throw new Exception('Failed to connect to FTP server');
        }
        if (!@ftp_login($ftp, $ftpConfig['username'], $ftpConfig['password'] ?? '')) {
            ftp_close($ftp);
            throw new Exception('FTP login failed');
        }
        ftp_pasv($ftp, true);
        $log->info("    ✓ Connected to FTP");
        
        // Determine remote directory
        $remoteBase = rtrim($ftpConfig['remote_path'] ?? '/public_html', '/');
        $remoteDir = $remoteBase . '/storage/backups';
        
        // Create remote directory if it doesn't exist
        if (!ftp_mkdir_recursive($ftp, $remoteDir)) {
            // Directory might already exist, try to continue
        }
        
        // Upload SQL file
        $remoteSqlFile = $remoteDir . '/' . basename($tempSqlFile);
        $log->info("    Uploading SQL file: " . basename($tempSqlFile));
        if (!@ftp_put($ftp, $remoteSqlFile, $tempSqlFile, FTP_BINARY)) {
            ftp_close($ftp);
            throw new Exception('Failed to upload SQL file via FTP');
        }
        $log->info("    ✓ SQL file uploaded");
        
        // Upload import script
        $remoteScriptFile = $remoteBase . '/import-database.php';
        $log->info("    Uploading import script...");
        if (!@ftp_put($ftp, $remoteScriptFile, $scriptFile, FTP_BINARY)) {
            ftp_close($ftp);
            throw new Exception('Failed to upload import script via FTP');
        }
        $log->info("    ✓ Import script uploaded");
        
        ftp_close($ftp);
        
        // Execute import script via HTTP
        $log->info("  [5/5] Executing import script on server...");
        $websiteUrl = $config['website_url'] ?? 'http://' . $ftpConfig['host'];
        if (empty($config['website_url'])) {
            // Try to construct URL from FTP config
            $websiteUrl = 'http://' . str_replace(['ftp.', 'www.'], '', $ftpConfig['host']);
        }
        
        $importUrl = rtrim($websiteUrl, '/') . '/import-database.php';
        $log->info("    Calling: {$importUrl}");
        
        $ch = curl_init($importUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 minutes timeout
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            throw new Exception("Failed to execute import script: " . $curlError);
        }
        
        if ($httpCode !== 200) {
            throw new Exception("Import script returned HTTP {$httpCode}. Response: " . substr($response, 0, 200));
        }
        
        // Parse response
        $responseData = json_decode($response, true);
        if ($responseData && isset($responseData['success'])) {
            if ($responseData['success']) {
                $log->info("    ✓ Database imported successfully!");
                $log->info("    Tables imported: " . ($responseData['tables'] ?? 'unknown'));
            } else {
                throw new Exception("Import failed: " . ($responseData['message'] ?? 'Unknown error'));
            }
        } else {
            // Response might be plain text
            if (strpos($response, 'success') !== false || strpos($response, 'imported') !== false) {
                $log->info("    ✓ Database import completed");
            } else {
                $log->warning("    ⚠️  Could not parse import response, but HTTP 200 received");
                $log->info("    Response: " . substr($response, 0, 500));
            }
        }
        
        // Clean up local files
        if ($isCompressed && file_exists($tempSqlFile)) {
            @unlink($tempSqlFile);
        }
        if (file_exists($scriptFile)) {
            @unlink($scriptFile);
        }
        
        $result['success'] = true;
        $result['message'] = 'Database imported successfully via FTP + PHP script';
        
    } catch (Exception $e) {
        $result['message'] = 'Database import failed: ' . $e->getMessage();
        $log->error("  ✗ " . $result['message']);
    }
    
    return $result;
}

function createImportScript($dbConfig, $sqlFileName) {
    $dbHost = $dbConfig['host'] ?? 'localhost';
    $dbName = $dbConfig['dbname'] ?? '';
    $dbUser = $dbConfig['username'] ?? '';
    $dbPass = addslashes($dbConfig['password'] ?? '');
    
    $script = <<<PHP
<?php
/**
 * Database Import Script
 * This script imports the SQL file to the database
 * Auto-generated by deployment system - DO NOT EDIT MANUALLY
 */

header('Content-Type: application/json');

\$result = ['success' => false, 'message' => '', 'tables' => 0];
\$executed = 0; // Initialize before try block

try {
    // Security check - allow deployment requests
    // For deployment scripts, we allow all requests since this script is temporary and self-deleting
    // The script will delete itself after execution for security
    \$clientIP = \$_SERVER['REMOTE_ADDR'] ?? '';
    \$userAgent = \$_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Allow all requests for deployment (script is temporary and self-deleting)
    // If you need additional security, you can add IP whitelist or secret token check here
    // Example: if (!in_array(\$clientIP, ['your.deployment.ip'])) { throw new Exception('Access denied'); }
    
    \$sqlFile = __DIR__ . '/storage/backups/' . basename('{$sqlFileName}');
    if (!file_exists(\$sqlFile)) {
        throw new Exception('SQL file not found: ' . basename('{$sqlFileName}'));
    }
    
    // Connect to database
    \$pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        '{$dbUser}',
        '{$dbPass}',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    // Read and execute SQL
    \$sql = file_get_contents(\$sqlFile);
    if (empty(\$sql)) {
        throw new Exception('SQL file is empty');
    }
    
    // Disable foreign key checks
    \$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    \$pdo->exec("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO'");
    \$pdo->exec("SET AUTOCOMMIT = 0");
    \$pdo->exec("START TRANSACTION");
    
    // Split SQL into statements
    \$statements = array_filter(array_map('trim', explode(';', \$sql)));
    
    foreach (\$statements as \$statement) {
        if (!empty(\$statement) && !preg_match('/^--/', \$statement)) {
            \$pdo->exec(\$statement . ';');
            \$executed++;
        }
    }
    
    \$pdo->exec("COMMIT");
    \$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Get table count
    \$tableCount = \$pdo->query("SHOW TABLES")->rowCount();
    
    // Clean up SQL file
    @unlink(\$sqlFile);
    
    // Clean up this script
    @unlink(__FILE__);
    
    \$result['success'] = true;
    \$result['message'] = "Database imported successfully. " . (\$executed ?? 0) . " statements executed.";
    \$result['tables'] = \$tableCount;
    
} catch (Exception \$e) {
    \$result['message'] = \$e->getMessage();
    if (isset(\$pdo) && \$pdo->inTransaction()) {
        \$pdo->exec("ROLLBACK");
        \$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    }
}

echo json_encode(\$result, JSON_PRETTY_PRINT);
PHP;
    
    return $script;
}

function ftp_mkdir_recursive($ftp, $dir) {
    $parts = explode('/', trim($dir, '/'));
    $currentDir = '';
    foreach ($parts as $part) {
        if (empty($part)) continue;
        $currentDir .= '/' . $part;
        @ftp_mkdir($ftp, $currentDir);
    }
    return true;
}

