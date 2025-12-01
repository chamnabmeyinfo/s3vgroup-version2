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
    
    // No slug validation - allow any slug
    
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
                
                // If SKU hasn't changed, remove it from update to avoid database UNIQUE constraint violation
                $currentSku = trim($currentProduct['sku'] ?? '');
                $newSku = trim($data['sku'] ?? '');
                if (strcasecmp($currentSku, $newSku) === 0) {
                    // SKU is the same - remove from update to avoid constraint error
                    unset($data['sku']);
                }
                
                // If slug hasn't changed, remove it from update to avoid database UNIQUE constraint violation
                if (isset($data['slug']) && $currentProduct['slug'] === $data['slug']) {
                    unset($data['slug']);
                }
                
                // No validation - allow updates without any restrictions
                
                $productModel->update($productId, $data);
                $message = 'Product updated successfully.';
            } else {
                // No SKU validation for new products - allow creation without blocking
                $productId = $productModel->create($data);
                $message = 'Product created successfully.';
                $isNewProduct = true; // Track that this is a new product
            }
            
            // Handle variants
            if ($productId) {
                // Create variant images table if it doesn't exist
                try {
                    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'product_variant_images'");
                    if (!$tableExists) {
                        // Try with foreign key first
                        try {
                            $db->getPdo()->exec("
                                CREATE TABLE product_variant_images (
                                    id INT AUTO_INCREMENT PRIMARY KEY,
                                    variant_id INT NOT NULL,
                                    image VARCHAR(255) NOT NULL,
                                    sort_order INT DEFAULT 0,
                                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE CASCADE,
                                    INDEX idx_variant_id (variant_id)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                            ");
                        } catch (Exception $e) {
                            // If foreign key fails, create without it
                            $db->getPdo()->exec("
                                CREATE TABLE product_variant_images (
                                    id INT AUTO_INCREMENT PRIMARY KEY,
                                    variant_id INT NOT NULL,
                                    image VARCHAR(255) NOT NULL,
                                    sort_order INT DEFAULT 0,
                                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                    INDEX idx_variant_id (variant_id)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                            ");
                        }
                    }
                } catch (Exception $e) {
                    // Table might already exist
                    error_log('Variant images table check: ' . $e->getMessage());
                }
                
                $variants = !empty($_POST['variants']) ? json_decode($_POST['variants'], true) ?? [] : [];
                
                // Get existing variants
                $existingVariants = [];
                try {
                    $existingVariantsRaw = $db->fetchAll(
                        "SELECT id FROM product_variants WHERE product_id = :product_id",
                        ['product_id' => $productId]
                    );
                    $existingVariants = array_column($existingVariantsRaw, 'id');
                } catch (Exception $e) {
                    // Tables might not exist yet
                }
                
                $processedVariantIds = [];
                
                // Update or insert variants
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
                        'is_active' => !empty($variant['is_active']) ? 1 : 0,
                        'sort_order' => intval($variant['sort_order'] ?? 0)
                    ];
                    
                    // Check if variant has an ID (existing variant)
                    $variantId = null;
                    if (!empty($variant['id']) && in_array($variant['id'], $existingVariants)) {
                        // Update existing variant
                        $variantId = (int)$variant['id'];
                        try {
                            $db->update('product_variants', $variantData, 'id = :id', ['id' => $variantId]);
                            
                            // Delete existing attributes
                            $db->delete('product_variant_attributes', 'variant_id = :variant_id', ['variant_id' => $variantId]);
                        } catch (Exception $e) {
                            // If update fails, try insert
                            $variantId = $db->insert('product_variants', $variantData);
                        }
                    } else {
                        // Insert new variant
                        try {
                            $variantId = $db->insert('product_variants', $variantData);
                        } catch (Exception $e) {
                            // If insert fails, log and continue
                            error_log('Failed to insert variant: ' . $e->getMessage());
                            continue;
                        }
                    }
                    
                    if ($variantId) {
                        $processedVariantIds[] = $variantId;
                        
                        // Insert variant attributes
                        foreach ($variant['attributes'] as $attrName => $attrValue) {
                            if (empty($attrName) || empty($attrValue)) continue;
                            
                            try {
                                $db->insert('product_variant_attributes', [
                                    'variant_id' => $variantId,
                                    'attribute_name' => trim($attrName),
                                    'attribute_value' => trim($attrValue)
                                ]);
                            } catch (Exception $e) {
                                error_log('Failed to insert variant attribute: ' . $e->getMessage());
                            }
                        }
                        
                        // Handle variant gallery
                        if (!empty($variant['gallery']) && is_array($variant['gallery'])) {
                            try {
                                // Delete existing gallery images
                                $db->delete('product_variant_images', 'variant_id = :variant_id', ['variant_id' => $variantId]);
                                
                                // Insert new gallery images
                                foreach ($variant['gallery'] as $sortOrder => $image) {
                                    if (!empty($image)) {
                                        $db->insert('product_variant_images', [
                                            'variant_id' => $variantId,
                                            'image' => trim($image),
                                            'sort_order' => $sortOrder
                                        ]);
                                    }
                                }
                            } catch (Exception $e) {
                                error_log('Failed to insert variant gallery: ' . $e->getMessage());
                            }
                        }
                    }
                }
                
                // Delete variants that are no longer in the list
                if (!empty($existingVariants) && !empty($processedVariantIds)) {
                    $variantsToDelete = array_diff($existingVariants, $processedVariantIds);
                    if (!empty($variantsToDelete)) {
                        try {
                            // Delete variant gallery images first
                            foreach ($variantsToDelete as $variantIdToDelete) {
                                $db->delete('product_variant_images', 'variant_id = :variant_id', ['variant_id' => $variantIdToDelete]);
                            }
                            // Delete variant attributes
                            foreach ($variantsToDelete as $variantIdToDelete) {
                                $db->delete('product_variant_attributes', 'variant_id = :variant_id', ['variant_id' => $variantIdToDelete]);
                            }
                            // Then delete variants
                            foreach ($variantsToDelete as $variantIdToDelete) {
                                $db->delete('product_variants', 'id = :id', ['id' => $variantIdToDelete]);
                            }
                        } catch (Exception $e) {
                            error_log('Failed to delete variants: ' . $e->getMessage());
                        }
                    }
                } elseif (!empty($existingVariants) && empty($variants)) {
                    // All variants were removed
                    try {
                        $db->delete('product_variant_images', 'variant_id IN (SELECT id FROM product_variants WHERE product_id = :product_id)', ['product_id' => $productId]);
                        $db->delete('product_variant_attributes', 'variant_id IN (SELECT id FROM product_variants WHERE product_id = :product_id)', ['product_id' => $productId]);
                        $db->delete('product_variants', 'product_id = :product_id', ['product_id' => $productId]);
                    } catch (Exception $e) {
                        error_log('Failed to delete all variants: ' . $e->getMessage());
                    }
                }
            }
            
            $db->getPdo()->commit();
            
            // Redirect to edit page after creating a new product
            if (isset($isNewProduct) && $isNewProduct && $productId) {
                header('Location: ' . url('admin/product-edit.php?id=' . $productId));
                exit;
            }
            $product = $productModel->getById($productId);
        } catch (Exception $e) {
            if (isset($db)) {
                $db->getPdo()->rollBack();
            }
            
            $errorMessage = $e->getMessage();
            
            // If it's a duplicate entry error (database constraint), try to handle it gracefully
            if (strpos($errorMessage, 'Duplicate entry') !== false || strpos($errorMessage, '1062') !== false) {
                // For duplicate SKU: if updating, try again without SKU field
                if ($productId && strpos($errorMessage, 'sku') !== false) {
                    try {
                        unset($data['sku']);
                        $db->getPdo()->beginTransaction();
                        $productModel->update($productId, $data);
                        $db->getPdo()->commit();
                        $message = 'Product updated successfully. (SKU was not changed due to duplicate constraint)';
                        $error = '';
                        $product = $productModel->getById($productId);
                    } catch (Exception $e2) {
                        $db->getPdo()->rollBack();
                        $error = 'Error saving product: ' . $e2->getMessage();
                    }
                } else {
                    // For other duplicates, just show error
                    $error = 'Error: ' . $errorMessage;
                }
            } else {
                // Other errors
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
            
            // Get attributes
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
            
            // Get variant galleries
            $variantGalleries = [];
            try {
                $galleryImages = db()->fetchAll(
                    "SELECT * FROM product_variant_images WHERE variant_id IN ($placeholders) ORDER BY sort_order, id",
                    $variantIds
                );
                
                foreach ($galleryImages as $img) {
                    if (!isset($variantGalleries[$img['variant_id']])) {
                        $variantGalleries[$img['variant_id']] = [];
                    }
                    $variantGalleries[$img['variant_id']][] = $img['image'];
                }
            } catch (Exception $e) {
                // Table might not exist yet
            }
            
            // Add attributes and galleries to variants
            foreach ($variants as &$variant) {
                $variant['attributes'] = $variantAttributes[$variant['id']] ?? [];
                $variant['gallery'] = $variantGalleries[$variant['id']] ?? [];
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
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 shadow-sm">
                                <i class="fas fa-layer-group text-white text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900">Product Variants</h3>
                                <p class="text-xs text-gray-500 mt-0.5"><?= count($variants) ?> variant<?= count($variants) !== 1 ? 's' : '' ?> configured</p>
                            </div>
                        </div>
                        <button type="button" onclick="addVariant()" class="btn-primary flex items-center gap-2 px-4 py-2 text-sm rounded-lg font-medium shadow-sm hover:shadow-md transition-all hover:scale-105">
                            <i class="fas fa-plus"></i> <span class="hidden sm:inline">Add Variant</span>
                        </button>
                    </div>
                    
                    <div id="variantsContainer" class="bg-white rounded-xl shadow-sm border border-gray-200/60 overflow-hidden backdrop-blur-sm">
                        <?php if (empty($variants)): ?>
                            <div class="border-2 border-dashed border-blue-200 rounded-xl p-8 text-center bg-gradient-to-br from-blue-50/50 to-white">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 shadow-lg mb-4 transform hover:scale-110 transition-transform">
                                    <i class="fas fa-layer-group text-2xl text-white"></i>
                                </div>
                                <h4 class="text-base font-bold text-gray-900 mb-2">No variants yet</h4>
                                <p class="text-sm text-gray-600 mb-4 max-w-sm mx-auto">Create product variations with different sizes, colors, or options to offer more choices to your customers</p>
                                <button type="button" onclick="addVariant()" class="btn-primary inline-flex items-center gap-2 px-5 py-2.5 rounded-lg shadow-md hover:shadow-lg transition-all hover:scale-105">
                                    <i class="fas fa-plus"></i> Create First Variant
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50 border-b">
                                            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600" style="width: 80px;">Image</th>
                                            <th class="px-2 py-2.5 text-left text-xs font-semibold text-gray-600" style="width: 160px;">
                                                <div class="flex items-center gap-1">
                                                    <i class="fas fa-images text-blue-600 text-xs"></i>
                                                    <span>Gallery</span>
                                                </div>
                                            </th>
                                            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600" style="width: 150px;">Name</th>
                                            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600" style="width: 110px;">SKU</th>
                                            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600" style="width: 75px;">Price</th>
                                            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600" style="width: 75px;">Sale</th>
                                            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600" style="width: 70px;">Stock</th>
                                            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600" style="width: 100px;">Status</th>
                                            <th class="px-3 py-2.5 text-center text-xs font-semibold text-gray-600" style="width: 60px;">Active</th>
                                            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600" style="width: 200px;">Attributes</th>
                                            <th class="px-3 py-2.5 text-center text-xs font-semibold text-gray-600" style="width: 60px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($variants as $index => $variant): ?>
                                        <tr class="variant-item hover:bg-gray-50 border-b" data-index="<?= $index ?>" data-variant-id="<?= escape($variant['id'] ?? '') ?>">
                                            <!-- Image -->
                                            <td class="px-3 py-3 whitespace-nowrap" style="width: 80px;">
                                                <div class="flex flex-col items-center gap-1">
                                                    <div class="relative group/image">
                                                        <?php if (!empty($variant['image'])): ?>
                                                            <img src="<?= asset('storage/uploads/' . escape($variant['image'])) ?>" 
                                                                 alt="Variant" 
                                                                 class="w-12 h-12 object-cover rounded border border-gray-200 shadow-sm group-hover/image:border-blue-400 transition-all"
                                                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2748%27 height=%2748%27%3E%3Crect fill=%27%23f3f4f6%27 width=%2748%27 height=%2748%27/%3E%3Ctext fill=%27%239ca3af%27 x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27 font-size=%2710%27%3E%3C/text%3E%3C/svg%3E'">
                                                        <?php else: ?>
                                                            <div class="w-12 h-12 bg-gray-100 rounded border border-gray-200 flex items-center justify-center">
                                                                <i class="fas fa-image text-gray-400 text-sm"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div class="absolute inset-0 bg-black/0 group-hover/image:bg-black/30 rounded transition-all flex items-center justify-center gap-1 opacity-0 group-hover/image:opacity-100">
                                                            <button type="button" onclick="openImageBrowserForVariant(this)" class="bg-white rounded p-1 shadow hover:bg-blue-50 text-blue-600 text-xs" title="Browse">
                                                                <i class="fas fa-folder-open"></i>
                                                            </button>
                                                            <label class="bg-white rounded p-1 shadow hover:bg-blue-50 text-blue-600 text-xs cursor-pointer" title="Upload">
                                                                <i class="fas fa-upload"></i>
                                                                <input type="file" accept="image/*" class="hidden" onchange="uploadVariantImage(this, <?= $index ?>)">
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <input type="text" class="variant-image hidden" value="<?= escape($variant['image'] ?? '') ?>">
                                                </div>
                                            </td>
                                            
                                            <!-- Gallery -->
                                            <td class="px-2 py-2" style="width: 160px;">
                                                <div class="variant-gallery bg-blue-50/20 border border-blue-200 rounded p-1.5">
                                                    <div class="flex items-center gap-1 mb-1">
                                                        <i class="fas fa-images text-blue-600 text-xs"></i>
                                                        <label class="text-xs font-semibold text-gray-700">Gallery</label>
                                                        <?php 
                                                        $variantGallery = $variant['gallery'] ?? [];
                                                        if (!empty($variantGallery)): 
                                                        ?>
                                                        <span class="ml-auto text-xs text-blue-600 font-medium"><?= count($variantGallery) ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="gallery-images flex flex-wrap gap-1 max-h-20 overflow-y-auto mb-1.5 p-1 bg-white rounded border border-dashed border-gray-300 min-h-[50px]">
                                                        <?php 
                                                        if (!empty($variantGallery)): 
                                                            foreach ($variantGallery as $galleryImage): 
                                                                $escapedGalleryImage = htmlspecialchars($galleryImage, ENT_QUOTES, 'UTF-8');
                                                        ?>
                                                            <div class="relative group/gallery">
                                                                <img src="<?= asset('storage/uploads/' . escape($galleryImage)) ?>" 
                                                                     alt="Gallery" 
                                                                     class="w-10 h-10 object-cover rounded border border-gray-300 group-hover/gallery:border-blue-400 transition-all"
                                                                     onerror="this.style.display='none'">
                                                                <button type="button" onclick="removeVariantGalleryImage(this, <?= $index ?>, '<?= $escapedGalleryImage ?>')" 
                                                                        class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-4 h-4 flex items-center justify-center opacity-0 group-hover/gallery:opacity-100 transition-opacity text-xs hover:bg-red-600"
                                                                        title="Remove">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </div>
                                                        <?php 
                                                            endforeach;
                                                        else:
                                                        ?>
                                                            <div class="w-full text-center py-2 text-gray-400 text-xs">
                                                                <i class="fas fa-image text-lg mb-0.5 block opacity-50"></i>
                                                                <span>No images</span>
                                                            </div>
                                                        <?php 
                                                        endif; 
                                                        ?>
                                                    </div>
                                                    
                                                    <div class="flex gap-1">
                                                        <button type="button" onclick="openImageBrowserForVariantGallery(this, <?= $index ?>)" 
                                                                class="flex-1 text-xs text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-1.5 py-1 rounded border border-blue-300 transition-colors flex items-center justify-center gap-1 font-medium">
                                                            <i class="fas fa-folder-open text-xs"></i>
                                                            <span>Browse</span>
                                                        </button>
                                                        <label class="flex-1 text-xs text-green-600 hover:text-green-700 bg-green-50 hover:bg-green-100 px-1.5 py-1 rounded border border-green-300 transition-colors flex items-center justify-center gap-1 font-medium cursor-pointer">
                                                            <i class="fas fa-upload text-xs"></i>
                                                            <span>Upload</span>
                                                            <input type="file" accept="image/*" multiple class="hidden" onchange="uploadVariantGalleryImages(this, <?= $index ?>)">
                                                        </label>
                                                    </div>
                                                    
                                                    <input type="hidden" class="variant-gallery-data" value="<?= escape(json_encode($variantGallery ?? [])) ?>">
                                                </div>
                                            </td>
                                            
                                            <!-- Name -->
                                            <td class="px-3 py-3" style="width: 150px;">
                                                <input type="text" class="variant-name w-full px-2 py-1.5 border border-gray-300 rounded text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400" 
                                                       value="<?= escape($variant['name'] ?? '') ?>"
                                                       placeholder="Variant name">
                                                <input type="text" class="variant-name-header hidden" value="<?= escape($variant['name'] ?? '') ?>">
                                            </td>
                                            
                                            <!-- SKU -->
                                            <td class="px-3 py-3" style="width: 110px;">
                                                <input type="text" class="variant-sku w-full px-2 py-1.5 border border-gray-300 rounded text-sm text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 font-mono" 
                                                       value="<?= escape($variant['sku'] ?? '') ?>"
                                                       placeholder="SKU">
                                            </td>
                                            
                                            <!-- Price -->
                                            <td class="px-2 py-3" style="width: 75px;">
                                                <div class="relative">
                                                    <span class="absolute left-1.5 top-1.5 text-gray-500 text-xs">$</span>
                                                    <input type="number" step="0.01" class="variant-price w-full pl-4 pr-1 py-1.5 border border-gray-300 rounded text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                                                           value="<?= escape($variant['price'] ?? '') ?>"
                                                           placeholder="0.00">
                                                </div>
                                            </td>
                                            
                                            <!-- Sale Price -->
                                            <td class="px-2 py-3" style="width: 75px;">
                                                <div class="relative">
                                                    <span class="absolute left-1.5 top-1.5 text-gray-500 text-xs">$</span>
                                                    <input type="number" step="0.01" class="variant-sale-price w-full pl-4 pr-1 py-1.5 border border-gray-300 rounded text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                                                           value="<?= escape($variant['sale_price'] ?? '') ?>"
                                                           placeholder="0.00">
                                                </div>
                                            </td>
                                            
                                            <!-- Stock -->
                                            <td class="px-3 py-3" style="width: 70px;">
                                                <input type="number" class="variant-stock w-full px-2 py-1.5 border border-gray-300 rounded text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-center" 
                                                       value="<?= escape($variant['stock_quantity'] ?? 0) ?>"
                                                       placeholder="0">
                                            </td>
                                            
                                            <!-- Status -->
                                            <td class="px-3 py-3" style="width: 100px;">
                                                <?php 
                                                $status = $variant['stock_status'] ?? 'in_stock';
                                                $statusColors = [
                                                    'in_stock' => 'bg-green-100 text-green-700 border-green-300',
                                                    'out_of_stock' => 'bg-red-100 text-red-700 border-red-300',
                                                    'on_order' => 'bg-yellow-100 text-yellow-700 border-yellow-300'
                                                ];
                                                ?>
                                                <select class="variant-stock-status w-full px-2 py-1.5 border rounded text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all <?= $statusColors[$status] ?>">
                                                    <option value="in_stock" <?= $status === 'in_stock' ? 'selected' : '' ?>>In Stock</option>
                                                    <option value="out_of_stock" <?= $status === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                                                    <option value="on_order" <?= $status === 'on_order' ? 'selected' : '' ?>>On Order</option>
                                                </select>
                                            </td>
                                            
                                            <!-- Active -->
                                            <td class="px-3 py-3 text-center" style="width: 60px;">
                                                <input type="checkbox" class="variant-active w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500 cursor-pointer" <?= ($variant['is_active'] ?? 1) ? 'checked' : '' ?>>
                                            </td>
                                            
                                            <!-- Attributes -->
                                            <td class="px-3 py-3" style="width: 200px;">
                                                <div class="variant-attributes">
                                                    <div class="attributes-list space-y-1 max-h-24 overflow-y-auto">
                                                        <?php if (!empty($variant['attributes'])): ?>
                                                            <?php foreach ($variant['attributes'] as $attrName => $attrValue): ?>
                                                                <div class="attribute-row flex gap-1 items-center text-xs">
                                                                    <input type="text" class="attr-name flex-1 px-2 py-1 border border-gray-200 rounded text-xs focus:ring-1 focus:ring-blue-400 focus:border-blue-400" 
                                                                           value="<?= escape($attrName) ?>" placeholder="Name">
                                                                    <span class="text-gray-400">:</span>
                                                                    <input type="text" class="attr-value flex-1 px-2 py-1 border border-gray-200 rounded text-xs focus:ring-1 focus:ring-blue-400 focus:border-blue-400" 
                                                                           value="<?= escape($attrValue) ?>" placeholder="Value">
                                                                    <button type="button" onclick="removeAttribute(this)" class="text-red-500 hover:text-red-700 px-1">
                                                                        <i class="fas fa-times text-xs"></i>
                                                                    </button>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                        <div class="attribute-row flex gap-1 items-center text-xs">
                                                            <input type="text" class="attr-name flex-1 px-2 py-1 border border-gray-200 rounded text-xs focus:ring-1 focus:ring-blue-400 focus:border-blue-400" 
                                                                   placeholder="Name">
                                                            <span class="text-gray-400">:</span>
                                                            <input type="text" class="attr-value flex-1 px-2 py-1 border border-gray-200 rounded text-xs focus:ring-1 focus:ring-blue-400 focus:border-blue-400" 
                                                                   placeholder="Value">
                                                            <button type="button" onclick="removeAttribute(this)" class="text-red-500 hover:text-red-700 px-1">
                                                                <i class="fas fa-times text-xs"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <button type="button" onclick="addAttributeToVariant(this)" class="mt-1 text-xs text-blue-600 hover:text-blue-700 font-medium">
                                                        <i class="fas fa-plus mr-1"></i>Add Attribute
                                                    </button>
                                                </div>
                                            </td>
                                            
                                            <!-- Actions -->
                                            <td class="px-3 py-3 text-center" style="width: 60px;">
                                                <button type="button" onclick="removeVariant(<?= $index ?>)" class="text-red-500 hover:text-red-700 hover:bg-red-50 rounded p-1.5 transition-colors" title="Remove">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
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
            <div class="flex items-center gap-4">
                <h3 class="text-xl font-bold">Select Image</h3>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="multiSelectToggle" onchange="toggleMultiSelect(this.checked)" class="w-4 h-4">
                    <span class="text-sm text-gray-600">Select Multiple</span>
                </label>
                <span id="selectedCount" class="text-sm text-blue-600 font-semibold hidden"></span>
            </div>
            <div class="flex items-center gap-2">
                <button id="addSelectedBtn" onclick="addSelectedImages()" class="btn-primary hidden">
                    <i class="fas fa-check mr-2"></i> Add Selected (<span id="selectedCountBtn">0</span>)
                </button>
                <button onclick="closeImageBrowser()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
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
// Make sure switchTab is available globally
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
    const tabContent = document.getElementById('tab-content-' + tab);
    const tabBtn = document.getElementById('tab-' + tab);
    
    if (tabContent) {
        tabContent.classList.remove('hidden');
    }
    if (tabBtn) {
        tabBtn.classList.add('active', 'border-blue-500', 'text-blue-600');
        tabBtn.classList.remove('border-transparent', 'text-gray-500');
    }
}

// Make it globally available
window.switchTab = switchTab;

// Gallery management
let gallery = <?= json_encode($gallery ?? []) ?>;
let imageBrowserMode = 'main'; // 'main' or 'gallery'
let multiSelectMode = false;
let selectedImages = new Set();

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
    
    grid.innerHTML = filtered.map(img => {
        const isSelected = selectedImages.has(img.filename);
        return `
        <div class="border rounded-lg overflow-hidden hover:shadow-lg cursor-pointer image-browser-item relative ${isSelected ? 'ring-2 ring-blue-500 border-blue-500' : ''}" 
             onclick="handleImageClick('${img.filename}', event)"
             data-filename="${img.filename}">
            ${multiSelectMode ? `
            <div class="absolute top-2 left-2 z-10">
                <input type="checkbox" 
                       class="image-checkbox w-5 h-5 cursor-pointer" 
                       ${isSelected ? 'checked' : ''}
                       onclick="event.stopPropagation(); toggleImageSelection('${img.filename}', this.checked)">
            </div>
            ` : ''}
            <div class="aspect-square bg-gray-100 overflow-hidden">
                <img src="${img.url}" alt="${img.filename}" class="w-full h-full object-cover">
            </div>
            <div class="p-2">
                <p class="text-xs text-gray-600 truncate" title="${img.filename}">${img.filename}</p>
            </div>
        </div>
    `;
    }).join('');
}

function filterImages(search) {
    // Preserve selected images when filtering
    displayImagesInBrowser(search);
}

function toggleMultiSelect(enabled) {
    multiSelectMode = enabled;
    if (!enabled) {
        selectedImages.clear();
        updateSelectedCount();
    }
    displayImagesInBrowser(document.getElementById('imageSearch')?.value || '');
}

function toggleImageSelection(filename, checked) {
    if (checked) {
        selectedImages.add(filename);
    } else {
        selectedImages.delete(filename);
    }
    updateSelectedCount();
    // Update visual state
    const item = document.querySelector(`[data-filename="${filename}"]`);
    if (item) {
        if (checked) {
            item.classList.add('ring-2', 'ring-blue-500', 'border-blue-500');
        } else {
            item.classList.remove('ring-2', 'ring-blue-500', 'border-blue-500');
        }
    }
}

function updateSelectedCount() {
    const count = selectedImages.size;
    const countSpan = document.getElementById('selectedCount');
    const countBtn = document.getElementById('selectedCountBtn');
    const addBtn = document.getElementById('addSelectedBtn');
    
    if (multiSelectMode && (imageBrowserMode === 'gallery' || imageBrowserMode === 'variant-gallery')) {
        if (count > 0) {
            countSpan.textContent = `${count} selected`;
            countSpan.classList.remove('hidden');
            countBtn.textContent = count;
            addBtn.classList.remove('hidden');
        } else {
            countSpan.classList.add('hidden');
            addBtn.classList.add('hidden');
        }
    } else {
        countSpan.classList.add('hidden');
        addBtn.classList.add('hidden');
    }
}

function handleImageClick(filename, event) {
    if (multiSelectMode && (imageBrowserMode === 'gallery' || imageBrowserMode === 'variant-gallery')) {
        // Toggle selection for gallery and variant-gallery in multi-select mode
        const checkbox = event.currentTarget.querySelector('.image-checkbox');
        if (checkbox) {
            checkbox.checked = !checkbox.checked;
            toggleImageSelection(filename, checkbox.checked);
        }
    } else {
        // Single selection mode
        selectImage(filename);
    }
}

function addSelectedImages() {
    if (selectedImages.size === 0) return;
    
    if (imageBrowserMode === 'gallery') {
        // Add all selected images to gallery
        selectedImages.forEach(filename => {
            if (!gallery.includes(filename)) {
                gallery.push(filename);
            }
        });
        updateGalleryDisplay();
        closeImageBrowser();
    } else if (imageBrowserMode === 'variant-gallery' && window.currentVariantGalleryIndex !== undefined) {
        // Add all selected images to variant gallery
        selectedImages.forEach(filename => {
            addToVariantGallery(filename, window.currentVariantGalleryIndex);
        });
        closeImageBrowser();
    }
}

function openImageBrowser(mode) {
    imageBrowserMode = mode;
    multiSelectMode = false;
    selectedImages.clear();
    const toggle = document.getElementById('multiSelectToggle');
    const toggleLabel = toggle?.parentElement;
    
    if (toggle) {
        toggle.checked = false;
        // Show multi-select toggle for gallery and variant-gallery modes
        if (toggleLabel) {
            if (mode === 'gallery' || mode === 'variant-gallery') {
                toggleLabel.classList.remove('hidden');
            } else {
                toggleLabel.classList.add('hidden');
            }
        }
    }
    
    document.getElementById('imageBrowserModal').classList.remove('hidden');
    updateSelectedCount();
    loadImagesForBrowser();
}

function closeImageBrowser() {
    document.getElementById('imageBrowserModal').classList.add('hidden');
    multiSelectMode = false;
    selectedImages.clear();
    document.getElementById('multiSelectToggle').checked = false;
    updateSelectedCount();
}

function selectImage(filename) {
    if (imageBrowserMode === 'main') {
        setMainImage(filename);
        closeImageBrowser();
    } else if (imageBrowserMode === 'variant' && window.currentVariantImageInput) {
        window.currentVariantImageInput.value = filename;
        updateVariantsData();
        closeImageBrowser();
    } else if (imageBrowserMode === 'variant-gallery' && window.currentVariantGalleryIndex !== undefined) {
        addToVariantGallery(filename, window.currentVariantGalleryIndex);
        closeImageBrowser();
    } else {
        addToGallery(filename);
        closeImageBrowser();
    }
}

function addToVariantGallery(filename, variantIndex) {
    const variantItem = document.querySelectorAll('.variant-item')[variantIndex];
    if (!variantItem) return;
    
    const galleryContainer = variantItem.querySelector('.gallery-images');
    const galleryDataInput = variantItem.querySelector('.variant-gallery-data');
    if (!galleryContainer || !galleryDataInput) return;
    
    // Get current gallery
    let currentGallery = [];
    try {
        currentGallery = JSON.parse(galleryDataInput.value) || [];
    } catch (e) {
        currentGallery = [];
    }
    
    // Add new image
    if (!currentGallery.includes(filename)) {
        currentGallery.push(filename);
        
        // Add image preview
        const imgDiv = document.createElement('div');
        imgDiv.className = 'relative group/gallery';
        // Escape filename for use in HTML attribute
        const escapedFilename = filename.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        imgDiv.innerHTML = `
            <img src="<?= asset('storage/uploads/') ?>${escapedFilename}" 
                 alt="Gallery" 
                 class="w-10 h-10 object-cover rounded border border-gray-300 group-hover/gallery:border-blue-400 transition-all"
                 onerror="this.style.display='none'">
            <button type="button" onclick="removeVariantGalleryImage(this, ${variantIndex}, '${escapedFilename}')" 
                    class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-4 h-4 flex items-center justify-center opacity-0 group-hover/gallery:opacity-100 transition-opacity text-xs hover:bg-red-600"
                    title="Remove">
                <i class="fas fa-times"></i>
            </button>
        `;
        galleryContainer.appendChild(imgDiv);
        
        // Update hidden input
        galleryDataInput.value = JSON.stringify(currentGallery);
        updateVariantsData();
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
    // Add variant at the beginning of the array (top of table)
    variants.unshift({
        name: '',
        sku: '',
        price: <?= floatval($product['price'] ?? 0) ?>,
        sale_price: null,
        stock_quantity: 0,
        stock_status: 'in_stock',
        image: '',
        attributes: {},
        gallery: [],
        is_active: 1,
        sort_order: 0
    });
    
    // Update sort_order for all variants
    variants.forEach((variant, index) => {
        variant.sort_order = index;
    });
    
    updateVariantsDisplay();
    switchTab('variants');
    
    // Scroll to top of variants container
    const container = document.getElementById('variantsContainer');
    if (container) {
        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
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
    
    // Update display without reloading page
    if (variants.length === 0) {
        if (container) {
            container.innerHTML = `
                <div class="border-2 border-dashed border-blue-200 rounded-xl p-8 text-center bg-gradient-to-br from-blue-50/50 to-white">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 shadow-lg mb-4 transform hover:scale-110 transition-transform">
                        <i class="fas fa-layer-group text-2xl text-white"></i>
                    </div>
                    <h4 class="text-base font-bold text-gray-900 mb-2">No variants yet</h4>
                    <p class="text-sm text-gray-600 mb-4 max-w-sm mx-auto">Create product variations with different sizes, colors, or options to offer more choices to your customers</p>
                    <button type="button" onclick="addVariant()" class="btn-primary inline-flex items-center gap-2 px-5 py-2.5 rounded-lg shadow-md hover:shadow-lg transition-all hover:scale-105">
                        <i class="fas fa-plus"></i> Create First Variant
                    </button>
                </div>
            `;
        }
        return;
    }
    
    // Check if table exists, if not, create it
    let table = container.querySelector('table');
    let tbody = container.querySelector('tbody');
    
    if (!table) {
        // Create table structure
        container.innerHTML = `
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600" style="width: 80px;">Image</th>
                            <th class="px-2 py-2.5 text-left text-xs font-semibold text-gray-600" style="width: 160px;">
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-images text-blue-600 text-xs"></i>
                                    <span>Gallery</span>
                                </div>
                            </th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600" style="width: 150px;">Name</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600" style="width: 110px;">SKU</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600" style="width: 75px;">Price</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600" style="width: 75px;">Sale</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600" style="width: 70px;">Stock</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600" style="width: 100px;">Status</th>
                            <th class="px-3 py-2.5 text-center text-xs font-semibold text-gray-600" style="width: 60px;">Active</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-600" style="width: 200px;">Attributes</th>
                            <th class="px-3 py-2.5 text-center text-xs font-semibold text-gray-600" style="width: 60px;">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        `;
        tbody = container.querySelector('tbody');
    }
    
    // Clear existing rows
    if (tbody) {
        tbody.innerHTML = '';
    }
    
    // Add all variant rows (newest first at top)
    // Insert in reverse order so newest appears at top
    for (let i = variants.length - 1; i >= 0; i--) {
        const row = createVariantRow(variants[i], i);
        // Insert at the beginning of tbody (top of table)
        if (tbody.firstChild) {
            tbody.insertBefore(row, tbody.firstChild);
        } else {
            tbody.appendChild(row);
        }
    }
    
    // Update variants data
    updateVariantsData();
}

function createVariantRow(variant, index) {
    const row = document.createElement('tr');
    row.className = 'variant-item hover:bg-gray-50 border-b';
    row.setAttribute('data-index', index);
    row.setAttribute('data-variant-id', variant.id || '');
    
    const defaultPrice = <?= floatval($product['price'] ?? 0) ?>;
    const imageUrl = variant.image ? '<?= asset('storage/uploads/') ?>' + variant.image : '';
    
    row.innerHTML = `
        <!-- Image -->
        <td class="px-3 py-3 whitespace-nowrap" style="width: 80px;">
            <div class="flex flex-col items-center gap-1">
                <div class="relative group/image">
                    ${variant.image ? `
                        <img src="${imageUrl}" 
                             alt="Variant" 
                             class="w-12 h-12 object-cover rounded border border-gray-200 shadow-sm group-hover/image:border-blue-400 transition-all"
                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%2748%27 height=%2748%27%3E%3Crect fill=%27%23f3f4f6%27 width=%2748%27 height=%2748%27/%3E%3Ctext fill=%27%239ca3af%27 x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27 font-size=%2710%27%3E%3C/text%3E%3C/svg%3E'">
                    ` : `
                        <div class="w-12 h-12 bg-gray-100 rounded border border-gray-200 flex items-center justify-center">
                            <i class="fas fa-image text-gray-400 text-sm"></i>
                        </div>
                    `}
                    <div class="absolute inset-0 bg-black/0 group-hover/image:bg-black/30 rounded transition-all flex items-center justify-center gap-1 opacity-0 group-hover/image:opacity-100">
                        <button type="button" onclick="openImageBrowserForVariant(this)" class="bg-white rounded p-1 shadow hover:bg-blue-50 text-blue-600 text-xs" title="Browse">
                            <i class="fas fa-folder-open"></i>
                        </button>
                        <label class="bg-white rounded p-1 shadow hover:bg-blue-50 text-blue-600 text-xs cursor-pointer" title="Upload">
                            <i class="fas fa-upload"></i>
                            <input type="file" accept="image/*" class="hidden" onchange="uploadVariantImage(this, ${index})">
                        </label>
                    </div>
                </div>
                <input type="text" class="variant-image hidden" value="${variant.image || ''}">
            </div>
        </td>
        
        <!-- Gallery -->
        <td class="px-2 py-2" style="width: 160px;">
            <div class="variant-gallery bg-blue-50/20 border border-blue-200 rounded p-1.5">
                <div class="flex items-center gap-1 mb-1">
                    <i class="fas fa-images text-blue-600 text-xs"></i>
                    <label class="text-xs font-semibold text-gray-700">Gallery</label>
                    ${variant.gallery && variant.gallery.length > 0 ? `<span class="ml-auto text-xs text-blue-600 font-medium">${variant.gallery.length}</span>` : ''}
                </div>
                
                <div class="gallery-images flex flex-wrap gap-1 max-h-20 overflow-y-auto mb-1.5 p-1 bg-white rounded border border-dashed border-gray-300 min-h-[50px]">
                    ${variant.gallery && variant.gallery.length > 0 ? variant.gallery.map((galleryImage) => {
                        const escapedImage = galleryImage.replace(/"/g, '&quot;').replace(/'/g, '&#39;');
                        return `
                        <div class="relative group/gallery">
                            <img src="<?= asset('storage/uploads/') ?>${escapedImage}" 
                                 alt="Gallery" 
                                 class="w-10 h-10 object-cover rounded border border-gray-300 group-hover/gallery:border-blue-400 transition-all"
                                 onerror="this.style.display='none'">
                            <button type="button" onclick="removeVariantGalleryImage(this, ${index}, '${escapedImage}')" 
                                    class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-4 h-4 flex items-center justify-center opacity-0 group-hover/gallery:opacity-100 transition-opacity text-xs hover:bg-red-600"
                                    title="Remove">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                    }).join('') : `
                        <div class="w-full text-center py-2 text-gray-400 text-xs">
                            <i class="fas fa-image text-lg mb-0.5 block opacity-50"></i>
                            <span>No images</span>
                        </div>
                    `}
                </div>
                
                <div class="flex gap-1">
                    <button type="button" onclick="openImageBrowserForVariantGallery(this, ${index})" 
                            class="flex-1 text-xs text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-1.5 py-1 rounded border border-blue-300 transition-colors flex items-center justify-center gap-1 font-medium">
                        <i class="fas fa-folder-open text-xs"></i>
                        <span>Browse</span>
                    </button>
                    <label class="flex-1 text-xs text-green-600 hover:text-green-700 bg-green-50 hover:bg-green-100 px-1.5 py-1 rounded border border-green-300 transition-colors flex items-center justify-center gap-1 font-medium cursor-pointer">
                        <i class="fas fa-upload text-xs"></i>
                        <span>Upload</span>
                        <input type="file" accept="image/*" multiple class="hidden" onchange="uploadVariantGalleryImages(this, ${index})">
                    </label>
                </div>
                
                <input type="hidden" class="variant-gallery-data" value="${JSON.stringify(variant.gallery || []).replace(/"/g, '&quot;')}">
            </div>
        </td>
        
        <!-- Name -->
        <td class="px-3 py-3" style="width: 150px;">
            <input type="text" class="variant-name w-full px-2 py-1.5 border border-gray-300 rounded text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400" 
                   value="${(variant.name || '').replace(/"/g, '&quot;')}"
                   placeholder="Variant name">
            <input type="text" class="variant-name-header hidden" value="${(variant.name || '').replace(/"/g, '&quot;')}">
        </td>
        
        <!-- SKU -->
        <td class="px-3 py-3" style="width: 110px;">
            <input type="text" class="variant-sku w-full px-2 py-1.5 border border-gray-300 rounded text-sm text-gray-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-gray-400 font-mono" 
                   value="${(variant.sku || '').replace(/"/g, '&quot;')}"
                   placeholder="SKU">
        </td>
        
        <!-- Price -->
        <td class="px-2 py-3" style="width: 75px;">
            <div class="relative">
                <span class="absolute left-1.5 top-1.5 text-gray-500 text-xs">$</span>
                <input type="number" step="0.01" class="variant-price w-full pl-4 pr-1 py-1.5 border border-gray-300 rounded text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                       value="${variant.price || defaultPrice}"
                       placeholder="0.00">
            </div>
        </td>
        
        <!-- Sale Price -->
        <td class="px-2 py-3" style="width: 75px;">
            <div class="relative">
                <span class="absolute left-1.5 top-1.5 text-gray-500 text-xs">$</span>
                <input type="number" step="0.01" class="variant-sale-price w-full pl-4 pr-1 py-1.5 border border-gray-300 rounded text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" 
                       value="${String(variant.sale_price || '').replace(/"/g, '&quot;')}"
                       placeholder="0.00">
            </div>
        </td>
        
        <!-- Stock -->
        <td class="px-3 py-3" style="width: 70px;">
            <input type="number" class="variant-stock w-full px-2 py-1.5 border border-gray-300 rounded text-sm text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-center" 
                   value="${variant.stock_quantity || 0}"
                   placeholder="0">
        </td>
        
        <!-- Status -->
        <td class="px-3 py-3" style="width: 100px;">
            <select class="variant-stock-status w-full px-2 py-1.5 border rounded text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all ${variant.stock_status === 'in_stock' ? 'bg-green-100 text-green-700 border-green-300' : variant.stock_status === 'out_of_stock' ? 'bg-red-100 text-red-700 border-red-300' : 'bg-yellow-100 text-yellow-700 border-yellow-300'}">
                <option value="in_stock" ${variant.stock_status === 'in_stock' ? 'selected' : ''}>In Stock</option>
                <option value="out_of_stock" ${variant.stock_status === 'out_of_stock' ? 'selected' : ''}>Out of Stock</option>
                <option value="on_order" ${variant.stock_status === 'on_order' ? 'selected' : ''}>On Order</option>
            </select>
        </td>
        
        <!-- Active -->
        <td class="px-3 py-3 text-center" style="width: 60px;">
            <input type="checkbox" class="variant-active w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500 cursor-pointer" ${variant.is_active ? 'checked' : ''}>
        </td>
        
        <!-- Attributes -->
        <td class="px-3 py-3" style="width: 200px;">
            <div class="variant-attributes">
                <div class="attributes-list space-y-1 max-h-24 overflow-y-auto">
                    ${Object.keys(variant.attributes || {}).map(attrName => `
                        <div class="attribute-row flex gap-1 items-center text-xs">
                            <input type="text" class="attr-name flex-1 px-2 py-1 border border-gray-200 rounded text-xs focus:ring-1 focus:ring-blue-400 focus:border-blue-400" 
                                   value="${attrName.replace(/"/g, '&quot;')}" placeholder="Name">
                            <span class="text-gray-400">:</span>
                            <input type="text" class="attr-value flex-1 px-2 py-1 border border-gray-200 rounded text-xs focus:ring-1 focus:ring-blue-400 focus:border-blue-400" 
                                   value="${(variant.attributes[attrName] || '').replace(/"/g, '&quot;')}" placeholder="Value">
                            <button type="button" onclick="removeAttribute(this)" class="text-red-500 hover:text-red-700 px-1">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    `).join('')}
                    <div class="attribute-row flex gap-1 items-center text-xs">
                        <input type="text" class="attr-name flex-1 px-2 py-1 border border-gray-200 rounded text-xs focus:ring-1 focus:ring-blue-400 focus:border-blue-400" 
                               placeholder="Name">
                        <span class="text-gray-400">:</span>
                        <input type="text" class="attr-value flex-1 px-2 py-1 border border-gray-200 rounded text-xs focus:ring-1 focus:ring-blue-400 focus:border-blue-400" 
                               placeholder="Value">
                        <button type="button" onclick="removeAttribute(this)" class="text-red-500 hover:text-red-700 px-1">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                </div>
                <button type="button" onclick="addAttributeToVariant(this)" class="mt-1 text-xs text-blue-600 hover:text-blue-700 font-medium">
                    <i class="fas fa-plus mr-1"></i>Add Attribute
                </button>
            </div>
        </td>
        
        <!-- Actions -->
        <td class="px-3 py-3 text-center" style="width: 60px;">
            <button type="button" onclick="removeVariant(${index})" class="text-red-500 hover:text-red-700 hover:bg-red-50 rounded p-1.5 transition-colors" title="Remove">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    return row;
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

