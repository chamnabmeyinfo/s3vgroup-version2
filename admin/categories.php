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

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Categories</h1>
        <a href="<?= url('admin/category-edit.php') ?>" class="btn-primary">
            <i class="fas fa-plus mr-2"></i> Add New Category
        </a>
    </div>
    
    <?php if ($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= escape($message) ?>
        </div>
    <?php endif; ?>
    
    <!-- Advanced Filters -->
    <?php include __DIR__ . '/includes/advanced-filters.php'; ?>
    
    <div class="mb-4 text-gray-600">
        Showing <?= count($categories) ?> of <?= count($allCategories) ?> categories
    </div>
    
    <div class="bg-white rounded-lg shadow-md overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2" data-column="actions">
                            <a href="<?= url('admin/category-edit.php?id=' . $category['id']) ?>" 
                               class="text-blue-600 hover:text-blue-900" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?delete=<?= $category['id'] ?>" 
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

<?php include __DIR__ . '/includes/footer.php'; ?>
