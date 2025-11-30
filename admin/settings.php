<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        if ($key !== 'submit') {
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
    
    $message = 'Settings updated successfully.';
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

<h1 class="text-3xl font-bold mb-6">Site Settings</h1>

<form method="POST" class="bg-white rounded-lg shadow p-6 space-y-6 max-w-2xl">
    <div>
        <label class="block text-sm font-medium mb-2">Site Name</label>
        <input type="text" name="site_name" value="<?= escape($settings['site_name']) ?>"
               class="w-full px-4 py-2 border rounded-lg">
    </div>
    
    <div>
        <label class="block text-sm font-medium mb-2">Site Email</label>
        <input type="email" name="site_email" value="<?= escape($settings['site_email']) ?>"
               class="w-full px-4 py-2 border rounded-lg">
    </div>
    
    <div>
        <label class="block text-sm font-medium mb-2">Site Phone</label>
        <input type="text" name="site_phone" value="<?= escape($settings['site_phone']) ?>"
               class="w-full px-4 py-2 border rounded-lg">
    </div>
    
    <div>
        <label class="block text-sm font-medium mb-2">Site Address</label>
        <textarea name="site_address" rows="3" class="w-full px-4 py-2 border rounded-lg"><?= escape($settings['site_address']) ?></textarea>
    </div>
    
    <div>
        <label class="block text-sm font-medium mb-2">Footer Text</label>
        <textarea name="footer_text" rows="2" class="w-full px-4 py-2 border rounded-lg"><?= escape($settings['footer_text']) ?></textarea>
    </div>
    
    <div class="flex space-x-4">
        <button type="submit" name="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
            Save Settings
        </button>
    </div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>

