<?php
/**
 * Hero Slider Component
 * Displays dynamic hero sliders from database
 */

use App\Database\Connection;
use App\Models\Setting;

// Get global settings with error handling - defaults ensure slider works even if settings fail
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

// Try to load settings from database, but don't break if it fails
try {
    $settingModel = new Setting();
    $globalSettings['autoplay_enabled'] = (bool)$settingModel->get('hero_slider_autoplay_enabled', 1);
    $globalSettings['autoplay_delay'] = (int)$settingModel->get('hero_slider_autoplay_delay', 5000);
    $globalSettings['pause_on_hover'] = (bool)$settingModel->get('hero_slider_pause_on_hover', 1);
    $globalSettings['show_navigation'] = (bool)$settingModel->get('hero_slider_show_navigation', 1);
    $globalSettings['show_pagination'] = (bool)$settingModel->get('hero_slider_show_pagination', 1);
    $globalSettings['navigation_mobile'] = (bool)$settingModel->get('hero_slider_navigation_mobile', 0);
    $globalSettings['transition_effect'] = $settingModel->get('hero_slider_transition_effect', 'fade');
    $globalSettings['transition_speed'] = (int)$settingModel->get('hero_slider_transition_speed', 1000);
    $globalSettings['loop'] = (bool)$settingModel->get('hero_slider_loop', 1);
    $globalSettings['height'] = $settingModel->get('hero_slider_height', 'auto');
    $globalSettings['custom_height'] = $settingModel->get('hero_slider_custom_height', '');
    $globalSettings['show_counter'] = (bool)$settingModel->get('hero_slider_show_counter', 1);
    $globalSettings['show_progress'] = (bool)$settingModel->get('hero_slider_show_progress', 1);
    $globalSettings['keyboard_enabled'] = (bool)$settingModel->get('hero_slider_keyboard_enabled', 1);
    $globalSettings['mousewheel_enabled'] = (bool)$settingModel->get('hero_slider_mousewheel_enabled', 0);
    $globalSettings['lazy_loading'] = (bool)$settingModel->get('hero_slider_lazy_loading', 1);
    $globalSettings['preload_images'] = (bool)$settingModel->get('hero_slider_preload_images', 0);
} catch (\Exception $e) {
    // If settings can't be loaded, use defaults - don't break the page
    // Error is silently handled, defaults ensure slider still works
}

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
                
                // Video URLs
                $videoUrl = $slider['video_url'] ?? '';
                $videoMobileUrl = $slider['video_mobile_url'] ?? $videoUrl;
                $hasVideo = !empty($videoUrl);
                
                $textAlign = $slider['text_alignment'] ?? 'center';
                $textColor = $slider['text_color'] ?? '#ffffff';
                
                // Background options
                $bgSize = $slider['background_size'] ?? 'cover';
                $bgPosition = $slider['background_position'] ?? 'center';
                $parallax = ($slider['parallax_effect'] ?? 0) ? 'true' : 'false';
                
                // Height options - prioritize individual slide settings, fallback to global, then default
                $slideHeight = !empty($slider['slide_height']) ? $slider['slide_height'] : (!empty($globalSettings['height']) ? $globalSettings['height'] : 'auto');
                $customHeight = !empty($slider['custom_height']) ? $slider['custom_height'] : (!empty($globalSettings['custom_height']) ? $globalSettings['custom_height'] : '');
                $heightStyle = '';
                if ($slideHeight === 'full') {
                    $heightStyle = 'height: 100vh;';
                } elseif ($slideHeight === 'custom' && !empty($customHeight)) {
                    $heightStyle = 'height: ' . escape($customHeight) . ';';
                }
                
                // Animation options
                $contentAnimation = $slider['content_animation'] ?? 'fade';
                $animationSpeed = $slider['animation_speed'] ?? 'normal';
                
                // Button styles
                $buttonStyle1 = $slider['button_style_1'] ?? 'primary';
                $buttonStyle2 = $slider['button_style_2'] ?? 'secondary';
                
                // Build background style (only if no video)
                $bgStyle = '';
                if (!$hasVideo) {
                    // Handle stretch and fill specially - they need 100% 100%
                    $bgSizeValue = $bgSize;
                    if ($bgSize === 'stretch' || $bgSize === 'fill') {
                        $bgSizeValue = '100% 100%';
                    }
                    
                    $bgStyle = "background-image: url('" . escape($bgImage) . "'); ";
                    $bgStyle .= "background-size: " . escape($bgSizeValue) . " !important; ";
                    $bgStyle .= "background-position: " . escape($bgPosition) . " !important; ";
                    $bgStyle .= "background-repeat: no-repeat;";
                }
                
                // Animation speed class (for child elements)
                $speedClass = 'animation-speed-' . $animationSpeed;
            ?>
                <div class="swiper-slide hero-slide" 
                     style="<?= $bgStyle ?> <?= $heightStyle ?>"
                     data-parallax="<?= $parallax ?>"
                     data-slide-index="<?= $index ?>"
                     data-has-video="<?= $hasVideo ? 'true' : 'false' ?>">
                    <?php if ($hasVideo): ?>
                        <!-- Video Background -->
                        <video class="hero-video-bg" 
                               autoplay 
                               muted 
                               loop 
                               playsinline
                               poster="<?= escape($bgImage) ?>">
                            <source src="<?= escape($videoUrl) ?>" type="video/mp4" media="(min-width: 769px)">
                            <?php if (!empty($videoMobileUrl)): ?>
                                <source src="<?= escape($videoMobileUrl) ?>" type="video/mp4" media="(max-width: 768px)">
                            <?php endif; ?>
                            <!-- Fallback to image if video fails -->
                            Your browser does not support the video tag.
                        </video>
                    <?php else: ?>
                        <style>
                            @media (max-width: 768px) {
                                .hero-slide[data-slide-index="<?= $index ?>"] {
                                    background-image: url('<?= escape($bgImageMobile) ?>') !important;
                                    background-size: <?= escape($bgSize === 'stretch' || $bgSize === 'fill' ? '100% 100%' : $bgSize) ?> !important;
                                    background-position: <?= escape($bgPosition) ?> !important;
                                }
                            }
                        </style>
                    <?php endif; ?>
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
                                           class="btn-<?= $buttonStyle1 ?> inline-block transform hover:scale-105 transition-all shadow-lg hover:shadow-xl">
                                            <i class="fas fa-<?= strpos(strtolower($slider['button_text_1']), 'browse') !== false || strpos(strtolower($slider['button_text_1']), 'shop') !== false ? 'box' : (strpos(strtolower($slider['button_text_1']), 'quote') !== false ? 'calculator' : 'arrow-right') ?> mr-2"></i>
                                            <?= escape($slider['button_text_1']) ?>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($slider['button_text_2'])): ?>
                                        <a href="<?= escape($slider['button_link_2'] ?? '#') ?>" 
                                           class="btn-<?= $buttonStyle2 ?> inline-block transform hover:scale-105 transition-all shadow-lg hover:shadow-xl">
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
        <?php if ($globalSettings['show_navigation']): ?>
        <div class="swiper-button-next hero-nav-next" style="display: <?= ($globalSettings['navigation_mobile'] || (!isset($_SERVER['HTTP_USER_AGENT']) || strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') === false)) ? 'flex' : 'none' ?>;"></div>
        <div class="swiper-button-prev hero-nav-prev" style="display: <?= ($globalSettings['navigation_mobile'] || (!isset($_SERVER['HTTP_USER_AGENT']) || strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') === false)) ? 'flex' : 'none' ?>;"></div>
        <?php endif; ?>
        
        <!-- Pagination -->
        <?php if ($globalSettings['show_pagination']): ?>
        <div class="swiper-pagination hero-pagination"></div>
        <?php endif; ?>
    </div>
