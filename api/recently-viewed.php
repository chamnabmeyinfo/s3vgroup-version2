<?php
/**
 * Track Recently Viewed Products
 */
require_once __DIR__ . '/../bootstrap/app.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$productId = (int)($_GET['product_id'] ?? $_POST['product_id'] ?? 0);

if (!$productId) {
    echo json_encode(['success' => false]);
    exit;
}

// Track in session
if (!isset($_SESSION['recently_viewed'])) {
    $_SESSION['recently_viewed'] = [];
}

// Remove if already exists
$_SESSION['recently_viewed'] = array_filter($_SESSION['recently_viewed'], fn($id) => $id != $productId);

// Add to beginning
array_unshift($_SESSION['recently_viewed'], $productId);

// Keep only last 10
$_SESSION['recently_viewed'] = array_slice($_SESSION['recently_viewed'], 0, 10);

// Also track in database for analytics (optional)
try {
    $sessionId = session_id();
    db()->query(
        "INSERT INTO recently_viewed (session_id, product_id) 
         VALUES (:session_id, :product_id)
         ON DUPLICATE KEY UPDATE viewed_at = CURRENT_TIMESTAMP",
        ['session_id' => $sessionId, 'product_id' => $productId]
    );
} catch (Exception $e) {
    // Ignore database errors for tracking
}

echo json_encode(['success' => true]);

