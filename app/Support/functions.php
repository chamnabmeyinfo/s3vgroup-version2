<?php

if (!function_exists('config')) {
    function config($key, $default = null)
    {
        static $configs = [];
        $parts = explode('.', $key);
        $file = array_shift($parts);
        
        if (!isset($configs[$file])) {
            $path = __DIR__ . "/../../config/{$file}.php";
            $configs[$file] = file_exists($path) ? require $path : [];
        }
        
        $value = $configs[$file];
        foreach ($parts as $part) {
            $value = $value[$part] ?? null;
        }
        
        return $value ?? $default;
    }
}

if (!function_exists('db')) {
    function db()
    {
        return \App\Database\Connection::getInstance();
    }
}

if (!function_exists('asset')) {
    function asset($path)
    {
        $baseUrl = config('app.url', 'http://localhost:8080');
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    function url($path = '')
    {
        $baseUrl = config('app.url', 'http://localhost:8080');
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('image_url')) {
    /**
     * Convert image path to full URL
     * Handles relative paths, absolute paths, and full URLs
     */
    function image_url($path)
    {
        if (empty($path)) {
            return '';
        }
        
        // If it's already a full URL (http:// or https://), return as is
        if (preg_match('/^https?:\/\//', $path)) {
            return $path;
        }
        
        // If it starts with /, it's an absolute path from root
        if (strpos($path, '/') === 0) {
            return url(ltrim($path, '/'));
        }
        
        // Otherwise, treat as relative path from root
        return url($path);
    }
}

if (!function_exists('escape')) {
    function escape($string)
    {
        if ($string === null) {
            return '';
        }
        if (!is_scalar($string)) {
            return '';
        }
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('old')) {
    function old($key, $default = '')
    {
        return $_SESSION['old_input'][$key] ?? $default;
    }
}

if (!function_exists('session')) {
    function session($key = null, $value = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if ($key === null) {
            return $_SESSION;
        }
        
        if ($value === null) {
            return $_SESSION[$key] ?? null;
        }
        
        $_SESSION[$key] = $value;
        return $value;
    }
}
