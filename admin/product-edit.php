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
    $name = trim($_POST['name'] ?? '');
    
    // Generate slug from name if empty
    $slug = trim($_POST['slug'] ?? '');
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^a-z0-9-]+/', '-', $name), '-'));
    } else {
        $slug = strtolower(trim(preg_replace('/[^a-z0-9-]+/', '-', $slug), '-'));
    }
    
    // Ensure slug is not empty
    if (empty($slug)) {
        $slug = 'product-' . time();
    }
    
    // Check for duplicate slug and make it unique
    $originalSlug = $slug;
    $counter = 1;
    while (true) {
        $existing = db()->fetchOne(
            "SELECT id FROM products WHERE slug = :slug" . ($productId ? " AND id != :product_id" : ""),
            array_filter([
                'slug' => $slug,
                'product_id' => $productId ?? null
            ])
        );
        
        if (!$existing) {
            break; // Slug is unique
        }
        
        $slug = $originalSlug . '-' . $counter;
        $counter++;
        
        // Safety limit
        if ($counter > 1000) {
            $slug = $originalSlug . '-' . time();
            break;
        }
    }
    
    $data = [
        'name' => $name,
        'slug' => $slug,
        'sku' => !empty(trim($_POST['sku'] ?? '')) ? trim($_POST['sku']) : null,
        'description' => trim($_POST['description'] ?? ''),
        'short_description' => trim($_POST['short_description'] ?? ''),
        'price' => floatval($_POST['price'] ?? 0),
        'sale_price' => !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : null,
        'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
        'image' => trim($_POST['image'] ?? ''),
        'gallery' => !empty($_POST['gallery']) ? $_POST['gallery'] : null,
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
            $db = db();
            $db->getPdo()->beginTransaction();
            
            if ($productId) {
                // Get current product data
                $currentProduct = $productModel->getById($productId);
                if (!$currentProduct) {
                    throw new Exception('Product not found.');
                }
                
                // Check if slug changed and if new slug conflicts
                if ($currentProduct['slug'] !== $data['slug']) {
                    // Slug is being changed, check for conflicts
                    $conflict = db()->fetchOne(
                        "SELECT id FROM products WHERE slug = :slug AND id != :product_id",
                        ['slug' => $data['slug'], 'product_id' => $productId]
                    );
                    if ($conflict) {
                        throw new Exception('Slug "' . $data['slug'] . '" is already in use by another product.');
                    }
                }
                
                // Check if SKU changed and if new SKU conflicts
                // Get current SKU from database and normalize
                $currentSku = isset($currentProduct['sku']) ? trim($currentProduct['sku']) : '';
                $currentSku = ($currentSku === '') ? null : $currentSku;
                
                // Get new SKU from form data (already trimmed and normalized in $data array)
                $newSku = $data['sku']; // This is already normalized: null if empty, or trimmed string
                
                // Determine if SKU is actually changing
                // Compare both values (treating null and empty string as equivalent)
                $skuIsChanging = false;
                
                if ($currentSku === null && $newSku === null) {
                    // Both are null/empty - no change
                    $skuIsChanging = false;
                } elseif ($currentSku === null || $newSku === null) {
                    // One is null, one has value - definitely changing
                    $skuIsChanging = true;
                } else {
                    // Both have values - compare them (case-insensitive)
                    // Both are already trimmed from normalization
                    $skuIsChanging = (strcasecmp($currentSku, $newSku) !== 0);
                }
                
                // Only validate uniqueness if SKU is actually changing to a non-empty value
                if ($skuIsChanging && $newSku !== null && $newSku !== '') {
                    // SKU is being changed to a new non-empty value, check for conflicts
                    $skuConflict = db()->fetchOne(
                        "SELECT id FROM products WHERE sku = :sku AND id != :product_id",
                        ['sku' => $newSku, 'product_id' => $productId]
                    );
                    if ($skuConflict) {
                        throw new Exception('This SKU is already in use. Please choose a different SKU.');
                    }
                }
                
                $productModel->update($productId, $data);
                $message = 'Product updated successfully.';
            } else {
                // For new products, check if SKU already exists (only if SKU is provided and not empty)
                if (!empty($data['sku'])) {
                    $skuConflict = db()->fetchOne(
                        "SELECT id FROM products WHERE sku = :sku",
                        ['sku' => $data['sku']]
                    );
                    if ($skuConflict) {
                        throw new Exception('This SKU is already in use. Please choose a different SKU.');
                    }
                }
                
                $productId = $productModel->create($data);
                $message = 'Product created successfully.';
            }
            
            // Handle variants
            if ($productId && !empty($_POST['variants'])) {
                $variants = json_decode($_POST['variants'], true) ?? [];
                
                // Delete existing variants
                try {
                    $db->delete('product_variant_attributes', 'variant_id IN (SELECT id FROM product_variants WHERE product_id = :product_id)', ['product_id' => $productId]);
                    $db->delete('product_variants', 'product_id = :product_id', ['product_id' => $productId]);
                } catch (Exception $e) {
                    // Tables might not exist yet
                }
                
                // Insert new variants
                foreach ($variants as $variant) {
                    // Allow variants without attributes (they can be added later)
                    if (!isset($variant['attributes']) || !is_array($variant['attributes'])) {
                        $variant['attributes'] = [];
                    }
                    
                    $variantData = [
                        'product_id' => $productId,
                        'sku' => trim($variant['sku'] ?? ''),
                        'name' => trim($variant['name'] ?? ''),
                        'price' => floatval($variant['price'] ?? $data['price']),
                        'sale_price' => !empty($variant['sale_price']) ? floatval($variant['sale_price']) : null,
                        'stock_quantity' => intval($variant['stock_quantity'] ?? 0),
                        'stock_status' => $variant['stock_status'] ?? 'in_stock',
                        'image' => trim($variant['image'] ?? ''),
                        'weight' => !empty($variant['weight']) ? floatval($variant['weight']) : null,
                        'is_active' => isset($variant['is_active']) ? 1 : 1,
                        'sort_order' => intval($variant['sort_order'] ?? 0)
                    ];
                    
                    $variantId = $db->insert('product_variants', $variantData);
                    
                    // Insert variant attributes
                    foreach ($variant['attributes'] as $attrName => $attrValue) {
                        if (empty($attrName) || empty($attrValue)) continue;
                        
                        $db->insert('product_variant_attributes', [
                            'variant_id' => $variantId,
                            'attribute_name' => trim($attrName),
                            'attribute_value' => trim($attrValue)
                        ]);
                    }
                }
            }
            
            $db->getPdo()->commit();
            
            if (!$productId) {
                header('Location: ' . url('admin/product-edit.php?id=' . $productId));
                exit;
            }
            $product = $productModel->getById($productId);
        } catch (Exception $e) {
            if (isset($db)) {
                $db->getPdo()->rollBack();
            }
            // Provide user-friendly error messages
            $errorMessage = $e->getMessage();
            
            // Check if this is our custom SKU error (already user-friendly)
            if (strpos($errorMessage, 'This SKU is already in use') !== false) {
                $error = $errorMessage; // Use the message as-is
            } elseif (strpos($errorMessage, 'Duplicate entry') !== false) {
                // Database-level duplicate entry error
                if (strpos($errorMessage, 'slug') !== false) {
                    $error = 'This slug is already in use. The system tried to auto-generate a unique slug, but there was a conflict. Please enter a different slug manually.';
                } elseif (strpos($errorMessage, 'sku') !== false) {
                    // This shouldn't happen if our validation worked, but handle it anyway
                    $error = 'This SKU is already in use. Please choose a different SKU.';
                } else {
                    $error = 'A duplicate entry was detected. Please check your input and try again.';
                }
            } else {
                $error = 'Error saving product: ' . $errorMessage;
            }
        }
    }
}

