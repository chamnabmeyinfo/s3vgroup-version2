<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

use App\Models\HeroSlider;

$heroSliderModel = new HeroSlider();
$message = '';
$error = '';
$slide = null;
$slideId = $_GET['id'] ?? null;

if ($slideId) {
    $slide = $heroSliderModel->getById($slideId);
    if (!$slide) {
        header('Location: ' . url('admin/hero-slider.php'));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle background image upload
    if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../storage/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $file = $_FILES['background_image'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 10 * 1024 * 1024; // 10MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            $error = 'Invalid file type. Please upload JPG, PNG, GIF, or WebP.';
        } elseif ($file['size'] > $maxSize) {
            $error = 'File size exceeds 10MB limit.';
        } else {
            // Delete old image if exists
            if ($slideId && !empty($slide['background_image']) && file_exists(__DIR__ . '/../storage/uploads/' . basename($slide['background_image']))) {
                @unlink(__DIR__ . '/../storage/uploads/' . basename($slide['background_image']));
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'hero_slide_' . time() . '_' . uniqid() . '.' . $extension;
            $filepath = $uploadDir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $_POST['background_image'] = 'storage/uploads/' . $filename;
            } else {
                $error = 'Failed to upload image.';
            }
        }
    }
    
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'button1_text' => trim($_POST['button1_text'] ?? '') ?: null,
        'button1_url' => trim($_POST['button1_url'] ?? '') ?: null,
        'button2_text' => trim($_POST['button2_text'] ?? '') ?: null,
        'button2_url' => trim($_POST['button2_url'] ?? '') ?: null,
        'background_image' => $_POST['background_image'] ?? ($slide['background_image'] ?? null),
        'background_gradient_start' => trim($_POST['background_gradient_start'] ?? '') ?: null,
        'background_gradient_end' => trim($_POST['background_gradient_end'] ?? '') ?: null,
        'content_transparency' => isset($_POST['content_transparency']) ? (float)$_POST['content_transparency'] : 0.10,
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'display_order' => (int)($_POST['display_order'] ?? 0),
    ];
    
    if (empty($data['title'])) {
        $error = 'Title is required.';
    } else {
        try {
            if ($slideId) {
                $updated = $heroSliderModel->update($slideId, $data);
                if ($updated) {
                    $message = 'Slide updated successfully.';
                    $slide = $heroSliderModel->getById($slideId);
                } else {
                    $error = 'Failed to update slide.';
                }
            } else {
                $newId = $heroSliderModel->create($data);
                if ($newId) {
                    $message = 'Slide created successfully.';
                    header('Location: ' . url('admin/hero-slider.php'));
                    exit;
                } else {
                    $error = 'Failed to create slide.';
                }
            }
        } catch (Exception $e) {
            $error = 'Error saving slide: ' . $e->getMessage();
        }
    }
}

