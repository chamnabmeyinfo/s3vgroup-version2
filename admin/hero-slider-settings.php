<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

use App\Models\Setting;

$settingModel = new Setting();
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Global autoplay settings
        $settingModel->set('hero_slider_autoplay_enabled', isset($_POST['autoplay_enabled']) ? 1 : 0, 'boolean');
        $settingModel->set('hero_slider_autoplay_delay', (int)($_POST['autoplay_delay'] ?? 5000), 'number');
        $settingModel->set('hero_slider_pause_on_hover', isset($_POST['pause_on_hover']) ? 1 : 0, 'boolean');
        
        // Navigation settings
        $settingModel->set('hero_slider_show_navigation', isset($_POST['show_navigation']) ? 1 : 0, 'boolean');
        $settingModel->set('hero_slider_show_pagination', isset($_POST['show_pagination']) ? 1 : 0, 'boolean');
        $settingModel->set('hero_slider_navigation_mobile', isset($_POST['navigation_mobile']) ? 1 : 0, 'boolean');
        
        // Animation settings
        $settingModel->set('hero_slider_transition_effect', $_POST['transition_effect'] ?? 'fade', 'text');
        $settingModel->set('hero_slider_transition_speed', (int)($_POST['transition_speed'] ?? 1000), 'number');
        $settingModel->set('hero_slider_loop', isset($_POST['loop']) ? 1 : 0, 'boolean');
        
        // Display settings
        $settingModel->set('hero_slider_height', $_POST['height'] ?? 'auto', 'text');
        $settingModel->set('hero_slider_custom_height', trim($_POST['custom_height'] ?? ''), 'text');
        $settingModel->set('hero_slider_show_counter', isset($_POST['show_counter']) ? 1 : 0, 'boolean');
        $settingModel->set('hero_slider_show_progress', isset($_POST['show_progress']) ? 1 : 0, 'boolean');
        
        // Keyboard & Touch settings
        $settingModel->set('hero_slider_keyboard_enabled', isset($_POST['keyboard_enabled']) ? 1 : 0, 'boolean');
        $settingModel->set('hero_slider_mousewheel_enabled', isset($_POST['mousewheel_enabled']) ? 1 : 0, 'boolean');
        
        // Advanced settings
        $settingModel->set('hero_slider_lazy_loading', isset($_POST['lazy_loading']) ? 1 : 0, 'boolean');
        $settingModel->set('hero_slider_preload_images', isset($_POST['preload_images']) ? 1 : 0, 'boolean');
        
        $message = 'Global hero slider settings saved successfully!';
    } catch (\Exception $e) {
        $error = 'Error saving settings: ' . $e->getMessage();
    }
}

// Get current settings
$settings = [
    'autoplay_enabled' => $settingModel->get('hero_slider_autoplay_enabled', 1),
    'autoplay_delay' => $settingModel->get('hero_slider_autoplay_delay', 5000),
    'pause_on_hover' => $settingModel->get('hero_slider_pause_on_hover', 1),
    'show_navigation' => $settingModel->get('hero_slider_show_navigation', 1),
    'show_pagination' => $settingModel->get('hero_slider_show_pagination', 1),
    'navigation_mobile' => $settingModel->get('hero_slider_navigation_mobile', 0),
    'transition_effect' => $settingModel->get('hero_slider_transition_effect', 'fade'),
    'transition_speed' => $settingModel->get('hero_slider_transition_speed', 1000),
    'loop' => $settingModel->get('hero_slider_loop', 1),
    'height' => $settingModel->get('hero_slider_height', 'auto'),
    'custom_height' => $settingModel->get('hero_slider_custom_height', ''),
    'show_counter' => $settingModel->get('hero_slider_show_counter', 1),
    'show_progress' => $settingModel->get('hero_slider_show_progress', 1),
    'keyboard_enabled' => $settingModel->get('hero_slider_keyboard_enabled', 1),
    'mousewheel_enabled' => $settingModel->get('hero_slider_mousewheel_enabled', 0),
    'lazy_loading' => $settingModel->get('hero_slider_lazy_loading', 1),
    'preload_images' => $settingModel->get('hero_slider_preload_images', 0),
];

$pageTitle = 'Hero Slider Global Settings';
include __DIR__ . '/includes/header.php';
?>

