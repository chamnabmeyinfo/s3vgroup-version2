<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

use App\Models\Product;
use App\Models\Category;

$productModel = new Product();
$categoryModel = new Category();

$message = '';
$error = '';
$product = null;
$productId = $_GET['id'] ?? null;

if ($productId) {
    $product = $productModel->getById($productId);
    if (!$product) {
        header('Location: ' . url('admin/products.php'));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'slug' => strtolower(trim(preg_replace('/[^a-z0-9-]+/', '-', $_POST['slug'] ?? ''), '-')),
        'sku' => trim($_POST['sku'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'short_description' => trim($_POST['short_description'] ?? ''),
        'price' => floatval($_POST['price'] ?? 0),
        'sale_price' => !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : null,
        'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
        'image' => trim($_POST['image'] ?? ''),
        'gallery' => !empty($_POST['gallery']) ? $_POST['gallery'] : null, // Already JSON encoded from hidden input
        'specifications' => !empty($_POST['specifications']) ? json_encode(json_decode($_POST['specifications'], true)) : null,
        'features' => trim($_POST['features'] ?? ''),
        'stock_status' => $_POST['stock_status'] ?? 'in_stock',
        'weight' => !empty($_POST['weight']) ? floatval($_POST['weight']) : null,
        'dimensions' => trim($_POST['dimensions'] ?? ''),
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'meta_title' => trim($_POST['meta_title'] ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
    ];
    
    if (empty($data['name'])) {
        $error = 'Product name is required.';
    } else {
        try {
            if ($productId) {
                $productModel->update($productId, $data);
                $message = 'Product updated successfully.';
            } else {
                $productId = $productModel->create($data);
                $message = 'Product created successfully.';
                header('Location: ' . url('admin/product-edit.php?id=' . $productId));
                exit;
            }
            $product = $productModel->getById($productId);
        } catch (Exception $e) {
            $error = 'Error saving product: ' . $e->getMessage();
        }
    }
}

$categories = $categoryModel->getAll();
$gallery = [];
if ($product && !empty($product['gallery'])) {
    $gallery = json_decode($product['gallery'], true) ?? [];
}

$pageTitle = $product ? 'Edit Product' : 'Add New Product';
include __DIR__ . '/includes/header.php';
?>

<h1 class="text-3xl font-bold mb-6"><?= $product ? 'Edit Product' : 'Add New Product' ?></h1>

<form method="POST" class="bg-white rounded-lg shadow p-6 space-y-6">
    <div class="grid md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium mb-2">Product Name *</label>
            <input type="text" name="name" required value="<?= escape($product['name'] ?? '') ?>"
                   class="w-full px-4 py-2 border rounded-lg">
        </div>
        
        <div>
            <label class="block text-sm font-medium mb-2">Slug</label>
            <input type="text" name="slug" value="<?= escape($product['slug'] ?? '') ?>"
                   class="w-full px-4 py-2 border rounded-lg">
        </div>
    </div>
    
    <div class="grid md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-medium mb-2">SKU</label>
            <input type="text" name="sku" value="<?= escape($product['sku'] ?? '') ?>"
                   class="w-full px-4 py-2 border rounded-lg">
        </div>
        
        <div>
            <label class="block text-sm font-medium mb-2">Category</label>
            <select name="category_id" class="w-full px-4 py-2 border rounded-lg">
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                        <?= escape($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    
    <div>
        <label class="block text-sm font-medium mb-2">Short Description</label>
        <textarea name="short_description" rows="2" class="w-full px-4 py-2 border rounded-lg"><?= escape($product['short_description'] ?? '') ?></textarea>
    </div>
    
    <div>
        <label class="block text-sm font-medium mb-2">Description</label>
        <textarea name="description" rows="6" class="w-full px-4 py-2 border rounded-lg"><?= escape($product['description'] ?? '') ?></textarea>
    </div>
    
    <div class="grid md:grid-cols-3 gap-6">
        <div>
            <label class="block text-sm font-medium mb-2">Price *</label>
            <input type="number" step="0.01" name="price" required value="<?= escape($product['price'] ?? '0') ?>"
                   class="w-full px-4 py-2 border rounded-lg">
        </div>
        
        <div>
            <label class="block text-sm font-medium mb-2">Sale Price</label>
            <input type="number" step="0.01" name="sale_price" value="<?= escape($product['sale_price'] ?? '') ?>"
                   class="w-full px-4 py-2 border rounded-lg">
        </div>
        
        <div>
            <label class="block text-sm font-medium mb-2">Stock Status</label>
            <select name="stock_status" class="w-full px-4 py-2 border rounded-lg">
                <option value="in_stock" <?= ($product['stock_status'] ?? '') == 'in_stock' ? 'selected' : '' ?>>In Stock</option>
                <option value="out_of_stock" <?= ($product['stock_status'] ?? '') == 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                <option value="on_order" <?= ($product['stock_status'] ?? '') == 'on_order' ? 'selected' : '' ?>>On Order</option>
            </select>
        </div>
    </div>
    
    <div>
        <label class="block text-sm font-medium mb-2">Product Image</label>
        <div class="flex gap-4 items-start">
            <input type="text" id="imageInput" name="image" value="<?= escape($product['image'] ?? '') ?>"
                   placeholder="image.jpg"
                   class="flex-1 px-4 py-2 border rounded-lg">
            <a href="<?= url('admin/images.php') ?>" target="_blank" 
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                <i class="fas fa-upload mr-2"></i> Browse Images
            </a>
        </div>
        <p class="text-sm text-gray-600 mt-1">
            Enter image filename or <a href="<?= url('admin/images.php') ?>" target="_blank" class="text-blue-600 underline">upload a new image</a>
        </p>
        <?php if (!empty($product['image'])): ?>
            <div class="mt-2">
                <img src="<?= asset('storage/uploads/' . escape($product['image'])) ?>" 
                     alt="Current image" 
                     class="h-32 w-32 object-cover rounded border">
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ($productId): ?>
    <?php
    $gallery = [];
    if (!empty($product['gallery'])) {
        $gallery = json_decode($product['gallery'], true) ?? [];
    }
    ?>
    <div>
        <label class="block text-sm font-medium mb-2">Product Gallery</label>
        <div id="galleryContainer" class="grid grid-cols-4 gap-4 mb-4">
            <?php foreach ($gallery as $index => $img): ?>
            <div class="relative border rounded p-2" data-image="<?= escape($img) ?>">
                <img src="<?= asset('storage/uploads/' . escape($img)) ?>" alt="" class="w-full h-24 object-cover rounded">
                <button type="button" onclick="removeFromGallery('<?= escape($img) ?>')" 
                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                    ×
                </button>
                <span class="text-xs text-gray-500 block mt-1 truncate"><?= escape($img) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="flex gap-2">
            <input type="text" id="galleryImageInput" placeholder="Enter image filename" 
                   class="flex-1 px-4 py-2 border rounded-lg">
            <button type="button" onclick="addToGallery()" 
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Add to Gallery
            </button>
            <a href="<?= url('admin/images.php') ?>" target="_blank" 
               class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                Browse Images
            </a>
        </div>
        <input type="hidden" id="galleryInput" name="gallery" value="<?= escape(json_encode($gallery)) ?>">
    </div>
    <?php endif; ?>
    
    <div class="flex items-center space-x-4">
        <label class="flex items-center">
            <input type="checkbox" name="is_featured" <?= ($product['is_featured'] ?? 0) ? 'checked' : '' ?>>
            <span class="ml-2">Featured Product</span>
        </label>
        
        <label class="flex items-center">
            <input type="checkbox" name="is_active" <?= ($product['is_active'] ?? 1) ? 'checked' : '' ?>>
            <span class="ml-2">Active</span>
        </label>
    </div>
    
    <div class="flex space-x-4">
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
            <?= $product ? 'Update Product' : 'Create Product' ?>
        </button>
        <a href="<?= url('admin/products.php') ?>" class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">
            Cancel
        </a>
    </div>
</form>

<script>
let gallery = <?= json_encode($gallery ?? []) ?>;

function addToGallery() {
    const input = document.getElementById('galleryImageInput');
    const filename = input.value.trim();
    
    if (!filename) {
        alert('Please enter an image filename.');
        return;
    }
    
    if (gallery.includes(filename)) {
        alert('Image already in gallery.');
        return;
    }
    
    gallery.push(filename);
    updateGalleryDisplay();
    input.value = '';
}

function removeFromGallery(filename) {
    gallery = gallery.filter(img => img !== filename);
    updateGalleryDisplay();
}

function updateGalleryDisplay() {
    const container = document.getElementById('galleryContainer');
    const hiddenInput = document.getElementById('galleryInput');
    
    if (!container) return;
    
    hiddenInput.value = JSON.stringify(gallery);
    
    if (gallery.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-sm">No images in gallery. Add images below.</p>';
        return;
    }
    
    container.innerHTML = gallery.map(img => `
        <div class="relative border rounded p-2" data-image="${img}">
            <img src="<?= asset('storage/uploads/') ?>${img}" alt="" class="w-full h-24 object-cover rounded" 
                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27200%27 height=%27200%27%3E%3Crect fill=%27%23ddd%27 width=%27200%27 height=%27200%27/%3E%3Ctext fill=%27%23999%27 x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27%3EImage%3C/text%3E%3C/svg%3E'">
            <button type="button" onclick="removeFromGallery('${img}')" 
                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                ×
            </button>
            <span class="text-xs text-gray-500 block mt-1 truncate">${img}</span>
        </div>
    `).join('');
}

// Allow Enter key to add to gallery
document.addEventListener('DOMContentLoaded', function() {
    const galleryInput = document.getElementById('galleryImageInput');
    if (galleryInput) {
        galleryInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addToGallery();
            }
        });
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

