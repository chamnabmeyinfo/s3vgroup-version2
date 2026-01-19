<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

use App\Models\Partner;

// Check if table exists, create if not
try {
    db()->fetchOne("SELECT 1 FROM partners LIMIT 1");
} catch (Exception $e) {
    // Table doesn't exist, try to create it
    $sql = file_get_contents(__DIR__ . '/../database/partners-clients.sql');
    try {
        db()->execute($sql);
    } catch (Exception $ex) {
        $error = 'Please run the SQL file: database/partners-clients.sql';
    }
}

$partnerModel = new Partner();
$message = '';
$error = '';

// Handle delete
if (!empty($_GET['delete'])) {
    try {
        $clientId = (int)$_GET['delete'];
        if ($clientId <= 0) {
            $error = 'Invalid client ID.';
        } else {
            $client = $partnerModel->getById($clientId);
            // Only allow deleting clients
            if ($client && $client['type'] === 'client') {
                if (!empty($client['logo'])) {
                    $logoPath = __DIR__ . '/../' . $client['logo'];
                    if (file_exists($logoPath)) {
                        @unlink($logoPath);
                    }
                }
                $partnerModel->delete($clientId);
                $message = 'Client deleted successfully.';
            } else {
                $error = 'Invalid client or not a client record.';
            }
        }
    } catch (\Exception $e) {
        $error = 'Error deleting client: ' . $e->getMessage();
    }
}

