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

if ($attempts >= 5 && (time() - $lastAttempt) < $lockoutTime) {
    $remainingTime = ceil(($lockoutTime - (time() - $lastAttempt)) / 60);
    $error = "Too many failed login attempts. Please try again in {$remainingTime} minute(s).";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Security: CSRF protection
    require_csrf();
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password.';
    } else {
        try {
            // Try to get user with role (if roles table exists)
            try {
                $user = db()->fetchOne(
                    "SELECT u.*, r.id as role_id FROM admin_users u 
                     LEFT JOIN roles r ON u.role_id = r.id 
                     WHERE u.username = :username AND u.is_active = 1",
                    ['username' => $username]
                );
            } catch (\Exception $e) {
                // Fallback if roles table doesn't exist yet
                $user = db()->fetchOne(
                    "SELECT * FROM admin_users WHERE username = :username AND is_active = 1",
                    ['username' => $username]
                );
            }
            
            if ($user && password_verify($password, $user['password'])) {
                // Security: Clear failed login attempts on success
                session('login_attempts', 0);
                session('last_attempt', 0);
                
                // Security: Regenerate session ID on successful login
                session_regenerate_id(true);
                
                session('admin_logged_in', true);
                session('admin_user_id', $user['id']);
                session('admin_username', $user['username']);
                
                // Load role information (if roles table exists)
                if (!empty($user['role_id'])) {
                    try {
                        $role = db()->fetchOne(
                            "SELECT * FROM roles WHERE id = :id",
                            ['id' => $user['role_id']]
                        );
                        
                        if ($role) {
                            session('admin_role_id', $role['id']);
                            session('admin_role_name', $role['name']);
                            session('admin_role_slug', $role['slug']);
                        }
                    } catch (\Exception $e) {
                        // Roles table doesn't exist - skip
                    }
                }
                
                try {
                    db()->query(
                        "UPDATE admin_users SET last_login = NOW() WHERE id = :id",
                        ['id' => $user['id']]
                    );
                } catch (\Exception $e) {
                    // Ignore if update fails
                }
                
                header('Location: ' . url('admin/index.php'));
                exit;
            } else {
                // Security: Track failed login attempts
                $attempts = (session('login_attempts') ?? 0) + 1;
                session('login_attempts', $attempts);
                session('last_attempt', time());
                
                // Security: Generic error message to prevent username enumeration
                $error = 'Invalid username or password.';
                
                // Log failed login attempt
                error_log(sprintf("Failed admin login attempt for username: %s from IP: %s", 
                    $username, 
                    get_real_ip()
                ));
            }
        } catch (\Exception $e) {
            $error = 'Database error. Please check your database setup.';
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
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Admin Login
                </h2>
            </div>
            <form class="mt-8 space-y-6 bg-white p-8 rounded-lg shadow-md" method="POST">
                <?= csrf_field() ?>
                <?php if (!empty($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <?= escape($error) ?>
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
</body>
</html>

