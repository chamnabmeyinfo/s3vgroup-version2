<?php
/**
 * Hero Slider Component - Ultra Modern Premium Design
 * Cutting-edge animations, effects, and modern UI patterns
 */

use App\Database\Connection;
use App\Models\Setting;

// ============================================
// 1. LOAD GLOBAL SETTINGS (with safe defaults)
// ============================================
$globalSettings = [
    'autoplay_enabled' => 1,
    'autoplay_delay' => 5000,
    'pause_on_hover' => 1,
    'show_navigation' => 1,
    'show_pagination' => 1,
    'navigation_mobile' => 0,
    'transition_effect' => 'fade',
    'transition_speed' => 1000,
    'loop' => 1,
    'height' => 'auto',
    'custom_height' => '',
    'show_counter' => 1,
    'show_progress' => 1,
    'keyboard_enabled' => 1,
    'mousewheel_enabled' => 0,
    'lazy_loading' => 1,
    'preload_images' => 0,
    'ken_burns_enabled' => 1,
    'parallax_enabled' => 1,
    'particle_effects' => 0,
    'animated_gradients' => 1,
    'morphing_shapes' => 1,
];

// Try to load from database, but never fail if it doesn't work
try {
    if (class_exists('App\Models\Setting')) {
        $settingModel = new Setting();
        $globalSettings['autoplay_enabled'] = (bool)$settingModel->get('hero_slider_autoplay_enabled', 1);
        $globalSettings['autoplay_delay'] = max(1000, min(30000, (int)$settingModel->get('hero_slider_autoplay_delay', 5000)));
        $globalSettings['pause_on_hover'] = (bool)$settingModel->get('hero_slider_pause_on_hover', 1);
        $globalSettings['show_navigation'] = (bool)$settingModel->get('hero_slider_show_navigation', 1);
        $globalSettings['show_pagination'] = (bool)$settingModel->get('hero_slider_show_pagination', 1);
        $globalSettings['navigation_mobile'] = (bool)$settingModel->get('hero_slider_navigation_mobile', 0);
        $globalSettings['transition_effect'] = $settingModel->get('hero_slider_transition_effect', 'fade');
        $globalSettings['transition_speed'] = max(100, min(5000, (int)$settingModel->get('hero_slider_transition_speed', 1000)));
        $globalSettings['loop'] = (bool)$settingModel->get('hero_slider_loop', 1);
        $globalSettings['height'] = $settingModel->get('hero_slider_height', 'auto');
        $globalSettings['custom_height'] = $settingModel->get('hero_slider_custom_height', '');
        $globalSettings['show_counter'] = (bool)$settingModel->get('hero_slider_show_counter', 1);
        $globalSettings['show_progress'] = (bool)$settingModel->get('hero_slider_show_progress', 1);
        $globalSettings['keyboard_enabled'] = (bool)$settingModel->get('hero_slider_keyboard_enabled', 1);
        $globalSettings['mousewheel_enabled'] = (bool)$settingModel->get('hero_slider_mousewheel_enabled', 0);
        $globalSettings['lazy_loading'] = (bool)$settingModel->get('hero_slider_lazy_loading', 1);
        $globalSettings['preload_images'] = (bool)$settingModel->get('hero_slider_preload_images', 0);
        $globalSettings['ken_burns_enabled'] = (bool)$settingModel->get('hero_slider_ken_burns', 1);
        $globalSettings['parallax_enabled'] = (bool)$settingModel->get('hero_slider_parallax', 1);
        $globalSettings['particle_effects'] = (bool)$settingModel->get('hero_slider_particles', 0);
        $globalSettings['animated_gradients'] = (bool)$settingModel->get('hero_slider_animated_gradients', 1);
        $globalSettings['morphing_shapes'] = (bool)$settingModel->get('hero_slider_morphing_shapes', 1);
    }
} catch (\Exception $e) {
    // Silently use defaults - never break the page
}

// ============================================
// 2. LOAD SLIDERS FROM DATABASE
// ============================================
$sliders = [];
try {
    if (class_exists('App\Database\Connection')) {
        $db = Connection::getInstance();
        $sliders = $db->fetchAll(
            "SELECT * FROM hero_sliders WHERE is_active = 1 ORDER BY sort_order ASC, id ASC LIMIT 20"
        );
    }
} catch (\Exception $e) {
    // Database error - use fallback
    $sliders = [];
}

// Fallback if no sliders found
if (empty($sliders)) {
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
            'text_color' => '#ffffff',
            'slide_height' => 'auto',
            'background_size' => 'cover',
            'background_position' => 'center',
            'content_animation' => 'fade',
            'animation_speed' => 'normal',
            'button_style_1' => 'primary',
            'button_style_2' => 'secondary',
        ]
    ];
}

