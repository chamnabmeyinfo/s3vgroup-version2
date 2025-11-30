<?php
/**
 * Shopping Cart API
 */
require_once __DIR__ . '/../bootstrap/app.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$productId = (int)($_GET['product_id'] ?? $_POST['product_id'] ?? 0);

$response = ['success' => false, 'message' => '', 'count' => count($_SESSION['cart'])];

switch ($action) {
    case 'add':
        if ($productId) {
            if (!isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId] = 0;
            }
            $_SESSION['cart'][$productId]++;
            $response['success'] = true;
            $response['message'] = 'Product added to cart';
            $response['count'] = array_sum($_SESSION['cart']);
        }
        break;
        
    case 'update':
        $quantity = (int)($_GET['quantity'] ?? $_POST['quantity'] ?? 1);
        if ($productId) {
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$productId]);
            } else {
                $_SESSION['cart'][$productId] = $quantity;
            }
            $response['success'] = true;
            $response['count'] = array_sum($_SESSION['cart']);
        }
        break;
        
    case 'remove':
        if ($productId) {
            unset($_SESSION['cart'][$productId]);
            $response['success'] = true;
            $response['message'] = 'Product removed from cart';
            $response['count'] = array_sum($_SESSION['cart']);
        }
        break;
        
    case 'clear':
        $_SESSION['cart'] = [];
        $response['success'] = true;
        $response['message'] = 'Cart cleared';
        $response['count'] = 0;
        break;
        
    case 'count':
        $response['success'] = true;
        $response['count'] = array_sum($_SESSION['cart']);
        break;
        
    default:
        $response['cart'] = $_SESSION['cart'];
        $response['success'] = true;
}

echo json_encode($response);

