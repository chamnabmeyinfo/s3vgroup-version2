<?php
require_once __DIR__ . '/bootstrap/app.php';

use App\Models\Product;
use App\Models\Category;

if (empty($_GET['slug'])) {
    header('Location: ' . url('products.php'));
    exit;
}

$productModel = new Product();
$categoryModel = new Category();

$product = $productModel->getBySlug($_GET['slug']);

if (!$product) {
    header('Location: ' . url('products.php'));
    exit;
}

// Track recently viewed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Track in session
if (!isset($_SESSION['recently_viewed'])) {
    $_SESSION['recently_viewed'] = [];
}

// Remove if already exists
$_SESSION['recently_viewed'] = array_filter($_SESSION['recently_viewed'], fn($id) => $id != $product['id']);

// Add to beginning
array_unshift($_SESSION['recently_viewed'], $product['id']);

// Keep only last 10
$_SESSION['recently_viewed'] = array_slice($_SESSION['recently_viewed'], 0, 10);

$relatedProducts = $productModel->getAll([
    'category_id' => $product['category_id'],
    'limit' => 4
]);

// Remove current product from related
$relatedProducts = array_filter($relatedProducts, fn($p) => $p['id'] != $product['id']);
$relatedProducts = array_slice($relatedProducts, 0, 3);

$gallery = [];
if (!empty($product['gallery'])) {
    $gallery = json_decode($product['gallery'], true) ?? [];
}
if (!empty($product['image'])) {
    array_unshift($gallery, $product['image']);
}

$specifications = [];
if (!empty($product['specifications'])) {
    $specifications = json_decode($product['specifications'], true) ?? [];
}

$pageTitle = escape($product['name']) . ' - Forklift & Equipment Pro';
$metaDescription = escape($product['short_description'] ?? $product['description'] ?? '');

include __DIR__ . '/includes/header.php';
?>

