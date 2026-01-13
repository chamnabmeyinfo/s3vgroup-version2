<?php
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/includes/auth.php';

use App\Models\Service;

$serviceModel = new Service();

// Demo services data
$demoServices = [
    [
        'title' => 'Forklift Sales',
        'slug' => 'forklift-sales',
        'description' => 'Wide selection of new and used forklifts from top manufacturers',
        'content' => '<h2>Comprehensive Forklift Sales</h2><p>We offer a wide range of forklifts including electric, diesel, and gas-powered models. From compact warehouse forklifts to heavy-duty industrial machines, we have the perfect solution for your business needs.</p><ul><li>New and used forklifts</li><li>All major brands available</li><li>Flexible financing options</li><li>Trade-in programs</li></ul>',
        'icon' => 'fas fa-truck',
        'sort_order' => 1,
        'is_active' => 1,
        'meta_title' => 'Forklift Sales - New & Used Forklifts',
        'meta_description' => 'Browse our extensive selection of new and used forklifts. Best prices and flexible financing available.'
    ],
    [
        'title' => 'Equipment Rental',
        'slug' => 'equipment-rental',
        'description' => 'Short-term and long-term rental solutions for all your equipment needs',
        'content' => '<h2>Flexible Equipment Rental</h2><p>Need equipment for a short-term project? Our rental program offers flexible terms from daily to monthly rentals. Perfect for seasonal peaks or special projects.</p><ul><li>Daily, weekly, and monthly rentals</li><li>Well-maintained equipment</li><li>Delivery and pickup service</li><li>Operator training included</li></ul>',
        'icon' => 'fas fa-hand-holding-usd',
        'sort_order' => 2,
        'is_active' => 1,
        'meta_title' => 'Equipment Rental Services',
        'meta_description' => 'Flexible rental solutions for forklifts and industrial equipment. Short-term and long-term options available.'
    ],
    [
        'title' => 'Maintenance & Repairs',
        'slug' => 'maintenance-repairs',
        'description' => 'Expert maintenance and repair services to keep your equipment running smoothly',
        'content' => '<h2>Professional Maintenance & Repairs</h2><p>Our certified technicians provide comprehensive maintenance and repair services. We offer scheduled maintenance programs to prevent costly breakdowns and extend equipment life.</p><ul><li>Preventive maintenance programs</li><li>Emergency repair services</li><li>Original parts and components</li><li>24/7 support available</li></ul>',
        'icon' => 'fas fa-tools',
        'sort_order' => 3,
        'is_active' => 1,
        'meta_title' => 'Forklift Maintenance & Repair Services',
        'meta_description' => 'Expert maintenance and repair services for all forklift brands. Preventive maintenance programs available.'
    ],
    [
        'title' => 'Parts & Accessories',
        'slug' => 'parts-accessories',
        'description' => 'Genuine parts and quality accessories for all equipment brands',
        'content' => '<h2>Genuine Parts & Accessories</h2><p>We stock a comprehensive inventory of genuine OEM parts and quality aftermarket alternatives. Fast shipping and competitive prices on all parts and accessories.</p><ul><li>Genuine OEM parts</li><li>Quality aftermarket alternatives</li><li>Fast shipping available</li><li>Competitive pricing</li></ul>',
        'icon' => 'fas fa-cog',
        'sort_order' => 4,
        'is_active' => 1,
        'meta_title' => 'Forklift Parts & Accessories',
        'meta_description' => 'Genuine parts and accessories for all forklift brands. Fast shipping and competitive prices.'
    ],
    [
        'title' => 'Operator Training',
        'slug' => 'operator-training',
        'description' => 'Comprehensive training programs to ensure safe and efficient equipment operation',
        'content' => '<h2>Professional Operator Training</h2><p>Safety is our priority. Our certified instructors provide comprehensive training programs covering operation, safety protocols, and maintenance basics.</p><ul><li>OSHA-compliant training</li><li>Certification programs</li><li>On-site or classroom training</li><li>Refresher courses available</li></ul>',
        'icon' => 'fas fa-user-graduate',
        'sort_order' => 5,
        'is_active' => 1,
        'meta_title' => 'Forklift Operator Training & Certification',
        'meta_description' => 'OSHA-compliant forklift operator training and certification programs. On-site and classroom options available.'
    ],
    [
        'title' => 'Consulting Services',
        'slug' => 'consulting-services',
        'description' => 'Expert advice to optimize your warehouse and material handling operations',
        'content' => '<h2>Material Handling Consulting</h2><p>Our experienced consultants help you optimize your warehouse operations, improve efficiency, and reduce costs through better equipment selection and layout planning.</p><ul><li>Warehouse optimization</li><li>Equipment selection guidance</li><li>Layout planning</li><li>Cost analysis</li></ul>',
        'icon' => 'fas fa-chart-line',
        'sort_order' => 6,
        'is_active' => 1,
        'meta_title' => 'Material Handling Consulting Services',
        'meta_description' => 'Expert consulting services for warehouse optimization and material handling solutions.'
    ]
];

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_demo'])) {
    try {
        $added = 0;
        
        // Add demo services
        foreach ($demoServices as $service) {
            // Check if already exists
            $existing = $serviceModel->getAll();
            $exists = false;
            foreach ($existing as $existingService) {
                if ($existingService['slug'] === $service['slug']) {
                    $exists = true;
                    break;
                }
            }
            
            if (!$exists) {
                $serviceModel->create($service);
                $added++;
            }
        }
        
        if ($added > 0) {
            $message = "Successfully added $added demo services.";
        } else {
            $message = "Demo data already exists. All services are already in the database.";
        }
    } catch (\Exception $e) {
        $error = 'Error adding demo data: ' . $e->getMessage();
    }
}

$pageTitle = 'Add Demo Services';
include __DIR__ . '/includes/header.php';
?>

<div class="w-full">
    <!-- Header -->
    <div class="bg-gradient-to-r from-gray-700 to-gray-900 rounded-xl shadow-xl p-4 md:p-6 lg:p-8 mb-4 md:mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold mb-1 md:mb-2">
                    <i class="fas fa-database mr-2 md:mr-3"></i>
                    Add Demo Services
                </h1>
                <p class="text-gray-300 text-sm md:text-lg">Add sample data for testing</p>
            </div>
            <a href="<?= url('admin/services.php') ?>" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Services
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
                This will add <strong>6 Services</strong> with sample data including descriptions, content, and icons.
            </p>
        </div>

        <!-- Demo Services Preview -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-3 text-blue-600">
                <i class="fas fa-concierge-bell mr-2"></i>Demo Services (6)
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($demoServices as $service): ?>
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <?php if (!empty($service['icon'])): ?>
                        <div class="flex-shrink-0">
                            <i class="<?= escape($service['icon']) ?> text-2xl text-blue-600"></i>
                        </div>
                        <?php endif; ?>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-700 mb-1"><?= escape($service['title']) ?></h4>
                            <p class="text-sm text-gray-600"><?= escape($service['description']) ?></p>
                        </div>
                    </div>
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
            <a href="<?= url('admin/services.php') ?>" class="ml-4 bg-gray-200 hover:bg-gray-300 text-gray-800 px-8 py-3 rounded-lg font-bold text-lg transition-all duration-300">
                Cancel
            </a>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