function updateVariantNameHeader(input, index) {
    // Sync header input with the variant-name input (for backward compatibility)
    const variantItem = input.closest('.variant-item');
    const nameInput = variantItem?.querySelector('.variant-name');
    if (nameInput) {
        nameInput.value = input.value;
    }
    updateVariantsData();
}

function syncVariantName(input, index) {
    // Sync variant-name input with header input (for backward compatibility)
    const variantItem = input.closest('.variant-item');
    const headerInput = variantItem?.querySelector('.variant-name-header');
    if (headerInput) {
        headerInput.value = input.value;
    }
    updateVariantsData();
}

function updateVariantsData() {
    const variantItems = document.querySelectorAll('.variant-item');
    variants = [];
    
    variantItems.forEach((item, index) => {
        const variantId = item.dataset.variantId || null;
        // Get name from either header or name input (prefer name input)
        const nameInput = item.querySelector('.variant-name') || item.querySelector('.variant-name-header');
        const variant = {
            id: variantId ? parseInt(variantId) : null,
            name: nameInput?.value || '',
            sku: item.querySelector('.variant-sku')?.value || '',
            price: parseFloat(item.querySelector('.variant-price')?.value || 0),
            sale_price: item.querySelector('.variant-sale-price')?.value ? parseFloat(item.querySelector('.variant-sale-price').value) : null,
            stock_quantity: parseInt(item.querySelector('.variant-stock')?.value || 0),
            stock_status: item.querySelector('.variant-stock-status')?.value || 'in_stock',
            image: item.querySelector('.variant-image')?.value || '',
            gallery: [],
            attributes: {},
            is_active: item.querySelector('.variant-active')?.checked ? 1 : 0,
            sort_order: index
        };
        
        // Get gallery images
        const galleryDataInput = item.querySelector('.variant-gallery-data');
        if (galleryDataInput && galleryDataInput.value) {
            try {
                variant.gallery = JSON.parse(galleryDataInput.value) || [];
            } catch (e) {
                variant.gallery = [];
            }
        }
        
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

function uploadVariantImage(input, variantIndex) {
    const file = input.files[0];
    if (!file) return;
    
    // Get variant item from input's parent
    const variantItem = input.closest('.variant-item');
    if (!variantItem) return;
    
    const imageInput = variantItem.querySelector('.variant-image');
    if (!imageInput) return;
    
    const formData = new FormData();
    formData.append('file', file);
    
    // Show loading state
    const originalValue = imageInput.value;
    imageInput.value = 'Uploading...';
    imageInput.disabled = true;
    
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
        if (data.success && data.file) {
            imageInput.value = data.file;
            updateVariantsData();
            
            // Update preview if exists
            const preview = variantItem.querySelector('img');
            if (preview && data.url) {
                preview.src = data.url;
            } else if (preview) {
                preview.src = '<?= asset('storage/uploads/') ?>' + data.file;
            } else {
                // Create preview if it doesn't exist
                const imageContainer = variantItem.querySelector('.variant-image').parentElement.parentElement;
                if (imageContainer) {
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'mt-2';
                    previewDiv.innerHTML = `<img src="${data.url || '<?= asset('storage/uploads/') ?>' + data.file}" alt="Variant image" class="max-w-32 max-h-32 object-contain rounded border" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27100%27 height=%27100%27%3E%3Crect fill=%27%23ddd%27 width=%27100%27 height=%27100%27/%3E%3Ctext fill=%27%23999%27 x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27%3EBroken%3C/text%3E%3C/svg%3E'">`;
                    imageContainer.appendChild(previewDiv);
                }
            }
        } else {
            throw new Error(data.message || 'Upload failed');
        }
    })
    .catch(error => {
        alert('Upload failed: ' + error.message);
        imageInput.value = originalValue;
        console.error('Upload error:', error);
    })
    .finally(() => {
        imageInput.disabled = false;
        input.value = ''; // Reset file input
    });
}

// Variant Gallery Functions
function openImageBrowserForVariantGallery(button, variantIndex) {
    window.currentVariantGalleryIndex = variantIndex;
    imageBrowserMode = 'variant-gallery';
    openImageBrowser('variant-gallery');
}

function uploadVariantGalleryImages(input, variantIndex) {
    const files = input.files;
    if (!files || files.length === 0) return;
    
    const variantItem = document.querySelectorAll('.variant-item')[variantIndex];
    if (!variantItem) return;
    
    const galleryContainer = variantItem.querySelector('.gallery-images');
    if (!galleryContainer) return;
    
    // Show loading indicator
    const loadingMsg = document.createElement('div');
    loadingMsg.className = 'w-full text-center py-2 text-blue-600 text-xs';
    loadingMsg.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Uploading...';
    galleryContainer.appendChild(loadingMsg);
    
    const formData = new FormData();
    for (let i = 0; i < files.length; i++) {
        formData.append('files[]', files[i]);
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
        loadingMsg.remove();
        
        if (data.success && data.files && data.files.length > 0) {
            data.files.forEach(file => {
                addToVariantGallery(file.filename, variantIndex);
            });
            // Reload images for browser
            loadImagesForBrowser();
        } else if (data.success && data.file) {
            // Single file response (backward compatibility)
            addToVariantGallery(data.file, variantIndex);
            loadImagesForBrowser();
        } else {
            throw new Error(data.message || 'Upload failed');
        }
    })
    .catch(error => {
        loadingMsg.remove();
        alert('Upload failed: ' + error.message);
        console.error('Upload error:', error);
    })
    .finally(() => {
        input.value = ''; // Reset file input
    });
}

