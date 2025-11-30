<?php
/**
 * Add Sample Variant Data
 * Adds realistic sample variants to existing products
 */
require_once __DIR__ . '/../bootstrap/app.php';

// Allow access for initial setup
$requireAuth = true;
try {
    db()->fetchOne("SELECT 1 FROM admin_users LIMIT 1");
} catch (Exception $e) {
    $requireAuth = false;
}

if ($requireAuth) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'];
    
    if (!$isLoggedIn) {
        header('Location: ' . url('admin/login.php'));
        exit;
    }
    
    require_once __DIR__ . '/includes/auth.php';
} else {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!function_exists('hasPermission')) {
        function hasPermission($permissionSlug) {
            return true;
        }
    }
}

$message = '';
$error = '';

// Check if variant tables exist
try {
    db()->fetchOne("SELECT 1 FROM product_variants LIMIT 1");
    db()->fetchOne("SELECT 1 FROM product_variant_attributes LIMIT 1");
    $tablesExist = true;
} catch (Exception $e) {
    $tablesExist = false;
    $error = "Variant tables don't exist. Please run <a href='" . url('admin/setup-variants.php') . "'>Setup Variants</a> first.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_variants']) && $tablesExist) {
    try {
        $db = db();
        $pdo = $db->getPdo();
        $pdo->beginTransaction();
        
        // Get all active products
        $products = $db->fetchAll("SELECT id, name, sku, price FROM products WHERE is_active = 1 LIMIT 20");
        
        if (empty($products)) {
            $error = "No active products found. Please add products first.";
        } else {
            $variantsAdded = 0;
            
            foreach ($products as $product) {
                // Skip if product already has variants
                $existingVariants = $db->fetchAll(
                    "SELECT id FROM product_variants WHERE product_id = :product_id",
                    ['product_id' => $product['id']]
                );
                
                if (!empty($existingVariants)) {
                    continue; // Skip products that already have variants
                }
                
                $basePrice = floatval($product['price'] ?? 0);
                // Ensure base SKU is always valid and unique - always include product ID
                $baseSku = trim($product['sku'] ?? '');
                if (empty($baseSku)) {
                    $baseSku = 'PROD-' . $product['id'];
                } else {
                    // Ensure product ID is in SKU for uniqueness
                    if (strpos($baseSku, (string)$product['id']) === false) {
                        $baseSku = $baseSku . '-' . $product['id'];
                    }
                }
                // Remove any trailing dashes or spaces
                $baseSku = rtrim($baseSku, '- ');
                
                // Generate variants based on product type/name
                $variants = generateVariantsForProduct($product, $basePrice, $baseSku, $product['id']);
                
                foreach ($variants as $variant) {
                    // Ensure SKU is unique - add variant index if needed
                    $variantSku = $variant['sku'];
                    $skuCounter = 0;
                    $maxAttempts = 100;
                    
                    // Check if SKU already exists and make it unique
                    while ($skuCounter < $maxAttempts) {
                        $existing = $db->fetchOne(
                            "SELECT id FROM product_variants WHERE sku = :sku",
                            ['sku' => $variantSku]
                        );
                        
                        if (!$existing) {
                            break; // SKU is unique
                        }
                        
                        // Add counter to make it unique
                        $skuCounter++;
                        $variantSku = $variant['sku'] . '-' . $skuCounter;
                    }
                    
                    // Insert variant
                    $variantId = $db->insert('product_variants', [
                        'product_id' => $product['id'],
                        'name' => $variant['name'],
                        'sku' => $variantSku,
                        'price' => $variant['price'],
                        'sale_price' => $variant['sale_price'] ?? null,
                        'stock_quantity' => $variant['stock_quantity'],
                        'stock_status' => $variant['stock_status'],
                        'is_active' => 1,
                        'sort_order' => $variant['sort_order']
                    ]);
                    
                    // Insert attributes
                    foreach ($variant['attributes'] as $attrName => $attrValue) {
                        $db->insert('product_variant_attributes', [
                            'variant_id' => $variantId,
                            'attribute_name' => $attrName,
                            'attribute_value' => $attrValue
                        ]);
                    }
                    
                    $variantsAdded++;
                }
            }
            
            $pdo->commit();
            $message = "Successfully added {$variantsAdded} variants to " . count($products) . " products!";
        }
        
    } catch (Exception $e) {
        if (isset($pdo)) {
            $pdo->rollBack();
        }
        $error = "Error: " . $e->getMessage();
    }
}

