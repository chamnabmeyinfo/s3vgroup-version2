<?php
/**
 * Product Quick View API
 */
require_once __DIR__ . '/../bootstrap/app.php';

use App\Models\Product;

header('Content-Type: application/json');

$productId = (int)($_GET['product_id'] ?? 0);

if (!$productId) {
    echo json_encode(['success' => false, 'error' => 'Product ID required']);
    exit;
}

$productModel = new Product();
$product = $productModel->getById($productId);

if (!$product) {
    echo json_encode(['success' => false, 'error' => 'Product not found']);
    exit;
}

// Get product gallery
$gallery = [];
if (!empty($product['gallery'])) {
    $gallery = json_decode($product['gallery'], true) ?: [];
}
if (!empty($product['image']) && !in_array($product['image'], $gallery)) {
    array_unshift($gallery, $product['image']);
}
if (empty($gallery)) {
    $gallery = ['placeholder.jpg'];
}

echo json_encode([
    'success' => true,
    'product' => [
        'id' => $product['id'],
        'name' => $product['name'],
        'slug' => $product['slug'],
        'price' => $product['price'],
        'sale_price' => $product['sale_price'],
        'short_description' => $product['short_description'] ?? '',
        'image' => $product['image'],
        'gallery' => $gallery,
        'stock_status' => $product['stock_status'],
        'sku' => $product['sku'] ?? '',
        'url' => url('product.php?slug=' . $product['slug'])
    ]
]);

