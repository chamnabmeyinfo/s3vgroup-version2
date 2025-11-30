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
    $productModel->delete($_GET['delete']);
    $message = 'Product deleted successfully.';
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

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Products</h1>
        <div class="flex gap-2">
            <a href="<?= url('admin/product-edit.php') ?>" class="btn-primary">
                <i class="fas fa-plus mr-2"></i> Add New Product
            </a>
            <a href="<?= url('admin/products-export.php') ?>" class="btn-secondary">
                <i class="fas fa-download mr-2"></i> Export CSV
            </a>
        </div>
    </div>
    
    <?php if ($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= escape($message) ?>
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
    
    <div class="mb-4 flex justify-between items-center">
        <div class="text-gray-600">
            Showing <?= count($products) ?> of <?= count($allProducts) ?> products
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
    
    <div class="bg-white rounded-lg shadow-md overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <?php if (in_array('checkbox', $selectedColumns) || empty($_GET['columns'])): ?>
                    <th class="px-6 py-3 text-left" data-column="checkbox">
                        <input type="checkbox" id="selectAll" onchange="toggleAll(this)">
                    </th>
                    <?php endif; ?>
                    
                    <?php if (in_array('image', $selectedColumns) || empty($_GET['columns'])): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="image">Image</th>
                    <?php endif; ?>
                    
                    <?php if (in_array('name', $selectedColumns) || empty($_GET['columns'])): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="name">Name</th>
                    <?php endif; ?>
                    
                    <?php if (in_array('sku', $selectedColumns)): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="sku">SKU</th>
                    <?php endif; ?>
                    
                    <?php if (in_array('category', $selectedColumns) || empty($_GET['columns'])): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="category">Category</th>
                    <?php endif; ?>
                    
                    <?php if (in_array('price', $selectedColumns) || empty($_GET['columns'])): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="price">Price</th>
                    <?php endif; ?>
                    
                    <?php if (in_array('sale_price', $selectedColumns)): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="sale_price">Sale Price</th>
                    <?php endif; ?>
                    
                    <?php if (in_array('stock', $selectedColumns)): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="stock">Stock</th>
                    <?php endif; ?>
                    
                    <?php if (in_array('views', $selectedColumns)): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="views">Views</th>
                    <?php endif; ?>
                    
                    <?php if (in_array('status', $selectedColumns) || empty($_GET['columns'])): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="status">Status</th>
                    <?php endif; ?>
                    
                    <?php if (in_array('featured', $selectedColumns)): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="featured">Featured</th>
                    <?php endif; ?>
                    
                    <?php if (in_array('created', $selectedColumns)): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="created">Created</th>
                    <?php endif; ?>
                    
                    <?php if (in_array('actions', $selectedColumns) || empty($_GET['columns'])): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="actions">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="15" class="px-6 py-4 text-center text-gray-500">No products found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <?php if (in_array('checkbox', $selectedColumns) || empty($_GET['columns'])): ?>
                        <td class="px-6 py-4 whitespace-nowrap" data-column="checkbox">
                            <input type="checkbox" class="product-checkbox" value="<?= $product['id'] ?>" onchange="updateBulkActions()">
                        </td>
                        <?php endif; ?>
                        
                        <?php if (in_array('image', $selectedColumns) || empty($_GET['columns'])): ?>
                        <td class="px-6 py-4 whitespace-nowrap" data-column="image">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?= asset('storage/uploads/' . escape($product['image'])) ?>" 
                                     alt="" class="h-12 w-12 object-cover rounded">
                            <?php else: ?>
                                <span class="text-gray-400 text-xs">No Image</span>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                        
                        <?php if (in_array('name', $selectedColumns) || empty($_GET['columns'])): ?>
                        <td class="px-6 py-4" data-column="name">
                            <div class="text-sm font-medium text-gray-900"><?= escape($product['name']) ?></div>
                            <?php if (!empty($product['short_description'])): ?>
                                <div class="text-xs text-gray-500 line-clamp-1"><?= escape(substr($product['short_description'], 0, 50)) ?>...</div>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                        
                        <?php if (in_array('sku', $selectedColumns)): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-column="sku">
                            <?= escape($product['sku'] ?? '-') ?>
                        </td>
                        <?php endif; ?>
                        
                        <?php if (in_array('category', $selectedColumns) || empty($_GET['columns'])): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-column="category">
                            <?= escape($product['category_name'] ?? 'Uncategorized') ?>
                        </td>
                        <?php endif; ?>
                        
                        <?php if (in_array('price', $selectedColumns) || empty($_GET['columns'])): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" data-column="price">
                            <?php if ($product['sale_price']): ?>
                                <div class="text-blue-600 font-bold">$<?= number_format($product['sale_price'], 2) ?></div>
                                <div class="text-xs text-gray-400 line-through">$<?= number_format($product['price'], 2) ?></div>
                            <?php else: ?>
                                <div class="font-semibold">$<?= number_format($product['price'], 2) ?></div>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                        
                        <?php if (in_array('sale_price', $selectedColumns)): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-sm" data-column="sale_price">
                            <?= $product['sale_price'] ? '$' . number_format($product['sale_price'], 2) : '-' ?>
                        </td>
                        <?php endif; ?>
                        
                        <?php if (in_array('stock', $selectedColumns)): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-sm" data-column="stock">
                            <span class="px-2 py-1 text-xs rounded <?= 
                                $product['stock_status'] === 'in_stock' ? 'bg-green-100 text-green-800' : 
                                ($product['stock_status'] === 'out_of_stock' ? 'bg-red-100 text-red-800' : 
                                'bg-yellow-100 text-yellow-800') 
                            ?>">
                                <?= ucwords(str_replace('_', ' ', $product['stock_status'])) ?>
                            </span>
                        </td>
                        <?php endif; ?>
                        
                        <?php if (in_array('views', $selectedColumns)): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-column="views">
                            <?= number_format($product['view_count'] ?? 0) ?>
                        </td>
                        <?php endif; ?>
                        
                        <?php if (in_array('status', $selectedColumns) || empty($_GET['columns'])): ?>
                        <td class="px-6 py-4 whitespace-nowrap" data-column="status">
                            <span class="px-2 py-1 text-xs rounded <?= $product['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= $product['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <?php endif; ?>
                        
                        <?php if (in_array('featured', $selectedColumns)): ?>
                        <td class="px-6 py-4 whitespace-nowrap" data-column="featured">
                            <?php if ($product['is_featured']): ?>
                                <span class="text-yellow-500"><i class="fas fa-star"></i></span>
                            <?php else: ?>
                                <span class="text-gray-300"><i class="far fa-star"></i></span>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                        
                        <?php if (in_array('created', $selectedColumns)): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-column="created">
                            <?= date('M d, Y', strtotime($product['created_at'])) ?>
                        </td>
                        <?php endif; ?>
                        
                        <?php if (in_array('actions', $selectedColumns) || empty($_GET['columns'])): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2" data-column="actions">
                            <a href="<?= url('admin/product-edit.php?id=' . $product['id']) ?>" 
                               class="text-blue-600 hover:text-blue-900" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?= url('admin/product-duplicate.php?id=' . $product['id']) ?>" 
                               onclick="return confirm('Duplicate this product?')" 
                               class="text-purple-600 hover:text-purple-900" title="Duplicate">
                                <i class="fas fa-copy"></i>
                            </a>
                            <a href="?toggle_featured=<?= $product['id'] ?>" 
                               class="text-yellow-600 hover:text-yellow-900" title="Toggle Featured">
                                <i class="fas fa-star"></i>
                            </a>
                            <a href="?delete=<?= $product['id'] ?>" 
                               onclick="return confirm('Are you sure?')" 
                               class="text-red-600 hover:text-red-900" title="Delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
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
