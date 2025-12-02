<?php
/**
 * Database SQL Import to cPanel MySQL
 * Automatically imports SQL file directly to database (overwrites existing)
 */

function importDatabaseToCPanel($config, $log) {
    $result = ['success' => false, 'message' => '', 'tables_imported' => 0];
    
    $ftpConfig = $config['ftp'] ?? [];
    if (empty($ftpConfig['host']) || empty($ftpConfig['username'])) {
        $result['message'] = 'FTP configuration incomplete';
        return $result;
    }
    
    // Database import settings
    $dbImportConfig = $config['database_import'] ?? [
        'enabled' => true,
        'overwrite' => true,
        'create_backup_before_import' => true,
        'compress' => false
    ];
    
    if (!($dbImportConfig['enabled'] ?? true)) {
        $result['message'] = 'Database import is disabled';
        return $result;
    }
    
    try {
        // Step 1: Load database config
        $log->info("  [1/4] Loading database configuration...");
        $dbConfigFile = __DIR__ . '/config/database.php';
        if (!file_exists($dbConfigFile)) {
            throw new Exception("Database config file not found");
        }
        
        $dbConfig = require $dbConfigFile;
        $dbHost = $dbConfig['host'] ?? 'localhost';
        $dbName = $dbConfig['dbname'] ?? $dbConfig['database'] ?? '';
        $dbUser = $dbConfig['username'] ?? '';
        $dbPass = $dbConfig['password'] ?? '';
        
        if (empty($dbName) || empty($dbUser)) {
            throw new Exception("Database credentials incomplete in config/database.php");
        }
        
        $log->info("    ✓ Database: {$dbName} on {$dbHost}");
        
        // Step 2: Create backup before import (if enabled)
        $backupFile = null;
        if ($dbImportConfig['create_backup_before_import'] ?? true) {
            $log->info("  [2/4] Creating backup before import...");
            $backupService = new \App\Core\Backup\BackupService();
            $backupFile = $backupService->backupDatabase();
            if ($backupFile && file_exists($backupFile)) {
                $backupSize = round(filesize($backupFile) / 1024 / 1024, 2);
                $log->info("    ✓ Backup created: " . basename($backupFile) . " ({$backupSize} MB)");
            }
        } else {
            $log->info("  [2/4] Skipping backup (disabled in config)");
        }
        
        // Step 3: Create fresh database backup for import
        $log->info("  [3/4] Creating fresh database export for import...");
        $backupService = new \App\Core\Backup\BackupService();
        $sqlFile = $backupService->backupDatabase();
        
        if (!$sqlFile || !file_exists($sqlFile)) {
            throw new Exception("Failed to create database export");
        }
        
        $sqlSize = round(filesize($sqlFile) / 1024 / 1024, 2);
        $log->info("    ✓ SQL file created: " . basename($sqlFile) . " ({$sqlSize} MB)");
        
        // Step 4: Import to database
        $log->info("  [4/4] Importing to database...");
        
        // Connect to MySQL
        try {
            $pdo = new PDO(
                "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
                $dbUser,
                $dbPass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            throw new Exception("Failed to connect to database: " . $e->getMessage());
        }
        
        $log->info("    ✓ Connected to MySQL");
        
        // Read SQL file
        $sql = file_get_contents($sqlFile);
        if (empty($sql)) {
            throw new Exception("SQL file is empty");
        }
        
        // Disable foreign key checks temporarily
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $pdo->exec("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO'");
        $pdo->exec("SET AUTOCOMMIT = 0");
        $pdo->exec("START TRANSACTION");
        
        // Split SQL into individual statements
        // Remove comments and empty lines
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // Split by semicolon, but be careful with semicolons inside strings
        $statements = [];
        $currentStatement = '';
        $inString = false;
        $stringChar = '';
        
        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];
            
            if (!$inString && ($char === '"' || $char === "'" || $char === '`')) {
                $inString = true;
                $stringChar = $char;
                $currentStatement .= $char;
            } elseif ($inString && $char === $stringChar && $sql[$i - 1] !== '\\') {
                $inString = false;
                $stringChar = '';
                $currentStatement .= $char;
            } elseif (!$inString && $char === ';') {
                $statement = trim($currentStatement);
                if (!empty($statement)) {
                    $statements[] = $statement;
                }
                $currentStatement = '';
            } else {
                $currentStatement .= $char;
            }
        }
        
        // Add last statement if exists
        if (!empty(trim($currentStatement))) {
            $statements[] = trim($currentStatement);
        }
        
        $log->info("    Found " . count($statements) . " SQL statements");
        
        // Execute statements
        $executed = 0;
        $errors = [];
        
        foreach ($statements as $index => $statement) {
            if (empty(trim($statement))) {
                continue;
            }
            
            try {
                $pdo->exec($statement);
                $executed++;
                
                // Log progress for large imports
                if (($index + 1) % 50 == 0) {
                    $log->info("    Progress: " . ($index + 1) . "/" . count($statements) . " statements executed");
                }
            } catch (PDOException $e) {
                // Some errors are acceptable (like DROP TABLE IF EXISTS on non-existent tables)
                $errorMsg = $e->getMessage();
                if (strpos($errorMsg, "Unknown table") === false && 
                    strpos($errorMsg, "doesn't exist") === false) {
                    $errors[] = "Statement " . ($index + 1) . ": " . $errorMsg;
                }
            }
        }
        
        // Re-enable foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        $pdo->exec("COMMIT");
        
        if (!empty($errors)) {
            $log->warning("    ⚠️  Import completed with " . count($errors) . " error(s)");
            foreach (array_slice($errors, 0, 5) as $error) {
                $log->warning("      - " . $error);
            }
            if (count($errors) > 5) {
                $log->warning("      ... and " . (count($errors) - 5) . " more errors");
            }
        } else {
            $log->info("    ✓ All statements executed successfully");
        }
        
        // Get table count
        $tableCount = $pdo->query("SHOW TABLES")->rowCount();
        $log->info("    ✓ Database now has {$tableCount} table(s)");
        
        // Clean up SQL file if not keeping
        if (!($dbImportConfig['keep_sql_file'] ?? false)) {
            if (file_exists($sqlFile)) {
                unlink($sqlFile);
                $log->info("    ✓ Removed temporary SQL file");
            }
        }
        
        $result['success'] = true;
        $result['message'] = "Database imported successfully! {$executed} statements executed, {$tableCount} tables in database";
        $result['tables_imported'] = $tableCount;
        $result['statements_executed'] = $executed;
        
    } catch (Exception $e) {
        $result['message'] = "Error: " . $e->getMessage();
        $log->error("  ✗ " . $result['message']);
        
        // Try to rollback if transaction was started
        if (isset($pdo)) {
            try {
                $pdo->exec("ROLLBACK");
                $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            } catch (Exception $rollbackError) {
                // Ignore rollback errors
            }
        }
    }
    
    return $result;
}

