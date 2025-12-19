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

if (!function_exists('get_real_ip')) {
    /**
     * Get the real client IP address
     * Works correctly when behind Cloudflare proxy
     * 
     * @return string IP address
     */
    function get_real_ip()
    {
        // Cloudflare sends real IP in CF-Connecting-IP header
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        
        // Check for other common proxy headers
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED'
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                // Validate IP address
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        // Fallback to REMOTE_ADDR
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}

// CSRF Protection Functions
if (!function_exists('csrf_token')) {
    /**
     * Generate or retrieve CSRF token
     * 
     * @return string CSRF token
     */
    function csrf_token()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate CSRF token hidden input field
     * 
     * @return string HTML input field
     */
    function csrf_field()
    {
        return '<input type="hidden" name="csrf_token" value="' . escape(csrf_token()) . '">';
    }
}

if (!function_exists('csrf_verify')) {
    /**
     * Verify CSRF token
     * 
     * @param string|null $token Token to verify (defaults to POST csrf_token)
     * @return bool True if valid, false otherwise
     */
    function csrf_verify($token = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        $token = $token ?? ($_POST['csrf_token'] ?? $_GET['csrf_token'] ?? null);
        
        if (empty($token)) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('require_csrf')) {
    /**
     * Require valid CSRF token or die with error
     * 
     * @return void
     */
    function require_csrf()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_verify()) {
            http_response_code(403);
            die('Invalid security token. Please refresh the page and try again.');
        }
    }
}

// Password Validation Function
if (!function_exists('validate_password')) {
    /**
     * Validate password strength
     * 
     * @param string $password Password to validate
     * @return array ['valid' => bool, 'errors' => array]
     */
    function validate_password($password)
    {
        $errors = [];
        
        if (strlen($password) < 12) {
            $errors[] = 'Password must be at least 12 characters long.';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter.';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number.';
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character.';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
