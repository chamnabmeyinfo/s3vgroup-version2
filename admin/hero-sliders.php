<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

$db = db();
$message = '';
$error = '';

// Handle delete
if (!empty($_GET['delete'])) {
    try {
        $id = (int)$_GET['delete'];
        if ($id <= 0) {
            $error = 'Invalid slider ID.';
        } else {
            $deleted = $db->delete('hero_sliders', 'id = :id', ['id' => $id]);
            if ($deleted > 0) {
                $message = 'Hero slider deleted successfully.';
            } else {
                $error = 'Hero slider not found.';
            }
        }
    } catch (\Exception $e) {
        $error = 'Error deleting slider: ' . $e->getMessage();
    }
}

// Handle toggle status
if (!empty($_GET['toggle'])) {
    try {
        $id = (int)$_GET['toggle'];
        $slider = $db->fetchOne("SELECT is_active FROM hero_sliders WHERE id = :id", ['id' => $id]);
        if ($slider) {
            $newStatus = $slider['is_active'] ? 0 : 1;
            $db->update('hero_sliders', ['is_active' => $newStatus], 'id = :id', ['id' => $id]);
            $message = 'Slider status updated successfully.';
        } else {
            $error = 'Slider not found.';
        }
    } catch (\Exception $e) {
        $error = 'Error updating status: ' . $e->getMessage();
    }
}

// Get all sliders
$sliders = $db->fetchAll("SELECT * FROM hero_sliders ORDER BY sort_order ASC, id DESC");

$pageTitle = 'Hero Sliders';
include __DIR__ . '/includes/header.php';
?>

<div class="w-full p-4 md:p-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl shadow-xl p-4 md:p-6 lg:p-8 mb-4 md:mb-6 text-white">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-1 md:mb-2">
                    <i class="fas fa-images mr-2 md:mr-3"></i>
                    Hero Sliders
                </h1>
                <p class="text-blue-100 text-sm md:text-lg">Manage homepage hero slider slides</p>
            </div>
            <a href="<?= url('admin/hero-slider-edit.php') ?>" class="bg-white text-blue-600 px-4 md:px-6 py-2 md:py-3 rounded-lg font-bold hover:bg-blue-50 transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                <i class="fas fa-plus mr-2"></i>Add New Slider
            </a>
        </div>
    </div>

    <?php if (!empty($message)): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-4 md:mb-6">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2 text-xl"></i>
            <span class="font-semibold"><?= escape($message) ?></span>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-4 md:mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2 text-xl"></i>
            <span class="font-semibold"><?= escape($error) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Sliders Grid -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <?php if (empty($sliders)): ?>
            <div class="p-8 md:p-12 text-center">
                <i class="fas fa-images text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-xl font-bold text-gray-700 mb-2">No Hero Sliders</h3>
                <p class="text-gray-500 mb-6">Get started by creating your first hero slider.</p>
                <a href="<?= url('admin/hero-slider-edit.php') ?>" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-blue-700 transition-all">
                    <i class="fas fa-plus mr-2"></i>Add First Slider
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Image</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Title</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Subtitle</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Order</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($sliders as $slider): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <?php if ($slider['image']): ?>
                                        <img src="<?= escape(image_url($slider['image'])) ?>" 
                                             alt="<?= escape($slider['title']) ?>" 
                                             class="w-20 h-16 object-cover rounded-lg border border-gray-200"
                                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27100%27 height=%2775%27%3E%3Crect fill=%27%23ddd%27 width=%27100%27 height=%2775%27/%3E%3Ctext fill=%27%23999%27 x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27%3ENo Image%3C/text%3E%3C/svg%3E'">
                                    <?php else: ?>
                                        <div class="w-20 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-image text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-900"><?= escape($slider['title']) ?></div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-gray-600"><?= escape(mb_substr($slider['subtitle'] ?? '', 0, 50)) ?><?= mb_strlen($slider['subtitle'] ?? '') > 50 ? '...' : '' ?></div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm font-semibold">
                                        <?= escape($slider['sort_order']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="?toggle=<?= $slider['id'] ?>" 
                                       class="inline-block px-3 py-1 rounded-full text-xs font-semibold transition-all <?= $slider['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                        <?= $slider['is_active'] ? 'Active' : 'Inactive' ?>
                                    </a>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center space-x-2">
                                        <a href="<?= url("admin/hero-slider-edit.php?id={$slider['id']}") ?>" 
                                           class="bg-blue-100 hover:bg-blue-200 text-blue-700 p-2 rounded-lg transition-all" 
                                           title="Edit">
                                            <i class="fas fa-edit text-sm"></i>
                                        </a>
                                        <a href="<?= url("admin/hero-slider-duplicate.php?id={$slider['id']}") ?>" 
                                           onclick="return confirm('Duplicate this slider?')" 
                                           class="bg-purple-100 hover:bg-purple-200 text-purple-700 p-2 rounded-lg transition-all" 
                                           title="Duplicate">
                                            <i class="fas fa-copy text-sm"></i>
                                        </a>
                                        <a href="?delete=<?= $slider['id'] ?>" 
                                           onclick="return confirm('Are you sure you want to delete this slider?')" 
                                           class="text-red-600 hover:text-red-800 p-2 rounded hover:bg-red-50 transition-all" 
                                           title="Delete">
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

