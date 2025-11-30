<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

$message = '';
$error = '';

// Handle export
if (!empty($_GET['export'])) {
    $subscribers = db()->fetchAll("SELECT * FROM newsletter_subscribers WHERE status = 'active' ORDER BY subscribed_at DESC");
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="newsletter_subscribers_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($output, ['Email', 'Name', 'Subscribed At', 'Status']);
    
    foreach ($subscribers as $sub) {
        fputcsv($output, [
            $sub['email'],
            $sub['name'] ?? '',
            $sub['subscribed_at'],
            $sub['status']
        ]);
    }
    
    fclose($output);
    exit;
}

// Handle unsubscribe
if (!empty($_GET['unsubscribe'])) {
    db()->update('newsletter_subscribers', 
        ['status' => 'unsubscribed', 'unsubscribed_at' => date('Y-m-d H:i:s')], 
        'id = :id', 
        ['id' => (int)$_GET['unsubscribe']]
    );
    $message = 'Subscriber unsubscribed successfully.';
}

// Handle delete
if (!empty($_GET['delete'])) {
    db()->delete('newsletter_subscribers', 'id = :id', ['id' => (int)$_GET['delete']]);
    $message = 'Subscriber deleted successfully.';
}

$statusFilter = $_GET['status'] ?? 'active';
$where = $statusFilter === 'all' ? '' : "WHERE status = '$statusFilter'";

$subscribers = db()->fetchAll(
    "SELECT * FROM newsletter_subscribers $where ORDER BY subscribed_at DESC"
);

$stats = [
    'total' => db()->fetchOne("SELECT COUNT(*) as count FROM newsletter_subscribers")['count'],
    'active' => db()->fetchOne("SELECT COUNT(*) as count FROM newsletter_subscribers WHERE status = 'active'")['count'],
    'unsubscribed' => db()->fetchOne("SELECT COUNT(*) as count FROM newsletter_subscribers WHERE status = 'unsubscribed'")['count'],
];

$pageTitle = 'Newsletter Subscribers';
include __DIR__ . '/includes/header.php';
?>

<h1 class="text-3xl font-bold mb-6">Newsletter Subscribers</h1>

<!-- Stats -->
<div class="grid md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-gray-600">Total Subscribers</p>
        <p class="text-3xl font-bold"><?= $stats['total'] ?></p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-gray-600">Active</p>
        <p class="text-3xl font-bold text-green-600"><?= $stats['active'] ?></p>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <p class="text-gray-600">Unsubscribed</p>
        <p class="text-3xl font-bold text-gray-600"><?= $stats['unsubscribed'] ?></p>
    </div>
</div>

<div class="mb-6 flex justify-between items-center">
    <div class="flex space-x-4">
        <a href="?status=all" class="px-4 py-2 rounded <?= $statusFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200' ?>">
            All
        </a>
        <a href="?status=active" class="px-4 py-2 rounded <?= $statusFilter === 'active' ? 'bg-blue-600 text-white' : 'bg-gray-200' ?>">
            Active
        </a>
        <a href="?status=unsubscribed" class="px-4 py-2 rounded <?= $statusFilter === 'unsubscribed' ? 'bg-blue-600 text-white' : 'bg-gray-200' ?>">
            Unsubscribed
        </a>
    </div>
    <a href="?export=1" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
        <i class="fas fa-download mr-2"></i> Export CSV
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subscribed</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($subscribers as $sub): ?>
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"><?= escape($sub['email']) ?></td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= escape($sub['name'] ?? '') ?></td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs rounded <?= $sub['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                        <?= ucfirst($sub['status']) ?>
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <?= date('M d, Y', strtotime($sub['subscribed_at'])) ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                    <?php if ($sub['status'] === 'active'): ?>
                        <a href="?unsubscribe=<?= $sub['id'] ?>" class="text-yellow-600 hover:text-yellow-900">
                            Unsubscribe
                        </a>
                    <?php endif; ?>
                    <a href="?delete=<?= $sub['id'] ?>" 
                       onclick="return confirm('Are you sure?')" 
                       class="text-red-600 hover:text-red-900">
                        Delete
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

