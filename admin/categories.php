<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

use App\Models\Category;

$categoryModel = new Category();
$message = '';
$error = '';

if (!empty($_GET['delete'])) {
    try {
        $categoryId = (int)$_GET['delete'];
        
        // Validate ID
        if ($categoryId <= 0) {
            $error = 'Invalid category ID.';
        } else {
            // Check if category exists
            $category = $categoryModel->getById($categoryId);
            if (!$category) {
                $error = 'Category not found.';
            } else {
                // Check if category has products
                $productCount = db()->fetchOne(
                    "SELECT COUNT(*) as count FROM products WHERE category_id = :id",
                    ['id' => $categoryId]
                )['count'] ?? 0;
                
                if ($productCount > 0) {
                    $error = "Cannot delete category. It has {$productCount} product(s) assigned. Please reassign or delete products first.";
                } else {
                    // Safe to delete
                    $deleted = db()->delete('categories', 'id = :id', ['id' => $categoryId]);
                    if ($deleted > 0) {
                        $message = 'Category deleted successfully.';
                    } else {
                        $error = 'Failed to delete category.';
                    }
                }
            }
        }
    } catch (\Exception $e) {
        $error = 'Error deleting category: ' . $e->getMessage();
    }
}

// Get filter parameters
$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'name_asc';

// Get all categories
$allCategories = $categoryModel->getAll(false);

// Apply filters
$categories = $allCategories;

if ($search) {
    $categories = array_filter($categories, function($cat) use ($search) {
        return stripos($cat['name'], $search) !== false || 
               stripos($cat['slug'], $search) !== false ||
               stripos($cat['description'] ?? '', $search) !== false;
    });
}

if ($statusFilter === 'active') {
    $categories = array_filter($categories, fn($c) => $c['is_active'] == 1);
} elseif ($statusFilter === 'inactive') {
    $categories = array_filter($categories, fn($c) => $c['is_active'] == 0);
}

