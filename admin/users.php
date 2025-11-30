<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

use App\Models\Role;

requirePermission('view_users');

$message = '';
$error = '';

// Handle delete
if (!empty($_GET['delete']) && hasPermission('delete_users')) {
    $userId = (int)$_GET['delete'];
    $currentUserId = session('admin_user_id');
    
    if ($userId === $currentUserId) {
        $error = 'You cannot delete your own account.';
    } else {
        db()->delete('admin_users', 'id = :id', ['id' => $userId]);
        $message = 'User deleted successfully.';
    }
}

// Handle toggle active
if (!empty($_GET['toggle_active']) && hasPermission('edit_users')) {
    $userId = (int)$_GET['toggle_active'];
    $currentUserId = session('admin_user_id');
    
    if ($userId === $currentUserId) {
        $error = 'You cannot deactivate your own account.';
    } else {
        $user = db()->fetchOne("SELECT is_active FROM admin_users WHERE id = :id", ['id' => $userId]);
        if ($user) {
            db()->update('admin_users', 
                ['is_active' => $user['is_active'] ? 0 : 1],
                'id = :id',
                ['id' => $userId]
            );
            $message = 'User status updated successfully.';
        }
    }
}

// Get all users with their roles
$users = db()->fetchAll(
    "SELECT u.*, r.name as role_name, r.slug as role_slug 
     FROM admin_users u 
     LEFT JOIN roles r ON u.role_id = r.id 
     ORDER BY u.created_at DESC"
);

$pageTitle = 'Users';
include __DIR__ . '/includes/header.php';
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Admin Users</h1>
        <?php if (hasPermission('create_users')): ?>
        <a href="<?= url('admin/user-edit.php') ?>" class="btn-primary">
            <i class="fas fa-plus mr-2"></i> Add New User
        </a>
        <?php endif; ?>
    </div>
    
    <?php if ($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= escape($message) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= escape($error) ?>
        </div>
    <?php endif; ?>
    
    <div class="bg-white rounded-lg shadow-md overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Login</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($users as $user): ?>
                    <?php $isCurrentUser = $user['id'] == session('admin_user_id'); ?>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            <?= escape($user['username']) ?>
                            <?php if ($isCurrentUser): ?>
                                <span class="text-xs text-blue-600">(You)</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= escape($user['name'] ?? '-') ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= escape($user['email']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php if ($user['role_name']): ?>
                            <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800">
                                <?= escape($user['role_name']) ?>
                            </span>
                        <?php else: ?>
                            <span class="text-xs text-gray-400">No Role</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded <?= $user['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                            <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never' ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                        <?php if (hasPermission('edit_users')): ?>
                        <a href="<?= url('admin/user-edit.php?id=' . $user['id']) ?>" 
                           class="text-blue-600 hover:text-blue-900" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('edit_users') && !$isCurrentUser): ?>
                        <a href="?toggle_active=<?= $user['id'] ?>" 
                           class="text-yellow-600 hover:text-yellow-900" title="Toggle Status">
                            <i class="fas fa-toggle-<?= $user['is_active'] ? 'on' : 'off' ?>"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (hasPermission('delete_users') && !$isCurrentUser): ?>
                        <a href="?delete=<?= $user['id'] ?>" 
                           onclick="return confirm('Are you sure you want to delete this user?')" 
                           class="text-red-600 hover:text-red-900" title="Delete">
                            <i class="fas fa-trash"></i>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

