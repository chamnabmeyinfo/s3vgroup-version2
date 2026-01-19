<?php
require_once __DIR__ . '/../bootstrap/app.php';

// Redirect if already logged in
if (session('admin_logged_in')) {
    header('Location: ' . url('admin/index.php'));
    exit;
}

$error = '';
$message = '';

// Security: Rate limiting for login attempts
$attempts = session('login_attempts') ?? 0;
$lastAttempt = session('last_attempt') ?? 0;
$lockoutTime = 900; // 15 minutes

// Allow reset via GET parameter (for emergency unlock - remove in production)
$resetLockout = $_GET['reset'] ?? false;
if ($resetLockout && ($resetLockout === 'true' || $resetLockout === '1')) {
    session('login_attempts', 0);
    session('last_attempt', 0);
    $attempts = 0;
    $lastAttempt = 0;
    $message = 'Login lockout has been reset. You can now try logging in again.';
    // Redirect to clear the reset parameter
    header('Location: ' . url('admin/login.php'));
    exit;
}

if ($attempts >= 5 && (time() - $lastAttempt) < $lockoutTime) {
    $remainingTime = ceil(($lockoutTime - (time() - $lastAttempt)) / 60);
    $error = "Too many failed login attempts. Please try again in {$remainingTime} minute(s).";
    
    // Show reset link for convenience (remove in production)
    $error .= ' <a href="' . url('admin/reset-login-lockout.php') . '" class="underline text-blue-600 hover:text-blue-800">Reset lockout</a>';
    
    // Debug: Log lockout status
    error_log("Login locked out - Attempts: {$attempts}, Last attempt: " . date('Y-m-d H:i:s', $lastAttempt) . ", Remaining: {$remainingTime} minutes");
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection is now disabled by default
    // Login processing
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
        if (empty($username) || empty($password)) {
            $error = 'Please enter username and password.';
        } else {
            // Debug: Log login attempt
            error_log("Login attempt - Username: {$username}, Password length: " . strlen($password));
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
                        $error = 'Database connection error. Please try again.';
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
                        
                        // Set session variables BEFORE regenerating session ID
                        session('admin_logged_in', true);
                        session('admin_user_id', $user['id']);
                        session('admin_username', $user['username']);
                        
                        // Security: Regenerate session ID on successful login (after setting session vars)
                        if (session_status() === PHP_SESSION_ACTIVE) {
                            session_regenerate_id(true);
                        }
                        
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
                        
                        // Debug: Log successful login attempt
                        error_log("Admin login successful - Username: {$username}, User ID: {$user['id']}, Session ID: " . session_id());
                        
                        // Verify session was set
                        if (!session('admin_logged_in')) {
                            error_log("WARNING: Session variable not set after login attempt!");
                            $error = 'Session error. Please try again.';
                        } else {
                            header('Location: ' . url('admin/index.php'));
                            exit;
                        }
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
                            "Failed admin login attempt - Username: %s, User found: Yes, Password incorrect, IP: %s, Attempts: %d",
                            $username,
                            get_real_ip(),
                            $attempts
                        );
                        error_log($logMessage);
                        
                        // Debug: Check if password hash exists
                        if (!empty($user['password'])) {
                            error_log("Password hash exists for user: " . substr($user['password'], 0, 20) . "...");
                        }
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
                        "Failed admin login attempt - Username: %s, User found: No, IP: %s, Attempts: %d",
                        $username,
                        get_real_ip(),
                        $attempts
                    );
                    error_log($logMessage);
                    
                    // Debug: Check if any users exist
                    try {
                        $userCount = db()->fetchOne("SELECT COUNT(*) as count FROM admin_users");
                        error_log("Total admin users in database: " . ($userCount['count'] ?? 0));
                    } catch (\Exception $e) {
                        error_log("Could not count admin users: " . $e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                $error = 'Database error. Please check your database setup.';
                error_log('Admin login database error: ' . $e->getMessage());
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
                <?php // CSRF field removed - protection disabled ?>
                <?php if (!empty($message)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?= escape($message) ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" id="error-message">
                        <?= escape($error) ?>
                        <?php if (isset($_GET['debug'])): ?>
                            <div class="mt-2 text-xs text-gray-600">
                                <strong>Debug Info:</strong><br>
                                Attempts: <?= session('login_attempts') ?? 0 ?><br>
                                Last Attempt: <?= session('last_attempt') ? date('Y-m-d H:i:s', session('last_attempt')) : 'Never' ?><br>
                                Session ID: <?= session_id() ?><br>
                                <a href="<?= url('admin/login.php?reset=true') ?>" class="text-blue-600 underline">Reset Lockout</a>
                            </div>
                        <?php endif; ?>
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
                
                <div class="text-center space-y-2">
                    <a href="<?= url('admin/forgot-password.php') ?>" class="text-sm text-blue-600 hover:text-blue-800">
                        <i class="fas fa-key mr-1"></i> Forgot Password?
                    </a>
                    <br>
                    <a href="<?= url('admin/login.php?reset=true') ?>" class="text-xs text-gray-500 hover:text-gray-700">
                        <i class="fas fa-unlock mr-1"></i> Reset Login Lockout
                    </a>
                    <br>
                    <a href="<?= url('admin/login.php?debug=1') ?>" class="text-xs text-gray-500 hover:text-gray-700">
                        <i class="fas fa-bug mr-1"></i> Show Debug Info
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

