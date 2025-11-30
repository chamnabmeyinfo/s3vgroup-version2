<?php
/**
 * Sample Products Generator
 * This script adds sample products to the database for testing
 * Access via: http://localhost:8080/admin/add-sample-products.php
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

use App\Models\Product;
use App\Models\Category;

$productModel = new Product();
$categoryModel = new Category();

$categories = $categoryModel->getAll();

if (empty($categories)) {
    die("Please create categories first!");
}

// Sample products data
$sampleProducts = [
    [
        'name' => 'Electric Forklift 3.5 Ton',
        'slug' => 'electric-forklift-3-5-ton',
        'sku' => 'FL-EL-3500',
        'short_description' => 'Powerful electric forklift perfect for indoor warehouse operations.',
        'description' => 'This electric forklift offers zero emissions and quiet operation, making it ideal for indoor use. Features include ergonomic controls, high visibility mast, and advanced battery management system.',
        'price' => 28500.00,
        'sale_price' => 26500.00,
        'category_id' => $categories[0]['id'] ?? null,
        'specifications' => [
            'Lift Capacity' => '3.5 Ton',
            'Lift Height' => '6.5m',
            'Power' => 'Electric',
            'Battery Voltage' => '48V',
            'Weight' => '4,200 kg'
        ],
        'features' => '• Zero emissions\n• Quiet operation\n• Ergonomic design\n• Advanced safety features\n• Long battery life',
        'stock_status' => 'in_stock',
        'weight' => 4200,
        'dimensions' => 'L: 2.8m x W: 1.2m x H: 2.1m',
        'is_featured' => 1,
        'is_active' => 1
    ],
    [
        'name' => 'Diesel Forklift 5 Ton',
        'slug' => 'diesel-forklift-5-ton',
        'sku' => 'FL-DS-5000',
        'short_description' => 'Heavy-duty diesel forklift for outdoor and rugged environments.',
        'description' => 'Built for demanding applications, this diesel forklift delivers exceptional power and durability. Perfect for construction sites, lumber yards, and outdoor warehouses.',
        'price' => 42500.00,
        'category_id' => $categories[0]['id'] ?? null,
        'specifications' => [
            'Lift Capacity' => '5 Ton',
            'Lift Height' => '7.0m',
            'Engine' => 'Diesel 3.5L',
            'Fuel Tank' => '80L',
            'Weight' => '6,800 kg'
        ],
        'features' => '• High power output\n• Durable construction\n• Excellent for outdoor use\n• Low maintenance\n• Superior lifting capacity',
        'stock_status' => 'in_stock',
        'weight' => 6800,
        'dimensions' => 'L: 3.2m x W: 1.4m x H: 2.3m',
        'is_featured' => 1,
        'is_active' => 1
    ],
    [
        'name' => 'Pallet Truck Electric',
        'slug' => 'pallet-truck-electric',
        'sku' => 'PT-EL-2500',
        'short_description' => 'Lightweight electric pallet truck for efficient material handling.',
        'description' => 'This electric pallet truck makes moving heavy loads effortless. Features include quick-charge battery, comfortable handle, and smooth hydraulic lifting.',
        'price' => 3200.00,
        'category_id' => $categories[1]['id'] ?? null,
        'specifications' => [
            'Load Capacity' => '2.5 Ton',
            'Fork Length' => '1.15m',
            'Power' => 'Electric 24V',
            'Lift Height' => '200mm',
            'Weight' => '380 kg'
        ],
        'features' => '• Easy to operate\n• Quick charging\n• Compact design\n• Ergonomic handle\n• Reliable performance',
        'stock_status' => 'in_stock',
        'weight' => 380,
        'dimensions' => 'L: 1.8m x W: 0.8m x H: 1.3m',
        'is_featured' => 0,
        'is_active' => 1
    ],
    [
        'name' => 'Reach Truck Narrow Aisle',
        'slug' => 'reach-truck-narrow-aisle',
        'sku' => 'RT-NA-2000',
        'short_description' => 'Narrow aisle reach truck for maximizing warehouse space.',
        'description' => 'Designed for narrow aisle warehouses, this reach truck maximizes storage density while maintaining excellent maneuverability and safety.',
        'price' => 38500.00,
        'category_id' => $categories[3]['id'] ?? null,
        'specifications' => [
            'Load Capacity' => '2 Ton',
            'Lift Height' => '10m',
            'Aisle Width' => '2.5m',
            'Power' => 'Electric 48V',
            'Weight' => '3,500 kg'
        ],
        'features' => '• Narrow aisle design\n• High lift capacity\n• Space efficient\n• Advanced control system\n• Excellent visibility',
        'stock_status' => 'in_stock',
        'weight' => 3500,
        'dimensions' => 'L: 2.5m x W: 1.2m x H: 2.0m',
        'is_featured' => 1,
        'is_active' => 1
    ],
    [
        'name' => 'Hand Pallet Truck',
        'slug' => 'hand-pallet-truck',
        'sku' => 'PT-HD-2000',
        'short_description' => 'Manual pallet truck for light to medium duty applications.',
        'description' => 'A reliable manual pallet truck that requires no power. Perfect for small warehouses, retail stores, and light industrial applications.',
        'price' => 450.00,
        'category_id' => $categories[1]['id'] ?? null,
        'specifications' => [
            'Load Capacity' => '2 Ton',
            'Fork Length' => '1.15m',
            'Type' => 'Manual Hydraulic',
            'Lift Height' => '200mm',
            'Weight' => '85 kg'
        ],
        'features' => '• No power required\n• Lightweight\n• Durable construction\n• Easy to use\n• Low maintenance',
        'stock_status' => 'in_stock',
        'weight' => 85,
        'dimensions' => 'L: 1.6m x W: 0.6m x H: 1.2m',
        'is_featured' => 0,
        'is_active' => 1
    ],
    [
        'name' => 'Electric Stacker 1.5 Ton',
        'slug' => 'electric-stacker-1-5-ton',
        'sku' => 'ST-EL-1500',
        'short_description' => 'Compact electric stacker for stacking and storage operations.',
        'description' => 'This electric stacker combines power and precision for efficient stacking operations. Ideal for warehouses with limited space.',
        'price' => 5500.00,
        'category_id' => $categories[2]['id'] ?? null,
        'specifications' => [
            'Load Capacity' => '1.5 Ton',
            'Lift Height' => '3.5m',
            'Power' => 'Electric 24V',
            'Battery Capacity' => '210Ah',
            'Weight' => '650 kg'
        ],
        'features' => '• Compact design\n• Easy operation\n• Reliable lifting\n• Battery powered\n• Versatile applications',
        'stock_status' => 'in_stock',
        'weight' => 650,
        'dimensions' => 'L: 1.5m x W: 0.9m x H: 1.8m',
        'is_featured' => 0,
        'is_active' => 1
    ]
];

$added = 0;
$skipped = 0;

foreach ($sampleProducts as $productData) {
    // Check if product already exists
    $existing = db()->fetchOne(
        "SELECT id FROM products WHERE slug = :slug",
        ['slug' => $productData['slug']]
    );
    
    if ($existing) {
        $skipped++;
        continue;
    }
    
    // Convert specifications array to JSON
    if (isset($productData['specifications']) && is_array($productData['specifications'])) {
        $productData['specifications'] = json_encode($productData['specifications']);
    }
    
    try {
        $productModel->create($productData);
        $added++;
    } catch (Exception $e) {
        // Skip if error
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sample Products Added</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-4">Sample Products</h1>
        <div class="mb-4">
            <p class="text-green-600">✓ <?= $added ?> products added successfully</p>
            <?php if ($skipped > 0): ?>
                <p class="text-gray-600"><?= $skipped ?> products already existed (skipped)</p>
            <?php endif; ?>
        </div>
        <a href="<?= url('admin/products.php') ?>" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            View Products
        </a>
    </div>
</body>
</html>

