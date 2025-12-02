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
    /**
     * Get logo color palette
     * 
     * @return array Color palette array
     */
    function get_logo_colors()
    {
        static $colors = null;
        
        if ($colors === null) {
            try {
                $paletteJson = db()->fetchOne("SELECT value FROM settings WHERE `key` = 'logo_color_palette'");
                
                if ($paletteJson && !empty($paletteJson['value'])) {
                    $colors = json_decode($paletteJson['value'], true);
                } else {
                    // Fallback to individual color settings
                    $primary = db()->fetchOne("SELECT value FROM settings WHERE `key` = 'logo_primary_color'");
                    if ($primary) {
                        $colors = [
                            'primary' => $primary['value'] ?? '#2563eb',
                            'secondary' => db()->fetchOne("SELECT value FROM settings WHERE `key` = 'logo_secondary_color'")['value'] ?? '#1e40af',
                            'accent' => db()->fetchOne("SELECT value FROM settings WHERE `key` = 'logo_accent_color'")['value'] ?? '#3b82f6',
                            'tertiary' => db()->fetchOne("SELECT value FROM settings WHERE `key` = 'logo_tertiary_color'")['value'] ?? '#60a5fa',
                            'quaternary' => db()->fetchOne("SELECT value FROM settings WHERE `key` = 'logo_quaternary_color'")['value'] ?? '#93c5fd',
                        ];
                    } else {
                        // Default colors
                        $colors = [
                            'primary' => '#2563eb',
                            'secondary' => '#1e40af',
                            'accent' => '#3b82f6',
                            'tertiary' => '#60a5fa',
                            'quaternary' => '#93c5fd',
                        ];
                    }
                }
            } catch (Exception $e) {
                // Default colors on error
                $colors = [
                    'primary' => '#2563eb',
                    'secondary' => '#1e40af',
                    'accent' => '#3b82f6',
                    'tertiary' => '#60a5fa',
                    'quaternary' => '#93c5fd',
                ];
            }
        }
        
        return $colors;
    }
    
    /**
     * Get a specific logo color
     * 
     * @param string $name Color name (primary, secondary, accent, etc.)
     * @return string Hex color code
     */
    function get_logo_color($name = 'primary')
    {
        $colors = get_logo_colors();
        return $colors[$name] ?? $colors['primary'] ?? '#2563eb';
    }
    
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
