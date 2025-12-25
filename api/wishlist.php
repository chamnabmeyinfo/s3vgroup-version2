<?php
/**
 * Wishlist API
 */
require_once __DIR__ . '/../bootstrap/app.php';

// CORS headers
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = [
    'https://www.s3vtgroup.com.kh',
    'https://s3vtgroup.com.kh',
    'http://localhost',
    'http://127.0.0.1'
];

if (in_array($origin, $allowedOrigins) || strpos($origin, 'localhost') !== false || strpos($origin, '127.0.0.1') !== false) {
    header("Access-Control-Allow-Origin: $origin");
}
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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
            echo json_encode([
                'success' => true, 
                'message' => 'Added to wishlist',
                'count' => count($_SESSION['wishlist'])
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Already in wishlist',
                'count' => count($_SESSION['wishlist'])
            ]);
        }
        break;
        
    case 'remove':
        $productId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $_SESSION['wishlist'] = array_values(array_filter($_SESSION['wishlist'], fn($id) => $id != $productId));
        echo json_encode([
            'success' => true, 
            'message' => 'Removed from wishlist',
            'count' => count($_SESSION['wishlist'])
        ]);
        break;
        
    case 'count':
        echo json_encode(['count' => count($_SESSION['wishlist'])]);
        break;
        
    default:
        echo json_encode([
            'wishlist' => $_SESSION['wishlist'],
            'count' => count($_SESSION['wishlist'])
        ]);
}

