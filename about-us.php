<?php
require_once __DIR__ . '/bootstrap/app.php';

// Check under construction mode
use App\Helpers\UnderConstruction;
UnderConstruction::show();

use App\Models\Setting;
use App\Helpers\QrCodeHelper;

$settingModel = new Setting();
$siteName = $settingModel->get('site_name', 'Forklift & Equipment Pro');
$siteEmail = $settingModel->get('site_email', 'info@example.com');
$sitePhone = $settingModel->get('site_phone', '');
$siteAddress = $settingModel->get('site_address', '');

// Generate QR code for website
$qrCodeUrl = QrCodeHelper::generateWebsiteQr('', 200, 'url');

$pageTitle = 'About Us - ' . $siteName;
include __DIR__ . '/includes/header.php';
?>

<main class="py-12 md:py-16 bg-gradient-to-br from-gray-50 via-white to-blue-50">
    <div class="container mx-auto px-4">
        <!-- Hero Section -->
        <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 rounded-3xl shadow-2xl mb-12 md:mb-16">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.1"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
            <div class="relative px-6 md:px-12 lg:px-16 py-12 md:py-20 text-white">
                <div class="max-w-4xl mx-auto text-center">
                    <div class="inline-block p-4 bg-white/20 backdrop-blur-sm rounded-2xl mb-6">
                        <i class="fas fa-building text-5xl md:text-6xl"></i>
                    </div>
                    <h1 class="text-4xl md:text-6xl font-bold mb-6">About Us</h1>
                    <p class="text-xl md:text-2xl text-blue-100 max-w-2xl mx-auto leading-relaxed">
                        Leading the industry in forklift and industrial equipment solutions
                    </p>
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto">
            <!-- Mission & Vision Section -->
            <div class="grid md:grid-cols-2 gap-8 mb-12">
                <!-- Mission Card -->
                <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100 transform hover:scale-105 transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-bullseye text-white text-2xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold mb-4 text-gray-800">Our Mission</h2>
                    <p class="text-gray-600 leading-relaxed">
                        To provide exceptional forklift and industrial equipment solutions that empower businesses to achieve their operational goals. We are committed to delivering quality products, outstanding service, and innovative solutions that drive productivity and success.
                    </p>
                </div>

                <!-- Vision Card -->
                <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100 transform hover:scale-105 transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-eye text-white text-2xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold mb-4 text-gray-800">Our Vision</h2>
                    <p class="text-gray-600 leading-relaxed">
                        To become the most trusted partner in the industrial equipment industry, recognized for excellence, innovation, and customer satisfaction. We envision a future where every business has access to the best equipment solutions tailored to their unique needs.
                    </p>
                </div>
            </div>

            <!-- Company Story Section -->
            <div class="bg-white rounded-2xl shadow-xl p-8 md:p-12 mb-12 border border-gray-100">
                <div class="text-center mb-8">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4 text-gray-800">Our Story</h2>
                    <div class="w-24 h-1 bg-gradient-to-r from-blue-600 to-indigo-600 mx-auto rounded-full"></div>
                </div>
                <div class="prose prose-lg max-w-none text-gray-700">
                    <p class="text-lg leading-relaxed mb-6">
                        Founded with a passion for excellence and a commitment to customer success, we have grown from a small local business to a leading provider of forklift and industrial equipment solutions. Our journey has been marked by continuous innovation, unwavering dedication to quality, and a deep understanding of our customers' needs.
                    </p>
                    <p class="text-lg leading-relaxed mb-6">
                        Over the years, we have built strong relationships with manufacturers, suppliers, and most importantly, our customers. We take pride in offering a comprehensive range of products, from compact warehouse forklifts to heavy-duty industrial machinery, all backed by expert service and support.
                    </p>
                    <p class="text-lg leading-relaxed">
                        Today, we continue to evolve and adapt, embracing new technologies and best practices to ensure we remain at the forefront of the industry. Our team of experienced professionals is dedicated to helping you find the perfect equipment solutions for your business.
                    </p>
                </div>
            </div>

            <!-- Values Section -->
            <div class="mb-12">
                <div class="text-center mb-8">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4 text-gray-800">Our Core Values</h2>
                    <p class="text-gray-600 text-lg">The principles that guide everything we do</p>
                </div>
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php
                    $values = [
                        ['icon' => 'fas fa-shield-alt', 'title' => 'Quality', 'color' => 'blue', 'description' => 'Uncompromising commitment to quality in every product and service'],
                        ['icon' => 'fas fa-handshake', 'title' => 'Integrity', 'color' => 'green', 'description' => 'Honest, transparent, and ethical in all our business practices'],
                        ['icon' => 'fas fa-lightbulb', 'title' => 'Innovation', 'color' => 'yellow', 'description' => 'Continuously seeking better solutions and improved methods'],
                        ['icon' => 'fas fa-heart', 'title' => 'Customer Focus', 'color' => 'red', 'description' => 'Your success is our success - we put customers first']
                    ];
                    foreach ($values as $value):
                    ?>
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2">
                        <div class="w-14 h-14 bg-gradient-to-br from-<?= $value['color'] ?>-500 to-<?= $value['color'] ?>-600 rounded-xl flex items-center justify-center mb-4 mx-auto">
                            <i class="<?= $value['icon'] ?> text-white text-xl"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-2 text-center text-gray-800"><?= $value['title'] ?></h3>
                        <p class="text-gray-600 text-sm text-center"><?= $value['description'] ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Contact & QR Code Section -->
            <div class="grid md:grid-cols-2 gap-8">
                <!-- Contact Information -->
                <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl shadow-xl p-8 text-white">
                    <h3 class="text-2xl font-bold mb-6 flex items-center">
                        <i class="fas fa-address-card mr-3"></i>Get in Touch
                    </h3>
                    <div class="space-y-4">
                        <?php if ($sitePhone): ?>
                        <div class="flex items-start">
                            <i class="fas fa-phone w-6 mt-1"></i>
                            <div class="ml-4">
                                <p class="font-semibold">Phone</p>
                                <a href="tel:<?= escape($sitePhone) ?>" class="text-blue-100 hover:text-white transition-colors">
                                    <?= escape($sitePhone) ?>
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($siteEmail): ?>
                        <div class="flex items-start">
                            <i class="fas fa-envelope w-6 mt-1"></i>
                            <div class="ml-4">
                                <p class="font-semibold">Email</p>
                                <a href="mailto:<?= escape($siteEmail) ?>" class="text-blue-100 hover:text-white transition-colors break-all">
                                    <?= escape($siteEmail) ?>
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($siteAddress): ?>
                        <div class="flex items-start">
                            <i class="fas fa-map-marker-alt w-6 mt-1"></i>
                            <div class="ml-4">
                                <p class="font-semibold">Address</p>
                                <p class="text-blue-100"><?= nl2br(escape($siteAddress)) ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- QR Code Card -->
                <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
                    <h3 class="text-2xl font-bold mb-4 text-gray-800 flex items-center">
                        <i class="fas fa-qrcode text-blue-600 mr-3"></i>Scan to Visit Website
                    </h3>
                    <p class="text-gray-600 mb-6">Scan this QR code with your mobile device to visit our website</p>
                    <div class="flex justify-center mb-6">
                        <div class="bg-white p-4 rounded-xl shadow-lg border-2 border-gray-200">
                            <img src="<?= escape($qrCodeUrl) ?>" alt="QR Code" class="w-48 h-48">
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 text-center">Point your camera at the QR code to open our website</p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
