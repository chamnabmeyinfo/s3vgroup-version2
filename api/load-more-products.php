<?php
/**
 * API: Load More Products (AJAX)
 */
require_once __DIR__ . '/../bootstrap/app.php';

header('Content-Type: application/json');

use App\Models\Product;
use App\Models\Category;

$productModel = new Product();
$categoryModel = new Category();

$filters = [
    'page' => (int)($_GET['page'] ?? 1),
    'limit' => 12
];

if (!empty($_GET['category'])) {
    $category = $categoryModel->getBySlug($_GET['category']);
    if ($category) {
        $filters['category_id'] = $category['id'];
    }
}

if (!empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}

if (!empty($_GET['featured'])) {
    $filters['featured'] = true;
}

$products = $productModel->getAll($filters);
$totalProducts = $productModel->count($filters);
$totalPages = ceil($totalProducts / $filters['limit']);
$hasMore = $filters['page'] < $totalPages;

// Generate HTML for products
ob_start();
foreach ($products as $product):
?>
<div class="product-card product-item bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden" data-product-id="<?= $product['id'] ?>">
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
                    <i class="fas fa-image text-4xl text-gray-400"></i>
                </div>
            <?php endif; ?>
            <?php if ($product['is_featured']): ?>
                <span class="absolute top-2 right-2 bg-yellow-400 text-yellow-900 px-2 py-1 rounded text-xs font-bold">
                    Featured
                </span>
            <?php endif; ?>
        </div>
        <div class="p-4">
            <h3 class="font-bold text-lg mb-2 line-clamp-2"><?= escape($product['name']) ?></h3>
            <p class="text-sm text-gray-600 mb-2"><?= escape($product['category_name'] ?? '') ?></p>
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
<?php
endforeach;
$html = ob_get_clean();

echo json_encode([
    'success' => true,
    'html' => $html,
    'hasMore' => $hasMore,
    'currentPage' => $filters['page'],
    'totalPages' => $totalPages,
    'totalProducts' => $totalProducts
]);

