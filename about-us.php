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
$metaDescription = 'Learn about our mission, vision, and story. We deliver quality forklift and industrial equipment solutions with integrity and customer focus.';

include __DIR__ . '/includes/header.php';
?>

<main class="min-h-screen bg-gradient-to-b from-gray-50 via-white to-blue-50/30">
    <!-- Hero -->
    <section class="relative overflow-hidden pt-8 pb-16 md:pt-12 md:pb-24">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-600 via-indigo-600 to-indigo-800"></div>
        <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=\"60\" height=\"60\" viewBox=\"0 0 60 60\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" fill-rule=\"evenodd\"%3E%3Cg fill=\"%23ffffff\" fill-opacity=\"1\"%3E%3Cpath d=\"M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-4xl mx-auto text-center text-white">
                <div class="inline-flex items-center justify-center w-20 h-20 md:w-24 md:h-24 rounded-2xl bg-white/15 backdrop-blur-sm border border-white/20 mb-6 md:mb-8">
                    <i class="fas fa-building text-4xl md:text-5xl" aria-hidden="true"></i>
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-4 md:mb-6 tracking-tight">About Us</h1>
                <p class="text-lg md:text-xl text-blue-100 max-w-2xl mx-auto leading-relaxed">
                    Leading the industry in forklift and industrial equipment solutions
                </p>
            </div>
        </div>
    </section>

    <div class="container mx-auto px-4 -mt-8 relative z-10 max-w-6xl">
        <!-- Mission & Vision -->
        <section class="grid md:grid-cols-2 gap-6 md:gap-8 mb-16 md:mb-20">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 md:p-10 hover:shadow-xl transition-shadow duration-300">
                <div class="w-14 h-14 md:w-16 md:h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fas fa-bullseye text-white text-2xl" aria-hidden="true"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Our Mission</h2>
                <p class="text-gray-600 leading-relaxed mb-6">
                    To provide exceptional forklift and industrial equipment solutions that empower businesses to achieve their operational goals. We are committed to delivering quality products, outstanding service, and innovative solutions that drive productivity and success.
                </p>
                <a href="<?= url('mission-vision.php') ?>" class="inline-flex items-center gap-2 text-blue-600 font-semibold hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-lg transition-colors duration-200 cursor-pointer">
                    <span>Mission &amp; Vision</span>
                    <i class="fas fa-arrow-right text-sm" aria-hidden="true"></i>
                </a>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 md:p-10 hover:shadow-xl transition-shadow duration-300">
                <div class="w-14 h-14 md:w-16 md:h-16 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl flex items-center justify-center mb-6">
                    <i class="fas fa-eye text-white text-2xl" aria-hidden="true"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Our Vision</h2>
                <p class="text-gray-600 leading-relaxed mb-6">
                    To become the most trusted partner in the industrial equipment industry, recognized for excellence, innovation, and customer satisfaction. We envision a future where every business has access to the best equipment solutions tailored to their unique needs.
                </p>
                <a href="<?= url('mission-vision.php') ?>" class="inline-flex items-center gap-2 text-purple-600 font-semibold hover:text-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 rounded-lg transition-colors duration-200 cursor-pointer">
                    <span>Learn more</span>
                    <i class="fas fa-arrow-right text-sm" aria-hidden="true"></i>
                </a>
            </div>
        </section>

        <!-- Our Story -->
        <section class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 md:p-12 mb-16 md:mb-20">
            <div class="text-center mb-10">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-3">Our Story</h2>
                <div class="w-16 h-1 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full mx-auto"></div>
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
        </section>

        <!-- Core Values -->
        <section class="mb-16 md:mb-20">
            <div class="text-center mb-10">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-3">Our Core Values</h2>
                <p class="text-gray-600 text-lg max-w-xl mx-auto">The principles that guide everything we do</p>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php
                $values = [
                    ['icon' => 'fas fa-shield-alt', 'title' => 'Quality', 'bg' => 'from-blue-500 to-blue-600', 'description' => 'Uncompromising commitment to quality in every product and service'],
                    ['icon' => 'fas fa-handshake', 'title' => 'Integrity', 'bg' => 'from-emerald-500 to-emerald-600', 'description' => 'Honest, transparent, and ethical in all our business practices'],
                    ['icon' => 'fas fa-lightbulb', 'title' => 'Innovation', 'bg' => 'from-amber-500 to-amber-600', 'description' => 'Continuously seeking better solutions and improved methods'],
                    ['icon' => 'fas fa-heart', 'title' => 'Customer Focus', 'bg' => 'from-rose-500 to-rose-600', 'description' => 'Your success is our success — we put customers first'],
                ];
                foreach ($values as $value):
                ?>
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-shadow duration-300">
                    <div class="w-14 h-14 bg-gradient-to-br <?= $value['bg'] ?> rounded-xl flex items-center justify-center mb-4">
                        <i class="<?= $value['icon'] ?> text-white text-xl" aria-hidden="true"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2"><?= escape($value['title']) ?></h3>
                    <p class="text-gray-600 text-sm leading-relaxed"><?= escape($value['description']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Discover more -->
        <section class="mb-16 md:mb-20">
            <div class="bg-gradient-to-r from-gray-800 to-gray-900 rounded-2xl p-8 md:p-10 text-white">
                <h2 class="text-2xl font-bold mb-6">Discover more</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <a href="<?= url('mission-vision.php') ?>" class="flex items-center gap-4 p-4 rounded-xl bg-white/10 hover:bg-white/15 border border-white/10 transition-colors duration-200 cursor-pointer focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                        <span class="flex items-center justify-center w-12 h-12 rounded-xl bg-blue-500/80">
                            <i class="fas fa-bullseye" aria-hidden="true"></i>
                        </span>
                        <div>
                            <span class="font-semibold block">Mission &amp; Vision</span>
                            <span class="text-sm text-gray-300">Our goals and direction</span>
                        </div>
                    </a>
                    <a href="<?= url('ceo-message.php') ?>" class="flex items-center gap-4 p-4 rounded-xl bg-white/10 hover:bg-white/15 border border-white/10 transition-colors duration-200 cursor-pointer focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                        <span class="flex items-center justify-center w-12 h-12 rounded-xl bg-purple-500/80">
                            <i class="fas fa-user-tie" aria-hidden="true"></i>
                        </span>
                        <div>
                            <span class="font-semibold block">CEO Message</span>
                            <span class="text-sm text-gray-300">A message from our leader</span>
                        </div>
                    </a>
                    <a href="<?= url('contact.php') ?>" class="flex items-center gap-4 p-4 rounded-xl bg-white/10 hover:bg-white/15 border border-white/10 transition-colors duration-200 cursor-pointer focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                        <span class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-500/80">
                            <i class="fas fa-envelope" aria-hidden="true"></i>
                        </span>
                        <div>
                            <span class="font-semibold block">Contact Us</span>
                            <span class="text-sm text-gray-300">Get in touch</span>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <!-- Contact & QR -->
        <section class="grid md:grid-cols-2 gap-6 md:gap-8 pb-16 md:pb-24">
            <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl shadow-xl p-8 text-white">
                <h3 class="text-2xl font-bold mb-6 flex items-center gap-3">
                    <i class="fas fa-address-card" aria-hidden="true"></i>
                    Get in Touch
                </h3>
                <div class="space-y-5">
                    <?php if ($sitePhone): ?>
                    <div class="flex items-start gap-4">
                        <i class="fas fa-phone w-6 mt-0.5 flex-shrink-0" aria-hidden="true"></i>
                        <div>
                            <p class="font-semibold text-white/90">Phone</p>
                            <a href="tel:<?= escape($sitePhone) ?>" class="text-blue-100 hover:text-white transition-colors cursor-pointer focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-600 rounded">
                                <?= escape($sitePhone) ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($siteEmail): ?>
                    <div class="flex items-start gap-4">
                        <i class="fas fa-envelope w-6 mt-0.5 flex-shrink-0" aria-hidden="true"></i>
                        <div>
                            <p class="font-semibold text-white/90">Email</p>
                            <a href="mailto:<?= escape($siteEmail) ?>" class="text-blue-100 hover:text-white transition-colors break-all cursor-pointer focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-600 rounded">
                                <?= escape($siteEmail) ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($siteAddress): ?>
                    <div class="flex items-start gap-4">
                        <i class="fas fa-map-marker-alt w-6 mt-0.5 flex-shrink-0" aria-hidden="true"></i>
                        <div>
                            <p class="font-semibold text-white/90">Address</p>
                            <p class="text-blue-100"><?= nl2br(escape($siteAddress)) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <a href="<?= url('contact.php') ?>" class="inline-flex items-center gap-2 mt-6 px-6 py-3 bg-white text-blue-700 font-semibold rounded-xl hover:bg-blue-50 transition-colors duration-200 cursor-pointer focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-600">
                    <i class="fas fa-paper-plane" aria-hidden="true"></i>
                    Contact page
                </a>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                <h3 class="text-2xl font-bold text-gray-800 mb-2 flex items-center gap-3">
                    <i class="fas fa-qrcode text-blue-600" aria-hidden="true"></i>
                    Scan to Visit
                </h3>
                <p class="text-gray-600 mb-6">Use your phone camera to scan and open our website.</p>
                <div class="flex justify-center mb-4">
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <img src="<?= escape($qrCodeUrl) ?>" alt="QR code to visit our website" class="w-44 h-44 md:w-48 md:h-48">
                    </div>
                </div>
                <p class="text-sm text-gray-500 text-center">Point your camera at the code</p>
            </div>
        </section>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
