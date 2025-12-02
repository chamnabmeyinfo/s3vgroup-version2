<?php
/**
 * Product Comparison API
 */
require_once __DIR__ . '/../bootstrap/app.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $productId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($productId) {
            $compare = $_SESSION['compare'] ?? [];
            if (!in_array($productId, $compare) && count($compare) < 4) {
                $compare[] = $productId;
                $_SESSION['compare'] = $compare;
                echo json_encode(['success' => true, 'count' => count($compare), 'message' => 'Added to comparison']);
            } elseif (count($compare) >= 4) {
                echo json_encode(['success' => false, 'message' => 'Maximum 4 products can be compared']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Already in comparison']);
            }
        }
        break;
        
    case 'remove':
        $productId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $compare = $_SESSION['compare'] ?? [];
        $_SESSION['compare'] = array_values(array_filter($compare, fn($id) => $id != $productId));
        echo json_encode(['success' => true, 'count' => count($_SESSION['compare'])]);
        break;
        
    case 'clear':
        $_SESSION['compare'] = [];
        echo json_encode(['success' => true]);
        break;
        
    case 'get':
        echo json_encode(['compare' => $_SESSION['compare'] ?? []]);
        break;
        
    default:
        echo json_encode(['compare' => $_SESSION['compare'] ?? []]);
}