<div class="max-w-5xl mx-auto p-4 md:p-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl shadow-xl p-4 md:p-6 lg:p-8 mb-4 md:mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-1 md:mb-2">
                    <i class="fas fa-cog mr-2 md:mr-3"></i>
                    Hero Slider Global Settings
                </h1>
                <p class="text-blue-100 text-sm md:text-lg">Configure global settings that apply to all hero slider slides</p>
                <div class="mt-3 bg-blue-500/20 backdrop-blur-sm rounded-lg p-3 text-sm">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Note:</strong> These settings apply globally to all slides. Individual slide settings (like autoplay delay) can override global settings when specified.
                </div>
            </div>
            <a href="<?= url('admin/hero-sliders.php') ?>" class="bg-white text-blue-600 px-4 md:px-6 py-2 md:py-3 rounded-lg font-bold hover:bg-blue-50 transition-all">
                <i class="fas fa-arrow-left mr-2"></i>Back to Sliders
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

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <form method="POST" class="p-4 md:p-6 lg:p-8">
            <!-- Autoplay Settings -->
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-play-circle text-blue-600 mr-2"></i>
                    Autoplay Settings
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                    <div class="md:col-span-2">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                                   name="autoplay_enabled" 
                                   <?= $settings['autoplay_enabled'] ? 'checked' : '' ?>>
                            <span class="ml-3 text-sm font-medium text-gray-700">Enable Autoplay</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 ml-8">Automatically advance to next slide</p>
                    </div>
                    
                    <div>
                        <label for="autoplay_delay" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-clock text-blue-600 mr-1"></i>Autoplay Delay (ms)
                        </label>
                        <input type="number" 
                               id="autoplay_delay" 
                               name="autoplay_delay" 
                               value="<?= escape($settings['autoplay_delay']) ?>" 
                               min="1000"
                               max="30000"
                               step="500"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <p class="text-xs text-gray-500 mt-1">Time in milliseconds before next slide (1000-30000)</p>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                                   name="pause_on_hover" 
                                   <?= $settings['pause_on_hover'] ? 'checked' : '' ?>>
                            <span class="ml-3 text-sm font-medium text-gray-700">Pause on Hover</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 ml-8">Pause autoplay when mouse hovers over slider</p>
                    </div>
                </div>
            </div>

            <!-- Navigation Settings -->
            <div class="mb-8 border-t border-gray-200 pt-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-compass text-blue-600 mr-2"></i>
                    Navigation Settings
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                    <div>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                                   name="show_navigation" 
                                   <?= $settings['show_navigation'] ? 'checked' : '' ?>>
                            <span class="ml-3 text-sm font-medium text-gray-700">Show Navigation Arrows</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 ml-8">Display prev/next arrow buttons</p>
                    </div>
                    
                    <div>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                                   name="show_pagination" 
                                   <?= $settings['show_pagination'] ? 'checked' : '' ?>>
                            <span class="ml-3 text-sm font-medium text-gray-700">Show Pagination Dots</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 ml-8">Display pagination bullets at bottom</p>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                                   name="navigation_mobile" 
                                   <?= $settings['navigation_mobile'] ? 'checked' : '' ?>>
                            <span class="ml-3 text-sm font-medium text-gray-700">Show Navigation on Mobile</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 ml-8">Display navigation arrows on mobile devices (usually hidden by default)</p>
                    </div>
                </div>
            </div>

            <!-- Animation & Transition Settings -->
            <div class="mb-8 border-t border-gray-200 pt-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-magic text-blue-600 mr-2"></i>
                    Animation & Transition Settings
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                    <div>
                        <label for="transition_effect" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-film text-blue-600 mr-1"></i>Transition Effect
                        </label>
                        <select id="transition_effect" 
                                name="transition_effect" 
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="fade" <?= $settings['transition_effect'] === 'fade' ? 'selected' : '' ?>>Fade</option>
                            <option value="slide" <?= $settings['transition_effect'] === 'slide' ? 'selected' : '' ?>>Slide</option>
                            <option value="cube" <?= $settings['transition_effect'] === 'cube' ? 'selected' : '' ?>>Cube</option>
                            <option value="coverflow" <?= $settings['transition_effect'] === 'coverflow' ? 'selected' : '' ?>>Coverflow</option>
                            <option value="flip" <?= $settings['transition_effect'] === 'flip' ? 'selected' : '' ?>>Flip</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">How slides transition between each other</p>
                    </div>
                    
                    <div>
                        <label for="transition_speed" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-tachometer-alt text-blue-600 mr-1"></i>Transition Speed (ms)
                        </label>
                        <input type="number" 
                               id="transition_speed" 
                               name="transition_speed" 
                               value="<?= escape($settings['transition_speed']) ?>" 
                               min="100"
                               max="5000"
                               step="100"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <p class="text-xs text-gray-500 mt-1">Speed of transition animation (100-5000ms)</p>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                                   name="loop" 
                                   <?= $settings['loop'] ? 'checked' : '' ?>>
                            <span class="ml-3 text-sm font-medium text-gray-700">Enable Loop</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 ml-8">Loop back to first slide after last slide</p>
                    </div>
                </div>
            </div>

            <!-- Display Settings -->
            <div class="mb-8 border-t border-gray-200 pt-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-desktop text-blue-600 mr-2"></i>
                    Display Settings
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                    <div>
                        <label for="height" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-arrows-alt-v text-blue-600 mr-1"></i>Slider Height
                        </label>
                        <select id="height" 
                                name="height" 
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="auto" <?= $settings['height'] === 'auto' ? 'selected' : '' ?>>Auto (Responsive)</option>
                            <option value="full" <?= $settings['height'] === 'full' ? 'selected' : '' ?>>Full Screen (100vh)</option>
                            <option value="custom" <?= $settings['height'] === 'custom' ? 'selected' : '' ?>>Custom</option>
                        </select>
                    </div>
                    
                    <div id="custom_height_container" style="display: <?= $settings['height'] === 'custom' ? 'block' : 'none' ?>;">
                        <label for="custom_height" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-ruler-vertical text-blue-600 mr-1"></i>Custom Height
                        </label>
                        <input type="text" 
                               id="custom_height" 
                               name="custom_height" 
                               value="<?= escape($settings['custom_height']) ?>" 
                               placeholder="600px or 80vh"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <p class="text-xs text-gray-500 mt-1">Examples: 600px, 80vh, 50rem</p>
                    </div>
                    
                    <div>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                                   name="show_counter" 
                                   <?= $settings['show_counter'] ? 'checked' : '' ?>>
                            <span class="ml-3 text-sm font-medium text-gray-700">Show Slide Counter</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 ml-8">Display "1/3" counter in bottom-right corner</p>
                    </div>
                    
                    <div>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                                   name="show_progress" 
                                   <?= $settings['show_progress'] ? 'checked' : '' ?>>
                            <span class="ml-3 text-sm font-medium text-gray-700">Show Progress Bar</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 ml-8">Display progress bar on pagination bullets</p>
                    </div>
                </div>
            </div>

            <!-- Keyboard & Touch Settings -->
            <div class="mb-8 border-t border-gray-200 pt-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-keyboard text-blue-600 mr-2"></i>
                    Keyboard & Touch Settings
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                    <div>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                                   name="keyboard_enabled" 
                                   <?= $settings['keyboard_enabled'] ? 'checked' : '' ?>>
                            <span class="ml-3 text-sm font-medium text-gray-700">Enable Keyboard Navigation</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 ml-8">Use arrow keys to navigate slides</p>
                    </div>
                    
                    <div>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                                   name="mousewheel_enabled" 
                                   <?= $settings['mousewheel_enabled'] ? 'checked' : '' ?>>
                            <span class="ml-3 text-sm font-medium text-gray-700">Enable Mousewheel</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 ml-8">Use mouse wheel to navigate slides</p>
                    </div>
                </div>
            </div>

            <!-- Advanced Settings -->
            <div class="mb-8 border-t border-gray-200 pt-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-cogs text-blue-600 mr-2"></i>
                    Advanced Settings
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                    <div>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                                   name="lazy_loading" 
                                   <?= $settings['lazy_loading'] ? 'checked' : '' ?>>
                            <span class="ml-3 text-sm font-medium text-gray-700">Enable Lazy Loading</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 ml-8">Load images only when needed (improves performance)</p>
                    </div>
                    
                    <div>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                                   name="preload_images" 
                                   <?= $settings['preload_images'] ? 'checked' : '' ?>>
                            <span class="ml-3 text-sm font-medium text-gray-700">Preload Images</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 ml-8">Preload all slide images (may slow initial load)</p>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex gap-3 border-t border-gray-200 pt-6">
                <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-3 rounded-lg font-bold hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl">
                    <i class="fas fa-save mr-2"></i>Save Global Settings
                </button>
                <a href="<?= url('admin/hero-sliders.php') ?>" class="px-6 py-3 bg-gray-600 text-white rounded-lg font-bold hover:bg-gray-700 transition-all">
                    <i class="fas fa-times mr-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Show/hide custom height field
document.getElementById('height').addEventListener('change', function() {
    const container = document.getElementById('custom_height_container');
    container.style.display = this.value === 'custom' ? 'block' : 'none';
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
