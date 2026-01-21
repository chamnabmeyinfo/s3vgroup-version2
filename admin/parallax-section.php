<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

use App\Models\Setting;

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    
    $settingsToUpdate = [
        'parallax_enabled' => isset($_POST['parallax_enabled']) ? 1 : 0,
        'parallax_title' => trim($_POST['parallax_title'] ?? ''),
        'parallax_subtitle' => trim($_POST['parallax_subtitle'] ?? ''),
        'parallax_description' => trim($_POST['parallax_description'] ?? ''),
        'parallax_button_text' => trim($_POST['parallax_button_text'] ?? ''),
        'parallax_button_url' => trim($_POST['parallax_button_url'] ?? ''),
        'parallax_button_2_text' => trim($_POST['parallax_button_2_text'] ?? ''),
        'parallax_button_2_url' => trim($_POST['parallax_button_2_url'] ?? ''),
        'parallax_bg_image' => trim($_POST['parallax_bg_image'] ?? ''),
        'parallax_overlay_color' => trim($_POST['parallax_overlay_color'] ?? '#000000'),
        'parallax_overlay_opacity' => (float)($_POST['parallax_overlay_opacity'] ?? 0.4),
        'parallax_title_color' => trim($_POST['parallax_title_color'] ?? '#ffffff'),
        'parallax_subtitle_color' => trim($_POST['parallax_subtitle_color'] ?? '#ffffff'),
        'parallax_text_color' => trim($_POST['parallax_text_color'] ?? '#ffffff'),
        'parallax_button_bg_color' => trim($_POST['parallax_button_bg_color'] ?? '#3b82f6'),
        'parallax_button_text_color' => trim($_POST['parallax_button_text_color'] ?? '#ffffff'),
        'parallax_button_2_bg_color' => trim($_POST['parallax_button_2_bg_color'] ?? 'transparent'),
        'parallax_button_2_text_color' => trim($_POST['parallax_button_2_text_color'] ?? '#ffffff'),
        'parallax_speed' => (float)($_POST['parallax_speed'] ?? 0.5),
        'parallax_height' => (int)($_POST['parallax_height'] ?? 600),
        'parallax_position' => trim($_POST['parallax_position'] ?? 'after-hero'),
        'parallax_animation_type' => trim($_POST['parallax_animation_type'] ?? 'fade-in'),
        'parallax_particles_enabled' => isset($_POST['parallax_particles_enabled']) ? 1 : 0,
        'parallax_particles_color' => trim($_POST['parallax_particles_color'] ?? '#ffffff'),
    ];
    
    // Handle background image upload
    if (isset($_FILES['parallax_bg_image_upload']) && $_FILES['parallax_bg_image_upload']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../storage/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $file = $_FILES['parallax_bg_image_upload'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = 'parallax_bg_' . time() . '_' . uniqid() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $settingsToUpdate['parallax_bg_image'] = 'storage/uploads/' . $filename;
            } else {
                $error = 'Failed to upload image.';
            }
        } else {
            $error = 'Invalid file type or file too large (max 5MB).';
        }
    }
    
    try {
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
        
        if (empty($error)) {
            $message = 'Parallax section updated successfully.';
        }
    } catch (Exception $e) {
        $error = 'Error updating parallax section: ' . $e->getMessage();
    }
}

// Get current settings
$settingsData = db()->fetchAll("SELECT `key`, value FROM settings WHERE `key` LIKE 'parallax_%'");
$settings = [];
foreach ($settingsData as $setting) {
    $settings[$setting['key']] = $setting['value'];
}

