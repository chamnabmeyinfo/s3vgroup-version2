<?php
/**
 * Hero Slider Component
 * Displays dynamic hero sliders from database
 */

use App\Database\Connection;

try {
    $db = Connection::getInstance();
    $sliders = $db->fetchAll(
        "SELECT * FROM hero_sliders WHERE is_active = 1 ORDER BY sort_order ASC, id ASC"
    );
} catch (\Exception $e) {
    $sliders = [];
}

if (empty($sliders)) {
    // Fallback to default slider if no sliders in database
    $sliders = [
        [
            'title' => 'Premium Forklifts & Industrial Equipment',
            'subtitle' => 'Quality equipment for your warehouse and factory needs',
            'description' => 'Trusted by industry leaders worldwide',
            'image' => 'https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=1920',
            'image_mobile' => null,
            'button_text_1' => 'Browse Products',
            'button_link_1' => url('products.php'),
            'button_text_2' => 'Get a Quote',
            'button_link_2' => url('quote.php'),
            'overlay_color' => 'rgba(30, 58, 138, 0.9)',
            'text_alignment' => 'center',
            'text_color' => '#ffffff'
        ]
    ];
}
?>

<!-- Hero Slider Section -->
<section class="hero-slider relative" id="heroSlider">
    <div class="swiper heroSwiper">
        <div class="swiper-wrapper">
            <?php foreach ($sliders as $index => $slider): 
                $overlayStyle = !empty($slider['overlay_gradient']) 
                    ? "background: {$slider['overlay_gradient']};" 
                    : "background: linear-gradient(135deg, {$slider['overlay_color']}, rgba(17, 24, 39, 0.8));";
                
                $bgImage = !empty($slider['image']) ? image_url($slider['image']) : 'https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=1920';
                $bgImageMobile = !empty($slider['image_mobile']) ? image_url($slider['image_mobile']) : $bgImage;
                
                $textAlign = $slider['text_alignment'] ?? 'center';
                $textColor = $slider['text_color'] ?? '#ffffff';
            ?>
                <div class="swiper-slide hero-slide" 
                     style="background-image: url('<?= escape($bgImage) ?>');">
                    <style>
                        @media (max-width: 768px) {
                            .hero-slide:nth-child(<?= $index + 1 ?>) {
                                background-image: url('<?= escape($bgImageMobile) ?>') !important;
                            }
                        }
                    </style>
                    <div class="hero-slide-overlay" style="<?= $overlayStyle ?>"></div>
                    <div class="container mx-auto px-4">
                        <div class="hero-slide-content max-w-4xl mx-auto py-20 md:py-32" 
                             style="text-align: <?= $textAlign ?>; color: <?= $textColor ?>;">
                            <?php if (!empty($slider['title'])): ?>
                                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold mb-4 md:mb-6 animate-fade-in-up" 
                                    style="color: <?= $textColor ?>;">
                                    <?= escape($slider['title']) ?>
                                </h1>
                            <?php endif; ?>
                            
                            <?php if (!empty($slider['subtitle'])): ?>
                                <p class="text-lg sm:text-xl md:text-2xl mb-4 md:mb-6 animate-fade-in-up animation-delay-200" 
                                   style="color: <?= $textColor ?>; opacity: 0.95;">
                                    <?= escape($slider['subtitle']) ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($slider['description'])): ?>
                                <p class="text-base sm:text-lg md:text-xl mb-6 md:mb-8 animate-fade-in-up animation-delay-400" 
                                   style="color: <?= $textColor ?>; opacity: 0.9;">
                                    <?= escape($slider['description']) ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($slider['button_text_1']) || !empty($slider['button_text_2'])): ?>
                                <div class="flex flex-col sm:flex-row gap-3 md:gap-4 justify-<?= $textAlign === 'center' ? 'center' : ($textAlign === 'right' ? 'end' : 'start') ?> animate-fade-in-up animation-delay-600">
                                    <?php if (!empty($slider['button_text_1'])): ?>
                                        <a href="<?= escape($slider['button_link_1'] ?? '#') ?>" 
                                           class="btn-primary inline-block transform hover:scale-105 transition-all shadow-lg hover:shadow-xl">
                                            <i class="fas fa-<?= strpos(strtolower($slider['button_text_1']), 'browse') !== false || strpos(strtolower($slider['button_text_1']), 'shop') !== false ? 'box' : (strpos(strtolower($slider['button_text_1']), 'quote') !== false ? 'calculator' : 'arrow-right') ?> mr-2"></i>
                                            <?= escape($slider['button_text_1']) ?>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($slider['button_text_2'])): ?>
                                        <a href="<?= escape($slider['button_link_2'] ?? '#') ?>" 
                                           class="btn-secondary inline-block transform hover:scale-105 transition-all shadow-lg hover:shadow-xl">
                                            <i class="fas fa-<?= strpos(strtolower($slider['button_text_2']), 'quote') !== false ? 'calculator' : (strpos(strtolower($slider['button_text_2']), 'contact') !== false ? 'phone' : 'arrow-right') ?> mr-2"></i>
                                            <?= escape($slider['button_text_2']) ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Navigation -->
        <div class="swiper-button-next hero-nav-next"></div>
        <div class="swiper-button-prev hero-nav-prev"></div>
        
        <!-- Pagination -->
        <div class="swiper-pagination hero-pagination"></div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Swiper for Hero Slider
    if (typeof Swiper !== 'undefined') {
        const heroSwiper = new Swiper('.heroSwiper', {
            slidesPerView: 1,
            spaceBetween: 0,
            loop: <?= count($sliders) > 1 ? 'true' : 'false' ?>,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            speed: 1000,
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },
            pagination: {
                el: '.hero-pagination',
                clickable: true,
                dynamicBullets: true,
            },
            navigation: {
                nextEl: '.hero-nav-next',
                prevEl: '.hero-nav-prev',
            },
            keyboard: {
                enabled: true,
            },
            a11y: {
                prevSlideMessage: 'Previous slide',
                nextSlideMessage: 'Next slide',
            },
        });
    }
});
</script>

