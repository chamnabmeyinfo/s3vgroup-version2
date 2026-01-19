<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

use App\Services\ColorExtractor;

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle cache clearing
    if (isset($_POST['clear_cache']) && $_POST['clear_cache'] == '1') {
        require_csrf();
        
        try {
            $cleared = [];
            
            // Clear OPcache if available
            if (function_exists('opcache_reset')) {
                if (opcache_reset()) {
                    $cleared[] = 'OPcache';
                }
            }
            
            // Clear APCu cache if available
            if (function_exists('apcu_clear_cache')) {
                if (apcu_clear_cache()) {
                    $cleared[] = 'APCu cache';
                }
            }
            
            // Clear file-based cache using CacheService
            try {
                $cacheService = new \App\Services\CacheService();
                $cacheService->clear();
                $cleared[] = 'File cache';
            } catch (\Exception $e) {
                // Fallback: manual file clearing
                $cacheDir = __DIR__ . '/../storage/cache';
                if (is_dir($cacheDir)) {
                    $files = glob($cacheDir . '/*');
                    foreach ($files as $file) {
                        if (is_file($file) && basename($file) !== '.gitkeep') {
                            @unlink($file);
                        }
                    }
                    $cleared[] = 'File cache';
                }
            }
            
            if (!empty($cleared)) {
                $message = 'Cache cleared successfully: ' . implode(', ', $cleared) . '.';
            } else {
                $message = 'No cache systems found or already cleared.';
            }
        } catch (\Exception $e) {
            $error = 'Error clearing cache: ' . $e->getMessage();
        }
        
        // Redirect to prevent form resubmission
        header('Location: ' . url('admin/settings.php') . '?cache_cleared=1');
        exit;
    }
    
    // Handle logo upload
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../storage/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $file = $_FILES['site_logo'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            $error = 'Invalid file type. Please upload JPG, PNG, GIF, WebP, or SVG.';
        } elseif ($file['size'] > $maxSize) {
            $error = 'File size exceeds 5MB limit.';
        } else {
            // Delete old logo if exists
            $oldLogo = db()->fetchOne("SELECT value FROM settings WHERE `key` = 'site_logo'");
            if ($oldLogo && file_exists(__DIR__ . '/../' . $oldLogo['value'])) {
                @unlink(__DIR__ . '/../' . $oldLogo['value']);
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'logo_' . time() . '_' . uniqid() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $logoPath = 'storage/uploads/' . $filename;
                $existing = db()->fetchOne("SELECT id FROM settings WHERE `key` = 'site_logo'");
                
                if ($existing) {
                    db()->update('settings', ['value' => $logoPath], '`key` = :key', ['key' => 'site_logo']);
                } else {
                    db()->insert('settings', [
                        'key' => 'site_logo',
                        'value' => $logoPath,
                        'type' => 'image'
                    ]);
                }
                
                // Extract colors from logo
                try {
                    $colorExtractor = new ColorExtractor();
                    $colorPalette = $colorExtractor->getColorPalette($filepath);
                    
                    // Save color palette to settings
                    $colorKeys = ['logo_primary_color', 'logo_secondary_color', 'logo_accent_color', 'logo_tertiary_color', 'logo_quaternary_color'];
                    $colorValues = array_values($colorPalette);
                    
                    foreach ($colorKeys as $index => $key) {
                        $existing = db()->fetchOne("SELECT id FROM settings WHERE `key` = :key", ['key' => $key]);
                        if ($existing) {
                            db()->update('settings', ['value' => $colorValues[$index]], '`key` = :key', ['key' => $key]);
                        } else {
                            db()->insert('settings', [
                                'key' => $key,
                                'value' => $colorValues[$index],
                                'type' => 'color'
                            ]);
                        }
                    }
                    
                    // Save full palette as JSON for easy access
                    $paletteJson = json_encode($colorPalette);
                    $existing = db()->fetchOne("SELECT id FROM settings WHERE `key` = :key", ['key' => 'logo_color_palette']);
                    if ($existing) {
                        db()->update('settings', ['value' => $paletteJson], '`key` = :key', ['key' => 'logo_color_palette']);
                    } else {
                        db()->insert('settings', [
                            'key' => 'logo_color_palette',
                            'value' => $paletteJson,
                            'type' => 'json'
                        ]);
                    }
                    
                    $message = 'Logo uploaded and colors extracted successfully.';
                } catch (Exception $e) {
                    $message = 'Logo uploaded, but color extraction failed: ' . $e->getMessage();
                }
            } else {
                $error = 'Failed to upload logo.';
            }
        }
    }
    
    // Handle text settings
    foreach ($_POST as $key => $value) {
        if ($key !== 'submit' && $key !== 'site_logo') {
            $existing = db()->fetchOne("SELECT id FROM settings WHERE `key` = :key", ['key' => $key]);
            
            if ($existing) {
                db()->update('settings', ['value' => trim($value)], '`key` = :key', ['key' => $key]);
            } else {
                db()->insert('settings', [
                    'key' => $key,
                    'value' => trim($value),
                    'type' => 'text'
                ]);
            }
        }
    }
    
    if (empty($error)) {
        $message = 'Settings updated successfully.';
    }
}

// Get all settings
$settingsData = db()->fetchAll("SELECT `key`, value FROM settings");
$settings = [];
foreach ($settingsData as $setting) {
    $settings[$setting['key']] = $setting['value'];
}

// Default values if not set
$defaults = [
    'site_name' => 'Forklift & Equipment Pro',
    'site_email' => 'info@example.com',
    'site_phone' => '+1 (555) 123-4567',
    'hotline' => '012 345 678',
    'site_address' => '123 Industrial Way, City, State 12345',
    'footer_text' => '© 2024 Forklift & Equipment Pro. All rights reserved.',
    'logo_height_mobile' => '40',
    'logo_height_tablet' => '56',
    'logo_height_desktop' => '64',
    'logo_max_width' => ''
];

foreach ($defaults as $key => $default) {
    if (!isset($settings[$key])) {
        $settings[$key] = $default;
    }
}

$pageTitle = 'Site Settings';
include __DIR__ . '/includes/header.php';
?>

