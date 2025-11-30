<?php
/**
 * Wishlist API
 */
require_once __DIR__ . '/../bootstrap/app.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $productId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($productId && !in_array($productId, $_SESSION['wishlist'])) {
            $_SESSION['wishlist'][] = $productId;
            echo json_encode(['success' => true, 'message' => 'Added to wishlist']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Already in wishlist']);
        }
        break;
        
    case 'remove':
        $productId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $_SESSION['wishlist'] = array_values(array_filter($_SESSION['wishlist'], fn($id) => $id != $productId));
        echo json_encode(['success' => true, 'message' => 'Removed from wishlist']);
        break;
        
    case 'count':
        echo json_encode(['count' => count($_SESSION['wishlist'])]);
        break;
        
    default:
        echo json_encode(['wishlist' => $_SESSION['wishlist']]);
}

