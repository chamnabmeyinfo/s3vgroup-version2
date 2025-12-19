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
                
                // Background options
                $bgSize = $slider['background_size'] ?? 'cover';
                $bgPosition = $slider['background_position'] ?? 'center';
                $parallax = ($slider['parallax_effect'] ?? 0) ? 'true' : 'false';
                
                // Height options
                $slideHeight = $slider['slide_height'] ?? 'auto';
                $customHeight = $slider['custom_height'] ?? '';
                $heightStyle = '';
                if ($slideHeight === 'full') {
                    $heightStyle = 'height: 100vh;';
                } elseif ($slideHeight === 'custom' && !empty($customHeight)) {
                    $heightStyle = 'height: ' . escape($customHeight) . ';';
                }
                
                // Animation options
                $contentAnimation = $slider['content_animation'] ?? 'fade';
                $animationSpeed = $slider['animation_speed'] ?? 'normal';
                
                // Build background style
                // Handle stretch and fill specially - they need 100% 100%
                $bgSizeValue = $bgSize;
                if ($bgSize === 'stretch' || $bgSize === 'fill') {
                    $bgSizeValue = '100% 100%';
                }
                
                $bgStyle = "background-image: url('" . escape($bgImage) . "'); ";
                $bgStyle .= "background-size: " . escape($bgSizeValue) . " !important; ";
                $bgStyle .= "background-position: " . escape($bgPosition) . " !important; ";
                $bgStyle .= "background-repeat: no-repeat;";
                
                // Animation speed class (for child elements)
                $speedClass = 'animation-speed-' . $animationSpeed;
            ?>
                <div class="swiper-slide hero-slide" 
                     style="<?= $bgStyle ?> <?= $heightStyle ?>"
                     data-parallax="<?= $parallax ?>"
                     data-slide-index="<?= $index ?>">
                    <style>
                        @media (max-width: 768px) {
                            .hero-slide[data-slide-index="<?= $index ?>"] {
                                background-image: url('<?= escape($bgImageMobile) ?>') !important;
                                background-size: <?= escape($bgSizeValue) ?> !important;
                                background-position: <?= escape($bgPosition) ?> !important;
                            }
                        }
                    </style>
                    <div class="hero-slide-overlay light" style="<?= $overlayStyle ?>"></div>
                    <div class="container mx-auto px-4">
                        <div class="hero-slide-content max-w-4xl mx-auto" 
                             style="text-align: <?= $textAlign ?>; color: <?= $textColor ?>;">
                            <?php 
                            // Animation classes based on content_animation setting
                            $titleAnimClass = $contentAnimation !== 'none' ? 'animate-' . $contentAnimation : '';
                            $subtitleAnimClass = $contentAnimation !== 'none' ? 'animate-' . $contentAnimation . ' animation-delay-200' : '';
                            $descAnimClass = $contentAnimation !== 'none' ? 'animate-' . $contentAnimation . ' animation-delay-400' : '';
                            $buttonAnimClass = $contentAnimation !== 'none' ? 'animate-' . $contentAnimation . ' animation-delay-600' : '';
                            ?>
                            <?php if (!empty($slider['title'])): ?>
                                <h1 class="<?= $titleAnimClass ?> <?= $speedClass ?>">
                                    <?= escape($slider['title']) ?>
                                </h1>
                            <?php endif; ?>
                            
                            <?php if (!empty($slider['subtitle'])): ?>
                                <p class="<?= $subtitleAnimClass ?> <?= $speedClass ?>">
                                    <?= escape($slider['subtitle']) ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($slider['description'])): ?>
                                <p class="<?= $descAnimClass ?> <?= $speedClass ?>">
                                    <?= escape($slider['description']) ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($slider['button_text_1']) || !empty($slider['button_text_2'])): ?>
                                <div class="flex flex-col sm:flex-row gap-3 md:gap-4 justify-<?= $textAlign === 'center' ? 'center' : ($textAlign === 'right' ? 'end' : 'start') ?> <?= $buttonAnimClass ?> <?= $speedClass ?>">
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
            on: {
                init: function() {
                    // Trigger animations on initial load
                    const activeSlide = this.slides[this.activeIndex];
                    if (activeSlide) {
                        const animatedElements = activeSlide.querySelectorAll('.animate-fade, .animate-slide-up, .animate-slide-down, .animate-zoom');
                        animatedElements.forEach(function(el) {
                            el.style.opacity = '0';
                            setTimeout(function() {
                                el.style.opacity = '1';
                            }, 50);
                        });
                    }
                },
                slideChange: function() {
                    // Trigger animations on slide change
                    const activeSlide = this.slides[this.activeIndex];
                    if (activeSlide) {
                        const animatedElements = activeSlide.querySelectorAll('.animate-fade, .animate-slide-up, .animate-slide-down, .animate-zoom');
                        animatedElements.forEach(function(el) {
                            el.style.opacity = '0';
                            setTimeout(function() {
                                el.style.opacity = '1';
                            }, 50);
                        });
                    }
                }
            }
        });
        
        // Parallax effect for slides with data-parallax="true"
        const parallaxSlides = document.querySelectorAll('.hero-slide[data-parallax="true"]');
        if (parallaxSlides.length > 0 && window.innerWidth > 768) {
            let ticking = false;
            window.addEventListener('scroll', function() {
                if (!ticking) {
                    window.requestAnimationFrame(function() {
                        const scrolled = window.pageYOffset;
                        parallaxSlides.forEach(function(slide) {
                            const rect = slide.getBoundingClientRect();
                            if (rect.top < window.innerHeight && rect.bottom > 0) {
                                const rate = scrolled * 0.3;
                                slide.style.transform = 'translateY(' + rate + 'px)';
                            }
                        });
                        ticking = false;
                    });
                    ticking = true;
                }
            });
        }
    }
});
</script>

