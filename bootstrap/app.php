<?php

// Configure secure session settings BEFORE starting session
// Security: Set secure session cookie parameters
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_lifetime', 0); // Session cookie (expires on browser close)

// Only set secure flag if using HTTPS
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

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
    error_reporting(E_ALL); // Still log all errors
    ini_set('display_errors', 0); // But don't display them
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../storage/logs/php_errors.log');
}

// Security: Prevent information disclosure in error messages
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    
    // Log the error
    error_log(sprintf("[%s] %s in %s on line %d", 
        date('Y-m-d H:i:s'), 
        $message, 
        $file, 
        $line
    ));
    
    // In production, show generic error
    if (!config('app.debug', false)) {
        if ($severity === E_ERROR || $severity === E_USER_ERROR) {
            http_response_code(500);
            die('An error occurred. Please contact support if this persists.');
        }
    }
    
    return false; // Let PHP handle it normally in debug mode
});

