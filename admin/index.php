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
    
    // Get recent orders
    $recentOrders = db()->fetchAll(
        "SELECT o.*, 
         (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
         FROM orders o 
         ORDER BY o.created_at DESC LIMIT 5"
    );
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

<div class="w-full">
    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl shadow-xl p-4 md:p-6 lg:p-8 mb-4 md:mb-6 text-white">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-1 md:mb-2">
                    <i class="fas fa-tachometer-alt mr-2 md:mr-3"></i>
                    Dashboard
                </h1>
                <p class="text-blue-100 text-sm md:text-lg">Welcome back, <?= escape(session('admin_username') ?? 'Admin') ?>!</p>
            </div>
            <div class="bg-white/20 rounded-full px-4 md:px-6 py-2 md:py-3 backdrop-blur-sm">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-calendar-alt"></i>
                    <span class="font-semibold text-sm md:text-base"><?= date('F j, Y') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-all cursor-pointer" onclick="window.location.href='<?= url('admin/products.php') ?>'">
            <div class="flex items-center justify-between mb-4">
                <div class="bg-white/20 rounded-lg p-3">
                    <i class="fas fa-box text-2xl"></i>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold"><?= $stats['total_products'] ?></div>
                    <div class="text-blue-100 text-sm">Active</div>
                </div>
            </div>
            <div class="text-blue-100 text-sm font-medium mb-1">Products</div>
            <div class="text-blue-200 text-xs"><?= $stats['featured_products'] ?> featured • <?= $stats['total_products_all'] ?> total</div>
        </div>
        
        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg p-4 md:p-6 text-white transform hover:scale-105 transition-all cursor-pointer" onclick="window.location.href='<?= url('admin/categories.php') ?>'">
            <div class="flex items-center justify-between mb-3 md:mb-4">
                <div class="bg-white/20 rounded-lg p-2 md:p-3">
                    <i class="fas fa-tags text-xl md:text-2xl"></i>
                </div>
                <div class="text-right">
                    <div class="text-2xl md:text-3xl font-bold"><?= $stats['total_categories'] ?></div>
                    <div class="text-green-100 text-xs md:text-sm">Categories</div>
                </div>
            </div>
            <div class="text-green-100 text-xs md:text-sm font-medium">Product Categories</div>
        </div>
        
        <div class="bg-gradient-to-br from-yellow-500 to-amber-600 rounded-xl shadow-lg p-4 md:p-6 text-white transform hover:scale-105 transition-all cursor-pointer" onclick="window.location.href='<?= url('admin/quotes.php') ?>'">
            <div class="flex items-center justify-between mb-3 md:mb-4">
                <div class="bg-white/20 rounded-lg p-2 md:p-3">
                    <i class="fas fa-calculator text-xl md:text-2xl"></i>
                </div>
                <div class="text-right">
                    <div class="text-2xl md:text-3xl font-bold"><?= $stats['pending_quotes'] ?></div>
                    <div class="text-yellow-100 text-xs md:text-sm">Pending</div>
                </div>
            </div>
            <div class="text-yellow-100 text-xs md:text-sm font-medium mb-1">Quote Requests</div>
            <div class="text-yellow-200 text-xs"><?= $stats['quotes_today'] ?> today • <?= $stats['total_quotes'] ?> total</div>
        </div>
        
        <?php if ($hasOrders): ?>
        <div class="bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl shadow-lg p-4 md:p-6 text-white transform hover:scale-105 transition-all cursor-pointer" onclick="window.location.href='<?= url('admin/orders.php') ?>'">
            <div class="flex items-center justify-between mb-3 md:mb-4">
                <div class="bg-white/20 rounded-lg p-2 md:p-3">
                    <i class="fas fa-shopping-cart text-xl md:text-2xl"></i>
                </div>
                <div class="text-right">
                    <div class="text-2xl md:text-3xl font-bold"><?= $stats['pending_orders'] ?></div>
                    <div class="text-purple-100 text-xs md:text-sm">Pending</div>
                </div>
            </div>
            <div class="text-purple-100 text-xs md:text-sm font-medium mb-1">Orders</div>
            <div class="text-purple-200 text-xs"><?= $stats['orders_today'] ?> today • <?= $stats['total_orders'] ?> total</div>
        </div>
        <?php else: ?>
        <div class="bg-gradient-to-br from-red-500 to-pink-600 rounded-xl shadow-lg p-4 md:p-6 text-white transform hover:scale-105 transition-all cursor-pointer" onclick="window.location.href='<?= url('admin/messages.php') ?>'">
            <div class="flex items-center justify-between mb-3 md:mb-4">
                <div class="bg-white/20 rounded-lg p-2 md:p-3">
                    <i class="fas fa-envelope text-xl md:text-2xl"></i>
                </div>
                <div class="text-right">
                    <div class="text-2xl md:text-3xl font-bold"><?= $stats['unread_messages'] ?></div>
                    <div class="text-red-100 text-xs md:text-sm">Unread</div>
                </div>
            </div>
            <div class="text-red-100 text-xs md:text-sm font-medium mb-1">Messages</div>
            <div class="text-red-200 text-xs"><?= $stats['messages_today'] ?> today • <?= $stats['total_messages'] ?> total</div>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($hasOrders && $stats['orders_revenue'] > 0): ?>
    <!-- Revenue Card -->
    <div class="bg-gradient-to-r from-green-500 via-emerald-500 to-green-600 rounded-xl shadow-xl p-4 md:p-6 lg:p-8 mb-4 md:mb-6 text-white">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <p class="text-green-100 text-xs md:text-sm mb-1 md:mb-2 font-medium">Total Revenue</p>
                <p class="text-3xl md:text-4xl lg:text-5xl font-bold mb-1 md:mb-2">$<?= number_format($stats['orders_revenue'], 2) ?></p>
                <p class="text-green-100 text-xs md:text-sm">From paid orders</p>
            </div>
            <div class="bg-white/20 rounded-full p-4 md:p-6 backdrop-blur-sm">
                <i class="fas fa-dollar-sign text-3xl md:text-4xl lg:text-5xl"></i>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Activity Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-4 md:mb-6">
        <?php if ($hasOrders && !empty($recentOrders)): ?>
        <!-- Recent Orders -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-purple-500 to-indigo-600 p-6 text-white">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Recent Orders
                    </h2>
                    <a href="<?= url('admin/orders.php') ?>" class="text-purple-100 hover:text-white text-sm font-medium transition-colors">
                        View All <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <?php foreach ($recentOrders as $order): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors border-l-4 border-purple-500">
                        <div class="flex-1">
                            <a href="<?= url('admin/order-view.php?id=' . $order['id']) ?>" 
                               class="text-blue-600 hover:text-blue-800 font-semibold text-sm">
                                <?= escape($order['order_number']) ?>
                            </a>
                            <p class="text-xs text-gray-600 mt-1">
                                <?php if ($order['first_name'] || $order['last_name']): ?>
                                    <i class="fas fa-user text-gray-400 mr-1"></i>
                                    <?= escape($order['first_name'] . ' ' . $order['last_name']) ?>
                                <?php else: ?>
                                    <i class="fas fa-user text-gray-400 mr-1"></i>
                                    Guest
                                <?php endif; ?>
                                • <i class="fas fa-box text-gray-400 mr-1"></i>
                                <?= escape($order['item_count'] ?? 0) ?> item(s)
                            </p>
                        </div>
                        <div class="text-right ml-4">
                            <span class="font-bold text-green-600 text-lg">$<?= number_format($order['total'], 2) ?></span>
                            <p class="text-xs text-gray-500 mt-1">
                                <i class="fas fa-clock mr-1"></i>
                                <?= date('M d', strtotime($order['created_at'])) ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Recent Products -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-cyan-600 p-4 md:p-6 text-white">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg md:text-xl font-bold">
                        <i class="fas fa-box mr-2"></i>
                        Recent Products
                    </h2>
                    <a href="<?= url('admin/products.php') ?>" class="text-blue-100 hover:text-white text-xs md:text-sm font-medium transition-colors">
                        View All <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            <div class="p-4 md:p-6">
                <div class="space-y-4">
                    <?php foreach ($recentProducts as $product): ?>
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors border-l-4 border-blue-500">
                        <div class="flex-1">
                            <a href="<?= url('product.php?slug=' . escape($product['slug'])) ?>" target="_blank" 
                               class="text-blue-600 hover:text-blue-800 font-semibold text-sm">
                                <?= escape($product['name']) ?>
                            </a>
                            <p class="text-xs text-gray-600 mt-1">
                                <i class="fas fa-tag text-gray-400 mr-1"></i>
                                SKU: <?= escape($product['sku'] ?? 'N/A') ?>
                            </p>
                        </div>
                        <div class="text-right ml-4">
                            <span class="text-xs text-gray-500">
                                <i class="fas fa-calendar mr-1"></i>
                                <?= date('M d, Y', strtotime($product['created_at'])) ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Quote Requests -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-yellow-500 to-amber-600 p-4 md:p-6 text-white">
            <div class="flex items-center justify-between">
                <h2 class="text-lg md:text-xl font-bold">
                    <i class="fas fa-calculator mr-2"></i>
                    Recent Quote Requests
                </h2>
                <a href="<?= url('admin/quotes.php') ?>" class="text-yellow-100 hover:text-white text-xs md:text-sm font-medium transition-colors">
                    View All <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        </div>
        <div class="p-4 md:p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                <?php foreach ($recentQuotes as $quote): ?>
                <div class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors border-l-4 <?= $quote['status'] === 'pending' ? 'border-yellow-500' : 'border-green-500' ?>">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-800"><?= escape($quote['name']) ?></p>
                            <?php if ($quote['product_name']): ?>
                                <p class="text-sm text-gray-600 mt-1">
                                    <i class="fas fa-box text-gray-400 mr-1"></i>
                                    <?= escape($quote['product_name']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $quote['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' ?>">
                            <?= ucfirst($quote['status']) ?>
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        <i class="fas fa-clock mr-1"></i>
                        <?= date('M d, Y', strtotime($quote['created_at'])) ?>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
