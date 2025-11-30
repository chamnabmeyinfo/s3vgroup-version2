<?php
/**
 * Deployment Utilities
 * Helper functions for deployment system
 */

class DeploymentLogger {
    private $logFile;
    private $consoleOutput;
    
    public function __construct($logFile = 'deploy-log.txt') {
        $this->logFile = $logFile;
        $this->consoleOutput = true;
    }
    
    public function info($message) {
        $this->log('INFO', $message);
    }
    
    public function error($message) {
        $this->log('ERROR', $message);
    }
    
    public function warning($message) {
        $this->log('WARNING', $message);
    }
    
    private function log($level, $message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}\n";
        
        // Write to file
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
        
        // Output to console
        if ($this->consoleOutput) {
            echo $logMessage;
        }
    }
}

// fnmatch() is built-in in PHP 7.2+, but for older versions or Windows compatibility
if (!function_exists('fnmatch')) {
    function fnmatch($pattern, $string) {
        // Simple fnmatch implementation for Windows
        $pattern = str_replace('*', '.*', preg_quote($pattern, '/'));
        return preg_match('/^' . $pattern . '$/i', $string);
    }
}

