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

// Check under construction mode
use App\Helpers\UnderConstruction;
UnderConstruction::show();

use App\Models\Product;
use App\Models\Category;

$productModel = new Product();
$categoryModel = new Category();

$featuredProducts = $productModel->getFeatured(8);
$allCategories = $categoryModel->getAll(true);
// Get only first 5 categories for homepage (minimal design)
$categories = array_slice($allCategories, 0, 5);

$pageTitle = 'Forklift & Equipment Pro - Industrial Equipment Solutions';
include __DIR__ . '/includes/header.php';
?>

<main>
    <!-- Hero Slider Section -->
    <section class="hero-slider relative">
        <div class="swiper heroSwiper">
            <div class="swiper-wrapper">
                <!-- Slide 1 -->
                <div class="swiper-slide hero-slide" style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.9), rgba(17, 24, 39, 0.8)), url('https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=1920') center/cover;">
                    <div class="container mx-auto px-4">
                        <div class="hero-slide-content max-w-4xl mx-auto text-center text-white py-20 md:py-32">
                            <h1 class="text-4xl md:text-6xl font-bold mb-6 animate-fade-in">
                                Premium Forklifts & Industrial Equipment
                            </h1>
                            <p class="text-xl md:text-2xl mb-8 text-blue-100">
                                Quality equipment for your warehouse and factory needs. Trusted by industry leaders worldwide.
                            </p>
                            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                <a href="<?= url('products.php') ?>" class="btn-primary inline-block transform hover:scale-105 transition-transform">
                                    <i class="fas fa-box mr-2"></i>Browse Products
                                </a>
                                <a href="<?= url('quote.php') ?>" class="btn-secondary inline-block transform hover:scale-105 transition-transform">
                                    <i class="fas fa-calculator mr-2"></i>Get a Quote
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Slide 2 -->
                <div class="swiper-slide hero-slide" style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.9), rgba(17, 24, 39, 0.8)), url('https://images.unsplash.com/photo-1565793298595-6a879b1d9492?w=1920') center/cover;">
                    <div class="container mx-auto px-4">
                        <div class="hero-slide-content max-w-4xl mx-auto text-center text-white py-20 md:py-32">
                            <h1 class="text-4xl md:text-6xl font-bold mb-6 animate-fade-in">
                                Industrial Solutions for Every Need
                            </h1>
                            <p class="text-xl md:text-2xl mb-8 text-blue-100">
                                From forklifts to pallet trucks, we have the equipment to power your operations.
                            </p>
                            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                <a href="<?= url('products.php') ?>" class="btn-primary inline-block transform hover:scale-105 transition-transform">
                                    <i class="fas fa-shopping-bag mr-2"></i>Shop Now
                                </a>
                                <a href="<?= url('contact.php') ?>" class="btn-secondary inline-block transform hover:scale-105 transition-transform">
                                    <i class="fas fa-phone mr-2"></i>Contact Us
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Slide 3 -->
                <div class="swiper-slide hero-slide" style="background: linear-gradient(135deg, rgba(30, 58, 138, 0.9), rgba(17, 24, 39, 0.8)), url('https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=1920') center/cover;">
                    <div class="container mx-auto px-4">
                        <div class="hero-slide-content max-w-4xl mx-auto text-center text-white py-20 md:py-32">
                            <h1 class="text-4xl md:text-6xl font-bold mb-6 animate-fade-in">
                                Expert Service & Support
                            </h1>
                            <p class="text-xl md:text-2xl mb-8 text-blue-100">
                                Our team of experts is here to help you find the perfect equipment for your business.
                            </p>
                            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                <a href="<?= url('products.php') ?>" class="btn-primary inline-block transform hover:scale-105 transition-transform">
                                    <i class="fas fa-search mr-2"></i>Explore Products
                                </a>
                                <a href="<?= url('quote.php') ?>" class="btn-secondary inline-block transform hover:scale-105 transition-transform">
                                    <i class="fas fa-file-alt mr-2"></i>Request Quote
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Navigation -->
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            
            <!-- Pagination -->
            <div class="swiper-pagination"></div>
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

    <!-- Categories Section - Minimal Design -->
    <?php if (!empty($categories)): ?>
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">
                    Shop by Category
                </h2>
                <p class="text-gray-600 max-w-xl mx-auto">
                    Browse our featured categories
                </p>
            </div>
            
            <!-- Categories Grid - Minimal -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-6 max-w-6xl mx-auto">
                <?php 
                $categoryIcons = [
                    'forklift' => 'fa-truck',
                    'electric' => 'fa-bolt',
                    'diesel' => 'fa-gas-pump',
                    'gas' => 'fa-fire',
                    'ic' => 'fa-cog',
                    'li-ion' => 'fa-battery-full',
                    'attachment' => 'fa-puzzle-piece',
                    'pallet' => 'fa-boxes',
                    'stacker' => 'fa-layer-group',
                    'reach' => 'fa-arrow-up',
                ];
                
                $colorClasses = [
                    ['bg' => 'bg-blue-100', 'bgHover' => 'bg-blue-500', 'text' => 'text-blue-600', 'border' => 'border-blue-500'],
                    ['bg' => 'bg-indigo-100', 'bgHover' => 'bg-indigo-500', 'text' => 'text-indigo-600', 'border' => 'border-indigo-500'],
                    ['bg' => 'bg-green-100', 'bgHover' => 'bg-green-500', 'text' => 'text-green-600', 'border' => 'border-green-500'],
                    ['bg' => 'bg-orange-100', 'bgHover' => 'bg-orange-500', 'text' => 'text-orange-600', 'border' => 'border-orange-500'],
                    ['bg' => 'bg-purple-100', 'bgHover' => 'bg-purple-500', 'text' => 'text-purple-600', 'border' => 'border-purple-500'],
                ];
                
                $index = 0;
                foreach ($categories as $category): 
                    $categoryName = strtolower($category['name']);
                    $icon = 'fa-box';
                    foreach ($categoryIcons as $key => $iconClass) {
                        if (strpos($categoryName, $key) !== false) {
                            $icon = $iconClass;
                            break;
                        }
                    }
                    $color = $colorClasses[$index % count($colorClasses)];
                    $index++;
                ?>
                <a href="<?= url('products.php?category=' . escape($category['slug'])) ?>" 
                   class="category-minimal group block bg-white border-2 border-gray-200 rounded-xl p-6 hover:<?= $color['border'] ?> hover:shadow-lg transition-all duration-300 text-center">
                    <!-- Icon -->
                    <div class="mb-4">
                        <div class="w-16 h-16 <?= $color['bg'] ?> rounded-xl flex items-center justify-center mx-auto group-hover:<?= $color['bgHover'] ?> transition-colors duration-300">
                            <i class="fas <?= $icon ?> <?= $color['text'] ?> text-2xl group-hover:text-white transition-colors duration-300"></i>
                        </div>
                    </div>
                    
                    <!-- Category Name -->
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 group-hover:<?= $color['text'] ?> transition-colors duration-300">
                        <?= escape($category['name']) ?>
                    </h3>
                    
                    <!-- Arrow -->
                    <div class="mt-4">
                        <i class="fas fa-arrow-right text-gray-400 group-hover:<?= $color['text'] ?> transform group-hover:translate-x-1 transition-all duration-300"></i>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            
            <!-- View All Link -->
            <div class="text-center mt-10">
                <a href="<?= url('products.php') ?>" 
                   class="text-blue-600 font-medium hover:text-blue-700 inline-flex items-center">
                    View All Categories
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
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
                                    <i class="fas fa-image text-4xl"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-lg mb-2 line-clamp-2"><?= escape($product['name']) ?></h3>
                            <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?= escape($product['short_description'] ?? '') ?></p>
                            <div class="flex justify-between items-center">
                                <?php if (!empty($product['sale_price']) && $product['sale_price'] > 0): ?>
                                    <div>
                                        <span class="text-lg font-bold text-blue-600">$<?= number_format((float)($product['sale_price'] ?? 0), 2) ?></span>
                                        <span class="text-sm text-gray-400 line-through ml-2">$<?= number_format((float)($product['price'] ?? 0), 2) ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-lg font-bold text-blue-600">$<?= number_format((float)($product['price'] ?? 0), 2) ?></span>
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
                                <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'%3E%3Crect fill='%23e5e7eb' width='400' height='300'/%3E%3C/svg%3E" 
                                     data-src="<?= asset('storage/uploads/' . escape($product['image'])) ?>"
                                     alt="<?= escape($product['name']) ?>" 
                                     class="lazy-load w-full h-full object-cover">
                            <?php else: ?>
                                <span class="text-gray-400">No Image</span>
                            <?php endif; ?>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-lg mb-2 line-clamp-2"><?= escape($product['name']) ?></h3>
                            <p class="text-lg font-bold text-blue-600">$<?= number_format((float)($product['price'] ?? 0), 2) ?></p>
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
