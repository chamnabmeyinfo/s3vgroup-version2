<?php
/**
 * Image Upload Handler
 */
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'file' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    // Check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'Upload error occurred.';
        echo json_encode($response);
        exit;
    }
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        $response['message'] = 'Invalid file type. Only images are allowed.';
        echo json_encode($response);
        exit;
    }
    
    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        $response['message'] = 'File too large. Maximum size is 5MB.';
        echo json_encode($response);
        exit;
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_', true) . '.' . $extension;
    $uploadDir = __DIR__ . '/../storage/uploads/';
    $filepath = $uploadDir . $filename;
    
    // Create upload directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $response['success'] = true;
        $response['message'] = 'File uploaded successfully.';
        $response['file'] = $filename;
        $response['url'] = asset('storage/uploads/' . $filename);
    } else {
        $response['message'] = 'Failed to move uploaded file.';
    }
} else {
    $response['message'] = 'No file uploaded.';
}

echo json_encode($response);

