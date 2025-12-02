<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

$db = db();
$message = '';
$error = '';
$slider = null;
$isEdit = false;

// Get slider if editing
if (!empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $slider = $db->fetchOne("SELECT * FROM hero_sliders WHERE id = :id", ['id' => $id]);
    if ($slider) {
        $isEdit = true;
    } else {
        $error = 'Slider not found.';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'subtitle' => trim($_POST['subtitle'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'image' => trim($_POST['image'] ?? ''),
            'image_mobile' => trim($_POST['image_mobile'] ?? ''),
            'button_text_1' => trim($_POST['button_text_1'] ?? ''),
            'button_link_1' => trim($_POST['button_link_1'] ?? ''),
            'button_text_2' => trim($_POST['button_text_2'] ?? ''),
            'button_link_2' => trim($_POST['button_link_2'] ?? ''),
            'overlay_color' => trim($_POST['overlay_color'] ?? 'rgba(30, 58, 138, 0.9)'),
            'overlay_gradient' => trim($_POST['overlay_gradient'] ?? ''),
            'text_alignment' => $_POST['text_alignment'] ?? 'center',
            'text_color' => trim($_POST['text_color'] ?? '#ffffff'),
            'background_size' => $_POST['background_size'] ?? 'cover',
            'background_position' => trim($_POST['background_position'] ?? 'center'),
            'parallax_effect' => isset($_POST['parallax_effect']) ? 1 : 0,
            'animation_speed' => $_POST['animation_speed'] ?? 'normal',
            'slide_height' => $_POST['slide_height'] ?? 'auto',
            'custom_height' => trim($_POST['custom_height'] ?? ''),
            'content_animation' => $_POST['content_animation'] ?? 'fade',
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];
        
        // Validate
        if (empty($data['title'])) {
            throw new Exception('Title is required.');
        }
        
        // Handle image upload
        if (!empty($_FILES['image_file']['name'])) {
            $uploadDir = __DIR__ . '/../storage/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExt = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (!in_array($fileExt, $allowedExts)) {
                throw new Exception('Invalid image format. Allowed: ' . implode(', ', $allowedExts));
            }
            
            $fileName = 'hero_' . time() . '_' . rand(1000, 9999) . '.' . $fileExt;
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $filePath)) {
                $data['image'] = 'storage/uploads/' . $fileName;
            } else {
                throw new Exception('Failed to upload image.');
            }
        }
        
        if (!empty($_FILES['image_mobile_file']['name'])) {
            $uploadDir = __DIR__ . '/../storage/uploads/';
            $fileExt = strtolower(pathinfo($_FILES['image_mobile_file']['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array($fileExt, $allowedExts)) {
                $fileName = 'hero_mobile_' . time() . '_' . rand(1000, 9999) . '.' . $fileExt;
                $filePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['image_mobile_file']['tmp_name'], $filePath)) {
                    $data['image_mobile'] = 'storage/uploads/' . $fileName;
                }
            }
        }
        
        if ($isEdit && $slider) {
            // Update existing
            if (empty($data['image']) && !empty($slider['image'])) {
                $data['image'] = $slider['image'];
            }
            if (empty($data['image_mobile']) && !empty($slider['image_mobile'])) {
                $data['image_mobile'] = $slider['image_mobile'];
            }
            
            $db->update('hero_sliders', $data, 'id = :id', ['id' => $slider['id']]);
            $message = 'Hero slider updated successfully.';
        } else {
            // Insert new
            $db->insert('hero_sliders', $data);
            $message = 'Hero slider created successfully.';
            header('Location: ' . url('admin/hero-sliders.php'));
            exit;
        }
        
        // Reload slider data
        if ($isEdit) {
            $slider = $db->fetchOne("SELECT * FROM hero_sliders WHERE id = :id", ['id' => $slider['id']]);
        }
        
    } catch (\Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

$pageTitle = $isEdit ? 'Edit Hero Slider' : 'Add Hero Slider';
include __DIR__ . '/includes/header.php';
?>

<div class="max-w-5xl mx-auto p-4 md:p-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl shadow-xl p-4 md:p-6 lg:p-8 mb-4 md:mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-1 md:mb-2">
                    <i class="fas fa-<?= $isEdit ? 'edit' : 'plus' ?> mr-2 md:mr-3"></i>
                    <?= $isEdit ? 'Edit Hero Slider' : 'Add Hero Slider' ?>
                </h1>
                <p class="text-blue-100 text-sm md:text-lg"><?= $isEdit ? 'Update slider information' : 'Create a new hero slider' ?></p>
            </div>
            <a href="<?= url('admin/hero-sliders.php') ?>" class="bg-white text-blue-600 px-4 md:px-6 py-2 md:py-3 rounded-lg font-bold hover:bg-blue-50 transition-all">
                <i class="fas fa-arrow-left mr-2"></i>Back
            </a>
        </div>
    </div>

    <?php if ($message): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-4 md:mb-6">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2 text-xl"></i>
            <span class="font-semibold"><?= escape($message) ?></span>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-4 md:mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2 text-xl"></i>
            <span class="font-semibold"><?= escape($error) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <form method="POST" enctype="multipart/form-data" class="p-4 md:p-6 lg:p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mb-4 md:mb-6">
                <div class="md:col-span-2">
                    <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-heading text-blue-600 mr-1"></i>Title *
                    </label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           value="<?= escape($slider['title'] ?? '') ?>" 
                           required
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>
                
                <div class="md:col-span-2">
                    <label for="subtitle" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-subscript text-blue-600 mr-1"></i>Subtitle
                    </label>
                    <input type="text" 
                           id="subtitle" 
                           name="subtitle" 
                           value="<?= escape($slider['subtitle'] ?? '') ?>" 
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>
                
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-align-left text-blue-600 mr-1"></i>Description
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="3"
                              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"><?= escape($slider['description'] ?? '') ?></textarea>
                </div>
                
                <div>
                    <label for="image" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-image text-blue-600 mr-1"></i>Image URL (Desktop)
                    </label>
                    <input type="text" 
                           id="image" 
                           name="image" 
                           value="<?= escape($slider['image'] ?? '') ?>" 
                           placeholder="https://example.com/image.jpg"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <p class="text-xs text-gray-500 mt-1">Or upload an image below</p>
                    
                    <?php if (!empty($slider['image'])): ?>
                        <div class="mt-3">
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Current Image Preview:</label>
                            <img src="<?= escape(image_url($slider['image'])) ?>" 
                                 alt="Preview" 
                                 class="max-w-full h-48 object-cover rounded-lg border-2 border-gray-300"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div style="display: none;" class="p-4 bg-gray-100 rounded-lg text-center text-gray-500">
                                <i class="fas fa-exclamation-triangle mr-2"></i>Image not found
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label for="image_file" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-upload text-blue-600 mr-1"></i>Upload Image (Desktop)
                    </label>
                    <input type="file" 
                           id="image_file" 
                           name="image_file" 
                           accept="image/*"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                           onchange="previewImage(this, 'desktop-preview')">
                    <div id="desktop-preview" class="mt-3 hidden">
                        <label class="block text-xs font-semibold text-gray-700 mb-2">Upload Preview:</label>
                        <img id="desktop-preview-img" src="" alt="Preview" class="max-w-full h-48 object-cover rounded-lg border-2 border-gray-300">
                    </div>
                </div>
                
                <div>
                    <label for="image_mobile" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-mobile-alt text-blue-600 mr-1"></i>Image URL (Mobile)
                    </label>
                    <input type="text" 
                           id="image_mobile" 
                           name="image_mobile" 
                           value="<?= escape($slider['image_mobile'] ?? '') ?>" 
                           placeholder="https://example.com/image-mobile.jpg"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    
                    <?php if (!empty($slider['image_mobile'])): ?>
                        <div class="mt-3">
                            <label class="block text-xs font-semibold text-gray-700 mb-2">Current Mobile Image Preview:</label>
                            <img src="<?= escape(image_url($slider['image_mobile'])) ?>" 
                                 alt="Mobile Preview" 
                                 class="max-w-full h-48 object-cover rounded-lg border-2 border-gray-300"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div style="display: none;" class="p-4 bg-gray-100 rounded-lg text-center text-gray-500">
                                <i class="fas fa-exclamation-triangle mr-2"></i>Image not found
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label for="image_mobile_file" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-upload text-blue-600 mr-1"></i>Upload Image (Mobile)
                    </label>
                    <input type="file" 
                           id="image_mobile_file" 
                           name="image_mobile_file" 
                           accept="image/*"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                           onchange="previewImage(this, 'mobile-preview')">
                    <div id="mobile-preview" class="mt-3 hidden">
                        <label class="block text-xs font-semibold text-gray-700 mb-2">Upload Preview:</label>
                        <img id="mobile-preview-img" src="" alt="Mobile Preview" class="max-w-full h-48 object-cover rounded-lg border-2 border-gray-300">
                    </div>
                </div>
                
                <div>
                    <label for="button_text_1" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-hand-pointer text-blue-600 mr-1"></i>Button 1 Text
                    </label>
                    <input type="text" 
                           id="button_text_1" 
                           name="button_text_1" 
                           value="<?= escape($slider['button_text_1'] ?? '') ?>" 
                           placeholder="Browse Products"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>
                
                <div>
                    <label for="button_link_1" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-link text-blue-600 mr-1"></i>Button 1 Link
                    </label>
                    <input type="text" 
                           id="button_link_1" 
                           name="button_link_1" 
                           value="<?= escape($slider['button_link_1'] ?? '') ?>" 
                           placeholder="/products.php"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>
                
                <div>
                    <label for="button_text_2" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-hand-pointer text-blue-600 mr-1"></i>Button 2 Text
                    </label>
                    <input type="text" 
                           id="button_text_2" 
                           name="button_text_2" 
                           value="<?= escape($slider['button_text_2'] ?? '') ?>" 
                           placeholder="Get a Quote"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>
                
                <div>
                    <label for="button_link_2" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-link text-blue-600 mr-1"></i>Button 2 Link
                    </label>
                    <input type="text" 
                           id="button_link_2" 
                           name="button_link_2" 
                           value="<?= escape($slider['button_link_2'] ?? '') ?>" 
                           placeholder="/quote.php"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>
                
                <div>
                    <label for="text_alignment" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-align-center text-blue-600 mr-1"></i>Text Alignment
                    </label>
                    <select id="text_alignment" 
                            name="text_alignment" 
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="left" <?= ($slider['text_alignment'] ?? 'center') === 'left' ? 'selected' : '' ?>>Left</option>
                        <option value="center" <?= ($slider['text_alignment'] ?? 'center') === 'center' ? 'selected' : '' ?>>Center</option>
                        <option value="right" <?= ($slider['text_alignment'] ?? 'center') === 'right' ? 'selected' : '' ?>>Right</option>
                    </select>
                </div>
                
                <div>
                    <label for="sort_order" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-sort text-blue-600 mr-1"></i>Sort Order
                    </label>
                    <input type="number" 
                           id="sort_order" 
                           name="sort_order" 
                           value="<?= escape($slider['sort_order'] ?? 0) ?>" 
                           min="0"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>
                
                <div>
                    <label for="overlay_color" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-palette text-blue-600 mr-1"></i>Overlay Color
                    </label>
                    <input type="text" 
                           id="overlay_color" 
                           name="overlay_color" 
                           value="<?= escape($slider['overlay_color'] ?? 'rgba(30, 58, 138, 0.9)') ?>" 
                           placeholder="rgba(30, 58, 138, 0.9)"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>
                
                <div>
                    <label for="text_color" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-font text-blue-600 mr-1"></i>Text Color
                    </label>
                    <input type="text" 
                           id="text_color" 
                           name="text_color" 
                           value="<?= escape($slider['text_color'] ?? '#ffffff') ?>" 
                           placeholder="#ffffff"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>
                
                <div class="md:col-span-2 border-t border-gray-200 pt-4 mt-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-image text-blue-600 mr-2"></i>Background Image Options
                    </h3>
                </div>
                
                <div>
                    <label for="background_size" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-expand-arrows-alt text-blue-600 mr-1"></i>Background Size
                    </label>
                    <select id="background_size" 
                            name="background_size" 
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="cover" <?= ($slider['background_size'] ?? 'cover') === 'cover' ? 'selected' : '' ?>>Cover (Fill container, maintain aspect)</option>
                        <option value="contain" <?= ($slider['background_size'] ?? '') === 'contain' ? 'selected' : '' ?>>Contain (Fit entire image)</option>
                        <option value="fill" <?= ($slider['background_size'] ?? '') === 'fill' ? 'selected' : '' ?>>Fill (Stretch to fill, may distort)</option>
                        <option value="stretch" <?= ($slider['background_size'] ?? '') === 'stretch' ? 'selected' : '' ?>>Stretch (100% width and height)</option>
                        <option value="auto" <?= ($slider['background_size'] ?? '') === 'auto' ? 'selected' : '' ?>>Auto (Original size)</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">How the background image should be sized</p>
                </div>
                
                <div>
                    <label for="background_position" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-crosshairs text-blue-600 mr-1"></i>Background Position
                    </label>
                    <select id="background_position" 
                            name="background_position" 
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="center" <?= ($slider['background_position'] ?? 'center') === 'center' ? 'selected' : '' ?>>Center</option>
                        <option value="top" <?= ($slider['background_position'] ?? '') === 'top' ? 'selected' : '' ?>>Top</option>
                        <option value="bottom" <?= ($slider['background_position'] ?? '') === 'bottom' ? 'selected' : '' ?>>Bottom</option>
                        <option value="left" <?= ($slider['background_position'] ?? '') === 'left' ? 'selected' : '' ?>>Left</option>
                        <option value="right" <?= ($slider['background_position'] ?? '') === 'right' ? 'selected' : '' ?>>Right</option>
                        <option value="top left" <?= ($slider['background_position'] ?? '') === 'top left' ? 'selected' : '' ?>>Top Left</option>
                        <option value="top right" <?= ($slider['background_position'] ?? '') === 'top right' ? 'selected' : '' ?>>Top Right</option>
                        <option value="bottom left" <?= ($slider['background_position'] ?? '') === 'bottom left' ? 'selected' : '' ?>>Bottom Left</option>
                        <option value="bottom right" <?= ($slider['background_position'] ?? '') === 'bottom right' ? 'selected' : '' ?>>Bottom Right</option>
                    </select>
                </div>
                
                <div>
                    <label for="slide_height" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-arrows-alt-v text-blue-600 mr-1"></i>Slide Height
                    </label>
                    <select id="slide_height" 
                            name="slide_height" 
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="auto" <?= ($slider['slide_height'] ?? 'auto') === 'auto' ? 'selected' : '' ?>>Auto (Responsive)</option>
                        <option value="full" <?= ($slider['slide_height'] ?? '') === 'full' ? 'selected' : '' ?>>Full Screen (100vh)</option>
                        <option value="custom" <?= ($slider['slide_height'] ?? '') === 'custom' ? 'selected' : '' ?>>Custom</option>
                    </select>
                </div>
                
                <div id="custom_height_container" style="display: <?= ($slider['slide_height'] ?? 'auto') === 'custom' ? 'block' : 'none' ?>;">
                    <label for="custom_height" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-ruler-vertical text-blue-600 mr-1"></i>Custom Height
                    </label>
                    <input type="text" 
                           id="custom_height" 
                           name="custom_height" 
                           value="<?= escape($slider['custom_height'] ?? '') ?>" 
                           placeholder="600px or 80vh"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    <p class="text-xs text-gray-500 mt-1">Examples: 600px, 80vh, 50rem</p>
                </div>
                
                <div class="md:col-span-2 border-t border-gray-200 pt-4 mt-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">
                        <i class="fas fa-magic text-blue-600 mr-2"></i>Animation & Effects
                    </h3>
                </div>
                
                <div>
                    <label for="content_animation" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-film text-blue-600 mr-1"></i>Content Animation
                    </label>
                    <select id="content_animation" 
                            name="content_animation" 
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="none" <?= ($slider['content_animation'] ?? 'fade') === 'none' ? 'selected' : '' ?>>None</option>
                        <option value="fade" <?= ($slider['content_animation'] ?? 'fade') === 'fade' ? 'selected' : '' ?>>Fade In</option>
                        <option value="slide-up" <?= ($slider['content_animation'] ?? '') === 'slide-up' ? 'selected' : '' ?>>Slide Up</option>
                        <option value="slide-down" <?= ($slider['content_animation'] ?? '') === 'slide-down' ? 'selected' : '' ?>>Slide Down</option>
                        <option value="zoom" <?= ($slider['content_animation'] ?? '') === 'zoom' ? 'selected' : '' ?>>Zoom In</option>
                    </select>
                </div>
                
                <div>
                    <label for="animation_speed" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-tachometer-alt text-blue-600 mr-1"></i>Animation Speed
                    </label>
                    <select id="animation_speed" 
                            name="animation_speed" 
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                        <option value="slow" <?= ($slider['animation_speed'] ?? 'normal') === 'slow' ? 'selected' : '' ?>>Slow</option>
                        <option value="normal" <?= ($slider['animation_speed'] ?? 'normal') === 'normal' ? 'selected' : '' ?>>Normal</option>
                        <option value="fast" <?= ($slider['animation_speed'] ?? '') === 'fast' ? 'selected' : '' ?>>Fast</option>
                    </select>
                </div>
                
                <div class="md:col-span-2">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" 
                               class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                               id="parallax_effect" 
                               name="parallax_effect" 
                               <?= ($slider['parallax_effect'] ?? 0) ? 'checked' : '' ?>>
                        <span class="ml-3 text-sm font-medium text-gray-700">Enable Parallax Effect</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1 ml-8">Creates a depth effect as user scrolls</p>
                </div>
                
                <div class="md:col-span-2 border-t border-gray-200 pt-4 mt-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" 
                               class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                               id="is_active" 
                               name="is_active" 
                               <?= ($slider['is_active'] ?? 1) ? 'checked' : '' ?>>
                        <span class="ml-3 text-sm font-medium text-gray-700">Active</span>
                    </label>
                </div>
            </div>
            
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-3 rounded-lg font-bold hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl">
                    <i class="fas fa-save mr-2"></i><?= $isEdit ? 'Update Slider' : 'Create Slider' ?>
                </button>
                <a href="<?= url('admin/hero-sliders.php') ?>" class="px-6 py-3 bg-gray-600 text-white rounded-lg font-bold hover:bg-gray-700 transition-all">
                    <i class="fas fa-times mr-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    const previewImg = document.getElementById(previewId + '-img');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');
        };
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.classList.add('hidden');
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

