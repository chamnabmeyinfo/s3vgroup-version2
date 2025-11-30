<?php
/**
 * Bulk Operations Handler
 */
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productIds = $_POST['product_ids'] ?? [];
    
    if (empty($productIds) || !is_array($productIds)) {
        $response['message'] = 'No products selected.';
        echo json_encode($response);
        exit;
    }
    
    $productIds = array_map('intval', $productIds);
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    
    try {
        switch ($action) {
            case 'delete':
                db()->query("UPDATE products SET is_active = 0 WHERE id IN ($placeholders)", $productIds);
                $response['success'] = true;
                $response['message'] = count($productIds) . ' product(s) deleted successfully.';
                break;
                
            case 'activate':
                db()->query("UPDATE products SET is_active = 1 WHERE id IN ($placeholders)", $productIds);
                $response['success'] = true;
                $response['message'] = count($productIds) . ' product(s) activated successfully.';
                break;
                
            case 'deactivate':
                db()->query("UPDATE products SET is_active = 0 WHERE id IN ($placeholders)", $productIds);
                $response['success'] = true;
                $response['message'] = count($productIds) . ' product(s) deactivated successfully.';
                break;
                
            case 'feature':
                db()->query("UPDATE products SET is_featured = 1 WHERE id IN ($placeholders)", $productIds);
                $response['success'] = true;
                $response['message'] = count($productIds) . ' product(s) marked as featured.';
                break;
                
            case 'unfeature':
                db()->query("UPDATE products SET is_featured = 0 WHERE id IN ($placeholders)", $productIds);
                $response['success'] = true;
                $response['message'] = count($productIds) . ' product(s) unmarked as featured.';
                break;
                
            default:
                $response['message'] = 'Invalid action.';
        }
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);

