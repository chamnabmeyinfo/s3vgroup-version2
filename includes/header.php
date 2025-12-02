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
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Swiper CSS for Slider -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
</head>
<body class="bg-white">
    <!-- Modern Navigation -->
    <nav class="bg-white/95 backdrop-blur-md shadow-lg sticky top-0 z-50 border-b border-gray-100 transition-all duration-300" id="main-nav">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-20 md:h-24">
                <!-- Logo -->
                <?php
                // Get site logo from settings
                $logoSetting = db()->fetchOne("SELECT value FROM settings WHERE `key` = 'site_logo'");
                $siteLogo = $logoSetting ? $logoSetting['value'] : null;
                $siteName = db()->fetchOne("SELECT value FROM settings WHERE `key` = 'site_name'");
                $siteNameText = $siteName ? $siteName['value'] : 'ForkliftPro';
                ?>
                <a href="<?= url() ?>" class="flex items-center space-x-3 group flex-shrink-0">
                    <?php if ($siteLogo): ?>
                        <img src="<?= escape(image_url($siteLogo)) ?>" 
                             alt="<?= escape($siteNameText) ?>" 
                             class="h-12 md:h-16 w-auto object-contain transform group-hover:scale-105 transition-transform duration-300">
                    <?php else: ?>
                        <div class="bg-gradient-to-br from-blue-600 to-indigo-600 p-2 md:p-3 rounded-xl transform group-hover:scale-110 transition-transform duration-300 shadow-lg group-hover:shadow-xl">
                            <i class="fas fa-industry text-white text-xl md:text-2xl"></i>
                        </div>
                    <?php endif; ?>
                    <div class="hidden sm:block">
                        <span class="text-xl md:text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent"><?= escape($siteNameText) ?></span>
                        <p class="text-xs text-gray-500 -mt-1 hidden md:block">Industrial Solutions</p>
                    </div>
                </a>
                
                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center space-x-1">
                    <a href="<?= url() ?>" class="nav-link-modern px-4 py-2 rounded-lg transition-all">
                        <i class="fas fa-home mr-2"></i>Home
                    </a>
                    
                    <!-- Products Dropdown -->
                    <div class="relative group" id="products-dropdown">
                        <button class="nav-link-modern px-4 py-2.5 rounded-xl transition-all duration-300 hover:bg-blue-50 hover:text-blue-600 font-medium text-gray-700 flex items-center">
                            <i class="fas fa-box mr-2"></i>Products
                            <i class="fas fa-chevron-down ml-2 text-xs transform group-hover:rotate-180 transition-transform duration-300"></i>
                        </button>
                        <div class="absolute top-full left-0 mt-2 w-72 bg-white rounded-2xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform translate-y-2 group-hover:translate-y-0 border border-gray-100 overflow-hidden">
                            <div class="p-2">
                                    <?php if (!empty($navCategories)): ?>
                                    <?php foreach (array_slice($navCategories, 0, 6) as $cat): ?>
                                    <a href="<?= url('products.php?category=' . escape($cat['slug'])) ?>" 
                                       class="block px-4 py-3 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group/item border-l-4 border-transparent hover:border-blue-500">
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium text-gray-800 group-hover/item:text-blue-600"><?= escape($cat['name']) ?></span>
                                            <i class="fas fa-arrow-right text-gray-400 group-hover/item:text-blue-600 transform group-hover/item:translate-x-1 transition-all duration-200"></i>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <div class="border-t border-gray-100 mt-2 pt-2 bg-gradient-to-r from-blue-50 to-indigo-50">
                                    <a href="<?= url('products.php') ?>" class="block px-4 py-3 rounded-xl hover:bg-blue-600 hover:text-white transition-all duration-200 font-semibold text-blue-600">
                                        <i class="fas fa-th mr-2"></i>View All Products
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <a href="<?= url('compare.php') ?>" class="nav-link-modern px-4 py-2.5 rounded-xl transition-all duration-300 hover:bg-blue-50 hover:text-blue-600 font-medium text-gray-700 relative">
                        <i class="fas fa-balance-scale mr-2"></i>Compare
                        <span id="compare-count" class="hidden absolute -top-1 -right-1 bg-gradient-to-r from-blue-500 to-indigo-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center animate-pulse shadow-lg">0</span>
                    </a>
                    
                    <a href="<?= url('wishlist.php') ?>" class="nav-link-modern px-4 py-2.5 rounded-xl transition-all duration-300 hover:bg-red-50 hover:text-red-600 font-medium text-gray-700 relative">
                        <i class="fas fa-heart mr-2"></i>Wishlist
                        <span id="wishlist-count" class="hidden absolute -top-1 -right-1 bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center animate-pulse shadow-lg">0</span>
                    </a>
                    
                    <a href="<?= url('cart.php') ?>" class="nav-link-modern px-4 py-2.5 rounded-xl transition-all duration-300 hover:bg-green-50 hover:text-green-600 font-medium text-gray-700 relative">
                        <i class="fas fa-shopping-cart mr-2"></i>Cart
                        <span id="cart-count" class="hidden absolute -top-1 -right-1 bg-gradient-to-r from-green-500 to-emerald-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center animate-pulse shadow-lg">0</span>
                    </a>
                    
                    <a href="<?= url('contact.php') ?>" class="nav-link-modern px-4 py-2.5 rounded-xl transition-all duration-300 hover:bg-purple-50 hover:text-purple-600 font-medium text-gray-700">
                        <i class="fas fa-envelope mr-2"></i>Contact
                    </a>
                    
                    <?php if (isset($_SESSION['customer_id'])): ?>
                        <div class="relative group ml-2" id="account-dropdown">
                            <button class="nav-link-modern px-4 py-2.5 rounded-xl transition-all duration-300 hover:bg-indigo-50 hover:text-indigo-600 font-medium text-gray-700 flex items-center">
                                <i class="fas fa-user-circle mr-2"></i>Account
                                <i class="fas fa-chevron-down ml-2 text-xs transform group-hover:rotate-180 transition-transform duration-300"></i>
                            </button>
                            <div class="absolute top-full right-0 mt-2 w-56 bg-white rounded-2xl shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform translate-y-2 group-hover:translate-y-0 border border-gray-100 overflow-hidden">
                                <div class="p-2">
                                    <a href="<?= url('account.php') ?>" class="block px-4 py-3 rounded-xl hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 border-l-4 border-transparent hover:border-blue-500">
                                        <i class="fas fa-user mr-2 text-blue-600"></i>My Account
                                    </a>
                                    <a href="<?= url('logout.php') ?>" class="block px-4 py-3 rounded-xl hover:bg-gradient-to-r hover:from-red-50 hover:to-pink-50 transition-all duration-200 text-red-600 border-l-4 border-transparent hover:border-red-500">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= url('login.php') ?>" class="nav-link-modern px-4 py-2.5 rounded-xl transition-all duration-300 hover:bg-gray-50 hover:text-gray-700 font-medium text-gray-600">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                        <a href="<?= url('register.php') ?>" class="bg-gradient-to-r from-gray-700 to-gray-800 text-white px-4 py-2.5 rounded-xl font-medium hover:from-gray-800 hover:to-gray-900 transition-all duration-300 shadow-md hover:shadow-lg ml-2">
                            Sign Up
                        </a>
                    <?php endif; ?>
                    
                    <a href="<?= url('quote.php') ?>" class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-5 py-2.5 rounded-xl font-semibold hover:from-blue-700 hover:to-indigo-700 transform hover:scale-105 transition-all duration-300 shadow-lg hover:shadow-xl ml-2">
                        <i class="fas fa-calculator mr-2"></i>Get Quote
                    </a>
                </div>
                
                <!-- Search Bar (Desktop) -->
                <div class="hidden lg:block flex-1 max-w-lg mx-8">
                    <div class="relative">
                        <input type="text" 
                               id="advanced-search" 
                               placeholder="Search products, categories..." 
                               autocomplete="off"
                               class="w-full px-5 py-3 pl-12 pr-4 border-2 border-gray-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 bg-gray-50 focus:bg-white shadow-sm focus:shadow-md">
                        <i class="fas fa-search absolute left-5 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <div id="search-results" class="hidden absolute top-full left-0 right-0 mt-2 bg-white border-2 border-gray-200 rounded-2xl shadow-2xl z-50 max-h-96 overflow-y-auto"></div>
                    </div>
                </div>
                
                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="lg:hidden text-gray-700 p-2 rounded-lg hover:bg-gray-100 transition-all">
                    <i class="fas fa-bars text-2xl" id="menu-icon"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu (Slide Animation) -->
        <div id="mobile-menu" class="lg:hidden bg-white border-t shadow-xl transform -translate-y-full transition-transform duration-300 ease-in-out fixed inset-x-0 top-20 z-40 max-h-[calc(100vh-5rem)] overflow-y-auto">
            <div class="container mx-auto px-4 py-6 space-y-2">
                <a href="<?= url() ?>" class="mobile-menu-item block px-4 py-3 rounded-lg hover:bg-blue-50 transition-all">
                    <i class="fas fa-home mr-3 text-blue-600"></i>Home
                </a>
                
                <!-- Mobile Products Accordion -->
                <div class="mobile-menu-accordion">
                    <button class="mobile-menu-item w-full text-left px-4 py-3 rounded-lg hover:bg-blue-50 transition-all flex items-center justify-between" onclick="toggleMobileAccordion(this)">
                        <span><i class="fas fa-box mr-3 text-blue-600"></i>Products</span>
                        <i class="fas fa-chevron-down transform transition-transform"></i>
                    </button>
                    <div class="hidden pl-4 mt-2 space-y-1">
                        <?php if (!empty($navCategories)): ?>
                            <?php foreach ($navCategories as $cat): ?>
                            <a href="<?= url('products.php?category=' . escape($cat['slug'])) ?>" 
                               class="block px-4 py-2 rounded-lg hover:bg-blue-50 transition-all text-gray-700">
                                <?= escape($cat['name']) ?>
                            </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <a href="<?= url('products.php') ?>" class="block px-4 py-2 rounded-lg hover:bg-blue-50 transition-all font-semibold text-blue-600">
                            View All Products
                        </a>
                    </div>
                </div>
                
                <a href="<?= url('compare.php') ?>" class="mobile-menu-item block px-4 py-3 rounded-lg hover:bg-blue-50 transition-all relative">
                    <i class="fas fa-balance-scale mr-3 text-blue-600"></i>Compare
                    <span id="compare-count-mobile" class="hidden absolute top-2 right-4 bg-blue-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">0</span>
                </a>
                <a href="<?= url('wishlist.php') ?>" class="mobile-menu-item block px-4 py-3 rounded-lg hover:bg-blue-50 transition-all">
                    <i class="fas fa-heart mr-3 text-blue-600"></i>Wishlist
                </a>
                <a href="<?= url('cart.php') ?>" class="mobile-menu-item block px-4 py-3 rounded-lg hover:bg-blue-50 transition-all">
                    <i class="fas fa-shopping-cart mr-3 text-blue-600"></i>Cart
                </a>
                <a href="<?= url('contact.php') ?>" class="mobile-menu-item block px-4 py-3 rounded-lg hover:bg-blue-50 transition-all">
                    <i class="fas fa-envelope mr-3 text-blue-600"></i>Contact
                </a>
                
                <?php if (isset($_SESSION['customer_id'])): ?>
                    <a href="<?= url('account.php') ?>" class="mobile-menu-item block px-4 py-3 rounded-lg hover:bg-blue-50 transition-all">
                        <i class="fas fa-user mr-3 text-blue-600"></i>My Account
                    </a>
                    <a href="<?= url('logout.php') ?>" class="mobile-menu-item block px-4 py-3 rounded-lg hover:bg-red-50 transition-all text-red-600">
                        <i class="fas fa-sign-out-alt mr-3"></i>Logout
                    </a>
                <?php else: ?>
                    <a href="<?= url('login.php') ?>" class="mobile-menu-item block px-4 py-3 rounded-lg hover:bg-blue-50 transition-all">
                        <i class="fas fa-sign-in-alt mr-3 text-blue-600"></i>Login
                    </a>
                    <a href="<?= url('register.php') ?>" class="mobile-menu-item block px-4 py-3 rounded-lg hover:bg-blue-50 transition-all">
                        <i class="fas fa-user-plus mr-3 text-blue-600"></i>Sign Up
                    </a>
                <?php endif; ?>
                
                <a href="<?= url('quote.php') ?>" class="btn-primary w-full mt-4 text-center">
                    <i class="fas fa-calculator mr-2"></i>Get Quote
                </a>
            </div>
        </div>
    </nav>

    <!-- Mobile Search (shown when menu is open) -->
    <div id="mobile-search" class="lg:hidden hidden px-4 py-3 bg-gray-50 border-b">
        <div class="relative">
            <input type="text" 
                   id="mobile-search-input" 
                   placeholder="Search products..." 
                   autocomplete="off"
                   class="w-full px-4 py-2.5 pl-11 pr-4 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
        </div>
    </div>
