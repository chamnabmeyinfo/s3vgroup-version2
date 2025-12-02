<?php

// Start session
// Note: Session name should be set BEFORE this file is included if you want a custom name
// Developer pages set session_name('developer_session') before including this file
// Skip session start if headers already sent (e.g., during deployment)
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}

// Load helper functions
require_once __DIR__ . '/../app/Support/functions.php';

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Error reporting
if (config('app.debug', false)) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

