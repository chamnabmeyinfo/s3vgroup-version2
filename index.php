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

    <!-- Partners Section -->
    <?php
    use App\Models\Partner;
    $partnerModel = new Partner();
    $partners = $partnerModel->getByType('partner', true); // Get only active partners
    
    // Get logo styling settings (including section, title, etc.)
    // Fetch all settings that start with partners_, clients_, or certs_
    try {
        $logoStyleSettings = db()->fetchAll("
            SELECT `key`, value 
            FROM settings 
            WHERE `key` LIKE 'partners_%' 
               OR `key` LIKE 'clients_%' 
               OR `key` LIKE 'certs_%'
            ORDER BY `key`
        ");
        $logoStyles = [];
        foreach ($logoStyleSettings as $setting) {
            $logoStyles[$setting['key']] = $setting['value'];
        }
        
        // Debug: Log if no settings found (only in development)
        if (empty($logoStyles) && defined('APP_DEBUG') && APP_DEBUG) {
            error_log('No logo slider settings found in database');
        }
    } catch (Exception $e) {
        // If database query fails, use defaults only
        error_log('Error loading logo slider settings: ' . $e->getMessage());
        $logoStyles = [];
    }
    
    // Default values - comprehensive styling
    $defaultLogoStyles = [
        // Partners
        'partners_section_bg_color1' => '#f0f7ff',
        'partners_section_bg_color2' => '#e0efff',
        'partners_section_padding' => '80',
        'partners_title_color1' => '#1e40af',
        'partners_title_color2' => '#3b82f6',
        'partners_desc_color' => '#475569',
        'partners_logo_item_width' => '180',
        'partners_logo_item_height' => '100',
        'partners_logo_gap' => '40',
        'partners_logo_padding' => '20',
        'partners_logo_border_width' => '2',
        'partners_logo_border_style' => 'solid',
        'partners_logo_border_color' => '#3b82f6',
        'partners_logo_border_radius' => '12',
        'partners_logo_bg_color' => '#ffffff',
        'partners_logo_shadow_x' => '0',
        'partners_logo_shadow_y' => '2',
        'partners_logo_shadow_blur' => '8',
        'partners_logo_shadow_color' => '#3b82f6',
        'partners_logo_shadow_opacity' => '10',
        'partners_logo_hover_y' => '-8',
        'partners_logo_hover_scale' => '1.02',
        'partners_logo_hover_border_color' => '#3b82f6',
        'partners_logo_hover_shadow_y' => '8',
        'partners_logo_hover_shadow_blur' => '24',
        'partners_logo_hover_shadow_opacity' => '20',
        'partners_logo_transition' => '300',
        'partners_logo_object_fit' => 'contain',
        'partners_logo_grayscale' => '80',
        'partners_logo_image_opacity' => '80',
        'partners_logo_hover_image_scale' => '1.05',
        // Clients
        'clients_section_bg_color1' => '#f0fdf4',
        'clients_section_bg_color2' => '#dcfce7',
        'clients_section_padding' => '80',
        'clients_title_color1' => '#059669',
        'clients_title_color2' => '#10b981',
        'clients_desc_color' => '#475569',
        'clients_logo_item_width' => '180',
        'clients_logo_item_height' => '100',
        'clients_logo_gap' => '40',
        'clients_logo_padding' => '20',
        'clients_logo_border_width' => '2',
        'clients_logo_border_style' => 'solid',
        'clients_logo_border_color' => '#10b981',
        'clients_logo_border_radius' => '12',
        'clients_logo_bg_color' => '#ffffff',
        'clients_logo_shadow_x' => '0',
        'clients_logo_shadow_y' => '2',
        'clients_logo_shadow_blur' => '8',
        'clients_logo_shadow_color' => '#10b981',
        'clients_logo_shadow_opacity' => '10',
        'clients_logo_hover_y' => '-8',
        'clients_logo_hover_scale' => '1.02',
        'clients_logo_hover_border_color' => '#10b981',
        'clients_logo_hover_shadow_y' => '8',
        'clients_logo_hover_shadow_blur' => '24',
        'clients_logo_hover_shadow_opacity' => '20',
        'clients_logo_transition' => '300',
        'clients_logo_object_fit' => 'contain',
        'clients_logo_grayscale' => '80',
        'clients_logo_image_opacity' => '80',
        'clients_logo_hover_image_scale' => '1.05',
        // Certifications
        'certs_section_bg_color1' => '#ffffff',
        'certs_section_bg_color2' => '#f8f9fa',
        'certs_section_padding' => '60',
        'certs_title_color' => '#1a1a1a',
        'certs_desc_color' => '#666666',
        'certs_logo_item_width' => '160',
        'certs_logo_item_height' => '120',
        'certs_logo_gap' => '30',
        'certs_logo_padding' => '20',
        'certs_logo_border_width' => '1',
        'certs_logo_border_style' => 'solid',
        'certs_logo_border_color' => '#e5e7eb',
        'certs_logo_border_radius' => '12',
        'certs_logo_bg_color' => '#ffffff',
        'certs_logo_shadow_x' => '0',
        'certs_logo_shadow_y' => '2',
        'certs_logo_shadow_blur' => '12',
        'certs_logo_shadow_color' => '#000000',
        'certs_logo_shadow_opacity' => '8',
        'certs_logo_hover_y' => '-8',
        'certs_logo_hover_scale' => '1.05',
        'certs_logo_hover_border_color' => '#3b82f6',
        'certs_logo_hover_shadow_y' => '8',
        'certs_logo_hover_shadow_blur' => '24',
        'certs_logo_hover_shadow_opacity' => '15',
        'certs_logo_transition' => '300',
        'certs_logo_object_fit' => 'contain',
        'certs_logo_max_image_height' => '80',
        'certs_logo_hover_image_scale' => '1.1',
        'certs_text_color' => '#6b7280',
        'certs_text_font_size' => '12',
        'certs_text_hover_color' => '#3b82f6',
    ];
    
    foreach ($defaultLogoStyles as $key => $default) {
        if (!isset($logoStyles[$key])) {
            $logoStyles[$key] = $default;
        }
    }
    
    if (!empty($partners)):
    ?>
    <!-- Dynamic Logo Slider Styles - Generated from Admin Settings -->
    <style id="logo-slider-dynamic-styles">
    <?php
    // Helper function to convert hex color to rgba
    function hexToRgba($hex, $opacity) {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) != 6) {
            // Handle 3-character hex codes
            if (strlen($hex) == 3) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            } else {
                return "rgba(0, 0, 0, " . ($opacity / 100) . ")";
            }
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "rgba($r, $g, $b, " . ($opacity / 100) . ")";
    }
    ?>
    
    /* ===== PARTNERS SECTION STYLES ===== */
    .partners-slider {
        background: linear-gradient(135deg, <?= escape($logoStyles['partners_section_bg_color1'] ?? '#f0f7ff') ?> 0%, <?= escape($logoStyles['partners_section_bg_color2'] ?? '#e0efff') ?> 100%) !important;
        padding: <?= (int)($logoStyles['partners_section_padding'] ?? 80) ?>px 0 !important;
    }
    .partners-slider-header h2 {
        background: linear-gradient(135deg, <?= escape($logoStyles['partners_title_color1'] ?? '#1e40af') ?>, <?= escape($logoStyles['partners_title_color2'] ?? '#3b82f6') ?>) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        background-clip: text !important;
    }
    .partners-slider-header p {
        color: <?= escape($logoStyles['partners_desc_color'] ?? '#475569') ?> !important;
    }
    .partners-slider-track {
        gap: <?= (int)($logoStyles['partners_logo_gap'] ?? 40) ?>px !important;
    }
    .partners-slider-item {
        width: <?= (int)($logoStyles['partners_logo_item_width'] ?? 180) ?>px !important;
        height: <?= (int)($logoStyles['partners_logo_item_height'] ?? 100) ?>px !important;
        padding: <?= (int)($logoStyles['partners_logo_padding'] ?? 20) ?>px !important;
        border: <?= (int)($logoStyles['partners_logo_border_width'] ?? 2) ?>px <?= escape($logoStyles['partners_logo_border_style'] ?? 'solid') ?> <?= escape($logoStyles['partners_logo_border_color'] ?? '#3b82f6') ?> !important;
        border-radius: <?= (int)($logoStyles['partners_logo_border_radius'] ?? 12) ?>px !important;
        background-color: <?= escape($logoStyles['partners_logo_bg_color'] ?? '#ffffff') ?> !important;
        box-shadow: <?= (int)($logoStyles['partners_logo_shadow_x'] ?? 0) ?>px <?= (int)($logoStyles['partners_logo_shadow_y'] ?? 2) ?>px <?= (int)($logoStyles['partners_logo_shadow_blur'] ?? 8) ?>px <?= hexToRgba($logoStyles['partners_logo_shadow_color'] ?? '#3b82f6', (int)($logoStyles['partners_logo_shadow_opacity'] ?? 10)) ?> !important;
        transition: all <?= (int)($logoStyles['partners_logo_transition'] ?? 300) ?>ms ease !important;
    }
    .partners-slider-item:hover {
        transform: translateY(<?= (int)($logoStyles['partners_logo_hover_y'] ?? -8) ?>px) scale(<?= escape($logoStyles['partners_logo_hover_scale'] ?? '1.02') ?>) !important;
        border-color: <?= escape($logoStyles['partners_logo_hover_border_color'] ?? '#3b82f6') ?> !important;
        box-shadow: <?= (int)($logoStyles['partners_logo_shadow_x'] ?? 0) ?>px <?= (int)($logoStyles['partners_logo_hover_shadow_y'] ?? 8) ?>px <?= (int)($logoStyles['partners_logo_hover_shadow_blur'] ?? 24) ?>px <?= hexToRgba($logoStyles['partners_logo_shadow_color'] ?? '#3b82f6', (int)($logoStyles['partners_logo_hover_shadow_opacity'] ?? 20)) ?> !important;
    }
    .partners-slider-item img {
        width: 100% !important;
        height: 100% !important;
        object-fit: <?= escape($logoStyles['partners_logo_object_fit'] ?? 'contain') ?> !important;
        filter: grayscale(<?= (int)($logoStyles['partners_logo_grayscale'] ?? 80) ?>%) opacity(<?= (int)(($logoStyles['partners_logo_image_opacity'] ?? 80) / 100) ?>) !important;
        transition: all <?= (int)($logoStyles['partners_logo_transition'] ?? 300) ?>ms ease !important;
    }
    .partners-slider-item:hover img {
        filter: grayscale(0%) opacity(1) !important;
        transform: scale(<?= escape($logoStyles['partners_logo_hover_image_scale'] ?? '1.05') ?>) !important;
    }
    
    /* ===== CLIENTS SECTION STYLES ===== */
    .clients-slider {
        background: linear-gradient(135deg, <?= escape($logoStyles['clients_section_bg_color1'] ?? '#f0fdf4') ?> 0%, <?= escape($logoStyles['clients_section_bg_color2'] ?? '#dcfce7') ?> 100%) !important;
        padding: <?= (int)($logoStyles['clients_section_padding'] ?? 80) ?>px 0 !important;
    }
    .clients-slider-header h2 {
        background: linear-gradient(135deg, <?= escape($logoStyles['clients_title_color1'] ?? '#059669') ?>, <?= escape($logoStyles['clients_title_color2'] ?? '#10b981') ?>) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        background-clip: text !important;
    }
    .clients-slider-header p {
        color: <?= escape($logoStyles['clients_desc_color'] ?? '#475569') ?> !important;
    }
    .clients-slider-track {
        gap: <?= (int)($logoStyles['clients_logo_gap'] ?? 40) ?>px !important;
    }
    .clients-slider-item {
        width: <?= (int)($logoStyles['clients_logo_item_width'] ?? 180) ?>px !important;
        height: <?= (int)($logoStyles['clients_logo_item_height'] ?? 100) ?>px !important;
        padding: <?= (int)($logoStyles['clients_logo_padding'] ?? 20) ?>px !important;
        border: <?= (int)($logoStyles['clients_logo_border_width'] ?? 2) ?>px <?= escape($logoStyles['clients_logo_border_style'] ?? 'solid') ?> <?= escape($logoStyles['clients_logo_border_color'] ?? '#10b981') ?> !important;
        border-radius: <?= (int)($logoStyles['clients_logo_border_radius'] ?? 12) ?>px !important;
        background-color: <?= escape($logoStyles['clients_logo_bg_color'] ?? '#ffffff') ?> !important;
        box-shadow: <?= (int)($logoStyles['clients_logo_shadow_x'] ?? 0) ?>px <?= (int)($logoStyles['clients_logo_shadow_y'] ?? 2) ?>px <?= (int)($logoStyles['clients_logo_shadow_blur'] ?? 8) ?>px <?= hexToRgba($logoStyles['clients_logo_shadow_color'] ?? '#10b981', (int)($logoStyles['clients_logo_shadow_opacity'] ?? 10)) ?> !important;
        transition: all <?= (int)($logoStyles['clients_logo_transition'] ?? 300) ?>ms ease !important;
    }
    .clients-slider-item:hover {
        transform: translateY(<?= (int)($logoStyles['clients_logo_hover_y'] ?? -8) ?>px) scale(<?= escape($logoStyles['clients_logo_hover_scale'] ?? '1.02') ?>) !important;
        border-color: <?= escape($logoStyles['clients_logo_hover_border_color'] ?? '#10b981') ?> !important;
        box-shadow: <?= (int)($logoStyles['clients_logo_shadow_x'] ?? 0) ?>px <?= (int)($logoStyles['clients_logo_hover_shadow_y'] ?? 8) ?>px <?= (int)($logoStyles['clients_logo_hover_shadow_blur'] ?? 24) ?>px <?= hexToRgba($logoStyles['clients_logo_shadow_color'] ?? '#10b981', (int)($logoStyles['clients_logo_hover_shadow_opacity'] ?? 20)) ?> !important;
    }
    .clients-slider-item img {
        width: 100% !important;
        height: 100% !important;
        object-fit: <?= escape($logoStyles['clients_logo_object_fit'] ?? 'contain') ?> !important;
        filter: grayscale(<?= (int)($logoStyles['clients_logo_grayscale'] ?? 80) ?>%) opacity(<?= (int)(($logoStyles['clients_logo_image_opacity'] ?? 80) / 100) ?>) !important;
        transition: all <?= (int)($logoStyles['clients_logo_transition'] ?? 300) ?>ms ease !important;
    }
    .clients-slider-item:hover img {
        filter: grayscale(0%) opacity(1) !important;
        transform: scale(<?= escape($logoStyles['clients_logo_hover_image_scale'] ?? '1.05') ?>) !important;
    }
    
    /* ===== QUALITY CERTIFICATIONS SECTION STYLES ===== */
    .quality-certifications-slider {
        background: linear-gradient(to bottom, <?= escape($logoStyles['certs_section_bg_color1'] ?? '#ffffff') ?>, <?= escape($logoStyles['certs_section_bg_color2'] ?? '#f8f9fa') ?>) !important;
        padding: <?= (int)($logoStyles['certs_section_padding'] ?? 60) ?>px 0 !important;
    }
    .quality-certifications-slider-header h2 {
        color: <?= escape($logoStyles['certs_title_color'] ?? '#1a1a1a') ?> !important;
    }
    .quality-certifications-slider-header p {
        color: <?= escape($logoStyles['certs_desc_color'] ?? '#666666') ?> !important;
    }
    .quality-certifications-slider-track {
        gap: <?= (int)($logoStyles['certs_logo_gap'] ?? 30) ?>px !important;
    }
    .quality-certifications-slider-item {
        width: <?= (int)($logoStyles['certs_logo_item_width'] ?? 160) ?>px !important;
        height: <?= (int)($logoStyles['certs_logo_item_height'] ?? 120) ?>px !important;
        padding: <?= (int)($logoStyles['certs_logo_padding'] ?? 20) ?>px !important;
        border: <?= (int)($logoStyles['certs_logo_border_width'] ?? 1) ?>px <?= escape($logoStyles['certs_logo_border_style'] ?? 'solid') ?> <?= escape($logoStyles['certs_logo_border_color'] ?? '#e5e7eb') ?> !important;
        border-radius: <?= (int)($logoStyles['certs_logo_border_radius'] ?? 12) ?>px !important;
        background-color: <?= escape($logoStyles['certs_logo_bg_color'] ?? '#ffffff') ?> !important;
        box-shadow: <?= (int)($logoStyles['certs_logo_shadow_x'] ?? 0) ?>px <?= (int)($logoStyles['certs_logo_shadow_y'] ?? 2) ?>px <?= (int)($logoStyles['certs_logo_shadow_blur'] ?? 12) ?>px <?= hexToRgba($logoStyles['certs_logo_shadow_color'] ?? '#000000', (int)($logoStyles['certs_logo_shadow_opacity'] ?? 8)) ?> !important;
        transition: all <?= (int)($logoStyles['certs_logo_transition'] ?? 300) ?>ms ease !important;
    }
    .quality-certifications-slider-item:hover {
        transform: translateY(<?= (int)($logoStyles['certs_logo_hover_y'] ?? -8) ?>px) scale(<?= escape($logoStyles['certs_logo_hover_scale'] ?? '1.05') ?>) !important;
        border-color: <?= escape($logoStyles['certs_logo_hover_border_color'] ?? '#3b82f6') ?> !important;
        box-shadow: <?= (int)($logoStyles['certs_logo_shadow_x'] ?? 0) ?>px <?= (int)($logoStyles['certs_logo_hover_shadow_y'] ?? 8) ?>px <?= (int)($logoStyles['certs_logo_hover_shadow_blur'] ?? 24) ?>px <?= hexToRgba($logoStyles['certs_logo_shadow_color'] ?? '#000000', (int)($logoStyles['certs_logo_hover_shadow_opacity'] ?? 15)) ?> !important;
    }
    .quality-certifications-slider-item img {
        width: 100% !important;
        max-height: <?= (int)($logoStyles['certs_logo_max_image_height'] ?? 80) ?>px !important;
        object-fit: <?= escape($logoStyles['certs_logo_object_fit'] ?? 'contain') ?> !important;
        transition: all <?= (int)($logoStyles['certs_logo_transition'] ?? 300) ?>ms ease !important;
    }
    .quality-certifications-slider-item:hover img {
        transform: scale(<?= escape($logoStyles['certs_logo_hover_image_scale'] ?? '1.1') ?>) !important;
    }
    .quality-certifications-slider-item .cert-name {
        color: <?= escape($logoStyles['certs_text_color'] ?? '#6b7280') ?> !important;
        font-size: <?= (int)($logoStyles['certs_text_font_size'] ?? 12) ?>px !important;
        transition: color <?= (int)($logoStyles['certs_logo_transition'] ?? 300) ?>ms ease !important;
    }
    .quality-certifications-slider-item:hover .cert-name {
        color: <?= escape($logoStyles['certs_text_hover_color'] ?? '#3b82f6') ?> !important;
    }
    </style>
    <!-- End Dynamic Logo Slider Styles -->
    <section id="partners" class="partners-slider">
        <div class="partners-slider-container">
            <div class="partners-slider-header">
                <h2>Our Partners</h2>
                <p>Trusted partnerships with industry leaders</p>
            </div>
            <div class="partners-slider-wrapper">
                <div class="partners-slider-track">
                    <?php foreach ($partners as $partner): ?>
                    <div class="partners-slider-item">
                        <?php if (!empty($partner['website_url'])): ?>
                        <a href="<?= escape($partner['website_url']) ?>" target="_blank" rel="noopener noreferrer" title="<?= escape($partner['name']) ?>">
                            <img src="<?= escape(image_url($partner['logo'])) ?>" alt="<?= escape($partner['name']) ?>">
                        </a>
                        <?php else: ?>
                        <img src="<?= escape(image_url($partner['logo'])) ?>" alt="<?= escape($partner['name']) ?>">
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Spacer between sections -->
    <div style="height: 20px;"></div>

    <!-- Clients Section -->
    <?php
    $clients = $partnerModel->getByType('client', true); // Get only active clients
    if (!empty($clients)):
    ?>
    <section id="clients" class="clients-slider">
        <div class="clients-slider-container">
            <div class="clients-slider-header">
                <h2>Our Clients</h2>
                <p>Proud to serve leading companies worldwide</p>
            </div>
            <div class="clients-slider-wrapper">
                <div class="clients-slider-track">
                    <?php foreach ($clients as $client): ?>
                    <div class="clients-slider-item">
                        <?php if (!empty($client['website_url'])): ?>
                        <a href="<?= escape($client['website_url']) ?>" target="_blank" rel="noopener noreferrer" title="<?= escape($client['name']) ?>">
                            <img src="<?= escape(image_url($client['logo'])) ?>" alt="<?= escape($client['name']) ?>">
                        </a>
                        <?php else: ?>
                        <img src="<?= escape(image_url($client['logo'])) ?>" alt="<?= escape($client['name']) ?>">
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Quality Certifications Section -->
    <?php
    use App\Models\QualityCertification;
    $certModel = new QualityCertification();
    $certifications = $certModel->getAll(true); // Get only active certifications
    if (!empty($certifications)):
    ?>
    <section class="quality-certifications-slider">
        <div class="quality-certifications-slider-container">
            <div class="quality-certifications-slider-header">
                <h2>Quality Certifications</h2>
                <p>Certified quality standards and compliance</p>
            </div>
            <div class="quality-certifications-slider-wrapper">
                <div class="quality-certifications-slider-track">
                    <?php foreach ($certifications as $cert): ?>
                    <div class="quality-certifications-slider-item">
                        <?php if (!empty($cert['reference_url'])): ?>
                        <a href="<?= escape($cert['reference_url']) ?>" target="_blank" rel="noopener noreferrer" title="<?= escape($cert['name']) ?>">
                            <img src="<?= escape(image_url($cert['logo'])) ?>" alt="<?= escape($cert['name']) ?>">
                            <span class="cert-name"><?= escape($cert['name']) ?></span>
                        </a>
                        <?php else: ?>
                        <div>
                            <img src="<?= escape(image_url($cert['logo'])) ?>" alt="<?= escape($cert['name']) ?>">
                            <span class="cert-name"><?= escape($cert['name']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

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
