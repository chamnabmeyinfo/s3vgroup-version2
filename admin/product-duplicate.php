<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

use App\Models\Product;

$productId = $_GET['id'] ?? null;
$productModel = new Product();

if (!$productId) {
    header('Location: ' . url('admin/products.php'));
    exit;
}

$product = $productModel->getById($productId);

if (!$product) {
    header('Location: ' . url('admin/products.php'));
    exit;
}

// Create duplicate
$newData = [
    'name' => $product['name'] . ' (Copy)',
    'slug' => $product['slug'] . '-copy-' . time(),
    'sku' => $product['sku'] ? $product['sku'] . '-COPY' : '',
    'description' => $product['description'],
    'short_description' => $product['short_description'],
    'price' => $product['price'],
    'sale_price' => $product['sale_price'],
    'category_id' => $product['category_id'],
    'image' => $product['image'],
    'gallery' => $product['gallery'],
    'specifications' => $product['specifications'],
    'features' => $product['features'],
    'stock_status' => $product['stock_status'],
    'weight' => $product['weight'],
    'dimensions' => $product['dimensions'],
    'is_featured' => 0, // Don't duplicate featured status
    'is_active' => 0, // Make inactive by default
    'meta_title' => $product['meta_title'],
    'meta_description' => $product['meta_description'],
];

$newId = $productModel->create($newData);

header('Location: ' . url('admin/product-edit.php?id=' . $newId . '&message=Product duplicated successfully'));
exit;

