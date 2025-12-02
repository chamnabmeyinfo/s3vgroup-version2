<?php
/**
 * Backup Download (Developer Only)
 */

require_once __DIR__ . '/../bootstrap/app.php';

// Ensure we're using developer session
if (session_status() === PHP_SESSION_NONE) {
    session_name('developer_session');
    session_start();
}

// Check if developer is logged in
if (!isset($_SESSION['developer_logged_in']) || $_SESSION['developer_logged_in'] !== true) {
    header('Location: ' . url('developer/login.php'));
    exit;
}

use App\Core\Backup\BackupService;

$filename = basename($_GET['file'] ?? '');
$backupService = new BackupService();

if (!$filename || !preg_match('/^db_backup_.+\.sql(\.gz)?$/', $filename)) {
    die('Invalid file');
}

$filePath = __DIR__ . '/../storage/backups/' . $filename;

if (!file_exists($filePath)) {
    die('File not found');
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filePath));

readfile($filePath);
exit;

