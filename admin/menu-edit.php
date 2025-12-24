<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Category;
use App\Models\Product;

$menuModel = new Menu();
$itemModel = new MenuItem();
$categoryModel = new Category();
$productModel = new Product();

$message = '';
$error = '';
$menu = null;
$menuId = !empty($_GET['id']) ? (int)$_GET['id'] : (!empty($_GET['menu_id']) ? (int)$_GET['menu_id'] : 0);
$isEdit = false;

// Handle AJAX requests FIRST - before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    // Check if JSON request
    if (strpos($contentType, 'application/json') !== false && !empty($input)) {
        $data = json_decode($input, true);
        
        if (!empty($data) && !empty($data['action'])) {
            header('Content-Type: application/json');
            
            if ($data['action'] === 'delete_item') {
                $itemId = (int)($data['item_id'] ?? 0);
                if ($itemId > 0) {
                    $deleted = $itemModel->delete($itemId);
                    echo json_encode(['success' => $deleted]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Invalid item ID']);
                }
                exit;
            }
            
            if ($data['action'] === 'save_order') {
                $menuId = (int)($data['menu_id'] ?? 0);
                $orders = $data['orders'] ?? [];
                if ($menuId > 0 && !empty($orders)) {
                    $saved = $itemModel->reorder($menuId, $orders);
                    echo json_encode(['success' => $saved]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Invalid data']);
                }
                exit;
            }
        }
    }
    
    // Handle form submission for add item
    if (!empty($_POST['action']) && $_POST['action'] === 'add_item') {
        header('Content-Type: application/json');
        
        $menuId = (int)($_POST['menu_id'] ?? 0);
        if ($menuId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid menu ID']);
            exit;
        }
        
        $data = [
            'menu_id' => $menuId,
            'title' => trim($_POST['title'] ?? ''),
            'url' => trim($_POST['url'] ?? ''),
            'type' => $_POST['item_type'] ?? 'custom',
            'object_id' => !empty($_POST['object_id']) ? (int)$_POST['object_id'] : null,
            'target' => $_POST['target'] ?? '_self',
            'icon' => trim($_POST['icon'] ?? ''),
            'css_classes' => trim($_POST['css_classes'] ?? '')
        ];
        
        if (empty($data['title'])) {
            echo json_encode(['success' => false, 'error' => 'Title is required']);
            exit;
        }
        
        if ($data['type'] === 'category') {
            if (empty($data['object_id'])) {
                echo json_encode(['success' => false, 'error' => 'Please select a category']);
                exit;
            }
            $cat = $categoryModel->getById($data['object_id']);
            if ($cat) {
                $data['title'] = $cat['name'];
                $data['url'] = url('products.php?category=' . $cat['slug']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Category not found']);
                exit;
            }
        } elseif ($data['type'] === 'product') {
            if (empty($data['object_id'])) {
                echo json_encode(['success' => false, 'error' => 'Please select a product']);
                exit;
            }
            $prod = $productModel->getById($data['object_id']);
            if ($prod) {
                $data['title'] = $prod['name'];
                $data['url'] = url('product.php?slug=' . $prod['slug']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Product not found']);
                exit;
            }
        } elseif ($data['type'] === 'page') {
            if (empty($data['object_id'])) {
                echo json_encode(['success' => false, 'error' => 'Please select a page']);
                exit;
            }
            try {
                $page = db()->fetchOne("SELECT id, title, slug FROM pages WHERE id = :id AND is_active = 1", ['id' => $data['object_id']]);
                if ($page) {
                    $data['title'] = $page['title'];
                    $data['url'] = url('page.php?slug=' . $page['slug']);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Page not found']);
                    exit;
                }
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'error' => 'Pages table not available']);
                exit;
            }
        } elseif ($data['type'] === 'post') {
            if (empty($data['object_id'])) {
                echo json_encode(['success' => false, 'error' => 'Please select a blog post']);
                exit;
            }
            try {
                $post = db()->fetchOne("SELECT id, title, slug FROM blog_posts WHERE id = :id AND is_published = 1", ['id' => $data['object_id']]);
                if ($post) {
                    $data['title'] = $post['title'];
                    // Blog posts use blog-post.php?slug=...
                    $data['url'] = url('blog-post.php?slug=' . urlencode($post['slug']));
                } else {
                    echo json_encode(['success' => false, 'error' => 'Blog post not found']);
                    exit;
                }
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'error' => 'Blog posts table not available']);
                exit;
            }
        } elseif ($data['type'] === 'post_category') {
            if (empty($_POST['category_name'])) {
                echo json_encode(['success' => false, 'error' => 'Please select a blog category']);
                exit;
            }
            $categoryName = trim($_POST['category_name']);
            try {
                // Verify category exists
                $categoryExists = db()->fetchOne("SELECT DISTINCT category FROM blog_posts WHERE category = :cat AND is_published = 1 LIMIT 1", ['cat' => $categoryName]);
                if ($categoryExists) {
                    $data['title'] = $categoryName;
                    $data['url'] = url('blog.php?category=' . urlencode($categoryName));
                    $data['object_id'] = null; // Post categories don't have object_id
                } else {
                    echo json_encode(['success' => false, 'error' => 'Blog category not found']);
                    exit;
                }
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'error' => 'Blog posts table not available']);
                exit;
            }
        } elseif ($data['type'] === 'custom') {
            // URL is optional for custom links, default to #
            if (empty($data['url'])) {
                $data['url'] = '#';
            }
        }
        
        $itemId = $itemModel->create($data);
        echo json_encode(['success' => $itemId > 0, 'item_id' => $itemId]);
        exit;
    }
}