$categories = $categoryModel->getAll();
$gallery = [];
if ($product && !empty($product['gallery'])) {
    $gallery = json_decode($product['gallery'], true) ?? [];
}

// Get product variants
$variants = [];
$variantAttributes = [];
if ($productId) {
    try {
        $variants = db()->fetchAll(
            "SELECT * FROM product_variants WHERE product_id = :product_id ORDER BY sort_order, id",
            ['product_id' => $productId]
        );
        
        if (!empty($variants)) {
            $variantIds = array_column($variants, 'id');
            $placeholders = implode(',', array_fill(0, count($variantIds), '?'));
            $attributes = db()->fetchAll(
                "SELECT * FROM product_variant_attributes WHERE variant_id IN ($placeholders)",
                $variantIds
            );
            
            foreach ($attributes as $attr) {
                if (!isset($variantAttributes[$attr['variant_id']])) {
                    $variantAttributes[$attr['variant_id']] = [];
                }
                $variantAttributes[$attr['variant_id']][$attr['attribute_name']] = $attr['attribute_value'];
            }
            
            // Add attributes to variants
            foreach ($variants as &$variant) {
                $variant['attributes'] = $variantAttributes[$variant['id']] ?? [];
            }
        }
    } catch (Exception $e) {
        // Variants table might not exist yet
        $variants = [];
    }
}

