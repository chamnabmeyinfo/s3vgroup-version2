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
    <?php
    use App\Models\HeroSlider;
    $heroSliderModel = new HeroSlider();
    $heroSlides = $heroSliderModel->getAll(true); // Get only active slides
    
    // Get hero slider settings
    $settingsData = db()->fetchAll("SELECT `key`, value FROM settings WHERE `key` LIKE 'hero_slider_%'");
    $sliderSettings = [];
    foreach ($settingsData as $setting) {
        $sliderSettings[$setting['key']] = $setting['value'];
    }
    
    // Default values
    $defaultSettings = [
        'hero_slider_autoplay_delay' => 5000,
        'hero_slider_default_transparency' => 0.02,
        'hero_slider_show_arrows' => 1,
        'hero_slider_show_dots' => 1,
        'hero_slider_show_progress' => 1,
        'hero_slider_pause_on_hover' => 1,
        'hero_slider_transition_speed' => 800,
        'hero_slider_enable_keyboard' => 1,
        'hero_slider_enable_touch' => 1,
    ];
    
    foreach ($defaultSettings as $key => $default) {
        if (!isset($sliderSettings[$key])) {
            $sliderSettings[$key] = $default;
        }
    }
    
    if (!empty($heroSlides)):
    ?>
    <section class="hero-slider">
        <div class="hero-slider-container">
            <?php foreach ($heroSlides as $index => $slide): 
                // Determine responsive image
                $isMobile = isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/Mobile|Android|iPhone/i', $_SERVER['HTTP_USER_AGENT']);
                $isTablet = isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/Tablet|iPad/i', $_SERVER['HTTP_USER_AGENT']);
                
                $bgImage = null;
                if ($isMobile && !empty($slide['image_mobile'])) {
                    $bgImage = $slide['image_mobile'];
                } elseif ($isTablet && !empty($slide['image_tablet'])) {
                    $bgImage = $slide['image_tablet'];
                } elseif (!empty($slide['background_image'])) {
                    $bgImage = $slide['background_image'];
                }
                
                // Build background style
                $bgStyle = '';
                $hasVideo = !empty($slide['video_background']);
                
                if ($hasVideo) {
                    // Video background - style will be handled by video element
                } elseif ($bgImage) {
                    // Show image only - no gradient overlay
                    $bgStyle = "background-image: url('" . escape(image_url($bgImage)) . "');";
                } elseif (!empty($slide['background_gradient_start']) && !empty($slide['background_gradient_end'])) {
                    // Only use gradient if no image is set
                    $bgStyle = "background-image: linear-gradient(135deg, " . 
                               escape($slide['background_gradient_start']) . ", " . 
                               escape($slide['background_gradient_end']) . ");";
                } else {
                    // No default gradient - just show the background color
                    $bgStyle = "";
                }
                
                // Get slide-specific settings
                $transition = $slide['transition_effect'] ?? 'fade';
                $textAnimation = $slide['text_animation'] ?? 'fadeInUp';
                $layout = $slide['content_layout'] ?? 'center';
                $template = $slide['template'] ?? 'default';
                $parallax = !empty($slide['parallax_enabled']);
                $overlayPattern = $slide['overlay_pattern'] ?? '';
                $badgeText = $slide['badge_text'] ?? '';
                $badgeColor = $slide['badge_color'] ?? 'blue';
                $countdownEnabled = !empty($slide['countdown_enabled']);
                $countdownDate = $slide['countdown_date'] ?? '';
                $socialSharing = !empty($slide['social_sharing']);
                $darkMode = !empty($slide['dark_mode']);
                $customFont = $slide['custom_font'] ?? '';
                
                // Use mobile content if on mobile
                $displayTitle = ($isMobile && !empty($slide['mobile_title'])) ? $slide['mobile_title'] : $slide['title'];
                $displayDescription = ($isMobile && !empty($slide['mobile_description'])) ? $slide['mobile_description'] : ($slide['description'] ?? '');
                
                // Button styles
                $button1Style = $slide['button1_style'] ?? 'primary';
                $button2Style = $slide['button2_style'] ?? 'secondary';
            ?>
            <div class="hero-slide <?= $index === 0 ? 'active' : '' ?> template-<?= escape($template) ?> <?= $darkMode ? 'dark-mode' : '' ?>"
                 data-transition="<?= escape($transition) ?>"
                 data-text-animation="<?= escape($textAnimation) ?>"
                 data-parallax="<?= $parallax ? 'true' : 'false' ?>"
                 data-slide-id="<?= $slide['id'] ?>"
                 style="<?= $bgStyle ?>">
                
                <?php if ($hasVideo): ?>
                    <video class="hero-video-background" autoplay muted loop playsinline
                           <?= !empty($slide['video_poster']) ? 'poster="' . escape(image_url($slide['video_poster'])) . '"' : '' ?>>
                        <source src="<?= escape(image_url($slide['video_background'])) ?>" type="video/mp4">
                    </video>
                <?php endif; ?>
                
                <?php if ($parallax && $bgImage): ?>
                    <div class="hero-slide-bg" style="background-image: url('<?= escape(image_url($bgImage)) ?>');"></div>
                <?php endif; ?>
                
                <?php if ($overlayPattern): ?>
                    <div class="hero-slide-overlay <?= escape($overlayPattern) ?>" style="position: absolute; inset: 0; z-index: 1;"></div>
                <?php endif; ?>
                
                <?php if ($badgeText): ?>
                    <div class="hero-slide-badge badge-<?= escape($badgeColor) ?>">
                        <?= escape($badgeText) ?>
                    </div>
                <?php endif; ?>
                
                <div class="hero-slide-content layout-<?= escape($layout) ?>" 
                     style="background: transparent; <?= $customFont ? 'font-family: ' . escape($customFont) . ';' : '' ?>">
                    <h1><?= escape($displayTitle) ?></h1>
                    <?php if (!empty($displayDescription)): ?>
                        <p><?= escape($displayDescription) ?></p>
                    <?php endif; ?>
                    
                    <?php if ($countdownEnabled && $countdownDate): ?>
                        <div class="hero-countdown" data-countdown-to="<?= escape($countdownDate) ?>">
                            <!-- Countdown will be populated by JavaScript -->
                        </div>
                    <?php endif; ?>
                    
                    <div class="hero-slide-buttons">
                        <?php if (!empty($slide['button1_text']) && !empty($slide['button1_url'])): ?>
                            <a href="<?= url($slide['button1_url']) ?>" 
                               class="hero-slide-btn hero-slide-btn-<?= escape($button1Style) ?>">
                                <?= escape($slide['button1_text']) ?>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($slide['button2_text']) && !empty($slide['button2_url'])): ?>
                            <a href="<?= url($slide['button2_url']) ?>" 
                               class="hero-slide-btn hero-slide-btn-<?= escape($button2Style) ?>">
                                <?= escape($slide['button2_text']) ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($socialSharing): ?>
                    <div class="hero-social-share">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(url()) ?>" target="_blank" title="Share on Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode(url()) ?>" target="_blank" title="Share on Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode(url()) ?>" target="_blank" title="Share on LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="https://wa.me/?text=<?= urlencode($displayTitle . ' - ' . url()) ?>" target="_blank" title="Share on WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Navigation Arrows -->
        <?php if ($sliderSettings['hero_slider_show_arrows']): ?>
        <button class="hero-slider-nav prev" aria-label="Previous slide">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="hero-slider-nav next" aria-label="Next slide">
            <i class="fas fa-chevron-right"></i>
        </button>
        <?php endif; ?>
        
        <!-- Dots Navigation -->
        <?php if ($sliderSettings['hero_slider_show_dots'] && count($heroSlides) > 1): ?>
        <div class="hero-slider-dots">
            <?php foreach ($heroSlides as $index => $slide): ?>
                <button class="hero-slider-dot <?= $index === 0 ? 'active' : '' ?>" aria-label="Slide <?= $index + 1 ?>"></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <!-- Progress Bar -->
        <?php if ($sliderSettings['hero_slider_show_progress']): ?>
        <div class="hero-slider-progress">
            <div class="hero-slider-progress-bar"></div>
        </div>
        <?php endif; ?>
        
        <!-- Pass settings to JavaScript and CSS -->
        <style>
        .hero-slider {
            --transition-speed: <?= (int)$sliderSettings['hero_slider_transition_speed'] ?>ms;
        }
        </style>
        <script>
        window.heroSliderSettings = {
            autoplayDelay: <?= (int)$sliderSettings['hero_slider_autoplay_delay'] ?>,
            pauseOnHover: <?= $sliderSettings['hero_slider_pause_on_hover'] ? 'true' : 'false' ?>,
            transitionSpeed: <?= (int)$sliderSettings['hero_slider_transition_speed'] ?>,
            enableKeyboard: <?= $sliderSettings['hero_slider_enable_keyboard'] ? 'true' : 'false' ?>,
            enableTouch: <?= $sliderSettings['hero_slider_enable_touch'] ? 'true' : 'false' ?>
        };
        </script>
    </section>
    <?php endif; ?>

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
