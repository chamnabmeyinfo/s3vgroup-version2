<?php
require_once __DIR__ . '/bootstrap/app.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $company = trim($_POST['company'] ?? '');
    
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        try {
            // Check if email exists
            $existing = db()->fetchOne("SELECT id FROM customers WHERE email = :email", ['email' => $email]);
            
            if ($existing) {
                $error = 'Email already registered. Please login instead.';
            } else {
                // Create account
                db()->insert('customers', [
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_BCRYPT),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $phone,
                    'company' => $company,
                    'is_active' => 1
                ]);
                
                $success = 'Account created successfully! Please login.';
                header('refresh:2;url=' . url('login.php'));
            }
        } catch (Exception $e) {
            $error = 'Error creating account. Please try again.';
        }
    }
}

$pageTitle = 'Create Account - Forklift & Equipment Pro';
include __DIR__ . '/includes/header.php';
?>

<main class="py-12">
    <div class="container mx-auto px-4 max-w-md">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold mb-6 text-center">Create Account</h1>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= escape($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?= escape($success) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-4">
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">First Name</label>
                        <input type="text" name="first_name" value="<?= escape($_POST['first_name'] ?? '') ?>"
                               class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Last Name</label>
                        <input type="text" name="last_name" value="<?= escape($_POST['last_name'] ?? '') ?>"
                               class="w-full px-4 py-2 border rounded-lg">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Email *</label>
                    <input type="email" name="email" required value="<?= escape($_POST['email'] ?? '') ?>"
                           class="w-full px-4 py-2 border rounded-lg">
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Phone</label>
                    <input type="tel" name="phone" value="<?= escape($_POST['phone'] ?? '') ?>"
                           class="w-full px-4 py-2 border rounded-lg">
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Company</label>
                    <input type="text" name="company" value="<?= escape($_POST['company'] ?? '') ?>"
                           class="w-full px-4 py-2 border rounded-lg">
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Password *</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-2 border rounded-lg">
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Confirm Password *</label>
                    <input type="password" name="confirm_password" required
                           class="w-full px-4 py-2 border rounded-lg">
                </div>
                
                <button type="submit" class="btn-primary w-full">Create Account</button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-gray-600">
                    Already have an account? 
                    <a href="<?= url('login.php') ?>" class="text-blue-600 hover:underline">Login here</a>
                </p>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

