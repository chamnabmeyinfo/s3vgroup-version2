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
        'mission_title' => trim($_POST['mission_title'] ?? 'Our Mission'),
        'mission_content' => trim($_POST['mission_content'] ?? ''),
        'mission_icon' => trim($_POST['mission_icon'] ?? 'fa-bullseye'),
        'mission_enabled' => isset($_POST['mission_enabled']) ? 1 : 0,
        'vision_title' => trim($_POST['vision_title'] ?? 'Our Vision'),
        'vision_content' => trim($_POST['vision_content'] ?? ''),
        'vision_icon' => trim($_POST['vision_icon'] ?? 'fa-eye'),
        'vision_enabled' => isset($_POST['vision_enabled']) ? 1 : 0,
        'mission_vision_section_enabled' => isset($_POST['mission_vision_section_enabled']) ? 1 : 0,
        'mission_vision_bg_color1' => trim($_POST['mission_vision_bg_color1'] ?? '#ffffff'),
        'mission_vision_bg_color2' => trim($_POST['mission_vision_bg_color2'] ?? '#f8f9fa'),
        'mission_vision_padding' => (int)($_POST['mission_vision_padding'] ?? 80),
        'mission_vision_title_color' => trim($_POST['mission_vision_title_color'] ?? '#1a1a1a'),
        'mission_vision_text_color' => trim($_POST['mission_vision_text_color'] ?? '#475569'),
        'mission_vision_icon_bg_color1' => trim($_POST['mission_vision_icon_bg_color1'] ?? '#3b82f6'),
        'mission_vision_icon_bg_color2' => trim($_POST['mission_vision_icon_bg_color2'] ?? '#2563eb'),
        'vision_icon_bg_color1' => trim($_POST['vision_icon_bg_color1'] ?? '#8b5cf6'),
        'vision_icon_bg_color2' => trim($_POST['vision_icon_bg_color2'] ?? '#7c3aed'),
    ];
    
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
        
        $message = 'Mission & Vision updated successfully.';
    } catch (Exception $e) {
        $error = 'Error updating Mission & Vision: ' . $e->getMessage();
    }
}

// Get current settings
$settingsData = db()->fetchAll("SELECT `key`, value FROM settings WHERE `key` LIKE 'mission_%' OR `key` LIKE 'vision_%'");
$settings = [];
foreach ($settingsData as $setting) {
    $settings[$setting['key']] = $setting['value'];
}

// Default values
$defaults = [
    'mission_title' => 'Our Mission',
    'mission_content' => 'To provide exceptional forklift and industrial equipment solutions that empower businesses to achieve their operational goals. We are committed to delivering quality products, outstanding service, and innovative solutions that drive productivity and success.',
    'mission_icon' => 'fa-bullseye',
    'mission_enabled' => 1,
    'vision_title' => 'Our Vision',
    'vision_content' => 'To become the most trusted partner in the industrial equipment industry, recognized for excellence, innovation, and customer satisfaction. We envision a future where every business has access to the best equipment solutions tailored to their unique needs.',
    'vision_icon' => 'fa-eye',
    'vision_enabled' => 1,
    'mission_vision_section_enabled' => 1,
    'mission_vision_bg_color1' => '#ffffff',
    'mission_vision_bg_color2' => '#f8f9fa',
    'mission_vision_padding' => 80,
    'mission_vision_title_color' => '#1a1a1a',
    'mission_vision_text_color' => '#475569',
    'mission_vision_icon_bg_color1' => '#3b82f6',
    'mission_vision_icon_bg_color2' => '#2563eb',
    'vision_icon_bg_color1' => '#8b5cf6',
    'vision_icon_bg_color2' => '#7c3aed',
];

foreach ($defaults as $key => $default) {
    if (!isset($settings[$key])) {
        $settings[$key] = $default;
    }
}