// Handle toggle active
if (!empty($_GET['toggle'])) {
    try {
        $clientId = (int)$_GET['toggle'];
        if ($clientId > 0) {
            $client = $partnerModel->getById($clientId);
            if ($client && $client['type'] === 'client') {
                $partnerModel->update($clientId, ['is_active' => $client['is_active'] ? 0 : 1]);
                $message = 'Client status updated.';
            }
        }
    } catch (\Exception $e) {
        $error = 'Error updating client: ' . $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['client_id'])) {
        // Update existing client
        $clientId = (int)$_POST['client_id'];
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'website_url' => trim($_POST['website_url'] ?? ''),
            'type' => 'client', // Always set to client
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Handle logo upload
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../storage/uploads/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    $error = 'Failed to create upload directory. Please check permissions.';
                }
            }
            
            // Ensure directory is writable
            if (empty($error) && !is_writable($uploadDir)) {
                @chmod($uploadDir, 0755);
                if (!is_writable($uploadDir)) {
                    $error = 'Upload directory is not writable. Please check permissions.';
                }
            }
            
            if (empty($error)) {
                $file = $_FILES['logo'];
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
                $maxSize = 2 * 1024 * 1024; // 2MB

                // Get file extension
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                // Validate extension
                if (!in_array($extension, $allowedExtensions)) {
                    $error = 'Invalid file type. Please upload JPG, PNG, GIF, WebP, or SVG.';
                } 
                // Validate MIME type
                elseif (!in_array($file['type'], $allowedTypes)) {
                    // Double-check with file info for better security
                    if (function_exists('finfo_open')) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mimeType = finfo_file($finfo, $file['tmp_name']);
                        finfo_close($finfo);
                        
                        if (!in_array($mimeType, $allowedTypes)) {
                            $error = 'Invalid file type. Please upload JPG, PNG, GIF, WebP, or SVG.';
                        }
                    } else {
                        // If finfo is not available, just check extension (less secure but works)
                        $error = 'Invalid file type. Please upload JPG, PNG, GIF, WebP, or SVG.';
                    }
                }
                // Validate file size
                elseif ($file['size'] > $maxSize) {
                    $error = 'File size exceeds 2MB limit.';
                }
                // Check for upload errors
                elseif ($file['error'] !== UPLOAD_ERR_OK) {
                    $uploadErrors = [
                        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive.',
                        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive.',
                        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
                        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
                    ];
                    $error = 'Upload error: ' . ($uploadErrors[$file['error']] ?? 'Unknown error');
                }
                // Validate file is actually an image (for non-SVG)
                elseif ($extension !== 'svg' && !@getimagesize($file['tmp_name'])) {
                    $error = 'File is not a valid image.';
                }
                else {
                    // Delete old logo first (only for updates)
                    if (!empty($clientId)) {
                        $oldClient = $partnerModel->getById($clientId);
                        if ($oldClient && !empty($oldClient['logo'])) {
                            $oldLogoPath = __DIR__ . '/../' . $oldClient['logo'];
                            if (file_exists($oldLogoPath)) {
                                @unlink($oldLogoPath);
                            }
                        }
                    }

                    // Generate unique filename
                    $filename = 'client_' . time() . '_' . uniqid() . '.' . $extension;
                    $filepath = $uploadDir . $filename;

                    // Resize and save image (max 800x800 for logos)
                    if (resize_and_save_image($file['tmp_name'], $filepath, 800, 800, 85)) {
                        // Verify file was actually saved
                        if (file_exists($filepath) && filesize($filepath) > 0) {
                            $data['logo'] = 'storage/uploads/' . $filename;
                        } else {
                            $error = 'File upload failed. File may be corrupted.';
                            @unlink($filepath); // Clean up
                        }
                    } else {
                        $error = 'Failed to process logo. Please check directory permissions and ensure GD extension is enabled.';
                    }
                }
            }
        }

        if (empty($error)) {
            try {
                $partnerModel->update($clientId, $data);
                $message = 'Client updated successfully.';
            } catch (\Exception $e) {
                $error = 'Error updating client: ' . $e->getMessage();
            }
        }
    } else {
        // Create new client
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'website_url' => trim($_POST['website_url'] ?? ''),
            'type' => 'client', // Always set to client
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Handle logo upload
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../storage/uploads/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    $error = 'Failed to create upload directory. Please check permissions.';
                }
            }
            
            // Ensure directory is writable
            if (empty($error) && !is_writable($uploadDir)) {
                @chmod($uploadDir, 0755);
                if (!is_writable($uploadDir)) {
                    $error = 'Upload directory is not writable. Please check permissions.';
                }
            }
            
            if (empty($error)) {
                $file = $_FILES['logo'];
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
                $maxSize = 2 * 1024 * 1024; // 2MB

                // Get file extension
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                // Validate extension
                if (!in_array($extension, $allowedExtensions)) {
                    $error = 'Invalid file type. Please upload JPG, PNG, GIF, WebP, or SVG.';
                } 
                // Validate MIME type
                elseif (!in_array($file['type'], $allowedTypes)) {
                    // Double-check with file info for better security
                    if (function_exists('finfo_open')) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mimeType = finfo_file($finfo, $file['tmp_name']);
                        finfo_close($finfo);
                        
                        if (!in_array($mimeType, $allowedTypes)) {
                            $error = 'Invalid file type. Please upload JPG, PNG, GIF, WebP, or SVG.';
                        }
                    } else {
                        // If finfo is not available, just check extension (less secure but works)
                        $error = 'Invalid file type. Please upload JPG, PNG, GIF, WebP, or SVG.';
                    }
                }
                // Validate file size
                elseif ($file['size'] > $maxSize) {
                    $error = 'File size exceeds 2MB limit.';
                }
                // Check for upload errors
                elseif ($file['error'] !== UPLOAD_ERR_OK) {
                    $uploadErrors = [
                        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive.',
                        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive.',
                        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
                        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
                    ];
                    $error = 'Upload error: ' . ($uploadErrors[$file['error']] ?? 'Unknown error');
                }
                // Validate file is actually an image (for non-SVG)
                elseif ($extension !== 'svg' && !@getimagesize($file['tmp_name'])) {
                    $error = 'File is not a valid image.';
                }
                else {
                    // Generate unique filename
                    $filename = 'client_' . time() . '_' . uniqid() . '.' . $extension;
                    $filepath = $uploadDir . $filename;

                    // Ensure directory is writable
                    if (!is_writable($uploadDir)) {
                        $error = 'Upload directory is not writable. Please check permissions.';
                    }
                    // Resize and save image (max 800x800 for logos)
                    elseif (resize_and_save_image($file['tmp_name'], $filepath, 800, 800, 85)) {
                        // Verify file was actually saved
                        if (file_exists($filepath) && filesize($filepath) > 0) {
                            $data['logo'] = 'storage/uploads/' . $filename;
                        } else {
                            $error = 'File upload failed. File may be corrupted.';
                            @unlink($filepath); // Clean up
                        }
                    } else {
                        $error = 'Failed to process logo. Please check directory permissions and ensure GD extension is enabled.';
                    }
                }
            }
        } else {
            // No file uploaded - logo is required for new clients only
            if (empty($clientId)) {
                $error = 'Logo is required.';
            }
        }

        if (empty($error)) {
            try {
                $partnerModel->create($data);
                $message = 'Client added successfully.';
            } catch (\Exception $e) {
                $error = 'Error adding client: ' . $e->getMessage();
            }
        }
    }
}

// Get all clients (filter by type='client')
$clients = $partnerModel->getByType('client', false);
$editingClient = null;
if (!empty($_GET['edit'])) {
    $editingClient = $partnerModel->getById((int)$_GET['edit']);
    // Verify it's actually a client
    if ($editingClient && $editingClient['type'] !== 'client') {
        $editingClient = null;
        $error = 'Invalid client record.';
    }
}

$pageTitle = 'Clients';
include __DIR__ . '/includes/header.php';
?>