// Ensure we have at least one slider
$sliderCount = count($sliders);
$hasMultipleSlides = $sliderCount > 1;
?>

<!-- Hero Slider Section - Ultra Modern Premium -->
<section class="hero-slider-premium" id="heroSliderSection" data-slide-count="<?= $sliderCount ?>">
    <!-- Loading State -->
    <div class="hero-slider-loading" id="heroSliderLoading">
        <div class="loading-spinner"></div>
    </div>
    
    <!-- Animated Background Shapes -->
    <?php if ($globalSettings['morphing_shapes']): ?>
    <div class="hero-animated-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
    </div>
    <?php endif; ?>
    
    <!-- Slider Container -->
    <div class="hero-slider-wrapper">
        <div class="swiper heroSwiper" id="heroSwiper">
            <div class="swiper-wrapper">
                <?php foreach ($sliders as $index => $slider): 
                    // Safe defaults for all fields
                    $title = !empty($slider['title']) ? escape($slider['title']) : '';
                    $subtitle = !empty($slider['subtitle']) ? escape($slider['subtitle']) : '';
                    $description = !empty($slider['description']) ? escape($slider['description']) : '';
                    
                    // Images with fallbacks
                    $bgImage = !empty($slider['image']) ? image_url($slider['image']) : 'https://images.unsplash.com/photo-1581092160562-40aa08e78837?w=1920';
                    $bgImageMobile = !empty($slider['image_mobile']) ? image_url($slider['image_mobile']) : $bgImage;
                    
                    // Video support
                    $hasVideo = false;
                    $videoUrl = '';
                    $videoMobileUrl = '';
                    if (isset($slider['video_url']) && !empty($slider['video_url'])) {
                        $hasVideo = true;
                        $videoUrl = escape($slider['video_url']);
                        $videoMobileUrl = !empty($slider['video_mobile_url']) ? escape($slider['video_mobile_url']) : $videoUrl;
                    }
                    
                    // Styling options
                    $textAlign = !empty($slider['text_alignment']) ? $slider['text_alignment'] : 'center';
                    $textColor = !empty($slider['text_color']) ? escape($slider['text_color']) : '#ffffff';
                    
                    // Overlay with animated gradient
                    $overlayStyle = '';
                    if (!empty($slider['overlay_gradient'])) {
                        $overlayStyle = "background: " . escape($slider['overlay_gradient']) . ";";
                    } elseif (!empty($slider['overlay_color'])) {
                        $overlayColor = escape($slider['overlay_color']);
                        $overlayStyle = "background: linear-gradient(135deg, {$overlayColor}, rgba(17, 24, 39, 0.8));";
                    } else {
                        $overlayStyle = "background: linear-gradient(135deg, rgba(30, 58, 138, 0.85), rgba(17, 24, 39, 0.75));";
                    }
                    
                    // Background options
                    $bgSize = !empty($slider['background_size']) ? $slider['background_size'] : 'cover';
                    $bgPosition = !empty($slider['background_position']) ? escape($slider['background_position']) : 'center';
                    $parallax = (isset($slider['parallax_effect']) && $slider['parallax_effect']) ? 'true' : 'false';
                    
                    // Ken Burns effect
                    $kenBurns = !empty($slider['ken_burns']) ? $slider['ken_burns'] : ($globalSettings['ken_burns_enabled'] ? 'zoom' : 'none');
                    
                    // Height
                    $slideHeight = !empty($slider['slide_height']) ? $slider['slide_height'] : $globalSettings['height'];
                    $customHeight = !empty($slider['custom_height']) ? escape($slider['custom_height']) : $globalSettings['custom_height'];
                    $heightStyle = '';
                    if ($slideHeight === 'full') {
                        $heightStyle = 'height: 100vh;';
                    } elseif ($slideHeight === 'custom' && !empty($customHeight)) {
                        $heightStyle = 'height: ' . $customHeight . ';';
                    }
                    
                    // Animation
                    $contentAnimation = !empty($slider['content_animation']) ? $slider['content_animation'] : 'fade';
                    $animationSpeed = !empty($slider['animation_speed']) ? $slider['animation_speed'] : 'normal';
                    $speedClass = 'animation-speed-' . $animationSpeed;
                    
                    // Button styles
                    $buttonStyle1 = !empty($slider['button_style_1']) ? $slider['button_style_1'] : 'primary';
                    $buttonStyle2 = !empty($slider['button_style_2']) ? $slider['button_style_2'] : 'secondary';
                    
                    // Build background style (only if no video)
                    $bgStyle = '';
                    if (!$hasVideo) {
                        $bgSizeValue = $bgSize;
                        if ($bgSize === 'stretch' || $bgSize === 'fill') {
                            $bgSizeValue = '100% 100%';
                        }
                        $bgStyle = "background-image: url('{$bgImage}'); background-size: {$bgSizeValue}; background-position: {$bgPosition}; background-repeat: no-repeat;";
                    }
                    
                    // Animation classes - Premium style
                    $titleAnimClass = ($contentAnimation !== 'none') ? 'premium-animate-' . $contentAnimation : '';
                    $subtitleAnimClass = ($contentAnimation !== 'none') ? 'premium-animate-' . $contentAnimation . ' premium-delay-200' : '';
                    $descAnimClass = ($contentAnimation !== 'none') ? 'premium-animate-' . $contentAnimation . ' premium-delay-400' : '';
                    $buttonAnimClass = ($contentAnimation !== 'none') ? 'premium-animate-' . $contentAnimation . ' premium-delay-600' : '';
                    
                    // Button alignment
                    $buttonJustify = 'justify-center';
                    if ($textAlign === 'left') {
                        $buttonJustify = 'justify-start';
                    } elseif ($textAlign === 'right') {
                        $buttonJustify = 'justify-end';
                    }
                ?>
                <div class="swiper-slide hero-slide-premium" 
                     data-slide-index="<?= $index ?>"
                     data-has-video="<?= $hasVideo ? 'true' : 'false' ?>"
                     data-parallax="<?= $parallax ?>"
                     data-ken-burns="<?= $kenBurns ?>"
                     style="<?= $bgStyle ?> <?= $heightStyle ?>">
                    
                    <!-- Background Layer -->
                    <div class="hero-bg-layer">
                        <?php if ($hasVideo): ?>
                            <!-- Video Background -->
                            <video class="hero-video-bg" 
                                   autoplay 
                                   muted 
                                   loop 
                                   playsinline
                                   preload="auto"
                                   poster="<?= $bgImage ?>">
                                <source src="<?= $videoUrl ?>" type="video/mp4" media="(min-width: 769px)">
                                <?php if (!empty($videoMobileUrl) && $videoMobileUrl !== $videoUrl): ?>
                                    <source src="<?= $videoMobileUrl ?>" type="video/mp4" media="(max-width: 768px)">
                                <?php endif; ?>
                            </video>
                        <?php else: ?>
                            <!-- Image Background with Ken Burns -->
                            <div class="hero-image-bg <?= $kenBurns !== 'none' ? 'ken-burns-' . $kenBurns : '' ?>" 
                                 style="background-image: url('<?= $bgImage ?>');">
                            </div>
                            <!-- Mobile Image Override -->
                            <style>
                                @media (max-width: 768px) {
                                    .hero-slide-premium[data-slide-index="<?= $index ?>"] .hero-image-bg {
                                        background-image: url('<?= $bgImageMobile ?>') !important;
                                    }
                                }
                            </style>
                        <?php endif; ?>
                        
                        <!-- Animated Gradient Overlay -->
                        <?php if ($globalSettings['animated_gradients']): ?>
                            <div class="hero-gradient-overlay"></div>
                        <?php endif; ?>
                        
                        <!-- Particle Effects (optional) -->
                        <?php if ($globalSettings['particle_effects']): ?>
                            <canvas class="hero-particles" id="particles-<?= $index ?>"></canvas>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Overlay Layer -->
                    <div class="hero-overlay-layer" style="<?= $overlayStyle ?>"></div>
                    
                    <!-- Content Layers Container -->
                    <div class="hero-layers-container">
                        <div class="container mx-auto px-4">
                            <div class="hero-layers-wrapper" style="text-align: <?= $textAlign ?>;">
                                
                                <!-- Layer 1: Title -->
                                <?php if (!empty($title)): ?>
                                    <div class="hero-layer hero-layer-title <?= $titleAnimClass ?> <?= $speedClass ?>" 
                                         data-layer-delay="0"
                                         style="color: <?= $textColor ?>;">
                                        <h1 class="premium-title">
                                            <?php if (strpos($contentAnimation, 'split') !== false || strpos($contentAnimation, 'word') !== false): ?>
                                                <span class="premium-text-split"><?= $title ?></span>
                                            <?php elseif (strpos($contentAnimation, 'letter') !== false): ?>
                                                <span class="premium-text-letters"><?= $title ?></span>
                                            <?php else: ?>
                                                <?= $title ?>
                                            <?php endif; ?>
                                        </h1>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Layer 2: Subtitle -->
                                <?php if (!empty($subtitle)): ?>
                                    <div class="hero-layer hero-layer-subtitle <?= $subtitleAnimClass ?> <?= $speedClass ?>" 
                                         data-layer-delay="200"
                                         style="color: <?= $textColor ?>;">
                                        <p class="premium-subtitle">
                                            <?php if (strpos($contentAnimation, 'split') !== false || strpos($contentAnimation, 'word') !== false): ?>
                                                <span class="premium-text-split"><?= $subtitle ?></span>
                                            <?php elseif (strpos($contentAnimation, 'letter') !== false): ?>
                                                <span class="premium-text-letters"><?= $subtitle ?></span>
                                            <?php else: ?>
                                                <?= $subtitle ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Layer 3: Description -->
                                <?php if (!empty($description)): ?>
                                    <div class="hero-layer hero-layer-description <?= $descAnimClass ?> <?= $speedClass ?>" 
                                         data-layer-delay="400"
                                         style="color: <?= $textColor ?>;">
                                        <p class="premium-description">
                                            <?php if (strpos($contentAnimation, 'split') !== false || strpos($contentAnimation, 'word') !== false): ?>
                                                <span class="premium-text-split"><?= $description ?></span>
                                            <?php else: ?>
                                                <?= $description ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Layer 4: Buttons -->
                                <?php if (!empty($slider['button_text_1']) || !empty($slider['button_text_2'])): ?>
                                    <div class="hero-layer hero-layer-buttons <?= $buttonAnimClass ?> <?= $speedClass ?>" 
                                         data-layer-delay="600">
                                        <div class="premium-buttons flex flex-col sm:flex-row gap-4 md:gap-5 <?= $buttonJustify ?>">
                                            <?php if (!empty($slider['button_text_1'])): ?>
                                                <a href="<?= escape($slider['button_link_1'] ?? '#') ?>" 
                                                   class="premium-btn premium-btn-<?= $buttonStyle1 ?>">
                                                    <?php
                                                    $btn1Icon = 'arrow-right';
                                                    $btn1Text = strtolower($slider['button_text_1']);
                                                    if (strpos($btn1Text, 'browse') !== false || strpos($btn1Text, 'shop') !== false) {
                                                        $btn1Icon = 'box';
                                                    } elseif (strpos($btn1Text, 'quote') !== false) {
                                                        $btn1Icon = 'calculator';
                                                    }
                                                    ?>
                                                    <span class="btn-content">
                                                        <i class="fas fa-<?= $btn1Icon ?>"></i>
                                                        <span><?= escape($slider['button_text_1']) ?></span>
                                                    </span>
                                                    <span class="btn-shine"></span>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($slider['button_text_2'])): ?>
                                                <a href="<?= escape($slider['button_link_2'] ?? '#') ?>" 
                                                   class="premium-btn premium-btn-<?= $buttonStyle2 ?>">
                                                    <?php
                                                    $btn2Icon = 'arrow-right';
                                                    $btn2Text = strtolower($slider['button_text_2']);
                                                    if (strpos($btn2Text, 'quote') !== false) {
                                                        $btn2Icon = 'calculator';
                                                    } elseif (strpos($btn2Text, 'contact') !== false) {
                                                        $btn2Icon = 'phone';
                                                    }
                                                    ?>
                                                    <span class="btn-content">
                                                        <i class="fas fa-<?= $btn2Icon ?>"></i>
                                                        <span><?= escape($slider['button_text_2']) ?></span>
                                                    </span>
                                                    <span class="btn-shine"></span>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Navigation Arrows -->
            <?php if ($globalSettings['show_navigation'] && $hasMultipleSlides): ?>
                <?php
                $showNavMobile = $globalSettings['navigation_mobile'] ? 'flex' : 'none';
                ?>
                <div class="swiper-button-next premium-nav-next" style="display: <?= $showNavMobile ?>;">
                    <div class="nav-arrow-inner"></div>
                </div>
                <div class="swiper-button-prev premium-nav-prev" style="display: <?= $showNavMobile ?>;">
                    <div class="nav-arrow-inner"></div>
                </div>
            <?php endif; ?>
            
            <!-- Pagination Dots -->
            <?php if ($globalSettings['show_pagination'] && $hasMultipleSlides): ?>
                <div class="swiper-pagination premium-pagination"></div>
            <?php endif; ?>
        </div>
        
        <!-- Slide Counter -->
        <?php if ($globalSettings['show_counter'] && $hasMultipleSlides): ?>
            <div class="premium-slide-counter" id="premium-slide-counter">
                <div class="counter-inner">
                    <span class="counter-current">1</span>
                    <span class="counter-separator">/</span>
                    <span class="counter-total"><?= $sliderCount ?></span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
