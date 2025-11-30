<?php
/**
 * XML Sitemap Generator
 */
require_once __DIR__ . '/bootstrap/app.php';

use App\Models\Product;
use App\Models\Category;

header('Content-Type: application/xml; charset=utf-8');

$baseUrl = config('app.url', 'http://localhost:8080');

$productModel = new Product();
$categoryModel = new Category();

$products = $productModel->getAll(['limit' => 1000]);
$categories = $categoryModel->getAll(true);

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

// Homepage
echo '<url>';
echo '<loc>' . escape($baseUrl) . '</loc>';
echo '<lastmod>' . date('Y-m-d') . '</lastmod>';
echo '<changefreq>daily</changefreq>';
echo '<priority>1.0</priority>';
echo '</url>';

// Products page
echo '<url>';
echo '<loc>' . escape($baseUrl) . '/products.php</loc>';
echo '<lastmod>' . date('Y-m-d') . '</lastmod>';
echo '<changefreq>daily</changefreq>';
echo '<priority>0.9</priority>';
echo '</url>';

// Contact page
echo '<url>';
echo '<loc>' . escape($baseUrl) . '/contact.php</loc>';
echo '<lastmod>' . date('Y-m-d') . '</lastmod>';
echo '<changefreq>monthly</changefreq>';
echo '<priority>0.7</priority>';
echo '</url>';

// Categories
foreach ($categories as $category) {
    echo '<url>';
    echo '<loc>' . escape($baseUrl) . '/products.php?category=' . escape($category['slug']) . '</loc>';
    echo '<lastmod>' . date('Y-m-d', strtotime($category['updated_at'] ?? 'now')) . '</lastmod>';
    echo '<changefreq>weekly</changefreq>';
    echo '<priority>0.8</priority>';
    echo '</url>';
}

// Products
foreach ($products as $product) {
    echo '<url>';
    echo '<loc>' . escape($baseUrl) . '/product.php?slug=' . escape($product['slug']) . '</loc>';
    echo '<lastmod>' . date('Y-m-d', strtotime($product['updated_at'] ?? 'now')) . '</lastmod>';
    echo '<changefreq>weekly</changefreq>';
    echo '<priority>0.8</priority>';
    echo '</url>';
}

echo '</urlset>';

