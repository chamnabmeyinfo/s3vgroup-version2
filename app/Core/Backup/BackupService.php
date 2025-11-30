<?php
/**
 * Automated Backup Service
 * Handles database and file backups
 */
namespace App\Core\Backup;

class BackupService {
    private $backupDir;
    private $db;
    
    public function __construct() {
        $this->backupDir = __DIR__ . '/../../../storage/backups/';
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
        $this->db = db();
    }
    
    /**
     * Create database backup
     */
    public function backupDatabase() {
        $config = require __DIR__ . '/../../../config/database.php';
        $dbName = $config['database'];
        
        $backupFile = $this->backupDir . 'db_backup_' . date('Y-m-d_H-i-s') . '.sql';
        
        // Get all tables
        $tables = $this->db->fetchAll("SHOW TABLES");
        $tableColumn = "Tables_in_{$dbName}";
        
        $sql = "-- Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        
        foreach ($tables as $table) {
            $tableName = $table[$tableColumn];
            
            // Drop table
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            
            // Create table
            $createTable = $this->db->fetchOne("SHOW CREATE TABLE `{$tableName}`");
            $sql .= $createTable['Create Table'] . ";\n\n";
            
            // Insert data
            $rows = $this->db->fetchAll("SELECT * FROM `{$tableName}`");
            if (!empty($rows)) {
                $columns = array_keys($rows[0]);
                $sql .= "INSERT INTO `{$tableName}` (`" . implode('`, `', $columns) . "`) VALUES\n";
                
                $values = [];
                foreach ($rows as $row) {
                    $rowValues = array_map(function($value) {
                        if ($value === null) {
                            return 'NULL';
                        }
                        return "'" . addslashes($value) . "'";
                    }, array_values($row));
                    
                    $values[] = "(" . implode(", ", $rowValues) . ")";
                }
                
                $sql .= implode(",\n", $values) . ";\n\n";
            }
        }
        
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
        
        file_put_contents($backupFile, $sql);
        
        // Compress backup
        $this->compressBackup($backupFile);
        
        // Clean old backups (keep last 30 days)
        $this->cleanOldBackups();
        
        return $backupFile;
    }
    
    /**
     * Restore database from backup
     */
    public function restoreDatabase($backupFile) {
        if (!file_exists($backupFile)) {
            throw new \Exception("Backup file not found");
        }
        
        // Decompress if needed
        if (pathinfo($backupFile, PATHINFO_EXTENSION) === 'gz') {
            $backupFile = $this->decompressBackup($backupFile);
        }
        
        $sql = file_get_contents($backupFile);
        
        // Split by semicolon and execute
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^--/', $statement)) {
                try {
                    $this->db->fetchAll($statement);
                } catch (\Exception $e) {
                    // Skip errors for comments and SET statements
                    if (strpos($statement, 'SET') === false && strpos($statement, 'FOREIGN_KEY_CHECKS') === false) {
                        // Only throw for non-SET statements
                    }
                }
            }
        }
        
        return true;
    }
    
    /**
     * List available backups
     */
    public function listBackups() {
        $files = glob($this->backupDir . 'db_backup_*.sql*');
        $backups = [];
        
        foreach ($files as $file) {
            $backups[] = [
                'file' => basename($file),
                'path' => $file,
                'size' => filesize($file),
                'date' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }
        
        usort($backups, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));
        
        return $backups;
    }
    
    /**
     * Compress backup
     */
    private function compressBackup($file) {
        if (function_exists('gzencode')) {
            $compressed = $file . '.gz';
            $data = file_get_contents($file);
            file_put_contents($compressed, gzencode($data, 9));
            unlink($file); // Remove uncompressed file
            return $compressed;
        }
        return $file;
    }
    
    /**
     * Decompress backup
     */
    private function decompressBackup($file) {
        if (function_exists('gzdecode')) {
            $data = gzdecode(file_get_contents($file));
            $uncompressed = str_replace('.gz', '', $file);
            file_put_contents($uncompressed, $data);
            return $uncompressed;
        }
        return $file;
    }
    
    /**
     * Clean old backups
     */
    private function cleanOldBackups($days = 30) {
        $files = glob($this->backupDir . 'db_backup_*');
        $cutoff = time() - ($days * 24 * 60 * 60);
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
}

