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
    <section class="hero-slider">
        <div class="hero-slider-container">
            <!-- Slide 1 -->
            <div class="hero-slide active" style="background-image: linear-gradient(135deg, rgba(37, 99, 235, 0.9), rgba(79, 70, 229, 0.9)), url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 1920 1080\'%3E%3Crect fill=\'%23e5e7eb\' width=\'1920\' height=\'1080\'/%3E%3C/svg%3E');">
                <div class="hero-slide-content">
                    <h1>Premium Forklifts & Industrial Equipment</h1>
                    <p>Discover our extensive range of high-quality forklifts, material handling equipment, and industrial solutions designed to power your business.</p>
                    <div class="hero-slide-buttons">
                        <a href="<?= url('products.php') ?>" class="hero-slide-btn hero-slide-btn-primary">
                            <i class="fas fa-shopping-bag"></i>
                            Shop Now
                        </a>
                        <a href="<?= url('quote.php') ?>" class="hero-slide-btn hero-slide-btn-secondary">
                            <i class="fas fa-calculator"></i>
                            Get Quote
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Slide 2 -->
            <div class="hero-slide" style="background-image: linear-gradient(135deg, rgba(16, 185, 129, 0.9), rgba(5, 150, 105, 0.9)), url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 1920 1080\'%3E%3Crect fill=\'%23e5e7eb\' width=\'1920\' height=\'1080\'/%3E%3C/svg%3E');">
                <div class="hero-slide-content">
                    <h1>Expert Support & Maintenance</h1>
                    <p>24/7 customer support and professional maintenance services to keep your equipment running at peak performance.</p>
                    <div class="hero-slide-buttons">
                        <a href="<?= url('contact.php') ?>" class="hero-slide-btn hero-slide-btn-primary">
                            <i class="fas fa-headset"></i>
                            Contact Us
                        </a>
                        <a href="<?= url('products.php') ?>" class="hero-slide-btn hero-slide-btn-secondary">
                            <i class="fas fa-box"></i>
                            Browse Products
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Slide 3 -->
            <div class="hero-slide" style="background-image: linear-gradient(135deg, rgba(139, 92, 246, 0.9), rgba(124, 58, 237, 0.9)), url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 1920 1080\'%3E%3Crect fill=\'%23e5e7eb\' width=\'1920\' height=\'1080\'/%3E%3C/svg%3E');">
                <div class="hero-slide-content">
                    <h1>Quality You Can Trust</h1>
                    <p>All our equipment is thoroughly inspected and certified to meet the highest industry standards for safety and performance.</p>
                    <div class="hero-slide-buttons">
                        <a href="<?= url('products.php') ?>" class="hero-slide-btn hero-slide-btn-primary">
                            <i class="fas fa-check-circle"></i>
                            Explore Quality
                        </a>
                        <a href="<?= url('testimonials.php') ?>" class="hero-slide-btn hero-slide-btn-secondary">
                            <i class="fas fa-star"></i>
                            Read Reviews
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Slide 4 -->
            <div class="hero-slide" style="background-image: linear-gradient(135deg, rgba(236, 72, 153, 0.9), rgba(219, 39, 119, 0.9)), url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 1920 1080\'%3E%3Crect fill=\'%23e5e7eb\' width=\'1920\' height=\'1080\'/%3E%3C/svg%3E');">
                <div class="hero-slide-content">
                    <h1>Fast Delivery & Installation</h1>
                    <p>Quick shipping and professional installation services to get your equipment up and running when you need it most.</p>
                    <div class="hero-slide-buttons">
                        <a href="<?= url('products.php') ?>" class="hero-slide-btn hero-slide-btn-primary">
                            <i class="fas fa-shipping-fast"></i>
                            Shop Now
                        </a>
                        <a href="<?= url('contact.php') ?>" class="hero-slide-btn hero-slide-btn-secondary">
                            <i class="fas fa-phone"></i>
                            Learn More
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Navigation Arrows -->
        <button class="hero-slider-nav prev" aria-label="Previous slide">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="hero-slider-nav next" aria-label="Next slide">
            <i class="fas fa-chevron-right"></i>
        </button>
        
        <!-- Dots Navigation -->
        <div class="hero-slider-dots">
            <button class="hero-slider-dot active" aria-label="Slide 1"></button>
            <button class="hero-slider-dot" aria-label="Slide 2"></button>
            <button class="hero-slider-dot" aria-label="Slide 3"></button>
            <button class="hero-slider-dot" aria-label="Slide 4"></button>
        </div>
        
        <!-- Progress Bar -->
        <div class="hero-slider-progress">
            <div class="hero-slider-progress-bar"></div>
        </div>
    </section>

    <!-- Features Section - Modern Design -->
    <section class="py-16 md:py-20 bg-gradient-to-b from-white to-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12 md:mb-16">
                <h2 class="text-3xl md:text-4xl font-bold mb-4 text-gray-800">
                    Why Choose Us
                </h2>
                <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                    We're committed to providing the best service and quality equipment for your business needs
                </p>
            </div>
            <div class="grid md:grid-cols-3 gap-8 md:gap-12">
                <div class="group text-center bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-6 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 shadow-lg">
                        <i class="fas fa-check-circle text-white text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3 text-gray-800 group-hover:text-blue-600 transition-colors">Quality Assured</h3>
                    <p class="text-gray-600 leading-relaxed">All equipment is thoroughly inspected and certified to meet the highest industry standards</p>
                </div>
                <div class="group text-center bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100">
                    <div class="w-20 h-20 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-6 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 shadow-lg">
                        <i class="fas fa-shipping-fast text-white text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3 text-gray-800 group-hover:text-green-600 transition-colors">Fast Delivery</h3>
                    <p class="text-gray-600 leading-relaxed">Quick shipping and reliable delivery service to get your equipment when you need it</p>
                </div>
                <div class="group text-center bg-white rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100">
                    <div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center mx-auto mb-6 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 shadow-lg">
                        <i class="fas fa-headset text-white text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-3 text-gray-800 group-hover:text-purple-600 transition-colors">Expert Support</h3>
                    <p class="text-gray-600 leading-relaxed">24/7 customer support and maintenance services from our experienced team</p>
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
            
            <!-- Categories Grid - Modern with Images -->
            <div class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6 max-w-7xl mx-auto">
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
                   class="category-modern group block bg-white border-2 border-gray-200 rounded-2xl overflow-hidden hover:<?= $color['border'] ?> hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                    <!-- Image or Icon -->
                    <div class="category-image-wrapper relative h-48 overflow-hidden bg-gradient-to-br <?= $color['bg'] ?> group-hover:<?= $color['bgHover'] ?> transition-all duration-300">
                        <?php if (!empty($category['image'])): ?>
                            <img src="<?= escape(image_url($category['image'])) ?>" 
                                 alt="<?= escape($category['name']) ?>"
                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                 loading="lazy"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="category-icon-fallback absolute inset-0 items-center justify-center hidden">
                                <i class="fas <?= $icon ?> <?= $color['text'] ?> text-5xl group-hover:text-white transition-colors duration-300"></i>
                            </div>
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="fas <?= $icon ?> <?= $color['text'] ?> text-5xl group-hover:text-white transition-colors duration-300"></i>
                            </div>
                        <?php endif; ?>
                        <!-- Overlay on hover -->
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-all duration-300"></div>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-6">
                        <!-- Category Name -->
                        <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:<?= $color['text'] ?> transition-colors duration-300">
                            <?= escape($category['name']) ?>
                        </h3>
                        
                        <!-- Short Description -->
                        <?php 
                        // Use short_description if available, otherwise fall back to description
                        $shortText = $category['short_description'] ?? null;
                        if (empty($shortText) && !empty($category['description'])) {
                            $shortText = substr($category['description'], 0, 100);
                        }
                        ?>
                        <?php if (!empty($shortText)): ?>
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2 leading-relaxed">
                                <?= escape($shortText) ?><?= !empty($category['description']) && strlen($category['description']) > 100 && empty($category['short_description']) ? '...' : '' ?>
                            </p>
                        <?php else: ?>
                            <p class="text-sm text-gray-500 mb-4 italic">
                                Explore our <?= strtolower(escape($category['name'])) ?> collection
                            </p>
                        <?php endif; ?>
                        
                        <!-- Arrow Button -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold <?= $color['text'] ?> opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                View Products
                            </span>
                            <div class="w-10 h-10 <?= $color['bg'] ?> rounded-full flex items-center justify-center group-hover:<?= $color['bgHover'] ?> transition-all duration-300 transform group-hover:scale-110 group-hover:rotate-[-5deg]">
                                <i class="fas fa-arrow-right <?= $color['text'] ?> group-hover:text-white transform group-hover:translate-x-1 transition-all duration-300"></i>
                            </div>
                        </div>
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
                        <div class="w-full aspect-[10/7] bg-gray-200 flex items-center justify-center overflow-hidden relative">
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

    <!-- CTA Section - Modern Design -->
    <section class="py-16 md:py-20 bg-gradient-to-br from-blue-600 via-indigo-700 to-purple-700 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
        </div>
        <div class="container mx-auto px-4 text-center relative z-10">
            <div class="max-w-3xl mx-auto">
                <div class="mb-6">
                    <i class="fas fa-question-circle text-6xl md:text-7xl mb-6 opacity-80"></i>
                </div>
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-4 leading-tight">
                    Need Help Choosing the Right Equipment?
                </h2>
                <p class="text-xl md:text-2xl mb-10 text-blue-100 leading-relaxed">
                    Our expert team is ready to assist you in finding the perfect solution for your business needs
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="<?= url('contact.php') ?>" class="bg-white text-blue-600 px-8 py-4 rounded-xl font-bold hover:bg-gray-100 transform hover:scale-105 transition-all duration-300 shadow-2xl hover:shadow-3xl inline-flex items-center justify-center">
                        <i class="fas fa-envelope mr-2"></i>Contact Us Today
                    </a>
                    <a href="<?= url('quote.php') ?>" class="bg-blue-500/20 backdrop-blur-sm border-2 border-white/30 text-white px-8 py-4 rounded-xl font-bold hover:bg-blue-500/30 transform hover:scale-105 transition-all duration-300 shadow-xl hover:shadow-2xl inline-flex items-center justify-center">
                        <i class="fas fa-calculator mr-2"></i>Get a Free Quote
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/quick-view-modal.php'; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
