<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/header.php';

use App\Services\UnForkliftScraper;
use App\Services\SmartProductImporter;
use App\Models\Product;
use App\Models\Category;
use App\Database\Connection;

$scraper = new UnForkliftScraper();
$smartImporter = new SmartProductImporter();
$productModel = new Product();
$categoryModel = new Category();
$db = Connection::getInstance();

$message = '';
$error = '';
$importResults = [];
$previewData = null;
$extractionResult = null;

// Handle smart import (preview/extract)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['smart_extract'])) {
    try {
        $productUrl = trim($_POST['smart_url'] ?? '');
        $forceAI = isset($_POST['force_ai']);
        
        if (empty($productUrl)) {
            throw new Exception('Product URL is required');
        }
        
        if (!filter_var($productUrl, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid URL format');
        }
        
        // Extract product data
        $extractionResult = $smartImporter->extractProductData($productUrl, [
            'force_ai' => $forceAI
        ]);
        
        if ($extractionResult['success']) {
            $previewData = $extractionResult['data'];
            $message = "Product data extracted successfully! Confidence: {$extractionResult['confidence']}% (Method: {$extractionResult['method']})";
        } else {
            $error = 'Failed to extract product data: ' . implode(', ', $extractionResult['errors']);
        }
        
    } catch (Exception $e) {
        $error = 'Extraction error: ' . $e->getMessage();
    }
}

// Handle smart import (save to database)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['smart_import_save'])) {
    try {
        $productData = json_decode($_POST['product_data'] ?? '{}', true);
        $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $downloadImages = isset($_POST['download_images']);
        
        if (empty($productData['name'])) {
            throw new Exception('Product name is required');
        }
        
        // Generate slug
        $slug = generateSlug($productData['name']);
        
        // Check if product already exists
        $existing = $db->fetchOne(
            "SELECT id FROM products WHERE name = :name OR slug = :slug",
            ['name' => $productData['name'], 'slug' => $slug]
        );
        
        if ($existing) {
            $error = 'Product already exists in database';
        } else {
            // Get or create category
            if (!$categoryId) {
                $dbCategory = $categoryModel->getBySlug('uncategorized');
                if (!$dbCategory) {
                    $categoryData = [
                        'name' => 'Uncategorized',
                        'slug' => 'uncategorized',
                        'description' => 'Imported products',
                        'is_active' => 1,
                        'sort_order' => 999,
                    ];
                    $categoryId = $db->insert('categories', $categoryData);
                } else {
                    $categoryId = $dbCategory['id'];
                }
            }
            
            // Handle images
            $imagePath = '';
            $gallery = [];
            
            if ($downloadImages && !empty($productData['images'])) {
                $uploadDir = __DIR__ . '/../storage/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                // Download main image
                if (!empty($productData['images'][0])) {
                    $mainImage = $productData['images'][0];
                    $imageExt = pathinfo(parse_url($mainImage, PHP_URL_PATH), PATHINFO_EXTENSION);
                    $imageExt = $imageExt ?: 'jpg';
                    $imageFileName = 'smart_' . time() . '_' . rand(1000, 9999) . '.' . $imageExt;
                    $imageFullPath = $uploadDir . $imageFileName;
                    
                    if ($smartImporter->downloadImage($mainImage, $imageFullPath)) {
                        $imagePath = $imageFileName;
                    }
                }
                
                // Download gallery images
                for ($i = 1; $i < min(5, count($productData['images'])); $i++) {
                    $galleryImage = $productData['images'][$i];
                    $galleryExt = pathinfo(parse_url($galleryImage, PHP_URL_PATH), PATHINFO_EXTENSION);
                    $galleryExt = $galleryExt ?: 'jpg';
                    $galleryFileName = 'smart_' . time() . '_' . rand(1000, 9999) . '_' . $i . '.' . $galleryExt;
                    $galleryFullPath = $uploadDir . $galleryFileName;
                    
                    if ($smartImporter->downloadImage($galleryImage, $galleryFullPath)) {
                        $gallery[] = $galleryFileName;
                    }
                }
            } else {
                // Just store URLs
                if (!empty($productData['images'])) {
                    $imagePath = $productData['images'][0];
                    $gallery = array_slice($productData['images'], 1, 4);
                }
            }
            
            // Insert product
            $productInsert = [
                'name' => $productData['name'],
                'slug' => $slug,
                'sku' => $productData['sku'] ?: 'SMART-' . strtoupper(substr($slug, 0, 8)) . '-' . time(),
                'description' => $productData['description'] ?: $productData['short_description'],
                'short_description' => $productData['short_description'] ?: mb_substr(strip_tags($productData['description']), 0, 200),
                'category_id' => $categoryId,
                'image' => $imagePath,
                'gallery' => !empty($gallery) ? json_encode($gallery) : null,
                'specifications' => !empty($productData['specifications']) ? json_encode($productData['specifications']) : null,
                'features' => !empty($productData['features']) ? implode("\n", $productData['features']) : null,
                'stock_status' => 'on_order',
                'is_active' => 1,
                'is_featured' => 0,
                'meta_title' => $productData['name'],
                'meta_description' => $productData['short_description'],
            ];
            
            $productId = $db->insert('products', $productInsert);
            $message = "Product imported successfully! <a href='" . url("admin/product-edit.php?id=$productId") . "' class='underline font-semibold'>View/Edit Product</a>";
            $previewData = null; // Clear preview after import
        }
        
    } catch (Exception $e) {
        $error = 'Import error: ' . $e->getMessage();
    }
}

