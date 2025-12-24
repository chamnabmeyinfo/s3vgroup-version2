<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

use App\Models\Category;

$categoryModel = new Category();
$message = '';
$error = '';

// Handle AJAX requests for drag and drop reordering
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    // Check if JSON request
    if (strpos($contentType, 'application/json') !== false && !empty($input)) {
        $data = json_decode($input, true);
        
        if (!empty($data) && !empty($data['action']) && $data['action'] === 'save_order') {
            header('Content-Type: application/json');
            
            $orders = $data['orders'] ?? [];
            if (!empty($orders)) {
                $saved = $categoryModel->reorder($orders);
                echo json_encode(['success' => $saved]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid data']);
            }
            exit;
        }
    }
}

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
                // Get all descendants (sub-categories at all levels)
                $descendants = $categoryModel->getDescendants($categoryId, false);
                $subCategoryCount = count($descendants);
                
                if ($subCategoryCount > 0) {
                    $error = "Cannot delete category. It has {$subCategoryCount} sub-category(ies). Please delete or reassign sub-categories first.";
                } else {
                    // Check if category has products
                    $productCount = $categoryModel->getProductCount($categoryId, false);
                    if ($productCount > 0) {
                        $error = "Cannot delete category. It has {$productCount} product(s) assigned. Please reassign or delete products first.";
                    } else {
                        // Safe to delete
                        $deleted = $categoryModel->delete($categoryId);
                        if ($deleted) {
                            $message = 'Category deleted successfully.';
                        } else {
                            $error = 'Failed to delete category.';
                        }
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

// Get hierarchical tree for display
$categoryTree = $categoryModel->getTree(null, false);

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
$selectedColumns = $_GET['columns'] ?? ['image', 'name', 'slug', 'status', 'actions'];
// Check if short_description column exists
$hasShortDescription = false;
try {
    db()->fetchOne("SELECT short_description FROM categories LIMIT 1");
    $hasShortDescription = true;
} catch (\Exception $e) {
    $hasShortDescription = false;
}

$availableColumns = [
    'image' => 'Image',
    'name' => 'Name',
    'slug' => 'Slug',
    'description' => 'Description',
    'status' => 'Status',
    'products' => 'Products Count',
    'created' => 'Created Date',
    'actions' => 'Actions'
];

if ($hasShortDescription) {
    $availableColumns['short_description'] = 'Short Description';
}

// Calculate stats for mini dashboard
$totalCategories = count($allCategories);
$activeCategories = count(array_filter($allCategories, fn($c) => $c['is_active'] == 1));
$inactiveCategories = $totalCategories - $activeCategories;

// Count products per category
$categoriesWithProducts = 0;
foreach ($allCategories as $cat) {
    $productCount = db()->fetchOne(
        "SELECT COUNT(*) as count FROM products WHERE category_id = :id",
        ['id' => $cat['id']]
    )['count'] ?? 0;
    if ($productCount > 0) {
        $categoriesWithProducts++;
    }
}

$miniStats = [
    [
        'label' => 'Total Categories',
        'value' => number_format($totalCategories),
        'icon' => 'fas fa-tags',
        'color' => 'from-green-500 to-emerald-600',
        'description' => 'All categories',
        'link' => url('admin/categories.php')
    ],
    [
        'label' => 'Active Categories',
        'value' => number_format($activeCategories),
        'icon' => 'fas fa-check-circle',
        'color' => 'from-blue-500 to-cyan-600',
        'description' => 'Currently active',
        'link' => url('admin/categories.php?status=active')
    ],
    [
        'label' => 'With Products',
        'value' => number_format($categoriesWithProducts),
        'icon' => 'fas fa-box',
        'color' => 'from-purple-500 to-indigo-600',
        'description' => 'Have products assigned',
        'link' => url('admin/categories.php')
    ],
    [
        'label' => 'Inactive',
        'value' => number_format($inactiveCategories),
        'icon' => 'fas fa-ban',
        'color' => 'from-gray-500 to-gray-600',
        'description' => 'Currently inactive',
        'link' => url('admin/categories.php?status=inactive')
    ]
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
                    <!-- Drag Handle Column - Always visible -->
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-10" data-column="drag">
                        <i class="fas fa-grip-vertical"></i>
                    </th>
                    
                    <!-- Icon Column - Always visible -->
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="icon">
                        <i class="fas fa-icons mr-1"></i>Icon
                    </th>
                    
                    <?php if (in_array('image', $selectedColumns)): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="image">Image</th>
                    <?php endif; ?>
                    
                    <?php if (in_array('name', $selectedColumns) || empty($_GET['columns'])): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="name">
                        <i class="fas fa-sitemap mr-1"></i>Name / Hierarchy
                    </th>
                    <?php endif; ?>
                    
                    <?php if (in_array('slug', $selectedColumns) || empty($_GET['columns'])): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="slug">Slug</th>
                    <?php endif; ?>
                    
                    <?php 
                    // Check if short_description column exists
                    $hasShortDescription = false;
                    try {
                        db()->fetchOne("SELECT short_description FROM categories LIMIT 1");
                        $hasShortDescription = true;
                    } catch (\Exception $e) {
                        $hasShortDescription = false;
                    }
                    ?>
                    <?php if ($hasShortDescription && in_array('short_description', $selectedColumns)): ?>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" data-column="short_description">Short Description</th>
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
            <tbody class="bg-white divide-y divide-gray-200" id="categories-tbody">
                <?php if (empty($categoryTree)): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                            <div class="py-8">
                                <i class="fas fa-folder-open text-gray-300 text-6xl mb-4"></i>
                                <p class="text-lg font-semibold text-gray-700 mb-2">No Categories</p>
                                <p class="text-gray-500 mb-4">Create your first category to get started.</p>
                                <a href="<?= url('admin/category-edit.php') ?>" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-all">
                                    <i class="fas fa-plus mr-2"></i>Add First Category
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    // Render categories in hierarchical tree
                    function renderCategoryTree($categoryTree, $categoryModel, $selectedColumns, $level = 0) {
                        $html = '';
                        foreach ($categoryTree as $category) {
                            // Get product count (including sub-categories)
                            $productCount = $categoryModel->getProductCount($category['id'], true);
                            $directProductCount = $categoryModel->getProductCount($category['id'], false);
                            $subCategoryCount = count($categoryModel->getChildren($category['id'], false));
                            
                            // Get parent info
                            $parent = null;
                            if (!empty($category['parent_id'])) {
                                $parent = $categoryModel->getById($category['parent_id']);
                            }
                            
                            // Get category path
                            $categoryPath = $categoryModel->getPath($category['id']);
                            
                            // Get automatic icon
                            $categoryIcons = [
                                'forklift' => 'fa-truck',
                                'electric' => 'fa-bolt',
                                'diesel' => 'fa-gas-pump',
                                'gas' => 'fa-fire',
                                'ic' => 'fa-cog',
                                'li-ion' => 'fa-battery-full',
                                'attachment' => 'fa-puzzle-piece',
                                'pallet' => 'fa-boxes',
                                'stacker' => 'fa-layer-group',
                                'reach' => 'fa-arrow-up',
                            ];
                            
                            $categoryName = strtolower($category['name']);
                            $icon = 'fa-box';
                            foreach ($categoryIcons as $key => $iconClass) {
                                if (strpos($categoryName, $key) !== false) {
                                    $icon = $iconClass;
                                    break;
                                }
                            }
                            
                            $indentClass = $level > 0 ? 'pl-' . ($level * 6) : '';
                            $hasChildren = !empty($category['children']);
                            
                            $html .= '<tr class="category-row ' . ($hasChildren ? 'bg-blue-50/30' : '') . ' hover:bg-gray-50 transition-colors" data-category-id="' . $category['id'] . '" data-parent-id="' . ($category['parent_id'] ?? '') . '" data-level="' . $level . '" data-sort-order="' . ($category['sort_order'] ?? 0) . '">';
                            
                            // Drag Handle Column
                            $html .= '<td class="px-4 py-4 whitespace-nowrap cursor-move drag-handle" data-column="drag">';
                            $html .= '<i class="fas fa-grip-vertical text-gray-400 hover:text-gray-600 transition-colors"></i>';
                            $html .= '</td>';
                            
                            // Icon Column
                            $html .= '<td class="px-6 py-4 whitespace-nowrap" data-column="icon">';
                            $html .= '<div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-lg border-2 border-blue-300 flex items-center justify-center shadow-sm">';
                            $html .= '<i class="fas ' . escape($icon) . ' text-blue-700 text-2xl" title="' . escape($category['name']) . '"></i>';
                            $html .= '</div></td>';
                            
                            // Image Column
                            if (in_array('image', $selectedColumns)) {
                                $html .= '<td class="px-6 py-4 whitespace-nowrap" data-column="image">';
                                if (!empty($category['image'])) {
                                    $html .= '<img src="' . escape(image_url($category['image'])) . '" alt="' . escape($category['name']) . '" class="w-16 h-16 object-cover rounded-lg border border-gray-200">';
                                } else {
                                    $html .= '<div class="w-16 h-16 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center"><span class="text-xs text-gray-500">No Image</span></div>';
                                }
                                $html .= '</td>';
                            }
                            
                            // Name Column with Hierarchy
                            if (in_array('name', $selectedColumns) || empty($_GET['columns'])) {
                                $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium ' . $indentClass . '" data-column="name">';
                                $html .= '<div class="flex items-center gap-2 category-name-content">';
                                if ($level > 0) {
                                    // Show indentation indicators
                                    for ($i = 0; $i < $level; $i++) {
                                        $html .= '<i class="fas fa-chevron-right text-gray-300 text-xs"></i>';
                                    }
                                    $html .= '<i class="fas fa-folder-open mr-2 text-indigo-500"></i>';
                                } else {
                                    $html .= '<i class="fas fa-folder mr-2 text-blue-600"></i>';
                                }
                                $html .= '<span class="font-semibold text-gray-900">' . escape($category['name']) . '</span>';
                                if ($hasChildren) {
                                    $html .= '<span class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-xs font-semibold">' . count($category['children']) . ' sub</span>';
                                }
                                if ($subCategoryCount > 0) {
                                    $html .= '<span class="ml-1 text-xs text-gray-500">(' . $subCategoryCount . ' sub-categories)</span>';
                                }
                                $html .= '</div>';
                                if ($parent && $level > 0) {
                                    $html .= '<div class="text-xs text-gray-500 mt-1 category-parent-info">Parent: ' . escape($parent['name']) . '</div>';
                                }
                                $html .= '</td>';
                            }
                            
                            // Slug Column
                            if (in_array('slug', $selectedColumns) || empty($_GET['columns'])) {
                                $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-column="slug">';
                                $html .= '<code class="px-2 py-1 bg-gray-100 rounded text-xs">' . escape($category['slug']) . '</code>';
                                $html .= '</td>';
                            }
                            
                            // Status Column
                            if (in_array('status', $selectedColumns) || empty($_GET['columns'])) {
                                $html .= '<td class="px-6 py-4 whitespace-nowrap" data-column="status">';
                                $html .= '<span class="px-2 py-1 text-xs rounded ' . ($category['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') . '">';
                                $html .= $category['is_active'] ? 'Active' : 'Inactive';
                                $html .= '</span></td>';
                            }
                            
                            // Products Column
                            if (in_array('products', $selectedColumns)) {
                                $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-column="products">';
                                $html .= '<div class="flex flex-col">';
                                $html .= '<span class="font-semibold">' . number_format($productCount) . '</span>';
                                if ($productCount > $directProductCount) {
                                    $html .= '<span class="text-xs text-gray-400">(' . number_format($directProductCount) . ' direct)</span>';
                                }
                                $html .= '</div></td>';
                            }
                            
                            // Created Column
                            if (in_array('created', $selectedColumns)) {
                                $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-column="created">';
                                $html .= date('M d, Y', strtotime($category['created_at']));
                                $html .= '</td>';
                            }
                            
                            // Actions Column
                            if (in_array('actions', $selectedColumns) || empty($_GET['columns'])) {
                                $html .= '<td class="px-6 py-4 whitespace-nowrap" data-column="actions">';
                                $html .= '<div class="flex items-center space-x-2">';
                                $html .= '<a href="' . url('admin/category-edit.php?id=' . $category['id']) . '" class="bg-blue-100 hover:bg-blue-200 text-blue-700 p-2 rounded-lg transition-all" title="Edit"><i class="fas fa-edit text-sm"></i></a>';
                                if ($hasChildren) {
                                    $html .= '<span class="bg-indigo-100 text-indigo-700 p-2 rounded-lg cursor-default" title="Has ' . count($category['children']) . ' Sub-Categories"><i class="fas fa-sitemap text-sm"></i></span>';
                                }
                                $descendants = $categoryModel->getDescendants($category['id'], false);
                                $hasDescendants = !empty($descendants);
                                $deleteMsg = $hasDescendants ? 'Delete this category and all ' . count($descendants) . ' sub-categories?' : 'Delete this category?';
                                $html .= '<a href="?delete=' . $category['id'] . '" onclick="return confirm(\'' . escape($deleteMsg) . '\')" class="bg-red-100 hover:bg-red-200 text-red-700 p-2 rounded-lg transition-all" title="Delete"><i class="fas fa-trash text-sm"></i></a>';
                                $html .= '</div></td>';
                            }
                            
                            $html .= '</tr>';
                            
                            // Render children recursively
                            if ($hasChildren) {
                                $html .= renderCategoryTree($category['children'], $categoryModel, $selectedColumns, $level + 1);
                            }
                        }
                        return $html;
                    }
                    
                    echo renderCategoryTree($categoryTree, $categoryModel, $selectedColumns);
                    ?>
                <?php endif; ?>
            </tbody>
        </table>
            </div>
        </div>
    </div>
</div>

<!-- SortableJS for Drag and Drop -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.css">
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<style>
.sortable-ghost {
    opacity: 0.4;
    background-color: #e5e7eb;
}

.category-row.sortable-ghost {
    background-color: #dbeafe !important;
}

.drag-handle {
    cursor: move;
    user-select: none;
}

.drag-handle:hover {
    color: #4b5563;
}

.category-row.dragging {
    opacity: 0.5;
}

.category-row {
    transition: background-color 0.2s ease;
}

.category-row.bg-blue-50\/30 {
    background-color: rgba(239, 246, 255, 0.3);
}

/* Visual feedback for drag and drop */
.category-row {
    position: relative;
}

.category-row[data-level="1"] {
    border-left: 3px solid #3b82f6;
}

.category-row[data-level="2"] {
    border-left: 3px solid #6366f1;
}

.category-row[data-level="3"] {
    border-left: 3px solid #8b5cf6;
}

.save-order-btn {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    z-index: 50;
    display: none;
}

.save-order-btn.show {
    display: block;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.querySelector('tbody');
    const saveOrderBtn = document.getElementById('save-order-btn');
    let sortableInstance = null;
    let hasChanges = false;
    
    if (tbody && typeof Sortable !== 'undefined') {
        // Create save button if it doesn't exist
        if (!saveOrderBtn) {
            const btn = document.createElement('button');
            btn.id = 'save-order-btn';
            btn.className = 'save-order-btn bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg shadow-lg font-semibold transition-all flex items-center gap-2';
            btn.innerHTML = '<i class="fas fa-save"></i> Save Order';
            btn.onclick = saveCategoryOrder;
            document.body.appendChild(btn);
        }
        
        sortableInstance = new Sortable(tbody, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'dragging',
            onStart: function(evt) {
                hasChanges = true;
                document.getElementById('save-order-btn').classList.add('show');
                // Store original position
                evt.item.setAttribute('data-original-index', evt.oldIndex);
            },
            onEnd: function(evt) {
                // Update hierarchy based on new position
                // Small delay to ensure DOM is updated
                setTimeout(() => {
                    updateCategoryHierarchy();
                }, 10);
            }
        });
    } else {
        console.error('SortableJS not loaded or tbody not found');
    }
    
    function updateCategoryHierarchy() {
        const rows = Array.from(tbody.querySelectorAll('.category-row:not(.sortable-ghost)'));
        
        // WordPress-style hierarchy logic:
        // - Items placed directly below another item become its children (indented)
        // - Items at the same indentation level are siblings
        // - This creates intuitive drag-and-drop behavior
        
        rows.forEach((row, index) => {
            let newParentId = null;
            let newLevel = 0;
            
            if (index > 0) {
                // Look at the previous row
                const prevRow = rows[index - 1];
                const prevLevel = parseInt(prevRow.getAttribute('data-level') || 0);
                const prevCategoryId = parseInt(prevRow.getAttribute('data-category-id'));
                
                // WordPress behavior: When you drag an item below another, it becomes a child
                // The level increases by 1 from the previous item
                const maxDepth = 5; // Allow up to 5 levels deep
                
                if (prevLevel < maxDepth) {
                    // Make this a child of the previous item (WordPress-style)
                    newParentId = prevCategoryId;
                    newLevel = prevLevel + 1;
                } else {
                    // At max depth, find the appropriate parent
                    // Look backwards to find an item at a lower level
                    newLevel = prevLevel;
                    for (let i = index - 1; i >= 0; i--) {
                        const checkRow = rows[i];
                        const checkLevel = parseInt(checkRow.getAttribute('data-level') || 0);
                        if (checkLevel < prevLevel) {
                            newParentId = parseInt(checkRow.getAttribute('data-category-id'));
                            newLevel = checkLevel + 1;
                            break;
                        }
                    }
                    // If no suitable parent found, make it top-level
                    if (!newParentId) {
                        newParentId = null;
                        newLevel = 0;
                    }
                }
            } else {
                // First item is always top-level
                newParentId = null;
                newLevel = 0;
            }
            
            // Update row attributes
            row.setAttribute('data-parent-id', newParentId || '');
            row.setAttribute('data-level', newLevel);
            row.setAttribute('data-sort-order', index + 1);
            
            // Update visual indentation immediately for instant feedback
            updateRowVisuals(row, newLevel);
        });
    }
    
    function updateRowVisuals(row, level) {
        // Update the name cell's indentation
        const nameCell = row.querySelector('[data-column="name"]');
        if (nameCell) {
            // Remove existing indentation classes
            nameCell.className = nameCell.className.replace(/pl-\d+/g, '');
            if (level > 0) {
                nameCell.classList.add('pl-' + (level * 6));
            }
            
            // Update the category name content div
            const nameContent = nameCell.querySelector('.category-name-content');
            if (nameContent) {
                // Remove existing chevrons and folder icons
                const existingIcons = nameContent.querySelectorAll('.fa-chevron-right, .fa-folder, .fa-folder-open');
                existingIcons.forEach(icon => icon.remove());
                
                // Get the name span to preserve it
                const nameSpan = nameContent.querySelector('span.font-semibold');
                const badges = Array.from(nameContent.querySelectorAll('span.ml-2, span.ml-1'));
                
                // Create a temporary container to hold everything
                const fragment = document.createDocumentFragment();
                
                // Add indentation chevrons
                for (let i = 0; i < level; i++) {
                    const chevron = document.createElement('i');
                    chevron.className = 'fas fa-chevron-right text-gray-300 text-xs';
                    fragment.appendChild(chevron);
                }
                
                // Add folder icon
                const folderIcon = document.createElement('i');
                folderIcon.className = level > 0 
                    ? 'fas fa-folder-open mr-2 text-indigo-500'
                    : 'fas fa-folder mr-2 text-blue-600';
                fragment.appendChild(folderIcon);
                
                // Add name span if it exists
                if (nameSpan) {
                    fragment.appendChild(nameSpan.cloneNode(true));
                }
                
                // Add badges
                badges.forEach(badge => {
                    fragment.appendChild(badge.cloneNode(true));
                });
                
                // Clear and rebuild
                nameContent.innerHTML = '';
                nameContent.appendChild(fragment);
                
            }
            
            // Update parent info (it's outside nameContent, in the nameCell)
            const existingParentInfo = nameCell.querySelector('.category-parent-info');
            if (level > 0) {
                // Find the parent category name from the row structure
                const allRows = Array.from(tbody.querySelectorAll('.category-row:not(.sortable-ghost)'));
                const currentIndex = allRows.indexOf(row);
                if (currentIndex > 0) {
                    const parentId = row.getAttribute('data-parent-id');
                    if (parentId) {
                        // Find the parent row
                        const parentRow = allRows.find(r => r.getAttribute('data-category-id') === parentId);
                        if (parentRow) {
                            const parentNameSpan = parentRow.querySelector('[data-column="name"] span.font-semibold');
                            const parentName = parentNameSpan ? parentNameSpan.textContent.trim() : 'Parent';
                            
                            if (existingParentInfo) {
                                existingParentInfo.textContent = 'Parent: ' + parentName;
                            } else {
                                const parentInfoDiv = document.createElement('div');
                                parentInfoDiv.className = 'text-xs text-gray-500 mt-1 category-parent-info';
                                parentInfoDiv.textContent = 'Parent: ' + parentName;
                                nameCell.appendChild(parentInfoDiv);
                            }
                        }
                    }
                }
            } else {
                // Remove parent info for top-level items
                if (existingParentInfo) {
                    existingParentInfo.remove();
                }
            }
        }
        
        // Update row background for sub-items
        if (level > 0) {
            if (!row.classList.contains('bg-blue-50/30')) {
                row.classList.add('bg-blue-50/30');
            }
        } else {
            row.classList.remove('bg-blue-50/30');
        }
    }
    
    function saveCategoryOrder() {
        const rows = tbody.querySelectorAll('.category-row:not(.sortable-ghost)');
        const orders = [];
        
        rows.forEach((row, index) => {
            const categoryId = parseInt(row.getAttribute('data-category-id'));
            // Get updated parent_id from data attribute (updated by updateCategoryHierarchy)
            const parentIdAttr = row.getAttribute('data-parent-id');
            const parentId = (parentIdAttr && parentIdAttr !== '' && parentIdAttr !== 'null') ? parseInt(parentIdAttr) : null;
            const sortOrder = index + 1;
            
            orders.push({
                id: categoryId,
                parent_id: parentId,
                sort_order: sortOrder
            });
        });
        
        if (orders.length === 0) {
            alert('No categories to save');
            return;
        }
        
        // Show loading state
        const btn = document.getElementById('save-order-btn');
        const originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'save_order',
                orders: orders
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                showNotification('Category order saved successfully!', 'success');
                hasChanges = false;
                btn.classList.remove('show');
                
                // Reload page after a short delay to show updated order
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showNotification('Failed to save category order: ' + (data.error || 'Unknown error'), 'error');
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error saving category order: ' + error.message, 'error');
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        });
    }
    
    function showNotification(message, type) {
        // Remove existing notifications
        const existing = document.querySelector('.category-notification');
        if (existing) {
            existing.remove();
        }
        
        const notification = document.createElement('div');
        notification.className = 'category-notification fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg font-semibold ' + 
            (type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white');
        notification.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + ' mr-2"></i>' + message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transition = 'opacity 0.3s';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    // Warn before leaving if there are unsaved changes
    window.addEventListener('beforeunload', function(e) {
        if (hasChanges) {
            e.preventDefault();
            e.returnValue = 'You have unsaved changes to category order. Are you sure you want to leave?';
            return e.returnValue;
        }
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