$pageTitle = 'Mission & Vision';
include __DIR__ . '/includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-bullseye text-blue-600 mr-3"></i>
            Mission & Vision
        </h1>
        <p class="text-gray-600 mt-2">Manage your company's Mission and Vision statements displayed on the homepage.</p>
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

    <form method="POST" class="bg-white rounded-xl shadow-lg p-6">
        <?= csrf_field() ?>
        
        <!-- Section Enable/Disable -->
        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Show Mission & Vision Section</label>
                    <p class="text-xs text-gray-500">Enable or disable the Mission & Vision section on the homepage</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="mission_vision_section_enabled" value="1" <?= ($settings['mission_vision_section_enabled'] ?? 1) ? 'checked' : '' ?> class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Mission Card -->
            <div class="border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-bullseye text-blue-600 mr-2"></i>
                        Mission
                    </h2>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="mission_enabled" value="1" <?= ($settings['mission_enabled'] ?? 1) ? 'checked' : '' ?> class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mission Title</label>
                        <input type="text" name="mission_title" value="<?= escape($settings['mission_title'] ?? 'Our Mission') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mission Content</label>
                        <textarea name="mission_content" rows="6" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?= escape($settings['mission_content'] ?? '') ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mission Icon (Font Awesome class)</label>
                        <input type="text" name="mission_icon" value="<?= escape($settings['mission_icon'] ?? 'fa-bullseye') ?>" placeholder="fa-bullseye" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Example: fa-bullseye, fa-target, fa-flag</p>
                    </div>
                </div>
            </div>

            <!-- Vision Card -->
            <div class="border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-eye text-purple-600 mr-2"></i>
                        Vision
                    </h2>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="vision_enabled" value="1" <?= ($settings['vision_enabled'] ?? 1) ? 'checked' : '' ?> class="sr-only peer">
                        <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Vision Title</label>
                        <input type="text" name="vision_title" value="<?= escape($settings['vision_title'] ?? 'Our Vision') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Vision Content</label>
                        <textarea name="vision_content" rows="6" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?= escape($settings['vision_content'] ?? '') ?></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Vision Icon (Font Awesome class)</label>
                        <input type="text" name="vision_icon" value="<?= escape($settings['vision_icon'] ?? 'fa-eye') ?>" placeholder="fa-eye" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Example: fa-eye, fa-lightbulb, fa-rocket</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Styling Settings -->
        <div class="mt-6 border-t border-gray-200 pt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-palette text-blue-600 mr-2"></i>
                Styling Settings
            </h3>
            
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Background Color 1</label>
                    <input type="color" name="mission_vision_bg_color1" value="<?= escape($settings['mission_vision_bg_color1'] ?? '#ffffff') ?>" class="w-full h-10 border border-gray-300 rounded">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Background Color 2</label>
                    <input type="color" name="mission_vision_bg_color2" value="<?= escape($settings['mission_vision_bg_color2'] ?? '#f8f9fa') ?>" class="w-full h-10 border border-gray-300 rounded">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Section Padding</label>
                    <input type="number" name="mission_vision_padding" value="<?= escape($settings['mission_vision_padding'] ?? 80) ?>" min="20" max="200" step="10" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Title Color</label>
                    <input type="color" name="mission_vision_title_color" value="<?= escape($settings['mission_vision_title_color'] ?? '#1a1a1a') ?>" class="w-full h-10 border border-gray-300 rounded">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Text Color</label>
                    <input type="color" name="mission_vision_text_color" value="<?= escape($settings['mission_vision_text_color'] ?? '#475569') ?>" class="w-full h-10 border border-gray-300 rounded">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mission Icon BG Color 1</label>
                    <input type="color" name="mission_vision_icon_bg_color1" value="<?= escape($settings['mission_vision_icon_bg_color1'] ?? '#3b82f6') ?>" class="w-full h-10 border border-gray-300 rounded">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mission Icon BG Color 2</label>
                    <input type="color" name="mission_vision_icon_bg_color2" value="<?= escape($settings['mission_vision_icon_bg_color2'] ?? '#2563eb') ?>" class="w-full h-10 border border-gray-300 rounded">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Vision Icon BG Color 1</label>
                    <input type="color" name="vision_icon_bg_color1" value="<?= escape($settings['vision_icon_bg_color1'] ?? '#8b5cf6') ?>" class="w-full h-10 border border-gray-300 rounded">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Vision Icon BG Color 2</label>
                    <input type="color" name="vision_icon_bg_color2" value="<?= escape($settings['vision_icon_bg_color2'] ?? '#7c3aed') ?>" class="w-full h-10 border border-gray-300 rounded">
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                <i class="fas fa-save mr-2"></i>Save Mission & Vision
            </button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
