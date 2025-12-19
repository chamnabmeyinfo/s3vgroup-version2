<?php
/**
 * Setup Hero Slider Advanced Options
 * Adds new columns to hero_sliders table if they don't exist
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

$db = db();
$message = '';
$error = '';

// Check if columns exist
function columnExists($db, $table, $column) {
    try {
        $result = $db->fetchOne(
            "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE table_schema = DATABASE() 
             AND table_name = :table 
             AND column_name = :column",
            ['table' => $table, 'column' => $column]
        );
        return (int)$result['count'] > 0;
    } catch (\Exception $e) {
        return false;
    }
}

// Handle migration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_migration'])) {
    try {
        $columnsToAdd = [
            'video_url' => "ALTER TABLE hero_sliders ADD COLUMN video_url varchar(500) DEFAULT NULL AFTER image_mobile",
            'video_mobile_url' => "ALTER TABLE hero_sliders ADD COLUMN video_mobile_url varchar(500) DEFAULT NULL AFTER video_url",
            'autoplay_delay' => "ALTER TABLE hero_sliders ADD COLUMN autoplay_delay int(11) DEFAULT 5000 AFTER content_animation",
            'button_style_1' => "ALTER TABLE hero_sliders ADD COLUMN button_style_1 enum('primary','secondary','outline','ghost') DEFAULT 'primary' AFTER button_link_1",
            'button_style_2' => "ALTER TABLE hero_sliders ADD COLUMN button_style_2 enum('primary','secondary','outline','ghost') DEFAULT 'secondary' AFTER button_link_2",
        ];
        
        $added = [];
        foreach ($columnsToAdd as $column => $sql) {
            if (!columnExists($db, 'hero_sliders', $column)) {
                $db->query($sql);
                $added[] = $column;
            }
        }
        
        if (!empty($added)) {
            $message = 'Successfully added columns: ' . implode(', ', $added);
        } else {
            $message = 'All columns already exist. No changes needed.';
        }
    } catch (\Exception $e) {
        $error = 'Error running migration: ' . $e->getMessage();
    }
}

// Check which columns exist
$columns = [
    'video_url' => columnExists($db, 'hero_sliders', 'video_url'),
    'video_mobile_url' => columnExists($db, 'hero_sliders', 'video_mobile_url'),
    'autoplay_delay' => columnExists($db, 'hero_sliders', 'autoplay_delay'),
    'button_style_1' => columnExists($db, 'hero_sliders', 'button_style_1'),
    'button_style_2' => columnExists($db, 'hero_sliders', 'button_style_2'),
];

$allExist = array_reduce($columns, function($carry, $exists) {
    return $carry && $exists;
}, true);

$pageTitle = 'Setup Hero Slider Advanced Options';
include __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto p-4 md:p-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl shadow-xl p-4 md:p-6 lg:p-8 mb-4 md:mb-6 text-white">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold mb-1 md:mb-2">
                <i class="fas fa-database mr-2 md:mr-3"></i>
                Setup Hero Slider Advanced Options
            </h1>
            <p class="text-blue-100 text-sm md:text-lg">Add new columns to support video backgrounds and advanced features</p>
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

    <!-- Status -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
        <div class="p-4 md:p-6 lg:p-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Column Status</h2>
            <div class="space-y-3">
                <?php foreach ($columns as $column => $exists): ?>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-<?= $exists ? 'check-circle text-green-500' : 'times-circle text-red-500' ?> mr-3"></i>
                        <span class="font-medium text-gray-700"><?= escape($column) ?></span>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $exists ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                        <?= $exists ? 'Exists' : 'Missing' ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Migration Button -->
    <?php if (!$allExist): ?>
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-4 md:p-6 lg:p-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Run Migration</h2>
            <p class="text-gray-600 mb-6">Click the button below to add the missing columns to your database.</p>
            <form method="POST">
                <button type="submit" 
                        name="run_migration" 
                        class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-3 rounded-lg font-bold hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl">
                    <i class="fas fa-database mr-2"></i>Add Missing Columns
                </button>
            </form>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
        <div class="flex items-center">
            <i class="fas fa-check-circle text-green-500 mr-3 text-xl"></i>
            <div>
                <h3 class="font-bold text-green-800">All columns are set up!</h3>
                <p class="text-green-700 text-sm mt-1">You can now use all advanced hero slider features.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Back Button -->
    <div class="mt-6">
        <a href="<?= url('admin/hero-sliders.php') ?>" class="inline-block bg-gray-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-gray-700 transition-all">
            <i class="fas fa-arrow-left mr-2"></i>Back to Hero Sliders
        </a>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
