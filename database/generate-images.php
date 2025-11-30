<?php
/**
 * Generate Placeholder Product Images
 * Creates placeholder images for all products that don't have images
 */
require_once __DIR__ . '/../bootstrap/app.php';

echo "🎨 Starting Image Generation...\n\n";

try {
    $db = db();
    
    // Get all products
    $products = $db->fetchAll("SELECT id, name, category_id FROM products ORDER BY id");
    
    // Get categories for category-based images
    $categories = $db->fetchAll("SELECT id, name FROM categories");
    $categoryMap = [];
    foreach ($categories as $cat) {
        $categoryMap[$cat['id']] = $cat['name'];
    }
    
    // Ensure uploads directory exists
    $uploadDir = __DIR__ . '/../storage/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Color schemes for different categories
    $colorSchemes = [
        'Electric Forklifts' => ['bg' => [34, 139, 34], 'text' => [255, 255, 255]], // Green
        'Gas Forklifts' => ['bg' => [220, 20, 60], 'text' => [255, 255, 255]], // Red
        'Pallet Trucks' => ['bg' => [30, 144, 255], 'text' => [255, 255, 255]], // Blue
        'Reach Trucks' => ['bg' => [255, 140, 0], 'text' => [255, 255, 255]], // Orange
        'Stackers' => ['bg' => [138, 43, 226], 'text' => [255, 255, 255]], // Purple
        'Order Pickers' => ['bg' => [255, 69, 0], 'text' => [255, 255, 255]], // Red-orange
        'Sideloaders' => ['bg' => [70, 130, 180], 'text' => [255, 255, 255]], // Steel blue
        'Attachments' => ['bg' => [105, 105, 105], 'text' => [255, 255, 255]], // Gray
    ];
    
    $generated = 0;
    $skipped = 0;
    
    foreach ($products as $product) {
        // Check if image already exists
        $currentImage = $db->fetchOne("SELECT image FROM products WHERE id = :id", ['id' => $product['id']]);
        $imageName = $currentImage['image'] ?? null;
        
        // Check if file exists
        if ($imageName && file_exists($uploadDir . $imageName)) {
            echo "  - Skipping: {$product['name']} (has image: {$imageName})\n";
            $skipped++;
            continue;
        }
        
        // Generate image filename
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $product['name']));
        $slug = trim($slug, '-');
        $imageName = $slug . '.jpg';
        
        // Get category for color scheme
        $categoryName = $categoryMap[$product['category_id']] ?? 'Electric Forklifts';
        $colors = $colorSchemes[$categoryName] ?? $colorSchemes['Electric Forklifts'];
        
        // Create image
        $width = 800;
        $height = 600;
        $image = imagecreatetruecolor($width, $height);
        
        // Background color
        $bgColor = imagecolorallocate($image, $colors['bg'][0], $colors['bg'][1], $colors['bg'][2]);
        imagefill($image, 0, 0, $bgColor);
        
        // Add gradient effect
        $this->addGradient($image, $width, $height, $colors['bg']);
        
        // Text color
        $textColor = imagecolorallocate($image, $colors['text'][0], $colors['text'][1], $colors['text'][2]);
        
        // Add equipment icon/illustration (simple representation)
        $this->drawEquipment($image, $width, $height, $textColor, $categoryName);
        
        // Add product name
        $this->addText($image, $product['name'], $width, $height, $textColor);
        
        // Save image
        $imagePath = $uploadDir . $imageName;
        imagejpeg($image, $imagePath, 85);
        imagedestroy($image);
        
        // Update product in database
        $db->update('products', ['image' => $imageName], ['id' => $product['id']]);
        
        echo "  ✓ Generated: {$product['name']} -> {$imageName}\n";
        $generated++;
    }
    
    echo "\n✅ Image Generation Complete!\n";
    echo "📊 Summary:\n";
    echo "   - Generated: {$generated} images\n";
    echo "   - Skipped: {$skipped} (already have images)\n";
    echo "\n🎉 All products now have images!\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    if (strpos($e->getMessage(), 'gd') !== false || strpos($e->getMessage(), 'image') !== false) {
        echo "\n💡 Tip: Make sure PHP GD extension is enabled in your PHP configuration.\n";
    }
}

function addGradient($image, $width, $height, $baseColor) {
    for ($y = 0; $y < $height; $y++) {
        $ratio = $y / $height;
        $r = (int)($baseColor[0] * (1 - $ratio * 0.3));
        $g = (int)($baseColor[1] * (1 - $ratio * 0.3));
        $b = (int)($baseColor[2] * (1 - $ratio * 0.3));
        $color = imagecolorallocate($image, $r, $g, $b);
        imageline($image, 0, $y, $width, $y, $color);
    }
}

function drawEquipment($image, $width, $height, $color, $category) {
    $centerX = $width / 2;
    $centerY = $height / 2 - 50;
    
    // Draw a simple forklift representation
    $thickness = 8;
    
    // Mast (vertical lines)
    imageline($image, $centerX - 40, $centerY - 80, $centerX - 40, $centerY + 20, $color);
    imageline($image, $centerX + 40, $centerY - 80, $centerX + 40, $centerY + 20, $color);
    
    // Forks
    imageline($image, $centerX - 40, $centerY + 20, $centerX - 80, $centerY + 30, $color);
    imageline($image, $centerX + 40, $centerY + 20, $centerX + 80, $centerY + 30, $color);
    
    // Mast cross
    imageline($image, $centerX - 40, $centerY - 40, $centerX + 40, $centerY - 40, $color);
    
    // Body (rectangle)
    imagerectangle($image, $centerX - 60, $centerY + 20, $centerX + 60, $centerY + 80, $color);
    
    // Wheels
    imagefilledellipse($image, $centerX - 45, $centerY + 80, 25, 25, $color);
    imagefilledellipse($image, $centerX + 45, $centerY + 80, 25, 25, $color);
    
    // Operator compartment
    imagerectangle($image, $centerX - 30, $centerY + 30, $centerX + 30, $centerY + 60, $color);
}

function addText($image, $text, $width, $height, $color) {
    // Load font or use built-in
    $fontSize = 24;
    $maxWidth = $width - 40;
    
    // Word wrap text
    $words = explode(' ', $text);
    $lines = [];
    $currentLine = '';
    
    foreach ($words as $word) {
        $testLine = $currentLine . ($currentLine ? ' ' : '') . $word;
        $bbox = imagettfbbox($fontSize, 0, 5, $testLine); // Using built-in font size 5
        if ($bbox[4] - $bbox[0] > $maxWidth && $currentLine) {
            $lines[] = $currentLine;
            $currentLine = $word;
        } else {
            $currentLine = $testLine;
        }
    }
    if ($currentLine) {
        $lines[] = $currentLine;
    }
    
    // Center text vertically
    $totalHeight = count($lines) * ($fontSize + 10);
    $startY = $height - 100;
    
    // Draw text using built-in font
    foreach ($lines as $index => $line) {
        $bbox = imagestring($image, 5, 0, 0, $line, $color);
        $textWidth = imagefontwidth(5) * strlen($line);
        $x = ($width - $textWidth) / 2;
        $y = $startY + ($index * ($fontSize + 10));
        imagestring($image, 5, (int)$x, (int)$y, $line, $color);
    }
}

