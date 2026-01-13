<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

use App\Models\QualityCertification;

// This script adds demo quality certifications with placeholder text
// You can upload actual logos later through the admin interface

$certModel = new QualityCertification();

// Demo certifications data
$demoCertifications = [
    [
        'name' => 'ISO 9001:2015',
        'logo' => 'storage/uploads/demo/cert-iso9001.png',
        'reference_url' => 'https://www.iso.org/iso-9001-quality-management.html',
        'description' => 'Quality Management System Certification',
        'sort_order' => 1,
        'is_active' => 1
    ],
    [
        'name' => 'ISO 14001:2015',
        'logo' => 'storage/uploads/demo/cert-iso14001.png',
        'reference_url' => 'https://www.iso.org/iso-14001-environmental-management.html',
        'description' => 'Environmental Management System Certification',
        'sort_order' => 2,
        'is_active' => 1
    ],
    [
        'name' => 'CE Marking',
        'logo' => 'storage/uploads/demo/cert-ce.png',
        'reference_url' => 'https://ec.europa.eu/growth/single-market/ce-marking_en',
        'description' => 'European Conformity Marking',
        'sort_order' => 3,
        'is_active' => 1
    ],
    [
        'name' => 'RIML',
        'logo' => 'storage/uploads/demo/cert-riml.png',
        'reference_url' => 'https://www.riml.org',
        'description' => 'RIML Quality Certification',
        'sort_order' => 4,
        'is_active' => 1
    ],
    [
        'name' => 'OHSAS 18001',
        'logo' => 'storage/uploads/demo/cert-ohsas.png',
        'reference_url' => 'https://www.iso.org/iso-45001-occupational-health-and-safety.html',
        'description' => 'Occupational Health and Safety Management',
        'sort_order' => 5,
        'is_active' => 1
    ],
    [
        'name' => 'ISO 45001:2018',
        'logo' => 'storage/uploads/demo/cert-iso45001.png',
        'reference_url' => 'https://www.iso.org/iso-45001-occupational-health-and-safety.html',
        'description' => 'Occupational Health and Safety Management System',
        'sort_order' => 6,
        'is_active' => 1
    ],
    [
        'name' => 'ISO 27001',
        'logo' => 'storage/uploads/demo/cert-iso27001.png',
        'reference_url' => 'https://www.iso.org/isoiec-27001-information-security.html',
        'description' => 'Information Security Management System',
        'sort_order' => 7,
        'is_active' => 1
    ],
    [
        'name' => 'IATF 16949',
        'logo' => 'storage/uploads/demo/cert-iatf.png',
        'reference_url' => 'https://www.iatfglobaloversight.org',
        'description' => 'Automotive Quality Management System',
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
        
        // Add demo certifications
        foreach ($demoCertifications as $cert) {
            // Check if already exists
            $existing = $certModel->getAll();
            $exists = false;
            foreach ($existing as $existingCert) {
                if ($existingCert['name'] === $cert['name']) {
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
                $certModel->create($cert);
                $added++;
            }
        }
        
        if ($added > 0) {
            $message = "Successfully added $added demo certifications. Please upload actual logos through the admin interface.";
        } else {
            $message = "Demo data already exists. All certifications are already in the database.";
        }
    } catch (\Exception $e) {
        $error = 'Error adding demo data: ' . $e->getMessage();
    }
}

$pageTitle = 'Add Demo Quality Certifications';
include __DIR__ . '/includes/header.php';
?>

<div class="w-full">
    <!-- Header -->
    <div class="bg-gradient-to-r from-gray-700 to-gray-900 rounded-xl shadow-xl p-4 md:p-6 lg:p-8 mb-4 md:mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-1 md:mb-2">
                    <i class="fas fa-database mr-2 md:mr-3"></i>
                    Add Demo Quality Certifications
                </h1>
                <p class="text-gray-300 text-sm md:text-lg">Add sample data for testing</p>
            </div>
            <a href="<?= url('admin/quality-certifications.php') ?>" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Certifications
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
                This will add <strong>8 Quality Certifications</strong> (ISO, CE, RIML, etc.) with sample data.
                You'll need to upload actual logos through the admin interface after adding them.
            </p>
        </div>

        <!-- Demo Certifications Preview -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3 text-blue-600">
                <i class="fas fa-certificate mr-2"></i>Demo Certifications (8)
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach ($demoCertifications as $cert): ?>
                <div class="border border-gray-200 rounded-lg p-3 text-center">
                    <div class="bg-gray-100 h-16 flex items-center justify-center rounded mb-2">
                        <i class="fas fa-certificate text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-700"><?= escape($cert['name']) ?></p>
                    <p class="text-xs text-gray-500 mt-1"><?= escape($cert['description']) ?></p>
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
            <a href="<?= url('admin/quality-certifications.php') ?>" class="ml-4 bg-gray-200 hover:bg-gray-300 text-gray-800 px-8 py-3 rounded-lg font-bold text-lg transition-all duration-300">
                Cancel
            </a>
        </form>

        <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
            <p class="text-sm text-blue-800">
                <i class="fas fa-info-circle mr-2"></i>
                <strong>Note:</strong> After adding demo data, go to <a href="<?= url('admin/quality-certifications.php') ?>" class="underline font-semibold">Quality Certifications</a> 
                to upload actual logos for each certification.
            </p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
