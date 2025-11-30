<?php
require_once __DIR__ . '/bootstrap/app.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get testimonials
$testimonials = db()->fetchAll("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY display_order, created_at DESC");

$pageTitle = 'Customer Testimonials - Forklift & Equipment Pro';
include __DIR__ . '/includes/header.php';
?>

<main class="py-12">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold mb-4">What Our Customers Say</h1>
            <p class="text-gray-600 text-lg">Read reviews from satisfied customers who trust us for their equipment needs</p>
        </div>
        
        <?php if (empty($testimonials)): ?>
            <div class="bg-white rounded-lg shadow-md p-12 text-center">
                <i class="fas fa-comments text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-600">No testimonials available yet.</p>
            </div>
        <?php else: ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                <?php foreach ($testimonials as $testimonial): ?>
                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-xl transition-shadow">
                    <!-- Rating Stars -->
                    <div class="flex items-center mb-4">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?= $i <= $testimonial['rating'] ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    
                    <!-- Testimonial Text -->
                    <p class="text-gray-700 mb-6 italic">"<?= escape($testimonial['testimonial']) ?>"</p>
                    
                    <!-- Customer Info -->
                    <div class="flex items-center border-t pt-4">
                        <?php if (!empty($testimonial['image'])): ?>
                            <img src="<?= asset('storage/uploads/' . escape($testimonial['image'])) ?>" 
                                 alt="<?= escape($testimonial['customer_name']) ?>"
                                 class="w-12 h-12 rounded-full object-cover mr-4">
                        <?php else: ?>
                            <div class="w-12 h-12 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold mr-4">
                                <?= strtoupper(substr($testimonial['customer_name'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <div>
                            <p class="font-semibold"><?= escape($testimonial['customer_name']) ?></p>
                            <?php if (!empty($testimonial['company'])): ?>
                                <p class="text-sm text-gray-600"><?= escape($testimonial['company']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- CTA Section -->
        <div class="bg-blue-600 text-white rounded-lg p-12 text-center">
            <h2 class="text-3xl font-bold mb-4">Ready to Join Our Satisfied Customers?</h2>
            <p class="text-xl mb-8 text-blue-100">Get the equipment you need, when you need it.</p>
            <div class="flex gap-4 justify-center">
                <a href="<?= url('products.php') ?>" class="btn-white">Browse Products</a>
                <a href="<?= url('contact.php') ?>" class="btn-white bg-transparent border-2 border-white hover:bg-white hover:text-blue-600">Contact Us</a>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

