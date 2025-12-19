<?php
require_once __DIR__ . '/bootstrap/app.php';

// Check under construction mode
use App\Helpers\UnderConstruction;
UnderConstruction::show();

use App\Models\Product;
use App\Models\Category;

$productModel = new Product();
$categoryModel = new Category();

$filters = [
    'page' => $_GET['page'] ?? 1,
    'limit' => 12
];

if (!empty($_GET['category'])) {
    $category = $categoryModel->getBySlug($_GET['category']);
    if ($category) {
        $filters['category_id'] = $category['id'];
        $categoryName = $category['name'];
    }
}

if (!empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}

if (!empty($_GET['featured'])) {
    $filters['featured'] = true;
}

// Advanced filters
if (!empty($_GET['min_price'])) {
    $filters['min_price'] = (float)$_GET['min_price'];
}

if (!empty($_GET['max_price'])) {
    $filters['max_price'] = (float)$_GET['max_price'];
}

if (!empty($_GET['sort'])) {
    $filters['sort'] = $_GET['sort'];
}

if (!empty($_GET['in_stock'])) {
    $filters['in_stock'] = true;
}

// Get price range for filter
$allProductsForRange = $productModel->getAll([]);
$prices = [];
foreach ($allProductsForRange as $p) {
    $price = !empty($p['sale_price']) ? (float)$p['sale_price'] : (!empty($p['price']) ? (float)$p['price'] : null);
    if ($price !== null) {
        $prices[] = $price;
    }
}
$minPriceRange = !empty($prices) ? min($prices) : 0;
$maxPriceRange = !empty($prices) ? max($prices) : 10000;

$products = $productModel->getAll($filters);
$totalProducts = $productModel->count($filters);
$totalPages = ceil($totalProducts / $filters['limit']);
$categories = $categoryModel->getAll(true);

$pageTitle = 'Products - Forklift & Equipment Pro';
$metaDescription = 'Browse our selection of forklifts and industrial equipment';

include __DIR__ . '/includes/header.php';
?>

