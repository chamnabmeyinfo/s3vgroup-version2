<?php
/**
 * Create Product Images with Better Quality
 * Generates professional-looking placeholder images for products
 */
require_once __DIR__ . '/../bootstrap/app.php';

echo "🎨 Creating Product Images...\n\n";

// Check if GD library is available
if (!extension_loaded('gd')) {
    die("❌ Error: PHP GD extension is not loaded.\nPlease enable it in your php.ini file.\n");
}

echo "✓ GD library is available\n\n";

try {
    $db = db();
    
    // Get all products
    $products = $db->fetchAll("SELECT id, name, category_id FROM products ORDER BY id");
    
    // Get categories
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
    
    // Color schemes for categories
    $colors = [
        'Electric Forklifts' => [52, 152, 219],    // Blue
        'Gas Forklifts' => [231, 76, 60],          // Red
        'Pallet Trucks' => [46, 204, 113],         // Green
        'Reach Trucks' => [241, 196, 15],          // Yellow
        'Stackers' => [155, 89, 182],              // Purple
        'Order Pickers' => [230, 126, 34],         // Orange
        'Sideloaders' => [52, 73, 94],             // Dark blue
        'Attachments' => [149, 165, 166],          // Gray
    ];
    
    $generated = 0;
    $skipped = 0;
    
    foreach ($products as $product) {
        // Generate image filename from product name
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $product['name']), '-'));
        $imageName = $slug . '.jpg';
        $imagePath = $uploadDir . $imageName;
        
        // Check if already exists and skip if so
        if (file_exists($imagePath)) {
            echo "  - Skipping: {$product['name']} (exists)\n";
            $skipped++;
            continue;
        }
        
        // Get category color
        $categoryName = $categoryMap[$product['category_id']] ?? 'Electric Forklifts';
        $bgColor = $colors[$categoryName] ?? $colors['Electric Forklifts'];
        
        // Create image
        $width = 800;
        $height = 600;
        $img = imagecreatetruecolor($width, $height);
        
        // Create gradient background
        createGradientBackground($img, $width, $height, $bgColor);
        
        // Draw equipment icon
        drawForkliftIcon($img, $width, $height);
        
        // Add product name text
        addProductName($img, $product['name'], $width, $height);
        
        // Add category label
        addCategoryLabel($img, $categoryName, $width, $height);
        
        // Save image
        imagejpeg($img, $imagePath, 90);
        imagedestroy($img);
        
        // Update product in database
        $db->update('products', ['image' => $imageName], 'id = :id', ['id' => $product['id']]);
        
        echo "  ✓ Created: {$product['name']} -> {$imageName}\n";
        $generated++;
    }
    
    echo "\n✅ Image Creation Complete!\n";
    echo "📊 Summary:\n";
    echo "   - Created: {$generated} images\n";
    echo "   - Skipped: {$skipped} (already exist)\n";
    echo "\n🎉 All products now have images!\n";
    echo "📁 Images saved to: storage/uploads/\n";
    
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
}

function createGradientBackground($img, $width, $height, $baseColor) {
    // Darker base
    $darkR = max(0, $baseColor[0] - 40);
    $darkG = max(0, $baseColor[1] - 40);
    $darkB = max(0, $baseColor[2] - 40);
    
    // Lighter top
    $lightR = min(255, $baseColor[0] + 30);
    $lightG = min(255, $baseColor[1] + 30);
    $lightB = min(255, $baseColor[2] + 30);
    
    for ($y = 0; $y < $height; $y++) {
        $ratio = $y / $height;
        $r = (int)($lightR + ($darkR - $lightR) * $ratio);
        $g = (int)($lightG + ($darkG - $lightG) * $ratio);
        $b = (int)($lightB + ($darkB - $lightB) * $ratio);
        $color = imagecolorallocate($img, $r, $g, $b);
        imageline($img, 0, $y, $width, $y, $color);
    }
}

function drawForkliftIcon($img, $width, $height) {
    $centerX = $width / 2;
    $centerY = $height / 2 - 40;
    $white = imagecolorallocate($img, 255, 255, 255);
    $gray = imagecolorallocate($img, 200, 200, 200);
    
    // Mast (vertical structure)
    imagefilledrectangle($img, $centerX - 50, $centerY - 120, $centerX - 45, $centerY + 40, $white);
    imagefilledrectangle($img, $centerX + 45, $centerY - 120, $centerX + 50, $centerY + 40, $white);
    
    // Cross bars
    imagefilledrectangle($img, $centerX - 50, $centerY - 120, $centerX + 50, $centerY - 115, $white);
    imagefilledrectangle($img, $centerX - 50, $centerY - 60, $centerX + 50, $centerY - 55, $white);
    
    // Forks
    imagefilledpolygon($img, [
        $centerX - 45, $centerY + 40,
        $centerX - 85, $centerY + 50,
        $centerX - 85, $centerY + 70,
        $centerX - 45, $centerY + 60
    ], $white);
    
    imagefilledpolygon($img, [
        $centerX + 45, $centerY + 40,
        $centerX + 85, $centerY + 50,
        $centerX + 85, $centerY + 70,
        $centerX + 45, $centerY + 60
    ], $white);
    
    // Body
    imagefilledrectangle($img, $centerX - 70, $centerY + 40, $centerX + 70, $centerY + 100, $white);
    
    // Operator cabin
    imagefilledrectangle($img, $centerX - 40, $centerY + 50, $centerX + 40, $centerY + 80, $gray);
    
    // Wheels
    imagefilledellipse($img, $centerX - 50, $centerY + 100, 30, 30, $gray);
    imagefilledellipse($img, $centerX + 50, $centerY + 100, 30, 30, $gray);
}

function addProductName($img, $text, $width, $height) {
    $white = imagecolorallocate($img, 255, 255, 255);
    $black = imagecolorallocate($img, 0, 0, 0);
    
    // Use built-in font for text
    $font = 5; // Large built-in font
    $fontWidth = imagefontwidth($font);
    $fontHeight = imagefontheight($font);
    
    // Break text into lines if too long
    $maxChars = 35;
    $words = explode(' ', $text);
    $lines = [];
    $currentLine = '';
    
    foreach ($words as $word) {
        if (strlen($currentLine . $word) <= $maxChars) {
            $currentLine = ($currentLine ? $currentLine . ' ' : '') . $word;
        } else {
            if ($currentLine) $lines[] = $currentLine;
            $currentLine = $word;
        }
    }
    if ($currentLine) $lines[] = $currentLine;
    
    // Draw text with shadow
    $y = $height - 60;
    foreach ($lines as $line) {
        $textWidth = strlen($line) * $fontWidth;
        $x = (int)(($width - $textWidth) / 2);
        
        // Shadow
        imagestring($img, $font, $x + 2, (int)$y + 2, $line, $black);
        // Text
        imagestring($img, $font, $x, (int)$y, $line, $white);
        $y += $fontHeight + 5;
    }
}

function addCategoryLabel($img, $category, $width, $height) {
    $white = imagecolorallocate($img, 255, 255, 255);
    $font = 3; // Smaller font
    $text = strtoupper($category);
    $textWidth = strlen($text) * imagefontwidth($font);
    $x = (int)(($width - $textWidth) / 2);
    $y = $height - 120;
    imagestring($img, $font, $x, $y, $text, $white);
}