$pageTitle = $product ? 'Edit Product' : 'Add New Product';
include __DIR__ . '/includes/header.php';
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold"><?= $product ? 'Edit Product' : 'Add New Product' ?></h1>
        <?php if ($productId): ?>
        <a href="<?= url('product.php?slug=' . escape($product['slug'])) ?>" target="_blank" class="btn-secondary">
            <i class="fas fa-external-link-alt mr-2"></i> View Product
        </a>
        <?php endif; ?>
    </div>
    
    <?php if ($message): ?>
    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
        <?= escape($message) ?>
    </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
        <?= escape($error) ?>
    </div>
    <?php endif; ?>
    
    <form method="POST" id="productForm" class="bg-white rounded-lg shadow-md p-6">
        <!-- Tabs -->
        <div class="border-b mb-6">
            <nav class="flex space-x-8">
                <button type="button" onclick="switchTab('basic')" id="tab-basic" class="tab-button active py-4 px-1 border-b-2 border-blue-500 font-medium text-blue-600">
                    <i class="fas fa-info-circle mr-2"></i> Basic Information
                </button>
                <button type="button" onclick="switchTab('images')" id="tab-images" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-gray-500 hover:text-gray-700">
                    <i class="fas fa-images mr-2"></i> Images & Gallery
                </button>
                <button type="button" onclick="switchTab('details')" id="tab-details" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-gray-500 hover:text-gray-700">
                    <i class="fas fa-list mr-2"></i> Details & Specs
                </button>
                <button type="button" onclick="switchTab('variants')" id="tab-variants" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-gray-500 hover:text-gray-700">
                    <i class="fas fa-layer-group mr-2"></i> Variants
                </button>
                <button type="button" onclick="switchTab('seo')" id="tab-seo" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-gray-500 hover:text-gray-700">
                    <i class="fas fa-search mr-2"></i> SEO & Settings
                </button>
            </nav>
        </div>
        
        <!-- Tab Content: Basic Information -->
        <div id="tab-content-basic" class="tab-content">
            <div class="space-y-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2">Product Name *</label>
                        <input type="text" name="name" required value="<?= escape($product['name'] ?? '') ?>"
                               class="w-full px-4 py-2 border rounded-lg"
                               placeholder="Enter product name">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Slug</label>
                        <input type="text" name="slug" value="<?= escape($product['slug'] ?? '') ?>"
                               class="w-full px-4 py-2 border rounded-lg"
                               placeholder="product-slug"
                               id="product-slug-input">
                        <p class="text-xs text-gray-500 mt-1">
                            Leave empty to auto-generate from name. 
                            <span class="text-red-600">Slug must be unique.</span>
                        </p>
                        <button type="button" onclick="generateSlugFromName()" class="mt-1 text-xs text-blue-600 hover:text-blue-800">
                            <i class="fas fa-magic mr-1"></i> Generate from name
                        </button>
                    </div>
                </div>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2">SKU</label>
                        <input type="text" name="sku" value="<?= escape($product['sku'] ?? '') ?>"
                               class="w-full px-4 py-2 border rounded-lg"
                               placeholder="PROD-001">
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
                    <textarea name="short_description" rows="3" 
                              class="w-full px-4 py-2 border rounded-lg"
                              placeholder="Brief description for product listings..."><?= escape($product['short_description'] ?? '') ?></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Full Description</label>
                    <textarea name="description" rows="8" 
                              class="w-full px-4 py-2 border rounded-lg"
                              placeholder="Detailed product description..."><?= escape($product['description'] ?? '') ?></textarea>
                </div>
                
                <div class="grid md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2">Price *</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                            <input type="number" step="0.01" name="price" required value="<?= escape($product['price'] ?? '0') ?>"
                                   class="w-full pl-8 pr-4 py-2 border rounded-lg">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Sale Price</label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                            <input type="number" step="0.01" name="sale_price" value="<?= escape($product['sale_price'] ?? '') ?>"
                                   class="w-full pl-8 pr-4 py-2 border rounded-lg"
                                   placeholder="Optional">
                        </div>
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
            </div>
        </div>
        
        <!-- Tab Content: Images & Gallery -->
        <div id="tab-content-images" class="tab-content hidden">
            <div class="space-y-6">
                <!-- Main Product Image -->
                <div>
                    <label class="block text-sm font-medium mb-2">Main Product Image</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                        <div id="mainImageArea" class="text-center">
                            <?php if (!empty($product['image'])): ?>
                                <div class="relative inline-block">
                                    <img src="<?= asset('storage/uploads/' . escape($product['image'])) ?>" 
                                         alt="Main image" 
                                         id="mainImagePreview"
                                         class="max-w-xs max-h-64 object-contain rounded-lg border shadow-lg">
                                    <button type="button" onclick="removeMainImage()" 
                                            class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-red-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            <?php else: ?>
                                <div id="mainImagePlaceholder" class="py-12">
                                    <i class="fas fa-image text-6xl text-gray-300 mb-4"></i>
                                    <p class="text-gray-500 mb-4">No main image selected</p>
                                    <button type="button" onclick="openImageBrowser('main')" class="btn-primary">
                                        <i class="fas fa-upload mr-2"></i> Select or Upload Image
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" id="imageInput" name="image" value="<?= escape($product['image'] ?? '') ?>">
                        
                        <!-- Quick Upload -->
                        <div class="mt-4 border-t pt-4">
                            <p class="text-sm text-gray-600 mb-2">Quick Upload:</p>
                            <div class="flex gap-2">
                                <input type="file" id="mainImageUpload" accept="image/*" class="hidden" onchange="uploadMainImage(this.files[0])">
                                <button type="button" onclick="document.getElementById('mainImageUpload').click()" class="btn-secondary">
                                    <i class="fas fa-cloud-upload-alt mr-2"></i> Upload New Image
                                </button>
                                <button type="button" onclick="openImageBrowser('main')" class="btn-secondary">
                                    <i class="fas fa-folder-open mr-2"></i> Browse Existing
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Product Gallery -->
                <div>
                    <label class="block text-sm font-medium mb-2">Product Gallery</label>
                    <div class="border rounded-lg p-4">
                        <div id="galleryContainer" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-4 min-h-[100px]">
                            <?php if (empty($gallery)): ?>
                                <div class="col-span-full text-center py-8 text-gray-500">
                                    <i class="fas fa-images text-4xl mb-2"></i>
                                    <p>No gallery images. Add images below.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($gallery as $index => $img): ?>
                                <div class="relative group gallery-item" data-image="<?= escape($img) ?>" draggable="true">
                                    <img src="<?= asset('storage/uploads/' . escape($img)) ?>" 
                                         alt="" 
                                         class="w-full h-32 object-cover rounded border cursor-move"
                                         onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27200%27 height=%27200%27%3E%3Crect fill=%27%23ddd%27 width=%27200%27 height=%27200%27/%3E%3Ctext fill=%27%23999%27 x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27%3EBroken%3C/text%3E%3C/svg%3E'">
                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all flex items-center justify-center">
                                        <div class="text-white opacity-0 group-hover:opacity-100 flex gap-2">
                                            <button type="button" onclick="setAsMainImage('<?= escape($img) ?>')" 
                                                    class="bg-blue-500 hover:bg-blue-600 rounded px-2 py-1 text-xs" title="Set as Main">
                                                <i class="fas fa-star"></i>
                                            </button>
                                            <button type="button" onclick="removeFromGallery('<?= escape($img) ?>')" 
                                                    class="bg-red-500 hover:bg-red-600 rounded px-2 py-1 text-xs" title="Remove">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="absolute top-1 left-1 bg-gray-800 bg-opacity-75 text-white text-xs px-2 py-1 rounded">
                                        <?= $index + 1 ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="border-t pt-4">
                            <div class="flex gap-2 flex-wrap">
                                <input type="file" id="galleryUpload" accept="image/*" multiple class="hidden" onchange="uploadGalleryImages(this.files)">
                                <button type="button" onclick="document.getElementById('galleryUpload').click()" class="btn-secondary">
                                    <i class="fas fa-cloud-upload-alt mr-2"></i> Upload Images
                                </button>
                                <button type="button" onclick="openImageBrowser('gallery')" class="btn-secondary">
                                    <i class="fas fa-folder-open mr-2"></i> Browse Existing
                                </button>
                                <button type="button" onclick="clearGallery()" class="btn-secondary bg-red-600 hover:bg-red-700 text-white">
                                    <i class="fas fa-trash mr-2"></i> Clear Gallery
                                </button>
                            </div>
                        </div>
                        <input type="hidden" id="galleryInput" name="gallery" value="<?= escape(json_encode($gallery)) ?>">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tab Content: Details & Specs -->
        <div id="tab-content-details" class="tab-content hidden">
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Features</label>
                    <textarea name="features" rows="6" 
                              class="w-full px-4 py-2 border rounded-lg"
                              placeholder="Enter product features, one per line..."><?= escape($product['features'] ?? '') ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">Enter one feature per line</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Specifications (JSON)</label>
                    <textarea name="specifications" rows="8" 
                              class="w-full px-4 py-2 border rounded-lg font-mono text-sm"
                              placeholder='{"key": "value", "key2": "value2"}'><?= escape($product['specifications'] ? json_encode(json_decode($product['specifications'], true), JSON_PRETTY_PRINT) : '') ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">Enter specifications as JSON format</p>
                </div>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium mb-2">Weight (kg)</label>
                        <input type="number" step="0.01" name="weight" value="<?= escape($product['weight'] ?? '') ?>"
                               class="w-full px-4 py-2 border rounded-lg"
                               placeholder="0.00">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-2">Dimensions</label>
                        <input type="text" name="dimensions" value="<?= escape($product['dimensions'] ?? '') ?>"
                               class="w-full px-4 py-2 border rounded-lg"
                               placeholder="L x W x H">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tab Content: Variants -->
        <div id="tab-content-variants" class="tab-content hidden">
            <div class="space-y-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <h3 class="font-semibold text-blue-900 mb-2">
                        <i class="fas fa-info-circle mr-2"></i>
                        Product Variants
                    </h3>
                    <p class="text-blue-800 text-sm">
                        Create different variations of this product (e.g., Size: Small/Medium/Large, Color: Red/Blue/Green).
                        Each variant can have its own price, SKU, stock quantity, and image.
                    </p>
                </div>
                
                <!-- Variant Management -->
                <div>
                    <div class="flex justify-between items-center mb-4">
                        <label class="block text-sm font-medium">Product Variants (<?= count($variants) ?>)</label>
                        <button type="button" onclick="addVariant()" class="btn-primary">
                            <i class="fas fa-plus mr-2"></i> Add Variant
                        </button>
                    </div>
                    
                    <div id="variantsContainer" class="space-y-4">
                        <?php if (empty($variants)): ?>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center text-gray-500">
                                <i class="fas fa-layer-group text-4xl mb-4"></i>
                                <p>No variants created yet.</p>
                                <p class="text-sm mt-2">Click "Add Variant" to create your first variant, or use the generator below.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($variants as $index => $variant): ?>
                            <div class="variant-item border rounded-lg p-4 bg-gray-50" data-index="<?= $index ?>">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold">Variant #<?= $index + 1 ?></span>
                                        <button type="button" onclick="removeVariant(<?= $index ?>)" class="text-red-600 hover:text-red-800 text-sm">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <label class="flex items-center text-sm">
                                            <input type="checkbox" class="variant-active" <?= ($variant['is_active'] ?? 1) ? 'checked' : '' ?>>
                                            <span class="ml-1">Active</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="grid md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Variant Name</label>
                                        <input type="text" class="variant-name w-full px-3 py-2 border rounded" 
                                               value="<?= escape($variant['name'] ?? '') ?>"
                                               placeholder="e.g., Small - Red">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">SKU</label>
                                        <input type="text" class="variant-sku w-full px-3 py-2 border rounded" 
                                               value="<?= escape($variant['sku'] ?? '') ?>"
                                               placeholder="e.g., PROD-001-SM-RED">
                                    </div>
                                </div>
                                
                                <div class="grid md:grid-cols-3 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Price</label>
                                        <div class="relative">
                                            <span class="absolute left-2 top-2 text-gray-500">$</span>
                                            <input type="number" step="0.01" class="variant-price w-full pl-6 pr-3 py-2 border rounded" 
                                                   value="<?= escape($variant['price'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Sale Price</label>
                                        <div class="relative">
                                            <span class="absolute left-2 top-2 text-gray-500">$</span>
                                            <input type="number" step="0.01" class="variant-sale-price w-full pl-6 pr-3 py-2 border rounded" 
                                                   value="<?= escape($variant['sale_price'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Stock Quantity</label>
                                        <input type="number" class="variant-stock w-full px-3 py-2 border rounded" 
                                               value="<?= escape($variant['stock_quantity'] ?? 0) ?>">
                                    </div>
                                </div>
                                
                                <div class="grid md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Stock Status</label>
                                        <select class="variant-stock-status w-full px-3 py-2 border rounded">
                                            <option value="in_stock" <?= ($variant['stock_status'] ?? 'in_stock') === 'in_stock' ? 'selected' : '' ?>>In Stock</option>
                                            <option value="out_of_stock" <?= ($variant['stock_status'] ?? '') === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                                            <option value="on_order" <?= ($variant['stock_status'] ?? '') === 'on_order' ? 'selected' : '' ?>>On Order</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Variant Image (Optional)</label>
                                        <div class="flex gap-2">
                                            <input type="text" class="variant-image flex-1 px-3 py-2 border rounded" 
                                                   value="<?= escape($variant['image'] ?? '') ?>"
                                                   placeholder="image.jpg">
                                            <button type="button" onclick="openImageBrowserForVariant(this)" class="btn-secondary">
                                                <i class="fas fa-folder-open"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Attributes -->
                                <div class="variant-attributes border-t pt-4">
                                    <label class="block text-sm font-medium mb-2">Attributes</label>
                                    <div class="attributes-list space-y-2">
                                        <?php if (!empty($variant['attributes'])): ?>
                                            <?php foreach ($variant['attributes'] as $attrName => $attrValue): ?>
                                                <div class="attribute-row flex gap-2 items-center">
                                                    <input type="text" class="attr-name flex-1 px-3 py-2 border rounded text-sm" 
                                                           value="<?= escape($attrName) ?>" placeholder="Attribute name">
                                                    <span class="text-gray-500">:</span>
                                                    <input type="text" class="attr-value flex-1 px-3 py-2 border rounded text-sm" 
                                                           value="<?= escape($attrValue) ?>" placeholder="Value">
                                                    <button type="button" onclick="removeAttribute(this)" class="text-red-600 hover:text-red-800">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="attribute-row flex gap-2 items-center">
                                                <input type="text" class="attr-name flex-1 px-3 py-2 border rounded text-sm" 
                                                       placeholder="e.g., Size">
                                                <span class="text-gray-500">:</span>
                                                <input type="text" class="attr-value flex-1 px-3 py-2 border rounded text-sm" 
                                                       placeholder="e.g., Small">
                                                <button type="button" onclick="removeAttribute(this)" class="text-red-600 hover:text-red-800">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <button type="button" onclick="addAttributeToVariant(this)" class="mt-2 text-sm text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-plus mr-1"></i> Add Attribute
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <input type="hidden" id="variantsInput" name="variants" value="<?= escape(json_encode($variants)) ?>">
                </div>
                
                <!-- Variant Generator (Advanced) -->
                <div class="border-t pt-6">
                    <h3 class="font-semibold mb-4">
                        <i class="fas fa-magic mr-2"></i>
                        Quick Variant Generator
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Generate multiple variants automatically from attribute combinations. For example: Size (Small, Medium, Large) × Color (Red, Blue) = 6 variants.
                    </p>
                    
                    <div id="variantGenerator" class="space-y-4">
                        <div id="attributeSets">
                            <div class="attribute-set border rounded-lg p-4">
                                <div class="flex justify-between items-center mb-3">
                                    <label class="font-medium">Attribute Set 1</label>
                                    <button type="button" onclick="removeAttributeSet(this)" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm mb-1">Attribute Name</label>
                                        <input type="text" class="attr-name w-full px-3 py-2 border rounded" placeholder="e.g., Size">
                                    </div>
                                    <div>
                                        <label class="block text-sm mb-1">Values (comma-separated)</label>
                                        <input type="text" class="attr-values w-full px-3 py-2 border rounded" placeholder="e.g., Small, Medium, Large">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" onclick="addAttributeSet()" class="btn-secondary">
                            <i class="fas fa-plus mr-2"></i> Add Another Attribute
                        </button>
                        
                        <div class="mt-4">
                            <button type="button" onclick="generateVariants()" class="btn-primary">
                                <i class="fas fa-magic mr-2"></i> Generate All Combinations
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tab Content: SEO & Settings -->
        <div id="tab-content-seo" class="tab-content hidden">
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Meta Title</label>
                    <input type="text" name="meta_title" value="<?= escape($product['meta_title'] ?? '') ?>"
                           class="w-full px-4 py-2 border rounded-lg"
                           placeholder="SEO title for search engines">
                    <p class="text-xs text-gray-500 mt-1">Leave empty to use product name</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Meta Description</label>
                    <textarea name="meta_description" rows="3" 
                              class="w-full px-4 py-2 border rounded-lg"
                              placeholder="SEO description for search engines"><?= escape($product['meta_description'] ?? '') ?></textarea>
                </div>
                
                <div class="border-t pt-4">
                    <h3 class="font-semibold mb-4">Product Settings</h3>
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_featured" <?= ($product['is_featured'] ?? 0) ? 'checked' : '' ?> class="rounded">
                            <span class="ml-2">Featured Product</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" <?= ($product['is_active'] ?? 1) ? 'checked' : '' ?> class="rounded">
                            <span class="ml-2">Active (Visible on website)</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Form Actions -->
        <div class="border-t mt-6 pt-6 flex justify-between">
            <a href="<?= url('admin/products.php') ?>" class="btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i> Back to Products
            </a>
            <div class="flex gap-2">
                <button type="button" onclick="saveDraft()" class="btn-secondary">
                    <i class="fas fa-save mr-2"></i> Save Draft
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-check mr-2"></i> <?= $product ? 'Update Product' : 'Create Product' ?>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Image Browser Modal -->
<div id="imageBrowserModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4" onclick="closeImageBrowser()">
    <div class="bg-white rounded-lg max-w-6xl w-full max-h-[90vh] overflow-auto" onclick="event.stopPropagation()">
        <div class="p-6 border-b flex justify-between items-center sticky top-0 bg-white z-10">
            <h3 class="text-xl font-bold">Select Image</h3>
            <button onclick="closeImageBrowser()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <input type="text" id="imageSearch" placeholder="Search images..." 
                       class="w-full px-4 py-2 border rounded-lg"
                       onkeyup="filterImages(this.value)">
            </div>
            <div id="imageBrowserGrid" class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <!-- Images will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
