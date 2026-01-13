<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

use App\Models\Partner;

// This script adds demo partners/clients with placeholder text
// You can upload actual logos later through the admin interface

$partnerModel = new Partner();

// Demo partners data
$demoPartners = [
    [
        'name' => 'Toyota Material Handling',
        'logo' => 'storage/uploads/demo/partner-toyota.png',
        'website_url' => 'https://www.toyotaforklift.com',
        'type' => 'partner',
        'sort_order' => 1,
        'is_active' => 1
    ],
    [
        'name' => 'Caterpillar',
        'logo' => 'storage/uploads/demo/partner-caterpillar.png',
        'website_url' => 'https://www.cat.com',
        'type' => 'partner',
        'sort_order' => 2,
        'is_active' => 1
    ],
    [
        'name' => 'Komatsu',
        'logo' => 'storage/uploads/demo/partner-komatsu.png',
        'website_url' => 'https://www.komatsu.com',
        'type' => 'partner',
        'sort_order' => 3,
        'is_active' => 1
    ],
    [
        'name' => 'Hyster',
        'logo' => 'storage/uploads/demo/partner-hyster.png',
        'website_url' => 'https://www.hyster.com',
        'type' => 'partner',
        'sort_order' => 4,
        'is_active' => 1
    ],
    [
        'name' => 'Yale',
        'logo' => 'storage/uploads/demo/partner-yale.png',
        'website_url' => 'https://www.yale.com',
        'type' => 'partner',
        'sort_order' => 5,
        'is_active' => 1
    ],
    [
        'name' => 'Crown',
        'logo' => 'storage/uploads/demo/partner-crown.png',
        'website_url' => 'https://www.crown.com',
        'type' => 'partner',
        'sort_order' => 6,
        'is_active' => 1
    ],
    [
        'name' => 'Raymond',
        'logo' => 'storage/uploads/demo/partner-raymond.png',
        'website_url' => 'https://www.raymondcorp.com',
        'type' => 'partner',
        'sort_order' => 7,
        'is_active' => 1
    ],
    [
        'name' => 'Linde',
        'logo' => 'storage/uploads/demo/partner-linde.png',
        'website_url' => 'https://www.linde-mh.com',
        'type' => 'partner',
        'sort_order' => 8,
        'is_active' => 1
    ]
];

// Demo clients data
$demoClients = [
    [
        'name' => 'Amazon Logistics',
        'logo' => 'storage/uploads/demo/client-amazon.png',
        'website_url' => 'https://www.amazon.com',
        'type' => 'client',
        'sort_order' => 1,
        'is_active' => 1
    ],
    [
        'name' => 'Walmart Distribution',
        'logo' => 'storage/uploads/demo/client-walmart.png',
        'website_url' => 'https://www.walmart.com',
        'type' => 'client',
        'sort_order' => 2,
        'is_active' => 1
    ],
    [
        'name' => 'FedEx Supply Chain',
        'logo' => 'storage/uploads/demo/client-fedex.png',
        'website_url' => 'https://www.fedex.com',
        'type' => 'client',
        'sort_order' => 3,
        'is_active' => 1
    ],
    [
        'name' => 'DHL Supply Chain',
        'logo' => 'storage/uploads/demo/client-dhl.png',
        'website_url' => 'https://www.dhl.com',
        'type' => 'client',
        'sort_order' => 4,
        'is_active' => 1
    ],
    [
        'name' => 'UPS Logistics',
        'logo' => 'storage/uploads/demo/client-ups.png',
        'website_url' => 'https://www.ups.com',
        'type' => 'client',
        'sort_order' => 5,
        'is_active' => 1
    ],
    [
        'name' => 'Target Corporation',
        'logo' => 'storage/uploads/demo/client-target.png',
        'website_url' => 'https://www.target.com',
        'type' => 'client',
        'sort_order' => 6,
        'is_active' => 1
    ],
    [
        'name' => 'Home Depot',
        'logo' => 'storage/uploads/demo/client-homedepot.png',
        'website_url' => 'https://www.homedepot.com',
        'type' => 'client',
        'sort_order' => 7,
        'is_active' => 1
    ],
    [
        'name' => 'Costco Wholesale',
        'logo' => 'storage/uploads/demo/client-costco.png',
        'website_url' => 'https://www.costco.com',
        'type' => 'client',
        'sort_order' => 8,
        'is_active' => 1
    ]
];

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_demo'])) {
    try {
        $added = 0;
        
        // Add demo partners
        foreach ($demoPartners as $partner) {
            // Check if already exists
            $existing = $partnerModel->getAll();
            $exists = false;
            foreach ($existing as $existingPartner) {
                if ($existingPartner['name'] === $partner['name']) {
                    $exists = true;
                    break;
                }
            }
            
            if (!$exists) {
                // Create placeholder logo if directory doesn't exist
                $logoDir = __DIR__ . '/../storage/uploads/demo/';
                if (!is_dir($logoDir)) {
                    mkdir($logoDir, 0755, true);
                }
                
                // Create a simple placeholder image path (you'll need to upload actual logos)
                $partnerModel->create($partner);
                $added++;
            }
        }
        
        // Add demo clients
        foreach ($demoClients as $client) {
            // Check if already exists
            $existing = $partnerModel->getAll();
            $exists = false;
            foreach ($existing as $existingClient) {
                if ($existingClient['name'] === $client['name']) {
                    $exists = true;
                    break;
                }
            }
            
            if (!$exists) {
                // Create placeholder logo if directory doesn't exist
                $logoDir = __DIR__ . '/../storage/uploads/demo/';
                if (!is_dir($logoDir)) {
                    mkdir($logoDir, 0755, true);
                }
                
                $partnerModel->create($client);
                $added++;
            }
        }
        
        if ($added > 0) {
            $message = "Successfully added $added demo partners/clients. Please upload actual logos through the admin interface.";
        } else {
            $message = "Demo data already exists. All partners/clients are already in the database.";
        }
    } catch (\Exception $e) {
        $error = 'Error adding demo data: ' . $e->getMessage();
    }
}

