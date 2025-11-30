<?php
/**
 * Product Recommendations API
 * Returns recommended products based on various criteria
 */
require_once __DIR__ . '/../bootstrap/app.php';

use App\Models\Product;

header('Content-Type: application/json');

$productId = (int)($_GET['product_id'] ?? 0);
$type = $_GET['type'] ?? 'related'; // related, viewed, popular

$productModel = new Product();

$recommendations = [];

try {
    if ($type === 'related' && $productId) {
        // Get related products (same category, exclude current)
        $currentProduct = $productModel->getById($productId);
        if ($currentProduct) {
            $products = $productModel->getAll([
                'category_id' => $currentProduct['category_id'],
                'limit' => 4,
                'exclude_id' => $productId
            ]);
            $recommendations = array_slice($products, 0, 4);
        }
    } elseif ($type === 'popular') {
        // Get most viewed products
        $products = $productModel->getAll(['limit' => 8, 'order_by' => 'view_count DESC']);
        $recommendations = $products;
    } elseif ($type === 'featured') {
        // Get featured products
        $recommendations = $productModel->getFeatured(8);
    } else {
        // Default: get featured products
        $recommendations = $productModel->getFeatured(8);
    }
    
    echo json_encode([
        'success' => true,
        'products' => $recommendations
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error fetching recommendations'
    ]);
}