function generateVariantsForProduct($product, $basePrice, $baseSku, $productId) {
    $productName = strtolower($product['name']);
    $variants = [];
    
    // Forklift variants - Size and Capacity
    if (strpos($productName, 'forklift') !== false || strpos($productName, 'lift') !== false) {
        $sizes = ['Small', 'Medium', 'Large'];
        $capacities = ['2000 lbs', '3000 lbs', '5000 lbs'];
        
        $sortOrder = 0;
        foreach ($sizes as $sizeIndex => $size) {
            foreach ($capacities as $capIndex => $capacity) {
                $priceMultiplier = 1 + ($sizeIndex * 0.2) + ($capIndex * 0.3);
                $price = round($basePrice * $priceMultiplier, 2);
                $salePrice = ($sizeIndex === 0 && $capIndex === 0) ? round($price * 0.9, 2) : null;
                
                $variants[] = [
                    'name' => "{$size} - {$capacity}",
                    'sku' => $baseSku . '-' . substr($size, 0, 2) . '-' . substr($capacity, 0, 3),
                    'price' => $price,
                    'sale_price' => $salePrice,
                    'stock_quantity' => rand(5, 50),
                    'stock_status' => rand(0, 10) > 1 ? 'in_stock' : 'on_order',
                    'sort_order' => $sortOrder++,
                    'attributes' => [
                        'Size' => $size,
                        'Capacity' => $capacity
                    ]
                ];
            }
        }
    }
    // Pallet Truck variants - Type and Weight Capacity
    elseif (strpos($productName, 'pallet') !== false || strpos($productName, 'truck') !== false) {
        $types = ['Manual', 'Electric', 'Hydraulic'];
        $weights = ['1500 lbs', '2500 lbs', '3500 lbs'];
        
        $sortOrder = 0;
        foreach ($types as $typeIndex => $type) {
            foreach ($weights as $weightIndex => $weight) {
                $priceMultiplier = 1 + ($typeIndex * 0.4) + ($weightIndex * 0.2);
                $price = round($basePrice * $priceMultiplier, 2);
                $salePrice = ($typeIndex === 0 && $weightIndex === 0) ? round($price * 0.85, 2) : null;
                
                $typeCode = strtoupper(substr($type, 0, 3));
                $weightCode = strtoupper(substr(str_replace(' ', '', $weight), 0, 3));
                $variants[] = [
                    'name' => "{$type} - {$weight}",
                    'sku' => $baseSku . '-' . $typeCode . '-' . $weightCode,
                    'price' => $price,
                    'sale_price' => $salePrice,
                    'stock_quantity' => rand(3, 40),
                    'stock_status' => rand(0, 10) > 1 ? 'in_stock' : 'in_stock',
                    'sort_order' => $sortOrder++,
                    'attributes' => [
                        'Type' => $type,
                        'Weight Capacity' => $weight
                    ]
                ];
            }
        }
    }
    // Stacker variants - Height and Capacity
    elseif (strpos($productName, 'stacker') !== false || strpos($productName, 'stack') !== false) {
        $heights = ['6 ft', '8 ft', '10 ft'];
        $capacities = ['1000 lbs', '1500 lbs', '2000 lbs'];
        
        $sortOrder = 0;
        foreach ($heights as $heightIndex => $height) {
            foreach ($capacities as $capIndex => $capacity) {
                $priceMultiplier = 1 + ($heightIndex * 0.25) + ($capIndex * 0.2);
                $price = round($basePrice * $priceMultiplier, 2);
                
                $heightCode = strtoupper(str_replace([' ', 'ft'], '', $height));
                $capCode = strtoupper(substr(str_replace(' ', '', $capacity), 0, 3));
                $variants[] = [
                    'name' => "{$height} - {$capacity}",
                    'sku' => $baseSku . '-' . $heightCode . '-' . $capCode,
                    'price' => $price,
                    'sale_price' => null,
                    'stock_quantity' => rand(4, 35),
                    'stock_status' => 'in_stock',
                    'sort_order' => $sortOrder++,
                    'attributes' => [
                        'Lifting Height' => $height,
                        'Capacity' => $capacity
                    ]
                ];
            }
        }
    }
    // Reach Truck variants - Reach and Capacity
    elseif (strpos($productName, 'reach') !== false) {
        $reaches = ['10 ft', '12 ft', '14 ft'];
        $capacities = ['2500 lbs', '3000 lbs', '3500 lbs'];
        
        $sortOrder = 0;
        foreach ($reaches as $reachIndex => $reach) {
            foreach ($capacities as $capIndex => $capacity) {
                $priceMultiplier = 1 + ($reachIndex * 0.3) + ($capIndex * 0.25);
                $price = round($basePrice * $priceMultiplier, 2);
                
                $reachCode = strtoupper(str_replace([' ', 'ft'], '', $reach));
                $capCode = strtoupper(substr(str_replace(' ', '', $capacity), 0, 3));
                $variants[] = [
                    'name' => "{$reach} - {$capacity}",
                    'sku' => $baseSku . '-' . $reachCode . '-' . $capCode,
                    'price' => $price,
                    'sale_price' => null,
                    'stock_quantity' => rand(2, 25),
                    'stock_status' => rand(0, 10) > 2 ? 'in_stock' : 'on_order',
                    'sort_order' => $sortOrder++,
                    'attributes' => [
                        'Reach' => $reach,
                        'Capacity' => $capacity
                    ]
                ];
            }
        }
    }
    // Trolley variants - Size and Material
    elseif (strpos($productName, 'trolley') !== false || strpos($productName, 'cart') !== false) {
        $sizes = ['Small', 'Medium', 'Large'];
        $materials = ['Steel', 'Aluminum', 'Plastic'];
        
        $sortOrder = 0;
        foreach ($sizes as $sizeIndex => $size) {
            foreach ($materials as $matIndex => $material) {
                $priceMultiplier = 1 + ($sizeIndex * 0.15) + ($matIndex * 0.1);
                $price = round($basePrice * $priceMultiplier, 2);
                $salePrice = ($sizeIndex === 0 && $matIndex === 0) ? round($price * 0.9, 2) : null;
                
                $sizeCode = strtoupper(substr($size, 0, 2));
                $matCode = strtoupper(substr($material, 0, 3));
                $variants[] = [
                    'name' => "{$size} - {$material}",
                    'sku' => $baseSku . '-' . $sizeCode . '-' . $matCode,
                    'price' => $price,
                    'sale_price' => $salePrice,
                    'stock_quantity' => rand(10, 60),
                    'stock_status' => 'in_stock',
                    'sort_order' => $sortOrder++,
                    'attributes' => [
                        'Size' => $size,
                        'Material' => $material
                    ]
                ];
            }
        }
    }
    // Lifting Equipment variants - Capacity and Type
    elseif (strpos($productName, 'lift') !== false || strpos($productName, 'crane') !== false) {
        $capacities = ['500 lbs', '1000 lbs', '2000 lbs'];
        $types = ['Manual', 'Electric', 'Hydraulic'];
        
        $sortOrder = 0;
        foreach ($capacities as $capIndex => $capacity) {
            foreach ($types as $typeIndex => $type) {
                $priceMultiplier = 1 + ($capIndex * 0.3) + ($typeIndex * 0.35);
                $price = round($basePrice * $priceMultiplier, 2);
                
                $capCode = strtoupper(substr(str_replace(' ', '', $capacity), 0, 3));
                $typeCode = strtoupper(substr($type, 0, 3));
                $variants[] = [
                    'name' => "{$capacity} - {$type}",
                    'sku' => $baseSku . '-' . $capCode . '-' . $typeCode,
                    'price' => $price,
                    'sale_price' => null,
                    'stock_quantity' => rand(3, 30),
                    'stock_status' => rand(0, 10) > 1 ? 'in_stock' : 'on_order',
                    'sort_order' => $sortOrder++,
                    'attributes' => [
                        'Capacity' => $capacity,
                        'Type' => $type
                    ]
                ];
            }
        }
    }
    // Default variants - Size and Color
    else {
        $sizes = ['Small', 'Medium', 'Large'];
        $colors = ['Red', 'Blue', 'Yellow'];
        
        $sortOrder = 0;
        foreach ($sizes as $sizeIndex => $size) {
            foreach ($colors as $colorIndex => $color) {
                $priceMultiplier = 1 + ($sizeIndex * 0.1) + ($colorIndex * 0.05);
                $price = round($basePrice * $priceMultiplier, 2);
                $salePrice = ($sizeIndex === 0 && $colorIndex === 0) ? round($price * 0.9, 2) : null;
                
                $sizeCode = strtoupper(substr($size, 0, 2));
                $colorCode = strtoupper(substr($color, 0, 3));
                $variants[] = [
                    'name' => "{$size} - {$color}",
                    'sku' => $baseSku . '-' . $sizeCode . '-' . $colorCode,
                    'price' => $price,
                    'sale_price' => $salePrice,
                    'stock_quantity' => rand(5, 45),
                    'stock_status' => 'in_stock',
                    'sort_order' => $sortOrder++,
                    'attributes' => [
                        'Size' => $size,
                        'Color' => $color
                    ]
                ];
            }
        }
    }
    
    return $variants;
}