(function() {
    'use strict';
    
    // Configuration
    const CONFIG = {
        swiperMaxWait: 5000,
        swiperCheckInterval: 100,
        autoplayDelay: <?= $globalSettings['autoplay_delay'] ?>,
        transitionSpeed: <?= $globalSettings['transition_speed'] ?>,
        transitionEffect: '<?= escape($globalSettings['transition_effect']) ?>',
        autoplayEnabled: <?= $globalSettings['autoplay_enabled'] ? 'true' : 'false' ?>,
        pauseOnHover: <?= $globalSettings['pause_on_hover'] ? 'true' : 'false' ?>,
        loopEnabled: <?= ($globalSettings['loop'] && $hasMultipleSlides) ? 'true' : 'false' ?>,
        showProgress: <?= $globalSettings['show_progress'] ? 'true' : 'false' ?>,
        showCounter: <?= $globalSettings['show_counter'] ? 'true' : 'false' ?>,
        keyboardEnabled: <?= $globalSettings['keyboard_enabled'] ? 'true' : 'false' ?>,
        mousewheelEnabled: <?= $globalSettings['mousewheel_enabled'] ? 'true' : 'false' ?>,
        lazyLoading: <?= $globalSettings['lazy_loading'] ? 'true' : 'false' ?>,
        preloadImages: <?= $globalSettings['preload_images'] ? 'true' : 'false' ?>,
        kenBurnsEnabled: <?= $globalSettings['ken_burns_enabled'] ? 'true' : 'false' ?>,
        parallaxEnabled: <?= $globalSettings['parallax_enabled'] ? 'true' : 'false' ?>,
        particleEffects: <?= $globalSettings['particle_effects'] ? 'true' : 'false' ?>,
        animatedGradients: <?= $globalSettings['animated_gradients'] ? 'true' : 'false' ?>,
        morphingShapes: <?= $globalSettings['morphing_shapes'] ? 'true' : 'false' ?>,
        slideCount: <?= $sliderCount ?>,
        hasMultipleSlides: <?= $hasMultipleSlides ? 'true' : 'false' ?>
    };
    
    // Get individual slide delay if available
    const firstSlider = <?= json_encode($sliders[0] ?? []) ?>;
    if (firstSlider && firstSlider.autoplay_delay && parseInt(firstSlider.autoplay_delay) > 0) {
        CONFIG.autoplayDelay = parseInt(firstSlider.autoplay_delay);
    }
    
    let heroSwiper = null;
    let initAttempts = 0;
    let isInitialized = false;
    
    // Fallback: Show first slide if Swiper fails
    function showFallbackSlider() {
        const slides = document.querySelectorAll('.hero-slide-premium');
        if (slides.length > 0) {
            slides.forEach((slide, index) => {
                if (index === 0) {
                    slide.style.display = 'flex';
                    slide.style.opacity = '1';
                    slide.style.position = 'relative';
                    slide.style.zIndex = '1';
                } else {
                    slide.style.display = 'none';
                }
            });
        }
        const loading = document.getElementById('heroSliderLoading');
        if (loading) loading.style.display = 'none';
    }
    
    // Initialize Swiper
    function initSwiper() {
        if (isInitialized) return;
        
        const swiperEl = document.querySelector('.heroSwiper');
        if (!swiperEl) {
            console.warn('Hero slider element not found');
            showFallbackSlider();
            return;
        }
        
        if (typeof Swiper === 'undefined') {
            initAttempts++;
            const maxAttempts = CONFIG.swiperMaxWait / CONFIG.swiperCheckInterval;
            
            if (initAttempts < maxAttempts) {
                setTimeout(initSwiper, CONFIG.swiperCheckInterval);
                return;
            } else {
                console.warn('Swiper library not loaded - using fallback');
                showFallbackSlider();
                return;
            }
        }
        
        if (swiperEl.swiper) {
            isInitialized = true;
            return;
        }
        
        try {
            const swiperConfig = {
                slidesPerView: 1,
                spaceBetween: 0,
                loop: CONFIG.loopEnabled,
                speed: CONFIG.transitionSpeed,
                effect: CONFIG.transitionEffect,
                touchEventsTarget: 'container',
                touchRatio: 1,
                touchAngle: 45,
                grabCursor: true,
                watchOverflow: true,
                observer: true,
                observeParents: true,
            };
            
            if (CONFIG.autoplayEnabled && CONFIG.hasMultipleSlides) {
                swiperConfig.autoplay = {
                    delay: CONFIG.autoplayDelay,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: CONFIG.pauseOnHover,
                };
            }
            
            if (CONFIG.transitionEffect === 'fade') {
                swiperConfig.fadeEffect = { crossFade: true };
            }
            
            if (CONFIG.transitionEffect === 'cube') {
                swiperConfig.cubeEffect = {
                    shadow: true,
                    slideShadows: true,
                    shadowOffset: 20,
                    shadowScale: 0.94,
                };
            }
            
            if (CONFIG.transitionEffect === 'coverflow') {
                swiperConfig.coverflowEffect = {
                    rotate: 50,
                    stretch: 0,
                    depth: 100,
                    modifier: 1,
                    slideShadows: true,
                };
            }
            
            if (CONFIG.transitionEffect === 'flip') {
                swiperConfig.flipEffect = {
                    slideShadows: true,
                    limitRotation: true,
                };
            }
            
            if (CONFIG.hasMultipleSlides && document.querySelector('.premium-pagination')) {
                swiperConfig.pagination = {
                    el: '.premium-pagination',
                    clickable: true,
                    dynamicBullets: true,
                    renderBullet: CONFIG.showProgress ? function(index, className) {
                        return '<span class="' + className + '"><span class="bullet-progress"></span></span>';
                    } : undefined,
                };
            }
            
            if (CONFIG.hasMultipleSlides && document.querySelector('.premium-nav-next')) {
                swiperConfig.navigation = {
                    nextEl: '.premium-nav-next',
                    prevEl: '.premium-nav-prev',
                };
            }
            
            if (CONFIG.keyboardEnabled) {
                swiperConfig.keyboard = {
                    enabled: true,
                    onlyInViewport: true,
                };
            }
            
            if (CONFIG.mousewheelEnabled) {
                swiperConfig.mousewheel = {
                    enabled: true,
                    invert: false,
                };
            }
            
            if (CONFIG.lazyLoading) {
                swiperConfig.lazy = {
                    enabled: true,
                    loadPrevNext: true,
                    loadPrevNextAmount: 1,
                };
            }
            
            swiperConfig.preloadImages = CONFIG.preloadImages;
            
            swiperConfig.a11y = {
                prevSlideMessage: 'Previous slide',
                nextSlideMessage: 'Next slide',
                firstSlideMessage: 'This is the first slide',
                lastSlideMessage: 'This is the last slide',
            };
            
            swiperConfig.on = {
                init: function() {
                    isInitialized = true;
                    hideLoading();
                    initPremiumAnimations(this);
                    initProgressBar();
                    updateSlideCounter(this);
                    initVideoBackgrounds();
                    initKenBurns();
                    if (CONFIG.particleEffects) initParticles();
                    if (CONFIG.animatedGradients) initAnimatedGradients();
                },
                slideChange: function() {
                    initPremiumAnimations(this);
                    resetProgressBar();
                    updateSlideCounter(this);
                    handleVideoOnSlideChange(this);
                    initKenBurns();
                    if (CONFIG.particleEffects) initParticles();
                    if (CONFIG.animatedGradients) initAnimatedGradients();
                },
                autoplayTimeLeft: function(swiper, time, progress) {
                    if (CONFIG.showProgress) {
                        updateProgressBar(progress);
                    }
                },
            };
            
            heroSwiper = new Swiper('.heroSwiper', swiperConfig);
            window.heroSwiper = heroSwiper;
            
            setupHoverPause();
            initVideoBackgrounds();
            initParallax();
            if (CONFIG.morphingShapes) initMorphingShapes();
            
        } catch (error) {
            console.error('Error initializing hero slider:', error);
            showFallbackSlider();
        }
    }
    
    // Hide loading
    function hideLoading() {
        const loading = document.getElementById('heroSliderLoading');
        if (loading) loading.style.display = 'none';
    }
    
    // Premium animations
    function initPremiumAnimations(swiper) {
        if (!swiper || !swiper.slides) return;
        
        const activeSlide = swiper.slides[swiper.activeIndex];
        if (!activeSlide) return;
        
        // Reset all layers
        const layers = activeSlide.querySelectorAll('.hero-layer');
        layers.forEach(function(layer) {
            layer.classList.remove('premium-animated');
            layer.style.opacity = '0';
            layer.style.transform = '';
        });
        
        // Animate layers with delays
        layers.forEach(function(layer) {
            const delay = parseInt(layer.getAttribute('data-layer-delay') || 0);
            const animClass = Array.from(layer.classList).find(cls => cls.startsWith('premium-animate-'));
            
            setTimeout(function() {
                layer.classList.add('premium-animated');
                
                // Handle text splitting animations
                const splitTexts = layer.querySelectorAll('.premium-text-split, .premium-text-letters');
                splitTexts.forEach(function(textEl) {
                    animateSplitText(textEl, animClass);
                });
            }, delay);
        });
    }
    
    // Animate split text
    function animateSplitText(element, animClass) {
        if (!element || element.classList.contains('premium-split-animated')) return;
        
        element.classList.add('premium-split-animated');
        const text = element.textContent.trim();
        const words = text.split(' ');
        
        const isLetterAnim = element.classList.contains('premium-text-letters');
        
        if (isLetterAnim) {
            element.innerHTML = '';
            text.split('').forEach(function(char, index) {
                const span = document.createElement('span');
                span.className = 'premium-char';
                span.textContent = char === ' ' ? '\u00A0' : char;
                span.style.opacity = '0';
                span.style.transform = getInitialTransform(animClass);
                element.appendChild(span);
                
                setTimeout(function() {
                    span.style.transition = 'all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)';
                    span.style.opacity = '1';
                    span.style.transform = 'translateY(0) scale(1)';
                }, index * 30);
            });
        } else {
            element.innerHTML = '';
            words.forEach(function(word, index) {
                const span = document.createElement('span');
                span.className = 'premium-word';
                span.textContent = word;
                span.style.opacity = '0';
                span.style.transform = getInitialTransform(animClass);
                element.appendChild(span);
                
                if (index < words.length - 1) {
                    element.appendChild(document.createTextNode(' '));
                }
                
                setTimeout(function() {
                    span.style.transition = 'all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1)';
                    span.style.opacity = '1';
                    span.style.transform = 'translateY(0) scale(1)';
                }, index * 80);
            });
        }
    }
    
    // Get initial transform
    function getInitialTransform(animClass) {
        if (!animClass) return 'translateY(0) scale(1)';
        
        if (animClass.includes('slide-up')) return 'translateY(60px)';
        if (animClass.includes('slide-down')) return 'translateY(-60px)';
        if (animClass.includes('slide-left')) return 'translateX(60px)';
        if (animClass.includes('slide-right')) return 'translateX(-60px)';
        if (animClass.includes('zoom')) return 'scale(0.3)';
        if (animClass.includes('rotate')) return 'rotate(15deg)';
        
        return 'translateY(40px)';
    }
    
    // Ken Burns effect
    function initKenBurns() {
        if (!CONFIG.kenBurnsEnabled) return;
        
        const activeSlide = document.querySelector('.hero-slide-premium.swiper-slide-active');
        if (!activeSlide) return;
        
        const kenBurns = activeSlide.getAttribute('data-ken-burns');
        if (!kenBurns || kenBurns === 'none') return;
        
        const imageBg = activeSlide.querySelector('.hero-image-bg');
        if (!imageBg) return;
        
        imageBg.style.animation = 'none';
        imageBg.offsetHeight;
        
        if (kenBurns === 'zoom') {
            imageBg.style.animation = 'kenBurnsZoom 20s ease-in-out infinite';
        } else if (kenBurns === 'pan-left') {
            imageBg.style.animation = 'kenBurnsPanLeft 20s ease-in-out infinite';
        } else if (kenBurns === 'pan-right') {
            imageBg.style.animation = 'kenBurnsPanRight 20s ease-in-out infinite';
        } else if (kenBurns === 'pan-up') {
            imageBg.style.animation = 'kenBurnsPanUp 20s ease-in-out infinite';
        } else if (kenBurns === 'pan-down') {
            imageBg.style.animation = 'kenBurnsPanDown 20s ease-in-out infinite';
        }
    }
    
    // Progress bar
    function initProgressBar() {
        if (!CONFIG.showProgress) return;
        const bullets = document.querySelectorAll('.premium-pagination .swiper-pagination-bullet');
        bullets.forEach(function(bullet) {
            if (!bullet.querySelector('.bullet-progress')) {
                const progress = document.createElement('span');
                progress.className = 'bullet-progress';
                bullet.appendChild(progress);
            }
        });
    }
    
    function resetProgressBar() {
        if (!CONFIG.showProgress) return;
        const bullets = document.querySelectorAll('.premium-pagination .swiper-pagination-bullet');
        bullets.forEach(function(bullet) {
            const progress = bullet.querySelector('.bullet-progress');
            if (progress) progress.style.width = '0%';
        });
    }
    
    function updateProgressBar(progress) {
        if (!CONFIG.showProgress) return;
        const activeBullet = document.querySelector('.premium-pagination .swiper-pagination-bullet-active');
        if (activeBullet) {
            const progressBar = activeBullet.querySelector('.bullet-progress');
            if (progressBar) progressBar.style.width = (progress * 100) + '%';
        }
    }
    
    // Slide counter
    function updateSlideCounter(swiper) {
        if (!CONFIG.showCounter || !swiper) return;
        let counterEl = document.getElementById('premium-slide-counter');
        if (!counterEl) {
            counterEl = document.createElement('div');
            counterEl.id = 'premium-slide-counter';
            counterEl.className = 'premium-slide-counter';
            const wrapper = document.querySelector('.hero-slider-wrapper');
            if (wrapper) wrapper.appendChild(counterEl);
        }
        if (counterEl) {
            const current = swiper.realIndex !== undefined ? swiper.realIndex + 1 : swiper.activeIndex + 1;
            const total = swiper.slides ? swiper.slides.length : CONFIG.slideCount;
            counterEl.innerHTML = '<div class="counter-inner"><span class="counter-current">' + current + '</span><span class="counter-separator">/</span><span class="counter-total">' + total + '</span></div>';
        }
    }
    
    // Video backgrounds
    function initVideoBackgrounds() {
        const videoSlides = document.querySelectorAll('.hero-slide-premium[data-has-video="true"]');
        videoSlides.forEach(function(slide) {
            const video = slide.querySelector('video.hero-video-bg');
            if (video) {
                const playPromise = video.play();
                if (playPromise !== undefined) {
                    playPromise.catch(function(error) {
                        console.log('Video autoplay prevented:', error);
                    });
                }
            }
        });
    }
    
    function handleVideoOnSlideChange(swiper) {
        if (!swiper || !swiper.slides) return;
        const activeSlide = swiper.slides[swiper.activeIndex];
        if (!activeSlide) return;
        
        const allVideos = document.querySelectorAll('.hero-video-bg');
        allVideos.forEach(function(video) {
            video.pause();
        });
        
        const activeVideo = activeSlide.querySelector('video.hero-video-bg');
        if (activeVideo) {
            const playPromise = activeVideo.play();
            if (playPromise !== undefined) {
                playPromise.catch(function(error) {
                    console.log('Video play prevented:', error);
                });
            }
        }
    }
    
    // Hover pause
    function setupHoverPause() {
        if (!heroSwiper || !CONFIG.pauseOnHover) return;
        const heroSlider = document.querySelector('.hero-slider-premium');
        if (!heroSlider) return;
        
        heroSlider.addEventListener('mouseenter', function() {
            if (heroSwiper.autoplay && heroSwiper.autoplay.running) {
                heroSwiper.autoplay.pause();
            }
            const videos = heroSlider.querySelectorAll('video.hero-video-bg');
            videos.forEach(function(video) {
                video.pause();
            });
        });
        
        heroSlider.addEventListener('mouseleave', function() {
            if (heroSwiper.autoplay && !heroSwiper.autoplay.running) {
                heroSwiper.autoplay.resume();
            }
            const activeSlide = document.querySelector('.hero-slide-premium.swiper-slide-active');
            if (activeSlide) {
                const video = activeSlide.querySelector('video.hero-video-bg');
                if (video) {
                    video.play().catch(function(e) {
                        console.log('Video play prevented:', e);
                    });
                }
            }
        });
    }
    
    // Parallax
    function initParallax() {
        if (!CONFIG.parallaxEnabled) return;
        const parallaxSlides = document.querySelectorAll('.hero-slide-premium[data-parallax="true"]');
        if (parallaxSlides.length === 0 || window.innerWidth <= 768) return;
        
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
        }, { passive: true });
    }
    
    // Particle effects
    function initParticles() {
        const activeSlide = document.querySelector('.hero-slide-premium.swiper-slide-active');
        if (!activeSlide) return;
        
        const canvas = activeSlide.querySelector('.hero-particles');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        const rect = activeSlide.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = rect.height;
        
        const particles = [];
        const particleCount = 60;
        
        for (let i = 0; i < particleCount; i++) {
            particles.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                radius: Math.random() * 2.5 + 1,
                speedX: (Math.random() - 0.5) * 0.8,
                speedY: (Math.random() - 0.5) * 0.8,
                opacity: Math.random() * 0.4 + 0.2,
            });
        }
        
        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            particles.forEach(function(particle) {
                particle.x += particle.speedX;
                particle.y += particle.speedY;
                
                if (particle.x < 0 || particle.x > canvas.width) particle.speedX *= -1;
                if (particle.y < 0 || particle.y > canvas.height) particle.speedY *= -1;
                
                ctx.beginPath();
                ctx.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(255, 255, 255, ' + particle.opacity + ')';
                ctx.fill();
            });
            
            requestAnimationFrame(animate);
        }
        
        animate();
    }
    
    // Animated gradients
    function initAnimatedGradients() {
        const gradientOverlays = document.querySelectorAll('.hero-gradient-overlay');
        gradientOverlays.forEach(function(overlay) {
            overlay.style.animation = 'gradientShift 8s ease infinite';
        });
    }
    
    // Morphing shapes
    function initMorphingShapes() {
        const shapes = document.querySelectorAll('.hero-animated-shapes .shape');
        shapes.forEach(function(shape, index) {
            const delay = index * 0.5;
            shape.style.animation = `morphShape ${5 + index * 2}s ease-in-out infinite ${delay}s`;
        });
    }
    
    // Initialize
    function startInitialization() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                initSwiper();
            });
        } else {
            initSwiper();
        }
        
        window.addEventListener('load', function() {
            setTimeout(function() {
                if (!isInitialized) {
                    initSwiper();
                }
            }, 100);
        });
    }
    
    startInitialization();
})();
</script>