// Tab switching
function switchTab(tab) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active', 'border-blue-500', 'text-blue-600');
        btn.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab
    document.getElementById('tab-content-' + tab).classList.remove('hidden');
    const tabBtn = document.getElementById('tab-' + tab);
    tabBtn.classList.add('active', 'border-blue-500', 'text-blue-600');
    tabBtn.classList.remove('border-transparent', 'text-gray-500');
}

// Gallery management
let gallery = <?= json_encode($gallery ?? []) ?>;
let imageBrowserMode = 'main'; // 'main' or 'gallery'

// Load all images for browser
let allImages = [];

function loadImagesForBrowser() {
    fetch('<?= url('admin/api/get-images.php') ?>')
        .then(response => response.json())
        .then(data => {
            allImages = data.images || [];
            displayImagesInBrowser();
        })
        .catch(error => {
            console.error('Error loading images:', error);
        });
}

function displayImagesInBrowser(filter = '') {
    const grid = document.getElementById('imageBrowserGrid');
    let filtered = allImages;
    
    if (filter) {
        filtered = allImages.filter(img => 
            img.filename.toLowerCase().includes(filter.toLowerCase())
        );
    }
    
    if (filtered.length === 0) {
        grid.innerHTML = '<div class="col-span-full text-center py-8 text-gray-500">No images found</div>';
        return;
    }
    
    grid.innerHTML = filtered.map(img => `
        <div class="border rounded-lg overflow-hidden hover:shadow-lg cursor-pointer image-browser-item" 
             onclick="selectImage('${img.filename}')">
            <div class="aspect-square bg-gray-100 overflow-hidden">
                <img src="${img.url}" alt="${img.filename}" class="w-full h-full object-cover">
            </div>
            <div class="p-2">
                <p class="text-xs text-gray-600 truncate" title="${img.filename}">${img.filename}</p>
            </div>
        </div>
    `).join('');
}

