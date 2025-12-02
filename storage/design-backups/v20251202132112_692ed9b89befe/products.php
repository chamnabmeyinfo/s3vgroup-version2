<?php
require_once __DIR__ . '/bootstrap/app.php';

// Check under construction mode
use App\Helpers\UnderConstruction;
UnderConstruction::show();

use App\Models\Product;
use App\Models\Category;

$productModel = new Product();
$categoryModel = new Category();

$filters = [
    'page' => $_GET['page'] ?? 1,
    'limit' => 12
];

if (!empty($_GET['category'])) {
    $category = $categoryModel->getBySlug($_GET['category']);
    if ($category) {
        $filters['category_id'] = $category['id'];
        $categoryName = $category['name'];
    }
}

if (!empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}

if (!empty($_GET['featured'])) {
    $filters['featured'] = true;
}

$products = $productModel->getAll($filters);
$totalProducts = $productModel->count($filters);
$totalPages = ceil($totalProducts / $filters['limit']);
$categories = $categoryModel->getAll(true);

$pageTitle = 'Products - Forklift & Equipment Pro';
$metaDescription = 'Browse our selection of forklifts and industrial equipment';

include __DIR__ . '/includes/header.php';
?>

<main class="py-8">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row gap-8">
            <!-- Sidebar Filters -->
            <aside class="md:w-64 flex-shrink-0">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-20">
                    <h3 class="text-xl font-bold mb-4">Filter Products</h3>
                    
                    <!-- Search -->
                    <form method="GET" class="mb-6">
                        <input type="text" name="search" 
                               value="<?= escape($_GET['search'] ?? '') ?>" 
                               placeholder="Search products..."
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button type="submit" class="btn-primary-sm w-full mt-2">Search</button>
                    </form>
                    
                    <!-- Categories -->
                    <div class="mb-6">
                        <h4 class="font-bold mb-3">Categories</h4>
                        <ul class="space-y-2">
                            <li>
                                <a href="<?= url('products.php') ?>" 
                                   class="text-gray-600 hover:text-blue-600 <?= empty($_GET['category']) ? 'font-bold text-blue-600' : '' ?>">
                                    All Categories
                                </a>
                            </li>
                            <?php foreach ($categories as $cat): ?>
                            <li>
                                <a href="<?= url('products.php?category=' . escape($cat['slug'])) ?>" 
                                   class="text-gray-600 hover:text-blue-600 <?= ($_GET['category'] ?? '') === $cat['slug'] ? 'font-bold text-blue-600' : '' ?>">
                                    <?= escape($cat['name']) ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <!-- Featured -->
                    <div>
                        <a href="<?= url('products.php?featured=1') ?>" 
                           class="text-blue-600 hover:underline font-semibold">
                            <i class="fas fa-star mr-2"></i> Featured Products
                        </a>
                    </div>
                </div>
            </aside>
            
            <!-- Products Grid -->
            <div class="flex-1">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold">
                        <?= isset($categoryName) ? escape($categoryName) : 'All Products' ?>
                        <?php if (!empty($_GET['search'])): ?>
                            <span class="text-gray-500 text-xl"> - Search: <?= escape($_GET['search']) ?></span>
                        <?php endif; ?>
                    </h1>
                    <p class="text-gray-600"><?= $totalProducts ?> products found</p>
                </div>
                
                <?php if (empty($products)): ?>
                    <div class="text-center py-12">
                        <p class="text-gray-600 text-xl">No products found.</p>
                        <a href="<?= url('products.php') ?>" class="btn-primary mt-4 inline-block">View All Products</a>
                    </div>
                <?php else: ?>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($products as $product): ?>
                        <div class="product-card bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden">
                            <a href="<?= url('product.php?slug=' . escape($product['slug'])) ?>">
                                <div class="h-48 bg-gray-200 flex items-center justify-center overflow-hidden relative">
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="<?= asset('storage/uploads/' . escape($product['image'])) ?>" 
                                             alt="<?= escape($product['name']) ?>" 
                                             class="w-full h-full object-cover transition-transform duration-300 hover:scale-110"
                                             loading="lazy"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="image-fallback" style="display: none;">
                                            <i class="fas fa-image text-4xl"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="product-image-placeholder w-full h-full">
                                            <i class="fas fa-image text-4xl"></i>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($product['is_featured']): ?>
                                        <span class="absolute top-2 right-2 bg-yellow-400 text-yellow-900 px-2 py-1 rounded text-xs font-bold">
                                            Featured
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="p-4">
                                    <h3 class="font-bold text-lg mb-2 line-clamp-2"><?= escape($product['name']) ?></h3>
                                    <p class="text-sm text-gray-600 mb-2"><?= escape($product['category_name'] ?? '') ?></p>
                                    <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?= escape($product['short_description'] ?? '') ?></p>
                                    <div class="flex justify-between items-center">
                                        <?php 
                                        $price = !empty($product['price']) && $product['price'] > 0 ? (float)$product['price'] : null;
                                        $salePrice = !empty($product['sale_price']) && $product['sale_price'] > 0 ? (float)$product['sale_price'] : null;
                                        ?>
                                        <?php if ($salePrice && $price): ?>
                                            <div>
                                                <span class="text-lg font-bold text-blue-600">$<?= number_format($salePrice, 2) ?></span>
                                                <span class="text-sm text-gray-400 line-through ml-2">$<?= number_format($price, 2) ?></span>
                                            </div>
                                        <?php elseif ($price): ?>
                                            <span class="text-lg font-bold text-blue-600">$<?= number_format($price, 2) ?></span>
                                        <?php else: ?>
                                            <span class="text-lg font-bold text-gray-500">Price on Request</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex gap-2 mt-3">
                                        <a href="<?= url('product.php?slug=' . escape($product['slug'])) ?>" class="btn-primary-sm flex-1 text-center">View</a>
                                        <button onclick="event.preventDefault(); openQuickView(<?= $product['id'] ?>)" 
                                                class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm"
                                                title="Quick View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="event.preventDefault(); quickAddToCart(<?= $product['id'] ?>)" 
                                                class="px-3 py-1 bg-blue-600 text-white hover:bg-blue-700 rounded text-sm transition-all"
                                                data-quick-add-cart="<?= $product['id'] ?>"
                                                title="Add to Cart">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                    </div>
                                </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="mt-8 flex justify-center space-x-2">
                        <?php if ($filters['page'] > 1): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $filters['page'] - 1])) ?>" 
                               class="px-4 py-2 border rounded hover:bg-gray-100">Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                               class="px-4 py-2 border rounded <?= $i == $filters['page'] ? 'bg-blue-600 text-white' : 'hover:bg-gray-100' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($filters['page'] < $totalPages): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $filters['page'] + 1])) ?>" 
                               class="px-4 py-2 border rounded hover:bg-gray-100">Next</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
function quickAddToCart(productId) {
    fetch('<?= url('api/cart.php') ?>?action=add&product_id=' + productId, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show notification if function exists
            if (typeof showNotification === 'function') {
                showNotification('Product added to cart!', 'success');
            } else {
                alert('Product added to cart!');
            }
            // Update cart count
            if (typeof updateCartCount === 'function') {
                updateCartCount();
            } else {
                location.reload();
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding product to cart');
    });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