<main class="py-8">
    <div class="container mx-auto px-4">
        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-600 mb-6">
            <a href="<?= url() ?>" class="hover:text-blue-600">Home</a>
            <span class="mx-2">/</span>
            <a href="<?= url('products.php') ?>" class="hover:text-blue-600">Products</a>
            <?php if (!empty($product['category_slug'])): ?>
                <span class="mx-2">/</span>
                <a href="<?= url('products.php?category=' . escape($product['category_slug'])) ?>" class="hover:text-blue-600">
                    <?= escape($product['category_name']) ?>
                </a>
            <?php endif; ?>
            <span class="mx-2">/</span>
            <span class="text-gray-900"><?= escape($product['name']) ?></span>
        </nav>
        
        <div class="grid md:grid-cols-2 gap-8 mb-12">
            <!-- Product Images -->
            <div>
                <div class="mb-4 relative overflow-hidden rounded-lg">
                    <?php if (!empty($gallery[0])): ?>
                        <img id="main-image" 
                             src="<?= asset('storage/uploads/' . escape($gallery[0])) ?>" 
                             alt="<?= escape($product['name']) ?>" 
                             class="product-zoom-image w-full h-96 object-cover rounded-lg cursor-zoom-in"
                             loading="eager">
                        <button onclick="openImageLightbox(this.previousElementSibling.src, this.previousElementSibling.alt)" 
                                class="absolute top-4 right-4 bg-white bg-opacity-80 hover:bg-opacity-100 rounded-full p-2 transition-all z-10">
                            <i class="fas fa-expand text-gray-700"></i>
                        </button>
                    <?php else: ?>
                        <div class="product-image-placeholder w-full h-96 rounded-lg">
                            <div class="text-center">
                                <i class="fas fa-image text-6xl mb-4"></i>
                                <p class="text-lg">No Image Available</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (count($gallery) > 1): ?>
                <div class="grid grid-cols-4 gap-2">
                    <?php foreach ($gallery as $index => $image): ?>
                    <img src="<?= asset('storage/uploads/' . escape($image)) ?>" 
                         alt="Gallery image <?= $index + 1 ?>"
                         onclick="changeMainImage('<?= asset('storage/uploads/' . escape($image)) ?>', this)"
                         class="gallery-thumbnail w-full h-24 object-cover rounded cursor-pointer border-2 border-transparent hover:border-blue-500 <?= $index === 0 ? 'active border-blue-500' : '' ?>"
                         loading="lazy">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Product Info -->
            <div>
                <?php if ($product['is_featured']): ?>
                    <span class="inline-block bg-yellow-400 text-yellow-900 px-3 py-1 rounded text-sm font-bold mb-4">
                        Featured Product
                    </span>
                <?php endif; ?>
                
                <h1 class="text-3xl font-bold mb-4"><?= escape($product['name']) ?></h1>
                
                <?php if (!empty($product['category_name'])): ?>
                    <p class="text-gray-600 mb-4">Category: 
                        <a href="<?= url('products.php?category=' . escape($product['category_slug'])) ?>" 
                           class="text-blue-600 hover:underline">
                            <?= escape($product['category_name']) ?>
                        </a>
                    </p>
                <?php endif; ?>
                
                <div class="mb-6">
                    <?php if ($product['sale_price']): ?>
                        <div class="flex items-center gap-4">
                            <span class="text-4xl font-bold text-blue-600">$<?= number_format($product['sale_price'], 2) ?></span>
                            <span class="text-2xl text-gray-400 line-through">$<?= number_format($product['price'], 2) ?></span>
                            <span class="bg-red-500 text-white px-2 py-1 rounded text-sm">
                                Save <?= number_format((($product['price'] - $product['sale_price']) / $product['price']) * 100, 0) ?>%
                            </span>
                        </div>
                    <?php else: ?>
                        <span class="text-4xl font-bold text-blue-600">$<?= number_format($product['price'], 2) ?></span>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($product['short_description'])): ?>
                    <p class="text-gray-700 mb-6"><?= nl2br(escape($product['short_description'])) ?></p>
                <?php endif; ?>
                
                <div class="flex gap-4 mb-6 flex-wrap">
                    <button onclick="addToCart(<?= $product['id'] ?>)" class="btn-primary flex-1 text-center min-w-[140px]">
                        <i class="fas fa-shopping-cart mr-2"></i> Add to Cart
                    </button>
                    <a href="<?= url('quote.php?product_id=' . $product['id']) ?>" class="btn-secondary flex-1 text-center min-w-[140px]">
                        <i class="fas fa-calculator mr-2"></i> Request Quote
                    </a>
                    <a href="<?= url('contact.php?product=' . escape($product['name'])) ?>" class="btn-secondary flex-1 text-center min-w-[140px]">
                        <i class="fas fa-envelope mr-2"></i> Contact Us
                    </a>
                    <button onclick="addToWishlist(<?= $product['id'] ?>)" 
                            id="wishlist-btn-<?= $product['id'] ?>"
                            class="px-4 py-2 border-2 border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                        <i class="fas fa-heart"></i>
                    </button>
                    <button onclick="addToCompare(<?= $product['id'] ?>)" 
                            id="compare-btn-<?= $product['id'] ?>"
                            class="px-4 py-2 border-2 border-blue-300 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors">
                        <i class="fas fa-balance-scale"></i>
                    </button>
                </div>
                
                <div class="border-t pt-6 space-y-3">
                    <?php if (!empty($product['sku'])): ?>
                        <p><strong>SKU:</strong> <?= escape($product['sku']) ?></p>
                    <?php endif; ?>
                    <p><strong>Stock Status:</strong> 
                        <span class="text-green-600 font-semibold"><?= ucwords(str_replace('_', ' ', $product['stock_status'])) ?></span>
                    </p>
                    <?php if (!empty($product['weight'])): ?>
                        <p><strong>Weight:</strong> <?= number_format($product['weight'], 2) ?> lbs</p>
                    <?php endif; ?>
                    <?php if (!empty($product['dimensions'])): ?>
                        <p><strong>Dimensions:</strong> <?= escape($product['dimensions']) ?></p>
                    <?php endif; ?>
                    
                    <!-- Social Sharing -->
                    <div class="pt-4">
                        <?php 
                        $metaDescription = $product['short_description'] ?? $product['name'];
                        include __DIR__ . '/includes/social-share.php'; 
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="mb-12">
            <div class="border-b flex items-center justify-between">
                <div>
                    <button onclick="showTab('description')" class="tab-btn active px-6 py-3 font-semibold">Description</button>
                    <button onclick="showTab('specifications')" class="tab-btn px-6 py-3 font-semibold">Specifications</button>
                    <button onclick="showTab('features')" class="tab-btn px-6 py-3 font-semibold">Features</button>
                </div>
                <a href="<?= url('product-reviews.php?product_id=' . $product['id']) ?>" class="text-blue-600 hover:underline font-semibold">
                    <i class="fas fa-star mr-1"></i> View Reviews
                </a>
            </div>
            
            <div id="description" class="tab-content py-6">
                <div class="prose max-w-none">
                    <?= nl2br(escape($product['description'] ?? 'No description available.')) ?>
                </div>
            </div>
            
            <div id="specifications" class="tab-content py-6 hidden">
                <?php if (!empty($specifications)): ?>
                    <table class="w-full">
                        <tbody>
                            <?php foreach ($specifications as $key => $value): ?>
                            <tr class="border-b">
                                <td class="py-2 font-semibold"><?= escape($key) ?></td>
                                <td class="py-2"><?= escape($value) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-gray-600">No specifications available.</p>
                <?php endif; ?>
            </div>
            
            <div id="features" class="tab-content py-6 hidden">
                <?php if (!empty($product['features'])): ?>
                    <div class="prose max-w-none">
                        <?= nl2br(escape($product['features'])) ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-600">No features listed.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
        <div>
            <h2 class="text-2xl font-bold mb-6">Related Products</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($relatedProducts as $related): ?>
                <div class="product-card bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden">
                    <a href="<?= url('product.php?slug=' . escape($related['slug'])) ?>">
                        <div class="h-48 bg-gray-200 flex items-center justify-center overflow-hidden relative">
                            <?php if (!empty($related['image'])): ?>
                                <img src="<?= asset('storage/uploads/' . escape($related['image'])) ?>" 
                                     alt="<?= escape($related['name']) ?>" 
                                     class="w-full h-full object-cover transition-transform duration-300 hover:scale-110"
                                     loading="lazy"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="image-fallback" style="display: none;">
                                    <i class="fas fa-image text-4xl text-white"></i>
                                </div>
                            <?php else: ?>
                                <div class="product-image-placeholder w-full h-full">
                                    <i class="fas fa-image text-4xl"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-lg mb-2"><?= escape($related['name']) ?></h3>
                            <p class="text-lg font-bold text-blue-600">$<?= number_format($related['price'], 2) ?></p>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<script>
