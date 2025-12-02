<?php
/**
 * Message Component Demo Page
 * Showcases all message types and styles
 */
require_once __DIR__ . '/bootstrap/app.php';

$pageTitle = 'Message Component Demo - Forklift & Equipment Pro';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/message.php';
?>

<main class="py-12">
    <div class="container mx-auto px-4 max-w-4xl">
        <h1 class="text-4xl font-bold text-center mb-4">Beautiful Message Component</h1>
        <p class="text-center text-gray-600 mb-12">Modern, animated message displays for your application</p>
        
        <!-- Success Messages -->
        <section class="mb-12">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Success Messages</h2>
            <?= showMessage('Your quote request has been submitted successfully! We will contact you shortly.', 'success') ?>
            <?= showMessage('Account created successfully! Please login to continue.', 'success', true, false) ?>
        </section>
        
        <!-- Error Messages -->
        <section class="mb-12">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Error Messages</h2>
            <?= showMessage('There was an error processing your request. Please try again.', 'error') ?>
            <?= showMessage('Invalid email address. Please check and try again.', 'error', true, false) ?>
        </section>
        
        <!-- Warning Messages -->
        <section class="mb-12">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Warning Messages</h2>
            <?= showMessage('Your session will expire in 5 minutes. Please save your work.', 'warning') ?>
            <?= showMessage('This action cannot be undone. Are you sure you want to continue?', 'warning', true, false) ?>
        </section>
        
        <!-- Info Messages -->
        <section class="mb-12">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Info Messages</h2>
            <?= showMessage('New features are now available! Check out our latest updates.', 'info') ?>
            <?= showMessage('We typically respond within 24 hours during business days.', 'info', true, false) ?>
        </section>
        
        <!-- Non-dismissible Messages -->
        <section class="mb-12">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Non-Dismissible Messages</h2>
            <?= showMessage('This is an important message that cannot be dismissed.', 'info', false, false) ?>
        </section>
        
        <!-- JavaScript Toast Demo -->
        <section class="mb-12">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">JavaScript Toast Messages</h2>
            <p class="text-gray-600 mb-4">Click the buttons below to see toast notifications:</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <button onclick="showToast('Success! Your action was completed.', 'success')" 
                        class="btn-primary">
                    Success Toast
                </button>
                <button onclick="showToast('Error! Something went wrong.', 'error')" 
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors">
                    Error Toast
                </button>
                <button onclick="showToast('Warning! Please review this.', 'warning')" 
                        class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors">
                    Warning Toast
                </button>
                <button onclick="showToast('Info: Here is some helpful information.', 'info')" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors">
                    Info Toast
                </button>
            </div>
        </section>
        
        <!-- Usage Examples -->
        <section class="mb-12 bg-gray-50 rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Usage Examples</h2>
            <div class="space-y-4">
                <div>
                    <h3 class="font-semibold mb-2">PHP Usage:</h3>
                    <pre class="bg-gray-800 text-green-400 p-4 rounded-lg overflow-x-auto"><code>&lt;?php
include __DIR__ . '/includes/message.php';

// Simple success message
echo showMessage('Operation completed successfully!', 'success');

// Error message (non-dismissible, no auto-hide)
echo showMessage('An error occurred.', 'error', false, false);

// Using displayMessage helper
echo displayMessage($successMsg, $errorMsg, $warningMsg, $infoMsg);
?&gt;</code></pre>
                </div>
                
                <div>
                    <h3 class="font-semibold mb-2">JavaScript Usage:</h3>
                    <pre class="bg-gray-800 text-green-400 p-4 rounded-lg overflow-x-auto"><code>// Show toast notification
showToast('Your message here', 'success', 5000);

// Available types: 'success', 'error', 'warning', 'info'
showToast('Error occurred!', 'error');
showToast('Warning message', 'warning');
showToast('Information', 'info');</code></pre>
                </div>
            </div>
        </section>
        
        <!-- Features -->
        <section class="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-4 text-gray-800">Features</h2>
            <ul class="grid md:grid-cols-2 gap-3 text-gray-700">
                <li class="flex items-center gap-2">
                    <i class="fas fa-check-circle text-green-600"></i>
                    <span>4 message types (success, error, warning, info)</span>
                </li>
                <li class="flex items-center gap-2">
                    <i class="fas fa-check-circle text-green-600"></i>
                    <span>Smooth slide-in animations</span>
                </li>
                <li class="flex items-center gap-2">
                    <i class="fas fa-check-circle text-green-600"></i>
                    <span>Auto-dismiss after 5 seconds (optional)</span>
                </li>
                <li class="flex items-center gap-2">
                    <i class="fas fa-check-circle text-green-600"></i>
                    <span>Dismissible with close button</span>
                </li>
                <li class="flex items-center gap-2">
                    <i class="fas fa-check-circle text-green-600"></i>
                    <span>Progress bar for auto-hide messages</span>
                </li>
                <li class="flex items-center gap-2">
                    <i class="fas fa-check-circle text-green-600"></i>
                    <span>Beautiful gradient backgrounds</span>
                </li>
                <li class="flex items-center gap-2">
                    <i class="fas fa-check-circle text-green-600"></i>
                    <span>Responsive design</span>
                </li>
                <li class="flex items-center gap-2">
                    <i class="fas fa-check-circle text-green-600"></i>
                    <span>JavaScript toast notifications</span>
                </li>
            </ul>
        </section>
    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

