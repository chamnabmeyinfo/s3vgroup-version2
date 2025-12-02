<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

use App\Models\Product;
use App\Models\Category;

$productModel = new Product();
$categoryModel = new Category();

$message = '';
$error = '';

// Handle delete
if (!empty($_GET['delete'])) {
    try {
        $productId = (int)$_GET['delete'];
        
        // Validate ID
        if ($productId <= 0) {
            $error = 'Invalid product ID.';
        } else {
            // Check if product exists
            $product = $productModel->getById($productId);
            if (!$product) {
                $error = 'Product not found.';
            } else {
                // Perform delete
                $productModel->delete($productId);
                $message = 'Product deleted successfully.';
            }
        }
    } catch (\Exception $e) {
        $error = 'Error deleting product: ' . $e->getMessage();
    }
}

// Handle toggle featured
if (!empty($_GET['toggle_featured'])) {
    $product = $productModel->getById($_GET['toggle_featured']);
    if ($product) {
        $productModel->update($_GET['toggle_featured'], [
            'is_featured' => $product['is_featured'] ? 0 : 1
        ]);
        $message = 'Product updated successfully.';
    }
}

// Get filter parameters
$search = trim($_GET['search'] ?? '');
$categoryFilter = $_GET['category'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$featuredFilter = $_GET['featured'] ?? '';
$sort = $_GET['sort'] ?? 'name_asc';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$priceMin = !empty($_GET['price_min']) ? (float)$_GET['price_min'] : null;
$priceMax = !empty($_GET['price_max']) ? (float)$_GET['price_max'] : null;

// Build filter conditions
$filterParams = ['include_inactive' => true];
if ($search) {
    $filterParams['search'] = $search;
}
if ($categoryFilter) {
    $cat = $categoryModel->getBySlug($categoryFilter);
    if ($cat) {
        $filterParams['category_id'] = $cat['id'];
    }
}
if ($statusFilter === 'active') {
    $filterParams['is_active'] = 1;
} elseif ($statusFilter === 'inactive') {
    $filterParams['is_active'] = 0;
}
if ($featuredFilter === 'yes') {
    $filterParams['is_featured'] = 1;
}

// Get all products
$allProducts = $productModel->getAll($filterParams);

// Apply additional filters
$products = $allProducts;
if ($priceMin !== null || $priceMax !== null || $dateFrom || $dateTo) {
    $products = array_filter($products, function($p) use ($priceMin, $priceMax, $dateFrom, $dateTo) {
        $price = $p['sale_price'] ?? $p['price'];
        if ($priceMin !== null && $price < $priceMin) return false;
        if ($priceMax !== null && $price > $priceMax) return false;
        
        $createdAt = strtotime($p['created_at']);
        if ($dateFrom && $createdAt < strtotime($dateFrom)) return false;
        if ($dateTo && $createdAt > strtotime($dateTo . ' 23:59:59')) return false;
        
        return true;
    });
}

// Sort products
switch ($sort) {
    case 'name_asc':
        usort($products, fn($a, $b) => strcmp($a['name'], $b['name']));
        break;
    case 'name_desc':
        usort($products, fn($a, $b) => strcmp($b['name'], $a['name']));
        break;
    case 'price_asc':
        usort($products, fn($a, $b) => ($a['sale_price'] ?? $a['price']) <=> ($b['sale_price'] ?? $b['price']));
        break;
    case 'price_desc':
        usort($products, fn($a, $b) => ($b['sale_price'] ?? $b['price']) <=> ($a['sale_price'] ?? $a['price']));
        break;
    case 'date_desc':
        usort($products, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
        break;
    case 'date_asc':
        usort($products, fn($a, $b) => strtotime($a['created_at']) - strtotime($b['created_at']));
        break;
}

// Get all categories for filter
$categories = $categoryModel->getAll();

// Get variant counts for products
$variantCounts = [];
try {
    $variantData = db()->fetchAll(
        "SELECT product_id, COUNT(*) as count FROM product_variants WHERE is_active = 1 GROUP BY product_id"
    );
    foreach ($variantData as $v) {
        $variantCounts[$v['product_id']] = $v['count'];
    }
} catch (Exception $e) {
    // Variants table might not exist
}

// Column visibility
$selectedColumns = $_GET['columns'] ?? ['checkbox', 'image', 'name', 'category', 'price', 'status', 'actions'];
$availableColumns = [
    'checkbox' => 'Checkbox',
    'image' => 'Image',
    'name' => 'Product Name',
    'sku' => 'SKU',
    'category' => 'Category',
    'price' => 'Price',
    'sale_price' => 'Sale Price',
    'stock' => 'Stock Status',
    'views' => 'Views',
    'status' => 'Status',
    'featured' => 'Featured',
    'created' => 'Created Date',
    'actions' => 'Actions'
];

// Calculate stats for mini dashboard
$totalProducts = count($allProducts);
$activeProducts = count(array_filter($allProducts, fn($p) => $p['is_active'] == 1));
$inactiveProducts = $totalProducts - $activeProducts;
$featuredProducts = count(array_filter($allProducts, fn($p) => $p['is_featured'] == 1));
$lowStockProducts = count(array_filter($allProducts, function($p) {
    return isset($p['stock_quantity']) && $p['stock_quantity'] < 10 && $p['stock_quantity'] > 0;
}));

$miniStats = [
    [
        'label' => 'Total Products',
        'value' => number_format($totalProducts),
        'icon' => 'fas fa-box',
        'color' => 'from-blue-500 to-blue-600',
        'description' => 'All products in catalog',
        'link' => url('admin/products.php')
    ],
    [
        'label' => 'Active Products',
        'value' => number_format($activeProducts),
        'icon' => 'fas fa-check-circle',
        'color' => 'from-green-500 to-emerald-600',
        'description' => 'Currently active',
        'link' => url('admin/products.php?status=active')
    ],
    [
        'label' => 'Featured Products',
        'value' => number_format($featuredProducts),
        'icon' => 'fas fa-star',
        'color' => 'from-yellow-500 to-amber-600',
        'description' => 'Featured items',
        'link' => url('admin/products.php?featured=yes')
    ],
    [
        'label' => 'Low Stock',
        'value' => number_format($lowStockProducts),
        'icon' => 'fas fa-exclamation-triangle',
        'color' => 'from-red-500 to-pink-600',
        'description' => 'Need restocking',
        'link' => url('admin/products.php')
    ]
];

$pageTitle = 'Products';
include __DIR__ . '/includes/header.php';

// Setup filter component variables
$filterId = 'products-filter';
$filters = [
    'search' => true,
    'category' => [
        'options' => array_combine(
            array_column($categories, 'slug'),
            array_column($categories, 'name')
        )
    ],
    'status' => [
        'options' => [
            'all' => 'All Statuses',
            'active' => 'Active Only',
            'inactive' => 'Inactive Only'
        ]
    ],
    'featured' => [
        'options' => [
            'all' => 'All Products',
            'yes' => 'Featured Only',
            'no' => 'Not Featured'
        ]
    ],
    'date_range' => true,
    'price_range' => true
];
$sortOptions = [
    'name_asc' => 'Name (A-Z)',
    'name_desc' => 'Name (Z-A)',
    'price_asc' => 'Price (Low to High)',
    'price_desc' => 'Price (High to Low)',
    'date_desc' => 'Newest First',
    'date_asc' => 'Oldest First'
];
$defaultColumns = ['checkbox', 'image', 'name', 'category', 'price', 'status', 'actions'];
?>

<div class="w-full">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl shadow-xl p-4 md:p-6 lg:p-8 mb-4 md:mb-6 text-white">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-1 md:mb-2">
                    <i class="fas fa-box mr-2 md:mr-3"></i>
                    Products Management
                </h1>
                <p class="text-blue-100 text-sm md:text-lg">Manage your product catalog</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 w-full sm:w-auto">
                <a href="<?= url('admin/products-export.php') ?>" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition-all text-center text-sm md:text-base">
                    <i class="fas fa-download mr-2"></i>
                    Export CSV
                </a>
                <a href="<?= url('admin/product-edit.php') ?>" class="bg-white text-blue-600 hover:bg-blue-50 px-4 md:px-6 py-2 rounded-lg font-semibold transition-all shadow-lg hover:shadow-xl text-center text-sm md:text-base">
                    <i class="fas fa-plus mr-2"></i>
                    Add New Product
                </a>
            </div>
        </div>
    </div>

    <!-- Mini Dashboard Stats -->
    <?php 
    $stats = $miniStats;
    include __DIR__ . '/includes/mini-stats.php'; 
    ?>

    <?php if (!empty($message)): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2 text-xl"></i>
            <span class="font-semibold"><?= escape($message) ?></span>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2 text-xl"></i>
            <span class="font-semibold"><?= escape($error) ?></span>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Advanced Filters -->
    <?php include __DIR__ . '/includes/advanced-filters.php'; ?>
    
    <!-- Additional Price Range Filter -->
    <?php if (isset($filters['price_range'])): ?>
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Price Min</label>
                <input type="number" name="price_min" value="<?= escape($_GET['price_min'] ?? '') ?>"
                       step="0.01" placeholder="0.00"
                       class="w-full px-4 py-2 border rounded-lg"
                       form="filter-form-<?= $filterId ?>">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Price Max</label>
                <input type="number" name="price_max" value="<?= escape($_GET['price_max'] ?? '') ?>"
                       step="0.01" placeholder="10000.00"
                       class="w-full px-4 py-2 border rounded-lg"
                       form="filter-form-<?= $filterId ?>"
                       onchange="applyFilters('<?= $filterId ?>')">
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Stats Bar -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center space-x-6">
                <div>
                    <span class="text-sm text-gray-600">Total Products:</span>
                    <span class="ml-2 font-bold text-gray-900"><?= count($allProducts) ?></span>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Showing:</span>
                    <span class="ml-2 font-bold text-blue-600"><?= count($products) ?></span>
                </div>
                <?php if (count($products) < count($allProducts)): ?>
                <div class="text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                    <i class="fas fa-filter mr-1"></i>
                    Filtered
                </div>
                <?php endif; ?>
            </div>
        <div id="bulkActions" class="hidden flex gap-2">
            <select id="bulkActionSelect" class="px-4 py-2 border rounded-lg">
                <option value="">Bulk Actions</option>
                <option value="activate">Activate</option>
                <option value="deactivate">Deactivate</option>
                <option value="feature">Mark as Featured</option>
                <option value="unfeature">Unmark as Featured</option>
                <option value="delete">Delete</option>
            </select>
            <button onclick="executeBulkAction()" class="btn-primary">Apply</button>
            <button onclick="clearSelection()" class="btn-secondary">Clear</button>
        </div>
    </div>
    
    <!-- Products Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto -mx-4 md:mx-0">
            <div class="inline-block min-w-full align-middle">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left" data-column="checkbox" style="display: <?= (in_array('checkbox', $selectedColumns) || empty($_GET['columns'])) ? '' : 'none' ?>;">
                        <input type="checkbox" id="selectAll" onchange="toggleAll(this)">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="image" style="display: <?= (in_array('image', $selectedColumns) || empty($_GET['columns'])) ? '' : 'none' ?>;">Image</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="name" style="display: <?= (in_array('name', $selectedColumns) || empty($_GET['columns'])) ? '' : 'none' ?>;">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="sku" style="display: <?= in_array('sku', $selectedColumns) ? '' : 'none' ?>;">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="category" style="display: <?= (in_array('category', $selectedColumns) || empty($_GET['columns'])) ? '' : 'none' ?>;">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="price" style="display: <?= (in_array('price', $selectedColumns) || empty($_GET['columns'])) ? '' : 'none' ?>;">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="sale_price" style="display: <?= in_array('sale_price', $selectedColumns) ? '' : 'none' ?>;">Sale Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="stock" style="display: <?= in_array('stock', $selectedColumns) ? '' : 'none' ?>;">Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="views" style="display: <?= in_array('views', $selectedColumns) ? '' : 'none' ?>;">Views</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="status" style="display: <?= (in_array('status', $selectedColumns) || empty($_GET['columns'])) ? '' : 'none' ?>;">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="featured" style="display: <?= in_array('featured', $selectedColumns) ? '' : 'none' ?>;">Featured</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="created" style="display: <?= in_array('created', $selectedColumns) ? '' : 'none' ?>;">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="actions" style="display: <?= (in_array('actions', $selectedColumns) || empty($_GET['columns'])) ? '' : 'none' ?>;">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="15" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="bg-gray-100 rounded-full p-6 mb-4">
                                    <i class="fas fa-box text-4xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-700 mb-2">No Products Found</h3>
                                <p class="text-gray-500 mb-4">Try adjusting your filters or add a new product.</p>
                                <a href="<?= url('admin/product-edit.php') ?>" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-700 transition-all">
                                    <i class="fas fa-plus mr-2"></i>
                                    Add New Product
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                    <tr class="hover:bg-blue-50/50 transition-colors border-b border-gray-100">
                        <td class="px-6 py-4 whitespace-nowrap" data-column="checkbox" style="display: <?= (in_array('checkbox', $selectedColumns) || empty($_GET['columns'])) ? '' : 'none' ?>;">
                            <input type="checkbox" class="product-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" value="<?= $product['id'] ?>" onchange="updateBulkActions()">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap" data-column="image" style="display: <?= (in_array('image', $selectedColumns) || empty($_GET['columns'])) ? '' : 'none' ?>;">
                            <?php if (!empty($product['image'])): ?>
                                <div class="relative group">
                                    <img src="<?= asset('storage/uploads/' . escape($product['image'])) ?>" 
                                         alt="" class="h-14 w-14 object-cover rounded-lg border-2 border-gray-200 group-hover:border-blue-400 transition-all shadow-sm">
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 rounded-lg transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                                        <i class="fas fa-eye text-white text-xs"></i>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="h-14 w-14 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center">
                                    <i class="fas fa-image text-gray-400 text-sm"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4" data-column="name" style="display: <?= (in_array('name', $selectedColumns) || empty($_GET['columns'])) ? '' : 'none' ?>;">
                            <div class="flex items-center gap-2">
                                <div class="text-sm font-medium text-gray-900"><?= escape($product['name']) ?></div>
                                <?php if (isset($variantCounts[$product['id']]) && $variantCounts[$product['id']] > 0): ?>
                                    <span class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded-full" title="<?= $variantCounts[$product['id']] ?> variant(s)">
                                        <i class="fas fa-layer-group mr-1"></i><?= $variantCounts[$product['id']] ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($product['short_description'])): ?>
                                <div class="text-xs text-gray-500 line-clamp-1"><?= escape(substr($product['short_description'], 0, 50)) ?>...</div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-column="sku" style="display: <?= in_array('sku', $selectedColumns) ? '' : 'none' ?>;">
                            <?= escape($product['sku'] ?? '-') ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-column="category" style="display: <?= (in_array('category', $selectedColumns) || empty($_GET['columns'])) ? '' : 'none' ?>;">
                            <?= escape($product['category_name'] ?? 'Uncategorized') ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" data-column="price" style="display: <?= (in_array('price', $selectedColumns) || empty($_GET['columns'])) ? '' : 'none' ?>;">
                            <?php if (!empty($product['sale_price']) && $product['sale_price'] > 0): ?>
                                <div class="text-blue-600 font-bold">$<?= number_format((float)$product['sale_price'], 2) ?></div>
                                <?php if (!empty($product['price']) && $product['price'] > 0): ?>
                                    <div class="text-xs text-gray-400 line-through">$<?= number_format((float)$product['price'], 2) ?></div>
                                <?php endif; ?>
                            <?php elseif (!empty($product['price']) && $product['price'] > 0): ?>
                                <div class="font-semibold">$<?= number_format((float)$product['price'], 2) ?></div>
                            <?php else: ?>
                                <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm" data-column="sale_price" style="display: <?= in_array('sale_price', $selectedColumns) ? '' : 'none' ?>;">
                            <?= (!empty($product['sale_price']) && $product['sale_price'] > 0) ? '$' . number_format((float)$product['sale_price'], 2) : '-' ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm" data-column="stock" style="display: <?= in_array('stock', $selectedColumns) ? '' : 'none' ?>;">
                            <span class="px-2 py-1 text-xs rounded <?= 
                                $product['stock_status'] === 'in_stock' ? 'bg-green-100 text-green-800' : 
                                ($product['stock_status'] === 'out_of_stock' ? 'bg-red-100 text-red-800' : 
                                'bg-yellow-100 text-yellow-800') 
                            ?>">
                                <?= ucwords(str_replace('_', ' ', $product['stock_status'])) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-column="views" style="display: <?= in_array('views', $selectedColumns) ? '' : 'none' ?>;">
                            <?= number_format($product['view_count'] ?? 0) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap" data-column="status" style="display: <?= (in_array('status', $selectedColumns) || empty($_GET['columns'])) ? '' : 'none' ?>;">
                            <span class="px-2 py-1 text-xs rounded <?= $product['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= $product['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap" data-column="featured" style="display: <?= in_array('featured', $selectedColumns) ? '' : 'none' ?>;">
                            <?php if ($product['is_featured']): ?>
                                <span class="text-yellow-500"><i class="fas fa-star"></i></span>
                            <?php else: ?>
                                <span class="text-gray-300"><i class="far fa-star"></i></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-column="created" style="display: <?= in_array('created', $selectedColumns) ? '' : 'none' ?>;">
                            <?= date('M d, Y', strtotime($product['created_at'])) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap" data-column="actions" style="display: <?= (in_array('actions', $selectedColumns) || empty($_GET['columns'])) ? '' : 'none' ?>;">
                            <div class="flex items-center space-x-2">
                                <a href="<?= url('admin/product-edit.php?id=' . $product['id']) ?>" 
                                   class="bg-blue-100 hover:bg-blue-200 text-blue-700 p-2 rounded-lg transition-all" title="Edit">
                                    <i class="fas fa-edit text-sm"></i>
                                </a>
                                <a href="<?= url('admin/product-duplicate.php?id=' . $product['id']) ?>" 
                                   onclick="return confirm('Duplicate this product?')" 
                                   class="bg-purple-100 hover:bg-purple-200 text-purple-700 p-2 rounded-lg transition-all" title="Duplicate">
                                    <i class="fas fa-copy text-sm"></i>
                                </a>
                                <a href="?toggle_featured=<?= $product['id'] ?>" 
                                   class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 p-2 rounded-lg transition-all" title="Toggle Featured">
                                    <i class="fas fa-star text-sm"></i>
                                </a>
                                <a href="?delete=<?= $product['id'] ?>" 
                                   onclick="return confirm('Are you sure you want to delete this product?')" 
                                   class="bg-red-100 hover:bg-red-200 text-red-700 p-2 rounded-lg transition-all" title="Delete">
                                    <i class="fas fa-trash text-sm"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAll(checkbox) {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateBulkActions();
}

function updateBulkActions() {
    const checked = document.querySelectorAll('.product-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    
    if (checked.length > 0) {
        bulkActions.classList.remove('hidden');
    } else {
        bulkActions.classList.add('hidden');
    }
}

function clearSelection() {
    document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    updateBulkActions();
}

function executeBulkAction() {
    const action = document.getElementById('bulkActionSelect').value;
    const checked = Array.from(document.querySelectorAll('.product-checkbox:checked')).map(cb => cb.value);
    
    if (!action || checked.length === 0) {
        alert('Please select an action and at least one product.');
        return;
    }
    
    if (action === 'delete' && !confirm(`Delete ${checked.length} product(s)?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', action);
    checked.forEach(id => formData.append('product_ids[]', id));
    
    fetch('<?= url('admin/products-bulk.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