function removeVariantGalleryImage(button, variantIndex, filename) {
    const variantItem = document.querySelectorAll('.variant-item')[variantIndex];
    if (!variantItem) return;
    
    const galleryDataInput = variantItem.querySelector('.variant-gallery-data');
    if (!galleryDataInput) return;
    
    // Get current gallery
    let currentGallery = [];
    try {
        currentGallery = JSON.parse(galleryDataInput.value) || [];
    } catch (e) {
        currentGallery = [];
    }
    
    // Find and remove image by filename (more reliable than index)
    const imageIndex = currentGallery.indexOf(filename);
    if (imageIndex !== -1) {
        currentGallery.splice(imageIndex, 1);
        
        // Update hidden input
        galleryDataInput.value = JSON.stringify(currentGallery);
        
        // Remove image element from DOM
        const imageElement = button.closest('.relative.group\\/gallery');
        if (imageElement) {
            imageElement.remove();
        }
        
        // If gallery is empty, show empty state
        const galleryContainer = variantItem.querySelector('.gallery-images');
        if (galleryContainer && currentGallery.length === 0) {
            galleryContainer.innerHTML = `
                <div class="w-full text-center py-2 text-gray-400 text-xs">
                    <i class="fas fa-image text-lg mb-0.5 block opacity-50"></i>
                    <span>No images</span>
                </div>
            `;
        }
        
        updateVariantsData();
    }
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

/* Variant Table - Clean & Simple */
#variantsContainer table {
    border-collapse: collapse;
    width: 100%;
}

