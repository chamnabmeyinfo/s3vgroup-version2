    <!-- Footer -->
    <?php
    use App\Models\Setting;
    $settingModel = new Setting();
    $siteName = $settingModel->get('site_name', 'ForkliftPro');
    $siteEmail = $settingModel->get('site_email', 'info@example.com');
    $sitePhone = $settingModel->get('site_phone', '+1 (555) 123-4567');
    $siteAddress = $settingModel->get('site_address', '123 Industrial Way');
    $footerText = $settingModel->get('footer_text', '© ' . date('Y') . ' ' . $siteName . '. All rights reserved.');
    ?>
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div>
                    <h3 class="text-xl font-bold mb-4"><?= escape($siteName) ?></h3>
                    <p class="text-gray-400">Premium industrial equipment for your business needs.</p>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="<?= url() ?>" class="hover:text-white">Home</a></li>
                        <li><a href="<?= url('products.php') ?>" class="hover:text-white">Products</a></li>
                        <li><a href="<?= url('blog.php') ?>" class="hover:text-white">Blog</a></li>
                        <li><a href="<?= url('faq.php') ?>" class="hover:text-white">FAQ</a></li>
                        <li><a href="<?= url('testimonials.php') ?>" class="hover:text-white">Testimonials</a></li>
                        <li><a href="<?= url('contact.php') ?>" class="hover:text-white">Contact</a></li>
                        <li><a href="<?= url('quote.php') ?>" class="hover:text-white">Get Quote</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Categories</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="<?= url('products.php?category=forklifts') ?>" class="hover:text-white">Forklifts</a></li>
                        <li><a href="<?= url('products.php?category=pallet-trucks') ?>" class="hover:text-white">Pallet Trucks</a></li>
                        <li><a href="<?= url('products.php?category=stackers') ?>" class="hover:text-white">Stackers</a></li>
                        <li><a href="<?= url('products.php?category=reach-trucks') ?>" class="hover:text-white">Reach Trucks</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Contact</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><i class="fas fa-phone mr-2"></i> <?= escape($sitePhone) ?></li>
                        <li><i class="fas fa-envelope mr-2"></i> <?= escape($siteEmail) ?></li>
                        <li><i class="fas fa-map-marker-alt mr-2"></i> <?= escape($siteAddress) ?></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8">
                <div class="grid md:grid-cols-2 gap-8 items-center">
                    <div>
                        <p class="text-gray-400"><?= nl2br(escape($footerText)) ?></p>
                    </div>
                    <div>
                        <h4 class="font-bold mb-4">Subscribe to Newsletter</h4>
                        <form id="newsletter-form" class="flex gap-2">
                            <input type="email" 
                                   id="newsletter-email" 
                                   placeholder="Your email address" 
                                   required
                                   class="flex-1 px-4 py-2 rounded-lg text-gray-900">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                                Subscribe
                            </button>
                        </form>
                        <p id="newsletter-message" class="text-sm mt-2 hidden"></p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <script>
    document.getElementById('newsletter-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const email = document.getElementById('newsletter-email').value;
        const messageEl = document.getElementById('newsletter-message');
        
        fetch('<?= url('api/newsletter.php') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=subscribe&email=' + encodeURIComponent(email)
        })
        .then(response => response.json())
        .then(data => {
            messageEl.classList.remove('hidden');
            messageEl.className = 'text-sm mt-2 ' + (data.success ? 'text-green-400' : 'text-red-400');
            messageEl.textContent = data.message;
            if (data.success) {
                document.getElementById('newsletter-email').value = '';
            }
        });
    });
    </script>

    <!-- Quick View Modal -->
    <?php include __DIR__ . '/quick-view-modal.php'; ?>
    
    <!-- Live Chat Widget -->
    <?php include __DIR__ . '/live-chat.php'; ?>
    
    <!-- Image Zoom -->
    <?php include __DIR__ . '/image-zoom.php'; ?>

    <!-- JavaScript -->
    <script src="<?= asset('assets/js/main.js') ?>"></script>
    <script src="<?= asset('assets/js/advanced-search.js') ?>"></script>
    <script src="<?= asset('assets/js/advanced-ux.js') ?>"></script>
</body>
</html>

