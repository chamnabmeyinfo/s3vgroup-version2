<?php
require_once __DIR__ . '/bootstrap/app.php';

use App\Models\Product;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$productModel = new Product();
$cartItems = $_SESSION['cart'] ?? [];
$products = [];
$total = 0;

foreach ($cartItems as $productId => $quantity) {
    $product = $productModel->getById($productId);
    if ($product && $product['is_active']) {
        $product['cart_quantity'] = $quantity;
        $product['line_total'] = ($product['sale_price'] ?? $product['price']) * $quantity;
        $total += $product['line_total'];
        $products[] = $product;
    }
}

$pageTitle = 'Shopping Cart - Forklift & Equipment Pro';
include __DIR__ . '/includes/header.php';
?>

<main class="py-8">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold mb-6">Shopping Cart</h1>
        
        <?php if (empty($products)): ?>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-12 text-center">
                <i class="fas fa-shopping-cart text-6xl text-blue-400 mb-4"></i>
                <h2 class="text-2xl font-bold mb-2">Your Cart is Empty</h2>
                <p class="text-gray-600 mb-6">Start adding products to your cart!</p>
                <a href="<?= url('products.php') ?>" class="btn-primary inline-block">
                    Browse Products
                </a>
            </div>
        <?php else: ?>
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Cart Items -->
                <div class="md:col-span-2 space-y-4">
                    <?php foreach ($products as $product): ?>
                    <div class="bg-white rounded-lg shadow-md p-6 flex gap-6">
                        <div class="w-32 h-32 flex-shrink-0 bg-gray-200 rounded overflow-hidden">
                            <?php if (!empty($product['image'])): ?>
                                <img src="<?= asset('storage/uploads/' . escape($product['image'])) ?>" 
                                     alt="<?= escape($product['name']) ?>" 
                                     class="w-full h-full object-cover">
                            <?php else: ?>
                                <span class="text-gray-400 text-xs">No Image</span>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold mb-2">
                                <a href="<?= url('product.php?slug=' . escape($product['slug'])) ?>" 
                                   class="text-blue-600 hover:underline">
                                    <?= escape($product['name']) ?>
                                </a>
                            </h3>
                            <p class="text-gray-600 text-sm mb-4"><?= escape($product['short_description'] ?? '') ?></p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xl font-bold text-blue-600">
                                        $<?= number_format($product['sale_price'] ?? $product['price'], 2) ?>
                                    </p>
                                    <p class="text-sm text-gray-500">Subtotal: $<?= number_format($product['line_total'], 2) ?></p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center border rounded">
                                        <button onclick="updateQuantity(<?= $product['id'] ?>, <?= $product['cart_quantity'] - 1 ?>)" 
                                                class="px-3 py-1 hover:bg-gray-100">-</button>
                                        <input type="number" 
                                               id="qty-<?= $product['id'] ?>" 
                                               value="<?= $product['cart_quantity'] ?>" 
                                               min="1"
                                               onchange="updateQuantity(<?= $product['id'] ?>, this.value)"
                                               class="w-16 text-center border-0">
                                        <button onclick="updateQuantity(<?= $product['id'] ?>, <?= $product['cart_quantity'] + 1 ?>)" 
                                                class="px-3 py-1 hover:bg-gray-100">+</button>
                                    </div>
                                    <button onclick="removeFromCart(<?= $product['id'] ?>)" 
                                            class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Cart Summary -->
                <div class="bg-white rounded-lg shadow-md p-6 h-fit sticky top-20">
                    <h2 class="text-xl font-bold mb-4">Cart Summary</h2>
                    <div class="space-y-3 mb-4">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span class="font-semibold">$<?= number_format($total, 2) ?></span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Tax (estimated):</span>
                            <span>$<?= number_format($total * 0.08, 2) ?></span>
                        </div>
                        <div class="border-t pt-3 flex justify-between text-xl font-bold">
                            <span>Total:</span>
                            <span class="text-blue-600">$<?= number_format($total * 1.08, 2) ?></span>
                        </div>
                    </div>
                    <a href="<?= url('checkout-guest.php') ?>" class="btn-primary w-full text-center block mb-3">
                        <i class="fas fa-arrow-right mr-2"></i> Proceed to Checkout
                    </a>
                    <?php if (!isset($_SESSION['customer_id'])): ?>
                        <p class="text-xs text-center text-gray-600 mb-2">Or</p>
                        <a href="<?= url('register.php') ?>" class="btn-secondary w-full text-center block">
                            Create Account for Faster Checkout
                        </a>
                    <?php endif; ?>
                    <a href="<?= url('products.php') ?>" class="btn-secondary w-full text-center block">
                        Continue Shopping
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
function updateQuantity(productId, quantity) {
    if (quantity < 1) {
        removeFromCart(productId);
        return;
    }
    
    fetch('<?= url('api/cart.php') ?>?action=update&product_id=' + productId + '&quantity=' + quantity, {
        method: 'POST'
    })
    .then(() => location.reload());
}

function removeFromCart(productId) {
    if (confirm('Remove this item from cart?')) {
        fetch('<?= url('api/cart.php') ?>?action=remove&product_id=' + productId, {
            method: 'POST'
        })
        .then(() => location.reload());
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