</section>

<script>
// Wait for Swiper to load - handle both DOM ready and Swiper loading
(function() {
    function initHeroSwiper() {
        // Check if Swiper is loaded
        if (typeof Swiper === 'undefined') {
            // Wait a bit more if Swiper isn't ready yet (max 5 seconds)
            if (typeof initHeroSwiper.attempts === 'undefined') {
                initHeroSwiper.attempts = 0;
            }
            initHeroSwiper.attempts++;
            if (initHeroSwiper.attempts < 50) { // Try for 5 seconds (50 * 100ms)
                setTimeout(initHeroSwiper, 100);
            } else {
                console.error('Swiper library failed to load after 5 seconds - using fallback');
                // Fallback: Show first slide only
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
            }
            return;
        }
        
        // Check if already initialized
        const swiperEl = document.querySelector('.heroSwiper');
        if (!swiperEl) {
            return; // Slider element not found
        }
        
        if (swiperEl.swiper) {
            return; // Already initialized
        }
        
        // Initialize Swiper for Hero Slider with Advanced Features
        // Get autoplay delay - prioritize individual slide setting, then global, then default
        const firstSlider = <?= json_encode($sliders[0] ?? []) ?>;
        let autoplayDelay = <?= $globalSettings['autoplay_delay'] ?>;
        // Use individual slide delay if set and valid
        if (firstSlider && firstSlider.autoplay_delay && parseInt(firstSlider.autoplay_delay) > 0) {
            autoplayDelay = parseInt(firstSlider.autoplay_delay);
        }
        
        try {
            const heroSwiper = new Swiper('.heroSwiper', {
                slidesPerView: 1,
                spaceBetween: 0,
                loop: <?= ($globalSettings['loop'] && count($sliders) > 1) ? 'true' : 'false' ?>,
                autoplay: <?= $globalSettings['autoplay_enabled'] ? '{ delay: autoplayDelay, disableOnInteraction: false, pauseOnMouseEnter: ' . ($globalSettings['pause_on_hover'] ? 'true' : 'false') . ' }' : 'false' ?>,
                speed: <?= $globalSettings['transition_speed'] ?>,
                effect: '<?= escape($globalSettings['transition_effect']) ?>',
                fadeEffect: {
                    crossFade: true
                },
            cubeEffect: {
                shadow: true,
                slideShadows: true,
                shadowOffset: 20,
                shadowScale: 0.94,
            },
            coverflowEffect: {
                rotate: 50,
                stretch: 0,
                depth: 100,
                modifier: 1,
                slideShadows: true,
            },
            flipEffect: {
                slideShadows: true,
                limitRotation: true,
            },
            pagination: <?= $globalSettings['show_pagination'] ? '{
                el: ".hero-pagination",
                clickable: true,
                dynamicBullets: true,
                renderBullet: function (index, className) {
                    return "<span class=\"" + className + "\"><span class=\"bullet-progress\"></span></span>";
                },
            }' : 'false' ?>,
            navigation: <?= $globalSettings['show_navigation'] ? '{
                nextEl: ".hero-nav-next",
                prevEl: ".hero-nav-prev",
            }' : 'false' ?>,
            keyboard: {
                enabled: <?= $globalSettings['keyboard_enabled'] ? 'true' : 'false' ?>,
                onlyInViewport: true,
            },
            mousewheel: {
                enabled: <?= $globalSettings['mousewheel_enabled'] ? 'true' : 'false' ?>,
                invert: false,
            },
            lazy: {
                enabled: <?= $globalSettings['lazy_loading'] ? 'true' : 'false' ?>,
                loadPrevNext: true,
                loadPrevNextAmount: 1,
            },
            preloadImages: <?= $globalSettings['preload_images'] ? 'true' : 'false' ?>,
            touchEventsTarget: 'container',
            touchRatio: 1,
            touchAngle: 45,
            grabCursor: true,
            a11y: {
                prevSlideMessage: 'Previous slide',
                nextSlideMessage: 'Next slide',
                firstSlideMessage: 'This is the first slide',
                lastSlideMessage: 'This is the last slide',
            },
            on: {
                init: function() {
                    initSlideAnimations(this);
                    initProgressBar(this);
                    updateSlideCounter(this);
                },
                slideChange: function() {
                    initSlideAnimations(this);
                    resetProgressBar(this);
                    updateSlideCounter(this);
                },
                autoplayTimeLeft: function(swiper, time, progress) {
                    updateProgressBar(swiper, progress);
                },
            }
            });
            
            // Store reference globally for debugging
            window.heroSwiper = heroSwiper;
            
            // Initialize slide animations
            function initSlideAnimations(swiper) {
            const activeSlide = swiper.slides[swiper.activeIndex];
            if (activeSlide) {
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
        }
        
        // Get initial transform based on animation type
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
        
        // Progress bar for autoplay
        <?php if ($globalSettings['show_progress']): ?>
        function initProgressBar(swiper) {
            const bullets = document.querySelectorAll('.hero-pagination .swiper-pagination-bullet');
            bullets.forEach(function(bullet) {
                if (!bullet.querySelector('.bullet-progress')) {
                    const progress = document.createElement('span');
                    progress.className = 'bullet-progress';
                    bullet.appendChild(progress);
                }
            });
        }
        
        function resetProgressBar(swiper) {
            const bullets = document.querySelectorAll('.hero-pagination .swiper-pagination-bullet');
            bullets.forEach(function(bullet) {
                const progress = bullet.querySelector('.bullet-progress');
                if (progress) {
                    progress.style.width = '0%';
                }
            });
        }
        
        function updateProgressBar(swiper, progress) {
            const activeBullet = document.querySelector('.hero-pagination .swiper-pagination-bullet-active');
            if (activeBullet) {
                const progressBar = activeBullet.querySelector('.bullet-progress');
                if (progressBar) {
                    progressBar.style.width = (progress * 100) + '%';
                }
            }
        }
        <?php else: ?>
        // Progress bar disabled in global settings
        function initProgressBar(swiper) {}
        function resetProgressBar(swiper) {}
        function updateProgressBar(swiper, progress) {}
        <?php endif; ?>
        
        // Slide counter
        function updateSlideCounter(swiper) {
            <?php if ($globalSettings['show_counter']): ?>
            let counterEl = document.getElementById('hero-slide-counter');
            if (!counterEl) {
                counterEl = document.createElement('div');
                counterEl.id = 'hero-slide-counter';
                counterEl.className = 'hero-slide-counter';
                document.querySelector('.hero-slider').appendChild(counterEl);
            }
            const current = swiper.realIndex + 1;
            const total = swiper.slides.length;
            counterEl.innerHTML = '<span class="counter-current">' + current + '</span><span class="counter-separator">/</span><span class="counter-total">' + total + '</span>';
            <?php else: ?>
            // Counter disabled in global settings
            <?php endif; ?>
        }
        
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
        
        // Initialize video backgrounds
        function initVideoBackgrounds() {
            const videoSlides = document.querySelectorAll('.hero-slide[data-has-video="true"]');
            videoSlides.forEach(function(slide) {
                const video = slide.querySelector('video.hero-video-bg');
                if (video) {
                    // Ensure video plays
                    video.addEventListener('loadeddata', function() {
                        video.play().catch(function(e) {
                            console.log('Video autoplay prevented:', e);
                        });
                    });
                    
                    // Play video when slide becomes active
                    const observer = new MutationObserver(function(mutations) {
                        if (slide.classList.contains('swiper-slide-active')) {
                            video.play().catch(function(e) {
                                console.log('Video play prevented:', e);
                            });
                        } else {
                            video.pause();
                        }
                    });
                    
                    observer.observe(slide, {
                        attributes: true,
                        attributeFilter: ['class']
                    });
                    
                    // Initial play if slide is active
                    if (slide.classList.contains('swiper-slide-active')) {
                        video.play().catch(function(e) {
                            console.log('Video autoplay prevented:', e);
                        });
                    }
                }
            });
        }
        
            // Initialize videos after Swiper is ready
            initVideoBackgrounds();
            
            // Re-initialize videos on slide change
            heroSwiper.on('slideChange', function() {
                initVideoBackgrounds();
            });
            
            // Pause autoplay on hover (if not already handled)
            const heroSlider = document.querySelector('.hero-slider');
            if (heroSlider && heroSwiper.autoplay) {
                heroSlider.addEventListener('mouseenter', function() {
                    if (heroSwiper.autoplay && heroSwiper.autoplay.running) {
                        heroSwiper.autoplay.pause();
                    }
                    // Pause videos on hover
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
            
            // Keyboard shortcuts indicator (optional - can be toggled)
            let showShortcuts = false;
            document.addEventListener('keydown', function(e) {
                if (e.key === '?' && e.shiftKey) {
                    showShortcuts = !showShortcuts;
                    toggleShortcutsIndicator(showShortcuts);
                }
            });
            
            function toggleShortcutsIndicator(show) {
                let indicator = document.getElementById('hero-shortcuts');
                if (!indicator && show) {
                    indicator = document.createElement('div');
                    indicator.id = 'hero-shortcuts';
                    indicator.className = 'hero-shortcuts';
                    indicator.innerHTML = '<div class="shortcuts-content"><h4>Keyboard Shortcuts</h4><ul><li><kbd>←</kbd> Previous slide</li><li><kbd>→</kbd> Next slide</li><li><kbd>Space</kbd> Pause/Resume</li><li><kbd>?</kbd> Toggle this help</li></ul></div>';
                    document.querySelector('.hero-slider').appendChild(indicator);
                } else if (indicator) {
                    indicator.style.display = show ? 'block' : 'none';
                }
            }
        } catch (error) {
            console.error('Error initializing hero slider:', error);
            // Fallback: Show first slide only
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
        }
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHeroSwiper);
    } else {
        // DOM already loaded, try to initialize
        initHeroSwiper();
    }
    
    // Also try after window load (in case Swiper loads late)
    window.addEventListener('load', function() {
        setTimeout(initHeroSwiper, 100);
    });
})();
</script>

