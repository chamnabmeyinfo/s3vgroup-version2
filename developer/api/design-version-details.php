<?php
/**
 * API: Get Design Version Details
 */

require_once __DIR__ . '/../../bootstrap/app.php';

use App\Services\DesignVersionService;

header('Content-Type: application/json');

$versionId = $_GET['version_id'] ?? '';

if (empty($versionId)) {
    echo json_encode(['success' => false, 'error' => 'Version ID required']);
    exit;
}

$versionService = new DesignVersionService();
$version = $versionService->getVersionDetails($versionId);

if ($version) {
    echo json_encode(['success' => true, 'version' => $version]);
} else {
    echo json_encode(['success' => false, 'error' => 'Version not found']);
}

