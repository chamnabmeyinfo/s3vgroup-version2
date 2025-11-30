<?php
/**
 * Advanced Search API with Autocomplete
 */
require_once __DIR__ . '/../bootstrap/app.php';

header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');
$type = $_GET['type'] ?? 'products'; // products, categories, all

if (empty($query) || strlen($query) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

$results = [];

if ($type === 'products' || $type === 'all') {
    $products = db()->fetchAll(
        "SELECT id, name, slug, price, image, short_description 
         FROM products 
         WHERE is_active = 1 
         AND (name LIKE :query OR short_description LIKE :query OR description LIKE :query)
         ORDER BY 
           CASE 
             WHEN name LIKE :exact THEN 1
             WHEN name LIKE :start THEN 2
             ELSE 3
           END,
           name ASC
         LIMIT 10",
        [
            'query' => "%{$query}%",
            'exact' => $query,
            'start' => "{$query}%"
        ]
    );
    
    foreach ($products as $product) {
        $results[] = [
            'type' => 'product',
            'id' => $product['id'],
            'title' => $product['name'],
            'url' => url('product.php?slug=' . $product['slug']),
            'price' => '$' . number_format($product['price'], 2),
            'image' => !empty($product['image']) ? asset('storage/uploads/' . $product['image']) : null,
            'description' => substr($product['short_description'] ?? '', 0, 100)
        ];
    }
}

if ($type === 'categories' || $type === 'all') {
    $categories = db()->fetchAll(
        "SELECT id, name, slug, description 
         FROM categories 
         WHERE is_active = 1 
         AND (name LIKE :query OR description LIKE :query)
         LIMIT 5",
        ['query' => "%{$query}%"]
    );
    
    foreach ($categories as $category) {
        $results[] = [
            'type' => 'category',
            'id' => $category['id'],
            'title' => $category['name'],
            'url' => url('products.php?category=' . $category['slug']),
            'description' => substr($category['description'] ?? '', 0, 100)
        ];
    }
}

echo json_encode(['results' => $results]);