$pageTitle = 'Add Demo Partners & Clients';
include __DIR__ . '/includes/header.php';
?>

<div class="w-full">
    <!-- Header -->
    <div class="bg-gradient-to-r from-gray-700 to-gray-900 rounded-xl shadow-xl p-4 md:p-6 lg:p-8 mb-4 md:mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-1 md:mb-2">
                    <i class="fas fa-database mr-2 md:mr-3"></i>
                    Add Demo Partners & Clients
                </h1>
                <p class="text-gray-300 text-sm md:text-lg">Add sample data for testing</p>
            </div>
            <a href="<?= url('admin/partners.php') ?>" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Partners
            </a>
        </div>
    </div>

    <?php if (!empty($message)): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2 text-xl"></i>
            <span class="font-semibold"><?= escape($message) ?></span>
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

    <div class="bg-white rounded-xl shadow-lg p-4 md:p-6 lg:p-8">
        <div class="mb-6">
            <h2 class="text-xl font-bold mb-4">Demo Data Preview</h2>
            <p class="text-gray-600 mb-4">
                This will add <strong>8 Partners</strong> and <strong>8 Clients</strong> with sample data.
                You'll need to upload actual logos through the admin interface after adding them.
            </p>
        </div>

        <!-- Demo Partners Preview -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3 text-blue-600">
                <i class="fas fa-handshake mr-2"></i>Demo Partners (8)
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach ($demoPartners as $partner): ?>
                <div class="border border-gray-200 rounded-lg p-3 text-center">
                    <div class="bg-gray-100 h-16 flex items-center justify-center rounded mb-2">
                        <i class="fas fa-image text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-700"><?= escape($partner['name']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Demo Clients Preview -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3 text-green-600">
                <i class="fas fa-building mr-2"></i>Demo Clients (8)
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach ($demoClients as $client): ?>
                <div class="border border-gray-200 rounded-lg p-3 text-center">
                    <div class="bg-gray-100 h-16 flex items-center justify-center rounded mb-2">
                        <i class="fas fa-image text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-700"><?= escape($client['name']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Add Button -->
        <form method="POST" class="pt-4 border-t border-gray-200">
            <button type="submit" name="add_demo" class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-8 py-3 rounded-lg font-bold text-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl">
                <i class="fas fa-plus-circle mr-2"></i>
                Add Demo Data
            </button>
            <a href="<?= url('admin/partners.php') ?>" class="ml-4 bg-gray-200 hover:bg-gray-300 text-gray-800 px-8 py-3 rounded-lg font-bold text-lg transition-all duration-300">
                Cancel
            </a>
        </form>

        <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <p class="text-sm text-blue-800">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Note:</strong> After adding demo data, go to <a href="<?= url('admin/partners.php') ?>" class="underline font-semibold">Partners & Clients</a> 
                to upload actual logos for each partner/client.
            </p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
