<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

$message = '';
$error = '';

// Handle delete
if (!empty($_GET['delete'])) {
    $filename = basename($_GET['delete']);
    $filepath = __DIR__ . '/../storage/uploads/' . $filename;
    
    if (file_exists($filepath) && is_file($filepath)) {
        if (unlink($filepath)) {
            $message = 'Image deleted successfully.';
        } else {
            $error = 'Failed to delete image.';
        }
    } else {
        $error = 'Image not found.';
    }
}

// Get all images
$uploadDir = __DIR__ . '/../storage/uploads/';
$images = [];

if (is_dir($uploadDir)) {
    $files = scandir($uploadDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && is_file($uploadDir . $file)) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $images[] = [
                    'filename' => $file,
                    'size' => filesize($uploadDir . $file),
                    'date' => filemtime($uploadDir . $file)
                ];
            }
        }
    }
    
    // Sort by date (newest first)
    usort($images, function($a, $b) {
        return $b['date'] - $a['date'];
    });
}

$pageTitle = 'Image Management';
include __DIR__ . '/includes/header.php';
?>

<h1 class="text-3xl font-bold mb-6">Image Management</h1>

<!-- Upload Area -->
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h2 class="text-xl font-bold mb-4">Upload New Image</h2>
    <form id="uploadForm" enctype="multipart/form-data" class="space-y-4">
        <div>
            <input type="file" id="fileInput" name="file" accept="image/*" required
                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <p class="text-sm text-gray-600 mt-1">Maximum file size: 5MB. Allowed types: JPG, PNG, GIF, WebP</p>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
            Upload Image
        </button>
    </form>
    <div id="uploadResult" class="mt-4"></div>
</div>

<!-- Images Grid -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-6 border-b">
        <h2 class="text-xl font-bold">Uploaded Images (<?= count($images) ?>)</h2>
    </div>
    
    <?php if (empty($images)): ?>
        <div class="p-12 text-center text-gray-500">
            <p>No images uploaded yet.</p>
        </div>
    <?php else: ?>
        <div class="p-6 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php foreach ($images as $image): ?>
            <div class="border rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                <div class="aspect-square bg-gray-100 overflow-hidden">
                    <img src="<?= asset('storage/uploads/' . escape($image['filename'])) ?>" 
                         alt="" 
                         class="w-full h-full object-cover">
                </div>
                <div class="p-3">
                    <p class="text-xs text-gray-600 truncate mb-2" title="<?= escape($image['filename']) ?>">
                        <?= escape($image['filename']) ?>
                    </p>
                    <p class="text-xs text-gray-500 mb-2">
                        <?= number_format($image['size'] / 1024, 2) ?> KB
                    </p>
                    <div class="flex gap-2">
                        <input type="text" 
                               value="<?= escape($image['filename']) ?>" 
                               readonly
                               onclick="this.select()"
                               class="flex-1 text-xs px-2 py-1 border rounded bg-gray-50">
                        <button onclick="copyToClipboard('<?= escape($image['filename']) ?>')" 
                                class="text-xs px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600"
                                title="Copy filename">
                            Copy
                        </button>
                    </div>
                    <button onclick="deleteImage('<?= escape($image['filename']) ?>')" 
                            class="mt-2 w-full text-xs px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">
                        Delete
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('file', document.getElementById('fileInput').files[0]);
    
    const resultDiv = document.getElementById('uploadResult');
    resultDiv.innerHTML = '<p class="text-blue-600">Uploading...</p>';
    
    fetch('<?= url('admin/upload.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultDiv.innerHTML = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">Image uploaded! Filename: ' + data.file + '</div>';
            document.getElementById('fileInput').value = '';
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            resultDiv.innerHTML = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">' + data.message + '</div>';
        }
    })
    .catch(error => {
        resultDiv.innerHTML = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">Upload failed. Please try again.</div>';
    });
});

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Filename copied to clipboard!');
    });
}

function deleteImage(filename) {
    if (confirm('Are you sure you want to delete this image?')) {
        window.location.href = '?delete=' + encodeURIComponent(filename);
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

