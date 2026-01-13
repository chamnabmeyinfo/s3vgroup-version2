<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

use App\Models\CeoMessage;

$ceoModel = new CeoMessage();
$message = '';
$error = '';

// Check if table exists
$tableExists = false;
try {
    db()->fetchOne("SELECT 1 FROM ceo_message LIMIT 1");
    $tableExists = true;
} catch (Exception $e) {
    $tableExists = false;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_demo'])) {
    if (!$tableExists) {
        $error = 'CEO message table does not exist. Please run database/ceo-message.sql first.';
    } else {
        try {
            // Demo CEO message data
            $demoData = [
                'ceo_name' => 'John Smith',
                'ceo_title' => 'Chief Executive Officer',
                'greeting' => 'Dear Valued Customers, Partners, and Friends,',
                'message_content' => '<p>It is with immense pleasure and deep gratitude that I welcome you to <strong>S3V Group</strong>. As the Chief Executive Officer, I am honored to lead a team of exceptional professionals who share an unwavering commitment to excellence, innovation, and customer satisfaction.</p>

<p>Since our founding, we have built our reputation on three fundamental pillars: <strong>Quality</strong>, <strong>Integrity</strong>, and <strong>Partnership</strong>. These core values are not just words on our website—they are the guiding principles that drive every decision we make, every product we deliver, and every relationship we build.</p>

<p>In the dynamic world of industrial equipment and material handling solutions, we understand that your success depends on more than just reliable machinery. It requires a trusted partner who understands your unique challenges, anticipates your needs, and provides comprehensive support at every stage of your journey.</p>

<p>Our commitment to you extends far beyond the point of sale. We invest heavily in:</p>

<ul style="margin-left: 2rem; margin-top: 1rem; margin-bottom: 1rem;">
    <li><strong>Training & Education:</strong> Ensuring your team is fully equipped to maximize the potential of every piece of equipment</li>
    <li><strong>Maintenance & Support:</strong> Proactive service programs that minimize downtime and extend equipment life</li>
    <li><strong>Innovation:</strong> Continuously exploring new technologies and solutions to keep you ahead of the competition</li>
    <li><strong>Customer Service:</strong> A dedicated support team available when you need us most</li>
</ul>

<p>As we look toward the future, we remain steadfast in our mission to be your most trusted partner in industrial excellence. We are constantly evolving, investing in our people, our technology, and our processes to ensure we can serve you better today than we did yesterday.</p>

<p>Your success is our success. When you thrive, we thrive. This philosophy has been the cornerstone of our growth and will continue to guide us as we expand our services, enhance our product offerings, and strengthen our commitment to the communities we serve.</p>

<p>I invite you to explore our website, connect with our team, and discover how we can help elevate your operations to new heights. Whether you are a long-standing partner or considering us for the first time, we are here to listen, understand, and deliver solutions that exceed your expectations.</p>

<p>Thank you for being part of our journey. Together, we will continue to build a future defined by excellence, innovation, and mutual success.</p>',
                'signature_name' => 'John Smith',
                'signature_title' => 'Chief Executive Officer',
                'is_active' => 1
            ];

            // Check if demo data already exists
            $existing = $ceoModel->getActive();
            if ($existing) {
                // Update existing message
                $ceoModel->update($existing['id'], $demoData);
                $message = 'Demo CEO message has been updated successfully!';
            } else {
                // Create new message
                $ceoModel->create($demoData);
                $message = 'Demo CEO message has been added successfully!';
            }
        } catch (Exception $e) {
            $error = 'Error adding demo data: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Add Demo CEO Message';
include __DIR__ . '/includes/header.php';
?>

<div class="w-full">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-xl shadow-xl p-4 md:p-6 lg:p-8 mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-2">
                    <i class="fas fa-user-tie mr-2 md:mr-3"></i>
                    Add Demo CEO Message
                </h1>
                <p class="text-indigo-100 text-sm md:text-lg">Add sample CEO message data to your database</p>
            </div>
            <a href="<?= url('admin/ceo-message.php') ?>" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Editor
            </a>
        </div>
    </div>

    <?php if (!empty($message)): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2 text-xl"></i>
            <span class="font-semibold"><?= escape($message) ?></span>
        </div>
        <div class="mt-4">
            <a href="<?= url('admin/ceo-message.php') ?>" class="text-green-700 hover:text-green-900 font-semibold underline mr-4">
                <i class="fas fa-edit mr-1"></i>Edit Message
            </a>
            <a href="<?= url('ceo-message.php') ?>" target="_blank" class="text-green-700 hover:text-green-900 font-semibold underline">
                <i class="fas fa-eye mr-1"></i>View on Website
            </a>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2 text-xl"></i>
            <span class="font-semibold"><?= escape($error) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!$tableExists): ?>
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg mb-6">
        <div class="flex items-start">
            <i class="fas fa-exclamation-triangle text-yellow-600 mr-3 mt-1"></i>
            <div>
                <h3 class="font-semibold text-yellow-800 mb-2">CEO Message Table Not Set Up</h3>
                <p class="text-yellow-700 text-sm mb-4">Please run the SQL file: <code class="bg-yellow-100 px-2 py-1 rounded">database/ceo-message.sql</code> first.</p>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">
                <i class="fas fa-info-circle text-indigo-600 mr-2"></i>
                Demo CEO Message Content
            </h2>
            <p class="text-gray-600 mb-6">
                This will add a professional demo CEO message to your database. You can edit it later through the CEO Message editor.
            </p>

            <div class="bg-gray-50 rounded-lg p-6 mb-6 border-2 border-gray-200">
                <h3 class="font-bold text-gray-800 mb-4">Demo Message Preview:</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="font-semibold text-gray-700">CEO Name:</span>
                        <span class="text-gray-600 ml-2">John Smith</span>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700">CEO Title:</span>
                        <span class="text-gray-600 ml-2">Chief Executive Officer</span>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700">Greeting:</span>
                        <span class="text-gray-600 ml-2">Dear Valued Customers, Partners, and Friends,</span>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700">Message:</span>
                        <p class="text-gray-600 mt-2 italic">A comprehensive professional message covering company values, commitment to customers, services offered, and vision for the future...</p>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-700">Signature:</span>
                        <span class="text-gray-600 ml-2">John Smith, Chief Executive Officer</span>
                    </div>
                </div>
            </div>

            <form method="POST" class="space-y-4">
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                    <p class="text-blue-800 text-sm font-medium">
                        <i class="fas fa-lightbulb mr-2"></i>
                        <strong>Note:</strong> If you already have a CEO message, this will update it with the demo content. You can always edit it later.
                    </p>
                </div>

                <div class="flex gap-4 pt-4">
                    <button type="submit" name="add_demo" class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-8 py-3 rounded-lg font-bold text-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl">
                        <i class="fas fa-plus-circle mr-2"></i>Add Demo CEO Message
                    </button>
                    <a href="<?= url('admin/ceo-message.php') ?>" class="bg-gray-200 text-gray-700 px-8 py-3 rounded-lg font-bold text-lg hover:bg-gray-300 transition-colors">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