// Handle import from URL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import_url'])) {
    try {
        $productUrl = trim($_POST['product_url'] ?? '');
        $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $downloadImages = isset($_POST['download_images_url']);
        
        if (empty($productUrl)) {
            throw new Exception('Product URL is required');
        }
        
        if (!filter_var($productUrl, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid URL format');
        }
        
        // Extract product details
        $productData = $scraper->extractProductDetails($productUrl);
        
        if (!$productData || empty($productData['name'])) {
            throw new Exception('Could not extract product information from the URL');
        }
        
        // Generate slug
        $slug = generateSlug($productData['name']);
        
        // Check if product already exists
        $existing = $db->fetchOne(
            "SELECT id FROM products WHERE name = :name OR slug = :slug",
            ['name' => $productData['name'], 'slug' => $slug]
        );
        
        if ($existing) {
            $error = 'Product already exists in database';
        } else {
            // Get or use provided category
            if (!$categoryId) {
                // Try to find a matching category or create a default one
                $dbCategory = $categoryModel->getBySlug('uncategorized');
                if (!$dbCategory) {
                    $categoryData = [
                        'name' => 'Uncategorized',
                        'slug' => 'uncategorized',
                        'description' => 'Imported products',
                        'is_active' => 1,
                        'sort_order' => 999,
                    ];
                    $categoryId = $db->insert('categories', $categoryData);
                } else {
                    $categoryId = $dbCategory['id'];
                }
            }
            
            // Handle images
            $imagePath = '';
            $gallery = [];
            
            if ($downloadImages && !empty($productData['images'])) {
                $uploadDir = __DIR__ . '/../storage/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $mainImage = $productData['images'][0];
                $imageExt = pathinfo(parse_url($mainImage, PHP_URL_PATH), PATHINFO_EXTENSION);
                $imageExt = $imageExt ?: 'jpg';
                $imageFileName = 'unforklift_' . time() . '_' . rand(1000, 9999) . '.' . $imageExt;
                $imageFullPath = $uploadDir . $imageFileName;
                
                if ($scraper->downloadImage($mainImage, $imageFullPath)) {
                    $imagePath = $imageFileName;
                }
                
                for ($i = 1; $i < min(5, count($productData['images'])); $i++) {
                    $galleryImage = $productData['images'][$i];
                    $galleryExt = pathinfo(parse_url($galleryImage, PHP_URL_PATH), PATHINFO_EXTENSION);
                    $galleryExt = $galleryExt ?: 'jpg';
                    $galleryFileName = 'unforklift_' . time() . '_' . rand(1000, 9999) . '_' . $i . '.' . $galleryExt;
                    $galleryFullPath = $uploadDir . $galleryFileName;
                    
                    if ($scraper->downloadImage($galleryImage, $galleryFullPath)) {
                        $gallery[] = $galleryFileName;
                    }
                }
            } else {
                if (!empty($productData['images'])) {
                    $imagePath = $productData['images'][0];
                    $gallery = array_slice($productData['images'], 1, 4);
                }
            }
            
            // Insert product
            $productInsert = [
                'name' => $productData['name'],
                'slug' => $slug,
                'sku' => 'UN-' . strtoupper(substr($slug, 0, 8)) . '-' . time(),
                'description' => $productData['description'] ?: $productData['short_description'],
                'short_description' => $productData['short_description'] ?: mb_substr(strip_tags($productData['description']), 0, 200),
                'category_id' => $categoryId,
                'image' => $imagePath,
                'gallery' => !empty($gallery) ? json_encode($gallery) : null,
                'specifications' => !empty($productData['specifications']) ? json_encode($productData['specifications']) : null,
                'features' => !empty($productData['features']) ? implode("\n", $productData['features']) : null,
                'stock_status' => 'on_order',
                'is_active' => 1,
                'is_featured' => 0,
                'meta_title' => $productData['name'],
                'meta_description' => $productData['short_description'],
            ];
            
            $productId = $db->insert('products', $productInsert);
            $message = "Product imported successfully! <a href='" . url("admin/product-edit.php?id=$productId") . "'>View/Edit Product</a>";
        }
        
    } catch (Exception $e) {
        $error = 'Import error: ' . $e->getMessage();
    }
}

