<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

use App\Models\Category;

$categoryModel = new Category();
$message = '';
$error = '';
$category = null;
$categoryId = $_GET['id'] ?? null;

if ($categoryId) {
    $category = $categoryModel->getById($categoryId);
    if (!$category) {
        header('Location: ' . url('admin/categories.php'));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'slug' => strtolower(trim(preg_replace('/[^a-z0-9-]+/', '-', $_POST['slug'] ?? ''), '-')),
        'description' => trim($_POST['description'] ?? ''),
        'image' => trim($_POST['image'] ?? ''),
        'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
        'sort_order' => (int)($_POST['sort_order'] ?? 0),
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
    ];
    
    if (empty($data['name'])) {
        $error = 'Category name is required.';
    } else {
        try {
            if ($categoryId) {
                db()->update('categories', $data, 'id = :id', ['id' => $categoryId]);
                $message = 'Category updated successfully.';
            } else {
                db()->insert('categories', $data);
                $message = 'Category created successfully.';
                header('Location: ' . url('admin/categories.php'));
                exit;
            }
            $category = $categoryModel->getById($categoryId);
        } catch (Exception $e) {
            $error = 'Error saving category: ' . $e->getMessage();
        }
    }
}

$categories = $categoryModel->getAll(false);

$pageTitle = $category ? 'Edit Category' : 'Add New Category';
include __DIR__ . '/includes/header.php';
?>

<h1 class="text-3xl font-bold mb-6"><?= $category ? 'Edit Category' : 'Add New Category' ?></h1>

<form method="POST" class="bg-white rounded-lg shadow p-6 space-y-6 max-w-2xl">
    <div>
        <label class="block text-sm font-medium mb-2">Category Name *</label>
        <input type="text" name="name" required value="<?= escape($category['name'] ?? '') ?>"
               class="w-full px-4 py-2 border rounded-lg">
    </div>
    
    <div>
        <label class="block text-sm font-medium mb-2">Slug</label>
        <input type="text" name="slug" value="<?= escape($category['slug'] ?? '') ?>"
               class="w-full px-4 py-2 border rounded-lg">
    </div>
    
    <div>
        <label class="block text-sm font-medium mb-2">Description</label>
        <textarea name="description" rows="4" class="w-full px-4 py-2 border rounded-lg"><?= escape($category['description'] ?? '') ?></textarea>
    </div>
    
    <div class="grid md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium mb-2">Parent Category</label>
            <select name="parent_id" class="w-full px-4 py-2 border rounded-lg">
                <option value="">None (Top Level)</option>
                <?php foreach ($categories as $cat): ?>
                    <?php if (!$categoryId || $cat['id'] != $categoryId): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($category['parent_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                            <?= escape($cat['name']) ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium mb-2">Sort Order</label>
            <input type="number" name="sort_order" value="<?= escape($category['sort_order'] ?? 0) ?>"
                   class="w-full px-4 py-2 border rounded-lg">
        </div>
    </div>
    
    <div>
        <label class="flex items-center">
            <input type="checkbox" name="is_active" <?= ($category['is_active'] ?? 1) ? 'checked' : '' ?>>
            <span class="ml-2">Active</span>
        </label>
    </div>
    
    <div class="flex space-x-4">
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
            <?= $category ? 'Update Category' : 'Create Category' ?>
        </button>
        <a href="<?= url('admin/categories.php') ?>" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
            Cancel
        </a>
    </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>