<div class="w-full">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-700 to-emerald-900 rounded-xl shadow-xl p-4 md:p-6 lg:p-8 mb-4 md:mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-1 md:mb-2">
                    <i class="fas fa-building mr-2 md:mr-3"></i>
                    Clients
                </h1>
                <p class="text-gray-300 text-sm md:text-lg">Manage client logos and information</p>
            </div>
            <div class="flex gap-2">
                <a href="<?= url('admin/clients.php?add=1') ?>" class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>Add New Client
                </a>
            </div>
        </div>
    </div>

    <?php if (!empty($message)): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2 text-xl"></i>
            <span class="font-semibold"><?= escape($message) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2 text-xl"></i>
            <span class="font-semibold"><?= escape($error) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Add/Edit Form -->
    <?php if ($editingClient || empty($clients) || (isset($_GET['add']) && $_GET['add'] == '1')): ?>
    <div class="bg-white rounded-xl shadow-lg p-4 md:p-6 lg:p-8 mb-6">
        <h2 class="text-xl font-bold mb-4">
            <?= $editingClient ? 'Edit Client' : 'Add New Client' ?>
        </h2>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="client_id" value="<?= $editingClient ? $editingClient['id'] : '' ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-building text-gray-400 mr-2"></i> Name *
                    </label>
                    <input type="text" name="name" value="<?= escape($editingClient['name'] ?? '') ?>" required
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-link text-gray-400 mr-2"></i> Website URL
                    </label>
                    <input type="url" name="website_url" value="<?= escape($editingClient['website_url'] ?? '') ?>"
                           placeholder="https://example.com"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-sort-numeric-down text-gray-400 mr-2"></i> Sort Order
                    </label>
                    <input type="number" name="sort_order" value="<?= escape($editingClient['sort_order'] ?? 0) ?>"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-image text-gray-400 mr-2"></i> Logo <?= $editingClient ? '' : '*' ?>
                    </label>
                    <?php if ($editingClient && !empty($editingClient['logo'])): ?>
                    <div class="mb-2">
                        <img src="<?= escape(image_url($editingClient['logo'])) ?>" alt="Current Logo" class="h-20 w-auto object-contain border-2 border-gray-200 rounded p-2">
                        <p class="text-xs text-gray-500 mt-1">Current logo. Upload a new one to replace it.</p>
                    </div>
                    <?php endif; ?>
                    <input type="file" name="logo" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp,image/svg+xml" <?= $editingClient ? '' : 'required' ?>
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all">
                    <p class="text-xs text-gray-500 mt-1">Recommended: PNG or SVG with transparent background. Max size: 2MB.</p>
                </div>

                <div class="md:col-span-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" <?= ($editingClient['is_active'] ?? 1) ? 'checked' : '' ?>
                               class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                        <span class="ml-2 text-sm text-gray-700">Active (show in slider)</span>
                    </label>
                </div>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" class="bg-gradient-to-r from-green-700 to-emerald-900 text-white px-8 py-3 rounded-lg font-bold text-lg hover:from-green-800 hover:to-emerald-950 transition-all duration-300 shadow-lg hover:shadow-xl">
                    <i class="fas fa-save mr-2"></i>
                    <?= $editingClient ? 'Update Client' : 'Add Client' ?>
                </button>
                <a href="<?= url('admin/clients.php') ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-8 py-3 rounded-lg font-bold text-lg transition-all duration-300">
                    Cancel
                </a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Clients List -->
    <div class="bg-white rounded-xl shadow-lg p-4 md:p-6 lg:p-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold">All Clients</h2>
            <div class="flex gap-2">
                <a href="<?= url('admin/clients.php?add=1') ?>" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>Add New
                </a>
            </div>
        </div>

        <?php if (empty($clients)): ?>
        <div class="text-center py-12">
            <i class="fas fa-building text-gray-300 text-6xl mb-4"></i>
            <p class="text-gray-500 text-lg">No clients yet.</p>
            <a href="<?= url('admin/clients.php?add=1') ?>" class="inline-block mt-4 bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition-colors">
                <i class="fas fa-plus mr-2"></i>Add First Client
            </a>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b-2 border-gray-200">
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Logo</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Name</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Website</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Order</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-3 px-4">
                            <?php if (!empty($client['logo'])): ?>
                            <img src="<?= escape(image_url($client['logo'])) ?>" alt="<?= escape($client['name']) ?>" class="h-12 w-auto object-contain">
                            <?php else: ?>
                            <span class="text-gray-400">No logo</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-4 font-medium"><?= escape($client['name']) ?></td>
                        <td class="py-3 px-4">
                            <?php if (!empty($client['website_url'])): ?>
                            <a href="<?= escape($client['website_url']) ?>" target="_blank" class="text-green-600 hover:underline">
                                <i class="fas fa-external-link-alt mr-1"></i>Visit
                            </a>
                            <?php else: ?>
                            <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-4"><?= escape($client['sort_order']) ?></td>
                        <td class="py-3 px-4">
                            <a href="?toggle=<?= $client['id'] ?>" class="px-2 py-1 rounded text-xs <?= $client['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= $client['is_active'] ? 'Active' : 'Inactive' ?>
                            </a>
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex gap-2">
                                <a href="?edit=<?= $client['id'] ?>" class="text-green-600 hover:text-green-800" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete=<?= $client['id'] ?>" class="text-red-600 hover:text-red-800" title="Delete" onclick="return confirm('Are you sure you want to delete this client?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