function filterImages(search) {
    displayImagesInBrowser(search);
}

function openImageBrowser(mode) {
    imageBrowserMode = mode;
    document.getElementById('imageBrowserModal').classList.remove('hidden');
    loadImagesForBrowser();
}

function closeImageBrowser() {
    document.getElementById('imageBrowserModal').classList.add('hidden');
}

function selectImage(filename) {
    if (imageBrowserMode === 'main') {
        setMainImage(filename);
        closeImageBrowser();
    } else if (imageBrowserMode === 'variant' && window.currentVariantImageInput) {
        window.currentVariantImageInput.value = filename;
        updateVariantsData();
        closeImageBrowser();
    } else {
        addToGallery(filename);
        closeImageBrowser();
    }
}

// Main image functions
function setMainImage(filename) {
    document.getElementById('imageInput').value = filename;
    const placeholder = document.getElementById('mainImagePlaceholder');
    const area = document.getElementById('mainImageArea');
    
    if (placeholder) {
        placeholder.remove();
    }
    
    area.innerHTML = `
        <div class="relative inline-block">
            <img src="<?= asset('storage/uploads/') ?>${filename}" 
                 alt="Main image" 
                 id="mainImagePreview"
                 class="max-w-xs max-h-64 object-contain rounded-lg border shadow-lg">
            <button type="button" onclick="removeMainImage()" 
                    class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-red-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
}

function removeMainImage() {
    document.getElementById('imageInput').value = '';
    const area = document.getElementById('mainImageArea');
    area.innerHTML = `
        <div id="mainImagePlaceholder" class="py-12">
            <i class="fas fa-image text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 mb-4">No main image selected</p>
            <button type="button" onclick="openImageBrowser('main')" class="btn-primary">
                <i class="fas fa-upload mr-2"></i> Select or Upload Image
            </button>
        </div>
    `;
}

function uploadMainImage(file) {
    if (!file) return;
    
    const formData = new FormData();
    formData.append('file', file);
    
    const preview = document.getElementById('mainImagePreview');
    const placeholder = document.getElementById('mainImagePlaceholder');
    
    if (preview) {
        preview.style.opacity = '0.5';
    }
    if (placeholder) {
        placeholder.innerHTML = '<i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i><p class="text-gray-600">Uploading...</p>';
    }
    
    fetch('<?= url('admin/upload.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Support both old format (data.file) and new format (data.files[0])
            const filename = data.file || (data.files && data.files[0] ? data.files[0].filename : null);
            const url = data.url || (data.files && data.files[0] ? data.files[0].url : null);
            
            if (filename) {
                setMainImage(filename);
                if (allImages.length > 0) {
                    allImages.unshift({
                        filename: filename,
                        url: url || '<?= asset('storage/uploads/') ?>' + filename
                    });
                }
            } else {
                throw new Error('No file returned from server');
            }
        } else {
            throw new Error(data.message || 'Upload failed');
        }
    })
    .catch(error => {
        if (placeholder) {
            placeholder.innerHTML = `
                <i class="fas fa-image text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 mb-4">No main image selected</p>
                <p class="text-red-500 text-sm mb-2">Upload failed: ${error.message}</p>
                <button type="button" onclick="openImageBrowser('main')" class="btn-primary">
                    <i class="fas fa-upload mr-2"></i> Select or Upload Image
                </button>
            `;
        }
        alert('Upload error: ' + error.message);
        console.error('Upload error:', error);
    });
}

// Gallery functions
function addToGallery(filename) {
    if (gallery.includes(filename)) {
        return; // Already in gallery
    }
    gallery.push(filename);
    updateGalleryDisplay();
}

function removeFromGallery(filename) {
    gallery = gallery.filter(img => img !== filename);
    updateGalleryDisplay();
}

function setAsMainImage(filename) {
    setMainImage(filename);
    removeFromGallery(filename);
}

function clearGallery() {
    if (confirm('Are you sure you want to clear all gallery images?')) {
        gallery = [];
        updateGalleryDisplay();
    }
}

function updateGalleryDisplay() {
    const container = document.getElementById('galleryContainer');
    const hiddenInput = document.getElementById('galleryInput');
    
    hiddenInput.value = JSON.stringify(gallery);
    
    if (gallery.length === 0) {
        container.innerHTML = `
            <div class="col-span-full text-center py-8 text-gray-500">
                <i class="fas fa-images text-4xl mb-2"></i>
                <p>No gallery images. Add images below.</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = gallery.map((img, index) => `
        <div class="relative group gallery-item" data-image="${img}" draggable="true">
            <img src="<?= asset('storage/uploads/') ?>${img}" 
                 alt="" 
                 class="w-full h-32 object-cover rounded border cursor-move"
                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27200%27 height=%27200%27%3E%3Crect fill=%27%23ddd%27 width=%27200%27 height=%27200%27/%3E%3Ctext fill=%27%23999%27 x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27%3EBroken%3C/text%3E%3C/svg%3E'">
            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all flex items-center justify-center">
                <div class="text-white opacity-0 group-hover:opacity-100 flex gap-2">
                    <button type="button" onclick="setAsMainImage('${img}')" 
                            class="bg-blue-500 hover:bg-blue-600 rounded px-2 py-1 text-xs" title="Set as Main">
                        <i class="fas fa-star"></i>
                    </button>
                    <button type="button" onclick="removeFromGallery('${img}')" 
                            class="bg-red-500 hover:bg-red-600 rounded px-2 py-1 text-xs" title="Remove">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="absolute top-1 left-1 bg-gray-800 bg-opacity-75 text-white text-xs px-2 py-1 rounded">
                ${index + 1}
            </div>
        </div>
    `).join('');
    
    // Re-enable drag and drop
    enableDragAndDrop();
}

