<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

use App\Models\QualityCertification;

// Check if table exists, create if not
try {
    db()->fetchOne("SELECT 1 FROM quality_certifications LIMIT 1");
} catch (Exception $e) {
    // Table doesn't exist, try to create it
    $sql = file_get_contents(__DIR__ . '/../database/quality-certifications.sql');
    try {
        db()->execute($sql);
    } catch (Exception $ex) {
        $error = 'Please run the SQL file: database/quality-certifications.sql';
    }
}

$certModel = new QualityCertification();
$message = '';
$error = '';

// Handle delete
if (!empty($_GET['delete'])) {
    try {
        $certId = (int)$_GET['delete'];
        if ($certId <= 0) {
            $error = 'Invalid certification ID.';
        } else {
            $cert = $certModel->getById($certId);
            if ($cert && !empty($cert['logo'])) {
                $logoPath = __DIR__ . '/../' . $cert['logo'];
                if (file_exists($logoPath)) {
                    @unlink($logoPath);
                }
            }
            $certModel->delete($certId);
            $message = 'Certification deleted successfully.';
        }
    } catch (\Exception $e) {
        $error = 'Error deleting certification: ' . $e->getMessage();
    }
}

// Handle toggle active
if (!empty($_GET['toggle'])) {
    try {
        $certId = (int)$_GET['toggle'];
        if ($certId > 0) {
            $cert = $certModel->getById($certId);
            if ($cert) {
                $certModel->update($certId, ['is_active' => $cert['is_active'] ? 0 : 1]);
                $message = 'Certification status updated.';
            }
        }
    } catch (\Exception $e) {
        $error = 'Error updating certification: ' . $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['cert_id'])) {
        // Update existing certification
        $certId = (int)$_POST['cert_id'];
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'reference_url' => trim($_POST['reference_url'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Handle logo upload
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../storage/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $file = $_FILES['logo'];
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            if (!in_array($file['type'], $allowedTypes)) {
                $error = 'Invalid file type. Please upload JPG, PNG, GIF, WebP, or SVG.';
            } elseif ($file['size'] > $maxSize) {
                $error = 'File size exceeds 2MB limit.';
            } else {
                // Delete old logo
                $oldCert = $certModel->getById($certId);
                if ($oldCert && !empty($oldCert['logo']) && file_exists(__DIR__ . '/../' . $oldCert['logo'])) {
                    @unlink(__DIR__ . '/../' . $oldCert['logo']);
                }

                // Upload new logo
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'cert_' . time() . '_' . uniqid() . '.' . $extension;
                $filepath = $uploadDir . $filename;

                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $data['logo'] = 'storage/uploads/' . $filename;
                } else {
                    $error = 'Failed to upload logo.';
                }
            }
        }

        if (empty($error)) {
            try {
                $certModel->update($certId, $data);
                $message = 'Certification updated successfully.';
            } catch (\Exception $e) {
                $error = 'Error updating certification: ' . $e->getMessage();
            }
        }
    } else {
        // Create new certification
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'reference_url' => trim($_POST['reference_url'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];

        // Handle logo upload
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../storage/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $file = $_FILES['logo'];
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            if (!in_array($file['type'], $allowedTypes)) {
                $error = 'Invalid file type. Please upload JPG, PNG, GIF, WebP, or SVG.';
            } elseif ($file['size'] > $maxSize) {
                $error = 'File size exceeds 2MB limit.';
            } else {
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'cert_' . time() . '_' . uniqid() . '.' . $extension;
                $filepath = $uploadDir . $filename;

                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $data['logo'] = 'storage/uploads/' . $filename;
                } else {
                    $error = 'Failed to upload logo.';
                }
            }
        } else {
            $error = 'Logo is required.';
        }

        if (empty($error)) {
            try {
                $certModel->create($data);
                $message = 'Certification added successfully.';
            } catch (\Exception $e) {
                $error = 'Error adding certification: ' . $e->getMessage();
            }
        }
    }
}

// Get all certifications
$certifications = $certModel->getAll();
$editingCert = null;
if (!empty($_GET['edit'])) {
    $editingCert = $certModel->getById((int)$_GET['edit']);
}

$pageTitle = 'Quality Certifications';
include __DIR__ . '/includes/header.php';
?>

