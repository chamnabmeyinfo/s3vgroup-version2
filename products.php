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

// Advanced filters
if (!empty($_GET['min_price'])) {
    $filters['min_price'] = (float)$_GET['min_price'];
}

if (!empty($_GET['max_price'])) {
    $filters['max_price'] = (float)$_GET['max_price'];
}

if (!empty($_GET['sort'])) {
    $filters['sort'] = $_GET['sort'];
}

if (!empty($_GET['in_stock'])) {
    $filters['in_stock'] = true;
}

// Get price range for filter
$allProductsForRange = $productModel->getAll([]);
$prices = [];
foreach ($allProductsForRange as $p) {
    $price = !empty($p['sale_price']) ? (float)$p['sale_price'] : (!empty($p['price']) ? (float)$p['price'] : null);
    if ($price !== null) {
        $prices[] = $price;
    }
}
$minPriceRange = !empty($prices) ? min($prices) : 0;
$maxPriceRange = !empty($prices) ? max($prices) : 10000;

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
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold">Filter Products</h3>
                        <button onclick="clearFilters()" class="text-sm text-blue-600 hover:underline">Clear</button>
                    </div>
                    
                    <form method="GET" id="filter-form" class="space-y-6">
                        <!-- Preserve existing params -->
                        <input type="hidden" name="page" value="1">
                        
                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium mb-2">Search</label>
                            <input type="text" name="search" 
                                   value="<?= escape($_GET['search'] ?? '') ?>" 
                                   placeholder="Search products..."
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   onkeyup="debounceFilter()">
                        </div>
                        
                        <!-- Price Range -->
                        <div>
                            <label class="block text-sm font-medium mb-2">Price Range</label>
                            <div class="flex gap-2">
                                <input type="number" name="min_price" 
                                       value="<?= escape($_GET['min_price'] ?? '') ?>" 
                                       placeholder="Min"
                                       min="0"
                                       step="0.01"
                                       class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                                       onchange="applyFilters()">
                                <input type="number" name="max_price" 
                                       value="<?= escape($_GET['max_price'] ?? '') ?>" 
                                       placeholder="Max"
                                       min="0"
                                       step="0.01"
                                       class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                                       onchange="applyFilters()">
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                Range: $<?= number_format($minPriceRange, 2) ?> - $<?= number_format($maxPriceRange, 2) ?>
                            </div>
                        </div>
                        
                        <!-- Categories -->
                        <div>
                            <label class="block text-sm font-medium mb-2">Categories</label>
                            <div class="max-h-48 overflow-y-auto space-y-2">
                                <label class="flex items-center">
                                    <input type="radio" name="category" value="" 
                                           <?= empty($_GET['category']) ? 'checked' : '' ?>
                                           onchange="applyFilters()"
                                           class="mr-2">
                                    <span class="text-sm">All Categories</span>
                                </label>
                                <?php foreach ($categories as $cat): ?>
                                <label class="flex items-center">
                                    <input type="radio" name="category" value="<?= escape($cat['slug']) ?>" 
                                           <?= ($_GET['category'] ?? '') === $cat['slug'] ? 'checked' : '' ?>
                                           onchange="applyFilters()"
                                           class="mr-2">
                                    <span class="text-sm"><?= escape($cat['name']) ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Stock Status -->
                        <div>
                            <label class="block text-sm font-medium mb-2">Availability</label>
                            <label class="flex items-center">
                                <input type="checkbox" name="in_stock" value="1" 
                                       <?= !empty($_GET['in_stock']) ? 'checked' : '' ?>
                                       onchange="applyFilters()"
                                       class="mr-2">
                                <span class="text-sm">In Stock Only</span>
                            </label>
                        </div>
                        
                        <!-- Featured -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="featured" value="1" 
                                       <?= !empty($_GET['featured']) ? 'checked' : '' ?>
                                       onchange="applyFilters()"
                                       class="mr-2">
                                <span class="text-sm font-semibold">
                                    <i class="fas fa-star text-yellow-500 mr-1"></i> Featured Only
                                </span>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn-primary-sm w-full hidden" id="filter-submit">Apply Filters</button>
                    </form>
                </div>
            </aside>
            
            <!-- Products Grid -->
            <div class="flex-1">
                <!-- Header with Layout Switcher and Sort -->
                <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl md:text-3xl font-bold">
                                <?= isset($categoryName) ? escape($categoryName) : 'All Products' ?>
                                <?php if (!empty($_GET['search'])): ?>
                                    <span class="text-gray-500 text-lg md:text-xl"> - Search: <?= escape($_GET['search']) ?></span>
                                <?php endif; ?>
                            </h1>
                            <p class="text-gray-600 text-sm mt-1"><?= $totalProducts ?> products found</p>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <!-- Sort Dropdown -->
                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-600">Sort:</label>
                                <select id="sort-select" onchange="applyFilters()" class="px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                    <option value="name" <?= ($_GET['sort'] ?? 'name') === 'name' ? 'selected' : '' ?>>Name A-Z</option>
                                    <option value="name_desc" <?= ($_GET['sort'] ?? '') === 'name_desc' ? 'selected' : '' ?>>Name Z-A</option>
                                    <option value="price_asc" <?= ($_GET['sort'] ?? '') === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                                    <option value="price_desc" <?= ($_GET['sort'] ?? '') === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                                    <option value="newest" <?= ($_GET['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Newest First</option>
                                    <option value="featured" <?= ($_GET['sort'] ?? '') === 'featured' ? 'selected' : '' ?>>Featured First</option>
                                </select>
                            </div>
                            
                            <!-- Layout Switcher -->
                            <div class="flex items-center gap-2 border rounded-lg p-1">
                                <button onclick="setLayout('grid')" id="layout-grid" class="layout-btn px-3 py-2 rounded text-sm font-medium transition-all" title="Grid View">
                                    <i class="fas fa-th"></i>
                                </button>
                                <button onclick="setLayout('list')" id="layout-list" class="layout-btn px-3 py-2 rounded text-sm font-medium transition-all" title="List View">
                                    <i class="fas fa-list"></i>
                                </button>
                                <button onclick="setLayout('compact')" id="layout-compact" class="layout-btn px-3 py-2 rounded text-sm font-medium transition-all" title="Compact View">
                                    <i class="fas fa-th-large"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (empty($products)): ?>
                    <div class="text-center py-12">
                        <p class="text-gray-600 text-xl">No products found.</p>
                        <a href="<?= url('products.php') ?>" class="btn-primary mt-4 inline-block">View All Products</a>
                    </div>
                <?php else: ?>
                    <div class="products-container grid sm:grid-cols-2 lg:grid-cols-3 gap-6" id="products-grid" data-layout="grid">
                        <?php foreach ($products as $product): ?>
                        <div class="product-card product-item bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden" data-product-id="<?= $product['id'] ?>">
                            <a href="<?= url('product.php?slug=' . escape($product['slug'])) ?>">
                                <div class="w-full aspect-[10/7] bg-gray-200 flex items-center justify-center overflow-hidden relative">
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'%3E%3Crect fill='%23e5e7eb' width='400' height='300'/%3E%3C/svg%3E" 
                                             data-src="<?= asset('storage/uploads/' . escape($product['image'])) ?>"
                                             alt="<?= escape($product['name']) ?>" 
                                             class="lazy-load w-full h-full object-cover transition-transform duration-300 hover:scale-110"
                                             loading="lazy"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="image-fallback" style="display: none;">
                                            <i class="fas fa-image text-4xl text-gray-400"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="product-image-placeholder w-full h-full">
                                            <i class="fas fa-image text-4xl text-gray-400"></i>
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
                                                <span class="text-lg font-bold text-blue-600">$<?= number_format((float)$salePrice, 2) ?></span>
                                                <span class="text-sm text-gray-400 line-through ml-2">$<?= number_format((float)$price, 2) ?></span>
                                            </div>
                                        <?php elseif ($price): ?>
                                            <span class="text-lg font-bold text-blue-600">$<?= number_format((float)$price, 2) ?></span>
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
                    
                    <!-- Load More Button -->
                    <?php if ($totalPages > 1 && $filters['page'] < $totalPages): ?>
                    <div class="mt-12 text-center" id="load-more-container">
                        <button id="load-more-btn" 
                                data-current-page="<?= $filters['page'] ?>"
                                data-total-pages="<?= $totalPages ?>"
                                data-category="<?= escape($_GET['category'] ?? '') ?>"
                                data-search="<?= escape($_GET['search'] ?? '') ?>"
                                data-featured="<?= escape($_GET['featured'] ?? '') ?>"
                                class="btn-primary px-8 py-3 text-lg">
                            <i class="fas fa-spinner fa-spin hidden mr-2" id="load-more-spinner"></i>
                            <span id="load-more-text">Load More Products</span>
                            <span class="text-sm font-normal ml-2" id="load-more-count">(<?= $totalProducts - (count($products) * $filters['page']) ?> remaining)</span>
                        </button>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
// Layout Management
let currentLayout = localStorage.getItem('productLayout') || 'grid';
setLayout(currentLayout);

function setLayout(layout) {
    currentLayout = layout;
    localStorage.setItem('productLayout', layout);
    const container = document.getElementById('products-grid');
    const items = document.querySelectorAll('.product-item');
    
    // Update active button
    document.querySelectorAll('.layout-btn').forEach(btn => {
        btn.classList.remove('bg-blue-600', 'text-white');
        btn.classList.add('text-gray-600', 'hover:bg-gray-100');
    });
    const activeBtn = document.getElementById('layout-' + layout);
    if (activeBtn) {
        activeBtn.classList.remove('text-gray-600', 'hover:bg-gray-100');
        activeBtn.classList.add('bg-blue-600', 'text-white');
    }
    
    // Remove all layout classes
    container.classList.remove('grid', 'list-view', 'compact-view', 'sm:grid-cols-2', 'md:grid-cols-3', 'lg:grid-cols-3', 'lg:grid-cols-4', 'gap-6', 'gap-4', 'space-y-4');
    items.forEach(item => {
        item.classList.remove('grid-item', 'list-item', 'compact-item', 'flex');
    });
    
    // Apply new layout
    if (layout === 'grid') {
        container.classList.add('grid', 'sm:grid-cols-2', 'lg:grid-cols-3', 'gap-6');
        items.forEach(item => item.classList.add('grid-item'));
    } else if (layout === 'list') {
        container.classList.add('list-view', 'space-y-4');
        items.forEach(item => {
            item.classList.add('list-item', 'flex', 'gap-4');
        });
    } else if (layout === 'compact') {
        container.classList.add('grid', 'sm:grid-cols-2', 'md:grid-cols-3', 'lg:grid-cols-4', 'gap-4', 'compact-view');
        items.forEach(item => item.classList.add('compact-item'));
    }
}

// Filter Management
let filterTimeout;
function debounceFilter() {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
}

function applyFilters() {
    document.getElementById('filter-form').submit();
}

function clearFilters() {
    window.location.href = '<?= url('products.php') ?>';
}

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