function uploadGalleryImages(files) {
    if (!files || files.length === 0) return;
    
    const formData = new FormData();
    for (let i = 0; i < files.length; i++) {
        formData.append('files[]', files[i]);
    }
    
    // Show loading indicator
    const galleryContainer = document.getElementById('galleryContainer');
    if (galleryContainer) {
        const loadingMsg = document.createElement('div');
        loadingMsg.id = 'upload-loading';
        loadingMsg.className = 'col-span-full text-center py-4 text-blue-600';
        loadingMsg.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Uploading images...';
        galleryContainer.appendChild(loadingMsg);
    }
    
    fetch('<?= url('admin/upload.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        // Remove loading indicator
        const loadingMsg = document.getElementById('upload-loading');
        if (loadingMsg) {
            loadingMsg.remove();
        }
        
        if (data.success && data.files && data.files.length > 0) {
            data.files.forEach(file => {
                addToGallery(file.filename);
            });
            // Reload images for browser
            loadImagesForBrowser();
        } else {
            alert('Upload failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        // Remove loading indicator
        const loadingMsg = document.getElementById('upload-loading');
        if (loadingMsg) {
            loadingMsg.remove();
        }
        alert('Upload error: ' + error.message);
        console.error('Upload error:', error);
    });
}

// Drag and drop for gallery reordering
function enableDragAndDrop() {
    const items = document.querySelectorAll('.gallery-item');
    items.forEach(item => {
        item.addEventListener('dragstart', handleDragStart);
        item.addEventListener('dragover', handleDragOver);
        item.addEventListener('drop', handleDrop);
        item.addEventListener('dragend', handleDragEnd);
    });
}

let draggedElement = null;

function handleDragStart(e) {
    draggedElement = this;
    this.style.opacity = '0.5';
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/html', this.innerHTML);
}

function handleDragOver(e) {
    if (e.preventDefault) {
        e.preventDefault();
    }
    e.dataTransfer.dropEffect = 'move';
    return false;
}

function handleDrop(e) {
    if (e.stopPropagation) {
        e.stopPropagation();
    }
    
    if (draggedElement !== this) {
        const draggedImage = draggedElement.dataset.image;
        const targetImage = this.dataset.image;
        
        const draggedIndex = gallery.indexOf(draggedImage);
        const targetIndex = gallery.indexOf(targetImage);
        
        // Reorder array
        gallery.splice(draggedIndex, 1);
        gallery.splice(targetIndex, 0, draggedImage);
        
        updateGalleryDisplay();
    }
    
    return false;
}

function handleDragEnd(e) {
    this.style.opacity = '1';
}

// Initialize drag and drop
enableDragAndDrop();

// Save draft (localStorage)
function saveDraft() {
    const form = document.getElementById('productForm');
    const formData = new FormData(form);
    const draft = {};
    
    for (const [key, value] of formData.entries()) {
        draft[key] = value;
    }
    
    localStorage.setItem('product_draft_<?= $productId ?? 'new' ?>', JSON.stringify(draft));
    alert('Draft saved locally!');
}

// Close modal on ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeImageBrowser();
    }
});