<div class="w-full">
    <!-- Header -->
    <div class="bg-gradient-to-r from-gray-700 to-gray-900 rounded-xl shadow-xl p-4 md:p-6 lg:p-8 mb-4 md:mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-1 md:mb-2">
                    <i class="fas fa-cog mr-2 md:mr-3"></i>
                    Site Settings
                </h1>
                <p class="text-gray-300 text-sm md:text-lg">Configure your website settings</p>
            </div>
        </div>
    </div>

    <?php 
    // Check for cache cleared message from redirect
    if (isset($_GET['cache_cleared']) && $_GET['cache_cleared'] == '1') {
        $message = 'Cache cleared successfully!';
    }
    ?>
    
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

    <!-- Tab Navigation -->
    <div class="bg-white rounded-xl shadow-lg mb-6 overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex flex-wrap -mb-px" id="settings-tabs">
                <button type="button" onclick="showSettingsTab('general')" id="tab-btn-general" class="settings-tab-button active px-6 py-4 text-sm font-medium border-b-2 border-indigo-600 text-indigo-600 transition-all">
                    <i class="fas fa-info-circle mr-2"></i>General
                </button>
                <button type="button" onclick="showSettingsTab('logo')" id="tab-btn-logo" class="settings-tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all">
                    <i class="fas fa-image mr-2"></i>Logo & Branding
                </button>
                <button type="button" onclick="showSettingsTab('contact')" id="tab-btn-contact" class="settings-tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all">
                    <i class="fas fa-address-book mr-2"></i>Contact
                </button>
                <button type="button" onclick="showSettingsTab('sliders')" id="tab-btn-sliders" class="settings-tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all">
                    <i class="fas fa-palette mr-2"></i>Logo Sliders
                </button>
                <button type="button" onclick="showSettingsTab('system')" id="tab-btn-system" class="settings-tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all">
                    <i class="fas fa-tools mr-2"></i>System
                </button>
            </nav>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-lg p-4 md:p-6 lg:p-8">
        
        <!-- ============================================ -->
        <!-- TAB 1: GENERAL SITE INFORMATION -->
        <!-- ============================================ -->
        <div id="tab-content-general" class="settings-tab-content">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-2 flex items-center">
                    <i class="fas fa-info-circle text-indigo-600 mr-3"></i>
                    General Site Information
                </h2>
                <p class="text-gray-600">Configure basic website information and settings</p>
            </div>
            
            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-6 border-2 border-gray-300 space-y-4">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-info-circle text-gray-600 mr-3"></i>
                General Site Information
            </h2>
            <p class="text-sm text-gray-600 mb-6">Basic information about your website</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-globe text-gray-400 mr-2"></i> Site Name
                    </label>
                    <input type="text" name="site_name" value="<?= escape($settings['site_name']) ?>"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-all">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-file-alt text-gray-400 mr-2"></i> Footer Text
                    </label>
                    <textarea name="footer_text" rows="2" 
                              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-all"><?= escape($settings['footer_text']) ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- ============================================ -->
        <!-- TAB 2: COMPANY LOGO & BRANDING -->
        <!-- ============================================ -->
        <div id="tab-content-logo" class="settings-tab-content hidden">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-2 flex items-center">
                    <i class="fas fa-image text-blue-600 mr-3"></i>
                    Company Logo & Branding
                </h2>
                <p class="text-gray-600">Upload your company logo and configure its display settings</p>
            </div>
            
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 border-2 border-blue-200">
            
            <!-- Extracted Colors Display -->
            <?php 
            $colorPalette = null;
            if (!empty($settings['logo_color_palette'])) {
                $colorPalette = json_decode($settings['logo_color_palette'], true);
            } elseif (!empty($settings['logo_primary_color'])) {
                $colorPalette = [
                    'primary' => $settings['logo_primary_color'] ?? '#2563eb',
                    'secondary' => $settings['logo_secondary_color'] ?? '#1e40af',
                    'accent' => $settings['logo_accent_color'] ?? '#3b82f6',
                    'tertiary' => $settings['logo_tertiary_color'] ?? '#60a5fa',
                    'quaternary' => $settings['logo_quaternary_color'] ?? '#93c5fd',
                ];
            }
            ?>
            
            <?php if ($colorPalette): ?>
            <div class="mb-4 p-4 bg-white rounded-lg border border-gray-200">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">
                    <i class="fas fa-palette text-purple-600 mr-2"></i> Extracted Color Palette
                </h4>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                    <?php foreach ($colorPalette as $name => $color): ?>
                    <div class="text-center">
                        <div class="w-full h-16 rounded-lg shadow-md mb-2 border-2 border-gray-200" style="background-color: <?= escape($color) ?>"></div>
                        <p class="text-xs font-medium text-gray-600 capitalize"><?= escape($name) ?></p>
                        <p class="text-xs text-gray-500 font-mono"><?= escape($color) ?></p>
                        <button type="button" onclick="copyColor('<?= escape($color) ?>')" class="mt-1 text-xs text-blue-600 hover:text-blue-800">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <p class="text-xs text-gray-500 mt-3">
                    <i class="fas fa-info-circle mr-1"></i> These colors are automatically extracted from your logo and applied throughout the website.
                </p>
            </div>
            <?php endif; ?>
            
            <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                <div class="flex-shrink-0">
                    <?php 
                    $logoPath = $settings['site_logo'] ?? null;
                    $logoUrl = $logoPath ? image_url($logoPath) : null;
                    ?>
                    <div class="w-32 h-32 bg-white rounded-lg border-2 border-gray-200 flex items-center justify-center overflow-hidden shadow-md">
                        <?php if ($logoUrl): ?>
                            <img src="<?= escape($logoUrl) ?>" alt="Company Logo" class="max-w-full max-h-full object-contain" id="logo-preview">
                        <?php else: ?>
                            <div class="text-center text-gray-400">
                                <i class="fas fa-image text-4xl mb-2"></i>
                                <p class="text-xs">No Logo</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex-1">
                    <input type="file" 
                           name="site_logo" 
                           id="site_logo" 
                           accept="image/jpeg,image/jpg,image/png,image/gif,image/webp,image/svg+xml"
                           onchange="previewLogo(this)"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all bg-white">
                    <p class="text-xs text-gray-500 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Recommended: PNG or SVG with transparent background. Max size: 5MB. Colors will be automatically extracted.
                    </p>
                </div>
            </div>
            
            <!-- Logo Size Settings -->
            <div class="mt-6 pt-6 border-t border-blue-200">
                <label class="block text-sm font-semibold text-gray-700 mb-4">
                    <i class="fas fa-ruler text-blue-600 mr-2"></i> Logo Size Settings
                </label>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Logo Height (Mobile) -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">
                            <i class="fas fa-mobile-alt text-gray-400 mr-1"></i> Mobile Height (px)
                        </label>
                        <input type="number" 
                               name="logo_height_mobile" 
                               value="<?= escape($settings['logo_height_mobile'] ?? '40') ?>"
                               min="20" 
                               max="200" 
                               step="1"
                               class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm">
                        <p class="text-xs text-gray-500 mt-1">Default: 40px</p>
                    </div>
                    
                    <!-- Logo Height (Tablet) -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">
                            <i class="fas fa-tablet-alt text-gray-400 mr-1"></i> Tablet Height (px)
                        </label>
                        <input type="number" 
                               name="logo_height_tablet" 
                               value="<?= escape($settings['logo_height_tablet'] ?? '56') ?>"
                               min="20" 
                               max="200" 
                               step="1"
                               class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm">
                        <p class="text-xs text-gray-500 mt-1">Default: 56px</p>
                    </div>
                    
                    <!-- Logo Height (Desktop) -->
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">
                            <i class="fas fa-desktop text-gray-400 mr-1"></i> Desktop Height (px)
                        </label>
                        <input type="number" 
                               name="logo_height_desktop" 
                               value="<?= escape($settings['logo_height_desktop'] ?? '64') ?>"
                               min="20" 
                               max="200" 
                               step="1"
                               class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm">
                        <p class="text-xs text-gray-500 mt-1">Default: 64px</p>
                    </div>
                </div>
                
                <!-- Logo Width (Optional - Auto if not set) -->
                <div class="mt-4">
                    <label class="block text-xs font-medium text-gray-600 mb-2">
                        <i class="fas fa-arrows-alt-h text-gray-400 mr-1"></i> Max Width (px) - Optional
                    </label>
                    <input type="number" 
                           name="logo_max_width" 
                           value="<?= escape($settings['logo_max_width'] ?? '') ?>"
                           min="0" 
                           max="500" 
                           step="1"
                           placeholder="Auto (maintain aspect ratio)"
                           class="w-full md:w-1/3 px-3 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm">
                    <p class="text-xs text-gray-500 mt-1">
                        <i class="fas fa-info-circle mr-1"></i>
                        Leave empty for auto width (maintains aspect ratio). Set a value to limit maximum width.
                    </p>
                </div>
                
                <!-- Preview -->
                <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <p class="text-xs font-semibold text-gray-700 mb-3">
                        <i class="fas fa-eye text-blue-600 mr-1"></i> Size Preview
                    </p>
                    <div class="flex items-center gap-4">
                        <div class="text-center">
                            <p class="text-xs text-gray-600 mb-2">Mobile</p>
                            <div class="bg-white border-2 border-gray-300 rounded p-2 inline-block logo-preview-mobile">
                                <?php if ($logoUrl): ?>
                                    <img src="<?= escape($logoUrl) ?>" 
                                         alt="Logo Preview" 
                                         style="height: <?= escape($settings['logo_height_mobile'] ?? '40') ?>px; <?= !empty($settings['logo_max_width']) ? 'max-width: ' . escape($settings['logo_max_width']) . 'px;' : '' ?> width: auto; object-fit: contain;">
                                <?php else: ?>
                                    <div class="bg-gray-200 rounded" style="width: <?= escape($settings['logo_height_mobile'] ?? '40') ?>px; height: <?= escape($settings['logo_height_mobile'] ?? '40') ?>px;"></div>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 logo-size-mobile"><?= escape($settings['logo_height_mobile'] ?? '40') ?>px</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-600 mb-2">Tablet</p>
                            <div class="bg-white border-2 border-gray-300 rounded p-2 inline-block logo-preview-tablet">
                                <?php if ($logoUrl): ?>
                                    <img src="<?= escape($logoUrl) ?>" 
                                         alt="Logo Preview" 
                                         style="height: <?= escape($settings['logo_height_tablet'] ?? '56') ?>px; <?= !empty($settings['logo_max_width']) ? 'max-width: ' . escape($settings['logo_max_width']) . 'px;' : '' ?> width: auto; object-fit: contain;">
                                <?php else: ?>
                                    <div class="bg-gray-200 rounded" style="width: <?= escape($settings['logo_height_tablet'] ?? '56') ?>px; height: <?= escape($settings['logo_height_tablet'] ?? '56') ?>px;"></div>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 logo-size-tablet"><?= escape($settings['logo_height_tablet'] ?? '56') ?>px</p>
                        </div>
                        <div class="text-center">
                            <p class="text-xs text-gray-600 mb-2">Desktop</p>
                            <div class="bg-white border-2 border-gray-300 rounded p-2 inline-block logo-preview-desktop">
                                <?php if ($logoUrl): ?>
                                    <img src="<?= escape($logoUrl) ?>" 
                                         alt="Logo Preview" 
                                         style="height: <?= escape($settings['logo_height_desktop'] ?? '64') ?>px; <?= !empty($settings['logo_max_width']) ? 'max-width: ' . escape($settings['logo_max_width']) . 'px;' : '' ?> width: auto; object-fit: contain;">
                                <?php else: ?>
                                    <div class="bg-gray-200 rounded" style="width: <?= escape($settings['logo_height_desktop'] ?? '64') ?>px; height: <?= escape($settings['logo_height_desktop'] ?? '64') ?>px;"></div>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 logo-size-desktop"><?= escape($settings['logo_height_desktop'] ?? '64') ?>px</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <!-- ============================================ -->
        <!-- TAB 3: CONTACT INFORMATION -->
        <!-- ============================================ -->
        <div id="tab-content-contact" class="settings-tab-content hidden">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-2 flex items-center">
                    <i class="fas fa-address-book text-green-600 mr-3"></i>
                    Contact Information
                </h2>
                <p class="text-gray-600">Contact details displayed throughout your website</p>
            </div>
            
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 border-2 border-green-200">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-envelope text-gray-400 mr-2"></i> Site Email
                    </label>
                    <input type="email" name="site_email" value="<?= escape($settings['site_email']) ?>"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all">
                    <p class="text-xs text-gray-500 mt-1">Primary contact email address</p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-phone text-gray-400 mr-2"></i> Site Phone
                    </label>
                    <input type="text" name="site_phone" value="<?= escape($settings['site_phone']) ?>"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all">
                    <p class="text-xs text-gray-500 mt-1">Main business phone number</p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-phone-alt text-red-400 mr-2"></i> Hotline (Header Display)
                    </label>
                    <input type="text" name="hotline" value="<?= escape($settings['hotline'] ?? '') ?>"
                           placeholder="e.g., 012 345 678 or +855 12 345 678"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all">
                    <p class="text-xs text-gray-500 mt-1">This number will be displayed prominently in the header hotline button</p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i> Site Address
                    </label>
                    <textarea name="site_address" rows="3" 
                              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all"><?= escape($settings['site_address']) ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">Physical business address</p>
                </div>
            </div>
        </div>
        
        <!-- ============================================ -->
        <!-- TAB 4: LOGO SLIDER STYLING -->
        <!-- ============================================ -->
        <div id="tab-content-sliders" class="settings-tab-content hidden">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-2 flex items-center">
                    <i class="fas fa-palette text-purple-600 mr-3"></i>
                    Logo Slider Styling
                </h2>
                <p class="text-gray-600">Customize the appearance of Partners, Clients, and Quality Certifications logo sliders</p>
            </div>
            
            <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-6 border-2 border-purple-200">
            
            <!-- Style Presets -->
            <div class="mb-6 p-4 bg-white rounded-lg border-2 border-purple-200">
                <h3 class="text-sm font-bold text-gray-700 mb-4 flex items-center">
                    <i class="fas fa-magic text-purple-600 mr-2"></i> Quick Style Presets
                </h3>
                <p class="text-xs text-gray-600 mb-4">Choose a preset style to quickly apply modern designs. You can customize individual settings after applying a preset.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
                    <!-- Minimal Modern Preset -->
                    <button type="button" onclick="applyPreset('minimal')" class="preset-card group relative bg-gradient-to-br from-gray-50 to-gray-100 border-2 border-gray-300 rounded-lg p-4 hover:border-purple-400 hover:shadow-lg transition-all text-left">
                        <div class="flex items-center justify-between mb-2">
                            <i class="fas fa-circle-notch text-gray-400 text-lg"></i>
                            <span class="text-xs font-semibold text-gray-600">Minimal</span>
                        </div>
                        <p class="text-xs text-gray-500 mb-3">Clean, simple design with subtle borders</p>
                        <div class="flex gap-1">
                            <div class="w-8 h-8 bg-white border border-gray-300 rounded"></div>
                            <div class="w-8 h-8 bg-white border border-gray-300 rounded"></div>
                            <div class="w-8 h-8 bg-white border border-gray-300 rounded"></div>
                        </div>
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <i class="fas fa-check-circle text-purple-600"></i>
                        </div>
                    </button>
                    
                    <!-- Bold Corporate Preset -->
                    <button type="button" onclick="applyPreset('bold')" class="preset-card group relative bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-300 rounded-lg p-4 hover:border-purple-400 hover:shadow-lg transition-all text-left">
                        <div class="flex items-center justify-between mb-2">
                            <i class="fas fa-building text-blue-500 text-lg"></i>
                            <span class="text-xs font-semibold text-blue-700">Bold</span>
                        </div>
                        <p class="text-xs text-blue-600 mb-3">Strong borders, vibrant colors</p>
                        <div class="flex gap-1">
                            <div class="w-8 h-8 bg-white border-2 border-blue-500 rounded shadow"></div>
                            <div class="w-8 h-8 bg-white border-2 border-blue-500 rounded shadow"></div>
                            <div class="w-8 h-8 bg-white border-2 border-blue-500 rounded shadow"></div>
                        </div>
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <i class="fas fa-check-circle text-purple-600"></i>
                        </div>
                    </button>
                    
                    <!-- Elegant Classic Preset -->
                    <button type="button" onclick="applyPreset('elegant')" class="preset-card group relative bg-gradient-to-br from-amber-50 to-yellow-50 border-2 border-amber-300 rounded-lg p-4 hover:border-purple-400 hover:shadow-lg transition-all text-left">
                        <div class="flex items-center justify-between mb-2">
                            <i class="fas fa-gem text-amber-600 text-lg"></i>
                            <span class="text-xs font-semibold text-amber-700">Elegant</span>
                        </div>
                        <p class="text-xs text-amber-600 mb-3">Sophisticated with soft shadows</p>
                        <div class="flex gap-1">
                            <div class="w-8 h-8 bg-white border border-amber-300 rounded-lg shadow-md"></div>
                            <div class="w-8 h-8 bg-white border border-amber-300 rounded-lg shadow-md"></div>
                            <div class="w-8 h-8 bg-white border border-amber-300 rounded-lg shadow-md"></div>
                        </div>
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <i class="fas fa-check-circle text-purple-600"></i>
                        </div>
                    </button>
                    
                    <!-- Colorful Vibrant Preset -->
                    <button type="button" onclick="applyPreset('colorful')" class="preset-card group relative bg-gradient-to-br from-pink-50 to-rose-50 border-2 border-pink-300 rounded-lg p-4 hover:border-purple-400 hover:shadow-lg transition-all text-left">
                        <div class="flex items-center justify-between mb-2">
                            <i class="fas fa-paint-brush text-pink-500 text-lg"></i>
                            <span class="text-xs font-semibold text-pink-700">Colorful</span>
                        </div>
                        <p class="text-xs text-pink-600 mb-3">Bright gradients, playful design</p>
                        <div class="flex gap-1">
                            <div class="w-8 h-8 bg-gradient-to-br from-pink-200 to-rose-200 border-2 border-pink-400 rounded-lg"></div>
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-200 to-indigo-200 border-2 border-blue-400 rounded-lg"></div>
                            <div class="w-8 h-8 bg-gradient-to-br from-green-200 to-emerald-200 border-2 border-green-400 rounded-lg"></div>
                        </div>
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <i class="fas fa-check-circle text-purple-600"></i>
                        </div>
                    </button>
                    
                    <!-- Dark Professional Preset -->
                    <button type="button" onclick="applyPreset('dark')" class="preset-card group relative bg-gradient-to-br from-gray-800 to-gray-900 border-2 border-gray-700 rounded-lg p-4 hover:border-purple-400 hover:shadow-lg transition-all text-left">
                        <div class="flex items-center justify-between mb-2">
                            <i class="fas fa-moon text-gray-300 text-lg"></i>
                            <span class="text-xs font-semibold text-gray-200">Dark</span>
                        </div>
                        <p class="text-xs text-gray-300 mb-3">Professional dark theme</p>
                        <div class="flex gap-1">
                            <div class="w-8 h-8 bg-gray-700 border border-gray-600 rounded"></div>
                            <div class="w-8 h-8 bg-gray-700 border border-gray-600 rounded"></div>
                            <div class="w-8 h-8 bg-gray-700 border border-gray-600 rounded"></div>
                        </div>
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <i class="fas fa-check-circle text-purple-400"></i>
                        </div>
                    </button>
                </div>
            </div>
            
            <div class="space-y-6">
                <!-- Partners Logo Styling -->
                <div class="bg-white rounded-lg p-4 border border-purple-200">
                    <h4 class="text-sm font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-handshake text-blue-600 mr-2"></i> Partners Logo Styling
                    </h4>
                    
                    <!-- Section Background -->
                    <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                        <h5 class="text-xs font-bold text-gray-700 mb-3">Section Background</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Background Color 1</label>
                                <input type="color" name="partners_section_bg_color1" value="<?= escape($settings['partners_section_bg_color1'] ?? '#f0f7ff') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Background Color 2</label>
                                <input type="color" name="partners_section_bg_color2" value="<?= escape($settings['partners_section_bg_color2'] ?? '#e0efff') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Section Padding (px)</label>
                                <input type="number" name="partners_section_padding" value="<?= escape($settings['partners_section_padding'] ?? '80') ?>" min="20" max="200" step="10" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Header Styling -->
                    <div class="mb-4 p-3 bg-purple-50 rounded-lg border border-purple-200">
                        <h5 class="text-xs font-bold text-gray-700 mb-3">Header Styling</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Title Color 1</label>
                                <input type="color" name="partners_title_color1" value="<?= escape($settings['partners_title_color1'] ?? '#1e40af') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Title Color 2</label>
                                <input type="color" name="partners_title_color2" value="<?= escape($settings['partners_title_color2'] ?? '#3b82f6') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Description Color</label>
                                <input type="color" name="partners_desc_color" value="<?= escape($settings['partners_desc_color'] ?? '#475569') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Item Styling -->
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <h5 class="text-xs font-bold text-gray-700 mb-3">Logo Item Styling</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Item Width (px)</label>
                                <input type="number" name="partners_logo_item_width" value="<?= escape($settings['partners_logo_item_width'] ?? '180') ?>" min="100" max="400" step="10" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Item Height (px)</label>
                                <input type="number" name="partners_logo_item_height" value="<?= escape($settings['partners_logo_item_height'] ?? '100') ?>" min="60" max="300" step="10" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Gap Between Items (px)</label>
                                <input type="number" name="partners_logo_gap" value="<?= escape($settings['partners_logo_gap'] ?? '40') ?>" min="10" max="100" step="5" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Padding (px)</label>
                                <input type="number" name="partners_logo_padding" value="<?= escape($settings['partners_logo_padding'] ?? '20') ?>" min="0" max="50" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Border Width (px)</label>
                                <input type="number" name="partners_logo_border_width" value="<?= escape($settings['partners_logo_border_width'] ?? '2') ?>" min="0" max="10" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Border Style</label>
                                <select name="partners_logo_border_style" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                                    <option value="solid" <?= ($settings['partners_logo_border_style'] ?? 'solid') === 'solid' ? 'selected' : '' ?>>Solid</option>
                                    <option value="dashed" <?= ($settings['partners_logo_border_style'] ?? '') === 'dashed' ? 'selected' : '' ?>>Dashed</option>
                                    <option value="dotted" <?= ($settings['partners_logo_border_style'] ?? '') === 'dotted' ? 'selected' : '' ?>>Dotted</option>
                                    <option value="double" <?= ($settings['partners_logo_border_style'] ?? '') === 'double' ? 'selected' : '' ?>>Double</option>
                                    <option value="none" <?= ($settings['partners_logo_border_style'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Border Color</label>
                                <input type="color" name="partners_logo_border_color" value="<?= escape($settings['partners_logo_border_color'] ?? '#3b82f6') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Border Radius (px)</label>
                                <input type="number" name="partners_logo_border_radius" value="<?= escape($settings['partners_logo_border_radius'] ?? '12') ?>" min="0" max="50" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Background Color</label>
                                <input type="color" name="partners_logo_bg_color" value="<?= escape($settings['partners_logo_bg_color'] ?? '#ffffff') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Box Shadow -->
                    <div class="mb-4 p-3 bg-indigo-50 rounded-lg border border-indigo-200">
                        <h5 class="text-xs font-bold text-gray-700 mb-3">Box Shadow</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Shadow X (px)</label>
                                <input type="number" name="partners_logo_shadow_x" value="<?= escape($settings['partners_logo_shadow_x'] ?? '0') ?>" min="-20" max="20" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Shadow Y (px)</label>
                                <input type="number" name="partners_logo_shadow_y" value="<?= escape($settings['partners_logo_shadow_y'] ?? '2') ?>" min="-20" max="20" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Shadow Blur (px)</label>
                                <input type="number" name="partners_logo_shadow_blur" value="<?= escape($settings['partners_logo_shadow_blur'] ?? '8') ?>" min="0" max="50" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Shadow Color</label>
                                <input type="color" name="partners_logo_shadow_color" value="<?= escape($settings['partners_logo_shadow_color'] ?? '#3b82f6') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Shadow Opacity (%)</label>
                                <input type="number" name="partners_logo_shadow_opacity" value="<?= escape($settings['partners_logo_shadow_opacity'] ?? '10') ?>" min="0" max="100" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hover Effects -->
                    <div class="mb-4 p-3 bg-pink-50 rounded-lg border border-pink-200">
                        <h5 class="text-xs font-bold text-gray-700 mb-3">Hover Effects</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Hover Transform Y (px)</label>
                                <input type="number" name="partners_logo_hover_y" value="<?= escape($settings['partners_logo_hover_y'] ?? '-8') ?>" min="-50" max="50" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Hover Scale</label>
                                <input type="number" name="partners_logo_hover_scale" value="<?= escape($settings['partners_logo_hover_scale'] ?? '1.02') ?>" min="0.5" max="2" step="0.01" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Hover Border Color</label>
                                <input type="color" name="partners_logo_hover_border_color" value="<?= escape($settings['partners_logo_hover_border_color'] ?? '#3b82f6') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Hover Shadow Y (px)</label>
                                <input type="number" name="partners_logo_hover_shadow_y" value="<?= escape($settings['partners_logo_hover_shadow_y'] ?? '8') ?>" min="-20" max="50" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Hover Shadow Blur (px)</label>
                                <input type="number" name="partners_logo_hover_shadow_blur" value="<?= escape($settings['partners_logo_hover_shadow_blur'] ?? '24') ?>" min="0" max="100" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Hover Shadow Opacity (%)</label>
                                <input type="number" name="partners_logo_hover_shadow_opacity" value="<?= escape($settings['partners_logo_hover_shadow_opacity'] ?? '20') ?>" min="0" max="100" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Transition Duration (ms)</label>
                                <input type="number" name="partners_logo_transition" value="<?= escape($settings['partners_logo_transition'] ?? '300') ?>" min="0" max="2000" step="50" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Image Effects -->
                    <div class="p-3 bg-cyan-50 rounded-lg border border-cyan-200">
                        <h5 class="text-xs font-bold text-gray-700 mb-3">Image Effects</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Image Fit</label>
                                <select name="partners_logo_object_fit" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                                    <option value="contain" <?= ($settings['partners_logo_object_fit'] ?? 'contain') === 'contain' ? 'selected' : '' ?>>Contain (Fit within frame)</option>
                                    <option value="cover" <?= ($settings['partners_logo_object_fit'] ?? '') === 'cover' ? 'selected' : '' ?>>Cover (Fill frame, may crop)</option>
                                    <option value="fill" <?= ($settings['partners_logo_object_fit'] ?? '') === 'fill' ? 'selected' : '' ?>>Fill (Stretch to fit)</option>
                                    <option value="scale-down" <?= ($settings['partners_logo_object_fit'] ?? '') === 'scale-down' ? 'selected' : '' ?>>Scale Down (Shrink if needed)</option>
                                    <option value="none" <?= ($settings['partners_logo_object_fit'] ?? '') === 'none' ? 'selected' : '' ?>>None (Original size)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Grayscale (%)</label>
                                <input type="number" name="partners_logo_grayscale" value="<?= escape($settings['partners_logo_grayscale'] ?? '80') ?>" min="0" max="100" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Image Opacity (%)</label>
                                <input type="number" name="partners_logo_image_opacity" value="<?= escape($settings['partners_logo_image_opacity'] ?? '80') ?>" min="0" max="100" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Hover Image Scale</label>
                                <input type="number" name="partners_logo_hover_image_scale" value="<?= escape($settings['partners_logo_hover_image_scale'] ?? '1.05') ?>" min="0.5" max="2" step="0.01" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Clients Logo Styling -->
                <div class="bg-white rounded-lg p-4 border border-green-200">
                    <h4 class="text-sm font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-building text-green-600 mr-2"></i> Clients Logo Styling
                    </h4>
                    
                    <!-- Section Background -->
                    <div class="mb-4 p-3 bg-green-50 rounded-lg border border-green-200">
                        <h5 class="text-xs font-bold text-gray-700 mb-3">Section Background</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Background Color 1</label>
                                <input type="color" name="clients_section_bg_color1" value="<?= escape($settings['clients_section_bg_color1'] ?? '#f0fdf4') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Background Color 2</label>
                                <input type="color" name="clients_section_bg_color2" value="<?= escape($settings['clients_section_bg_color2'] ?? '#dcfce7') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Section Padding (px)</label>
                                <input type="number" name="clients_section_padding" value="<?= escape($settings['clients_section_padding'] ?? '80') ?>" min="20" max="200" step="10" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Header Styling -->
                    <div class="mb-4 p-3 bg-emerald-50 rounded-lg border border-emerald-200">
                        <h5 class="text-xs font-bold text-gray-700 mb-3">Header Styling</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Title Color 1</label>
                                <input type="color" name="clients_title_color1" value="<?= escape($settings['clients_title_color1'] ?? '#059669') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Title Color 2</label>
                                <input type="color" name="clients_title_color2" value="<?= escape($settings['clients_title_color2'] ?? '#10b981') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Description Color</label>
                                <input type="color" name="clients_desc_color" value="<?= escape($settings['clients_desc_color'] ?? '#475569') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Item Styling -->
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <h5 class="text-xs font-bold text-gray-700 mb-3">Logo Item Styling</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Item Width (px)</label>
                                <input type="number" name="clients_logo_item_width" value="<?= escape($settings['clients_logo_item_width'] ?? '180') ?>" min="100" max="400" step="10" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Item Height (px)</label>
                                <input type="number" name="clients_logo_item_height" value="<?= escape($settings['clients_logo_item_height'] ?? '100') ?>" min="60" max="300" step="10" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Gap Between Items (px)</label>
                                <input type="number" name="clients_logo_gap" value="<?= escape($settings['clients_logo_gap'] ?? '40') ?>" min="10" max="100" step="5" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Padding (px)</label>
                                <input type="number" name="clients_logo_padding" value="<?= escape($settings['clients_logo_padding'] ?? '20') ?>" min="0" max="50" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Border Width (px)</label>
                                <input type="number" name="clients_logo_border_width" value="<?= escape($settings['clients_logo_border_width'] ?? '2') ?>" min="0" max="10" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Border Style</label>
                                <select name="clients_logo_border_style" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                                    <option value="solid" <?= ($settings['clients_logo_border_style'] ?? 'solid') === 'solid' ? 'selected' : '' ?>>Solid</option>
                                    <option value="dashed" <?= ($settings['clients_logo_border_style'] ?? '') === 'dashed' ? 'selected' : '' ?>>Dashed</option>
                                    <option value="dotted" <?= ($settings['clients_logo_border_style'] ?? '') === 'dotted' ? 'selected' : '' ?>>Dotted</option>
                                    <option value="double" <?= ($settings['clients_logo_border_style'] ?? '') === 'double' ? 'selected' : '' ?>>Double</option>
                                    <option value="none" <?= ($settings['clients_logo_border_style'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Border Color</label>
                                <input type="color" name="clients_logo_border_color" value="<?= escape($settings['clients_logo_border_color'] ?? '#10b981') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Border Radius (px)</label>
                                <input type="number" name="clients_logo_border_radius" value="<?= escape($settings['clients_logo_border_radius'] ?? '12') ?>" min="0" max="50" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Background Color</label>
                                <input type="color" name="clients_logo_bg_color" value="<?= escape($settings['clients_logo_bg_color'] ?? '#ffffff') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Box Shadow -->
                    <div class="mb-4 p-3 bg-teal-50 rounded-lg border border-teal-200">
                        <h5 class="text-xs font-bold text-gray-700 mb-3">Box Shadow</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Shadow X (px)</label>
                                <input type="number" name="clients_logo_shadow_x" value="<?= escape($settings['clients_logo_shadow_x'] ?? '0') ?>" min="-20" max="20" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Shadow Y (px)</label>
                                <input type="number" name="clients_logo_shadow_y" value="<?= escape($settings['clients_logo_shadow_y'] ?? '2') ?>" min="-20" max="20" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Shadow Blur (px)</label>
                                <input type="number" name="clients_logo_shadow_blur" value="<?= escape($settings['clients_logo_shadow_blur'] ?? '8') ?>" min="0" max="50" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Shadow Color</label>
                                <input type="color" name="clients_logo_shadow_color" value="<?= escape($settings['clients_logo_shadow_color'] ?? '#10b981') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Shadow Opacity (%)</label>
                                <input type="number" name="clients_logo_shadow_opacity" value="<?= escape($settings['clients_logo_shadow_opacity'] ?? '10') ?>" min="0" max="100" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hover Effects -->
                    <div class="mb-4 p-3 bg-lime-50 rounded-lg border border-lime-200">
                        <h5 class="text-xs font-bold text-gray-700 mb-3">Hover Effects</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Hover Transform Y (px)</label>
                                <input type="number" name="clients_logo_hover_y" value="<?= escape($settings['clients_logo_hover_y'] ?? '-8') ?>" min="-50" max="50" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Hover Scale</label>
                                <input type="number" name="clients_logo_hover_scale" value="<?= escape($settings['clients_logo_hover_scale'] ?? '1.02') ?>" min="0.5" max="2" step="0.01" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Hover Border Color</label>
                                <input type="color" name="clients_logo_hover_border_color" value="<?= escape($settings['clients_logo_hover_border_color'] ?? '#10b981') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Hover Shadow Y (px)</label>
                                <input type="number" name="clients_logo_hover_shadow_y" value="<?= escape($settings['clients_logo_hover_shadow_y'] ?? '8') ?>" min="-20" max="50" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Hover Shadow Blur (px)</label>
                                <input type="number" name="clients_logo_hover_shadow_blur" value="<?= escape($settings['clients_logo_hover_shadow_blur'] ?? '24') ?>" min="0" max="100" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Hover Shadow Opacity (%)</label>
                                <input type="number" name="clients_logo_hover_shadow_opacity" value="<?= escape($settings['clients_logo_hover_shadow_opacity'] ?? '20') ?>" min="0" max="100" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Transition Duration (ms)</label>
                                <input type="number" name="clients_logo_transition" value="<?= escape($settings['clients_logo_transition'] ?? '300') ?>" min="0" max="2000" step="50" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Image Effects -->
                    <div class="p-3 bg-emerald-50 rounded-lg border border-emerald-200">
                        <h5 class="text-xs font-bold text-gray-700 mb-3">Image Effects</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Image Fit</label>
                                <select name="clients_logo_object_fit" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                                    <option value="contain" <?= ($settings['clients_logo_object_fit'] ?? 'contain') === 'contain' ? 'selected' : '' ?>>Contain (Fit within frame)</option>
                                    <option value="cover" <?= ($settings['clients_logo_object_fit'] ?? '') === 'cover' ? 'selected' : '' ?>>Cover (Fill frame, may crop)</option>
                                    <option value="fill" <?= ($settings['clients_logo_object_fit'] ?? '') === 'fill' ? 'selected' : '' ?>>Fill (Stretch to fit)</option>
                                    <option value="scale-down" <?= ($settings['clients_logo_object_fit'] ?? '') === 'scale-down' ? 'selected' : '' ?>>Scale Down (Shrink if needed)</option>
                                    <option value="none" <?= ($settings['clients_logo_object_fit'] ?? '') === 'none' ? 'selected' : '' ?>>None (Original size)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Grayscale (%)</label>
                                <input type="number" name="clients_logo_grayscale" value="<?= escape($settings['clients_logo_grayscale'] ?? '80') ?>" min="0" max="100" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Image Opacity (%)</label>
                                <input type="number" name="clients_logo_image_opacity" value="<?= escape($settings['clients_logo_image_opacity'] ?? '80') ?>" min="0" max="100" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Hover Image Scale</label>
                                <input type="number" name="clients_logo_hover_image_scale" value="<?= escape($settings['clients_logo_hover_image_scale'] ?? '1.05') ?>" min="0.5" max="2" step="0.01" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quality Certifications Logo Styling -->
                <div class="bg-white rounded-lg p-4 border border-gray-200">
                    <h4 class="text-sm font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-certificate text-gray-600 mr-2"></i> Quality Certifications Logo Styling
                    </h4>
                    
                    <!-- Section Background -->
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <h5 class="text-xs font-bold text-gray-700 mb-3">Section Background</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Background Color 1</label>
                                <input type="color" name="certs_section_bg_color1" value="<?= escape($settings['certs_section_bg_color1'] ?? '#ffffff') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Background Color 2</label>
                                <input type="color" name="certs_section_bg_color2" value="<?= escape($settings['certs_section_bg_color2'] ?? '#f8f9fa') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Section Padding (px)</label>
                                <input type="number" name="certs_section_padding" value="<?= escape($settings['certs_section_padding'] ?? '60') ?>" min="20" max="200" step="10" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Header Styling -->
                    <div class="mb-4 p-3 bg-slate-50 rounded-lg border border-slate-200">
                        <h5 class="text-xs font-bold text-gray-700 mb-3">Header Styling</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Title Color</label>
                                <input type="color" name="certs_title_color" value="<?= escape($settings['certs_title_color'] ?? '#1a1a1a') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Description Color</label>
                                <input type="color" name="certs_desc_color" value="<?= escape($settings['certs_desc_color'] ?? '#666666') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Item Styling -->
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <h5 class="text-xs font-bold text-gray-700 mb-3">Logo Item Styling</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Item Width (px)</label>
                                <input type="number" name="certs_logo_item_width" value="<?= escape($settings['certs_logo_item_width'] ?? '160') ?>" min="100" max="400" step="10" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Item Height (px)</label>
                                <input type="number" name="certs_logo_item_height" value="<?= escape($settings['certs_logo_item_height'] ?? '120') ?>" min="60" max="300" step="10" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Gap Between Items (px)</label>
                                <input type="number" name="certs_logo_gap" value="<?= escape($settings['certs_logo_gap'] ?? '30') ?>" min="10" max="100" step="5" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Padding (px)</label>
                                <input type="number" name="certs_logo_padding" value="<?= escape($settings['certs_logo_padding'] ?? '20') ?>" min="0" max="50" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Border Width (px)</label>
                                <input type="number" name="certs_logo_border_width" value="<?= escape($settings['certs_logo_border_width'] ?? '1') ?>" min="0" max="10" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Border Style</label>
                                <select name="certs_logo_border_style" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                                    <option value="solid" <?= ($settings['certs_logo_border_style'] ?? 'solid') === 'solid' ? 'selected' : '' ?>>Solid</option>
                                    <option value="dashed" <?= ($settings['certs_logo_border_style'] ?? '') === 'dashed' ? 'selected' : '' ?>>Dashed</option>
                                    <option value="dotted" <?= ($settings['certs_logo_border_style'] ?? '') === 'dotted' ? 'selected' : '' ?>>Dotted</option>
                                    <option value="double" <?= ($settings['certs_logo_border_style'] ?? '') === 'double' ? 'selected' : '' ?>>Double</option>
                                    <option value="none" <?= ($settings['certs_logo_border_style'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Border Color</label>
                                <input type="color" name="certs_logo_border_color" value="<?= escape($settings['certs_logo_border_color'] ?? '#e5e7eb') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Border Radius (px)</label>
                                <input type="number" name="certs_logo_border_radius" value="<?= escape($settings['certs_logo_border_radius'] ?? '12') ?>" min="0" max="50" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Background Color</label>
                                <input type="color" name="certs_logo_bg_color" value="<?= escape($settings['certs_logo_bg_color'] ?? '#ffffff') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Box Shadow -->
                    <div class="mb-4 p-3 bg-stone-50 rounded-lg border border-stone-200">
                        <h5 class="text-xs font-bold text-gray-700 mb-3">Box Shadow</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Shadow X (px)</label>
                                <input type="number" name="certs_logo_shadow_x" value="<?= escape($settings['certs_logo_shadow_x'] ?? '0') ?>" min="-20" max="20" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Shadow Y (px)</label>
                                <input type="number" name="certs_logo_shadow_y" value="<?= escape($settings['certs_logo_shadow_y'] ?? '2') ?>" min="-20" max="20" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Shadow Blur (px)</label>
                                <input type="number" name="certs_logo_shadow_blur" value="<?= escape($settings['certs_logo_shadow_blur'] ?? '12') ?>" min="0" max="50" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Shadow Color</label>
                                <input type="color" name="certs_logo_shadow_color" value="<?= escape($settings['certs_logo_shadow_color'] ?? '#000000') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Shadow Opacity (%)</label>
                                <input type="number" name="certs_logo_shadow_opacity" value="<?= escape($settings['certs_logo_shadow_opacity'] ?? '8') ?>" min="0" max="100" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hover Effects -->
                    <div class="mb-4 p-3 bg-amber-50 rounded-lg border border-amber-200">
                        <h5 class="text-xs font-bold text-gray-700 mb-3">Hover Effects</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Hover Transform Y (px)</label>
                                <input type="number" name="certs_logo_hover_y" value="<?= escape($settings['certs_logo_hover_y'] ?? '-8') ?>" min="-50" max="50" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Hover Scale</label>
                                <input type="number" name="certs_logo_hover_scale" value="<?= escape($settings['certs_logo_hover_scale'] ?? '1.05') ?>" min="0.5" max="2" step="0.01" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Hover Border Color</label>
                                <input type="color" name="certs_logo_hover_border_color" value="<?= escape($settings['certs_logo_hover_border_color'] ?? '#3b82f6') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Hover Shadow Y (px)</label>
                                <input type="number" name="certs_logo_hover_shadow_y" value="<?= escape($settings['certs_logo_hover_shadow_y'] ?? '8') ?>" min="-20" max="50" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Hover Shadow Blur (px)</label>
                                <input type="number" name="certs_logo_hover_shadow_blur" value="<?= escape($settings['certs_logo_hover_shadow_blur'] ?? '24') ?>" min="0" max="100" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Hover Shadow Opacity (%)</label>
                                <input type="number" name="certs_logo_hover_shadow_opacity" value="<?= escape($settings['certs_logo_hover_shadow_opacity'] ?? '15') ?>" min="0" max="100" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Transition Duration (ms)</label>
                                <input type="number" name="certs_logo_transition" value="<?= escape($settings['certs_logo_transition'] ?? '300') ?>" min="0" max="2000" step="50" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Image Effects -->
                    <div class="mb-4 p-3 bg-neutral-50 rounded-lg border border-neutral-200">
                        <h5 class="text-xs font-bold text-gray-700 mb-3">Image Effects</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Image Fit</label>
                                <select name="certs_logo_object_fit" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                                    <option value="contain" <?= ($settings['certs_logo_object_fit'] ?? 'contain') === 'contain' ? 'selected' : '' ?>>Contain (Fit within frame)</option>
                                    <option value="cover" <?= ($settings['certs_logo_object_fit'] ?? '') === 'cover' ? 'selected' : '' ?>>Cover (Fill frame, may crop)</option>
                                    <option value="fill" <?= ($settings['certs_logo_object_fit'] ?? '') === 'fill' ? 'selected' : '' ?>>Fill (Stretch to fit)</option>
                                    <option value="scale-down" <?= ($settings['certs_logo_object_fit'] ?? '') === 'scale-down' ? 'selected' : '' ?>>Scale Down (Shrink if needed)</option>
                                    <option value="none" <?= ($settings['certs_logo_object_fit'] ?? '') === 'none' ? 'selected' : '' ?>>None (Original size)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Max Image Height (px)</label>
                                <input type="number" name="certs_logo_max_image_height" value="<?= escape($settings['certs_logo_max_image_height'] ?? '80') ?>" min="40" max="200" step="5" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Hover Image Scale</label>
                                <input type="number" name="certs_logo_hover_image_scale" value="<?= escape($settings['certs_logo_hover_image_scale'] ?? '1.1') ?>" min="0.5" max="2" step="0.01" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Text Styling (for certification names) -->
                    <div class="p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                        <h5 class="text-xs font-bold text-gray-700 mb-3">Text Styling (Certification Names)</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Text Color</label>
                                <input type="color" name="certs_text_color" value="<?= escape($settings['certs_text_color'] ?? '#6b7280') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Text Font Size (px)</label>
                                <input type="number" name="certs_text_font_size" value="<?= escape($settings['certs_text_font_size'] ?? '12') ?>" min="8" max="24" step="1" class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Hover Text Color</label>
                                <input type="color" name="certs_text_hover_color" value="<?= escape($settings['certs_text_hover_color'] ?? '#3b82f6') ?>" class="w-full h-10 border-2 border-gray-300 rounded-lg">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ============================================ -->
        <!-- TAB 5: SYSTEM & MAINTENANCE -->
        <!-- ============================================ -->
        <div id="tab-content-system" class="settings-tab-content hidden">
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-2 flex items-center">
                    <i class="fas fa-tools text-amber-600 mr-3"></i>
                    System & Maintenance
                </h2>
                <p class="text-gray-600">Manage cache and system maintenance tasks</p>
            </div>
            
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl p-6 border-2 border-amber-200">
            
            <!-- Cache Settings -->
            <div class="bg-white rounded-lg p-4 border border-amber-200">
                <h3 class="text-sm font-bold text-gray-700 mb-3 flex items-center">
                    <i class="fas fa-broom text-amber-600 mr-2"></i> Cache Management
                </h3>
                <p class="text-sm text-gray-700 mb-4">
                    <i class="fas fa-info-circle text-amber-600 mr-2"></i>
                    Clear cached data to ensure the latest content is displayed. This includes file cache, OPcache, and APCu cache.
                </p>
                
                <?php
                // Get cache statistics
                $cacheDir = __DIR__ . '/../storage/cache';
                $cacheFiles = [];
                $cacheSize = 0;
                if (is_dir($cacheDir)) {
                    $files = glob($cacheDir . '/*');
                    foreach ($files as $file) {
                        if (is_file($file) && basename($file) !== '.gitkeep') {
                            $cacheFiles[] = $file;
                            $cacheSize += filesize($file);
                        }
                    }
                }
                $cacheCount = count($cacheFiles);
                $cacheSizeFormatted = $cacheSize > 0 ? number_format($cacheSize / 1024, 2) . ' KB' : '0 KB';
                ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                        <p class="text-xs text-gray-600 mb-1">Cache Files</p>
                        <p class="text-lg font-bold text-gray-800"><?= $cacheCount ?></p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                        <p class="text-xs text-gray-600 mb-1">Cache Size</p>
                        <p class="text-lg font-bold text-gray-800"><?= $cacheSizeFormatted ?></p>
                    </div>
                </div>
                
                <form method="POST" action="<?= url('admin/settings.php') ?>" onsubmit="return confirmClearCache()" class="inline-block">
                    <input type="hidden" name="clear_cache" value="1">
                    <?= csrf_field() ?>
                    <button type="submit" class="bg-gradient-to-r from-amber-500 to-orange-500 text-white px-6 py-3 rounded-lg font-semibold hover:from-amber-600 hover:to-orange-600 transition-all duration-300 shadow-md hover:shadow-lg transform hover:scale-105">
                        <i class="fas fa-trash-alt mr-2"></i>
                        Clear All Cache
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Save Button - Fixed at bottom -->
        <div class="sticky bottom-0 bg-white border-t-2 border-gray-200 -mx-4 md:-mx-6 lg:-mx-8 px-4 md:px-6 lg:px-8 py-4 mt-8 shadow-lg">
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-600">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                    Make sure to save your changes after editing settings
                </p>
                <button type="submit" name="submit" class="bg-gradient-to-r from-indigo-600 to-indigo-700 text-white px-8 py-3 rounded-lg font-bold text-lg hover:from-indigo-700 hover:to-indigo-800 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                    <i class="fas fa-save mr-2"></i>
                    Save All Settings
                </button>
            </div>
        </div>
    </form>

    <style>
    /* Settings Tab Styles */
    .settings-tab-button {
        position: relative;
        transition: all 0.3s ease;
    }
    
    .settings-tab-button:hover {
        background-color: #f9fafb;
    }
    
    .settings-tab-button.active {
        background-color: #f9fafb;
    }
    
    .settings-tab-content {
        animation: fadeIn 0.3s ease-in;
        min-height: 200px;
    }
    
    .settings-tab-content.hidden {
        display: none !important;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .preset-card {
        transition: all 0.3s ease;
    }
    
    .preset-card:hover {
        transform: translateY(-2px);
    }
    
    .preset-card.ring-2 {
        animation: pulse 0.5s ease;
    }
    
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.02);
        }
    }
    
    /* Responsive tab navigation */
    @media (max-width: 768px) {
        .settings-tab-button {
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
        }
        
        .settings-tab-button i {
            display: none;
        }
    }
    </style>

    <script>
    function copyColor(color) {
        navigator.clipboard.writeText(color).then(function() {
            alert('Color code copied: ' + color);
        }).catch(function() {
            // Fallback for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = color;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            alert('Color code copied: ' + color);
        });
    }
    
    function previewLogo(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('logo-preview');
                if (preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                } else {
                    const container = input.closest('.bg-gradient-to-br').querySelector('.w-32');
                    if (container) {
                        container.innerHTML = '<img src="' + e.target.result + '" alt="Logo Preview" class="max-w-full max-h-full object-contain" id="logo-preview">';
                    }
                }
                // Update size previews after a short delay to ensure preview is loaded
                setTimeout(updateLogoSizePreview, 100);
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    function updateLogoSizePreview() {
        const mobileHeight = document.querySelector('input[name="logo_height_mobile"]')?.value || '40';
        const tabletHeight = document.querySelector('input[name="logo_height_tablet"]')?.value || '56';
        const desktopHeight = document.querySelector('input[name="logo_height_desktop"]')?.value || '64';
        const maxWidth = document.querySelector('input[name="logo_max_width"]')?.value || '';
        
        // Get logo URL from preview or file input
        let logoUrl = '';
        const logoPreview = document.getElementById('logo-preview');
        if (logoPreview && logoPreview.src) {
            logoUrl = logoPreview.src;
        } else {
            const fileInput = document.getElementById('site_logo');
            if (fileInput && fileInput.files && fileInput.files[0]) {
                // Will be updated when file is selected
                return;
            }
        }
        
        const maxWidthStyle = maxWidth ? `max-width: ${maxWidth}px;` : '';
        
        // Update mobile preview
        const mobilePreview = document.querySelector('.logo-preview-mobile');
        const mobileSize = document.querySelector('.logo-size-mobile');
        if (mobilePreview) {
            if (logoUrl && logoUrl !== '' && !logoUrl.includes('data:image/svg+xml')) {
                const img = mobilePreview.querySelector('img');
                if (img) {
                    img.style.height = mobileHeight + 'px';
                    if (maxWidth) img.style.maxWidth = maxWidth + 'px';
                } else {
                    mobilePreview.innerHTML = `<img src="${logoUrl}" alt="Logo Preview" style="height: ${mobileHeight}px; ${maxWidthStyle} width: auto; object-fit: contain;">`;
                }
            } else {
                mobilePreview.innerHTML = `<div class="bg-gray-200 rounded" style="width: ${mobileHeight}px; height: ${mobileHeight}px;"></div>`;
            }
            if (mobileSize) mobileSize.textContent = mobileHeight + 'px';
        }
        
        // Update tablet preview
        const tabletPreview = document.querySelector('.logo-preview-tablet');
        const tabletSize = document.querySelector('.logo-size-tablet');
        if (tabletPreview) {
            if (logoUrl && logoUrl !== '' && !logoUrl.includes('data:image/svg+xml')) {
                const img = tabletPreview.querySelector('img');
                if (img) {
                    img.style.height = tabletHeight + 'px';
                    if (maxWidth) img.style.maxWidth = maxWidth + 'px';
                } else {
                    tabletPreview.innerHTML = `<img src="${logoUrl}" alt="Logo Preview" style="height: ${tabletHeight}px; ${maxWidthStyle} width: auto; object-fit: contain;">`;
                }
            } else {
                tabletPreview.innerHTML = `<div class="bg-gray-200 rounded" style="width: ${tabletHeight}px; height: ${tabletHeight}px;"></div>`;
            }
            if (tabletSize) tabletSize.textContent = tabletHeight + 'px';
        }
        
        // Update desktop preview
        const desktopPreview = document.querySelector('.logo-preview-desktop');
        const desktopSize = document.querySelector('.logo-size-desktop');
        if (desktopPreview) {
            if (logoUrl && logoUrl !== '' && !logoUrl.includes('data:image/svg+xml')) {
                const img = desktopPreview.querySelector('img');
                if (img) {
                    img.style.height = desktopHeight + 'px';
                    if (maxWidth) img.style.maxWidth = maxWidth + 'px';
                } else {
                    desktopPreview.innerHTML = `<img src="${logoUrl}" alt="Logo Preview" style="height: ${desktopHeight}px; ${maxWidthStyle} width: auto; object-fit: contain;">`;
                }
            } else {
                desktopPreview.innerHTML = `<div class="bg-gray-200 rounded" style="width: ${desktopHeight}px; height: ${desktopHeight}px;"></div>`;
            }
            if (desktopSize) desktopSize.textContent = desktopHeight + 'px';
        }
    }
    
    // Update preview when size inputs change
    document.addEventListener('DOMContentLoaded', function() {
        const sizeInputs = document.querySelectorAll('input[name^="logo_height"], input[name="logo_max_width"]');
        sizeInputs.forEach(input => {
            input.addEventListener('input', updateLogoSizePreview);
            input.addEventListener('change', updateLogoSizePreview);
        });
    });
    
    function confirmClearCache() {
        return confirm('Are you sure you want to clear all cache? This action cannot be undone.');
    }
    
    // Tab Navigation Functions
    function showSettingsTab(tabName) {
        console.log('Switching to tab:', tabName);
        
        // Hide all tab contents with both methods
        document.querySelectorAll('.settings-tab-content').forEach(tab => {
            tab.style.display = 'none';
            tab.classList.add('hidden');
        });
        
        // Remove active class from all buttons
        document.querySelectorAll('.settings-tab-button').forEach(btn => {
            btn.classList.remove('active', 'border-indigo-600', 'text-indigo-600');
            btn.classList.add('border-transparent', 'text-gray-500');
        });
        
        // Show selected tab
        const selectedTab = document.getElementById('tab-content-' + tabName);
        console.log('Selected tab element:', selectedTab);
        if (selectedTab) {
            selectedTab.style.display = 'block';
            selectedTab.classList.remove('hidden');
            console.log('Tab shown successfully');
        } else {
            console.error('Tab not found: tab-content-' + tabName);
            alert('Tab content not found: ' + tabName);
        }
        
        // Activate button
        const activeButton = document.getElementById('tab-btn-' + tabName);
        if (activeButton) {
            activeButton.classList.add('active', 'border-indigo-600', 'text-indigo-600');
            activeButton.classList.remove('border-transparent', 'text-gray-500');
        }
        
        // Scroll to form
        setTimeout(() => {
            const formElement = document.querySelector('form[method="POST"]');
            if (formElement) {
                formElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 150);
    }
    
    // Make function globally available
    window.showSettingsTab = showSettingsTab;
    
    // Initialize - show first tab by default
    function initializeTabs() {
        console.log('Initializing tabs...');
        
        // Hide all tabs first
        document.querySelectorAll('.settings-tab-content').forEach(tab => {
            if (tab.id !== 'tab-content-general') {
                tab.style.display = 'none';
                tab.classList.add('hidden');
            }
        });
        
        // Show general tab
        const generalTab = document.getElementById('tab-content-general');
        if (generalTab) {
            generalTab.style.display = 'block';
            generalTab.classList.remove('hidden');
            console.log('General tab initialized');
        } else {
            console.error('General tab not found!');
        }
    }
    
    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing tabs');
            initializeTabs();
        });
    } else {
        console.log('DOM already ready, initializing tabs');
        initializeTabs();
    }
    
    // Style Presets
    const stylePresets = {
        minimal: {
            // Partners
            partners_section_bg_color1: '#f8f9fa',
            partners_section_bg_color2: '#ffffff',
            partners_section_padding: '60',
            partners_title_color1: '#1f2937',
            partners_title_color2: '#374151',
            partners_desc_color: '#6b7280',
            partners_logo_item_width: '160',
            partners_logo_item_height: '90',
            partners_logo_gap: '30',
            partners_logo_padding: '15',
            partners_logo_border_width: '1',
            partners_logo_border_style: 'solid',
            partners_logo_border_color: '#e5e7eb',
            partners_logo_border_radius: '8',
            partners_logo_bg_color: '#ffffff',
            partners_logo_shadow_x: '0',
            partners_logo_shadow_y: '1',
            partners_logo_shadow_blur: '3',
            partners_logo_shadow_color: '#000000',
            partners_logo_shadow_opacity: '5',
            partners_logo_hover_y: '-4',
            partners_logo_hover_scale: '1.02',
            partners_logo_hover_border_color: '#d1d5db',
            partners_logo_hover_shadow_y: '4',
            partners_logo_hover_shadow_blur: '12',
            partners_logo_hover_shadow_opacity: '10',
            partners_logo_transition: '250',
            partners_logo_object_fit: 'contain',
            partners_logo_grayscale: '0',
            partners_logo_image_opacity: '100',
            partners_logo_hover_image_scale: '1.03',
            // Clients
            clients_section_bg_color1: '#f8f9fa',
            clients_section_bg_color2: '#ffffff',
            clients_section_padding: '60',
            clients_title_color1: '#1f2937',
            clients_title_color2: '#374151',
            clients_desc_color: '#6b7280',
            clients_logo_item_width: '160',
            clients_logo_item_height: '90',
            clients_logo_gap: '30',
            clients_logo_padding: '15',
            clients_logo_border_width: '1',
            clients_logo_border_style: 'solid',
            clients_logo_border_color: '#e5e7eb',
            clients_logo_border_radius: '8',
            clients_logo_bg_color: '#ffffff',
            clients_logo_shadow_x: '0',
            clients_logo_shadow_y: '1',
            clients_logo_shadow_blur: '3',
            clients_logo_shadow_color: '#000000',
            clients_logo_shadow_opacity: '5',
            clients_logo_hover_y: '-4',
            clients_logo_hover_scale: '1.02',
            clients_logo_hover_border_color: '#d1d5db',
            clients_logo_hover_shadow_y: '4',
            clients_logo_hover_shadow_blur: '12',
            clients_logo_hover_shadow_opacity: '10',
            clients_logo_transition: '250',
            clients_logo_object_fit: 'contain',
            clients_logo_grayscale: '0',
            clients_logo_image_opacity: '100',
            clients_logo_hover_image_scale: '1.03',
            // Certifications
            certs_section_bg_color1: '#ffffff',
            certs_section_bg_color2: '#f9fafb',
            certs_section_padding: '50',
            certs_title_color: '#1f2937',
            certs_desc_color: '#6b7280',
            certs_logo_item_width: '150',
            certs_logo_item_height: '110',
            certs_logo_gap: '25',
            certs_logo_padding: '15',
            certs_logo_border_width: '1',
            certs_logo_border_style: 'solid',
            certs_logo_border_color: '#e5e7eb',
            certs_logo_border_radius: '8',
            certs_logo_bg_color: '#ffffff',
            certs_logo_shadow_x: '0',
            certs_logo_shadow_y: '1',
            certs_logo_shadow_blur: '3',
            certs_logo_shadow_color: '#000000',
            certs_logo_shadow_opacity: '5',
            certs_logo_hover_y: '-4',
            certs_logo_hover_scale: '1.02',
            certs_logo_hover_border_color: '#d1d5db',
            certs_logo_hover_shadow_y: '4',
            certs_logo_hover_shadow_blur: '12',
            certs_logo_hover_shadow_opacity: '10',
            certs_logo_transition: '250',
            certs_logo_object_fit: 'contain',
            certs_logo_max_image_height: '70',
            certs_logo_hover_image_scale: '1.05',
            certs_text_color: '#6b7280',
            certs_text_font_size: '11',
            certs_text_hover_color: '#374151'
        },
        bold: {
            // Partners
            partners_section_bg_color1: '#eff6ff',
            partners_section_bg_color2: '#dbeafe',
            partners_section_padding: '80',
            partners_title_color1: '#1e40af',
            partners_title_color2: '#3b82f6',
            partners_desc_color: '#1e3a8a',
            partners_logo_item_width: '200',
            partners_logo_item_height: '110',
            partners_logo_gap: '40',
            partners_logo_padding: '25',
            partners_logo_border_width: '3',
            partners_logo_border_style: 'solid',
            partners_logo_border_color: '#3b82f6',
            partners_logo_border_radius: '16',
            partners_logo_bg_color: '#ffffff',
            partners_logo_shadow_x: '0',
            partners_logo_shadow_y: '4',
            partners_logo_shadow_blur: '12',
            partners_logo_shadow_color: '#3b82f6',
            partners_logo_shadow_opacity: '25',
            partners_logo_hover_y: '-10',
            partners_logo_hover_scale: '1.05',
            partners_logo_hover_border_color: '#2563eb',
            partners_logo_hover_shadow_y: '10',
            partners_logo_hover_shadow_blur: '30',
            partners_logo_hover_shadow_opacity: '35',
            partners_logo_transition: '300',
            partners_logo_object_fit: 'contain',
            partners_logo_grayscale: '0',
            partners_logo_image_opacity: '100',
            partners_logo_hover_image_scale: '1.08',
            // Clients
            clients_section_bg_color1: '#f0fdf4',
            clients_section_bg_color2: '#dcfce7',
            clients_section_padding: '80',
            clients_title_color1: '#059669',
            clients_title_color2: '#10b981',
            clients_desc_color: '#047857',
            clients_logo_item_width: '200',
            clients_logo_item_height: '110',
            clients_logo_gap: '40',
            clients_logo_padding: '25',
            clients_logo_border_width: '3',
            clients_logo_border_style: 'solid',
            clients_logo_border_color: '#10b981',
            clients_logo_border_radius: '16',
            clients_logo_bg_color: '#ffffff',
            clients_logo_shadow_x: '0',
            clients_logo_shadow_y: '4',
            clients_logo_shadow_blur: '12',
            clients_logo_shadow_color: '#10b981',
            clients_logo_shadow_opacity: '25',
            clients_logo_hover_y: '-10',
            clients_logo_hover_scale: '1.05',
            clients_logo_hover_border_color: '#059669',
            clients_logo_hover_shadow_y: '10',
            clients_logo_hover_shadow_blur: '30',
            clients_logo_hover_shadow_opacity: '35',
            clients_logo_transition: '300',
            clients_logo_object_fit: 'contain',
            clients_logo_grayscale: '0',
            clients_logo_image_opacity: '100',
            clients_logo_hover_image_scale: '1.08',
            // Certifications
            certs_section_bg_color1: '#fef3c7',
            certs_section_bg_color2: '#fde68a',
            certs_section_padding: '70',
            certs_title_color: '#92400e',
            certs_desc_color: '#78350f',
            certs_logo_item_width: '180',
            certs_logo_item_height: '130',
            certs_logo_gap: '35',
            certs_logo_padding: '25',
            certs_logo_border_width: '2',
            certs_logo_border_style: 'solid',
            certs_logo_border_color: '#f59e0b',
            certs_logo_border_radius: '16',
            certs_logo_bg_color: '#ffffff',
            certs_logo_shadow_x: '0',
            certs_logo_shadow_y: '4',
            certs_logo_shadow_blur: '12',
            certs_logo_shadow_color: '#f59e0b',
            certs_logo_shadow_opacity: '20',
            certs_logo_hover_y: '-10',
            certs_logo_hover_scale: '1.06',
            certs_logo_hover_border_color: '#d97706',
            certs_logo_hover_shadow_y: '10',
            certs_logo_hover_shadow_blur: '30',
            certs_logo_hover_shadow_opacity: '30',
            certs_logo_transition: '300',
            certs_logo_object_fit: 'contain',
            certs_logo_max_image_height: '85',
            certs_logo_hover_image_scale: '1.12',
            certs_text_color: '#78350f',
            certs_text_font_size: '13',
            certs_text_hover_color: '#92400e'
        },
        elegant: {
            // Partners
            partners_section_bg_color1: '#fef3c7',
            partners_section_bg_color2: '#fde68a',
            partners_section_padding: '70',
            partners_title_color1: '#92400e',
            partners_title_color2: '#d97706',
            partners_desc_color: '#78350f',
            partners_logo_item_width: '180',
            partners_logo_item_height: '100',
            partners_logo_gap: '35',
            partners_logo_padding: '20',
            partners_logo_border_width: '1',
            partners_logo_border_style: 'solid',
            partners_logo_border_color: '#fbbf24',
            partners_logo_border_radius: '12',
            partners_logo_bg_color: '#ffffff',
            partners_logo_shadow_x: '0',
            partners_logo_shadow_y: '2',
            partners_logo_shadow_blur: '8',
            partners_logo_shadow_color: '#f59e0b',
            partners_logo_shadow_opacity: '15',
            partners_logo_hover_y: '-6',
            partners_logo_hover_scale: '1.03',
            partners_logo_hover_border_color: '#f59e0b',
            partners_logo_hover_shadow_y: '8',
            partners_logo_hover_shadow_blur: '20',
            partners_logo_hover_shadow_opacity: '25',
            partners_logo_transition: '350',
            partners_logo_object_fit: 'contain',
            partners_logo_grayscale: '20',
            partners_logo_image_opacity: '90',
            partners_logo_hover_image_scale: '1.06',
            // Clients
            clients_section_bg_color1: '#f0fdf4',
            clients_section_bg_color2: '#dcfce7',
            clients_section_padding: '70',
            clients_title_color1: '#047857',
            clients_title_color2: '#10b981',
            clients_desc_color: '#065f46',
            clients_logo_item_width: '180',
            clients_logo_item_height: '100',
            clients_logo_gap: '35',
            clients_logo_padding: '20',
            clients_logo_border_width: '1',
            clients_logo_border_style: 'solid',
            clients_logo_border_color: '#34d399',
            clients_logo_border_radius: '12',
            clients_logo_bg_color: '#ffffff',
            clients_logo_shadow_x: '0',
            clients_logo_shadow_y: '2',
            clients_logo_shadow_blur: '8',
            clients_logo_shadow_color: '#10b981',
            clients_logo_shadow_opacity: '15',
            clients_logo_hover_y: '-6',
            clients_logo_hover_scale: '1.03',
            clients_logo_hover_border_color: '#10b981',
            clients_logo_hover_shadow_y: '8',
            clients_logo_hover_shadow_blur: '20',
            clients_logo_hover_shadow_opacity: '25',
            clients_logo_transition: '350',
            clients_logo_object_fit: 'contain',
            clients_logo_grayscale: '20',
            clients_logo_image_opacity: '90',
            clients_logo_hover_image_scale: '1.06',
            // Certifications
            certs_section_bg_color1: '#faf5ff',
            certs_section_bg_color2: '#f3e8ff',
            certs_section_padding: '60',
            certs_title_color: '#6b21a8',
            certs_desc_color: '#7c3aed',
            certs_logo_item_width: '170',
            certs_logo_item_height: '120',
            certs_logo_gap: '30',
            certs_logo_padding: '20',
            certs_logo_border_width: '1',
            certs_logo_border_style: 'solid',
            certs_logo_border_color: '#c084fc',
            certs_logo_border_radius: '12',
            certs_logo_bg_color: '#ffffff',
            certs_logo_shadow_x: '0',
            certs_logo_shadow_y: '2',
            certs_logo_shadow_blur: '8',
            certs_logo_shadow_color: '#a855f7',
            certs_logo_shadow_opacity: '15',
            certs_logo_hover_y: '-6',
            certs_logo_hover_scale: '1.04',
            certs_logo_hover_border_color: '#a855f7',
            certs_logo_hover_shadow_y: '8',
            certs_logo_hover_shadow_blur: '20',
            certs_logo_hover_shadow_opacity: '25',
            certs_logo_transition: '350',
            certs_logo_object_fit: 'contain',
            certs_logo_max_image_height: '80',
            certs_logo_hover_image_scale: '1.08',
            certs_text_color: '#7c3aed',
            certs_text_font_size: '12',
            certs_text_hover_color: '#6b21a8'
        },
        colorful: {
            // Partners
            partners_section_bg_color1: '#fef3c7',
            partners_section_bg_color2: '#fde68a',
            partners_section_padding: '80',
            partners_title_color1: '#dc2626',
            partners_title_color2: '#ef4444',
            partners_desc_color: '#991b1b',
            partners_logo_item_width: '190',
            partners_logo_item_height: '105',
            partners_logo_gap: '35',
            partners_logo_padding: '22',
            partners_logo_border_width: '2',
            partners_logo_border_style: 'solid',
            partners_logo_border_color: '#f87171',
            partners_logo_border_radius: '14',
            partners_logo_bg_color: '#ffffff',
            partners_logo_shadow_x: '0',
            partners_logo_shadow_y: '3',
            partners_logo_shadow_blur: '10',
            partners_logo_shadow_color: '#f87171',
            partners_logo_shadow_opacity: '20',
            partners_logo_hover_y: '-8',
            partners_logo_hover_scale: '1.04',
            partners_logo_hover_border_color: '#ef4444',
            partners_logo_hover_shadow_y: '8',
            partners_logo_hover_shadow_blur: '25',
            partners_logo_hover_shadow_opacity: '30',
            partners_logo_transition: '300',
            partners_logo_object_fit: 'contain',
            partners_logo_grayscale: '0',
            partners_logo_image_opacity: '100',
            partners_logo_hover_image_scale: '1.07',
            // Clients
            clients_section_bg_color1: '#dbeafe',
            clients_section_bg_color2: '#bfdbfe',
            clients_section_padding: '80',
            clients_title_color1: '#1e40af',
            clients_title_color2: '#3b82f6',
            clients_desc_color: '#1e3a8a',
            clients_logo_item_width: '190',
            clients_logo_item_height: '105',
            clients_logo_gap: '35',
            clients_logo_padding: '22',
            clients_logo_border_width: '2',
            clients_logo_border_style: 'solid',
            clients_logo_border_color: '#60a5fa',
            clients_logo_border_radius: '14',
            clients_logo_bg_color: '#ffffff',
            clients_logo_shadow_x: '0',
            clients_logo_shadow_y: '3',
            clients_logo_shadow_blur: '10',
            clients_logo_shadow_color: '#60a5fa',
            clients_logo_shadow_opacity: '20',
            clients_logo_hover_y: '-8',
            clients_logo_hover_scale: '1.04',
            clients_logo_hover_border_color: '#3b82f6',
            clients_logo_hover_shadow_y: '8',
            clients_logo_hover_shadow_blur: '25',
            clients_logo_hover_shadow_opacity: '30',
            clients_logo_transition: '300',
            clients_logo_object_fit: 'contain',
            clients_logo_grayscale: '0',
            clients_logo_image_opacity: '100',
            clients_logo_hover_image_scale: '1.07',
            // Certifications
            certs_section_bg_color1: '#fce7f3',
            certs_section_bg_color2: '#fbcfe8',
            certs_section_padding: '70',
            certs_title_color: '#be185d',
            certs_desc_color: '#9f1239',
            certs_logo_item_width: '175',
            certs_logo_item_height: '125',
            certs_logo_gap: '32',
            certs_logo_padding: '22',
            certs_logo_border_width: '2',
            certs_logo_border_style: 'solid',
            certs_logo_border_color: '#f472b6',
            certs_logo_border_radius: '14',
            certs_logo_bg_color: '#ffffff',
            certs_logo_shadow_x: '0',
            certs_logo_shadow_y: '3',
            certs_logo_shadow_blur: '10',
            certs_logo_shadow_color: '#f472b6',
            certs_logo_shadow_opacity: '20',
            certs_logo_hover_y: '-8',
            certs_logo_hover_scale: '1.05',
            certs_logo_hover_border_color: '#ec4899',
            certs_logo_hover_shadow_y: '8',
            certs_logo_hover_shadow_blur: '25',
            certs_logo_hover_shadow_opacity: '30',
            certs_logo_transition: '300',
            certs_logo_object_fit: 'contain',
            certs_logo_max_image_height: '85',
            certs_logo_hover_image_scale: '1.1',
            certs_text_color: '#9f1239',
            certs_text_font_size: '13',
            certs_text_hover_color: '#be185d'
        },
        dark: {
            // Partners
            partners_section_bg_color1: '#1f2937',
            partners_section_bg_color2: '#111827',
            partners_section_padding: '70',
            partners_title_color1: '#f9fafb',
            partners_title_color2: '#e5e7eb',
            partners_desc_color: '#d1d5db',
            partners_logo_item_width: '180',
            partners_logo_item_height: '100',
            partners_logo_gap: '35',
            partners_logo_padding: '20',
            partners_logo_border_width: '1',
            partners_logo_border_style: 'solid',
            partners_logo_border_color: '#4b5563',
            partners_logo_border_radius: '10',
            partners_logo_bg_color: '#374151',
            partners_logo_shadow_x: '0',
            partners_logo_shadow_y: '2',
            partners_logo_shadow_blur: '8',
            partners_logo_shadow_color: '#000000',
            partners_logo_shadow_opacity: '40',
            partners_logo_hover_y: '-6',
            partners_logo_hover_scale: '1.03',
            partners_logo_hover_border_color: '#6b7280',
            partners_logo_hover_shadow_y: '8',
            partners_logo_hover_shadow_blur: '20',
            partners_logo_hover_shadow_opacity: '50',
            partners_logo_transition: '300',
            partners_logo_object_fit: 'contain',
            partners_logo_grayscale: '0',
            partners_logo_image_opacity: '100',
            partners_logo_hover_image_scale: '1.05',
            // Clients
            clients_section_bg_color1: '#1f2937',
            clients_section_bg_color2: '#111827',
            clients_section_padding: '70',
            clients_title_color1: '#f9fafb',
            clients_title_color2: '#e5e7eb',
            clients_desc_color: '#d1d5db',
            clients_logo_item_width: '180',
            clients_logo_item_height: '100',
            clients_logo_gap: '35',
            clients_logo_padding: '20',
            clients_logo_border_width: '1',
            clients_logo_border_style: 'solid',
            clients_logo_border_color: '#4b5563',
            clients_logo_border_radius: '10',
            clients_logo_bg_color: '#374151',
            clients_logo_shadow_x: '0',
            clients_logo_shadow_y: '2',
            clients_logo_shadow_blur: '8',
            clients_logo_shadow_color: '#000000',
            clients_logo_shadow_opacity: '40',
            clients_logo_hover_y: '-6',
            clients_logo_hover_scale: '1.03',
            clients_logo_hover_border_color: '#6b7280',
            clients_logo_hover_shadow_y: '8',
            clients_logo_hover_shadow_blur: '20',
            clients_logo_hover_shadow_opacity: '50',
            clients_logo_transition: '300',
            clients_logo_object_fit: 'contain',
            clients_logo_grayscale: '0',
            clients_logo_image_opacity: '100',
            clients_logo_hover_image_scale: '1.05',
            // Certifications
            certs_section_bg_color1: '#111827',
            certs_section_bg_color2: '#0f172a',
            certs_section_padding: '60',
            certs_title_color: '#f9fafb',
            certs_desc_color: '#d1d5db',
            certs_logo_item_width: '170',
            certs_logo_item_height: '120',
            certs_logo_gap: '30',
            certs_logo_padding: '20',
            certs_logo_border_width: '1',
            certs_logo_border_style: 'solid',
            certs_logo_border_color: '#4b5563',
            certs_logo_border_radius: '10',
            certs_logo_bg_color: '#374151',
            certs_logo_shadow_x: '0',
            certs_logo_shadow_y: '2',
            certs_logo_shadow_blur: '8',
            certs_logo_shadow_color: '#000000',
            certs_logo_shadow_opacity: '40',
            certs_logo_hover_y: '-6',
            certs_logo_hover_scale: '1.04',
            certs_logo_hover_border_color: '#6b7280',
            certs_logo_hover_shadow_y: '8',
            certs_logo_hover_shadow_blur: '20',
            certs_logo_hover_shadow_opacity: '50',
            certs_logo_transition: '300',
            certs_logo_object_fit: 'contain',
            certs_logo_max_image_height: '80',
            certs_logo_hover_image_scale: '1.08',
            certs_text_color: '#9ca3af',
            certs_text_font_size: '12',
            certs_text_hover_color: '#d1d5db'
        }
    };
    
    function applyPreset(presetName) {
        if (!stylePresets[presetName]) {
            alert('Preset not found!');
            return;
        }
        
        const preset = stylePresets[presetName];
        const presetNames = {
            minimal: 'Minimal Modern',
            bold: 'Bold Corporate',
            elegant: 'Elegant Classic',
            colorful: 'Colorful Vibrant',
            dark: 'Dark Professional'
        };
        
        if (!confirm(`Apply "${presetNames[presetName]}" preset? This will update all logo slider settings.`)) {
            return;
        }
        
        // Apply all preset values to form inputs
        Object.keys(preset).forEach(key => {
            const input = document.querySelector(`input[name="${key}"], select[name="${key}"]`);
            if (input) {
                if (input.type === 'color') {
                    input.value = preset[key];
                } else if (input.type === 'number') {
                    input.value = preset[key];
                } else if (input.tagName === 'SELECT') {
                    input.value = preset[key];
                    // Trigger change event for selects
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                } else {
                    input.value = preset[key];
                }
                // Trigger input event for real-time updates
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
        
        // Visual feedback
        const presetCards = document.querySelectorAll('.preset-card');
        presetCards.forEach(card => card.classList.remove('ring-2', 'ring-purple-500'));
        event.target.closest('.preset-card')?.classList.add('ring-2', 'ring-purple-500');
        
        // Show success message
        setTimeout(() => {
            alert(`"${presetNames[presetName]}" preset applied successfully! Don't forget to click "Save Settings" to save your changes.`);
        }, 100);
    }
    </script>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