// Get menu if editing (after AJAX handling, but before menu save handling)
if ($menuId > 0) {
    $menu = $menuModel->getById($menuId);
    if (!$menu) {
        header('Location: ' . url('admin/menus.php'));
        exit;
    }
    $isEdit = true;
}

// Handle menu save (only if not AJAX request)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['menu_name']) && empty($_POST['action'])) {
    try {
        $data = [
            'name' => trim($_POST['menu_name']),
            'slug' => trim($_POST['menu_slug'] ?? ''),
            'description' => trim($_POST['menu_description'] ?? '')
        ];
        
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name'])));
        }
        
        if (empty($data['name'])) {
            $error = 'Menu name is required.';
        } else {
            if ($isEdit) {
                $menuModel->update($menuId, $data);
                $message = 'Menu updated successfully.';
                $menu = $menuModel->getById($menuId);
            } else {
                $menuId = $menuModel->create($data);
                if ($menuId) {
                    $message = 'Menu created successfully.';
                    $menu = $menuModel->getById($menuId);
                    $isEdit = true;
                } else {
                    $error = 'Failed to create menu.';
                }
            }
        }
    } catch (\Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Get menu items
$items = [];
if ($isEdit) {
    $items = $itemModel->getByMenuId($menuId);
}

// Get data for dropdowns
$categories = $categoryModel->getAll(true);
$products = $productModel->getAll(true, 100);

// Get pages (if pages table exists)
$pages = [];
try {
    db()->fetchOne("SELECT 1 FROM pages LIMIT 1");
    $pages = db()->fetchAll("SELECT id, title, slug FROM pages WHERE is_active = 1 ORDER BY title ASC");
} catch (\Exception $e) {
    // Pages table doesn't exist yet
}

// Get blog posts (if blog_posts table exists)
$posts = [];
$postCategories = [];
try {
    db()->fetchOne("SELECT 1 FROM blog_posts LIMIT 1");
    $posts = db()->fetchAll("SELECT id, title, slug, category FROM blog_posts WHERE is_published = 1 ORDER BY title ASC");
    
    // Get unique post categories
    $postCategoryData = db()->fetchAll("SELECT DISTINCT category FROM blog_posts WHERE category IS NOT NULL AND category != '' AND is_published = 1 ORDER BY category ASC");
    foreach ($postCategoryData as $pc) {
        if (!empty($pc['category'])) {
            $postCategories[] = $pc['category'];
        }
    }
} catch (\Exception $e) {
    // Blog posts table doesn't exist yet
}

$pageTitle = $isEdit ? 'Edit Menu' : 'Add Menu';
include __DIR__ . '/includes/header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.css">
<style>
.menu-item {
    cursor: move;
    transition: all 0.2s;
}
.menu-item:hover {
    background: #f3f4f6;
}
.menu-item.dragging {
    opacity: 0.5;
}
.menu-item-child {
    margin-left: 2rem;
    border-left: 2px solid #e5e7eb;
    padding-left: 1rem;
}
.sortable-ghost {
    opacity: 0.4;
    background: #dbeafe;
}
</style>

<div class="w-full p-4 md:p-6">
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl shadow-xl p-4 md:p-6 mb-6 text-white">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-2">
                    <i class="fas fa-<?= $isEdit ? 'edit' : 'plus' ?> mr-2"></i>
                    <?= $isEdit ? 'Edit Menu' : 'Add Menu' ?>
                </h1>
                <p class="text-blue-100"><?= $isEdit ? 'Manage menu items' : 'Create a new menu' ?></p>
            </div>
            <div class="flex gap-3">
                <a href="<?= url('admin/menus.php') ?>" class="bg-white/20 text-white px-4 py-2 rounded-lg font-semibold hover:bg-white/30 transition-all">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
            </div>
        </div>
    </div>

    <?php if (!empty($message)): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6">
        <i class="fas fa-check-circle mr-2"></i><?= escape($message) ?>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6">
        <i class="fas fa-exclamation-circle mr-2"></i><?= escape($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" id="menuForm" class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Menu Name *</label>
                <input type="text" name="menu_name" required value="<?= escape($menu['name'] ?? '') ?>"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Menu Slug</label>
                <input type="text" name="menu_slug" value="<?= escape($menu['slug'] ?? '') ?>"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <div class="mt-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
            <textarea name="menu_description" rows="2"
                      class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"><?= escape($menu['description'] ?? '') ?></textarea>
        </div>
        <div class="mt-6 flex justify-end">
            <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-all">
                <i class="fas fa-save mr-2"></i><?= $isEdit ? 'Update Menu' : 'Create Menu' ?>
            </button>
        </div>
    </form>

    <?php if ($isEdit): ?>
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800">Menu Items</h2>
            <button onclick="showAddItemModal()" class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg">
                <i class="fas fa-plus mr-2"></i>Add Menu Item
            </button>
        </div>

        <div id="menuItemsList" class="space-y-2">
            <?php if (empty($items)): ?>
                <p class="text-gray-500 text-center py-8">No menu items. Add your first item above.</p>
            <?php else: ?>
                <?php
                function renderMenuItems($items, $parentId = null) {
                    $children = array_filter($items, function($item) use ($parentId) {
                        $itemParentId = $item['parent_id'] ?? null;
                        return ($itemParentId === null && $parentId === null) || 
                               ($itemParentId !== null && (int)$itemParentId === (int)$parentId);
                    });
                    
                    if (empty($children)) return '';
                    
                    $html = '';
                    foreach ($children as $item) {
                        $itemChildren = renderMenuItems($items, $item['id']);
                        $hasChildren = !empty($itemChildren);
                        $indent = $parentId ? 'menu-item-child' : '';
                        $parentIdValue = $item['parent_id'] ?? '';
                        
                        $html .= '<div class="menu-item ' . $indent . ' bg-white border border-gray-200 rounded-lg p-4 flex items-center justify-between mb-2" data-item-id="' . $item['id'] . '" data-parent-id="' . $parentIdValue . '">';
                        $html .= '<div class="flex items-center gap-3 flex-1">';
                        $html .= '<i class="fas fa-grip-vertical text-gray-400 cursor-move hover:text-gray-600"></i>';
                        if ($item['icon']) {
                            $html .= '<i class="' . escape($item['icon']) . ' text-blue-600"></i>';
                        }
                        $html .= '<div class="flex-1">';
                        $html .= '<div class="font-semibold text-gray-900">' . escape($item['title']) . '</div>';
                        $html .= '<div class="text-sm text-gray-500 truncate">';
                        $html .= '<span class="inline-block px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs mr-2">' . escape($item['type']) . '</span>';
                        $html .= escape($item['url'] ?? '');
                        $html .= '</div>';
                        $html .= '</div>';
                        $html .= '</div>';
                        $html .= '<div class="flex items-center gap-2">';
                        $html .= '<button onclick="editItem(' . $item['id'] . ')" class="bg-blue-100 hover:bg-blue-200 text-blue-700 p-2 rounded transition-all" title="Edit">';
                        $html .= '<i class="fas fa-edit text-sm"></i>';
                        $html .= '</button>';
                        $html .= '<button onclick="deleteItem(' . $item['id'] . ')" class="bg-red-100 hover:bg-red-200 text-red-700 p-2 rounded transition-all" title="Delete">';
                        $html .= '<i class="fas fa-trash text-sm"></i>';
                        $html .= '</button>';
                        $html .= '</div>';
                        $html .= '</div>';
                        if ($hasChildren) {
                            $html .= '<div class="ml-8 mt-2 space-y-2 border-l-2 border-gray-200 pl-4" data-parent="' . $item['id'] . '">' . $itemChildren . '</div>';
                        }
                    }
                    return $html;
                }
                echo renderMenuItems($items, null);
                ?>
            <?php endif; ?>
        </div>

        <div class="mt-6 flex justify-end">
            <button onclick="saveMenuOrder()" class="bg-green-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-green-700 transition-all">
                <i class="fas fa-save mr-2"></i>Save Menu Order
            </button>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Add Item Modal -->
<div id="addItemModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-bold">Add Menu Item</h3>
                <button onclick="closeAddItemModal()" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        <form id="addItemForm" class="p-6">
            <input type="hidden" name="menu_id" value="<?= $menuId ?>">
            <input type="hidden" name="action" value="add_item">
            <input type="hidden" name="item_type" id="item_type" value="custom">
            
            <!-- Item Type Selection -->
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-3">
                    <i class="fas fa-tag mr-2 text-blue-600"></i>Item Type *
                </label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <button type="button" onclick="selectItemType('custom')" class="item-type-btn bg-blue-50 hover:bg-blue-100 border-2 border-blue-200 rounded-lg p-4 text-center transition-all" data-type="custom">
                        <i class="fas fa-link text-blue-600 text-2xl mb-2"></i>
                        <div class="font-semibold text-gray-800">Custom Link</div>
                        <div class="text-xs text-gray-500 mt-1">Any URL</div>
                    </button>
                    <button type="button" onclick="selectItemType('category')" class="item-type-btn bg-indigo-50 hover:bg-indigo-100 border-2 border-indigo-200 rounded-lg p-4 text-center transition-all" data-type="category">
                        <i class="fas fa-folder text-indigo-600 text-2xl mb-2"></i>
                        <div class="font-semibold text-gray-800">Category</div>
                        <div class="text-xs text-gray-500 mt-1">Product Category</div>
                    </button>
                    <button type="button" onclick="selectItemType('product')" class="item-type-btn bg-purple-50 hover:bg-purple-100 border-2 border-purple-200 rounded-lg p-4 text-center transition-all" data-type="product">
                        <i class="fas fa-box text-purple-600 text-2xl mb-2"></i>
                        <div class="font-semibold text-gray-800">Product</div>
                        <div class="text-xs text-gray-500 mt-1">Single Product</div>
                    </button>
                    <?php if (!empty($pages)): ?>
                    <button type="button" onclick="selectItemType('page')" class="item-type-btn bg-green-50 hover:bg-green-100 border-2 border-green-200 rounded-lg p-4 text-center transition-all" data-type="page">
                        <i class="fas fa-file-alt text-green-600 text-2xl mb-2"></i>
                        <div class="font-semibold text-gray-800">Page</div>
                        <div class="text-xs text-gray-500 mt-1">CMS Page</div>
                    </button>
                    <?php endif; ?>
                    <?php if (!empty($posts)): ?>
                    <button type="button" onclick="selectItemType('post')" class="item-type-btn bg-orange-50 hover:bg-orange-100 border-2 border-orange-200 rounded-lg p-4 text-center transition-all" data-type="post">
                        <i class="fas fa-newspaper text-orange-600 text-2xl mb-2"></i>
                        <div class="font-semibold text-gray-800">Blog Post</div>
                        <div class="text-xs text-gray-500 mt-1">Single Post</div>
                    </button>
                    <?php endif; ?>
                    <?php if (!empty($postCategories)): ?>
                    <button type="button" onclick="selectItemType('post_category')" class="item-type-btn bg-yellow-50 hover:bg-yellow-100 border-2 border-yellow-200 rounded-lg p-4 text-center transition-all" data-type="post_category">
                        <i class="fas fa-tags text-yellow-600 text-2xl mb-2"></i>
                        <div class="font-semibold text-gray-800">Blog Category</div>
                        <div class="text-xs text-gray-500 mt-1">Post Category</div>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Dynamic Fields Based on Type -->
            <div id="itemTypeFields">
                <!-- Custom Link Fields -->
                <div id="customLinkFields" class="item-type-fields">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-heading mr-2 text-gray-400"></i>Title *
                        </label>
                        <input type="text" name="title" required class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Menu Item Title">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-link mr-2 text-gray-400"></i>URL
                        </label>
                        <input type="text" name="url" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="/ or /page.php">
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Leave empty to use # as URL
                        </p>
                    </div>
                </div>
                
                <!-- Category Fields -->
                <div id="categoryFields" class="item-type-fields hidden">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-folder mr-2 text-indigo-600"></i>Select Category *
                        </label>
                        <select name="object_id" id="category_object_id" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Select Category --</option>
                            <?php 
                            $categoryTree = $categoryModel->getFlatTree(null, true);
                            foreach ($categoryTree as $cat): 
                                $indent = str_repeat('&nbsp;&nbsp;&nbsp;', $cat['level'] ?? 0);
                                $prefix = ($cat['level'] ?? 0) > 0 ? '└─ ' : '';
                            ?>
                                <option value="<?= $cat['id'] ?>"><?= $indent . $prefix . escape($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Select a product category to link to
                        </p>
                    </div>
                </div>
                
                <!-- Product Fields -->
                <div id="productFields" class="item-type-fields hidden">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-box mr-2 text-purple-600"></i>Select Product *
                        </label>
                        <select name="object_id" id="product_object_id" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">-- Select Product --</option>
                            <?php foreach ($products as $prod): ?>
                                <option value="<?= $prod['id'] ?>"><?= escape($prod['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Select a product to link to
                        </p>
                    </div>
                </div>
                
                <!-- Page Fields -->
                <?php if (!empty($pages)): ?>
                <div id="pageFields" class="item-type-fields hidden">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-file-alt mr-2 text-green-600"></i>Select Page *
                        </label>
                        <select name="object_id" id="page_object_id" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">-- Select Page --</option>
                            <?php foreach ($pages as $page): ?>
                                <option value="<?= $page['id'] ?>"><?= escape($page['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Select a CMS page to link to
                        </p>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Post Fields -->
                <?php if (!empty($posts)): ?>
                <div id="postFields" class="item-type-fields hidden">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-newspaper mr-2 text-orange-600"></i>Select Blog Post *
                        </label>
                        <select name="object_id" id="post_object_id" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                            <option value="">-- Select Blog Post --</option>
                            <?php foreach ($posts as $post): ?>
                                <option value="<?= $post['id'] ?>"><?= escape($post['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Select a blog post to link to
                        </p>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Post Category Fields -->
                <?php if (!empty($postCategories)): ?>
                <div id="postCategoryFields" class="item-type-fields hidden">
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-tags mr-2 text-yellow-600"></i>Select Blog Category *
                        </label>
                        <select name="category_name" id="post_category_name" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                            <option value="">-- Select Blog Category --</option>
                            <?php foreach ($postCategories as $postCat): ?>
                                <option value="<?= escape($postCat) ?>"><?= escape($postCat) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>
                            Select a blog post category to link to
                        </p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Common Fields (shown for all types) -->
            <div class="border-t pt-4 mt-4">
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-icons mr-2 text-gray-400"></i>Icon (Font Awesome class)
                    </label>
                    <input type="text" name="icon" placeholder="fas fa-home" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Optional: e.g., "fas fa-home", "fas fa-user"
                    </p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-code mr-2 text-gray-400"></i>CSS Classes
                    </label>
                    <input type="text" name="css_classes" placeholder="custom-class another-class" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Optional: Add custom CSS classes for styling
                    </p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-external-link-alt mr-2 text-gray-400"></i>Link Target
                    </label>
                    <select name="target" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="_self">Same Window (_self)</option>
                        <option value="_blank">New Window/Tab (_blank)</option>
                    </select>
                </div>
            </div>
            
            <div class="flex justify-end gap-3 pt-4 border-t mt-4">
                <button type="button" onclick="closeAddItemModal()" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-300 transition-all">
                    <i class="fas fa-times mr-2"></i>Cancel
                </button>
                <button type="submit" class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg">
                    <i class="fas fa-plus mr-2"></i>Add Item
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.item-type-btn {
    cursor: pointer;
    transition: all 0.2s;
}

.item-type-btn.active {
    background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
    border-color: #3b82f6;
    color: white;
}

.item-type-btn.active i,
.item-type-btn.active .font-semibold,
.item-type-btn.active .text-xs {
    color: white !important;
}

.item-type-fields {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
let sortableInstance = null;

document.addEventListener('DOMContentLoaded', function() {
    const list = document.getElementById('menuItemsList');
    if (list && typeof Sortable !== 'undefined') {
        sortableInstance = new Sortable(list, {
            handle: '.fa-grip-vertical',
            animation: 150,
            ghostClass: 'sortable-ghost',
            group: 'menu-items',
            draggable: '.menu-item',
            onEnd: function(evt) {
                // Visual feedback
                console.log('Menu item moved');
            }
        });
    } else {
        console.error('SortableJS not loaded or menuItemsList not found');
    }
});

function showAddItemModal(type = null) {
    const form = document.getElementById('addItemForm');
    if (!form) return;
    
    // Reset form
    form.reset();
    
    // Reset all item type buttons
    document.querySelectorAll('.item-type-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Hide all type-specific fields
    document.querySelectorAll('.item-type-fields').forEach(field => {
        field.classList.add('hidden');
    });
    
    // Remove required attributes from all inputs
    form.querySelectorAll('input[required], select[required]').forEach(input => {
        input.removeAttribute('required');
    });
    
    // If type is provided, select it; otherwise default to custom
    const selectedType = type || 'custom';
    selectItemType(selectedType);
    
    document.getElementById('addItemModal').classList.remove('hidden');
}

function selectItemType(type) {
    const form = document.getElementById('addItemForm');
    if (!form) return;
    
    // Set the item type
    document.getElementById('item_type').value = type;
    
    // Update button states
    document.querySelectorAll('.item-type-btn').forEach(btn => {
        if (btn.getAttribute('data-type') === type) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    
    // Hide all fields
    document.querySelectorAll('.item-type-fields').forEach(field => {
        field.classList.add('hidden');
    });
    
    // Remove required from all inputs
    form.querySelectorAll('input, select').forEach(input => {
        input.removeAttribute('required');
    });
    
    // Show relevant fields based on type
    let fieldsToShow = [];
    let requiredFields = [];
    
    switch(type) {
        case 'custom':
            fieldsToShow = ['customLinkFields'];
            requiredFields = ['title'];
            break;
        case 'category':
            fieldsToShow = ['categoryFields'];
            requiredFields = ['category_object_id'];
            break;
        case 'product':
            fieldsToShow = ['productFields'];
            requiredFields = ['product_object_id'];
            break;
        case 'page':
            fieldsToShow = ['pageFields'];
            requiredFields = ['page_object_id'];
            break;
        case 'post':
            fieldsToShow = ['postFields'];
            requiredFields = ['post_object_id'];
            break;
        case 'post_category':
            fieldsToShow = ['postCategoryFields'];
            requiredFields = ['post_category_name'];
            break;
    }
    
    // Show the relevant fields
    fieldsToShow.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.classList.remove('hidden');
        }
    });
    
    // Set required attributes
    requiredFields.forEach(fieldName => {
        const field = document.getElementById(fieldName) || form.querySelector(`[name="${fieldName}"]`);
        if (field) {
            field.setAttribute('required', 'required');
        }
    });
    
    // For custom type, also require title input
    if (type === 'custom') {
        const titleInput = form.querySelector('#customLinkFields input[name="title"]');
        if (titleInput) {
            titleInput.setAttribute('required', 'required');
        }
    }
}

function closeAddItemModal() {
    document.getElementById('addItemModal').classList.add('hidden');
    document.getElementById('addItemForm').reset();
}

const addItemForm = document.getElementById('addItemForm');
if (addItemForm) {
    addItemForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validate form based on item type
        const itemType = document.getElementById('item_type').value;
        let validationError = '';
        
        if (itemType === 'custom') {
            const title = this.querySelector('input[name="title"]')?.value.trim();
            if (!title) {
                validationError = 'Title is required for custom links';
            }
        } else if (itemType === 'category') {
            const objectId = document.getElementById('category_object_id')?.value;
            if (!objectId) {
                validationError = 'Please select a category';
            }
        } else if (itemType === 'product') {
            const objectId = document.getElementById('product_object_id')?.value;
            if (!objectId) {
                validationError = 'Please select a product';
            }
        } else if (itemType === 'page') {
            const objectId = document.getElementById('page_object_id')?.value;
            if (!objectId) {
                validationError = 'Please select a page';
            }
        } else if (itemType === 'post') {
            const objectId = document.getElementById('post_object_id')?.value;
            if (!objectId) {
                validationError = 'Please select a blog post';
            }
        } else if (itemType === 'post_category') {
            const categoryName = document.getElementById('post_category_name')?.value;
            if (!categoryName) {
                validationError = 'Please select a blog category';
            }
        }
        
        if (validationError) {
            alert(validationError);
            return;
        }
        
        const formData = new FormData(this);
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Adding...';
        
        try {
            const response = await fetch('<?= url("admin/menu-edit.php?id={$menuId}") ?>', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const result = await response.json();
            if (result.success) {
                closeAddItemModal();
                location.reload();
            } else {
                alert('Error adding item: ' + (result.error || 'Unknown error'));
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (error) {
            alert('Error: ' + error.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

function updateMenuStructure() {
    // Update parent relationships based on DOM structure
    const list = document.getElementById('menuItemsList');
    if (!list) return {};
    
    const orders = {};
    let orderIndex = 0;
    
    // Get all menu items in DOM order
    const allItems = list.querySelectorAll('.menu-item');
    
    allItems.forEach((item) => {
        const itemId = item.getAttribute('data-item-id');
        if (!itemId) return;
        
        // Find parent by checking DOM hierarchy
        let parentId = null;
        let parent = item.parentElement;
        
        // Look for parent menu item
        while (parent && parent !== list) {
            const parentItem = parent.querySelector('.menu-item[data-item-id]');
            if (parentItem && parentItem !== item) {
                // Check if this item is nested under parentItem
                const childContainer = parentItem.nextElementSibling;
                if (childContainer && childContainer.contains(item)) {
                    parentId = parentItem.getAttribute('data-item-id');
                    break;
                }
            }
            parent = parent.parentElement;
        }
        
        orderIndex++;
        orders[itemId] = {
            order: orderIndex,
            parent_id: parentId || null
        };
    });
    
    return orders;
}

function saveMenuOrder() {
    const orders = updateMenuStructure();
    
    if (Object.keys(orders).length === 0) {
        alert('No items to save');
        return;
    }
    
    const saveBtn = document.querySelector('button[onclick="saveMenuOrder()"]');
    if (saveBtn) {
        saveBtn.disabled = true;
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
        
        fetch('<?= url("admin/menu-edit.php?id={$menuId}") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'save_order',
                menu_id: <?= $menuId ?>,
                orders: orders
            })
        }).then(res => {
            if (!res.ok) {
                throw new Error('Network response was not ok');
            }
            return res.json();
        }).then(data => {
            if (data.success) {
                alert('Menu order saved!');
                location.reload();
            } else {
                alert('Error saving order: ' + (data.error || 'Unknown error'));
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalText;
            }
        }).catch(error => {
            alert('Error: ' + error.message);
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        });
    }
}

function editItem(id) {
    // TODO: Implement edit modal
    // For now, redirect to edit page or show alert
    if (confirm('Edit menu item? This will reload the page.')) {
        // Could implement inline editing modal here
        alert('Edit functionality coming soon. For now, delete and recreate the item.');
    }
}

function deleteItem(id) {
    if (!confirm('Delete this menu item? This will also delete all sub-items.')) {
        return;
    }
    
    fetch('<?= url("admin/menu-edit.php?id={$menuId}") ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'delete_item',
            item_id: id
        })
    }).then(res => {
        if (!res.ok) {
            throw new Error('Network response was not ok');
        }
        return res.json();
    }).then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error deleting item: ' + (data.error || 'Unknown error'));
        }
    }).catch(error => {
        alert('Error: ' + error.message);
    });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