$pageTitle = 'Add Sample Variants';
include __DIR__ . '/includes/header.php';

// Get product count
$productCount = 0;
$productsWithVariants = 0;
if ($tablesExist) {
    try {
        $productCount = db()->fetchOne("SELECT COUNT(*) as count FROM products WHERE is_active = 1")['count'];
        $productsWithVariants = db()->fetchOne(
            "SELECT COUNT(DISTINCT product_id) as count FROM product_variants"
        )['count'];
    } catch (Exception $e) {
        // Ignore
    }
}
?>

<div class="p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Add Sample Variant Data</h1>
        <p class="text-gray-600 mt-2">This will add realistic sample variants to your products.</p>
    </div>
    
    <?php if ($message): ?>
    <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
        <?= escape($message) ?>
    </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
        <?= $error ?>
    </div>
    <?php endif; ?>
    
    <?php if (!$tablesExist): ?>
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
        <h3 class="font-bold text-yellow-900 mb-2">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Variant Tables Required
        </h3>
        <p class="text-yellow-800 mb-4">
            Please set up the variant tables first.
        </p>
        <a href="<?= url('admin/setup-variants.php') ?>" class="btn-primary">
            <i class="fas fa-database mr-2"></i>
            Setup Variants
        </a>
    </div>
    <?php else: ?>
    <!-- Current Status -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Current Status</h2>
        <div class="space-y-2">
            <div class="flex justify-between">
                <span class="text-gray-700">Active Products:</span>
                <span class="font-semibold"><?= $productCount ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-700">Products with Variants:</span>
                <span class="font-semibold"><?= $productsWithVariants ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-700">Products without Variants:</span>
                <span class="font-semibold"><?= max(0, $productCount - $productsWithVariants) ?></span>
            </div>
        </div>
    </div>
    
    <!-- Variant Types -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Variant Types That Will Be Created</h2>
        <div class="grid md:grid-cols-2 gap-4 text-sm">
            <div class="border-l-4 border-blue-500 pl-4">
                <h3 class="font-semibold mb-2">Forklifts</h3>
                <p class="text-gray-600">Size × Capacity (Small/Medium/Large × 2000/3000/5000 lbs)</p>
            </div>
            <div class="border-l-4 border-green-500 pl-4">
                <h3 class="font-semibold mb-2">Pallet Trucks</h3>
                <p class="text-gray-600">Type × Weight (Manual/Electric/Hydraulic × 1500/2500/3500 lbs)</p>
            </div>
            <div class="border-l-4 border-purple-500 pl-4">
                <h3 class="font-semibold mb-2">Stackers</h3>
                <p class="text-gray-600">Height × Capacity (6/8/10 ft × 1000/1500/2000 lbs)</p>
            </div>
            <div class="border-l-4 border-yellow-500 pl-4">
                <h3 class="font-semibold mb-2">Reach Trucks</h3>
                <p class="text-gray-600">Reach × Capacity (10/12/14 ft × 2500/3000/3500 lbs)</p>
            </div>
            <div class="border-l-4 border-red-500 pl-4">
                <h3 class="font-semibold mb-2">Trolleys</h3>
                <p class="text-gray-600">Size × Material (Small/Medium/Large × Steel/Aluminum/Plastic)</p>
            </div>
            <div class="border-l-4 border-indigo-500 pl-4">
                <h3 class="font-semibold mb-2">Lifting Equipment</h3>
                <p class="text-gray-600">Capacity × Type (500/1000/2000 lbs × Manual/Electric/Hydraulic)</p>
            </div>
            <div class="border-l-4 border-gray-500 pl-4 md:col-span-2">
                <h3 class="font-semibold mb-2">Other Products</h3>
                <p class="text-gray-600">Size × Color (Small/Medium/Large × Red/Blue/Yellow)</p>
            </div>
        </div>
    </div>
    
    <!-- Action -->
    <form method="POST" class="mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-4">
            <h3 class="font-bold text-blue-900 mb-2">
                <i class="fas fa-info-circle mr-2"></i>
                What This Does
            </h3>
            <ul class="list-disc list-inside text-blue-800 space-y-1 text-sm">
                <li>Adds variants to products that don't have any yet</li>
                <li>Skips products that already have variants</li>
                <li>Creates realistic attribute combinations (Size, Capacity, Type, etc.)</li>
                <li>Sets appropriate prices based on variant attributes</li>
                <li>Adds random stock quantities and statuses</li>
            </ul>
        </div>
        
        <button type="submit" name="add_variants" class="btn-primary" onclick="return confirm('This will add variants to products without variants. Continue?');">
            <i class="fas fa-plus-circle mr-2"></i>
            Add Sample Variants
        </button>
    </form>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