// Variant Management
let variants = <?= json_encode($variants ?? []) ?>;

function addVariant() {
    variants.push({
        name: '',
        sku: '',
        price: <?= floatval($product['price'] ?? 0) ?>,
        sale_price: null,
        stock_quantity: 0,
        stock_status: 'in_stock',
        image: '',
        attributes: {},
        is_active: 1,
        sort_order: variants.length
    });
    updateVariantsDisplay();
    switchTab('variants');
}

function removeVariant(index) {
    if (confirm('Are you sure you want to remove this variant?')) {
        variants.splice(index, 1);
        updateVariantsDisplay();
    }
}

function updateVariantsDisplay() {
    const container = document.getElementById('variantsContainer');
    const hiddenInput = document.getElementById('variantsInput');
    
    if (!hiddenInput) return;
    
    hiddenInput.value = JSON.stringify(variants);
    
    if (variants.length === 0) {
        if (container) {
            container.innerHTML = `
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center text-gray-500">
                    <i class="fas fa-layer-group text-4xl mb-4"></i>
                    <p>No variants created yet.</p>
                    <p class="text-sm mt-2">Click "Add Variant" to create your first variant, or use the generator below.</p>
                </div>
            `;
        }
        return;
    }
    
    // Reload page to show updated variants
    location.reload();
}

