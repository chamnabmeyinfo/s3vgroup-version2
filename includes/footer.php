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
    <footer class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white py-12 md:py-16 relative overflow-hidden">
        <!-- Decorative Elements -->
        <div class="absolute top-0 left-0 w-full h-full opacity-5">
            <div class="absolute top-20 left-10 w-72 h-72 bg-blue-500 rounded-full blur-3xl"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-purple-500 rounded-full blur-3xl"></div>
        </div>
        
        <div class="container mx-auto px-4 relative z-10">
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8 md:gap-12 mb-12">
                <!-- Company Info -->
                <div class="lg:col-span-1">
                    <h3 class="text-2xl font-bold mb-4 bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
                        <?= escape($siteName) ?>
                    </h3>
                    <p class="text-gray-400 mb-6 leading-relaxed">
                        Premium industrial equipment for your business needs. Quality, reliability, and expert support.
                    </p>
                    <!-- Social Links -->
                    <div class="flex gap-3">
                        <a href="#" class="w-10 h-10 bg-white/10 hover:bg-blue-600 rounded-xl flex items-center justify-center transition-all duration-300 transform hover:scale-110 hover:rotate-3">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-white/10 hover:bg-blue-400 rounded-xl flex items-center justify-center transition-all duration-300 transform hover:scale-110 hover:rotate-3">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-white/10 hover:bg-pink-600 rounded-xl flex items-center justify-center transition-all duration-300 transform hover:scale-110 hover:rotate-3">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-white/10 hover:bg-blue-700 rounded-xl flex items-center justify-center transition-all duration-300 transform hover:scale-110 hover:rotate-3">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h4 class="font-bold text-lg mb-6 flex items-center">
                        <i class="fas fa-link mr-2 text-blue-400"></i>Quick Links
                    </h4>
                    <ul class="space-y-3">
                        <li><a href="<?= url() ?>" class="text-gray-400 hover:text-white transition-colors flex items-center group">
                            <i class="fas fa-chevron-right text-xs mr-2 group-hover:translate-x-1 transition-transform"></i>Home
                        </a></li>
                        <li><a href="<?= url('products.php') ?>" class="text-gray-400 hover:text-white transition-colors flex items-center group">
                            <i class="fas fa-chevron-right text-xs mr-2 group-hover:translate-x-1 transition-transform"></i>Products
                        </a></li>
                        <li><a href="<?= url('blog.php') ?>" class="text-gray-400 hover:text-white transition-colors flex items-center group">
                            <i class="fas fa-chevron-right text-xs mr-2 group-hover:translate-x-1 transition-transform"></i>Blog
                        </a></li>
                        <li><a href="<?= url('faq.php') ?>" class="text-gray-400 hover:text-white transition-colors flex items-center group">
                            <i class="fas fa-chevron-right text-xs mr-2 group-hover:translate-x-1 transition-transform"></i>FAQ
                        </a></li>
                        <li><a href="<?= url('testimonials.php') ?>" class="text-gray-400 hover:text-white transition-colors flex items-center group">
                            <i class="fas fa-chevron-right text-xs mr-2 group-hover:translate-x-1 transition-transform"></i>Testimonials
                        </a></li>
                        <li><a href="<?= url('contact.php') ?>" class="text-gray-400 hover:text-white transition-colors flex items-center group">
                            <i class="fas fa-chevron-right text-xs mr-2 group-hover:translate-x-1 transition-transform"></i>Contact
                        </a></li>
                        <li><a href="<?= url('quote.php') ?>" class="text-gray-400 hover:text-white transition-colors flex items-center group">
                            <i class="fas fa-chevron-right text-xs mr-2 group-hover:translate-x-1 transition-transform"></i>Get Quote
                        </a></li>
                    </ul>
                </div>
                
                <!-- Categories -->
                <div>
                    <h4 class="font-bold text-lg mb-6 flex items-center">
                        <i class="fas fa-th-large mr-2 text-blue-400"></i>Categories
                    </h4>
                    <ul class="space-y-3">
                        <li><a href="<?= url('products.php?category=forklifts') ?>" class="text-gray-400 hover:text-white transition-colors flex items-center group">
                            <i class="fas fa-chevron-right text-xs mr-2 group-hover:translate-x-1 transition-transform"></i>Forklifts
                        </a></li>
                        <li><a href="<?= url('products.php?category=pallet-trucks') ?>" class="text-gray-400 hover:text-white transition-colors flex items-center group">
                            <i class="fas fa-chevron-right text-xs mr-2 group-hover:translate-x-1 transition-transform"></i>Pallet Trucks
                        </a></li>
                        <li><a href="<?= url('products.php?category=stackers') ?>" class="text-gray-400 hover:text-white transition-colors flex items-center group">
                            <i class="fas fa-chevron-right text-xs mr-2 group-hover:translate-x-1 transition-transform"></i>Stackers
                        </a></li>
                        <li><a href="<?= url('products.php?category=reach-trucks') ?>" class="text-gray-400 hover:text-white transition-colors flex items-center group">
                            <i class="fas fa-chevron-right text-xs mr-2 group-hover:translate-x-1 transition-transform"></i>Reach Trucks
                        </a></li>
                    </ul>
                </div>
                
                <!-- Contact & Newsletter -->
                <div>
                    <h4 class="font-bold text-lg mb-6 flex items-center">
                        <i class="fas fa-envelope mr-2 text-blue-400"></i>Stay Connected
                    </h4>
                    <div class="space-y-4 mb-6">
                        <div class="flex items-start group">
                            <i class="fas fa-phone text-blue-400 mr-3 mt-1"></i>
                            <a href="tel:<?= escape($sitePhone) ?>" class="text-gray-400 hover:text-white transition-colors">
                                <?= escape($sitePhone) ?>
                            </a>
                        </div>
                        <div class="flex items-start group">
                            <i class="fas fa-envelope text-blue-400 mr-3 mt-1"></i>
                            <a href="mailto:<?= escape($siteEmail) ?>" class="text-gray-400 hover:text-white transition-colors break-all">
                                <?= escape($siteEmail) ?>
                            </a>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt text-blue-400 mr-3 mt-1"></i>
                            <span class="text-gray-400"><?= escape($siteAddress) ?></span>
                        </div>
                    </div>
                    
                    <!-- Newsletter -->
                    <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/10">
                        <h5 class="font-semibold mb-3 text-sm">Newsletter</h5>
                        <form id="newsletter-form" class="space-y-2">
                            <input type="email" 
                                   id="newsletter-email" 
                                   placeholder="Your email" 
                                   required
                                   class="w-full px-3 py-2 rounded-lg text-gray-900 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-4 py-2 rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 text-sm font-semibold transform hover:scale-105">
                                <i class="fas fa-paper-plane mr-2"></i>Subscribe
                            </button>
                        </form>
                        <p id="newsletter-message" class="text-xs mt-2 hidden"></p>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Bar -->
            <div class="border-t border-gray-700/50 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div>
                        <p class="text-gray-400 text-sm"><?= nl2br(escape($footerText)) ?></p>
                    </div>
                    <div class="flex gap-6 text-sm text-gray-400">
                        <a href="#" class="hover:text-white transition-colors">Privacy Policy</a>
                        <a href="#" class="hover:text-white transition-colors">Terms of Service</a>
                        <a href="#" class="hover:text-white transition-colors">Cookie Policy</a>
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

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    <!-- Global API URLs Config -->
    <script>
    // Global configuration for API URLs
    window.APP_CONFIG = {
        apiUrl: '<?= url("api") ?>',
        baseUrl: '<?= url() ?>',
        urls: {
            search: '<?= url("api/search.php") ?>',
            smartSearch: '<?= url("api/smart-search.php") ?>',
            cart: '<?= url("api/cart.php") ?>',
            wishlist: '<?= url("api/wishlist.php") ?>',
            compare: '<?= url("api/compare.php") ?>',
            loadMore: '<?= url("api/load-more-products.php") ?>',
            products: '<?= url("products.php") ?>'
        }
    };
    </script>
    
    <!-- Modern Navigation & Slider Scripts -->
    <script>
    // Initialize Hero Slider
    document.addEventListener('DOMContentLoaded', function() {
        // Hero Slider
        const heroSwiper = new Swiper('.heroSwiper', {
            slidesPerView: 1,
            spaceBetween: 0,
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
                dynamicBullets: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },
            speed: 1000,
        });
        
        // Mobile Menu Toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuIcon = document.getElementById('menu-icon');
        const mobileSearch = document.getElementById('mobile-search');
        
        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', function() {
                mobileMenu.classList.toggle('show');
                mobileSearch.classList.toggle('hidden');
                
                if (mobileMenu.classList.contains('show')) {
                    menuIcon.classList.remove('fa-bars');
                    menuIcon.classList.add('fa-times');
                } else {
                    menuIcon.classList.remove('fa-times');
                    menuIcon.classList.add('fa-bars');
                }
            });
        }
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            if (mobileMenu && mobileMenuBtn && 
                !mobileMenu.contains(event.target) && 
                !mobileMenuBtn.contains(event.target)) {
                mobileMenu.classList.remove('show');
                mobileSearch.classList.add('hidden');
                if (menuIcon) {
                    menuIcon.classList.remove('fa-times');
                    menuIcon.classList.add('fa-bars');
                }
            }
        });
        
        // Navbar scroll effect
        const nav = document.getElementById('main-nav');
        let lastScroll = 0;
        
        window.addEventListener('scroll', function() {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
            
            lastScroll = currentScroll;
        });
    });
    
    // Mobile Accordion Toggle
    function toggleMobileAccordion(button) {
        const content = button.nextElementSibling;
        const icon = button.querySelector('.fa-chevron-down');
        
        if (content) {
            content.classList.toggle('hidden');
            if (icon) {
                icon.classList.toggle('rotate-180');
            }
        }
    }
    </script>
    
    <!-- JavaScript -->
    <script src="<?= asset('assets/js/lazy-load.js') ?>"></script>
    <script src="<?= asset('assets/js/main.js') ?>"></script>
    <script src="<?= asset('assets/js/advanced-search.js') ?>"></script>
    <script src="<?= asset('assets/js/advanced-ux.js') ?>"></script>
</body>
</html>

