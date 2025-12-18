<?php
/**
 * Safe Git Pull Script for cPanel
 * 
 * This script safely pulls code from GitHub by:
 * 1. Discarding local changes
 * 2. Removing conflicting untracked files
 * 3. Pulling latest code
 * 
 * Access via: https://s3vtgroup.com.kh/cpanel-safe-pull.php
 * 
 * SECURITY: Delete this file after use!
 */

// Security: Only allow access from specific IP or with password
$ALLOWED_IP = ''; // Set your IP here, or leave empty to use password
$PASSWORD = 'your-secure-password-here'; // CHANGE THIS!

// Helper function to get real IP (works with Cloudflare)
if (!function_exists('get_real_ip')) {
    function get_real_ip() {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        $headers = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP'];
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}

// Check access (use real IP to work with Cloudflare)
$clientIp = get_real_ip();
if (!empty($ALLOWED_IP) && $clientIp !== $ALLOWED_IP) {
    die('Access denied: IP not allowed (Your IP: ' . htmlspecialchars($clientIp) . ')');
}

if (empty($ALLOWED_IP)) {
    // Password protection
    if (!isset($_POST['password'])) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Safe Git Pull - Authentication</title>
            <style>
                body { font-family: Arial, sans-serif; max-width: 500px; margin: 50px auto; padding: 20px; }
                input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; }
                button { background: #007cba; color: white; padding: 10px 20px; border: none; cursor: pointer; }
                button:hover { background: #005a87; }
            </style>
        </head>
        <body>
            <h2>Safe Git Pull - Authentication Required</h2>
            <form method="POST">
                <label>Password:</label>
                <input type="password" name="password" required>
                <button type="submit">Authenticate</button>
            </form>
        </body>
        </html>
        <?php
        exit;
    }
    
    if ($_POST['password'] !== $PASSWORD) {
        die('Access denied: Invalid password');
    }
}

// Get the directory (assume we're in public_html or the Git root)
$gitDir = __DIR__;
$output = [];
$errors = [];

// Change to Git directory
chdir($gitDir);

// Step 1: Show current status
echo "<h2>Step 1: Current Git Status</h2>";
echo "<pre>";
$status = shell_exec('cd ' . escapeshellarg($gitDir) . ' && git status 2>&1');
echo htmlspecialchars($status);
echo "</pre>";

// Step 2: Discard local changes
echo "<h2>Step 2: Discarding Local Changes</h2>";
echo "<pre>";
$reset = shell_exec('cd ' . escapeshellarg($gitDir) . ' && git reset --hard HEAD 2>&1');
echo htmlspecialchars($reset);
echo "</pre>";

// Step 3: Remove untracked files
echo "<h2>Step 3: Removing Untracked Files</h2>";
echo "<pre>";
$clean = shell_exec('cd ' . escapeshellarg($gitDir) . ' && git clean -fd 2>&1');
echo htmlspecialchars($clean);
echo "</pre>";

// Step 4: Pull latest code
echo "<h2>Step 4: Pulling Latest Code</h2>";
echo "<pre>";
$pull = shell_exec('cd ' . escapeshellarg($gitDir) . ' && git pull origin main 2>&1');
echo htmlspecialchars($pull);
echo "</pre>";

// Check if pull was successful
if (strpos($pull, 'Already up to date') !== false || strpos($pull, 'Updating') !== false) {
    echo "<h2 style='color: green;'>✅ Success! Code pulled successfully.</h2>";
} else if (strpos($pull, 'error') !== false || strpos($pull, 'fatal') !== false) {
    echo "<h2 style='color: red;'>❌ Error occurred during pull. Check output above.</h2>";
} else {
    echo "<h2 style='color: orange;'>⚠️ Pull completed. Check output above for details.</h2>";
}

// Show final status
echo "<h2>Final Git Status</h2>";
echo "<pre>";
$finalStatus = shell_exec('cd ' . escapeshellarg($gitDir) . ' && git status 2>&1');
echo htmlspecialchars($finalStatus);
echo "</pre>";

echo "<hr>";
echo "<p><strong>⚠️ SECURITY WARNING:</strong> Delete this file after use!</p>";
echo "<p>File location: " . __FILE__ . "</p>";
?>