// Default values
$defaults = [
    'parallax_enabled' => 1,
    'parallax_title' => 'Experience Excellence',
    'parallax_subtitle' => 'Premium Industrial Solutions',
    'parallax_description' => 'Discover our comprehensive range of forklifts and industrial equipment designed to elevate your business operations.',
    'parallax_button_text' => 'Explore Products',
    'parallax_button_url' => 'products.php',
    'parallax_button_2_text' => 'Contact Us',
    'parallax_button_2_url' => 'contact.php',
    'parallax_bg_image' => '',
    'parallax_overlay_color' => '#000000',
    'parallax_overlay_opacity' => 0.4,
    'parallax_title_color' => '#ffffff',
    'parallax_subtitle_color' => '#ffffff',
    'parallax_text_color' => '#ffffff',
    'parallax_button_bg_color' => '#3b82f6',
    'parallax_button_text_color' => '#ffffff',
    'parallax_button_2_bg_color' => 'transparent',
    'parallax_button_2_text_color' => '#ffffff',
    'parallax_speed' => 0.5,
    'parallax_height' => 600,
    'parallax_position' => 'after-hero',
    'parallax_animation_type' => 'fade-in',
    'parallax_particles_enabled' => 1,
    'parallax_particles_color' => '#ffffff',
];

foreach ($defaults as $key => $default) {
    if (!isset($settings[$key])) {
        $settings[$key] = $default;
    }
}