// Sort
switch ($sort) {
    case 'name_asc':
        usort($categories, fn($a, $b) => strcmp($a['name'], $b['name']));
        break;
    case 'name_desc':
        usort($categories, fn($a, $b) => strcmp($b['name'], $a['name']));
        break;
    case 'date_desc':
        usort($categories, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
        break;
}

// Column visibility
$selectedColumns = $_GET['columns'] ?? ['name', 'slug', 'status', 'actions'];
$availableColumns = [
    'name' => 'Name',
    'slug' => 'Slug',
    'description' => 'Description',
    'status' => 'Status',
    'products' => 'Products Count',
    'created' => 'Created Date',
    'actions' => 'Actions'
];

$pageTitle = 'Categories';
include __DIR__ . '/includes/header.php';

// Setup filter component
$filterId = 'categories-filter';
$filters = [
    'search' => true,
    'status' => [
        'options' => [
            'all' => 'All Statuses',
            'active' => 'Active Only',
            'inactive' => 'Inactive Only'
        ]
    ]
];
$sortOptions = [
    'name_asc' => 'Name (A-Z)',
    'name_desc' => 'Name (Z-A)',
    'date_desc' => 'Newest First'
];
$defaultColumns = ['name', 'slug', 'status', 'actions'];
?>

<div class="w-full">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-xl shadow-xl p-4 md:p-6 lg:p-8 mb-4 md:mb-6 text-white">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-1 md:mb-2">
                    <i class="fas fa-tags mr-2 md:mr-3"></i>
                    Categories Management
                </h1>
                <p class="text-green-100 text-sm md:text-lg">Organize your products into categories</p>
            </div>
            <a href="<?= url('admin/category-edit.php') ?>" class="bg-white text-green-600 hover:bg-green-50 px-4 md:px-6 py-2 rounded-lg font-semibold transition-all shadow-lg hover:shadow-xl w-full sm:w-auto text-center text-sm md:text-base">
                <i class="fas fa-plus mr-2"></i>
                Add New Category
            </a>
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
    
    <!-- Stats Bar -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-6">
                <div>
                    <span class="text-sm text-gray-600">Total Categories:</span>
                    <span class="ml-2 font-bold text-gray-900"><?= count($allCategories) ?></span>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Showing:</span>
                    <span class="ml-2 font-bold text-green-600"><?= count($categories) ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Categories Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto -mx-4 md:mx-0">
            <div class="inline-block min-w-full align-middle">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                <tr>
                    <?php if (in_array('name', $selectedColumns) || empty($_GET['columns'])): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="name">Name</th>
                    <?php endif; ?>
                    
                    <?php if (in_array('slug', $selectedColumns) || empty($_GET['columns'])): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="slug">Slug</th>
                    <?php endif; ?>
                    
                    <?php if (in_array('description', $selectedColumns)): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="description">Description</th>
                    <?php endif; ?>
                    
                    <?php if (in_array('status', $selectedColumns) || empty($_GET['columns'])): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="status">Status</th>
                    <?php endif; ?>
                    
                    <?php if (in_array('products', $selectedColumns)): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="products">Products</th>
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
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No categories found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                    <?php
                    // Get product count for this category
                    $productCount = db()->fetchOne(
                        "SELECT COUNT(*) as count FROM products WHERE category_id = :id",
                        ['id' => $category['id']]
                    )['count'] ?? 0;
                    ?>
                    <tr>
                        <?php if (in_array('name', $selectedColumns) || empty($_GET['columns'])): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" data-column="name">
                            <?= escape($category['name']) ?>
                        </td>
                        <?php endif; ?>
                        
                        <?php if (in_array('slug', $selectedColumns) || empty($_GET['columns'])): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-column="slug">
                            <?= escape($category['slug']) ?>
                        </td>
                        <?php endif; ?>
                        
                        <?php if (in_array('description', $selectedColumns)): ?>
                        <td class="px-6 py-4 text-sm text-gray-500" data-column="description">
                            <div class="max-w-xs truncate" title="<?= escape($category['description'] ?? '') ?>">
                                <?= escape(substr($category['description'] ?? '', 0, 50)) ?>...
                            </div>
                        </td>
                        <?php endif; ?>
                        
                        <?php if (in_array('status', $selectedColumns) || empty($_GET['columns'])): ?>
                        <td class="px-6 py-4 whitespace-nowrap" data-column="status">
                            <span class="px-2 py-1 text-xs rounded <?= $category['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= $category['is_active'] ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <?php endif; ?>
                        
                        <?php if (in_array('products', $selectedColumns)): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-column="products">
                            <?= number_format($productCount) ?>
                        </td>
                        <?php endif; ?>
                        
                        <?php if (in_array('created', $selectedColumns)): ?>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-column="created">
                            <?= date('M d, Y', strtotime($category['created_at'])) ?>
                        </td>
                        <?php endif; ?>
                        
                        <?php if (in_array('actions', $selectedColumns) || empty($_GET['columns'])): ?>
                        <td class="px-6 py-4 whitespace-nowrap" data-column="actions">
                            <div class="flex items-center space-x-2">
                                <a href="<?= url('admin/category-edit.php?id=' . $category['id']) ?>" 
                                   class="bg-blue-100 hover:bg-blue-200 text-blue-700 p-2 rounded-lg transition-all" title="Edit">
                                    <i class="fas fa-edit text-sm"></i>
                                </a>
                                <a href="<?= url('admin/category-duplicate.php?id=' . $category['id']) ?>" 
                                   onclick="return confirm('Duplicate this category?')" 
                                   class="bg-purple-100 hover:bg-purple-200 text-purple-700 p-2 rounded-lg transition-all" title="Duplicate">
                                    <i class="fas fa-copy text-sm"></i>
                                </a>
                                <a href="?delete=<?= $category['id'] ?>" 
                                   onclick="return confirm('Are you sure you want to delete this category?')" 
                                   class="bg-red-100 hover:bg-red-200 text-red-700 p-2 rounded-lg transition-all" title="Delete">
                                    <i class="fas fa-trash text-sm"></i>
                                </a>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