// Handle import request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import'])) {
    try {
        $categorySlug = $_POST['category'] ?? '';
        $downloadImages = isset($_POST['download_images']);
        $importCount = 0;
        $errorCount = 0;
        $skippedCount = 0;
        
        // Get all categories from scraper
        $categories = $scraper->getProductCategories();
        
        // Find the selected category
        $selectedCategory = null;
        foreach ($categories as $cat) {
            if ($cat['slug'] === $categorySlug) {
                $selectedCategory = $cat;
                break;
            }
        }
        
        if (!$selectedCategory) {
            throw new Exception('Invalid category selected');
        }
        
        // Get or create category in database
        $dbCategory = $categoryModel->getBySlug($categorySlug);
        if (!$dbCategory) {
            // Create category
            $categoryData = [
                'name' => $selectedCategory['name'],
                'slug' => $categorySlug,
                'description' => 'Imported from UN Forklift',
                'is_active' => 1,
                'sort_order' => 0,
            ];
            $categoryId = $db->insert('categories', $categoryData);
            $dbCategory = $categoryModel->getById($categoryId);
        }
        
        // Extract products from category page
        try {
            $products = $scraper->extractProductsFromCategory($selectedCategory['url']);
        } catch (Exception $e) {
            $error = 'Failed to extract products: ' . $e->getMessage() . 
                     '<br><br><strong>Tip:</strong> Try using the "Import from Product URL" method on the right to import individual products. ' .
                     'You can find product URLs by visiting <a href="https://www.unforklift.com/product/" target="_blank">UN Forklift product page</a> and clicking on individual products.';
            $products = [];
        }
        
        if (empty($products)) {
            if (empty($error)) {
                $error = 'No products found on the category page. The website structure may have changed. ' .
                         '<br><br><strong>Tip:</strong> Use the "Import from Product URL" method to import individual products instead.';
            }
        } else {
            foreach ($products as $productData) {
                try {
                    // Generate slug
                    $slug = generateSlug($productData['name']);
                    
                    // Check if product already exists (by name or slug)
                    $existing = $db->fetchOne(
                        "SELECT id FROM products WHERE name = :name OR slug = :slug",
                        [
                            'name' => $productData['name'],
                            'slug' => $slug
                        ]
                    );
                    
                    if ($existing) {
                        $skippedCount++;
                        continue;
                    }
                    
                    // Handle images
                    $imagePath = '';
                    $gallery = [];
                    
                    if ($downloadImages && !empty($productData['images'])) {
                        // Ensure upload directory exists
                        $uploadDir = __DIR__ . '/../storage/uploads/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        
                        // Download main image
                        $mainImage = $productData['images'][0];
                        $imageExt = pathinfo(parse_url($mainImage, PHP_URL_PATH), PATHINFO_EXTENSION);
                        $imageExt = $imageExt ?: 'jpg';
                        $imageFileName = 'unforklift_' . time() . '_' . rand(1000, 9999) . '.' . $imageExt;
                        $imageFullPath = $uploadDir . $imageFileName;
                        
                        if ($scraper->downloadImage($mainImage, $imageFullPath)) {
                            $imagePath = $imageFileName; // Store just filename, relative to storage/uploads/
                        } else {
                            $imagePath = '';
                        }
                        
                        // Download gallery images
                        for ($i = 1; $i < min(5, count($productData['images'])); $i++) {
                            $galleryImage = $productData['images'][$i];
                            $galleryExt = pathinfo(parse_url($galleryImage, PHP_URL_PATH), PATHINFO_EXTENSION);
                            $galleryExt = $galleryExt ?: 'jpg';
                            $galleryFileName = 'unforklift_' . time() . '_' . rand(1000, 9999) . '_' . $i . '.' . $galleryExt;
                            $galleryFullPath = $uploadDir . $galleryFileName;
                            
                            if ($scraper->downloadImage($galleryImage, $galleryFullPath)) {
                                $gallery[] = $galleryFileName; // Store just filename
                            }
                        }
                    } else {
                        // Just store URLs
                        if (!empty($productData['images'])) {
                            $imagePath = $productData['images'][0];
                            $gallery = array_slice($productData['images'], 1, 4);
                        }
                    }
                    
                    // Prepare product data
                    $productInsert = [
                        'name' => $productData['name'],
                        'slug' => $slug,
                        'sku' => 'UN-' . strtoupper(substr($slug, 0, 8)) . '-' . time(),
                        'description' => $productData['description'] ?: $productData['short_description'],
                        'short_description' => $productData['short_description'] ?: mb_substr(strip_tags($productData['description']), 0, 200),
                        'category_id' => $dbCategory['id'],
                        'image' => $imagePath,
                        'gallery' => !empty($gallery) ? json_encode($gallery) : null,
                        'specifications' => !empty($productData['specifications']) ? json_encode($productData['specifications']) : null,
                        'features' => !empty($productData['features']) ? implode("\n", $productData['features']) : null,
                        'stock_status' => 'on_order', // Products from supplier are typically on order
                        'is_active' => 1,
                        'is_featured' => 0,
                        'meta_title' => $productData['name'],
                        'meta_description' => $productData['short_description'],
                    ];
                    
                    // Insert product
                    $productId = $db->insert('products', $productInsert);
                    $importCount++;
                    
                    $importResults[] = [
                        'status' => 'success',
                        'name' => $productData['name'],
                        'id' => $productId
                    ];
                    
                } catch (Exception $e) {
                    $errorCount++;
                    $importResults[] = [
                        'status' => 'error',
                        'name' => $productData['name'] ?? 'Unknown',
                        'error' => $e->getMessage()
                    ];
                }
            }
            
            $message = "Import completed! Imported: $importCount, Errors: $errorCount, Skipped: $skippedCount";
        }
        
    } catch (Exception $e) {
        $error = 'Import error: ' . $e->getMessage();
    }
}