function addAttributeToVariant(button) {
    const variantItem = button.closest('.variant-item');
    if (!variantItem) return;
    
    const attributesList = variantItem.querySelector('.attributes-list');
    if (!attributesList) return;
    
    const newRow = document.createElement('div');
    newRow.className = 'attribute-row flex gap-2 items-center';
    newRow.innerHTML = `
        <input type="text" class="attr-name flex-1 px-3 py-2 border rounded text-sm" placeholder="e.g., Size">
        <span class="text-gray-500">:</span>
        <input type="text" class="attr-value flex-1 px-3 py-2 border rounded text-sm" placeholder="e.g., Small">
        <button type="button" onclick="removeAttribute(this)" class="text-red-600 hover:text-red-800">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    attributesList.appendChild(newRow);
    updateVariantsData();
}

function removeAttribute(button) {
    button.closest('.attribute-row').remove();
    updateVariantsData();
}

function updateVariantsData() {
    const variantItems = document.querySelectorAll('.variant-item');
    variants = [];
    
    variantItems.forEach((item, index) => {
        const variant = {
            name: item.querySelector('.variant-name')?.value || '',
            sku: item.querySelector('.variant-sku')?.value || '',
            price: parseFloat(item.querySelector('.variant-price')?.value || 0),
            sale_price: item.querySelector('.variant-sale-price')?.value ? parseFloat(item.querySelector('.variant-sale-price').value) : null,
            stock_quantity: parseInt(item.querySelector('.variant-stock')?.value || 0),
            stock_status: item.querySelector('.variant-stock-status')?.value || 'in_stock',
            image: item.querySelector('.variant-image')?.value || '',
            attributes: {},
            is_active: item.querySelector('.variant-active')?.checked ? 1 : 0,
            sort_order: index
        };
        
        // Get attributes
        const attrRows = item.querySelectorAll('.attribute-row');
        attrRows.forEach(row => {
            const name = row.querySelector('.attr-name')?.value.trim();
            const value = row.querySelector('.attr-value')?.value.trim();
            if (name && value) {
                variant.attributes[name] = value;
            }
        });
        
        variants.push(variant);
    });
    
    const hiddenInput = document.getElementById('variantsInput');
    if (hiddenInput) {
        hiddenInput.value = JSON.stringify(variants);
    }
}

// Variant Generator
function addAttributeSet() {
    const container = document.getElementById('attributeSets');
    if (!container) return;
    
    const newSet = document.createElement('div');
    newSet.className = 'attribute-set border rounded-lg p-4';
    newSet.innerHTML = `
        <div class="flex justify-between items-center mb-3">
            <label class="font-medium">Attribute Set ${container.children.length + 1}</label>
            <button type="button" onclick="removeAttributeSet(this)" class="text-red-600 hover:text-red-800">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm mb-1">Attribute Name</label>
                <input type="text" class="attr-name w-full px-3 py-2 border rounded" placeholder="e.g., Size">
            </div>
            <div>
                <label class="block text-sm mb-1">Values (comma-separated)</label>
                <input type="text" class="attr-values w-full px-3 py-2 border rounded" placeholder="e.g., Small, Medium, Large">
            </div>
        </div>
    `;
    container.appendChild(newSet);
}

function removeAttributeSet(button) {
    const container = document.getElementById('attributeSets');
    if (!container) return;
    
    if (container.children.length > 1) {
        button.closest('.attribute-set').remove();
    } else {
        alert('You need at least one attribute set.');
    }
}

function generateVariants() {
    const attributeSets = document.querySelectorAll('.attribute-set');
    const sets = [];
    
    attributeSets.forEach(set => {
        const name = set.querySelector('.attr-name')?.value.trim();
        const values = set.querySelector('.attr-values')?.value.split(',').map(v => v.trim()).filter(v => v);
        
        if (name && values.length > 0) {
            sets.push({ name, values });
        }
    });
    
    if (sets.length === 0) {
        alert('Please add at least one attribute set with name and values.');
        return;
    }
    
    // Generate all combinations
    function generateCombinations(sets, index = 0, current = {}) {
        if (index === sets.length) {
            return [current];
        }
        
        const results = [];
        sets[index].values.forEach(value => {
            const newCurrent = { ...current, [sets[index].name]: value };
            results.push(...generateCombinations(sets, index + 1, newCurrent));
        });
        
        return results;
    }
    
    const combinations = generateCombinations(sets);
    
    if (combinations.length > 50) {
        if (!confirm(`This will create ${combinations.length} variants. Continue?`)) {
            return;
        }
    }
    
    // Add variants
    combinations.forEach(combo => {
        const variantName = Object.values(combo).join(' - ');
        const variantSku = '<?= escape($product['sku'] ?? 'PROD') ?>-' + Object.values(combo).map(v => v.substring(0, 3).toUpperCase()).join('-');
        
        variants.push({
            name: variantName,
            sku: variantSku,
            price: <?= floatval($product['price'] ?? 0) ?>,
            sale_price: null,
            stock_quantity: 0,
            stock_status: 'in_stock',
            image: '',
            attributes: combo,
            is_active: 1,
            sort_order: variants.length
        });
    });
    
    updateVariantsDisplay();
    alert(`Generated ${combinations.length} variants!`);
}

function openImageBrowserForVariant(button) {
    const variantItem = button.closest('.variant-item');
    if (!variantItem) return;
    
    const imageInput = variantItem.querySelector('.variant-image');
    if (!imageInput) return;
    
    // Store which input to update
    window.currentVariantImageInput = imageInput;
    imageBrowserMode = 'variant';
    openImageBrowser('variant');
}

// Update variants data on input change
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners to all variant inputs
    document.addEventListener('input', function(e) {
        if (e.target.closest('.variant-item')) {
            updateVariantsData();
        }
    });
    
    document.addEventListener('change', function(e) {
        if (e.target.closest('.variant-item')) {
            updateVariantsData();
        }
    });
    
    // Auto-generate slug from name when name changes
    const nameInput = document.querySelector('input[name="name"]');
    const slugInput = document.getElementById('product-slug-input');
    
    if (nameInput && slugInput) {
        let slugManuallyEdited = false;
        
        // Track if user manually edits slug
        slugInput.addEventListener('input', function() {
            slugManuallyEdited = true;
        });
        
        // Auto-generate slug when name changes (only if slug wasn't manually edited)
        nameInput.addEventListener('blur', function() {
            if (!slugManuallyEdited && (!slugInput.value || slugInput.value === '')) {
                generateSlugFromName();
            }
        });
    }
});

// Generate slug from product name
function generateSlugFromName() {
    const nameInput = document.querySelector('input[name="name"]');
    const slugInput = document.getElementById('product-slug-input');
    
    if (!nameInput || !slugInput) return;
    
    const name = nameInput.value.trim();
    if (!name) {
        alert('Please enter a product name first.');
        return;
    }
    
    // Generate slug: lowercase, replace spaces/special chars with hyphens
    let slug = name.toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
    
    // Ensure it's not empty
    if (!slug) {
        slug = 'product-' + Date.now();
    }
    
    slugInput.value = slug;
}
</script>

<style>
.tab-button.active {
    border-bottom-color: #3b82f6;
    color: #2563eb;
}
.gallery-item {
    transition: transform 0.2s;
}
.gallery-item:hover {
    transform: scale(1.05);
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
