<?php
require_once __DIR__ . '/bootstrap/app.php';

// Check under construction mode
use App\Helpers\UnderConstruction;
UnderConstruction::show();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $messageText = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($messageText)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            db()->insert('contact_messages', [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'subject' => $subject,
                'message' => $messageText
            ]);
            
            $message = 'Thank you for contacting us! We will get back to you soon.';
            $_POST = []; // Clear form
        } catch (Exception $e) {
            $error = 'Sorry, there was an error sending your message. Please try again.';
        }
    }
}

$productName = $_GET['product'] ?? '';

$pageTitle = 'Contact Us - Forklift & Equipment Pro';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/message.php';
?>

<main class="py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl font-bold text-center mb-4">Contact Us</h1>
            <p class="text-center text-gray-600 mb-12">We're here to help! Get in touch with our team.</p>
            
            <?= displayMessage($message, $error) ?>
            
            <div class="grid md:grid-cols-2 gap-12">
                <!-- Contact Form -->
                <div>
                    
                    <form method="POST" class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium mb-2">Name *</label>
                            <input type="text" id="name" name="name" required
                                   value="<?= escape($_POST['name'] ?? '') ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium mb-2">Email *</label>
                            <input type="email" id="email" name="email" required
                                   value="<?= escape($_POST['email'] ?? '') ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-sm font-medium mb-2">Phone</label>
                            <input type="tel" id="phone" name="phone"
                                   value="<?= escape($_POST['phone'] ?? '') ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="subject" class="block text-sm font-medium mb-2">Subject</label>
                            <input type="text" id="subject" name="subject"
                                   value="<?= escape($_POST['subject'] ?? $productName) ?>"
                                   class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="message" class="block text-sm font-medium mb-2">Message *</label>
                            <textarea id="message" name="message" rows="6" required
                                      class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?= escape($_POST['message'] ?? '') ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn-primary w-full">
                            Send Message
                        </button>
                    </form>
                </div>
                
                <!-- Contact Information -->
                <div>
                    <?php
                    use App\Models\Setting;
                    $settingModel = new Setting();
                    $sitePhone = $settingModel->get('site_phone', '+1 (555) 123-4567');
                    $siteEmail = $settingModel->get('site_email', 'info@example.com');
                    $siteAddress = $settingModel->get('site_address', '123 Industrial Way, City, State 12345');
                    ?>
                    <div class="bg-gray-50 rounded-lg p-6 mb-6">
                        <h3 class="text-xl font-bold mb-4">Get in Touch</h3>
                        <div class="space-y-4">
                            <div>
                                <i class="fas fa-phone text-blue-600 mr-3"></i>
                                <span><?= escape($sitePhone) ?></span>
                            </div>
                            <div>
                                <i class="fas fa-envelope text-blue-600 mr-3"></i>
                                <span><?= escape($siteEmail) ?></span>
                            </div>
                            <div>
                                <i class="fas fa-map-marker-alt text-blue-600 mr-3"></i>
                                <span><?= nl2br(escape($siteAddress)) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-blue-600 text-white rounded-lg p-6">
                        <h3 class="text-xl font-bold mb-4">Business Hours</h3>
                        <div class="space-y-2">
                            <p><strong>Monday - Friday:</strong> 8:00 AM - 6:00 PM</p>
                            <p><strong>Saturday:</strong> 9:00 AM - 4:00 PM</p>
                            <p><strong>Sunday:</strong> Closed</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

