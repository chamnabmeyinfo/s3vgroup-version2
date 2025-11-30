<?php
/**
 * Image Upload Handler - Supports Multiple Files
 */
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'files' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = __DIR__ . '/../storage/uploads/';
    
    // Create upload directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $uploadedFiles = [];
    $errors = [];
    
    // Handle multiple files (files[]) or single file (file)
    $files = [];
    if (isset($_FILES['files']) && is_array($_FILES['files']['name'])) {
        // Multiple files
        for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
            $files[] = [
                'name' => $_FILES['files']['name'][$i],
                'type' => $_FILES['files']['type'][$i],
                'tmp_name' => $_FILES['files']['tmp_name'][$i],
                'error' => $_FILES['files']['error'][$i],
                'size' => $_FILES['files']['size'][$i]
            ];
        }
    } elseif (isset($_FILES['file'])) {
        // Single file (backward compatibility)
        $files[] = $_FILES['file'];
    }
    
    foreach ($files as $file) {
        // Check for errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = $file['name'] . ': Upload error occurred.';
            continue;
        }
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            $errors[] = $file['name'] . ': Invalid file type. Only images are allowed.';
            continue;
        }
        
        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            $errors[] = $file['name'] . ': File too large. Maximum size is 5MB.';
            continue;
        }
        
        // Generate unique filename (preserve original name if possible, or use unique ID)
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
        // Sanitize filename
        $originalName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);
        $filename = $originalName . '_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        // Ensure unique filename
        $counter = 1;
        while (file_exists($filepath)) {
            $filename = $originalName . '_' . uniqid() . '_' . $counter . '.' . $extension;
            $filepath = $uploadDir . $filename;
            $counter++;
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $uploadedFiles[] = [
                'filename' => $filename,
                'url' => asset('storage/uploads/' . $filename),
                'size' => $file['size']
            ];
        } else {
            $errors[] = $file['name'] . ': Failed to move uploaded file.';
        }
    }
    
    if (!empty($uploadedFiles)) {
        $response['success'] = true;
        $response['message'] = 'Successfully uploaded ' . count($uploadedFiles) . ' file(s).';
        $response['files'] = $uploadedFiles;
        if (!empty($errors)) {
            $response['message'] .= ' ' . count($errors) . ' file(s) failed.';
            $response['errors'] = $errors;
        }
    } else {
        $response['message'] = !empty($errors) ? implode(' ', $errors) : 'No files uploaded.';
    }
} else {
    $response['message'] = 'No files uploaded.';
}

echo json_encode($response);