// Helper function to generate slug
function generateSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

// Get available categories
$availableCategories = $scraper->getProductCategories();

$pageTitle = 'Import Products from UN Forklift';
?>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="bg-gradient-to-r from-teal-600 to-cyan-600 rounded-xl shadow-xl p-4 md:p-6 lg:p-8 mb-4 md:mb-6 text-white">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-1 md:mb-2">
                    <i class="fas fa-download mr-2 md:mr-3"></i>
                    Import Products from UN Forklift
                </h1>
                <p class="text-teal-100 text-sm md:text-lg">Import products from UN Forklift website to your database</p>
            </div>
            <div class="bg-white/20 rounded-full px-4 md:px-6 py-2 md:py-3 backdrop-blur-sm">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-external-link-alt"></i>
                    <span class="font-semibold text-sm md:text-base">unforklift.com</span>
                </div>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-4 md:mb-6">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2 text-xl"></i>
            <span class="font-semibold"><?= escape($message) ?></span>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-4 md:mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2 text-xl"></i>
            <span class="font-semibold"><?= escape($error) ?></span>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Smart Import from Any URL -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden border-2 border-purple-200 mb-4 md:mb-6">
        <div class="bg-gradient-to-r from-purple-500 via-pink-500 to-purple-600 p-4 md:p-6 text-white">
            <h2 class="text-xl md:text-2xl font-bold flex items-center">
                <i class="fas fa-magic mr-3"></i>
                Smart Import from Any Website
            </h2>
            <p class="text-purple-100 text-sm md:text-base mt-2">AI-powered product extraction from any e-commerce website</p>
        </div>
        <div class="p-4 md:p-6">
            <form method="POST" action="" id="smartImportForm">
                <div class="mb-4 md:mb-6">
                    <label for="smart_url" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-globe text-purple-600 mr-2"></i>Product URL (Any Website)
                    </label>
                    <input type="url" 
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all" 
                           id="smart_url" 
                           name="smart_url" 
                           placeholder="https://example.com/product/..." 
                           value="<?= escape($_POST['smart_url'] ?? '') ?>"
                           required>
                    <p class="text-xs md:text-sm text-gray-600 mt-2">
                        Paste any product page URL from any e-commerce website. Our AI will analyze and extract product information.
                    </p>
                </div>
                
                <div class="mb-4 md:mb-6">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500" id="force_ai" name="force_ai">
                        <span class="ml-3 text-sm font-medium text-gray-700">Force AI extraction (skip pattern recognition)</span>
                    </label>
                    <p class="text-xs text-gray-600 mt-1 ml-8">
                        Enable this to use AI directly. Otherwise, pattern recognition will be tried first.
                    </p>
                </div>
                
                <button type="submit" name="smart_extract" class="w-full bg-gradient-to-r from-purple-600 via-pink-600 to-purple-600 text-white px-6 py-3 rounded-lg font-bold hover:from-purple-700 hover:via-pink-700 hover:to-purple-700 transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                    <i class="fas fa-magic mr-2"></i> Extract Product Data
                </button>
            </form>
        </div>
    </div>
    
    <!-- Preview/Edit Extracted Data -->
    <?php if ($previewData): ?>
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border-2 border-green-200 mb-4 md:mb-6">
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-4 md:p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl md:text-2xl font-bold flex items-center">
                            <i class="fas fa-eye mr-3"></i>
                            Extracted Product Data
                        </h2>
                        <p class="text-green-100 text-sm md:text-base mt-2">
                            Confidence: <strong><?= $extractionResult['confidence'] ?>%</strong> | 
                            Method: <strong><?= ucfirst($extractionResult['method']) ?></strong>
                            <?php if ($extractionResult['pattern_confidence'] > 0): ?>
                                | Pattern: <?= $extractionResult['pattern_confidence'] ?>%
                            <?php endif; ?>
                            <?php if ($extractionResult['ai_confidence'] > 0): ?>
                                | AI: <?= $extractionResult['ai_confidence'] ?>%
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="bg-white/20 rounded-full px-4 py-2">
                        <i class="fas fa-check-circle text-2xl"></i>
                    </div>
                </div>
            </div>
            <div class="p-4 md:p-6">
                <form method="POST" action="" id="smartImportSaveForm">
                    <input type="hidden" name="product_data" id="product_data_json" value="<?= escape(json_encode($previewData)) ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mb-4 md:mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Product Name *</label>
                            <input type="text" 
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                   id="preview_name" 
                                   value="<?= escape($previewData['name'] ?? '') ?>"
                                   required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Price</label>
                            <input type="number" 
                                   step="0.01" 
                                   class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                   id="preview_price" 
                                   value="<?= escape($previewData['price'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="mb-4 md:mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Short Description</label>
                        <textarea class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                  id="preview_short_desc" 
                                  rows="2"><?= escape($previewData['short_description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="mb-4 md:mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Full Description</label>
                        <textarea class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                  id="preview_description" 
                                  rows="5"><?= escape($previewData['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="mb-4 md:mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                        <?php $allCategories = $categoryModel->getAll(false); ?>
                        <select name="category_id" id="preview_category" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">-- Auto-create --</option>
                            <?php foreach ($allCategories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <?php if (!empty($previewData['images'])): ?>
                        <div class="mb-4 md:mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Product Images (<?= count($previewData['images']) ?>)</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <?php foreach (array_slice($previewData['images'], 0, 8) as $img): ?>
                                    <div class="border-2 border-gray-200 rounded-lg overflow-hidden">
                                        <img src="<?= escape($img) ?>" 
                                             alt="Product image" 
                                             class="w-full h-32 object-cover"
                                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27100%27 height=%27100%27%3E%3Crect fill=%27%23ddd%27 width=%27100%27 height=%27100%27/%3E%3Ctext fill=%27%23999%27 x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27%3EBroken%3C/text%3E%3C/svg%3E'">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($previewData['specifications'])): ?>
                        <div class="mb-4 md:mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Specifications</label>
                            <div class="bg-gray-50 border-2 border-gray-200 rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <?php foreach ($previewData['specifications'] as $key => $value): ?>
                                        <div class="flex justify-between py-2 border-b border-gray-200">
                                            <span class="font-medium text-gray-700"><?= escape($key) ?>:</span>
                                            <span class="text-gray-600"><?= escape($value) ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($previewData['features'])): ?>
                        <div class="mb-4 md:mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Features</label>
                            <ul class="list-disc list-inside bg-gray-50 border-2 border-gray-200 rounded-lg p-4">
                                <?php foreach ($previewData['features'] as $feature): ?>
                                    <li class="text-gray-700 py-1"><?= escape($feature) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-4 md:mb-6">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500" id="download_images_smart" name="download_images" checked>
                            <span class="ml-3 text-sm font-medium text-gray-700">Download images to server</span>
                        </label>
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="submit" name="smart_import_save" class="flex-1 bg-gradient-to-r from-green-600 to-emerald-600 text-white px-6 py-3 rounded-lg font-bold hover:from-green-700 hover:to-emerald-700 transition-all shadow-lg hover:shadow-xl">
                            <i class="fas fa-save mr-2"></i> Import to Database
                        </button>
                        <button type="button" onclick="document.getElementById('smartImportForm').reset(); location.reload();" class="px-6 py-3 bg-gray-600 text-white rounded-lg font-bold hover:bg-gray-700 transition-all">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <script>
        // Update JSON data when form fields change
        document.getElementById('preview_name')?.addEventListener('input', updateProductData);
        document.getElementById('preview_price')?.addEventListener('input', updateProductData);
        document.getElementById('preview_short_desc')?.addEventListener('input', updateProductData);
        document.getElementById('preview_description')?.addEventListener('input', updateProductData);
        
        function updateProductData() {
            const data = <?= json_encode($previewData) ?>;
            data.name = document.getElementById('preview_name').value;
            data.price = document.getElementById('preview_price').value;
            data.short_description = document.getElementById('preview_short_desc').value;
            data.description = document.getElementById('preview_description').value;
            document.getElementById('product_data_json').value = JSON.stringify(data);
        }
        </script>
    <?php endif; ?>
    
    <!-- Import Methods Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-4 md:mb-6">
        <!-- Import from Category -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border-2 border-teal-200">
            <div class="bg-gradient-to-r from-teal-500 to-cyan-600 p-4 md:p-6 text-white">
                <h2 class="text-xl md:text-2xl font-bold flex items-center">
                    <i class="fas fa-folder-open mr-3"></i>
                    Import from Category
                </h2>
                <p class="text-teal-100 text-sm md:text-base mt-2">Import multiple products from a category page</p>
            </div>
            <div class="p-4 md:p-6">
                <form method="POST" action="">
                    <div class="mb-4 md:mb-6">
                        <label for="category" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-list text-teal-600 mr-2"></i>Select Category to Import
                        </label>
                        <select name="category" id="category" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all" required>
                            <option value="">-- Select Category --</option>
                            <?php foreach ($availableCategories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['slug']); ?>" 
                                        data-url="<?php echo htmlspecialchars($cat['url']); ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs md:text-sm text-gray-600 mt-2">
                            This will import products from the selected UN Forklift category page.
                            <br><strong>Note:</strong> If category import fails (404 error), use the "Import from Product URL" method instead.
                        </p>
                    </div>
                    
                    <div class="mb-4 md:mb-6">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" class="w-5 h-5 text-teal-600 border-gray-300 rounded focus:ring-teal-500" id="download_images" name="download_images" checked>
                            <span class="ml-3 text-sm font-medium text-gray-700">Download images to server</span>
                        </label>
                        <p class="text-xs text-gray-600 mt-1 ml-8">
                            If unchecked, product images will be linked directly from UN Forklift website.
                        </p>
                    </div>
                    
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 md:p-4 mb-4 rounded">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-2 mt-1"></i>
                            <div class="text-sm text-yellow-800">
                                <strong>Important:</strong> Category import may not work if UN Forklift has changed their website structure. 
                                If you get a 404 error, please use the <strong>"Import from Product URL"</strong> method instead.
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-3 md:p-4 mb-4 md:mb-6 rounded">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-600 mr-2 mt-1"></i>
                            <div class="text-sm text-blue-800">
                                <strong>Note:</strong> This import process may take several minutes depending on the number of products. 
                                The script will automatically skip products that already exist in your database.
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="import" class="w-full bg-gradient-to-r from-teal-600 to-cyan-600 text-white px-6 py-3 rounded-lg font-bold hover:from-teal-700 hover:to-cyan-700 transition-all shadow-lg hover:shadow-xl transform hover:scale-105" id="btnImportCategory">
                        <i class="fas fa-download mr-2"></i> Start Import
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Import from Product URL -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden border-2 border-green-200">
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-4 md:p-6 text-white">
                <h2 class="text-xl md:text-2xl font-bold flex items-center">
                    <i class="fas fa-link mr-3"></i>
                    Import from Product URL
                </h2>
                <p class="text-green-100 text-sm md:text-base mt-2">Import a single product from its URL</p>
            </div>
            <div class="p-4 md:p-6">
                <form method="POST" action="">
                    <div class="mb-4 md:mb-6">
                        <label for="product_url" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-globe text-green-600 mr-2"></i>Product URL
                        </label>
                        <input type="url" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all" 
                               id="product_url" name="product_url" 
                               placeholder="https://www.unforklift.com/product/..." required>
                        <p class="text-xs md:text-sm text-gray-600 mt-2">
                            Paste the direct URL to a product page on UN Forklift website.
                        </p>
                    </div>
                    
                    <div class="mb-4 md:mb-6">
                        <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-tags text-green-600 mr-2"></i>Category (Optional)
                        </label>
                        <?php 
                        $allCategories = $categoryModel->getAll(false);
                        ?>
                        <select name="category_id" id="category_id" class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all">
                            <option value="">-- Auto-detect or create --</option>
                            <?php foreach ($allCategories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs md:text-sm text-gray-600 mt-2">
                            Select a category or leave empty to auto-create.
                        </p>
                    </div>
                    
                    <div class="mb-4 md:mb-6">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" class="w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500" id="download_images_url" name="download_images_url" checked>
                            <span class="ml-3 text-sm font-medium text-gray-700">Download images to server</span>
                        </label>
                    </div>
                    
                    <button type="submit" name="import_url" class="w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white px-6 py-3 rounded-lg font-bold hover:from-green-700 hover:to-emerald-700 transition-all shadow-lg hover:shadow-xl transform hover:scale-105" id="btnImportUrl">
                        <i class="fas fa-link mr-2"></i> Import from URL
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Import Results -->
    <?php if (!empty($importResults)): ?>
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-4 md:mb-6">
            <div class="bg-gradient-to-r from-purple-500 to-indigo-600 p-4 md:p-6 text-white">
                <h2 class="text-xl md:text-2xl font-bold flex items-center">
                    <i class="fas fa-list-check mr-3"></i>
                    Import Results
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Product Name</th>
                            <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Details</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($importResults as $result): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                    <?php if ($result['status'] === 'success'): ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i> Success
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                            <i class="fas fa-times-circle mr-1"></i> Error
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 md:px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($result['name']); ?></div>
                                </td>
                                <td class="px-4 md:px-6 py-4">
                                    <?php if ($result['status'] === 'success'): ?>
                                        <a href="<?= url('admin/product-edit.php?id=' . $result['id']) ?>" 
                                           class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                            <i class="fas fa-eye mr-2"></i> View/Edit
                                        </a>
                                    <?php else: ?>
                                        <span class="text-sm text-red-600"><?php echo htmlspecialchars($result['error'] ?? 'Unknown error'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Manual Import Instructions -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-gray-700 to-gray-900 p-4 md:p-6 text-white">
            <h2 class="text-xl md:text-2xl font-bold flex items-center">
                <i class="fas fa-book mr-3"></i>
                Manual Import Instructions
            </h2>
        </div>
        <div class="p-4 md:p-6">
            <p class="text-gray-700 mb-4">If the automatic import doesn't work, you can manually import products:</p>
            <ol class="list-decimal list-inside space-y-2 text-gray-700 mb-4">
                <li>Visit the UN Forklift website: <a href="https://www.unforklift.com" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium">https://www.unforklift.com</a></li>
                <li>Navigate to the product category you want to import</li>
                <li>Copy product information and images</li>
                <li>Use the <a href="<?= url('admin/product-edit.php') ?>" class="text-blue-600 hover:text-blue-800 font-medium">Add Product</a> page to manually create products</li>
            </ol>
            <div class="bg-gray-50 border-l-4 border-gray-400 p-4 rounded">
                <p class="text-sm text-gray-700">
                    <strong>Alternative:</strong> You can also use the API endpoint if UN Forklift provides one, 
                    or contact them for a product data export (CSV/JSON format).
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Progress Modal -->
<div id="progressModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-4 md:px-6 py-4 flex justify-between items-center">
            <h5 class="text-base md:text-lg font-semibold flex items-center" id="progressModalLabel">
                <i class="fas fa-spinner fa-spin mr-2"></i> <span class="hidden sm:inline">Importing Products</span><span class="sm:hidden">Importing</span>
            </h5>
            <button type="button" id="btnCloseModal" class="text-white hover:text-gray-200 transition-colors p-2" onclick="closeProgressModal()">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <div class="p-4 md:p-6 overflow-y-auto flex-1">
            <div class="mb-4 md:mb-6">
                <div class="flex justify-between mb-2">
                    <span id="progressStatus" class="text-xs md:text-sm font-medium text-gray-700">Initializing...</span>
                    <span id="progressPercent" class="text-xs md:text-sm font-bold text-blue-600">0%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4 md:h-6 overflow-hidden shadow-inner">
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 h-4 md:h-6 rounded-full transition-all duration-300 flex items-center justify-center" 
                         id="progressBar" 
                         style="width: 0%">
                        <span class="text-xs text-white font-medium" id="progressBarText"></span>
                    </div>
                </div>
            </div>
            
            <div class="mb-4 md:mb-6">
                <strong class="text-xs md:text-sm font-semibold text-gray-700 block mb-1">Current Product:</strong>
                <div id="currentProduct" class="text-gray-600 text-xs md:text-sm bg-gray-50 p-2 rounded">-</div>
            </div>
            
            <div class="grid grid-cols-3 gap-2 md:gap-4 mb-4 md:mb-6">
                <div class="border-2 border-green-200 rounded-lg p-2 md:p-3 text-center bg-green-50">
                    <div class="text-xl md:text-2xl font-bold text-green-600 mb-1" id="importedCount">0</div>
                    <small class="text-gray-600 text-xs">Imported</small>
                </div>
                <div class="border-2 border-yellow-200 rounded-lg p-2 md:p-3 text-center bg-yellow-50">
                    <div class="text-xl md:text-2xl font-bold text-yellow-600 mb-1" id="skippedCount">0</div>
                    <small class="text-gray-600 text-xs">Skipped</small>
                </div>
                <div class="border-2 border-red-200 rounded-lg p-2 md:p-3 text-center bg-red-50">
                    <div class="text-xl md:text-2xl font-bold text-red-600 mb-1" id="errorCount">0</div>
                    <small class="text-gray-600 text-xs">Errors</small>
                </div>
            </div>
            
            <div>
                <strong class="text-xs md:text-sm font-semibold mb-2 block text-gray-700">Recent Activity:</strong>
                <div id="progressLog" class="border-2 border-gray-200 rounded-lg p-3 bg-gray-50 max-h-32 md:max-h-48 overflow-y-auto">
                    <small class="text-gray-500 text-xs">Waiting for import to start...</small>
                </div>
            </div>
        </div>
        <div class="bg-gray-100 px-4 md:px-6 py-4 flex justify-end border-t border-gray-200">
            <button type="button" 
                    class="px-4 md:px-6 py-2 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-lg hover:from-gray-700 hover:to-gray-800 disabled:opacity-50 disabled:cursor-not-allowed transition-all font-medium text-sm md:text-base" 
                    id="btnCloseProgress" 
                    disabled 
                    onclick="location.reload()">
                <i class="fas fa-times mr-2"></i> Close
            </button>
        </div>
    </div>
</div>

<script>
let progressInterval = null;
let currentSessionId = null;

function showProgressModal() {
    document.getElementById('progressModal').classList.remove('hidden');
    document.getElementById('btnCloseProgress').disabled = true;
}

function closeProgressModal() {
    if (progressInterval) {
        clearInterval(progressInterval);
    }
    document.getElementById('progressModal').classList.add('hidden');
}

function updateProgress(sessionId) {
    if (!sessionId) return;
    
    fetch('<?= url("admin/api/import-progress.php") ?>?session_id=' + sessionId)
        .then(response => response.json())
        .then(data => {
            // Update status
            document.getElementById('progressStatus').textContent = data.message || 'Processing...';
            document.getElementById('currentProduct').textContent = data.current_product || '-';
            
            // Update counts
            document.getElementById('importedCount').textContent = data.imported || 0;
            document.getElementById('skippedCount').textContent = data.skipped || 0;
            document.getElementById('errorCount').textContent = data.errors || 0;
            
            // Update progress bar
            if (data.total > 0) {
                const percent = Math.round((data.current / data.total) * 100);
                document.getElementById('progressBar').style.width = percent + '%';
                document.getElementById('progressBarText').textContent = percent + '%';
                document.getElementById('progressPercent').textContent = percent + '%';
            } else if (data.status === 'completed' || data.status === 'error') {
                document.getElementById('progressBar').style.width = '100%';
                document.getElementById('progressBarText').textContent = '100%';
                document.getElementById('progressPercent').textContent = '100%';
            }
            
            // Update log
            if (data.results && data.results.length > 0) {
                const logDiv = document.getElementById('progressLog');
                let logHtml = '';
                data.results.slice(-10).forEach(result => {
                    const icon = result.status === 'success' ? '✓' : result.status === 'error' ? '✗' : '⊘';
                    const color = result.status === 'success' ? 'text-success' : result.status === 'error' ? 'text-danger' : 'text-warning';
                    logHtml += `<div class="${color}"><small>${icon} ${result.name}</small></div>`;
                });
                logDiv.innerHTML = logHtml;
                logDiv.scrollTop = logDiv.scrollHeight;
            }
            
            // Check if completed
            if (data.status === 'completed' || data.status === 'error') {
                clearInterval(progressInterval);
                document.getElementById('btnCloseProgress').disabled = false;
                const labelEl = document.getElementById('progressModalLabel');
                if (data.status === 'completed') {
                    labelEl.innerHTML = '<i class="fas fa-check-circle text-green-500"></i> Import Completed';
                    labelEl.classList.add('text-green-600');
                } else {
                    labelEl.innerHTML = '<i class="fas fa-exclamation-circle text-red-500"></i> Import Failed';
                    labelEl.classList.add('text-red-600');
                }
                
                // Show final message
                if (data.status === 'completed') {
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                }
            }
        })
        .catch(error => {
            console.error('Error fetching progress:', error);
        });
}

// Handle category import form
document.getElementById('btnImportCategory')?.addEventListener('click', function(e) {
    e.preventDefault();
    
    const form = this.closest('form');
    const formData = new FormData(form);
    formData.append('import_type', 'category');
    
    showProgressModal();
    
    fetch('<?= url("admin/api/import-unforklift.php") ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            return response.text().then(text => {
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response. Check console for details.');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.session_id) {
            currentSessionId = data.session_id;
            progressInterval = setInterval(() => {
                updateProgress(currentSessionId);
            }, 1000);
            updateProgress(currentSessionId);
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
            closeProgressModal();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error starting import: ' + error.message + '\n\nCheck browser console for more details.');
        closeProgressModal();
    });
});

// Handle URL import form
document.getElementById('btnImportUrl')?.addEventListener('click', function(e) {
    e.preventDefault();
    
    const form = this.closest('form');
    const formData = new FormData(form);
    formData.append('import_type', 'url');
    
    showProgressModal();
    
    fetch('<?= url("admin/api/import-unforklift.php") ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        const contentType = response.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            return response.text().then(text => {
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response. Check console for details.');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.session_id) {
            currentSessionId = data.session_id;
            progressInterval = setInterval(() => {
                updateProgress(currentSessionId);
            }, 1000);
            updateProgress(currentSessionId);
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
            closeProgressModal();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error starting import: ' + error.message + '\n\nCheck browser console for more details.');
        closeProgressModal();
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

