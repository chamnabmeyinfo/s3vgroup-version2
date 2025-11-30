<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

use App\Models\Product;
use App\Models\Category;

$productModel = new Product();
$categoryModel = new Category();

// Check if orders table exists
$hasOrders = false;
try {
    db()->fetchOne("SELECT 1 FROM orders LIMIT 1");
    $hasOrders = true;
} catch (Exception $e) {
    // Orders table doesn't exist
}

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

if ($hasOrders) {
    $stats['pending_orders'] = db()->fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")['count'];
    $stats['total_orders'] = db()->fetchOne("SELECT COUNT(*) as count FROM orders")['count'];
    $stats['orders_today'] = db()->fetchOne("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()")['count'];
    $stats['orders_revenue'] = db()->fetchOne("SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE payment_status = 'paid'")['total'] ?? 0;
}

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
    
    <?php if ($hasOrders): ?>
    <div class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600">Pending Orders</p>
                <p class="text-3xl font-bold"><?= $stats['pending_orders'] ?></p>
                <p class="text-sm text-gray-500"><?= $stats['orders_today'] ?> today</p>
            </div>
            <i class="fas fa-shopping-cart text-4xl text-purple-600"></i>
        </div>
        <a href="<?= url('admin/orders.php') ?>" class="text-xs text-purple-600 hover:underline mt-2 block">View All (<?= $stats['total_orders'] ?>) →</a>
    </div>
    <?php endif; ?>
    
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

<?php if ($hasOrders): ?>
<!-- Revenue Summary -->
<div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 mb-8 text-white">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-green-100 text-sm mb-1">Total Revenue</p>
            <p class="text-4xl font-bold">$<?= number_format($stats['orders_revenue'], 2) ?></p>
            <p class="text-green-100 text-sm mt-1">From paid orders</p>
        </div>
        <i class="fas fa-dollar-sign text-6xl opacity-50"></i>
    </div>
</div>
<?php endif; ?>

<div class="grid md:grid-cols-2 gap-6">
    <?php if ($hasOrders && !empty($recentOrders)): ?>
    <!-- Recent Orders -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Recent Orders</h2>
            <a href="<?= url('admin/orders.php') ?>" class="text-sm text-blue-600 hover:underline">View All →</a>
        </div>
        <div class="space-y-3">
            <?php foreach ($recentOrders as $order): ?>
            <div class="flex justify-between items-center border-b pb-2">
                <div>
                    <a href="<?= url('admin/order-view.php?id=' . $order['id']) ?>" 
                       class="text-blue-600 hover:underline font-semibold">
                        <?= escape($order['order_number']) ?>
                    </a>
                    <p class="text-sm text-gray-500">
                        <?php if ($order['first_name'] || $order['last_name']): ?>
                            <?= escape($order['first_name'] . ' ' . $order['last_name']) ?>
                        <?php else: ?>
                            Guest
                        <?php endif; ?>
                        • <?= escape($order['item_count'] ?? 0) ?> item(s)
                    </p>
                </div>
                <div class="text-right">
                    <span class="font-bold text-green-600">$<?= number_format($order['total'], 2) ?></span>
                    <p class="text-xs text-gray-500"><?= date('M d', strtotime($order['created_at'])) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Recent Products -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Recent Products</h2>
            <a href="<?= url('admin/products.php') ?>" class="text-sm text-blue-600 hover:underline">View All →</a>
        </div>
        <div class="space-y-3">
            <?php foreach ($recentProducts as $product): ?>
            <div class="flex justify-between items-center border-b pb-2">
                <a href="<?= url('product.php?slug=' . escape($product['slug'])) ?>" target="_blank" 
                   class="text-blue-600 hover:underline"><?= escape($product['name']) ?></a>
                <span class="text-gray-600 text-sm"><?= date('M d, Y', strtotime($product['created_at'])) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="grid md:grid-cols-2 gap-6 mt-6">
    <!-- Recent Quote Requests -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Recent Quote Requests</h2>
            <a href="<?= url('admin/quotes.php') ?>" class="text-sm text-blue-600 hover:underline">View All →</a>
        </div>
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
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