$pageTitle = $slideId ? 'Edit Hero Slide' : 'Add Hero Slide';
include __DIR__ . '/includes/header.php';
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold"><?= $slideId ? 'Edit Hero Slide' : 'Add New Hero Slide' ?></h1>
        <a href="hero-slider.php" class="btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i> Back to Slides
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
    
    <form method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-6">
        <div class="grid md:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="<?= escape($slide['title'] ?? '') ?>"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="4"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?= escape($slide['description'] ?? '') ?></textarea>
                </div>
                
                <div>
                    <label for="display_order" class="block text-sm font-medium text-gray-700 mb-2">
                        Display Order
                    </label>
                    <input type="number" 
                           id="display_order" 
                           name="display_order" 
                           value="<?= escape($slide['display_order'] ?? 0) ?>"
                           min="0"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Lower numbers appear first</p>
                </div>
                
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="is_active" 
                               value="1"
                               <?= (!isset($slide) || $slide['is_active']) ? 'checked' : '' ?>
                               class="mr-2">
                        <span class="text-sm font-medium text-gray-700">Active</span>
                    </label>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="space-y-6">
                <div>
                    <label for="background_image" class="block text-sm font-medium text-gray-700 mb-2">
                        Background Image
                    </label>
                    <input type="file" 
                           id="background_image" 
                           name="background_image" 
                           accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <?php if (!empty($slide['background_image'])): ?>
                        <div class="mt-2">
                            <img src="<?= url($slide['background_image']) ?>" 
                                 alt="Current image" 
                                 class="max-w-full h-32 object-cover rounded-lg border border-gray-300">
                            <p class="text-xs text-gray-500 mt-1">Current image</p>
                        </div>
                    <?php endif; ?>
                    <p class="text-xs text-gray-500 mt-1">Optional. If not provided, gradient will be used.</p>
                </div>
                
                <div>
                    <label for="background_gradient_start" class="block text-sm font-medium text-gray-700 mb-2">
                        Gradient Start Color
                    </label>
                    <input type="text" 
                           id="background_gradient_start" 
                           name="background_gradient_start" 
                           value="<?= escape($slide['background_gradient_start'] ?? 'rgba(37, 99, 235, 0.9)') ?>"
                           placeholder="rgba(37, 99, 235, 0.9)"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">CSS color value (e.g., rgba(37, 99, 235, 0.9))</p>
                </div>
                
                <div>
                    <label for="background_gradient_end" class="block text-sm font-medium text-gray-700 mb-2">
                        Gradient End Color
                    </label>
                    <input type="text" 
                           id="background_gradient_end" 
                           name="background_gradient_end" 
                           value="<?= escape($slide['background_gradient_end'] ?? 'rgba(79, 70, 229, 0.9)') ?>"
                           placeholder="rgba(79, 70, 229, 0.9)"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">CSS color value (e.g., rgba(79, 70, 229, 0.9))</p>
                </div>
                
                <div>
                    <label for="content_transparency" class="block text-sm font-medium text-gray-700 mb-2">
                        Content Transparency (Glass Effect)
                    </label>
                    <div class="space-y-2">
                        <input type="range" 
                               id="content_transparency" 
                               name="content_transparency" 
                               min="0" 
                               max="1" 
                               step="0.01"
                               value="<?= escape($slide['content_transparency'] ?? 0.10) ?>"
                               oninput="updateTransparencyDisplay(this.value)"
                               class="w-full">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-500">0% (Fully Transparent)</span>
                            <span id="transparency_value" class="text-sm font-semibold text-blue-600">
                                <?= number_format((float)($slide['content_transparency'] ?? 0.10) * 100, 0) ?>%
                            </span>
                            <span class="text-xs text-gray-500">100% (Fully Opaque)</span>
                        </div>
                        <p class="text-xs text-gray-500">Adjust the transparency of the glass effect overlay on the slide content</p>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        function updateTransparencyDisplay(value) {
            document.getElementById('transparency_value').textContent = Math.round(value * 100) + '%';
        }
        </script>
        
        <!-- Buttons Section -->
        <div class="mt-8 border-t border-gray-200 pt-6">
            <h3 class="text-lg font-semibold mb-4">Call-to-Action Buttons</h3>
            <div class="grid md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h4 class="font-medium text-gray-700">Button 1 (Primary)</h4>
                    <div>
                        <label for="button1_text" class="block text-sm font-medium text-gray-700 mb-2">
                            Button Text
                        </label>
                        <input type="text" 
                               id="button1_text" 
                               name="button1_text" 
                               value="<?= escape($slide['button1_text'] ?? '') ?>"
                               placeholder="Shop Now"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="button1_url" class="block text-sm font-medium text-gray-700 mb-2">
                            Button URL
                        </label>
                        <input type="text" 
                               id="button1_url" 
                               name="button1_url" 
                               value="<?= escape($slide['button1_url'] ?? '') ?>"
                               placeholder="products.php"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
                
                <div class="space-y-4">
                    <h4 class="font-medium text-gray-700">Button 2 (Secondary)</h4>
                    <div>
                        <label for="button2_text" class="block text-sm font-medium text-gray-700 mb-2">
                            Button Text
                        </label>
                        <input type="text" 
                               id="button2_text" 
                               name="button2_text" 
                               value="<?= escape($slide['button2_text'] ?? '') ?>"
                               placeholder="Get Quote"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label for="button2_url" class="block text-sm font-medium text-gray-700 mb-2">
                            Button URL
                        </label>
                        <input type="text" 
                               id="button2_url" 
                               name="button2_url" 
                               value="<?= escape($slide['button2_url'] ?? '') ?>"
                               placeholder="quote.php"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-8 flex justify-end gap-4">
            <a href="hero-slider.php" class="btn-secondary">
                Cancel
            </a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-save mr-2"></i> <?= $slideId ? 'Update Slide' : 'Create Slide' ?>
            </button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