$pageTitle = 'Parallax Section';
include __DIR__ . '/includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-layer-group text-blue-600 mr-3"></i>
                Parallax Section
            </h1>
            <p class="text-gray-600 mt-2">Create a stunning parallax effect section for your homepage.</p>
        </div>
        <a href="<?= url('index.php') ?>" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
            <i class="fas fa-external-link-alt mr-2"></i>View Homepage
        </a>
    </div>

    <?php if ($message): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        <i class="fas fa-check-circle mr-2"></i><?= escape($message) ?>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <i class="fas fa-exclamation-circle mr-2"></i><?= escape($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="parallaxForm" class="bg-white rounded-xl shadow-lg p-4 md:p-6 lg:p-8">
        <?= csrf_field() ?>
        
        <!-- Enable/Disable -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Enable Parallax Section</label>
                    <p class="text-xs text-gray-500">Show or hide the parallax section on the homepage</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="parallax_enabled" value="1" <?= ($settings['parallax_enabled'] ?? 1) ? 'checked' : '' ?> class="sr-only peer" onchange="updatePreview()">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
        </div>

        <!-- Live Preview -->
        <div class="mb-6 bg-white rounded-xl shadow-lg p-4 md:p-6 lg:p-8 border-2 border-blue-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-eye text-blue-600 mr-2"></i>
                Live Preview
            </h3>
            <div id="parallaxPreview" class="relative overflow-hidden rounded-lg" style="
                height: 300px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                background-size: cover;
                background-position: center;
            ">
                <div class="absolute inset-0 flex items-center justify-center text-center px-4" style="
                    background: rgba(0, 0, 0, <?= escape($settings['parallax_overlay_opacity'] ?? 0.4) ?>);
                ">
                    <div>
                        <p class="text-sm mb-2" style="color: <?= escape($settings['parallax_subtitle_color'] ?? '#ffffff') ?>;">
                            <?= escape($settings['parallax_subtitle'] ?? 'Premium Industrial Solutions') ?>
                        </p>
                        <h2 class="text-2xl font-bold mb-3" style="color: <?= escape($settings['parallax_title_color'] ?? '#ffffff') ?>;">
                            <?= escape($settings['parallax_title'] ?? 'Experience Excellence') ?>
                        </h2>
                        <p class="text-sm mb-4 max-w-md mx-auto" style="color: <?= escape($settings['parallax_text_color'] ?? '#ffffff') ?>;">
                            <?= escape(substr($settings['parallax_description'] ?? '', 0, 80)) ?>...
                        </p>
                        <div class="flex gap-3 justify-center">
                            <?php if (!empty($settings['parallax_button_text'])): ?>
                            <button class="px-4 py-2 rounded-lg text-sm font-semibold" style="
                                background: <?= escape($settings['parallax_button_bg_color'] ?? '#3b82f6') ?>;
                                color: <?= escape($settings['parallax_button_text_color'] ?? '#ffffff') ?>;
                            ">
                                <?= escape($settings['parallax_button_text']) ?>
                            </button>
                            <?php endif; ?>
                            <?php if (!empty($settings['parallax_button_2_text'])): ?>
                            <button class="px-4 py-2 rounded-lg text-sm font-semibold border-2" style="
                                background: <?= escape($settings['parallax_button_2_bg_color'] ?? 'transparent') ?>;
                                color: <?= escape($settings['parallax_button_2_text_color'] ?? '#ffffff') ?>;
                                border-color: <?= escape($settings['parallax_button_2_text_color'] ?? '#ffffff') ?>;
                            ">
                                <?= escape($settings['parallax_button_2_text']) ?>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Settings -->
        <div class="mb-6">
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <button type="button" class="w-full px-6 py-4 bg-gray-50 hover:bg-gray-100 flex items-center justify-between transition-colors" onclick="toggleAccordion('contentSettings')">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-edit text-blue-600 mr-2"></i>
                        Content Settings
                    </h3>
                    <i class="fas fa-chevron-down transform transition-transform" id="contentSettingsIcon"></i>
                </button>
                <div id="contentSettings" class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Subtitle</label>
                            <input type="text" name="parallax_subtitle" value="<?= escape($settings['parallax_subtitle'] ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" oninput="updatePreview()" placeholder="Premium Industrial Solutions">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                            <input type="text" name="parallax_title" value="<?= escape($settings['parallax_title'] ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" oninput="updatePreview()" placeholder="Experience Excellence">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea name="parallax_description" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" oninput="updatePreview()" placeholder="Discover our comprehensive range of forklifts..."><?= escape($settings['parallax_description'] ?? '') ?></textarea>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Button 1 Text</label>
                                <input type="text" name="parallax_button_text" value="<?= escape($settings['parallax_button_text'] ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" oninput="updatePreview()" placeholder="Explore Products">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Button 1 URL</label>
                                <input type="text" name="parallax_button_url" value="<?= escape($settings['parallax_button_url'] ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="products.php">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Button 2 Text</label>
                                <input type="text" name="parallax_button_2_text" value="<?= escape($settings['parallax_button_2_text'] ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" oninput="updatePreview()" placeholder="Contact Us">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Button 2 URL</label>
                                <input type="text" name="parallax_button_2_url" value="<?= escape($settings['parallax_button_2_url'] ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="contact.php">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Background Settings -->
        <div class="mb-6">
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <button type="button" class="w-full px-6 py-4 bg-gray-50 hover:bg-gray-100 flex items-center justify-between transition-colors" onclick="toggleAccordion('backgroundSettings')">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-image text-blue-600 mr-2"></i>
                        Background Settings
                    </h3>
                    <i class="fas fa-chevron-down transform transition-transform" id="backgroundSettingsIcon"></i>
                </button>
                <div id="backgroundSettings" class="hidden p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Background Image URL</label>
                            <input type="text" name="parallax_bg_image" value="<?= escape($settings['parallax_bg_image'] ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="updatePreview()" placeholder="https://example.com/image.jpg or storage/uploads/image.jpg">
                            <p class="text-xs text-gray-500 mt-1">Enter image URL or upload below</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Upload Background Image</label>
                            <input type="file" name="parallax_bg_image_upload" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="text-xs text-gray-500 mt-1">Max 5MB. JPG, PNG, or WebP</p>
                        </div>
                        <?php if (!empty($settings['parallax_bg_image'])): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Background Image</label>
                            <img src="<?= escape($settings['parallax_bg_image']) ?>" alt="Background" class="max-w-full h-32 object-cover rounded-lg border border-gray-300">
                        </div>
                        <?php endif; ?>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Overlay Color</label>
                                <input type="color" name="parallax_overlay_color" value="<?= escape($settings['parallax_overlay_color'] ?? '#000000') ?>" class="w-full h-10 border border-gray-300 rounded cursor-pointer" onchange="updatePreview()">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Overlay Opacity (0-1)</label>
                                <input type="number" name="parallax_overlay_opacity" value="<?= escape($settings['parallax_overlay_opacity'] ?? 0.4) ?>" min="0" max="1" step="0.1" class="w-full px-4 py-2 border border-gray-300 rounded-lg" onchange="updatePreview()">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Typography & Colors -->
        <div class="mb-6">
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <button type="button" class="w-full px-6 py-4 bg-gray-50 hover:bg-gray-100 flex items-center justify-between transition-colors" onclick="toggleAccordion('typographySettings')">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-palette text-blue-600 mr-2"></i>
                        Typography & Colors
                    </h3>
                    <i class="fas fa-chevron-down transform transition-transform" id="typographySettingsIcon"></i>
                </button>
                <div id="typographySettings" class="hidden p-6">
                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Title Color</label>
                            <input type="color" name="parallax_title_color" value="<?= escape($settings['parallax_title_color'] ?? '#ffffff') ?>" class="w-full h-10 border border-gray-300 rounded cursor-pointer" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Subtitle Color</label>
                            <input type="color" name="parallax_subtitle_color" value="<?= escape($settings['parallax_subtitle_color'] ?? '#ffffff') ?>" class="w-full h-10 border border-gray-300 rounded cursor-pointer" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Text Color</label>
                            <input type="color" name="parallax_text_color" value="<?= escape($settings['parallax_text_color'] ?? '#ffffff') ?>" class="w-full h-10 border border-gray-300 rounded cursor-pointer" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Button 1 BG Color</label>
                            <input type="color" name="parallax_button_bg_color" value="<?= escape($settings['parallax_button_bg_color'] ?? '#3b82f6') ?>" class="w-full h-10 border border-gray-300 rounded cursor-pointer" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Button 1 Text Color</label>
                            <input type="color" name="parallax_button_text_color" value="<?= escape($settings['parallax_button_text_color'] ?? '#ffffff') ?>" class="w-full h-10 border border-gray-300 rounded cursor-pointer" onchange="updatePreview()">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Button 2 BG Color</label>
                            <input type="text" name="parallax_button_2_bg_color" value="<?= escape($settings['parallax_button_2_bg_color'] ?? 'transparent') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg" onchange="updatePreview()" placeholder="transparent or #hex">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Button 2 Text Color</label>
                            <input type="color" name="parallax_button_2_text_color" value="<?= escape($settings['parallax_button_2_text_color'] ?? '#ffffff') ?>" class="w-full h-10 border border-gray-300 rounded cursor-pointer" onchange="updatePreview()">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Animation & Effects -->
        <div class="mb-6">
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <button type="button" class="w-full px-6 py-4 bg-gray-50 hover:bg-gray-100 flex items-center justify-between transition-colors" onclick="toggleAccordion('animationSettings')">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-magic text-blue-600 mr-2"></i>
                        Animation & Effects
                    </h3>
                    <i class="fas fa-chevron-down transform transition-transform" id="animationSettingsIcon"></i>
                </button>
                <div id="animationSettings" class="hidden p-6">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Parallax Speed (0-1)</label>
                            <input type="number" name="parallax_speed" value="<?= escape($settings['parallax_speed'] ?? 0.5) ?>" min="0" max="1" step="0.1" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <p class="text-xs text-gray-500 mt-1">Lower = slower parallax effect</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Section Height (px)</label>
                            <input type="number" name="parallax_height" value="<?= escape($settings['parallax_height'] ?? 600) ?>" min="300" max="1000" step="50" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Position on Page</label>
                            <select name="parallax_position" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="after-hero" <?= ($settings['parallax_position'] ?? 'after-hero') === 'after-hero' ? 'selected' : '' ?>>After Hero Slider</option>
                                <option value="after-features" <?= ($settings['parallax_position'] ?? '') === 'after-features' ? 'selected' : '' ?>>After Features Section</option>
                                <option value="after-mission" <?= ($settings['parallax_position'] ?? '') === 'after-mission' ? 'selected' : '' ?>>After Mission & Vision</option>
                                <option value="before-footer" <?= ($settings['parallax_position'] ?? '') === 'before-footer' ? 'selected' : '' ?>>Before Footer</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Animation Type</label>
                            <select name="parallax_animation_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="fade-in" <?= ($settings['parallax_animation_type'] ?? 'fade-in') === 'fade-in' ? 'selected' : '' ?>>Fade In</option>
                                <option value="slide-up" <?= ($settings['parallax_animation_type'] ?? '') === 'slide-up' ? 'selected' : '' ?>>Slide Up</option>
                                <option value="zoom-in" <?= ($settings['parallax_animation_type'] ?? '') === 'zoom-in' ? 'selected' : '' ?>>Zoom In</option>
                                <option value="none" <?= ($settings['parallax_animation_type'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Enable Particles Effect</label>
                                    <p class="text-xs text-gray-500">Add animated particles in the background</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="parallax_particles_enabled" value="1" <?= ($settings['parallax_particles_enabled'] ?? 1) ? 'checked' : '' ?> class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Particles Color</label>
                            <input type="color" name="parallax_particles_color" value="<?= escape($settings['parallax_particles_color'] ?? '#ffffff') ?>" class="w-full h-10 border border-gray-300 rounded cursor-pointer">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <a href="<?= url('index.php') ?>" target="_blank" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                <i class="fas fa-home mr-2"></i>View Homepage
            </a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                <i class="fas fa-save mr-2"></i>Save Parallax Section
            </button>
        </div>
    </form>
</div>

<script>
function toggleAccordion(id) {
    const element = document.getElementById(id);
    const icon = document.getElementById(id + 'Icon');
    
    if (element.classList.contains('hidden')) {
        element.classList.remove('hidden');
        icon.classList.add('rotate-180');
    } else {
        element.classList.add('hidden');
        icon.classList.remove('rotate-180');
    }
}

function updatePreview() {
    const form = document.getElementById('parallaxForm');
    const preview = document.getElementById('parallaxPreview');
    
    const subtitle = form.querySelector('[name="parallax_subtitle"]').value;
    const title = form.querySelector('[name="parallax_title"]').value;
    const description = form.querySelector('[name="parallax_description"]').value;
    const bgImage = form.querySelector('[name="parallax_bg_image"]').value;
    const overlayColor = form.querySelector('[name="parallax_overlay_color"]').value;
    const overlayOpacity = form.querySelector('[name="parallax_overlay_opacity"]').value;
    const titleColor = form.querySelector('[name="parallax_title_color"]').value;
    const subtitleColor = form.querySelector('[name="parallax_subtitle_color"]').value;
    const textColor = form.querySelector('[name="parallax_text_color"]').value;
    const buttonText = form.querySelector('[name="parallax_button_text"]').value;
    const button2Text = form.querySelector('[name="parallax_button_2_text"]').value;
    const buttonBg = form.querySelector('[name="parallax_button_bg_color"]').value;
    const buttonTextColor = form.querySelector('[name="parallax_button_text_color"]').value;
    const button2Bg = form.querySelector('[name="parallax_button_2_bg_color"]').value;
    const button2TextColor = form.querySelector('[name="parallax_button_2_text_color"]').value;
    
    // Update background
    if (bgImage) {
        preview.style.backgroundImage = `url(${bgImage})`;
        preview.style.backgroundSize = 'cover';
        preview.style.backgroundPosition = 'center';
    } else {
        preview.style.backgroundImage = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
    }
    
    // Update overlay
    const overlay = preview.querySelector('.absolute.inset-0');
    if (overlay) {
        overlay.style.background = `rgba(${hexToRgb(overlayColor)}, ${overlayOpacity})`;
    }
    
    // Update content
    const content = preview.querySelector('.absolute.inset-0 > div');
    if (content) {
        content.innerHTML = `
            <div>
                <p class="text-sm mb-2" style="color: ${subtitleColor};">${subtitle || 'Subtitle'}</p>
                <h2 class="text-2xl font-bold mb-3" style="color: ${titleColor};">${title || 'Title'}</h2>
                <p class="text-sm mb-4 max-w-md mx-auto" style="color: ${textColor};">${description ? description.substring(0, 80) + '...' : 'Description...'}</p>
                <div class="flex gap-3 justify-center">
                    ${buttonText ? `<button class="px-4 py-2 rounded-lg text-sm font-semibold" style="background: ${buttonBg}; color: ${buttonTextColor};">${buttonText}</button>` : ''}
                    ${button2Text ? `<button class="px-4 py-2 rounded-lg text-sm font-semibold border-2" style="background: ${button2Bg}; color: ${button2TextColor}; border-color: ${button2TextColor};">${button2Text}</button>` : ''}
                </div>
            </div>
        `;
    }
}

function hexToRgb(hex) {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return `${r}, ${g}, ${b}`;
}

// Initialize accordions
document.addEventListener('DOMContentLoaded', function() {
    toggleAccordion('contentSettings');
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
