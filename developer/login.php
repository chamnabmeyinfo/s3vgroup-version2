<?php
/**
 * Developer Login
 * Separate login system for developer-only access
 */

// Start separate developer session (different from admin session)
if (session_status() === PHP_SESSION_NONE) {
    session_name('developer_session');
    session_start();
}

require_once __DIR__ . '/../bootstrap/app.php';

// Redirect if already logged in
if (isset($_SESSION['developer_logged_in']) && $_SESSION['developer_logged_in'] === true) {
    header('Location: ' . url('developer/index.php'));
    exit;
}

$error = '';
$loginAttempts = $_SESSION['developer_login_attempts'] ?? 0;
$lockedUntil = $_SESSION['developer_locked_until'] ?? 0;

// Check if account is locked
if ($lockedUntil > time()) {
    $remaining = ceil(($lockedUntil - time()) / 60);
    $error = "Account locked due to too many failed login attempts. Please try again in {$remaining} minute(s).";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Load developer config
    $devConfig = require __DIR__ . '/../config/developer.php';
    
    // Verify credentials
    if ($username === $devConfig['username'] && password_verify($password, $devConfig['password'])) {
        // Successful login
        $_SESSION['developer_logged_in'] = true;
        $_SESSION['developer_username'] = $username;
        $_SESSION['developer_name'] = $devConfig['name'];
        $_SESSION['developer_login_time'] = time();
        
        // Reset login attempts
        unset($_SESSION['developer_login_attempts']);
        unset($_SESSION['developer_locked_until']);
        
        // Redirect to developer dashboard
        header('Location: ' . url('developer/index.php'));
        exit;
    } else {
        // Failed login
        $loginAttempts++;
        $_SESSION['developer_login_attempts'] = $loginAttempts;
        
        if ($loginAttempts >= $devConfig['max_login_attempts']) {
            $_SESSION['developer_locked_until'] = time() + $devConfig['lockout_duration'];
            $error = "Too many failed login attempts. Account locked for 15 minutes.";
        } else {
            $remaining = $devConfig['max_login_attempts'] - $loginAttempts;
            $error = "Invalid credentials. {$remaining} attempt(s) remaining.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Login - S3VGroup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-10 w-full max-w-md border-4 border-purple-200">
        <div class="text-center mb-8">
            <div class="inline-block bg-gradient-to-r from-purple-600 via-indigo-600 to-purple-600 text-white rounded-2xl p-6 mb-6 shadow-lg transform hover:scale-105 transition-transform">
                <i class="fas fa-code text-5xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-gray-800 mb-2">Developer Access</h1>
            <p class="text-gray-600 text-lg">Project Development Tools</p>
            <div class="mt-4 inline-block bg-purple-100 text-purple-700 px-4 py-2 rounded-full text-sm font-semibold">
                <i class="fas fa-shield-alt mr-2"></i>
                Secure Developer Only
            </div>
        </div>

        <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?= escape($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <div>
                <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-user text-purple-600 mr-2"></i>Username
                </label>
                <div class="relative">
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required 
                        autofocus
                        class="w-full px-4 py-4 pl-12 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-600 focus:border-purple-600 transition-all"
                        placeholder="Enter developer username"
                    >
                    <i class="fas fa-user absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-lock text-purple-600 mr-2"></i>Password
                </label>
                <div class="relative">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="w-full px-4 py-4 pl-12 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-600 focus:border-purple-600 transition-all"
                        placeholder="Enter developer password"
                    >
                    <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>

            <button 
                type="submit" 
                class="w-full bg-gradient-to-r from-purple-600 via-indigo-600 to-purple-600 text-white py-4 rounded-xl font-bold text-lg hover:from-purple-700 hover:via-indigo-700 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-2xl transform hover:scale-105"
            >
                <i class="fas fa-sign-in-alt mr-2"></i>
                Login to Developer Panel
            </button>
        </form>

        <div class="mt-8 pt-6 border-t-2 border-gray-200">
            <div class="text-center">
                <a href="<?= url('admin/login.php') ?>" class="inline-flex items-center text-sm text-gray-600 hover:text-purple-600 font-medium transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Admin Login
                </a>
            </div>
            <div class="mt-4 text-center">
                <p class="text-xs text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    This area is completely separate from the Admin Panel
                </p>
            </div>
        </div>
    </div>
</body>
</html>

