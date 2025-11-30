<?php
/**
 * Product Comparison API
 */
require_once __DIR__ . '/../bootstrap/app.php';

use App\Models\Product;

$productIds = $_GET['ids'] ?? '';
$ids = array_filter(array_map('intval', explode(',', $productIds)));

if (empty($ids) || count($ids) > 4) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid product IDs']);
    exit;
}

$productModel = new Product();
$products = [];

foreach ($ids as $id) {
    $product = $productModel->getById($id);
    if ($product && $product['is_active']) {
        $products[] = $product;
    }
}

header('Content-Type: application/json');
echo json_encode(['products' => $products]);

