<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

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
    'footer_text' => '© 2024 Forklift & Equipment Pro. All rights reserved.'
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

    <?php if ($message): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2 text-xl"></i>
            <span class="font-semibold"><?= escape($message) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
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
                        Recommended: PNG or SVG with transparent background. Max size: 5MB
                    </p>
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
                    container.innerHTML = '<img src="' + e.target.result + '" alt="Logo Preview" class="max-w-full max-h-full object-contain" id="logo-preview">';
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