#variantsContainer thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #f9fafb;
    border-bottom: 2px solid #e5e7eb;
}

#variantsContainer tbody tr {
    transition: background-color 0.15s ease;
}

#variantsContainer tbody tr:hover {
    background: #f9fafb;
}

#variantsContainer tbody td {
    vertical-align: middle;
    border-bottom: 1px solid #f3f4f6;
}

#variantsContainer input[type="text"],
#variantsContainer input[type="number"],
#variantsContainer select {
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

#variantsContainer input[type="text"]:focus,
#variantsContainer input[type="number"]:focus,
#variantsContainer select:focus {
    outline: none;
}

#variantsContainer .attributes-list {
    max-height: 120px;
    overflow-y: auto;
}

#variantsContainer .attributes-list::-webkit-scrollbar {
    width: 4px;
}

#variantsContainer .attributes-list::-webkit-scrollbar-track {
    background: #f1f1f1;
}

#variantsContainer .attributes-list::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 2px;
}

#variantsContainer tbody tr:last-child td {
    border-bottom: none;
}

#variantsContainer input[type="text"],
#variantsContainer input[type="number"],
#variantsContainer select {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 0.75rem;
    border-width: 1.5px;
}

#variantsContainer input[type="text"]:focus,
#variantsContainer input[type="number"]:focus,
#variantsContainer select:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1), 0 1px 2px rgba(0, 0, 0, 0.05);
    outline: none;
    transform: translateY(-1px);
}

