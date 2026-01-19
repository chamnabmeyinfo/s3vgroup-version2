<?php
require_once __DIR__ . '/../bootstrap/app.php';

// Redirect if already logged in
if (session('admin_logged_in')) {
    header('Location: ' . url('admin/index.php'));
    exit;
}

$error = '';

// Security: Rate limiting for login attempts
$attempts = session('login_attempts') ?? 0;
$lastAttempt = session('last_attempt') ?? 0;
$lockoutTime = 900; // 15 minutes

// Allow reset via GET parameter (for emergency unlock - remove in production)
$resetLockout = $_GET['reset'] ?? false;
if ($resetLockout && $resetLockout === 'true') {
    session('login_attempts', 0);
    session('last_attempt', 0);
    $attempts = 0;
    $lastAttempt = 0;
}

if ($attempts >= 5 && (time() - $lastAttempt) < $lockoutTime) {
    $remainingTime = ceil(($lockoutTime - (time() - $lastAttempt)) / 60);
    $error = "Too many failed login attempts. Please try again in {$remainingTime} minute(s).";
    
    // Show reset link for convenience (remove in production)
    $error .= ' <a href="' . url('admin/reset-login-lockout.php') . '" class="underline text-blue-600 hover:text-blue-800">Reset lockout</a>';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Security: CSRF protection - but provide better error message
    if (!csrf_verify()) {
        $error = 'Security token expired. Please refresh the page and try again.';
        $error .= ' <button type="button" onclick="resetCsrfToken()" class="ml-2 px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">Reset Token</button>';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter username and password.';
        } else {
            try {
                // Simplified login: Find user by username (case-insensitive) without strict is_active check
                // This ensures backward compatibility with existing credentials
                $user = null;
                
                // Try exact match first (most common case)
                try {
                    $user = db()->fetchOne(
                        "SELECT u.*, r.id as role_id, r.slug as role_slug, r.name as role_name 
                         FROM admin_users u 
                         LEFT JOIN roles r ON u.role_id = r.id 
                         WHERE u.username = :username",
                        ['username' => $username]
                    );
                } catch (\Exception $e) {
                    // If roles table doesn't exist, try without join
                    try {
                        $user = db()->fetchOne(
                            "SELECT * FROM admin_users WHERE username = :username",
                            ['username' => $username]
                        );
                    } catch (\Exception $e2) {
                        // Database error - log it
                        error_log('Login query error: ' . $e2->getMessage());
                    }
                }
                
                // If not found with exact match, try case-insensitive
                if (!$user) {
                    try {
                        $user = db()->fetchOne(
                            "SELECT u.*, r.id as role_id, r.slug as role_slug, r.name as role_name 
                             FROM admin_users u 
                             LEFT JOIN roles r ON u.role_id = r.id 
                             WHERE LOWER(u.username) = LOWER(:username)",
                            ['username' => $username]
                        );
                    } catch (\Exception $e) {
                        // If roles table doesn't exist, try without join
                        try {
                            $user = db()->fetchOne(
                                "SELECT * FROM admin_users WHERE LOWER(username) = LOWER(:username)",
                                ['username' => $username]
                            );
                        } catch (\Exception $e2) {
                            // Database error - log it
                            error_log('Login case-insensitive query error: ' . $e2->getMessage());
                        }
                    }
                }
                
                // Verify password
                if ($user) {
                    // Check if password is set
                    if (empty($user['password'])) {
                        $error = 'User account has no password set. Please contact administrator.';
                        error_log("Login attempt for user '{$username}' failed: No password set");
                    } elseif (password_verify($password, $user['password'])) {
                        // Password is correct - proceed with login
                        // Security: Clear failed login attempts on success
                        session('login_attempts', 0);
                        session('last_attempt', 0);
                        
                        // Security: Regenerate session ID on successful login
                        session_regenerate_id(true);
                        
                        session('admin_logged_in', true);
                        session('admin_user_id', $user['id']);
                        session('admin_username', $user['username']);
                        
                        // Load role information (already loaded in query, but set session)
                        if (!empty($user['role_id'])) {
                            session('admin_role_id', $user['role_id']);
                            if (!empty($user['role_name'])) {
                                session('admin_role_name', $user['role_name']);
                            }
                            if (!empty($user['role_slug'])) {
                                session('admin_role_slug', $user['role_slug']);
                            }
                        }
                    
                        try {
                            db()->query(
                                "UPDATE admin_users SET last_login = NOW() WHERE id = :id",
                                ['id' => $user['id']]
                            );
                        } catch (\Exception $e) {
                            // Ignore if update fails (last_login column might not exist)
                        }
                        
                        header('Location: ' . url('admin/index.php'));
                        exit;
                    } else {
                        // Password is incorrect
                        // Security: Track failed login attempts
                        $attempts = (session('login_attempts') ?? 0) + 1;
                        session('login_attempts', $attempts);
                        session('last_attempt', time());
                        
                        // Security: Generic error message to prevent username enumeration
                        $error = 'Invalid username or password.';
                        
                        // Log failed login attempt with more details
                        $logMessage = sprintf(
                            "Failed admin login attempt - Username: %s, User found: Yes, Password incorrect, IP: %s",
                            $username,
                            get_real_ip()
                        );
                        error_log($logMessage);
                    }
                } else {
                    // User not found
                    // Security: Track failed login attempts
                    $attempts = (session('login_attempts') ?? 0) + 1;
                    session('login_attempts', $attempts);
                    session('last_attempt', time());
                    
                    // Security: Generic error message to prevent username enumeration
                    $error = 'Invalid username or password.';
                    
                    // Log failed login attempt
                    $logMessage = sprintf(
                        "Failed admin login attempt - Username: %s, User found: No, IP: %s",
                        $username,
                        get_real_ip()
                    );
                    error_log($logMessage);
                }
            } catch (\Exception $e) {
                $error = 'Database error. Please check your database setup.';
                error_log('Admin login database error: ' . $e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - ForkliftPro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Admin Login
                </h2>
            </div>
            <form class="mt-8 space-y-6 bg-white p-8 rounded-lg shadow-md" method="POST" id="login-form">
                <?= csrf_field() ?>
                <?php if (!empty($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" id="error-message">
                        <?php 
                        // Check if error contains HTML (reset button)
                        if (strpos($error, '<button') !== false || strpos($error, '<a') !== false) {
                            echo $error; // Don't escape if it contains HTML
                        } else {
                            echo escape($error);
                        }
                        ?>
                    </div>
                <?php endif; ?>
                
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                    <input id="username" name="username" type="text" required
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input id="password" name="password" type="password" required
                           class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Sign in
                    </button>
                </div>
                
                <div class="text-center">
                    <a href="<?= url('admin/forgot-password.php') ?>" class="text-sm text-blue-600 hover:text-blue-800">
                        <i class="fas fa-key mr-1"></i> Forgot Password?
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    // CSRF Token Reset Function (fallback if footer not loaded)
    if (typeof resetCsrfToken === 'undefined') {
        function resetCsrfToken() {
            const errorDiv = document.getElementById('error-message');
            const form = document.getElementById('login-form');
            const csrfInput = form ? form.querySelector('input[name="csrf_token"]') : null;
            
            // Show loading state
            if (errorDiv) {
                errorDiv.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Resetting token...';
            }
            
            // Fetch new token from API
            fetch('<?= url("api/csrf-reset.php") ?>')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.token) {
                        // Update CSRF token in form
                        if (csrfInput) {
                            csrfInput.value = data.token;
                        }
                        
                        // Update error message
                        if (errorDiv) {
                            errorDiv.innerHTML = '<i class="fas fa-check-circle text-green-600 mr-2"></i> Token reset successfully! You can now try logging in again.';
                            errorDiv.className = 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded';
                        }
                    } else {
                        throw new Error('Failed to reset token');
                    }
                })
                .catch(error => {
                    console.error('Error resetting CSRF token:', error);
                    if (errorDiv) {
                        errorDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i> Failed to reset token. Please refresh the page.';
                        errorDiv.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded';
                    }
                });
        }
    }
    </script>
</body>
</html>

