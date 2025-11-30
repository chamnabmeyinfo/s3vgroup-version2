<?php
/**
 * New Page Template
 * 
 * Copy this template when creating new public-facing pages
 */
require_once __DIR__ . '/bootstrap/app.php';

// Check under construction mode
// This shows the construction page to public users
// Admin users and API bypass this automatically
use App\Helpers\UnderConstruction;
UnderConstruction::show();

// Your page code starts here
// Admin users will see this content
// Public users (when construction is enabled) see the construction page

$pageTitle = 'Your Page Title';
$metaDescription = 'Your page description';
include __DIR__ . '/includes/header.php';
?>

<!-- Your HTML content here -->

<?php include __DIR__ . '/includes/footer.php'; ?>

