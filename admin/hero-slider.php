<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

use App\Models\HeroSlider;

// Check if table exists, redirect to setup if not
try {
    db()->fetchOne("SELECT 1 FROM hero_slides LIMIT 1");
} catch (Exception $e) {
    header('Location: ' . url('admin/setup-hero-slider.php'));
    exit;
}

$heroSliderModel = new HeroSlider();
$message = '';
$error = '';

// Handle delete
if (!empty($_GET['delete'])) {
    try {
        $slideId = (int)$_GET['delete'];
        if ($slideId <= 0) {
            $error = 'Invalid slide ID.';
        } else {
            $heroSliderModel->delete($slideId);
            $message = 'Slide deleted successfully.';
        }
    } catch (\Exception $e) {
        $error = 'Error deleting slide: ' . $e->getMessage();
    }
}

// Handle toggle active
if (!empty($_GET['toggle'])) {
    try {
        $slideId = (int)$_GET['toggle'];
        if ($slideId > 0) {
            $heroSliderModel->toggleActive($slideId);
            $message = 'Slide status updated.';
        }
    } catch (\Exception $e) {
        $error = 'Error updating slide: ' . $e->getMessage();
    }
}

// Handle order update
if (!empty($_POST['update_order'])) {
    try {
        $orders = $_POST['order'] ?? [];
        foreach ($orders as $id => $order) {
            $heroSliderModel->updateOrder((int)$id, (int)$order);
        }
        $message = 'Display order updated successfully.';
    } catch (\Exception $e) {
        $error = 'Error updating order: ' . $e->getMessage();
    }
}

// Get all slides
$slides = $heroSliderModel->getAll();

$pageTitle = 'Manage Hero Slider';
include __DIR__ . '/includes/header.php';
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Hero Slider</h1>
        <a href="hero-slider-edit.php" class="btn-primary">
            <i class="fas fa-plus mr-2"></i> Add New Slide
        </a>
    </div>
    
    <?php if ($message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= escape($message) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= escape($error) ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($slides)): ?>
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <i class="fas fa-images text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">No slides found</h3>
            <p class="text-gray-500 mb-4">Get started by adding your first hero slide.</p>
            <a href="hero-slider-edit.php" class="btn-primary inline-block">
                <i class="fas fa-plus mr-2"></i> Add First Slide
            </a>
        </div>
    <?php else: ?>
        <form method="POST" action="">
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Buttons</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($slides as $slide): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="number" 
                                       name="order[<?= $slide['id'] ?>]" 
                                       value="<?= escape($slide['display_order']) ?>"
                                       class="w-20 px-2 py-1 border border-gray-300 rounded text-sm"
                                       min="0">
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900"><?= escape($slide['title']) ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-500 max-w-xs truncate">
                                    <?= escape($slide['description'] ?? '') ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if ($slide['button1_text']): ?>
                                    <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs mr-1">
                                        <?= escape($slide['button1_text']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($slide['button2_text']): ?>
                                    <span class="inline-block px-2 py-1 bg-gray-100 text-gray-800 rounded text-xs">
                                        <?= escape($slide['button2_text']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="?toggle=<?= $slide['id'] ?>" 
                                   class="px-2 py-1 text-xs rounded <?= $slide['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                    <?= $slide['is_active'] ? 'Active' : 'Inactive' ?>
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                <a href="hero-slider-edit.php?id=<?= $slide['id'] ?>" 
                                   class="text-blue-600 hover:underline">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="?delete=<?= $slide['id'] ?>" 
                                   onclick="return confirm('Are you sure you want to delete this slide?')" 
                                   class="text-red-600 hover:underline">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 flex justify-end">
                <button type="submit" name="update_order" class="btn-primary">
                    <i class="fas fa-save mr-2"></i> Update Order
                </button>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

