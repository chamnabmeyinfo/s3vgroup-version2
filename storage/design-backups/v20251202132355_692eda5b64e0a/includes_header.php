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
</head>
<body class="bg-white">
    <!-- Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <a href="<?= url() ?>" class="flex items-center space-x-2">
                    <span class="text-2xl font-bold text-blue-600">ForkliftPro</span>
                </a>
                
                <!-- Advanced Search -->
                <div class="hidden md:block flex-1 max-w-md mx-8">
                    <div class="relative">
                        <input type="text" 
                               id="advanced-search" 
                               placeholder="Search products..." 
                               autocomplete="off"
                               class="w-full px-4 py-2 pl-10 pr-4 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <div id="search-results" class="hidden absolute top-full left-0 right-0 mt-1 bg-white border rounded-lg shadow-xl z-50 max-h-96 overflow-y-auto"></div>
                    </div>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-6">
                    <a href="<?= url() ?>" class="nav-link">Home</a>
                    <a href="<?= url('products.php') ?>" class="nav-link">Products</a>
                    <a href="<?= url('compare.php') ?>" class="nav-link">
                        <i class="fas fa-balance-scale mr-1"></i> Compare
                    </a>
                    <a href="<?= url('wishlist.php') ?>" class="nav-link relative">
                        <i class="fas fa-heart mr-1"></i> Wishlist
                        <span id="wishlist-count" class="hidden absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">0</span>
                    </a>
                    <a href="<?= url('cart.php') ?>" class="nav-link relative">
                        <i class="fas fa-shopping-cart mr-1"></i> Cart
                        <span id="cart-count" class="hidden absolute -top-2 -right-2 bg-blue-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">0</span>
                    </a>
                    <a href="<?= url('contact.php') ?>" class="nav-link">Contact</a>
                    <?php if (isset($_SESSION['customer_id'])): ?>
                        <a href="<?= url('account.php') ?>" class="nav-link">
                            <i class="fas fa-user mr-1"></i> My Account
                        </a>
                        <a href="<?= url('logout.php') ?>" class="nav-link">Logout</a>
                    <?php else: ?>
                        <a href="<?= url('login.php') ?>" class="nav-link">Login</a>
                        <a href="<?= url('register.php') ?>" class="btn-secondary-sm">Sign Up</a>
                    <?php endif; ?>
                    <a href="<?= url('quote.php') ?>" class="btn-primary-sm">Get Quote</a>
                </div>
                
                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden text-gray-700">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t">
            <div class="container mx-auto px-4 py-4 space-y-4">
                <a href="<?= url() ?>" class="block py-2">Home</a>
                <a href="<?= url('products.php') ?>" class="block py-2">Products</a>
                <a href="<?= url('contact.php') ?>" class="block py-2">Contact</a>
                <a href="<?= url('quote.php') ?>" class="btn-primary-sm inline-block">Get Quote</a>
            </div>
        </div>
    </nav>