<main class="app-products-main">
    <div class="products-page-container">
        <!-- Mobile Filter Button - Only visible on mobile -->
        <button onclick="openMobileFilters()" 
                class="mobile-filter-trigger fixed bottom-6 right-6 z-50 bg-blue-600 text-white p-4 rounded-full shadow-2xl hover:bg-blue-700 transition-all duration-300 transform hover:scale-110 md:hidden"
                id="mobile-filter-trigger"
                title="Open Filters">
            <i class="fas fa-filter text-xl"></i>
            <span class="mobile-filter-badge" id="mobile-filter-count">0</span>
        </button>
        
        <div class="products-page-main">
            <!-- Sidebar Filters - Desktop Design -->
            <aside class="sidebar-filters desktop-sidebar md:w-64 flex-shrink-0 transition-all duration-300 ease-in-out hidden md:block" id="sidebar-filters-desktop">
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 sticky top-24 overflow-hidden" id="sidebar-content">
                    <!-- Toggle Button - Attached to Sidebar -->
                    <button onclick="toggleSidebar()" 
                            class="w-full bg-blue-600 text-white p-3 hover:bg-blue-700 transition-all duration-300 flex items-center justify-center gap-2 font-semibold"
                            id="sidebar-toggle-btn"
                            title="Toggle Filters">
                        <i class="fas fa-filter" id="sidebar-toggle-icon"></i>
                        <span class="sidebar-toggle-text">Filters</span>
                        <i class="fas fa-chevron-left ml-auto sidebar-chevron" id="sidebar-chevron"></i>
                    </button>
                    
                    <!-- Collapsed Icons View -->
                    <div class="sidebar-collapsed-icons">
                        <button type="button" class="sidebar-icon-btn" onclick="expandAndFocus('filters')" title="Expand Filters">
                            <i class="fas fa-filter"></i>
                        </button>
                        <button type="button" class="sidebar-icon-btn" onclick="expandAndFocus('categories')" title="Categories">
                            <i class="fas fa-th-large"></i>
                        </button>
                        <button type="button" class="sidebar-icon-btn" onclick="expandAndFocus('search')" title="Search">
                            <i class="fas fa-search"></i>
                        </button>
                        <button type="button" class="sidebar-icon-btn" onclick="expandAndFocus('price')" title="Price Range">
                            <i class="fas fa-dollar-sign"></i>
                        </button>
                    </div>
                    
                    <!-- Sidebar Content Wrapper -->
                    <div class="sidebar-content-wrapper p-6">
                    
                    <!-- Expanded Full View -->
                    <div class="sidebar-expanded-content">
                        <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-800">Filter Products</h3>
                            <button onclick="clearFilters()" class="text-sm text-blue-600 hover:text-blue-700 font-semibold hover:underline transition-colors flex items-center gap-1">
                                <i class="fas fa-redo text-xs"></i>Clear
                            </button>
                        </div>
                    
                        <form method="GET" id="filter-form" class="space-y-6">
                        <!-- Preserve existing params -->
                        <input type="hidden" name="page" value="1">
                        
                        <!-- Search -->
                        <div class="filter-section">
                            <label class="block text-sm font-semibold mb-2 text-gray-700 flex items-center gap-2">
                                <i class="fas fa-search text-blue-600"></i>Search
                            </label>
                            <div class="relative">
                                <input type="text" name="search" 
                                       value="<?= escape($_GET['search'] ?? '') ?>" 
                                       placeholder="Search products..."
                                       class="w-full px-4 py-2.5 pl-10 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all bg-gray-50 focus:bg-white"
                                       onkeyup="debounceFilter()">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                        
                        <!-- Price Range -->
                        <div class="filter-section">
                            <label class="block text-sm font-semibold mb-2 text-gray-700 flex items-center gap-2">
                                <i class="fas fa-dollar-sign text-blue-600"></i>Price Range
                            </label>
                            <div class="flex gap-2">
                                <input type="number" name="min_price" 
                                       value="<?= escape($_GET['min_price'] ?? '') ?>" 
                                       placeholder="Min"
                                       min="0"
                                       step="0.01"
                                       class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 focus:bg-white transition-all"
                                       onchange="applyFilters()">
                                <input type="number" name="max_price" 
                                       value="<?= escape($_GET['max_price'] ?? '') ?>" 
                                       placeholder="Max"
                                       min="0"
                                       step="0.01"
                                       class="w-full px-3 py-2.5 border-2 border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 focus:bg-white transition-all"
                                       onchange="applyFilters()">
                            </div>
                            <div class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                                <i class="fas fa-info-circle"></i>
                                Range: $<?= number_format($minPriceRange, 2) ?> - $<?= number_format($maxPriceRange, 2) ?>
                            </div>
                        </div>
                        
                        <!-- Categories - Expandable/Collapsible -->
                        <div class="category-filter-section">
                            <button type="button" 
                                    onclick="toggleCategoryFilter()" 
                                    class="w-full flex items-center justify-between p-3 bg-blue-50 hover:bg-blue-100 rounded-xl transition-all duration-300 group border border-blue-200"
                                    id="category-filter-toggle">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-th-large text-blue-600"></i>
                                    <span class="font-semibold text-gray-800">Categories</span>
                                    <span class="text-xs bg-blue-600 text-white px-2 py-0.5 rounded-full font-medium" id="category-count"><?= count($categories) ?></span>
                                </div>
                                <i class="fas fa-chevron-down text-blue-600 transform transition-transform duration-300" id="category-chevron"></i>
                            </button>
                            <div class="category-filter-content hidden mt-3 space-y-1.5" id="category-filter-content">
                                <label class="flex items-center p-2.5 rounded-lg hover:bg-blue-50 transition-all cursor-pointer group/item border border-transparent hover:border-blue-200">
                                    <input type="radio" name="category" value="" 
                                           <?= empty($_GET['category']) ? 'checked' : '' ?>
                                           onchange="applyFilters()"
                                           class="mr-3 w-4 h-4 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                    <span class="text-sm font-medium text-gray-700 group-hover/item:text-blue-600 transition-colors flex items-center">
                                        <i class="fas fa-th mr-2 text-blue-600"></i>All Categories
                                    </span>
                                </label>
                                <?php foreach ($categories as $cat): ?>
                                <label class="flex items-center p-2.5 rounded-lg hover:bg-blue-50 transition-all cursor-pointer group/item border border-transparent hover:border-blue-200 <?= ($_GET['category'] ?? '') === $cat['slug'] ? 'bg-blue-50 border-blue-200' : '' ?>">
                                    <input type="radio" name="category" value="<?= escape($cat['slug']) ?>" 
                                           <?= ($_GET['category'] ?? '') === $cat['slug'] ? 'checked' : '' ?>
                                           onchange="applyFilters()"
                                           class="mr-3 w-4 h-4 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                    <span class="text-sm text-gray-700 group-hover/item:text-blue-600 transition-colors flex items-center flex-1">
                                        <i class="fas fa-box mr-2 text-gray-500 group-hover/item:text-blue-600 transition-colors"></i>
                                        <?= escape($cat['name']) ?>
                                    </span>
                                    <?php if (($cat['slug'] ?? '') === ($_GET['category'] ?? '')): ?>
                                        <i class="fas fa-check-circle text-blue-600 ml-auto"></i>
                                    <?php endif; ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Stock Status -->
                        <div class="filter-section">
                            <label class="block text-sm font-semibold mb-2 text-gray-700 flex items-center gap-2">
                                <i class="fas fa-box-check text-blue-600"></i>Availability
                            </label>
                            <label class="flex items-center p-3 bg-gray-50 hover:bg-blue-50 rounded-xl cursor-pointer transition-colors group">
                                <input type="checkbox" name="in_stock" value="1" 
                                       <?= !empty($_GET['in_stock']) ? 'checked' : '' ?>
                                       onchange="applyFilters()"
                                       class="mr-3 w-4 h-4 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                <span class="text-sm font-medium text-gray-700 group-hover:text-blue-600 transition-colors">In Stock Only</span>
                            </label>
                        </div>
                        
                        <!-- Featured -->
                        <div class="filter-section">
                            <label class="flex items-center p-3 bg-yellow-50 hover:bg-yellow-100 rounded-xl cursor-pointer transition-all border border-yellow-200 group">
                                <input type="checkbox" name="featured" value="1" 
                                       <?= !empty($_GET['featured']) ? 'checked' : '' ?>
                                       onchange="applyFilters()"
                                       class="mr-3 w-4 h-4 text-yellow-500 focus:ring-yellow-500 cursor-pointer">
                                <span class="text-sm font-semibold text-gray-700 group-hover:text-yellow-700 transition-colors flex items-center">
                                    <i class="fas fa-star text-yellow-500 mr-2"></i>Featured Only
                                </span>
                            </label>
                        </div>
                        
                            <button type="submit" class="btn-primary-sm w-full hidden" id="filter-submit">Apply Filters</button>
                        </form>
                    </div>
                    </div>
                </div>
            </aside>
            
            <!-- Mobile Sidebar Filters - Different Design -->
            <aside class="mobile-sidebar-filters fixed inset-0 z-[9999] hidden" id="sidebar-filters-mobile">
                <div class="mobile-sidebar-overlay" onclick="closeMobileFilters()"></div>
                <div class="mobile-sidebar-content">
                    <!-- Mobile Sidebar Header -->
                    <div class="mobile-sidebar-header">
                        <h2 class="mobile-sidebar-title">Filter Products</h2>
                        <button onclick="closeMobileFilters()" class="mobile-sidebar-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <!-- Mobile Sidebar Body -->
                    <div class="mobile-sidebar-body">
                        <form method="GET" id="mobile-filter-form" class="mobile-filter-form">
                            <input type="hidden" name="page" value="1">
                            
                            <!-- Search - Mobile -->
                            <div class="mobile-filter-section">
                                <label class="mobile-filter-label">
                                    <i class="fas fa-search mobile-filter-icon"></i>Search Products
                                </label>
                                <input type="text" name="search" 
                                       value="<?= escape($_GET['search'] ?? '') ?>" 
                                       placeholder="Search products..."
                                       class="mobile-filter-input"
                                       onkeyup="debounceFilter()">
                            </div>
                            
                            <!-- Price Range - Mobile -->
                            <div class="mobile-filter-section">
                                <label class="mobile-filter-label">
                                    <i class="fas fa-dollar-sign mobile-filter-icon"></i>Price Range
                                </label>
                                <div class="mobile-price-inputs">
                                    <input type="number" name="min_price" 
                                           value="<?= escape($_GET['min_price'] ?? '') ?>" 
                                           placeholder="Min"
                                           min="0"
                                           step="0.01"
                                           class="mobile-filter-input"
                                           onchange="applyFilters()">
                                    <span class="mobile-price-separator">to</span>
                                    <input type="number" name="max_price" 
                                           value="<?= escape($_GET['max_price'] ?? '') ?>" 
                                           placeholder="Max"
                                           min="0"
                                           step="0.01"
                                           class="mobile-filter-input"
                                           onchange="applyFilters()">
                                </div>
                                <div class="mobile-price-info">
                                    Range: $<?= number_format($minPriceRange, 2) ?> - $<?= number_format($maxPriceRange, 2) ?>
                                </div>
                            </div>
                            
                            <!-- Categories - Mobile -->
                            <div class="mobile-filter-section">
                                <button type="button" 
                                        onclick="toggleMobileCategoryFilter()" 
                                        class="mobile-filter-toggle"
                                        id="mobile-category-toggle">
                                    <div class="mobile-filter-toggle-content">
                                        <i class="fas fa-th-large mobile-filter-icon"></i>
                                        <span>Categories</span>
                                        <span class="mobile-filter-badge-small"><?= count($categories) ?></span>
                                    </div>
                                    <i class="fas fa-chevron-down mobile-filter-chevron" id="mobile-category-chevron"></i>
                                </button>
                                <div class="mobile-category-content hidden" id="mobile-category-content">
                                    <label class="mobile-filter-option">
                                        <input type="radio" name="category" value="" 
                                               <?= empty($_GET['category']) ? 'checked' : '' ?>
                                               onchange="applyFilters()"
                                               class="mobile-filter-radio">
                                        <span class="mobile-filter-option-text">
                                            <i class="fas fa-th mobile-filter-option-icon"></i>All Categories
                                        </span>
                                    </label>
                                    <?php foreach ($categories as $cat): ?>
                                    <label class="mobile-filter-option <?= ($_GET['category'] ?? '') === $cat['slug'] ? 'active' : '' ?>">
                                        <input type="radio" name="category" value="<?= escape($cat['slug']) ?>" 
                                               <?= ($_GET['category'] ?? '') === $cat['slug'] ? 'checked' : '' ?>
                                               onchange="applyFilters()"
                                               class="mobile-filter-radio">
                                        <span class="mobile-filter-option-text">
                                            <i class="fas fa-box mobile-filter-option-icon"></i><?= escape($cat['name']) ?>
                                        </span>
                                        <?php if (($cat['slug'] ?? '') === ($_GET['category'] ?? '')): ?>
                                            <i class="fas fa-check-circle mobile-filter-check"></i>
                                        <?php endif; ?>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- Stock Status - Mobile -->
                            <div class="mobile-filter-section">
                                <label class="mobile-filter-checkbox-label">
                                    <input type="checkbox" name="in_stock" value="1" 
                                           <?= !empty($_GET['in_stock']) ? 'checked' : '' ?>
                                           onchange="applyFilters()"
                                           class="mobile-filter-checkbox">
                                    <div class="mobile-filter-checkbox-content">
                                        <i class="fas fa-box-check mobile-filter-icon"></i>
                                        <span>In Stock Only</span>
                                    </div>
                                </label>
                            </div>
                            
                            <!-- Featured - Mobile -->
                            <div class="mobile-filter-section">
                                <label class="mobile-filter-checkbox-label featured">
                                    <input type="checkbox" name="featured" value="1" 
                                           <?= !empty($_GET['featured']) ? 'checked' : '' ?>
                                           onchange="applyFilters()"
                                           class="mobile-filter-checkbox">
                                    <div class="mobile-filter-checkbox-content">
                                        <i class="fas fa-star mobile-filter-icon"></i>
                                        <span>Featured Only</span>
                                    </div>
                                </label>
                            </div>
                            
                            <button type="submit" class="mobile-filter-submit hidden">Apply Filters</button>
                        </form>
                    </div>
                    
                    <!-- Mobile Sidebar Footer -->
                    <div class="mobile-sidebar-footer">
                        <button onclick="clearFilters()" class="mobile-filter-clear">
                            <i class="fas fa-redo"></i>Clear All
                        </button>
                        <button onclick="applyMobileFilters()" class="mobile-filter-apply">
                            <i class="fas fa-check"></i>Apply Filters
                        </button>
                    </div>
                </div>
            </aside>
            
            <!-- Products Grid -->
            <div class="flex-1 transition-all duration-300" id="products-container">
                <!-- Advanced Filters - Top Section (Toggleable) -->
                <div class="advanced-filters-top">
                    <button onclick="toggleAdvancedFilters()" 
                            class="advanced-filters-toggle"
                            id="advanced-filters-toggle-btn"
                            aria-expanded="false">
                        <div class="advanced-filters-toggle-content">
                            <i class="fas fa-sliders-h"></i>
                            <span class="advanced-filters-toggle-text">Advanced Filters</span>
                            <span class="advanced-filters-count" id="advanced-filters-count">0</span>
                        </div>
                        <i class="fas fa-chevron-down advanced-filters-chevron" id="advanced-filters-chevron"></i>
                    </button>
                    
                    <div class="advanced-filters-content" id="advanced-filters-content">
                        <form method="GET" id="advanced-filters-form" class="advanced-filters-form">
                            <input type="hidden" name="page" value="1">
                            <?php if (!empty($_GET['sort'])): ?>
                                <input type="hidden" name="sort" value="<?= escape($_GET['sort']) ?>">
                            <?php endif; ?>
                            
                            <div class="advanced-filters-grid">
                                <!-- Search -->
                                <div class="advanced-filter-item">
                                    <label class="advanced-filter-label">
                                        <i class="fas fa-search"></i>
                                        <span>Search</span>
                                    </label>
                                    <input type="text" 
                                           name="search" 
                                           value="<?= escape($_GET['search'] ?? '') ?>" 
                                           placeholder="Search products..."
                                           class="advanced-filter-input"
                                           onkeyup="debounceFilter()">
                                </div>
                                
                                <!-- Price Range -->
                                <div class="advanced-filter-item">
                                    <label class="advanced-filter-label">
                                        <i class="fas fa-dollar-sign"></i>
                                        <span>Price Range</span>
                                    </label>
                                    <div class="advanced-filter-price-inputs">
                                        <input type="number" 
                                               name="min_price" 
                                               value="<?= escape($_GET['min_price'] ?? '') ?>" 
                                               placeholder="Min"
                                               min="0"
                                               step="0.01"
                                               class="advanced-filter-input advanced-filter-price-input"
                                               onchange="applyFilters()">
                                        <span class="advanced-filter-price-separator">-</span>
                                        <input type="number" 
                                               name="max_price" 
                                               value="<?= escape($_GET['max_price'] ?? '') ?>" 
                                               placeholder="Max"
                                               min="0"
                                               step="0.01"
                                               class="advanced-filter-input advanced-filter-price-input"
                                               onchange="applyFilters()">
                                    </div>
                                    <div class="advanced-filter-hint">
                                        <i class="fas fa-info-circle"></i>
                                        Range: $<?= number_format($minPriceRange, 2) ?> - $<?= number_format($maxPriceRange, 2) ?>
                                    </div>
                                </div>
                                
                                <!-- Category -->
                                <div class="advanced-filter-item">
                                    <label class="advanced-filter-label">
                                        <i class="fas fa-th-large"></i>
                                        <span>Category</span>
                                    </label>
                                    <select name="category" 
                                            class="advanced-filter-select"
                                            onchange="applyFilters()">
                                        <option value="">All Categories</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= escape($cat['slug']) ?>" 
                                                    <?= ($_GET['category'] ?? '') === $cat['slug'] ? 'selected' : '' ?>>
                                                <?= escape($cat['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Quick Filters -->
                                <div class="advanced-filter-item advanced-filter-quick">
                                    <label class="advanced-filter-label">
                                        <i class="fas fa-bolt"></i>
                                        <span>Quick Filters</span>
                                    </label>
                                    <div class="advanced-filter-chips">
                                        <label class="advanced-filter-chip">
                                            <input type="checkbox" 
                                                   name="in_stock" 
                                                   value="1" 
                                                   <?= !empty($_GET['in_stock']) ? 'checked' : '' ?>
                                                   onchange="applyFilters()">
                                            <span><i class="fas fa-box-check"></i> In Stock</span>
                                        </label>
                                        <label class="advanced-filter-chip advanced-filter-chip-featured">
                                            <input type="checkbox" 
                                                   name="featured" 
                                                   value="1" 
                                                   <?= !empty($_GET['featured']) ? 'checked' : '' ?>
                                                   onchange="applyFilters()">
                                            <span><i class="fas fa-star"></i> Featured</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="advanced-filters-actions">
                                <button type="button" 
                                        onclick="clearFilters()" 
                                        class="advanced-filter-btn advanced-filter-btn-clear">
                                    <i class="fas fa-redo"></i>
                                    <span>Clear All</span>
                                </button>
                                <button type="submit" 
                                        class="advanced-filter-btn advanced-filter-btn-apply hidden"
                                        id="advanced-filter-submit">
                                    <i class="fas fa-check"></i>
                                    <span>Apply</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- App-Style Header -->
                <div class="app-products-header">
                    <div class="app-header-content">
                        <div class="app-header-title-section">
                            <h1 class="app-page-title">
                                <?= isset($categoryName) ? escape($categoryName) : 'All Products' ?>
                            </h1>
                            <?php if (!empty($_GET['search'])): ?>
                                <div class="app-search-badge">
                                    <i class="fas fa-search"></i>
                                    <span><?= escape($_GET['search']) ?></span>
                                </div>
                            <?php endif; ?>
                            <p class="app-results-count">
                                <i class="fas fa-box"></i>
                                <?= $totalProducts ?> <?= $totalProducts === 1 ? 'product' : 'products' ?>
                            </p>
                        </div>
                        
                        <div class="app-header-controls">
                            <!-- Sort Dropdown - App Style -->
                            <div class="app-sort-container">
                                <label class="app-sort-label">
                                    <i class="fas fa-sort"></i>
                                    <span class="hidden sm:inline">Sort</span>
                                </label>
                                <select id="sort-select" onchange="applyFilters()" class="app-sort-select">
                                    <option value="name" <?= ($_GET['sort'] ?? 'name') === 'name' ? 'selected' : '' ?>>Name A-Z</option>
                                    <option value="name_desc" <?= ($_GET['sort'] ?? '') === 'name_desc' ? 'selected' : '' ?>>Name Z-A</option>
                                    <option value="price_asc" <?= ($_GET['sort'] ?? '') === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                                    <option value="price_desc" <?= ($_GET['sort'] ?? '') === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                                    <option value="newest" <?= ($_GET['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Newest First</option>
                                    <option value="featured" <?= ($_GET['sort'] ?? '') === 'featured' ? 'selected' : '' ?>>Featured First</option>
                                </select>
                            </div>
                            
                            <!-- Layout Switcher - App Style -->
                            <div class="app-layout-switcher">
                                <button onclick="setLayout('grid')" id="layout-grid" class="app-layout-btn active" title="Grid View">
                                    <i class="fas fa-th"></i>
                                </button>
                                <button onclick="setLayout('list')" id="layout-list" class="app-layout-btn" title="List View">
                                    <i class="fas fa-list"></i>
                                </button>
                                <button onclick="setLayout('compact')" id="layout-compact" class="app-layout-btn" title="Compact View">
                                    <i class="fas fa-th-large"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (empty($products)): ?>
                    <div class="app-empty-state">
                        <div class="app-empty-icon">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <h3 class="app-empty-title">No Products Found</h3>
                        <p class="app-empty-text">Try adjusting your filters or search terms</p>
                        <a href="<?= url('products.php') ?>" class="app-btn-primary">
                            <i class="fas fa-redo mr-2"></i>Reset Filters
                        </a>
                    </div>
                <?php else: ?>
                    <div class="products-container app-products-grid" id="products-grid" data-layout="grid">
                        <?php foreach ($products as $product): ?>
                        <div class="app-product-card product-item" data-product-id="<?= $product['id'] ?>">
                            <a href="<?= url('product.php?slug=' . escape($product['slug'])) ?>" class="app-product-link">
                                <!-- Product Image -->
                                <div class="app-product-image-wrapper">
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'%3E%3Crect fill='%23e5e7eb' width='400' height='300'/%3E%3C/svg%3E" 
                                             data-src="<?= asset('storage/uploads/' . escape($product['image'])) ?>"
                                             alt="<?= escape($product['name']) ?>" 
                                             class="app-product-image lazy-load"
                                             loading="lazy"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="app-image-fallback" style="display: none;">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="app-product-placeholder">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Featured Badge -->
                                    <?php if ($product['is_featured']): ?>
                                        <div class="app-featured-badge">
                                            <i class="fas fa-star"></i>
                                            <span>Featured</span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Quick Actions Overlay -->
                                    <div class="app-product-overlay">
                                        <button onclick="event.preventDefault(); event.stopPropagation(); openQuickView(<?= $product['id'] ?>)" 
                                                class="app-overlay-btn"
                                                title="Quick View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="event.preventDefault(); event.stopPropagation(); quickAddToCart(<?= $product['id'] ?>)" 
                                                class="app-overlay-btn app-overlay-btn-primary"
                                                data-quick-add-cart="<?= $product['id'] ?>"
                                                title="Add to Cart">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Product Info -->
                                <div class="app-product-info">
                                    <div class="app-product-category">
                                        <i class="fas fa-tag"></i>
                                        <?= escape($product['category_name'] ?? 'Uncategorized') ?>
                                    </div>
                                    <h3 class="app-product-title"><?= escape($product['name']) ?></h3>
                                    <?php if (!empty($product['short_description'])): ?>
                                        <p class="app-product-description"><?= escape($product['short_description']) ?></p>
                                    <?php endif; ?>
                                    
                                    <!-- Price Section -->
                                    <div class="app-product-price-section">
                                        <?php 
                                        $price = !empty($product['price']) && $product['price'] > 0 ? (float)$product['price'] : null;
                                        $salePrice = !empty($product['sale_price']) && $product['sale_price'] > 0 ? (float)$product['sale_price'] : null;
                                        ?>
                                        <?php if ($salePrice && $price): ?>
                                            <div class="app-price-container">
                                                <span class="app-price-current">$<?= number_format((float)$salePrice, 2) ?></span>
                                                <span class="app-price-original">$<?= number_format((float)$price, 2) ?></span>
                                            </div>
                                            <div class="app-discount-badge">
                                                <?= round((($price - $salePrice) / $price) * 100) ?>% OFF
                                            </div>
                                        <?php elseif ($price): ?>
                                            <span class="app-price-current">$<?= number_format((float)$price, 2) ?></span>
                                        <?php else: ?>
                                            <span class="app-price-request">Price on Request</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Action Button -->
                                    <button onclick="event.preventDefault(); event.stopPropagation(); window.location.href='<?= url('product.php?slug=' . escape($product['slug'])) ?>'" 
                                            class="app-product-btn">
                                        <span>View Details</span>
                                        <i class="fas fa-arrow-right"></i>
                                    </button>
                                </div>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Load More Button -->
                    <?php if ($totalPages > 1 && $filters['page'] < $totalPages): ?>
                    <div class="mt-12 text-center" id="load-more-container">
                        <button id="load-more-btn" 
                                data-current-page="<?= $filters['page'] ?>"
                                data-total-pages="<?= $totalPages ?>"
                                data-category="<?= escape($_GET['category'] ?? '') ?>"
                                data-search="<?= escape($_GET['search'] ?? '') ?>"
                                data-featured="<?= escape($_GET['featured'] ?? '') ?>"
                                class="btn-primary px-8 py-3 text-lg">
                            <i class="fas fa-spinner fa-spin hidden mr-2" id="load-more-spinner"></i>
                            <span id="load-more-text">Load More Products</span>
                            <span class="text-sm font-normal ml-2" id="load-more-count">(<?= $totalProducts - (count($products) * $filters['page']) ?> remaining)</span>
                        </button>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
// Sidebar Toggle Function (Desktop only)
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar-filters-desktop');
    if (!sidebar) return;
    
    const isExpanded = !sidebar.classList.contains('collapsed');
    
    if (isExpanded) {
        // Collapse sidebar
        sidebar.classList.add('collapsed');
        localStorage.setItem('sidebarExpanded', 'false');
    } else {
        // Expand sidebar
        sidebar.classList.remove('collapsed');
        localStorage.setItem('sidebarExpanded', 'true');
    }
}

// Expand sidebar and focus on specific section (Desktop only)
function expandAndFocus(section) {
    const sidebar = document.getElementById('sidebar-filters-desktop');
    if (!sidebar) return;
    
    // Expand sidebar if collapsed
    if (sidebar.classList.contains('collapsed')) {
        sidebar.classList.remove('collapsed');
        localStorage.setItem('sidebarExpanded', 'true');
    }
    
    // Focus on specific section after a short delay
    setTimeout(() => {
        switch(section) {
            case 'search':
                const searchInput = document.querySelector('#sidebar-filters-desktop input[name="search"]');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                break;
            case 'price':
                const priceInput = document.querySelector('#sidebar-filters-desktop input[name="min_price"]');
                if (priceInput) {
                    priceInput.focus();
                    priceInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                break;
            case 'categories':
                const categoryToggle = document.getElementById('category-filter-toggle');
                if (categoryToggle) {
                    // Expand categories if collapsed
                    const categoryContent = document.getElementById('category-filter-content');
                    if (categoryContent && categoryContent.classList.contains('hidden')) {
                        toggleCategoryFilter();
                    }
                    categoryToggle.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                break;
            case 'filters':
                // Just expand, no specific focus
                break;
        }
    }, 100);
}

// Mobile Filter Functions
function openMobileFilters() {
    const mobileSidebar = document.getElementById('sidebar-filters-mobile');
    if (mobileSidebar) {
        mobileSidebar.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        updateMobileFilterCount();
    }
}

function closeMobileFilters() {
    const mobileSidebar = document.getElementById('sidebar-filters-mobile');
    if (mobileSidebar) {
        mobileSidebar.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

function toggleMobileCategoryFilter() {
    const content = document.getElementById('mobile-category-content');
    const chevron = document.getElementById('mobile-category-chevron');
    if (content && chevron) {
        content.classList.toggle('hidden');
        chevron.classList.toggle('rotate-180');
    }
}

function applyMobileFilters() {
    document.getElementById('mobile-filter-form').submit();
}

function updateMobileFilterCount() {
    const form = document.getElementById('mobile-filter-form');
    let count = 0;
    
    // Count active filters
    if (form.querySelector('input[name="search"]').value) count++;
    if (form.querySelector('input[name="min_price"]').value || form.querySelector('input[name="max_price"]').value) count++;
    if (form.querySelector('input[name="category"]:checked') && form.querySelector('input[name="category"]:checked').value) count++;
    if (form.querySelector('input[name="in_stock"]:checked')) count++;
    if (form.querySelector('input[name="featured"]:checked')) count++;
    
    const badge = document.getElementById('mobile-filter-count');
    if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    }
}

// Initialize sidebar state (Desktop only)
document.addEventListener('DOMContentLoaded', function() {
    const savedState = localStorage.getItem('sidebarExpanded');
    const desktopSidebar = document.getElementById('sidebar-filters-desktop');
    
    if (desktopSidebar) {
        // Default to expanded on desktop, collapsed on mobile
        if (savedState === 'false') {
            desktopSidebar.classList.add('collapsed');
        }
    }
    
    // Update mobile filter count on load
    updateMobileFilterCount();
    
    // Update count when filters change
    const mobileForm = document.getElementById('mobile-filter-form');
    if (mobileForm) {
        mobileForm.addEventListener('change', updateMobileFilterCount);
    }
    
    // Initialize advanced filters state
    const savedAdvancedState = localStorage.getItem('advancedFiltersExpanded');
    const advancedContent = document.getElementById('advanced-filters-content');
    const advancedBtn = document.getElementById('advanced-filters-toggle-btn');
    const advancedChevron = document.getElementById('advanced-filters-chevron');
    
    if (advancedContent && advancedBtn && advancedChevron) {
        // Default to collapsed
        if (savedAdvancedState === 'true') {
            advancedContent.classList.add('expanded');
            advancedBtn.setAttribute('aria-expanded', 'true');
            advancedChevron.classList.add('rotate-180');
        }
    }
    
    // Update advanced filters count on load
    updateAdvancedFiltersCount();
    
    // Update count when filters change
    const advancedForm = document.getElementById('advanced-filters-form');
    if (advancedForm) {
        advancedForm.addEventListener('change', updateAdvancedFiltersCount);
        advancedForm.addEventListener('input', updateAdvancedFiltersCount);
    }
});

// Category Filter Toggle
function toggleCategoryFilter() {
    const content = document.getElementById('category-filter-content');
    const chevron = document.getElementById('category-chevron');
    const isExpanded = !content.classList.contains('hidden');
    
    if (isExpanded) {
        content.classList.add('hidden');
        chevron.classList.remove('rotate-180');
        localStorage.setItem('categoryFilterExpanded', 'false');
    } else {
        content.classList.remove('hidden');
        chevron.classList.add('rotate-180');
        localStorage.setItem('categoryFilterExpanded', 'true');
    }
}

// Advanced Filters Toggle
function toggleAdvancedFilters() {
    const content = document.getElementById('advanced-filters-content');
    const btn = document.getElementById('advanced-filters-toggle-btn');
    const chevron = document.getElementById('advanced-filters-chevron');
    
    if (!content || !btn || !chevron) return;
    
    const isExpanded = content.classList.contains('expanded');
    
    if (isExpanded) {
        content.classList.remove('expanded');
        btn.setAttribute('aria-expanded', 'false');
        chevron.classList.remove('rotate-180');
        localStorage.setItem('advancedFiltersExpanded', 'false');
    } else {
        content.classList.add('expanded');
        btn.setAttribute('aria-expanded', 'true');
        chevron.classList.add('rotate-180');
        localStorage.setItem('advancedFiltersExpanded', 'true');
    }
}

// Update Advanced Filters Count
function updateAdvancedFiltersCount() {
    const form = document.getElementById('advanced-filters-form');
    if (!form) return;
    
    let count = 0;
    
    // Check search
    if (form.querySelector('input[name="search"]')?.value.trim()) count++;
    
    // Check price range
    if (form.querySelector('input[name="min_price"]')?.value || 
        form.querySelector('input[name="max_price"]')?.value) count++;
    
    // Check category
    if (form.querySelector('select[name="category"]')?.value) count++;
    
    // Check quick filters
    if (form.querySelector('input[name="in_stock"]:checked')) count++;
    if (form.querySelector('input[name="featured"]:checked')) count++;
    
    const countBadge = document.getElementById('advanced-filters-count');
    if (countBadge) {
        countBadge.textContent = count;
        countBadge.style.display = count > 0 ? 'inline-flex' : 'none';
    }
}

// Initialize category filter state
document.addEventListener('DOMContentLoaded', function() {
    const savedState = localStorage.getItem('categoryFilterExpanded');
    const content = document.getElementById('category-filter-content');
    const chevron = document.getElementById('category-chevron');
    
    // Default to expanded if a category is selected
    const hasCategory = '<?= !empty($_GET['category']) ? 'true' : 'false' ?>' === 'true';
    
    if (savedState === 'true' || (savedState === null && hasCategory)) {
        content.classList.remove('hidden');
        chevron.classList.add('rotate-180');
    } else if (savedState === 'false') {
        content.classList.add('hidden');
        chevron.classList.remove('rotate-180');
    }
});

// Layout Management - App Style
let currentLayout = localStorage.getItem('productLayout') || 'grid';

// Make setLayout globally accessible - define it first
window.setLayout = function(layout) {
    currentLayout = layout;
    localStorage.setItem('productLayout', layout);
    const container = document.getElementById('products-grid');
    if (!container) return;
    
    const items = container.querySelectorAll('.product-item');
    
    // Update active button immediately (synchronous)
    document.querySelectorAll('.app-layout-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    const activeBtn = document.getElementById('layout-' + layout);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }
    
    // Remove all layout classes immediately (synchronous)
    container.classList.remove('list-view', 'compact-view');
    items.forEach(item => {
        item.classList.remove('list-item', 'compact-item', 'flex');
    });
    
    // Force reflow to ensure classes are removed before adding new ones
    void container.offsetHeight;
    
    // Apply new layout immediately (synchronous)
    if (layout === 'grid') {
        container.classList.remove('list-view', 'compact-view');
        // Grid is default, no additional classes needed
    } else if (layout === 'list') {
        container.classList.add('list-view');
        items.forEach(item => {
            item.classList.add('list-item');
        });
    } else if (layout === 'compact') {
        container.classList.add('compact-view');
        items.forEach(item => item.classList.add('compact-item'));
    }
    
    // Force another reflow to ensure new classes are applied
    void container.offsetHeight;
}

// Initialize layout after function is defined
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setLayout(currentLayout);
    });
} else {
    setLayout(currentLayout);
}

// Filter Management
let filterTimeout;
function debounceFilter() {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
}

function applyFilters() {
    // Try advanced filters form first (top section), then fallback to sidebar form
    const advancedForm = document.getElementById('advanced-filters-form');
    const sidebarForm = document.getElementById('filter-form');
    
    if (advancedForm) {
        advancedForm.submit();
    } else if (sidebarForm) {
        sidebarForm.submit();
    }
}

function clearFilters() {
    window.location.href = '<?= url('products.php') ?>';
}

function quickAddToCart(productId) {
    fetch('<?= url('api/cart.php') ?>?action=add&product_id=' + productId, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show notification if function exists
            if (typeof showNotification === 'function') {
                showNotification('Product added to cart!', 'success');
            } else {
                alert('Product added to cart!');
            }
            // Update cart count
            if (typeof updateCartCount === 'function') {
                updateCartCount();
            } else {
                location.reload();
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding product to cart');
    });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