function changeMainImage(imageSrc, thumbnail) {
    const mainImage = document.getElementById('main-image');
    if (mainImage) {
        mainImage.src = imageSrc;
        
        // Update active thumbnail
        document.querySelectorAll('.gallery-thumbnail').forEach(thumb => {
            thumb.classList.remove('active', 'border-blue-500');
        });
        if (thumbnail) {
            thumbnail.classList.add('active', 'border-blue-500');
        }
    }
}

function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active', 'border-b-2', 'border-blue-600');
    });
    
    // Show selected tab content
    document.getElementById(tabName).classList.remove('hidden');
    
    // Add active class to clicked button
    event.target.classList.add('active', 'border-b-2', 'border-blue-600');
}

function addToCart(productId) {
    fetch('<?= url('api/cart.php') ?>?action=add&product_id=' + productId, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Product added to cart!', 'success');
            updateCartCount();
        } else {
            showNotification('Error adding to cart', 'error');
        }
    })
    .catch(error => {
        showNotification('Error adding to cart', 'error');
    });
}

function updateCartCount() {
    fetch('<?= url('api/cart.php') ?>?action=count')
        .then(response => response.json())
        .then(data => {
            const cartCount = document.getElementById('cart-count');
            if (cartCount) {
                if (data.count > 0) {
                    cartCount.textContent = data.count;
                    cartCount.classList.remove('hidden');
                } else {
                    cartCount.classList.add('hidden');
                }
            }
        });
}

// Load cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});
</script>

<?php include __DIR__ . '/includes/image-zoom.php'; ?>
<?php include __DIR__ . '/includes/quick-view-modal.php'; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>

