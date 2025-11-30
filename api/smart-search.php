<?php
/**
 * Smart Search API
 */
require_once __DIR__ . '/../bootstrap/app.php';

use App\Services\SmartSearch;

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'search';
$query = trim($_GET['q'] ?? $_GET['query'] ?? '');
$categoryId = !empty($_GET['category_id']) ? (int)$_GET['category_id'] : null;

$searchService = new SmartSearch();

try {
    if ($action === 'autocomplete') {
        $suggestions = $searchService->autocomplete($query, 10);
        echo json_encode([
            'success' => true,
            'suggestions' => $suggestions
        ]);
    } else {
        $results = $searchService->search($query, [
            'category_id' => $categoryId,
            'limit' => 20
        ]);
        
        // Format products
        $products = [];
        foreach ($results['products'] as $product) {
            $products[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'slug' => $product['slug'],
                'price' => $product['price'],
                'sale_price' => $product['sale_price'] ?? null,
                'image' => $product['image'] ?? null,
                'short_description' => $product['short_description'] ?? '',
                'category_name' => $product['category_name'] ?? '',
                'url' => url('product.php?slug=' . $product['slug'])
            ];
        }
        
        echo json_encode([
            'success' => true,
            'products' => $products,
            'suggestions' => $results['suggestions'],
            'related' => $results['related'],
            'count' => count($products)
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Search error'
    ]);
}