#variantsContainer input[type="text"]:hover:not(:focus),
#variantsContainer input[type="number"]:hover:not(:focus),
#variantsContainer select:hover:not(:focus) {
    border-color: #cbd5e1;
}

#variantsContainer .attribute-row {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

#variantsContainer .attribute-row:hover {
    background: linear-gradient(to right, #f3f4f6, #e5e7eb);
    transform: translateX(2px);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

/* Status Badge Styling */
#variantsContainer select.variant-stock-status {
    font-weight: 600;
    text-transform: capitalize;
}

#variantsContainer select.variant-stock-status option {
    background: white;
    color: #374151;
}

/* Image Hover Effects */
#variantsContainer .group\/image:hover img {
    transform: scale(1.1);
}

#variantsContainer .group\/image:hover .fa-image {
    transform: scale(1.2);
}

/* Checkbox Styling */
#variantsContainer input[type="checkbox"] {
    accent-color: #3b82f6;
    cursor: pointer;
}

#variantsContainer input[type="checkbox"]:checked {
    background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L4 12.586l7.793-7.793a1 1 0 011.414 0z'/%3e%3c/svg%3e");
}

/* Scrollbar styling for attributes */
#variantsContainer .attributes-list::-webkit-scrollbar {
    width: 4px;
}

#variantsContainer .attributes-list::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

#variantsContainer .attributes-list::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, #cbd5e1, #94a3b8);
    border-radius: 4px;
}

#variantsContainer .attributes-list::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #94a3b8, #64748b);
}

/* Button Enhancements */
#variantsContainer button {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

#variantsContainer button:hover {
    transform: translateY(-1px);
}

#variantsContainer button:active {
    transform: translateY(0);
}

/* Responsive Design */
@media (max-width: 1280px) {
    #variantsContainer table {
        font-size: 0.7rem;
    }
    
    #variantsContainer thead th {
        padding: 0.6rem 0.6rem;
        font-size: 0.65rem;
    }
    
    #variantsContainer tbody td {
        padding: 0.6rem;
    }
}

@media (max-width: 768px) {
    #variantsContainer {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: 0.5rem;
    }
    
    #variantsContainer table {
        min-width: 1000px;
    }
    
    #variantsContainer thead th:nth-child(n+6),
    #variantsContainer tbody td:nth-child(n+6) {
        display: none;
    }
    
    #variantsContainer thead th:nth-child(-n+5),
    #variantsContainer tbody td:nth-child(-n+5) {
        display: table-cell;
    }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>
