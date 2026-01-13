<?php
require_once __DIR__ . '/bootstrap/app.php';

// Check under construction mode
use App\Helpers\UnderConstruction;
UnderConstruction::show();

use App\Models\Setting;
use App\Helpers\QrCodeHelper;

$settingModel = new Setting();
$siteName = $settingModel->get('site_name', 'Forklift & Equipment Pro');

// Generate QR code for website
$qrCodeUrl = QrCodeHelper::generateWebsiteQr('', 200, 'url');

$pageTitle = 'CEO Message - ' . $siteName;
include __DIR__ . '/includes/header.php';
?>

<main class="py-12 md:py-16 bg-gradient-to-br from-gray-50 via-white to-indigo-50">
    <div class="container mx-auto px-4">
        <!-- Hero Section -->
        <div class="relative overflow-hidden bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-3xl shadow-2xl mb-12 md:mb-16">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="absolute inset-0 opacity-20" style="background-image: url('data:image/svg+xml,%3Csvg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"%3E%3Cpath d="M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z" fill="%23ffffff" fill-opacity="1" fill-rule="evenodd"/%3E%3C/svg%3E');"></div>
            <div class="relative px-6 md:px-12 lg:px-16 py-12 md:py-20 text-white">
                <div class="max-w-4xl mx-auto text-center">
                    <div class="inline-block p-4 bg-white/20 backdrop-blur-sm rounded-2xl mb-6">
                        <i class="fas fa-user-tie text-5xl md:text-6xl"></i>
                    </div>
                    <h1 class="text-4xl md:text-6xl font-bold mb-6">Message from Our CEO</h1>
                    <p class="text-xl md:text-2xl text-indigo-100 max-w-2xl mx-auto leading-relaxed">
                        A personal message from our leadership
                    </p>
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto">
            <!-- CEO Message Card -->
            <div class="bg-white rounded-2xl shadow-2xl p-8 md:p-12 mb-12 border border-gray-100">
                <!-- CEO Profile Section -->
                <div class="flex flex-col md:flex-row items-center md:items-start gap-6 mb-8 pb-8 border-b border-gray-200">
                    <div class="flex-shrink-0">
                        <div class="w-32 h-32 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-user-tie text-white text-5xl"></i>
                        </div>
                    </div>
                    <div class="flex-1 text-center md:text-left">
                        <h2 class="text-3xl font-bold mb-2 text-gray-800">Chief Executive Officer</h2>
                        <p class="text-xl text-gray-600 mb-4"><?= escape($siteName) ?></p>
                        <div class="flex flex-wrap justify-center md:justify-start gap-4 text-sm text-gray-500">
                            <span class="flex items-center">
                                <i class="fas fa-calendar mr-2"></i>
                                <?= date('F Y') ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Message Content -->
                <div class="prose prose-lg max-w-none">
                    <div class="text-gray-700 leading-relaxed space-y-6">
                        <p class="text-lg">
                            <strong class="text-gray-900">Dear Valued Customers and Partners,</strong>
                        </p>
                        <p class="text-lg">
                            It is with great pleasure and pride that I welcome you to <?= escape($siteName) ?>. As the Chief Executive Officer, I am honored to lead a team of dedicated professionals who are committed to delivering excellence in every aspect of our business.
                        </p>
                        <p class="text-lg">
                            Our company was founded on the principles of quality, integrity, and customer satisfaction. These core values have guided us through years of growth and have established us as a trusted leader in the forklift and industrial equipment industry.
                        </p>
                        <p class="text-lg">
                            We understand that your business success depends on reliable, efficient equipment. That's why we go beyond simply selling products – we partner with you to understand your unique needs and provide solutions that drive your operational excellence.
                        </p>
                        <p class="text-lg">
                            Our commitment extends to every interaction: from the initial consultation through installation, training, and ongoing support. We believe in building long-term relationships, not just making transactions.
                        </p>
                        <p class="text-lg">
                            As we look to the future, we remain dedicated to innovation, continuous improvement, and exceeding your expectations. We invest in our team, our technology, and our processes to ensure we can serve you better every day.
                        </p>
                        <p class="text-lg">
                            Thank you for choosing <?= escape($siteName) ?>. We are here to support your success, and we look forward to being your trusted partner for years to come.
                        </p>
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <p class="text-lg font-semibold text-gray-900 mb-2">Sincerely,</p>
                            <p class="text-xl font-bold text-indigo-600">CEO</p>
                            <p class="text-gray-600"><?= escape($siteName) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QR Code & Contact Section -->
            <div class="grid md:grid-cols-2 gap-8">
                <!-- QR Code Card -->
                <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
                    <h3 class="text-2xl font-bold mb-4 text-gray-800 flex items-center">
                        <i class="fas fa-qrcode text-indigo-600 mr-3"></i>Scan to Visit Website
                    </h3>
                    <p class="text-gray-600 mb-6">Scan this QR code with your mobile device to visit our website</p>
                    <div class="flex justify-center mb-6">
                        <div class="bg-white p-4 rounded-xl shadow-lg border-2 border-gray-200">
                            <img src="<?= escape($qrCodeUrl) ?>" alt="QR Code" class="w-48 h-48">
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 text-center">Point your camera at the QR code to open our website</p>
                </div>

                <!-- Quick Links -->
                <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl shadow-xl p-8 text-white">
                    <h3 class="text-2xl font-bold mb-6 flex items-center">
                        <i class="fas fa-link mr-3"></i>Quick Links
                    </h3>
                    <div class="space-y-4">
                        <a href="<?= url('about-us.php') ?>" class="block bg-white/20 hover:bg-white/30 backdrop-blur-sm px-6 py-4 rounded-xl font-semibold transition-all duration-300 transform hover:scale-105">
                            <i class="fas fa-building mr-2"></i>About Us
                        </a>
                        <a href="<?= url('contact.php') ?>" class="block bg-white/20 hover:bg-white/30 backdrop-blur-sm px-6 py-4 rounded-xl font-semibold transition-all duration-300 transform hover:scale-105">
                            <i class="fas fa-envelope mr-2"></i>Contact Us
                        </a>
                        <a href="<?= url('products.php') ?>" class="block bg-white/20 hover:bg-white/30 backdrop-blur-sm px-6 py-4 rounded-xl font-semibold transition-all duration-300 transform hover:scale-105">
                            <i class="fas fa-box mr-2"></i>Our Products
                        </a>
                        <a href="<?= url('services.php') ?>" class="block bg-white/20 hover:bg-white/30 backdrop-blur-sm px-6 py-4 rounded-xl font-semibold transition-all duration-300 transform hover:scale-105">
                            <i class="fas fa-concierge-bell mr-2"></i>Our Services
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
