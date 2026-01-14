<?php
/**
 * Toggle Product Featured Status API
 * Only accessible to admin users
 */
require_once __DIR__ . '/../bootstrap/app.php';

header('Content-Type: application/json');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin
if (!session('admin_logged_in')) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized. Admin access required.'
    ]);
    exit;
}

use App\Models\Product;

$productModel = new Product();
$response = ['success' => false, 'message' => ''];

// Get product ID
$productId = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);

if ($productId <= 0) {
    $response['message'] = 'Invalid product ID.';
    echo json_encode($response);
    exit;
}

try {
    // Get current product
    $product = $productModel->getById($productId);
    
    if (!$product) {
        $response['message'] = 'Product not found.';
        echo json_encode($response);
        exit;
    }
    
    // Toggle featured status
    $newFeaturedStatus = $product['is_featured'] ? 0 : 1;
    
    $productModel->update($productId, [
        'is_featured' => $newFeaturedStatus
    ]);
    
    $response['success'] = true;
    $response['message'] = $newFeaturedStatus ? 'Product marked as featured.' : 'Product unmarked as featured.';
    $response['is_featured'] = (bool)$newFeaturedStatus;
    
} catch (\Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
