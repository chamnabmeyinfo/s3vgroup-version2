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
    <?php if (basename($_SERVER['PHP_SELF']) === 'products.php'): ?>
    <link rel="stylesheet" href="<?= asset('assets/css/products-responsive.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/app-products.css') ?>">
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
        
        /* Site name gradient */
        .bg-gradient-to-r.from-blue-600 {
            background: linear-gradient(to right, var(--logo-primary), var(--logo-accent), var(--logo-tertiary)) !important;
        }
        
        /* Mobile menu items */
        .mobile-menu-item-ultra:hover {
            background: linear-gradient(to right, color-mix(in srgb, var(--logo-primary) 10%, white), color-mix(in srgb, var(--logo-accent) 10%, white)) !important;
        }
    </style>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Swiper CSS for Slider -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
</head>
<body class="bg-white">
    <!-- Ultra Modern Navigation -->
    <nav class="bg-white backdrop-blur-xl shadow-sm sticky top-0 z-50 border-b border-gray-200/50 transition-all duration-500" id="main-nav" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="flex justify-between items-center h-20 md:h-24">
                <!-- Logo Section -->
                <?php
                // Get site logo from settings
                $logoSetting = db()->fetchOne("SELECT value FROM settings WHERE `key` = 'site_logo'");
                $siteLogo = $logoSetting ? $logoSetting['value'] : null;
                $siteName = db()->fetchOne("SELECT value FROM settings WHERE `key` = 'site_name'");
                $siteNameText = $siteName ? $siteName['value'] : 'ForkliftPro';
                ?>
                <a href="<?= url() ?>" class="flex items-center space-x-3 group flex-shrink-0 z-10">
                    <?php if ($siteLogo): ?>
                        <img src="<?= escape(image_url($siteLogo)) ?>" 
                             alt="<?= escape($siteNameText) ?>" 
                             class="h-12 md:h-16 w-auto object-contain transform group-hover:scale-105 transition-all duration-300">
                    <?php else: ?>
                        <div class="bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-600 p-2.5 md:p-3 rounded-2xl transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300 shadow-lg group-hover:shadow-2xl group-hover:shadow-blue-500/50">
                            <i class="fas fa-industry text-white text-xl md:text-2xl"></i>
                        </div>
                    <?php endif; ?>
                </a>
                
                <!-- Desktop Navigation -->
                <div class="hidden xl:flex items-center space-x-2">
                    <a href="<?= url() ?>" class="nav-link-ultra px-4 py-2.5 rounded-xl transition-all duration-300 group relative">
                        <i class="fas fa-home mr-2"></i>Home
                        <span class="nav-link-indicator"></span>
                    </a>
                    
                    <!-- Products Mega Menu -->
                    <div class="relative group" id="products-dropdown">
                        <button class="nav-link-ultra px-4 py-2.5 rounded-xl transition-all duration-300 group relative flex items-center">
                            <i class="fas fa-box mr-2"></i>Products
                            <i class="fas fa-chevron-down ml-2 text-xs transform group-hover:rotate-180 transition-transform duration-300"></i>
                            <span class="nav-link-indicator"></span>
                        </button>
                        <div class="absolute top-full left-0 mt-3 w-80 rounded-3xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-500 transform translate-y-4 group-hover:translate-y-0 border border-gray-200/50 overflow-hidden" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);">
                            <div class="p-3 bg-gradient-to-r from-blue-50/50 to-indigo-50/50 border-b border-gray-100">
                                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider px-3 py-2">Browse Categories</h3>
                            </div>
                            <div class="p-2 max-h-96 overflow-y-auto">
                                <?php if (!empty($navCategories)): ?>
                                    <?php foreach (array_slice($navCategories, 0, 8) as $cat): ?>
                                    <a href="<?= url('products.php?category=' . escape($cat['slug'])) ?>" 
                                       class="group/item block px-4 py-3 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:via-indigo-50 hover:to-purple-50 transition-all duration-300 border-l-4 border-transparent hover:border-blue-500 hover:shadow-md mb-1">
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold text-gray-800 group-hover/item:text-blue-600 transition-colors"><?= escape($cat['name']) ?></span>
                                            <i class="fas fa-arrow-right text-gray-400 group-hover/item:text-blue-600 transform group-hover/item:translate-x-2 transition-all duration-300"></i>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <div class="mt-3 pt-3 border-t border-gray-200">
                                    <a href="<?= url('products.php') ?>" class="block px-4 py-3.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white transition-all duration-300 font-bold text-center shadow-lg hover:shadow-xl transform hover:scale-105">
                                        <i class="fas fa-th mr-2"></i>View All Products
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <a href="<?= url('compare.php') ?>" class="nav-link-ultra px-4 py-2.5 rounded-xl transition-all duration-300 relative group">
                        <i class="fas fa-balance-scale mr-2"></i>Compare
                        <span id="compare-count" class="hidden absolute -top-1.5 -right-1.5 bg-gradient-to-r from-blue-500 to-indigo-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center font-bold shadow-lg ring-2 ring-white">0</span>
                        <span class="nav-link-indicator"></span>
                    </a>
                    
                    <a href="<?= url('wishlist.php') ?>" class="nav-link-ultra px-4 py-2.5 rounded-xl transition-all duration-300 relative group">
                        <i class="fas fa-heart mr-2"></i>Wishlist
                        <span id="wishlist-count" class="hidden absolute -top-1.5 -right-1.5 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center font-bold shadow-lg ring-2 ring-white">0</span>
                        <span class="nav-link-indicator"></span>
                    </a>
                    
                    <a href="<?= url('cart.php') ?>" class="nav-link-ultra px-4 py-2.5 rounded-xl transition-all duration-300 relative group">
                        <i class="fas fa-shopping-cart mr-2"></i>Cart
                        <span id="cart-count" class="hidden absolute -top-1.5 -right-1.5 bg-gradient-to-r from-green-500 to-emerald-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center font-bold shadow-lg ring-2 ring-white">0</span>
                        <span class="nav-link-indicator"></span>
                    </a>
                    
                    <a href="<?= url('contact.php') ?>" class="nav-link-ultra px-4 py-2.5 rounded-xl transition-all duration-300 group relative">
                        <i class="fas fa-envelope mr-2"></i>Contact
                        <span class="nav-link-indicator"></span>
                    </a>
                    
                    <?php if (isset($_SESSION['customer_id'])): ?>
                        <div class="relative group ml-2" id="account-dropdown">
                            <button class="nav-link-ultra px-4 py-2.5 rounded-xl transition-all duration-300 flex items-center group relative">
                                <i class="fas fa-user-circle mr-2"></i>Account
                                <i class="fas fa-chevron-down ml-2 text-xs transform group-hover:rotate-180 transition-transform duration-300"></i>
                                <span class="nav-link-indicator"></span>
                            </button>
                            <div class="absolute top-full right-0 mt-3 w-64 rounded-3xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-500 transform translate-y-4 group-hover:translate-y-0 border border-gray-200/50 overflow-hidden" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);">
                                <div class="p-2">
                                    <a href="<?= url('account.php') ?>" class="group/item block px-4 py-3 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:via-indigo-50 transition-all duration-300 border-l-4 border-transparent hover:border-blue-500 mb-1">
                                        <i class="fas fa-user mr-3 text-blue-600"></i><span class="font-semibold text-gray-800 group-hover/item:text-blue-600">My Account</span>
                                    </a>
                                    <a href="<?= url('logout.php') ?>" class="group/item block px-4 py-3 rounded-xl hover:bg-gradient-to-r hover:from-red-50 hover:to-pink-50 transition-all duration-300 border-l-4 border-transparent hover:border-red-500">
                                        <i class="fas fa-sign-out-alt mr-3 text-red-600"></i><span class="font-semibold text-red-600">Logout</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= url('login.php') ?>" class="nav-link-ultra px-4 py-2.5 rounded-xl transition-all duration-300 group relative">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                            <span class="nav-link-indicator"></span>
                        </a>
                        <a href="<?= url('register.php') ?>" class="bg-gradient-to-r from-gray-800 to-gray-900 text-white px-5 py-2.5 rounded-xl font-bold hover:from-gray-900 hover:to-black transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 ml-2">
                            Sign Up
                        </a>
                    <?php endif; ?>
                    
                    <a href="<?= url('quote.php') ?>" class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white px-6 py-2.5 rounded-xl font-bold hover:from-blue-700 hover:via-indigo-700 hover:to-purple-700 transform hover:scale-105 transition-all duration-300 shadow-xl hover:shadow-2xl hover:shadow-blue-500/50 ml-3">
                        <i class="fas fa-calculator mr-2"></i>Get Quote
                    </a>
                </div>
                
                <!-- Search Bar (Desktop) - Enhanced -->
                <div class="hidden lg:block flex-1 max-w-xl mx-6 xl:mx-8">
                    <div class="relative group">
                        <input type="text" 
                               id="advanced-search" 
                               placeholder="Search products, categories..." 
                               autocomplete="off"
                               class="w-full px-6 py-3.5 pl-14 pr-12 border-2 border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 bg-gray-50/80 focus:bg-white shadow-sm focus:shadow-xl backdrop-blur-sm">
                        <i class="fas fa-search absolute left-5 top-1/2 transform -translate-y-1/2 text-gray-400 group-focus-within:text-blue-600 transition-colors"></i>
                        <button class="absolute right-3 top-1/2 transform -translate-y-1/2 px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all opacity-0 group-focus-within:opacity-100 pointer-events-none group-focus-within:pointer-events-auto">
                            <i class="fas fa-arrow-right text-xs"></i>
                        </button>
                        <div id="search-results" class="hidden absolute top-full left-0 right-0 mt-3 border-2 border-gray-200 rounded-3xl shadow-2xl z-50 max-h-96 overflow-y-auto" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);"></div>
                    </div>
                </div>
                
                <!-- Mobile Menu Button - Enhanced -->
                <button id="mobile-menu-btn" class="xl:hidden text-gray-700 p-2.5 rounded-xl hover:bg-gray-100 transition-all duration-300 relative z-10">
                    <i class="fas fa-bars text-2xl transition-transform duration-300" id="menu-icon"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu (Ultra Modern Slide Animation) -->
        <div id="mobile-menu" class="xl:hidden border-t border-gray-200/50 shadow-2xl transform -translate-y-full transition-all duration-500 ease-out fixed inset-x-0 top-20 z-40 max-h-[calc(100vh-5rem)] overflow-y-auto" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);">
            <div class="container mx-auto px-4 py-6 space-y-2">
                <!-- Mobile Search (Always Visible) -->
                <div class="mb-4 pb-4 border-b border-gray-200">
                    <div class="relative">
                        <input type="text" 
                               id="mobile-search-input" 
                               placeholder="Search products..." 
                               autocomplete="off"
                               class="w-full px-5 py-3.5 pl-12 pr-4 border-2 border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 bg-gray-50/80 focus:bg-white shadow-sm">
                        <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
                
                <a href="<?= url() ?>" class="mobile-menu-item-ultra block px-5 py-3.5 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300 group">
                    <i class="fas fa-home mr-3 text-blue-600 group-hover:scale-110 transition-transform"></i>
                    <span class="font-semibold text-gray-800">Home</span>
                </a>
                
                <!-- Mobile Products Accordion - Enhanced -->
                <div class="mobile-menu-accordion">
                    <button class="mobile-menu-item-ultra w-full text-left px-5 py-3.5 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300 flex items-center justify-between group" onclick="toggleMobileAccordion(this)">
                        <span class="flex items-center">
                            <i class="fas fa-box mr-3 text-blue-600 group-hover:scale-110 transition-transform"></i>
                            <span class="font-semibold text-gray-800">Products</span>
                        </span>
                        <i class="fas fa-chevron-down transform transition-transform duration-300 text-gray-400"></i>
                    </button>
                    <div class="hidden pl-4 mt-2 space-y-1">
                        <?php if (!empty($navCategories)): ?>
                            <?php foreach ($navCategories as $cat): ?>
                            <a href="<?= url('products.php?category=' . escape($cat['slug'])) ?>" 
                               class="block px-4 py-2.5 rounded-xl hover:bg-blue-50 transition-all duration-300 text-gray-700 font-medium border-l-2 border-transparent hover:border-blue-500">
                                <?= escape($cat['name']) ?>
                            </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <a href="<?= url('products.php') ?>" class="block px-4 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white transition-all duration-300 font-bold text-center mt-2 shadow-lg">
                            <i class="fas fa-th mr-2"></i>View All Products
                        </a>
                    </div>
                </div>
                
                <a href="<?= url('compare.php') ?>" class="mobile-menu-item-ultra block px-5 py-3.5 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-300 relative group">
                    <i class="fas fa-balance-scale mr-3 text-blue-600 group-hover:scale-110 transition-transform"></i>
                    <span class="font-semibold text-gray-800">Compare</span>
                    <span id="compare-count-mobile" class="hidden absolute top-2 right-4 bg-gradient-to-r from-blue-500 to-indigo-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center font-bold shadow-lg ring-2 ring-white">0</span>
                </a>
                <a href="<?= url('wishlist.php') ?>" class="mobile-menu-item-ultra block px-5 py-3.5 rounded-xl hover:bg-gradient-to-r hover:from-red-50 hover:to-pink-50 transition-all duration-300 group">
                    <i class="fas fa-heart mr-3 text-red-600 group-hover:scale-110 transition-transform"></i>
                    <span class="font-semibold text-gray-800">Wishlist</span>
                </a>
                <a href="<?= url('cart.php') ?>" class="mobile-menu-item-ultra block px-5 py-3.5 rounded-xl hover:bg-gradient-to-r hover:from-green-50 hover:to-emerald-50 transition-all duration-300 group">
                    <i class="fas fa-shopping-cart mr-3 text-green-600 group-hover:scale-110 transition-transform"></i>
                    <span class="font-semibold text-gray-800">Cart</span>
                </a>
                <a href="<?= url('contact.php') ?>" class="mobile-menu-item-ultra block px-5 py-3.5 rounded-xl hover:bg-gradient-to-r hover:from-purple-50 hover:to-pink-50 transition-all duration-300 group">
                    <i class="fas fa-envelope mr-3 text-purple-600 group-hover:scale-110 transition-transform"></i>
                    <span class="font-semibold text-gray-800">Contact</span>
                </a>
                
                <?php if (isset($_SESSION['customer_id'])): ?>
                    <a href="<?= url('account.php') ?>" class="mobile-menu-item-ultra block px-5 py-3.5 rounded-xl hover:bg-gradient-to-r hover:from-indigo-50 hover:to-blue-50 transition-all duration-300 group">
                        <i class="fas fa-user mr-3 text-indigo-600 group-hover:scale-110 transition-transform"></i>
                        <span class="font-semibold text-gray-800">My Account</span>
                    </a>
                    <a href="<?= url('logout.php') ?>" class="mobile-menu-item-ultra block px-5 py-3.5 rounded-xl hover:bg-gradient-to-r hover:from-red-50 hover:to-pink-50 transition-all duration-300 text-red-600 group">
                        <i class="fas fa-sign-out-alt mr-3 group-hover:scale-110 transition-transform"></i>
                        <span class="font-semibold">Logout</span>
                    </a>
                <?php else: ?>
                    <a href="<?= url('login.php') ?>" class="mobile-menu-item-ultra block px-5 py-3.5 rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-blue-50 transition-all duration-300 group">
                        <i class="fas fa-sign-in-alt mr-3 text-blue-600 group-hover:scale-110 transition-transform"></i>
                        <span class="font-semibold text-gray-800">Login</span>
                    </a>
                    <a href="<?= url('register.php') ?>" class="mobile-menu-item-ultra block px-5 py-3.5 rounded-xl hover:bg-gradient-to-r hover:from-gray-50 hover:to-indigo-50 transition-all duration-300 group">
                        <i class="fas fa-user-plus mr-3 text-indigo-600 group-hover:scale-110 transition-transform"></i>
                        <span class="font-semibold text-gray-800">Sign Up</span>
                    </a>
                <?php endif; ?>
                
                <a href="<?= url('quote.php') ?>" class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white px-5 py-3.5 rounded-xl font-bold hover:from-blue-700 hover:via-indigo-700 hover:to-purple-700 transform hover:scale-105 transition-all duration-300 shadow-xl hover:shadow-2xl text-center block mt-4">
                    <i class="fas fa-calculator mr-2"></i>Get Quote
                </a>
            </div>
        </div>
    </nav>
