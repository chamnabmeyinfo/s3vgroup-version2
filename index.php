<?php
// Check if database is set up
try {
    require_once __DIR__ . '/bootstrap/app.php';
    $db = db();
    $db->fetchOne("SELECT 1 FROM products LIMIT 1");
} catch (Exception $e) {
    // Database not set up - redirect to setup
    header('Location: setup.php');
    exit;
}

require_once __DIR__ . '/bootstrap/app.php';

use App\Models\Product;
use App\Models\Category;

$productModel = new Product();
$categoryModel = new Category();

$featuredProducts = $productModel->getFeatured(8);
$categories = $categoryModel->getAll(true);

$pageTitle = 'Forklift & Equipment Pro - Industrial Equipment Solutions';
include __DIR__ . '/includes/header.php';
?>

<main>
    <!-- Hero Section -->
    <section class="hero bg-gradient-to-br from-blue-900 via-blue-800 to-gray-900 text-white py-20 md:py-32">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-6 animate-fade-in">
                    Premium Forklifts & Industrial Equipment
                </h1>
                <p class="text-xl md:text-2xl mb-8 text-blue-100">
                    Quality equipment for your warehouse and factory needs. Trusted by industry leaders worldwide.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="<?= url('products.php') ?>" class="btn-primary inline-block">
                        Browse Products
                    </a>
                    <a href="<?= url('contact.php') ?>" class="btn-secondary inline-block">
                        Get a Quote
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Quality Assured</h3>
                    <p class="text-gray-600">All equipment is thoroughly inspected and certified</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Fast Delivery</h3>
                    <p class="text-gray-600">Quick shipping and reliable delivery service</p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Expert Support</h3>
                    <p class="text-gray-600">24/7 customer support and maintenance services</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <?php if (!empty($categories)): ?>
    <section class="py-16">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12">Shop by Category</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($categories as $category): ?>
                <a href="<?= url('products.php?category=' . escape($category['slug'])) ?>" 
                   class="category-card group block bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden">
                    <div class="h-48 bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center">
                        <h3 class="text-2xl font-bold text-white"><?= escape($category['name']) ?></h3>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600"><?= escape($category['description'] ?? 'Browse our selection') ?></p>
                        <span class="inline-block mt-4 text-blue-600 font-semibold group-hover:underline">
                            View Products →
                        </span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Featured Products Section -->
    <?php if (!empty($featuredProducts)): ?>
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-12">
                <h2 class="text-3xl font-bold">Featured Products</h2>
                <a href="<?= url('products.php') ?>" class="text-blue-600 font-semibold hover:underline">
                    View All →
                </a>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($featuredProducts as $product): ?>
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
                                    <i class="fas fa-image text-4xl text-white"></i>
                                </div>
                            <?php else: ?>
                                <div class="product-image-placeholder w-full h-full">
                                    <i class="fas fa-image text-4xl"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-lg mb-2 line-clamp-2"><?= escape($product['name']) ?></h3>
                            <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?= escape($product['short_description'] ?? '') ?></p>
                            <div class="flex justify-between items-center">
                                <?php if ($product['sale_price']): ?>
                                    <div>
                                        <span class="text-lg font-bold text-blue-600">$<?= number_format($product['sale_price'], 2) ?></span>
                                        <span class="text-sm text-gray-400 line-through ml-2">$<?= number_format($product['price'], 2) ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-lg font-bold text-blue-600">$<?= number_format($product['price'], 2) ?></span>
                                <?php endif; ?>
                                <span class="btn-primary-sm">View Details</span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Recently Viewed Products -->
    <?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $recentIds = $_SESSION['recently_viewed'] ?? [];
    if (!empty($recentIds)):
        $recentProducts = [];
        foreach (array_slice($recentIds, 0, 4) as $id) {
            $product = $productModel->getById($id);
            if ($product && $product['is_active']) {
                $recentProducts[] = $product;
            }
        }
        if (!empty($recentProducts)):
    ?>
    <section class="py-16">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-12">
                <h2 class="text-3xl font-bold">Recently Viewed</h2>
                <a href="<?= url('recently-viewed.php') ?>" class="text-blue-600 font-semibold hover:underline">
                    View All →
                </a>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($recentProducts as $product): ?>
                <div class="product-card bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden">
                    <a href="<?= url('product.php?slug=' . escape($product['slug'])) ?>">
                        <div class="h-48 bg-gray-200 flex items-center justify-center overflow-hidden">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?= asset('storage/uploads/' . escape($product['image'])) ?>" 
                                     alt="<?= escape($product['name']) ?>" 
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <span class="text-gray-400">No Image</span>
                            <?php endif; ?>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-lg mb-2 line-clamp-2"><?= escape($product['name']) ?></h3>
                            <p class="text-lg font-bold text-blue-600">$<?= number_format($product['price'], 2) ?></p>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php 
        endif;
    endif; 
    ?>

    <!-- CTA Section -->
    <section class="py-16 bg-blue-600 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-4">Need Help Choosing the Right Equipment?</h2>
            <p class="text-xl mb-8 text-blue-100">Our experts are ready to assist you</p>
            <a href="<?= url('contact.php') ?>" class="btn-white inline-block">
                Contact Us Today
            </a>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/quick-view-modal.php'; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
