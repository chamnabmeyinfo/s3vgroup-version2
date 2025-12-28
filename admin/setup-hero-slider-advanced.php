<?php
/**
 * Advanced Hero Slider Features Setup
 * Run this to add all advanced features to existing hero_slides table
 */

require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Setup Advanced Hero Slider Features';
include __DIR__ . '/includes/header.php';

$message = '';
$error = '';
$success = false;

// Check if table exists
$tableExists = false;
try {
    db()->fetchOne("SELECT 1 FROM hero_slides LIMIT 1");
    $tableExists = true;
} catch (Exception $e) {
    $tableExists = false;
}

if (!$tableExists) {
    $error = 'Hero slides table does not exist. Please run the basic setup first.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_features'])) {
    try {
        // Read and execute the migration SQL
        $sqlFile = __DIR__ . '/../database/add-hero-slider-advanced-features.sql';
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            // Split by semicolon and execute each statement
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            foreach ($statements as $statement) {
                if (!empty($statement) && !preg_match('/^--/', $statement)) {
                    try {
                        db()->query($statement);
                    } catch (Exception $e) {
                        // Ignore errors for columns that already exist
                        if (strpos($e->getMessage(), 'Duplicate column') === false) {
                            throw $e;
                        }
                    }
                }
            }
            
            $message = 'Advanced features added successfully! All new fields are now available.';
            $success = true;
        } else {
            $error = 'Migration file not found.';
        }
    } catch (Exception $e) {
        $error = 'Error adding features: ' . $e->getMessage();
    }
}

// Check which columns exist
$existingColumns = [];
if ($tableExists) {
    try {
        $columns = db()->fetchAll("SHOW COLUMNS FROM hero_slides");
        $existingColumns = array_column($columns, 'Field');
    } catch (Exception $e) {
        // Ignore
    }
}

$newColumns = [
    'transition_effect', 'video_background', 'video_poster', 'template',
    'image_mobile', 'image_tablet', 'scheduled_start', 'scheduled_end',
    'text_animation', 'parallax_enabled', 'content_layout', 'overlay_pattern',
    'button1_style', 'button2_style', 'social_sharing', 'countdown_enabled',
    'countdown_date', 'badge_text', 'badge_color', 'mobile_title',
    'mobile_description', 'custom_font', 'slide_group', 'ab_test_variant',
    'auto_height', 'dark_mode'
];

$missingColumns = array_diff($newColumns, $existingColumns);
?>

<div class="p-6">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Advanced Hero Slider Features Setup</h1>
        
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
        
        <?php if ($tableExists): ?>
            <?php if (empty($missingColumns)): ?>
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
                    <i class="fas fa-check-circle mr-2"></i>
                    All advanced features are already installed! You can now use all the new options.
                </div>
            <?php else: ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Add Advanced Features</h2>
                    <p class="text-gray-600 mb-4">
                        The following features will be added to your hero slider:
                    </p>
                    <ul class="list-disc list-inside text-gray-700 mb-6 space-y-2">
                        <li>Multiple transition effects (fade, slide, zoom, cube, flip, etc.)</li>
                        <li>Video background support</li>
                        <li>Slide templates</li>
                        <li>Responsive images (mobile/tablet/desktop)</li>
                        <li>Scheduled slides (date/time)</li>
                        <li>Text animations</li>
                        <li>Parallax scrolling</li>
                        <li>Multiple content layouts</li>
                        <li>Overlay patterns</li>
                        <li>Button style options</li>
                        <li>Social sharing</li>
                        <li>Countdown timers</li>
                        <li>Badges/labels</li>
                        <li>Mobile-specific content</li>
                        <li>Custom fonts</li>
                        <li>Slide groups</li>
                        <li>A/B testing</li>
                        <li>Auto-height adjustment</li>
                        <li>Dark mode support</li>
                    </ul>
                    
                    <p class="text-sm text-gray-500 mb-6">
                        Missing columns: <?= count($missingColumns) ?>
                    </p>
                    
                    <form method="POST">
                        <button type="submit" name="add_features" class="btn-primary">
                            <i class="fas fa-magic mr-2"></i> Add Advanced Features
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                Please run the basic hero slider setup first.
            </div>
        <?php endif; ?>
        
        <div class="mt-6">
            <a href="hero-slider.php" class="btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i> Back to Hero Slider
            </a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

