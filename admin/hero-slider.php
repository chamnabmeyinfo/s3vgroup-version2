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

// Handle general settings update
if (!empty($_POST['update_settings'])) {
    try {
        $settingsToUpdate = [
            'hero_slider_autoplay_delay' => (int)($_POST['autoplay_delay'] ?? 5000),
            'hero_slider_default_transparency' => (float)($_POST['default_transparency'] ?? 0.10),
            'hero_slider_show_arrows' => isset($_POST['show_arrows']) ? 1 : 0,
            'hero_slider_show_dots' => isset($_POST['show_dots']) ? 1 : 0,
            'hero_slider_show_progress' => isset($_POST['show_progress']) ? 1 : 0,
            'hero_slider_pause_on_hover' => isset($_POST['pause_on_hover']) ? 1 : 0,
            'hero_slider_transition_speed' => (int)($_POST['transition_speed'] ?? 800),
            'hero_slider_enable_keyboard' => isset($_POST['enable_keyboard']) ? 1 : 0,
            'hero_slider_enable_touch' => isset($_POST['enable_touch']) ? 1 : 0,
        ];
        
        foreach ($settingsToUpdate as $key => $value) {
            $existing = db()->fetchOne("SELECT id FROM settings WHERE `key` = :key", ['key' => $key]);
            if ($existing) {
                db()->update('settings', ['value' => $value], '`key` = :key', ['key' => $key]);
            } else {
                db()->insert('settings', [
                    'key' => $key,
                    'value' => $value,
                    'type' => 'text'
                ]);
            }
        }
        
        $message = 'General settings updated successfully.';
    } catch (\Exception $e) {
        $error = 'Error updating settings: ' . $e->getMessage();
    }
}

// Get current settings
$settingsData = db()->fetchAll("SELECT `key`, value FROM settings WHERE `key` LIKE 'hero_slider_%'");
$sliderSettings = [];
foreach ($settingsData as $setting) {
    $sliderSettings[$setting['key']] = $setting['value'];
}

// Default values
$defaultSettings = [
    'hero_slider_autoplay_delay' => 5000,
    'hero_slider_default_transparency' => 0.10,
    'hero_slider_show_arrows' => 1,
    'hero_slider_show_dots' => 1,
    'hero_slider_show_progress' => 1,
    'hero_slider_pause_on_hover' => 1,
    'hero_slider_transition_speed' => 800,
    'hero_slider_enable_keyboard' => 1,
    'hero_slider_enable_touch' => 1,
];