<div class="w-full">
    <!-- Header -->
    <div class="bg-gradient-to-r from-gray-700 to-gray-900 rounded-xl shadow-xl p-4 md:p-6 lg:p-8 mb-4 md:mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-1 md:mb-2">
                    <i class="fas fa-certificate mr-2 md:mr-3"></i>
                    Quality Certifications
                </h1>
                <p class="text-gray-300 text-sm md:text-lg">Manage quality certification logos (ISO, CE, RIML, etc.)</p>
            </div>
            <div class="flex gap-2">
                <a href="<?= url('admin/quality-certifications-add-demo.php') ?>" class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-database mr-2"></i>Add Demo Data
                </a>
                <a href="<?= url('admin/quality-certifications.php') ?>" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>Add New
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
    <?php if ($editingCert || empty($certifications) || (isset($_GET['add']) && $_GET['add'] == '1')): ?>
    <div class="bg-white rounded-xl shadow-lg p-4 md:p-6 lg:p-8 mb-6">
        <h2 class="text-xl font-bold mb-4">
            <?= $editingCert ? 'Edit Certification' : 'Add New Certification' ?>
        </h2>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="cert_id" value="<?= $editingCert ? $editingCert['id'] : '' ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-certificate text-gray-400 mr-2"></i> Certification Name *
                    </label>
                    <input type="text" name="name" value="<?= escape($editingCert['name'] ?? '') ?>" required
                           placeholder="e.g., ISO 9001:2015"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-all">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-link text-gray-400 mr-2"></i> Reference URL
                    </label>
                    <input type="url" name="reference_url" value="<?= escape($editingCert['reference_url'] ?? '') ?>"
                           placeholder="https://example.com/certification"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-all">
                    <p class="text-xs text-gray-500 mt-1">Link to certification details or official source</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-sort-numeric-down text-gray-400 mr-2"></i> Sort Order
                    </label>
                    <input type="number" name="sort_order" value="<?= escape($editingCert['sort_order'] ?? 0) ?>"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-all">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-align-left text-gray-400 mr-2"></i> Description
                    </label>
                    <textarea name="description" rows="2" 
                              placeholder="Brief description of the certification (optional)"
                              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-all"><?= escape($editingCert['description'] ?? '') ?></textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-image text-gray-400 mr-2"></i> Certification Logo <?= $editingCert ? '' : '*' ?>
                    </label>
                    <?php if ($editingCert && !empty($editingCert['logo'])): ?>
                    <div class="mb-2">
                        <img src="<?= escape(image_url($editingCert['logo'])) ?>" alt="Current Logo" class="h-20 w-auto object-contain border-2 border-gray-200 rounded p-2">
                        <p class="text-xs text-gray-500 mt-1">Current logo. Upload a new one to replace it.</p>
                    </div>
                    <?php endif; ?>
                    <input type="file" name="logo" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp,image/svg+xml" <?= $editingCert ? '' : 'required' ?>
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-all">
                    <p class="text-xs text-gray-500 mt-1">Recommended: PNG or SVG with transparent background. Max size: 2MB.</p>
                </div>

                <div class="md:col-span-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" <?= ($editingCert['is_active'] ?? 1) ? 'checked' : '' ?>
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Active (show in slider)</span>
                    </label>
                </div>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" class="bg-gradient-to-r from-gray-700 to-gray-900 text-white px-8 py-3 rounded-lg font-bold text-lg hover:from-gray-800 hover:to-gray-950 transition-all duration-300 shadow-lg hover:shadow-xl">
                    <i class="fas fa-save mr-2"></i>
                    <?= $editingCert ? 'Update Certification' : 'Add Certification' ?>
                </button>
                <a href="<?= url('admin/quality-certifications.php') ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-8 py-3 rounded-lg font-bold text-lg transition-all duration-300">
                    Cancel
                </a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Certifications List -->
    <div class="bg-white rounded-xl shadow-lg p-4 md:p-6 lg:p-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold">All Quality Certifications</h2>
            <div class="flex gap-2">
                <a href="<?= url('admin/quality-certifications-add-demo.php') ?>" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-database mr-2"></i>Add Demo
                </a>
                <a href="<?= url('admin/quality-certifications.php?add=1') ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>Add New
                </a>
            </div>
        </div>

        <?php if (empty($certifications)): ?>
        <div class="text-center py-12">
            <i class="fas fa-certificate text-gray-300 text-6xl mb-4"></i>
            <p class="text-gray-500 text-lg">No certifications yet.</p>
            <a href="<?= url('admin/quality-certifications.php?add=1') ?>" class="inline-block mt-4 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors">
                <i class="fas fa-plus mr-2"></i>Add First Certification
            </a>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b-2 border-gray-200">
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Logo</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Name</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Description</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Reference URL</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Order</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Status</th>
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($certifications as $cert): ?>
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="py-3 px-4">
                            <?php if (!empty($cert['logo'])): ?>
                            <img src="<?= escape(image_url($cert['logo'])) ?>" alt="<?= escape($cert['name']) ?>" class="h-12 w-auto object-contain">
                            <?php else: ?>
                            <span class="text-gray-400">No logo</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-4 font-medium"><?= escape($cert['name']) ?></td>
                        <td class="py-3 px-4 text-sm text-gray-600"><?= escape($cert['description'] ?? '-') ?></td>
                        <td class="py-3 px-4">
                            <?php if (!empty($cert['reference_url'])): ?>
                            <a href="<?= escape($cert['reference_url']) ?>" target="_blank" class="text-blue-600 hover:underline">
                                <i class="fas fa-external-link-alt mr-1"></i>View
                            </a>
                            <?php else: ?>
                            <span class="text-gray-400">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-4"><?= escape($cert['sort_order']) ?></td>
                        <td class="py-3 px-4">
                            <a href="?toggle=<?= $cert['id'] ?>" class="px-2 py-1 rounded text-xs <?= $cert['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= $cert['is_active'] ? 'Active' : 'Inactive' ?>
                            </a>
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex gap-2">
                                <a href="?edit=<?= $cert['id'] ?>" class="text-blue-600 hover:text-blue-800" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete=<?= $cert['id'] ?>" class="text-red-600 hover:text-red-800" title="Delete" onclick="return confirm('Are you sure you want to delete this certification?')">
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
