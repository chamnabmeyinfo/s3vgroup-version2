<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

use App\Models\Product;
use App\Models\Category;

$productModel = new Product();
$categoryModel = new Category();

$stats = [
    'total_products' => db()->fetchOne("SELECT COUNT(*) as count FROM products WHERE is_active = 1")['count'],
    'total_products_all' => db()->fetchOne("SELECT COUNT(*) as count FROM products")['count'],
    'featured_products' => db()->fetchOne("SELECT COUNT(*) as count FROM products WHERE is_featured = 1 AND is_active = 1")['count'],
    'total_categories' => db()->fetchOne("SELECT COUNT(*) as count FROM categories WHERE is_active = 1")['count'],
    'pending_quotes' => db()->fetchOne("SELECT COUNT(*) as count FROM quote_requests WHERE status = 'pending'")['count'],
    'total_quotes' => db()->fetchOne("SELECT COUNT(*) as count FROM quote_requests")['count'],
    'unread_messages' => db()->fetchOne("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0")['count'],
    'total_messages' => db()->fetchOne("SELECT COUNT(*) as count FROM contact_messages")['count'],
    'quotes_today' => db()->fetchOne("SELECT COUNT(*) as count FROM quote_requests WHERE DATE(created_at) = CURDATE()")['count'],
    'messages_today' => db()->fetchOne("SELECT COUNT(*) as count FROM contact_messages WHERE DATE(created_at) = CURDATE()")['count'],
];

$recentProducts = $productModel->getAll(['limit' => 5]);
$recentQuotes = db()->fetchAll(
    "SELECT q.*, p.name as product_name FROM quote_requests q 
     LEFT JOIN products p ON q.product_id = p.id 
     ORDER BY q.created_at DESC LIMIT 5"
);

$pageTitle = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>

<h1 class="text-3xl font-bold mb-6">Dashboard</h1>

<!-- Stats Grid -->
<div class="grid md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600">Active Products</p>
                <p class="text-3xl font-bold"><?= $stats['total_products'] ?></p>
                <p class="text-sm text-gray-500"><?= $stats['featured_products'] ?> featured</p>
            </div>
            <i class="fas fa-box text-4xl text-blue-600"></i>
        </div>
        <a href="<?= url('admin/products.php') ?>" class="text-xs text-blue-600 hover:underline mt-2 block">View All →</a>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600">Categories</p>
                <p class="text-3xl font-bold"><?= $stats['total_categories'] ?></p>
            </div>
            <i class="fas fa-tags text-4xl text-green-600"></i>
        </div>
        <a href="<?= url('admin/categories.php') ?>" class="text-xs text-green-600 hover:underline mt-2 block">Manage →</a>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600">Pending Quotes</p>
                <p class="text-3xl font-bold"><?= $stats['pending_quotes'] ?></p>
                <p class="text-sm text-gray-500"><?= $stats['quotes_today'] ?> today</p>
            </div>
            <i class="fas fa-calculator text-4xl text-yellow-600"></i>
        </div>
        <a href="<?= url('admin/quotes.php') ?>" class="text-xs text-yellow-600 hover:underline mt-2 block">View All (<?= $stats['total_quotes'] ?>) →</a>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600">Unread Messages</p>
                <p class="text-3xl font-bold"><?= $stats['unread_messages'] ?></p>
                <p class="text-sm text-gray-500"><?= $stats['messages_today'] ?> today</p>
            </div>
            <i class="fas fa-envelope text-4xl text-red-600"></i>
        </div>
        <a href="<?= url('admin/messages.php') ?>" class="text-xs text-red-600 hover:underline mt-2 block">View All (<?= $stats['total_messages'] ?>) →</a>
    </div>
</div>

<div class="grid md:grid-cols-2 gap-6">
    <!-- Recent Products -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4">Recent Products</h2>
        <div class="space-y-3">
            <?php foreach ($recentProducts as $product): ?>
            <div class="flex justify-between items-center border-b pb-2">
                <a href="<?= url('product.php?slug=' . escape($product['slug'])) ?>" target="_blank" 
                   class="text-blue-600 hover:underline"><?= escape($product['name']) ?></a>
                <span class="text-gray-600 text-sm"><?= date('M d, Y', strtotime($product['created_at'])) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <a href="<?= url('admin/products.php') ?>" class="block mt-4 text-blue-600 hover:underline">View All →</a>
    </div>
    
    <!-- Recent Quote Requests -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4">Recent Quote Requests</h2>
        <div class="space-y-3">
            <?php foreach ($recentQuotes as $quote): ?>
            <div class="border-b pb-2">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-semibold"><?= escape($quote['name']) ?></p>
                        <?php if ($quote['product_name']): ?>
                            <p class="text-sm text-gray-600"><?= escape($quote['product_name']) ?></p>
                        <?php endif; ?>
                    </div>
                    <span class="text-xs px-2 py-1 rounded <?= $quote['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' ?>">
                        <?= ucfirst($quote['status']) ?>
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-1"><?= date('M d, Y', strtotime($quote['created_at'])) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <a href="<?= url('admin/quotes.php') ?>" class="block mt-4 text-blue-600 hover:underline">View All →</a>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