foreach ($defaultSettings as $key => $default) {
    if (!isset($sliderSettings[$key])) {
        $sliderSettings[$key] = $default;
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
    
    <!-- General Settings Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold flex items-center">
                <i class="fas fa-cog mr-2 text-blue-600"></i> General Settings
            </h2>
            <button type="button" onclick="toggleSettings()" class="text-blue-600 hover:text-blue-800 text-sm">
                <i class="fas fa-chevron-down" id="settings-toggle-icon"></i>
            </button>
        </div>
        
        <form method="POST" id="settings-form" class="hidden">
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Auto-play Settings -->
                <div class="space-y-4">
                    <h3 class="font-semibold text-gray-700 border-b pb-2">Auto-play Settings</h3>
                    
                    <div>
                        <label for="autoplay_delay" class="block text-sm font-medium text-gray-700 mb-2">
                            Auto-play Delay (milliseconds)
                        </label>
                        <input type="number" 
                               id="autoplay_delay" 
                               name="autoplay_delay" 
                               value="<?= escape($sliderSettings['hero_slider_autoplay_delay']) ?>"
                               min="1000" 
                               max="30000" 
                               step="500"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Time between slides (1000-30000ms)</p>
                    </div>
                    
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="pause_on_hover" 
                                   value="1"
                                   <?= $sliderSettings['hero_slider_pause_on_hover'] ? 'checked' : '' ?>
                                   class="mr-2">
                            <span class="text-sm font-medium text-gray-700">Pause on Hover</span>
                        </label>
                    </div>
                </div>
                
                <!-- Display Settings -->
                <div class="space-y-4">
                    <h3 class="font-semibold text-gray-700 border-b pb-2">Display Settings</h3>
                    
                    <div>
                        <label for="default_transparency" class="block text-sm font-medium text-gray-700 mb-2">
                            Default Transparency
                        </label>
                        <div class="space-y-2">
                            <input type="range" 
                                   id="default_transparency" 
                                   name="default_transparency" 
                                   min="0" 
                                   max="1" 
                                   step="0.01"
                                   value="<?= escape($sliderSettings['hero_slider_default_transparency']) ?>"
                                   oninput="updateTransparencyDisplay(this.value)"
                                   class="w-full">
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-500">0%</span>
                                <span id="transparency_display" class="text-sm font-semibold text-blue-600">
                                    <?= number_format((float)$sliderSettings['hero_slider_default_transparency'] * 100, 0) ?>%
                                </span>
                                <span class="text-xs text-gray-500">100%</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Default glass effect transparency for new slides</p>
                    </div>
                    
                    <div>
                        <label for="transition_speed" class="block text-sm font-medium text-gray-700 mb-2">
                            Transition Speed (milliseconds)
                        </label>
                        <input type="number" 
                               id="transition_speed" 
                               name="transition_speed" 
                               value="<?= escape($sliderSettings['hero_slider_transition_speed']) ?>"
                               min="200" 
                               max="2000" 
                               step="100"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Animation speed between slides</p>
                    </div>
                </div>
                
                <!-- Navigation Settings -->
                <div class="space-y-4">
                    <h3 class="font-semibold text-gray-700 border-b pb-2">Navigation Settings</h3>
                    
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="show_arrows" 
                                   value="1"
                                   <?= $sliderSettings['hero_slider_show_arrows'] ? 'checked' : '' ?>
                                   class="mr-2">
                            <span class="text-sm font-medium text-gray-700">Show Navigation Arrows</span>
                        </label>
                    </div>
                    
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="show_dots" 
                                   value="1"
                                   <?= $sliderSettings['hero_slider_show_dots'] ? 'checked' : '' ?>
                                   class="mr-2">
                            <span class="text-sm font-medium text-gray-700">Show Dots Navigation</span>
                        </label>
                    </div>
                    
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="show_progress" 
                                   value="1"
                                   <?= $sliderSettings['hero_slider_show_progress'] ? 'checked' : '' ?>
                                   class="mr-2">
                            <span class="text-sm font-medium text-gray-700">Show Progress Bar</span>
                        </label>
                    </div>
                </div>
                
                <!-- Interaction Settings -->
                <div class="space-y-4">
                    <h3 class="font-semibold text-gray-700 border-b pb-2">Interaction Settings</h3>
                    
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="enable_keyboard" 
                                   value="1"
                                   <?= $sliderSettings['hero_slider_enable_keyboard'] ? 'checked' : '' ?>
                                   class="mr-2">
                            <span class="text-sm font-medium text-gray-700">Enable Keyboard Navigation</span>
                        </label>
                        <p class="text-xs text-gray-500 ml-6">Arrow keys to navigate</p>
                    </div>
                    
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="enable_touch" 
                                   value="1"
                                   <?= $sliderSettings['hero_slider_enable_touch'] ? 'checked' : '' ?>
                                   class="mr-2">
                            <span class="text-sm font-medium text-gray-700">Enable Touch/Swipe</span>
                        </label>
                        <p class="text-xs text-gray-500 ml-6">Swipe on mobile devices</p>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end">
                <button type="submit" name="update_settings" class="btn-primary">
                    <i class="fas fa-save mr-2"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
    
    <script>
    function toggleSettings() {
        const form = document.getElementById('settings-form');
        const icon = document.getElementById('settings-toggle-icon');
        form.classList.toggle('hidden');
        icon.classList.toggle('fa-chevron-down');
        icon.classList.toggle('fa-chevron-up');
    }
    
    function updateTransparencyDisplay(value) {
        document.getElementById('transparency_display').textContent = Math.round(value * 100) + '%';
    }
    </script>
    
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

