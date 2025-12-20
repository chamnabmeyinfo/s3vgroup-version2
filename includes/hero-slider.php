<?php
/**
 * Hero Slider Component - Redesigned from Scratch
 * Robust, production-ready slider with comprehensive error handling
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

<!-- Hero Slider Section -->
<section class="hero-slider-container" id="heroSliderSection" data-slide-count="<?= $sliderCount ?>">
    <!-- Loading State -->
    <div class="hero-slider-loading" id="heroSliderLoading">
        <div class="loading-spinner"></div>
    </div>
    
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
                    
                    // Video support (check if columns exist)
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
                    
                    // Overlay
                    $overlayStyle = '';
                    if (!empty($slider['overlay_gradient'])) {
                        $overlayStyle = "background: " . escape($slider['overlay_gradient']) . ";";
                    } elseif (!empty($slider['overlay_color'])) {
                        $overlayColor = escape($slider['overlay_color']);
                        $overlayStyle = "background: linear-gradient(135deg, {$overlayColor}, rgba(17, 24, 39, 0.8));";
                    } else {
                        $overlayStyle = "background: linear-gradient(135deg, rgba(30, 58, 138, 0.9), rgba(17, 24, 39, 0.8));";
                    }
                    
                    // Background options
                    $bgSize = !empty($slider['background_size']) ? $slider['background_size'] : 'cover';
                    $bgPosition = !empty($slider['background_position']) ? escape($slider['background_position']) : 'center';
                    $parallax = (isset($slider['parallax_effect']) && $slider['parallax_effect']) ? 'true' : 'false';
                    
                    // Height - prioritize slide, then global, then default
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
                    
                    // Animation classes
                    $titleAnimClass = ($contentAnimation !== 'none') ? 'animate-' . $contentAnimation : '';
                    $subtitleAnimClass = ($contentAnimation !== 'none') ? 'animate-' . $contentAnimation . ' animation-delay-200' : '';
                    $descAnimClass = ($contentAnimation !== 'none') ? 'animate-' . $contentAnimation . ' animation-delay-400' : '';
                    $buttonAnimClass = ($contentAnimation !== 'none') ? 'animate-' . $contentAnimation . ' animation-delay-600' : '';
                    
                    // Button alignment
                    $buttonJustify = 'justify-center';
                    if ($textAlign === 'left') {
                        $buttonJustify = 'justify-start';
                    } elseif ($textAlign === 'right') {
                        $buttonJustify = 'justify-end';
                    }
                ?>
                <div class="swiper-slide hero-slide" 
                     data-slide-index="<?= $index ?>"
                     data-has-video="<?= $hasVideo ? 'true' : 'false' ?>"
                     data-parallax="<?= $parallax ?>"
                     style="<?= $bgStyle ?> <?= $heightStyle ?>">
                    
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
                        <!-- Mobile Image Override -->
                        <style>
                            @media (max-width: 768px) {
                                .hero-slide[data-slide-index="<?= $index ?>"] {
                                    background-image: url('<?= $bgImageMobile ?>') !important;
                                }
                            }
                        </style>
                    <?php endif; ?>
                    
                    <!-- Overlay -->
                    <div class="hero-slide-overlay" style="<?= $overlayStyle ?>"></div>
                    
                    <!-- Content -->
                    <div class="container mx-auto px-4">
                        <div class="hero-slide-content max-w-4xl mx-auto" 
                             style="text-align: <?= $textAlign ?>; color: <?= $textColor ?>;">
                            
                            <?php if (!empty($title)): ?>
                                <h1 class="hero-title <?= $titleAnimClass ?> <?= $speedClass ?>">
                                    <?= $title ?>
                                </h1>
                            <?php endif; ?>
                            
                            <?php if (!empty($subtitle)): ?>
                                <p class="hero-subtitle <?= $subtitleAnimClass ?> <?= $speedClass ?>">
                                    <?= $subtitle ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($description)): ?>
                                <p class="hero-description <?= $descAnimClass ?> <?= $speedClass ?>">
                                    <?= $description ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($slider['button_text_1']) || !empty($slider['button_text_2'])): ?>
                                <div class="hero-buttons flex flex-col sm:flex-row gap-3 md:gap-4 <?= $buttonJustify ?> <?= $buttonAnimClass ?> <?= $speedClass ?>">
                                    <?php if (!empty($slider['button_text_1'])): ?>
                                        <a href="<?= escape($slider['button_link_1'] ?? '#') ?>" 
                                           class="btn-<?= $buttonStyle1 ?> hero-btn">
                                            <?php
                                            $btn1Icon = 'arrow-right';
                                            $btn1Text = strtolower($slider['button_text_1']);
                                            if (strpos($btn1Text, 'browse') !== false || strpos($btn1Text, 'shop') !== false) {
                                                $btn1Icon = 'box';
                                            } elseif (strpos($btn1Text, 'quote') !== false) {
                                                $btn1Icon = 'calculator';
                                            }
                                            ?>
                                            <i class="fas fa-<?= $btn1Icon ?> mr-2"></i>
                                            <?= escape($slider['button_text_1']) ?>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($slider['button_text_2'])): ?>
                                        <a href="<?= escape($slider['button_link_2'] ?? '#') ?>" 
                                           class="btn-<?= $buttonStyle2 ?> hero-btn">
                                            <?php
                                            $btn2Icon = 'arrow-right';
                                            $btn2Text = strtolower($slider['button_text_2']);
                                            if (strpos($btn2Text, 'quote') !== false) {
                                                $btn2Icon = 'calculator';
                                            } elseif (strpos($btn2Text, 'contact') !== false) {
                                                $btn2Icon = 'phone';
                                            }
                                            ?>
                                            <i class="fas fa-<?= $btn2Icon ?> mr-2"></i>
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
            
            <!-- Navigation Arrows -->
            <?php if ($globalSettings['show_navigation'] && $hasMultipleSlides): ?>
                <?php
                $showNavMobile = $globalSettings['navigation_mobile'] ? 'flex' : 'none';
                ?>
                <div class="swiper-button-next hero-nav-next" style="display: <?= $showNavMobile ?>;"></div>
                <div class="swiper-button-prev hero-nav-prev" style="display: <?= $showNavMobile ?>;"></div>
            <?php endif; ?>
            
            <!-- Pagination Dots -->
            <?php if ($globalSettings['show_pagination'] && $hasMultipleSlides): ?>
                <div class="swiper-pagination hero-pagination"></div>
            <?php endif; ?>
        </div>
        
        <!-- Slide Counter -->
        <?php if ($globalSettings['show_counter'] && $hasMultipleSlides): ?>
            <div class="hero-slide-counter" id="hero-slide-counter">
                <span class="counter-current">1</span>
                <span class="counter-separator">/</span>
                <span class="counter-total"><?= $sliderCount ?></span>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
(function() {
    'use strict';
    
    // Configuration
    const CONFIG = {
        swiperMaxWait: 5000, // Max 5 seconds to wait for Swiper
        swiperCheckInterval: 100, // Check every 100ms
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
        const slides = document.querySelectorAll('.hero-slide');
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
        // Hide loading
        const loading = document.getElementById('heroSliderLoading');
        if (loading) loading.style.display = 'none';
    }
    
    // Initialize Swiper
    function initSwiper() {
        // Prevent double initialization
        if (isInitialized) return;
        
        const swiperEl = document.querySelector('.heroSwiper');
        if (!swiperEl) {
            console.warn('Hero slider element not found');
            showFallbackSlider();
            return;
        }
        
        // Check if Swiper is available
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
        
        // Check if already initialized
        if (swiperEl.swiper) {
            isInitialized = true;
            return;
        }
        
        try {
            // Build Swiper config
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
            
            // Autoplay
            if (CONFIG.autoplayEnabled && CONFIG.hasMultipleSlides) {
                swiperConfig.autoplay = {
                    delay: CONFIG.autoplayDelay,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: CONFIG.pauseOnHover,
                };
            }
            
            // Fade effect
            if (CONFIG.transitionEffect === 'fade') {
                swiperConfig.fadeEffect = { crossFade: true };
            }
            
            // Cube effect
            if (CONFIG.transitionEffect === 'cube') {
                swiperConfig.cubeEffect = {
                    shadow: true,
                    slideShadows: true,
                    shadowOffset: 20,
                    shadowScale: 0.94,
                };
            }
            
            // Coverflow effect
            if (CONFIG.transitionEffect === 'coverflow') {
                swiperConfig.coverflowEffect = {
                    rotate: 50,
                    stretch: 0,
                    depth: 100,
                    modifier: 1,
                    slideShadows: true,
                };
            }
            
            // Flip effect
            if (CONFIG.transitionEffect === 'flip') {
                swiperConfig.flipEffect = {
                    slideShadows: true,
                    limitRotation: true,
                };
            }
            
            // Pagination
            if (CONFIG.hasMultipleSlides && document.querySelector('.hero-pagination')) {
                swiperConfig.pagination = {
                    el: '.hero-pagination',
                    clickable: true,
                    dynamicBullets: true,
                    renderBullet: CONFIG.showProgress ? function(index, className) {
                        return '<span class="' + className + '"><span class="bullet-progress"></span></span>';
                    } : undefined,
                };
            }
            
            // Navigation
            if (CONFIG.hasMultipleSlides && document.querySelector('.hero-nav-next')) {
                swiperConfig.navigation = {
                    nextEl: '.hero-nav-next',
                    prevEl: '.hero-nav-prev',
                };
            }
            
            // Keyboard
            if (CONFIG.keyboardEnabled) {
                swiperConfig.keyboard = {
                    enabled: true,
                    onlyInViewport: true,
                };
            }
            
            // Mousewheel
            if (CONFIG.mousewheelEnabled) {
                swiperConfig.mousewheel = {
                    enabled: true,
                    invert: false,
                };
            }
            
            // Lazy loading
            if (CONFIG.lazyLoading) {
                swiperConfig.lazy = {
                    enabled: true,
                    loadPrevNext: true,
                    loadPrevNextAmount: 1,
                };
            }
            
            // Preload images
            swiperConfig.preloadImages = CONFIG.preloadImages;
            
            // Accessibility
            swiperConfig.a11y = {
                prevSlideMessage: 'Previous slide',
                nextSlideMessage: 'Next slide',
                firstSlideMessage: 'This is the first slide',
                lastSlideMessage: 'This is the last slide',
            };
            
            // Event handlers
            swiperConfig.on = {
                init: function() {
                    isInitialized = true;
                    hideLoading();
                    initSlideAnimations(this);
                    initProgressBar();
                    updateSlideCounter(this);
                    initVideoBackgrounds();
                },
                slideChange: function() {
                    initSlideAnimations(this);
                    resetProgressBar();
                    updateSlideCounter(this);
                    handleVideoOnSlideChange(this);
                },
                autoplayTimeLeft: function(swiper, time, progress) {
                    if (CONFIG.showProgress) {
                        updateProgressBar(progress);
                    }
                },
            };
            
            // Initialize Swiper
            heroSwiper = new Swiper('.heroSwiper', swiperConfig);
            
            // Store globally for debugging
            window.heroSwiper = heroSwiper;
            
            // Setup hover pause
            setupHoverPause();
            
            // Setup video backgrounds
            initVideoBackgrounds();
            
        } catch (error) {
            console.error('Error initializing hero slider:', error);
            showFallbackSlider();
        }
    }
    
    // Hide loading indicator
    function hideLoading() {
        const loading = document.getElementById('heroSliderLoading');
        if (loading) {
            loading.style.display = 'none';
        }
    }
    
    // Initialize slide animations
    function initSlideAnimations(swiper) {
        if (!swiper || !swiper.slides) return;
        
        const activeSlide = swiper.slides[swiper.activeIndex];
        if (!activeSlide) return;
        
        const animatedElements = activeSlide.querySelectorAll('.animate-fade, .animate-slide-up, .animate-slide-down, .animate-zoom');
        animatedElements.forEach(function(el) {
            el.style.opacity = '0';
            el.style.transform = getInitialTransform(el);
            
            setTimeout(function() {
                el.style.transition = 'opacity 0.8s ease-out, transform 0.8s ease-out';
                el.style.opacity = '1';
                el.style.transform = 'translateY(0) scale(1)';
            }, 100);
        });
    }
    
    // Get initial transform
    function getInitialTransform(el) {
        if (el.classList.contains('animate-slide-up')) {
            return 'translateY(30px)';
        } else if (el.classList.contains('animate-slide-down')) {
            return 'translateY(-30px)';
        } else if (el.classList.contains('animate-zoom')) {
            return 'scale(0.9)';
        }
        return 'translateY(0) scale(1)';
    }
    
    // Progress bar functions
    function initProgressBar() {
        if (!CONFIG.showProgress) return;
        
        const bullets = document.querySelectorAll('.hero-pagination .swiper-pagination-bullet');
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
        
        const bullets = document.querySelectorAll('.hero-pagination .swiper-pagination-bullet');
        bullets.forEach(function(bullet) {
            const progress = bullet.querySelector('.bullet-progress');
            if (progress) {
                progress.style.width = '0%';
            }
        });
    }
    
    function updateProgressBar(progress) {
        if (!CONFIG.showProgress) return;
        
        const activeBullet = document.querySelector('.hero-pagination .swiper-pagination-bullet-active');
        if (activeBullet) {
            const progressBar = activeBullet.querySelector('.bullet-progress');
            if (progressBar) {
                progressBar.style.width = (progress * 100) + '%';
            }
        }
    }
    
    // Slide counter
    function updateSlideCounter(swiper) {
        if (!CONFIG.showCounter || !swiper) return;
        
        let counterEl = document.getElementById('hero-slide-counter');
        if (!counterEl) {
            counterEl = document.createElement('div');
            counterEl.id = 'hero-slide-counter';
            counterEl.className = 'hero-slide-counter';
            const wrapper = document.querySelector('.hero-slider-wrapper');
            if (wrapper) wrapper.appendChild(counterEl);
        }
        
        if (counterEl) {
            const current = swiper.realIndex !== undefined ? swiper.realIndex + 1 : swiper.activeIndex + 1;
            const total = swiper.slides ? swiper.slides.length : CONFIG.slideCount;
            counterEl.innerHTML = '<span class="counter-current">' + current + '</span><span class="counter-separator">/</span><span class="counter-total">' + total + '</span>';
        }
    }
    
    // Video background handling
    function initVideoBackgrounds() {
        const videoSlides = document.querySelectorAll('.hero-slide[data-has-video="true"]');
        videoSlides.forEach(function(slide) {
            const video = slide.querySelector('video.hero-video-bg');
            if (video) {
                // Try to play video
                const playPromise = video.play();
                if (playPromise !== undefined) {
                    playPromise.catch(function(error) {
                        // Autoplay prevented - this is normal in some browsers
                        console.log('Video autoplay prevented (normal behavior):', error);
                    });
                }
            }
        });
    }
    
    function handleVideoOnSlideChange(swiper) {
        if (!swiper || !swiper.slides) return;
        
        const activeSlide = swiper.slides[swiper.activeIndex];
        if (!activeSlide) return;
        
        // Pause all videos
        const allVideos = document.querySelectorAll('.hero-video-bg');
        allVideos.forEach(function(video) {
            video.pause();
        });
        
        // Play active slide video
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
    
    // Setup hover pause
    function setupHoverPause() {
        if (!heroSwiper || !CONFIG.pauseOnHover) return;
        
        const heroSlider = document.querySelector('.hero-slider-container');
        if (!heroSlider) return;
        
        heroSlider.addEventListener('mouseenter', function() {
            if (heroSwiper.autoplay && heroSwiper.autoplay.running) {
                heroSwiper.autoplay.pause();
            }
            // Pause videos
            const videos = heroSlider.querySelectorAll('video.hero-video-bg');
            videos.forEach(function(video) {
                video.pause();
            });
        });
        
        heroSlider.addEventListener('mouseleave', function() {
            if (heroSwiper.autoplay && !heroSwiper.autoplay.running) {
                heroSwiper.autoplay.resume();
            }
            // Resume active video
            const activeSlide = document.querySelector('.hero-slide.swiper-slide-active');
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
    
    // Parallax effect
    function initParallax() {
        const parallaxSlides = document.querySelectorAll('.hero-slide[data-parallax="true"]');
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
    
    // Initialize when ready
    function startInitialization() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                initSwiper();
                initParallax();
            });
        } else {
            initSwiper();
            initParallax();
        }
        
        // Also try after window load
        window.addEventListener('load', function() {
            setTimeout(function() {
                if (!isInitialized) {
                    initSwiper();
                }
            }, 100);
        });
    }
    
    // Start
    startInitialization();
})();
</script>
