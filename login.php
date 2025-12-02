<?php
require_once __DIR__ . '/bootstrap/app.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['customer_id'])) {
    header('Location: ' . url('account.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password.';
    } else {
        try {
            $customer = db()->fetchOne(
                "SELECT * FROM customers WHERE email = :email AND is_active = 1",
                ['email' => $email]
            );
            
            if ($customer && password_verify($password, $customer['password'])) {
                $_SESSION['customer_id'] = $customer['id'];
                $_SESSION['customer_email'] = $customer['email'];
                $_SESSION['customer_name'] = trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''));
                
                // Update last login
                db()->update('customers', ['last_login' => date('Y-m-d H:i:s')], ['id' => $customer['id']]);
                
                $redirect = $_GET['redirect'] ?? 'account.php';
                header('Location: ' . url($redirect));
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (Exception $e) {
            $error = 'Error logging in. Please try again.';
        }
    }
}

$pageTitle = 'Customer Login - Forklift & Equipment Pro';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/message.php';
?>

<main class="py-12">
    <div class="container mx-auto px-4 max-w-md">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold mb-6 text-center">Customer Login</h1>
            
            <?= displayMessage('', $error) ?>
            
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Email</label>
                    <input type="email" name="email" required value="<?= escape($_POST['email'] ?? '') ?>"
                           class="w-full px-4 py-2 border rounded-lg">
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Password</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-2 border rounded-lg">
                </div>
                
                <button type="submit" class="btn-primary w-full">Login</button>
            </form>
            
            <div class="mt-6 text-center space-y-2">
                <p class="text-gray-600">
                    Don't have an account? 
                    <a href="<?= url('register.php') ?>" class="text-blue-600 hover:underline">Register here</a>
                </p>
                <p class="text-gray-600">
                    <a href="<?= url('admin/login.php') ?>" class="text-blue-600 hover:underline">Admin Login</a>
                </p>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

