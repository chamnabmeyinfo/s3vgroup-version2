<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

use App\Services\ColorExtractor;

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    <form method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-lg p-4 md:p-6 lg:p-8 space-y-4 md:space-y-6">
        <!-- Logo Upload Section -->
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 border-2 border-blue-200">
            <label class="block text-sm font-semibold text-gray-700 mb-4">
                <i class="fas fa-image text-blue-600 mr-2"></i> Company Logo
            </label>
            
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
                    <i class="fas fa-envelope text-gray-400 mr-2"></i> Site Email
                </label>
                <input type="email" name="site_email" value="<?= escape($settings['site_email']) ?>"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-all">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-phone text-gray-400 mr-2"></i> Site Phone
                </label>
                <input type="text" name="site_phone" value="<?= escape($settings['site_phone']) ?>"
                       class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-all">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i> Site Address
                </label>
                <textarea name="site_address" rows="3" 
                          class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-all"><?= escape($settings['site_address']) ?></textarea>
            </div>
        </div>
    
            <div class="col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-file-alt text-gray-400 mr-2"></i> Footer Text
                </label>
                <textarea name="footer_text" rows="2" 
                          class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-all"><?= escape($settings['footer_text']) ?></textarea>
            </div>
        </div>
        
        <div class="pt-4 border-t border-gray-200">
            <button type="submit" name="submit" class="bg-gradient-to-r from-gray-700 to-gray-900 text-white px-8 py-3 rounded-lg font-bold text-lg hover:from-gray-800 hover:to-gray-950 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                <i class="fas fa-save mr-2"></i>
                Save Settings
            </button>
        </div>
    </form>

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
    </script>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

