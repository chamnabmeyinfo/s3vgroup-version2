<?php
// Get categories for navigation dropdown
use App\Models\Category;
$categoryModel = new Category();
$navCategories = $categoryModel->getAll(true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= escape($metaDescription ?? 'Premium forklifts and industrial equipment for warehouses and factories') ?>">
    <title><?= escape($pageTitle ?? 'Forklift & Equipment Pro') ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/product-images.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/mobile-bottom-nav.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/category-modern.css') ?>">
    <?php if (basename($_SERVER['PHP_SELF']) === 'index.php'): ?>
    <link rel="stylesheet" href="<?= asset('assets/css/hero-slider.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/hero-slider-advanced.css') ?>">
    <?php endif; ?>
    <?php if (basename($_SERVER['PHP_SELF']) === 'products.php'): ?>
    <link rel="stylesheet" href="<?= asset('assets/css/products-responsive.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/app-products.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/advanced-filters.css') ?>">
    <?php endif; ?>
    
    <!-- Dynamic Logo Colors -->
    <?php 
    $logoColors = get_logo_colors();
    ?>
    <style>
        :root {
            --logo-primary: <?= escape($logoColors['primary']) ?>;
            --logo-secondary: <?= escape($logoColors['secondary']) ?>;
            --logo-accent: <?= escape($logoColors['accent']) ?>;
            --logo-tertiary: <?= escape($logoColors['tertiary']) ?>;
            --logo-quaternary: <?= escape($logoColors['quaternary']) ?>;
        }
        
        /* Override Tailwind blue colors with logo colors */
        .bg-blue-50 { background-color: color-mix(in srgb, var(--logo-primary) 10%, white) !important; }
        .bg-blue-100 { background-color: color-mix(in srgb, var(--logo-primary) 20%, white) !important; }
        .bg-blue-500 { background-color: var(--logo-accent) !important; }
        .bg-blue-600 { background-color: var(--logo-primary) !important; }
        .bg-blue-700 { background-color: var(--logo-secondary) !important; }
        
        .text-blue-500 { color: var(--logo-accent) !important; }
        .text-blue-600 { color: var(--logo-primary) !important; }
        .text-blue-700 { color: var(--logo-secondary) !important; }
        
        .border-blue-500 { border-color: var(--logo-accent) !important; }
        .border-blue-600 { border-color: var(--logo-primary) !important; }
        
        .hover\:bg-blue-50:hover { background-color: color-mix(in srgb, var(--logo-primary) 10%, white) !important; }
        .hover\:bg-blue-100:hover { background-color: color-mix(in srgb, var(--logo-primary) 20%, white) !important; }
        .hover\:bg-blue-600:hover { background-color: var(--logo-primary) !important; }
        .hover\:bg-blue-700:hover { background-color: var(--logo-secondary) !important; }
        
        .hover\:text-blue-600:hover { color: var(--logo-primary) !important; }
        .hover\:border-blue-500:hover { border-color: var(--logo-accent) !important; }
        .hover\:border-blue-600:hover { border-color: var(--logo-primary) !important; }
        
        /* Gradient overrides */
        .from-blue-50 { --tw-gradient-from: color-mix(in srgb, var(--logo-primary) 10%, white) !important; }
        .from-blue-600 { --tw-gradient-from: var(--logo-primary) !important; }
        .from-blue-700 { --tw-gradient-from: var(--logo-secondary) !important; }
        .via-indigo-600 { --tw-gradient-stops: var(--tw-gradient-from), var(--logo-accent) var(--tw-gradient-via-position), var(--tw-gradient-to) !important; }
        .to-indigo-600 { --tw-gradient-to: var(--logo-accent) !important; }
        .to-indigo-700 { --tw-gradient-to: var(--logo-secondary) !important; }
        .to-purple-600 { --tw-gradient-to: var(--logo-tertiary) !important; }
        .to-purple-700 { --tw-gradient-to: var(--logo-secondary) !important; }
        
        .hover\:from-blue-700:hover { --tw-gradient-from: var(--logo-secondary) !important; }
        .hover\:via-indigo-700:hover { --tw-gradient-stops: var(--tw-gradient-from), var(--logo-secondary) var(--tw-gradient-via-position), var(--tw-gradient-to) !important; }
        .hover\:to-purple-700:hover { --tw-gradient-to: var(--logo-secondary) !important; }
        
        /* Focus states */
        .focus\:ring-blue-500\/20:focus { --tw-ring-color: color-mix(in srgb, var(--logo-primary) 20%, transparent) !important; }
        .focus\:border-blue-500:focus { border-color: var(--logo-accent) !important; }
        .focus\:border-blue-600:focus { border-color: var(--logo-primary) !important; }
        .group-focus-within\:text-blue-600.group:focus-within { color: var(--logo-primary) !important; }
        
        /* Shadow colors */
        .shadow-blue-500\/50 { box-shadow: 0 10px 15px -3px color-mix(in srgb, var(--logo-primary) 50%, transparent), 0 4px 6px -4px color-mix(in srgb, var(--logo-primary) 50%, transparent) !important; }
        
        /* Apply logo colors to key elements */
        .btn-primary,
        .btn-primary-sm {
            background-color: var(--logo-primary) !important;
            color: white !important;
            border-color: var(--logo-primary) !important;
        }
        
        .btn-primary:hover,
        .btn-primary-sm:hover {
            background-color: var(--logo-secondary) !important;
            border-color: var(--logo-secondary) !important;
        }
        
        .gradient-primary {
            background: linear-gradient(135deg, var(--logo-primary) 0%, var(--logo-secondary) 100%);
        }
        
        .text-primary {
            color: var(--logo-primary) !important;
        }
        
        .border-primary {
            border-color: var(--logo-primary) !important;
        }
        
        /* Navigation active state */
        .nav-link-ultra.active,
        .nav-link-ultra:hover {
            color: var(--logo-primary) !important;
        }
        
        /* Buttons */
        button.btn-primary,
        a.btn-primary {
            background: linear-gradient(135deg, var(--logo-primary) 0%, var(--logo-accent) 100%);
        }
        
        button.btn-primary:hover,
        a.btn-primary:hover {
            background: linear-gradient(135deg, var(--logo-secondary) 0%, var(--logo-primary) 100%);
        }
        
        /* Logo icon gradient */
        .bg-gradient-to-br.from-blue-600 {
            background: linear-gradient(to bottom right, var(--logo-primary), var(--logo-accent), var(--logo-tertiary)) !important;
        }
        
        /* Site name gradient - Removed as requested */
        
        /* Mobile menu items */
        .mobile-menu-item-ultra:hover {
            background: linear-gradient(to right, color-mix(in srgb, var(--logo-primary) 10%, white), color-mix(in srgb, var(--logo-accent) 10%, white)) !important;
        }
        
        /* Modern Navigation Bar - Premium Design */
        #main-nav {
            background: rgba(255, 255, 255, 0.99);
            backdrop-filter: blur(16px) saturate(200%);
            -webkit-backdrop-filter: blur(16px) saturate(200%);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03), 
                        0 2px 4px -1px rgba(0, 0, 0, 0.02),
                        0 0 0 1px rgba(0, 0, 0, 0.02);
            border-bottom: 1px solid rgba(229, 231, 235, 0.4);
        }
        
        /* Navigation container improvements */
        #main-nav .container {
            max-width: 1400px;
        }
        
        /* Smooth scroll behavior */
        @media (prefers-reduced-motion: no-preference) {
            #main-nav {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
        }
        
        /* Enhanced Navigation Links */
        .nav-link-modern {
            position: relative;
            color: #4b5563;
            font-weight: 500;
            font-size: 0.9375rem;
            padding: 0.625rem 1rem;
            border-radius: 0.625rem;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-link-modern:hover {
            color: var(--logo-primary, #2563eb);
            background: rgba(59, 130, 246, 0.08);
            transform: translateY(-1px);
        }
        
        .nav-link-modern.active {
            color: var(--logo-primary, #2563eb);
            background: rgba(59, 130, 246, 0.1);
            font-weight: 600;
        }
        
        /* Modern Action Buttons */
        .nav-action-btn {
            position: relative;
            padding: 0.625rem 1rem;
            border-radius: 0.625rem;
            font-weight: 500;
            font-size: 0.9375rem;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-action-btn:hover {
            transform: translateY(-1px);
        }
        
        /* Badge styling */
        .nav-badge {
            position: absolute;
            top: -0.25rem;
            right: -0.25rem;
            min-width: 1.25rem;
            height: 1.25rem;
            padding: 0 0.375rem;
            border-radius: 0.625rem;
            font-size: 0.75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
            border: 2px solid white;
        }
        
        /* Search Bar - Visible in Menu */
        .nav-search-container {
            position: relative;
            width: 200px;
            min-width: 180px;
            display: flex !important;
            align-items: center;
            margin-left: 0.5rem;
        }
        
        .nav-search-input {
            width: 100%;
            padding: 0.625rem 0.75rem 0.625rem 2.5rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.625rem;
            background: #ffffff;
            font-size: 0.9375rem;
            font-weight: 500;
            color: #1f2937;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            height: 2.5rem;
            line-height: 1.5;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        
        .nav-search-input::placeholder {
            color: #9ca3af;
            font-weight: 400;
        }
        
        .nav-search-input:focus {
            outline: none;
            border-color: var(--logo-primary, #2563eb);
            background: #ffffff;
            color: #1f2937;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1), 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .nav-search-icon {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: 0.875rem;
            transition: color 0.25s;
            pointer-events: none;
            z-index: 1;
        }
        
        .nav-search-input:focus + .nav-search-icon {
            color: var(--logo-primary, #2563eb);
        }
        
        /* Enhanced Dropdown Menus */
        .nav-dropdown {
            position: absolute;
            top: calc(100% + 0.75rem);
            left: 0;
            min-width: 20rem;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px) saturate(200%);
            -webkit-backdrop-filter: blur(20px) saturate(200%);
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1),
                        0 8px 10px -6px rgba(0, 0, 0, 0.1),
                        0 0 0 1px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(229, 231, 235, 0.5);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-0.5rem);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 50;
        }
        
        .nav-dropdown-group:hover .nav-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
    </style>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
</head>
<body class="bg-white">
    <!-- Premium Modern Navigation -->
    <nav class="sticky top-0 z-50" id="main-nav">
        <div class="container mx-auto px-4 lg:px-6 xl:px-8">
            <div class="flex items-center justify-center md:justify-start h-20 lg:h-24 gap-2">
                <!-- Logo Section -->
                <?php
                // Get site logo from settings
                $logoSetting = db()->fetchOne("SELECT value FROM settings WHERE `key` = 'site_logo'");
                $siteLogo = $logoSetting ? $logoSetting['value'] : null;
                $siteName = db()->fetchOne("SELECT value FROM settings WHERE `key` = 'site_name'");
                $siteNameText = $siteName ? $siteName['value'] : 'ForkliftPro';
                
                // Get logo size settings
                $logoHeightMobile = db()->fetchOne("SELECT value FROM settings WHERE `key` = 'logo_height_mobile'");
                $logoHeightTablet = db()->fetchOne("SELECT value FROM settings WHERE `key` = 'logo_height_tablet'");
                $logoHeightDesktop = db()->fetchOne("SELECT value FROM settings WHERE `key` = 'logo_height_desktop'");
                $logoMaxWidth = db()->fetchOne("SELECT value FROM settings WHERE `key` = 'logo_max_width'");
                
                $logoHeightMobile = $logoHeightMobile ? (int)$logoHeightMobile['value'] : 40;
                $logoHeightTablet = $logoHeightTablet ? (int)$logoHeightTablet['value'] : 56;
                $logoHeightDesktop = $logoHeightDesktop ? (int)$logoHeightDesktop['value'] : 64;
                $logoMaxWidth = $logoMaxWidth && !empty($logoMaxWidth['value']) ? (int)$logoMaxWidth['value'] : null;
                ?>
                <!-- Logo Section - Enhanced -->
                <a href="<?= url() ?>" class="flex items-center gap-3 group flex-shrink-0 z-10">
                    <?php if ($siteLogo): ?>
                        <style>
                            #site-logo {
                                height: <?= escape($logoHeightMobile) ?>px;
                                <?= $logoMaxWidth ? 'max-width: ' . escape($logoMaxWidth) . 'px;' : '' ?>
                                width: auto;
                                object-fit: contain;
                            }
                            @media (min-width: 768px) {
                                #site-logo {
                                    height: <?= escape($logoHeightTablet) ?>px;
                                }
                            }
                            @media (min-width: 1024px) {
                                #site-logo {
                                    height: <?= escape($logoHeightDesktop) ?>px;
                                }
                            }
                        </style>
                        <img src="<?= escape(image_url($siteLogo)) ?>" 
                             alt="<?= escape($siteNameText) ?>" 
                             id="site-logo"
                             class="object-contain transform group-hover:scale-105 transition-transform duration-300">
                    <?php else: ?>
                        <div class="bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-600 p-2.5 md:p-3 rounded-xl transform group-hover:scale-105 group-hover:rotate-2 transition-all duration-300 shadow-md group-hover:shadow-lg">
                            <i class="fas fa-industry text-white text-lg md:text-xl lg:text-2xl"></i>
                        </div>
                    <?php endif; ?>
                    <!-- Site Name - Visible on Mobile -->
                    <span class="xl:hidden font-bold text-base md:text-lg text-gray-800">
                        <?= escape($siteNameText) ?>
                    </span>
                </a>
                
                <!-- Search Field - Aligned Left (Hidden on mobile, small on tablet, full on desktop) -->
                <div class="hidden md:flex items-center ml-2">
                    <div class="relative">
                        <input type="text" 
                               id="advanced-search" 
                               placeholder="Search products..." 
                               autocomplete="off"
                               class="w-32 md:w-48 lg:w-64 px-3 md:px-4 py-2 md:py-2.5 pl-8 md:pl-10 pr-3 md:pr-4 border border-gray-300 rounded-lg bg-white text-gray-900 text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm">
                        <i class="fas fa-search absolute left-2 md:left-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none text-xs md:text-sm"></i>
                        <div id="search-results" class="hidden absolute top-full left-0 mt-2 w-80 md:w-96 rounded-lg shadow-xl bg-white border border-gray-200 z-50 max-h-96 overflow-y-auto"></div>
                    </div>
                </div>
                
                <!-- Mobile Search Icon Button (Alternative) -->
                <button onclick="toggleMobileSearch()" 
                        class="md:hidden ml-2 p-2 text-gray-600 hover:text-blue-600 transition-colors"
                        id="mobile-search-toggle"
                        aria-label="Search">
                    <i class="fas fa-search text-lg"></i>
                </button>
                
                <!-- Mobile Search Overlay -->
                <div id="mobile-search-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 md:hidden">
                    <div class="bg-white p-4">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="relative flex-1">
                                <input type="text" 
                                       id="mobile-advanced-search" 
                                       placeholder="Search products..." 
                                       autocomplete="off"
                                       class="w-full px-4 py-3 pl-10 pr-4 border border-gray-300 rounded-lg bg-white text-gray-900 text-base focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                                <div id="mobile-search-results" class="hidden absolute top-full left-0 mt-2 w-full rounded-lg shadow-xl bg-white border border-gray-200 z-50 max-h-96 overflow-y-auto"></div>
                            </div>
                            <button onclick="toggleMobileSearch()" 
                                    class="px-4 py-3 text-gray-600 hover:text-gray-800">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Desktop Navigation -->
                <?php
                // Use new menu system
                try {
                    require_once __DIR__ . '/../app/Helpers/MenuHelper.php';
                    $headerMenu = \App\Helpers\get_menu_by_location('header');
                    
                    if ($headerMenu && !empty($headerMenu['id'])):
                        echo \App\Helpers\render_menu($headerMenu['id'], ['location' => 'header']);
                    else:
                        // Fallback to old menu
                ?>
                    <div class="hidden xl:flex items-center space-x-1">
                        <a href="<?= url() ?>" class="nav-link-ultra px-4 py-2.5 rounded-xl transition-all duration-300 group relative">
                            <i class="fas fa-home mr-2"></i>Home
                            <span class="nav-link-indicator"></span>
                        </a>
                        
                        <!-- Products Mega Menu -->
                        <div class="nav-dropdown-group relative" id="products-dropdown">
                            <button class="nav-link-modern">
                                <i class="fas fa-box"></i>
                                <span>Products</span>
                                <i class="fas fa-chevron-down text-xs transform group-hover:rotate-180 transition-transform duration-300"></i>
                            </button>
                            <div class="nav-dropdown w-80 overflow-hidden">
                                <div class="p-4 bg-gradient-to-r from-blue-50/60 to-indigo-50/60 border-b border-gray-100/50">
                                    <h3 class="text-xs font-bold text-gray-700 uppercase tracking-wider">Browse Categories</h3>
                                </div>
                                <div class="p-2 max-h-96 overflow-y-auto">
                                    <?php if (!empty($navCategories)): ?>
                                        <?php foreach (array_slice($navCategories, 0, 8) as $cat): ?>
                                        <a href="<?= url('products.php?category=' . escape($cat['slug'])) ?>" 
                                           class="group/item block px-4 py-2.5 rounded-lg hover:bg-blue-50/50 transition-all duration-200 border-l-2 border-transparent hover:border-blue-500 mb-0.5">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium text-gray-700 group-hover/item:text-blue-600 transition-colors"><?= escape($cat['name']) ?></span>
                                                <i class="fas fa-arrow-right text-xs text-gray-400 group-hover/item:text-blue-600 transform group-hover/item:translate-x-1 transition-all duration-200"></i>
                                            </div>
                                        </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <div class="mt-2 pt-2 border-t border-gray-100 p-2">
                                        <a href="<?= url('products.php') ?>" class="block px-4 py-2.5 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white transition-all duration-200 font-semibold text-sm text-center shadow-md hover:shadow-lg">
                                            <i class="fas fa-th mr-2"></i>View All Products
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <a href="<?= url('compare.php') ?>" class="nav-action-btn relative text-gray-600 hover:text-blue-600">
                            <i class="fas fa-balance-scale"></i>
                            <span class="hidden 2xl:inline">Compare</span>
                            <span id="compare-count" class="nav-badge hidden bg-gradient-to-r from-blue-500 to-indigo-500 text-white">0</span>
                        </a>
                        
                        <a href="<?= url('wishlist.php') ?>" class="nav-action-btn relative text-gray-600 hover:text-red-600">
                            <i class="fas fa-heart"></i>
                            <span class="hidden 2xl:inline">Wishlist</span>
                            <span id="wishlist-count" class="nav-badge hidden bg-gradient-to-r from-red-500 to-pink-500 text-white">0</span>
                        </a>
                        
                        <a href="<?= url('cart.php') ?>" class="nav-action-btn relative text-gray-600 hover:text-green-600">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="hidden 2xl:inline">Cart</span>
                            <span id="cart-count" class="nav-badge hidden bg-gradient-to-r from-green-500 to-emerald-500 text-white">0</span>
                        </a>
                        
                        <a href="<?= url('contact.php') ?>" class="nav-link-modern">
                            <i class="fas fa-envelope"></i>
                            <span>Contact</span>
                        </a>
                    </div>
                <?php 
                    endif;
                } catch (\Exception $e) {
                    // Fallback to old menu on error
                ?>
                    <div class="hidden xl:flex items-center gap-0.5">
                        <a href="<?= url() ?>" class="nav-link-modern">
                            <i class="fas fa-home"></i>
                            <span>Home</span>
                        </a>
                        <a href="<?= url('products.php') ?>" class="nav-link-modern">
                            <i class="fas fa-box"></i>
                            <span>Products</span>
                        </a>
                        <a href="<?= url('contact.php') ?>" class="nav-link-modern">
                            <i class="fas fa-envelope"></i>
                            <span>Contact</span>
                        </a>
                    </div>
                <?php } ?>
                    
                    <?php if (isset($_SESSION['customer_id'])): ?>
                        <div class="nav-dropdown-group relative ml-2" id="account-dropdown">
                            <button class="nav-link-modern">
                                <i class="fas fa-user-circle"></i>
                                <span>Account</span>
                                <i class="fas fa-chevron-down text-xs transform group-hover:rotate-180 transition-transform duration-300"></i>
                            </button>
                            <div class="nav-dropdown right-0 left-auto w-56">
                                <div class="p-2">
                                    <a href="<?= url('account.php') ?>" class="group/item block px-4 py-2.5 rounded-lg hover:bg-blue-50/50 transition-all duration-200 border-l-2 border-transparent hover:border-blue-500 mb-0.5">
                                        <i class="fas fa-user mr-3 text-blue-600 text-sm"></i><span class="text-sm font-medium text-gray-700 group-hover/item:text-blue-600">My Account</span>
                                    </a>
                                    <a href="<?= url('logout.php') ?>" class="group/item block px-4 py-2.5 rounded-lg hover:bg-red-50/50 transition-all duration-200 border-l-2 border-transparent hover:border-red-500">
                                        <i class="fas fa-sign-out-alt mr-3 text-red-600 text-sm"></i><span class="text-sm font-medium text-red-600">Logout</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Mobile Bottom Navigation - OUTSIDE main nav to position at bottom -->
    <div id="mobile-bottom-nav" class="xl:hidden" style="position: fixed !important; bottom: 0 !important; left: 0 !important; right: 0 !important; top: auto !important; z-index: 9999 !important; width: 100% !important;">
        <nav class="mobile-bottom-nav-container">
            <?php
            // Define menu items with icons
            $menuItems = [
                ['url' => url(), 'icon' => 'fa-home', 'label' => 'Home', 'color' => 'blue'],
                ['url' => url('products.php'), 'icon' => 'fa-box', 'label' => 'Products', 'color' => 'indigo'],
                ['url' => url('cart.php'), 'icon' => 'fa-shopping-cart', 'label' => 'Cart', 'color' => 'green', 'badge' => 'cart-count'],
                ['url' => url('wishlist.php'), 'icon' => 'fa-heart', 'label' => 'Wishlist', 'color' => 'red'],
            ];
            
            // Additional menu items for "More" popup
            $moreMenuItems = [
                ['url' => url('compare.php'), 'icon' => 'fa-balance-scale', 'label' => 'Compare', 'color' => 'blue', 'badge' => 'compare-count'],
                ['url' => url('contact.php'), 'icon' => 'fa-envelope', 'label' => 'Contact', 'color' => 'purple'],
                ['url' => url('quote.php'), 'icon' => 'fa-calculator', 'label' => 'Get Quote', 'color' => 'blue'],
            ];
            
            // Add account items based on login status
            if (isset($_SESSION['customer_id'])) {
                $moreMenuItems[] = ['url' => url('account.php'), 'icon' => 'fa-user', 'label' => 'Account', 'color' => 'indigo'];
                $moreMenuItems[] = ['url' => url('logout.php'), 'icon' => 'fa-sign-out-alt', 'label' => 'Logout', 'color' => 'red'];
            } else {
                $moreMenuItems[] = ['url' => url('login.php'), 'icon' => 'fa-sign-in-alt', 'label' => 'Login', 'color' => 'blue'];
                $moreMenuItems[] = ['url' => url('register.php'), 'icon' => 'fa-user-plus', 'label' => 'Sign Up', 'color' => 'indigo'];
            }
            
            // Show first 4 items in bottom nav
            foreach (array_slice($menuItems, 0, 4) as $item):
                $currentUrl = parse_url($item['url'], PHP_URL_PATH);
                $currentPage = basename($_SERVER['PHP_SELF']);
                $itemPage = basename($currentUrl);
                $isActive = ($currentPage === $itemPage) || 
                            ($item['url'] === url() && $currentPage === 'index.php') ||
                            (strpos($item['url'], 'products.php') !== false && $currentPage === 'products.php');
            ?>
                <a href="<?= $item['url'] ?>" 
                   class="mobile-bottom-nav-item <?= $isActive ? 'active' : '' ?>"
                   data-color="<?= $item['color'] ?>">
                    <div class="mobile-bottom-nav-icon">
                        <i class="fas <?= $item['icon'] ?>"></i>
                        <?php if (isset($item['badge'])): ?>
                            <span class="mobile-bottom-nav-badge" id="<?= $item['badge'] ?>-mobile">0</span>
                        <?php endif; ?>
                    </div>
                    <span class="mobile-bottom-nav-label"><?= $item['label'] ?></span>
                </a>
            <?php endforeach; ?>
            
            <!-- More Button (if there are more items) -->
            <?php if (count($moreMenuItems) > 0): ?>
                <button onclick="toggleMobileMoreMenu()" 
                        class="mobile-bottom-nav-item mobile-bottom-nav-more"
                        id="mobile-more-btn">
                    <div class="mobile-bottom-nav-icon">
                        <i class="fas fa-ellipsis-h"></i>
                    </div>
                    <span class="mobile-bottom-nav-label">More</span>
                </button>
            <?php endif; ?>
        </nav>
    </div>
    
    <!-- Mobile More Menu Popup -->
    <div id="mobile-more-menu" class="mobile-more-menu-overlay hidden" onclick="if(event.target === this) toggleMobileMoreMenu()">
        <div class="mobile-more-menu-backdrop"></div>
        <div class="mobile-more-menu-content" onclick="event.stopPropagation()">
            <div class="mobile-more-menu-header">
                <h3 class="mobile-more-menu-title">Menu</h3>
                <button onclick="toggleMobileMoreMenu()" class="mobile-more-menu-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mobile-more-menu-grid">
                <?php foreach ($moreMenuItems as $item): ?>
                    <a href="<?= $item['url'] ?>" 
                       class="mobile-more-menu-item"
                       data-color="<?= $item['color'] ?>"
                       onclick="toggleMobileMoreMenu()">
                        <div class="mobile-more-menu-icon">
                            <i class="fas <?= $item['icon'] ?>"></i>
                            <?php if (isset($item['badge'])): ?>
                                <span class="mobile-more-menu-badge" id="<?= $item['badge'] ?>-more">0</span>
                            <?php endif; ?>
                        </div>
                        <span class="mobile-more-menu-label"><?= $item['label'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
