<?php
/**
 * Production Cleanup Script
 * 
 * This script safely removes development, test, and unnecessary files
 * before deploying to production.
 * 
 * SECURITY: Review the files list before running!
 * 
 * Usage:
 * 1. Review PRODUCTION-CLEANUP-PLAN.md
 * 2. Run: php cleanup-production.php --dry-run (to see what will be deleted)
 * 3. Run: php cleanup-production.php --execute (to actually delete)
 */

// Security check - only allow from command line or with password
$isCli = php_sapi_name() === 'cli';
$hasPassword = isset($_GET['password']) && $_GET['password'] === 'CLEANUP2024';

if (!$isCli && !$hasPassword) {
    die("This script can only be run from command line or with password parameter.\n");
}

$dryRun = true;
$execute = false;

// Parse arguments
if ($isCli) {
    $args = $argv ?? [];
    if (in_array('--execute', $args)) {
        $dryRun = false;
        $execute = true;
    } elseif (in_array('--dry-run', $args)) {
        $dryRun = true;
    }
} else {
    if (isset($_GET['execute']) && $_GET['execute'] === '1') {
        $execute = true;
        $dryRun = false;
    }
}

echo "========================================\n";
echo "PRODUCTION CLEANUP SCRIPT\n";
echo "========================================\n";
echo "Mode: " . ($dryRun ? "DRY RUN (no files will be deleted)" : "EXECUTE (files will be deleted)") . "\n";
echo "========================================\n\n";

// Files to remove (categorized)
$filesToRemove = [
    // Test & Debug Files (SECURITY RISK)
    'test-simple.php',
    'test-connection.php',
    'check-php-config.php',
    'admin/check-images.php',
    'admin/api-test.php',
    'developer/debug-test.php',
    
    // Setup Scripts (No longer needed)
    'setup.php',
    'setup-advanced.php',
    'setup-direct.php',
    'setup-hero-slider-options.php',
    'setup-hero-sliders.php',
    'setup-hero-sliders-direct.php',
    'setup-password-reset.php',
    'admin/setup-roles.php',
    'admin/setup-orders.php',
    'admin/setup-variants.php',
    
    // Development/Demo Files
    'message-demo.php',
    'start-here.php',
    'NEW-PAGE-TEMPLATE.php',
    
    // Deployment Scripts (remove if not using automated deployment)
    'deploy-cleanup.php',
    'deploy-database-import-remote.php',
    'deploy-database-import.php',
    'deploy-database-upload.php',
    'deploy-ftp.php',
    'deploy-git.php',
    'deploy-main.php',
    'deploy-smart-ftp.php',
    'deploy-utils.php',
    'deploy-validation.php',
    'deploy.bat',
    'deploy-log.txt',
    'prepare-deployment.bat',
    'cpanel-safe-pull.php',
    'deploy-config.json', // Contains sensitive data
    
    // Git/Version Control Scripts
    'git-auto-push.bat',
    'git-auto-push.sh',
    'show-ignored-files.bat',
    'show-ignored-files.sh',
    
    // Documentation Files (keep only README.md and LICENSE)
    'ADVANCED-BACKEND-COMPLETE.md',
    'ADVANCED-BACKEND.md',
    'ADVANCED-FEATURES.md',
    'ADVANCED-FILTERS-COMPLETE.md',
    'ADVANCED-UX-FEATURES.md',
    'ALL-FEATURES.md',
    'APACHE-SETUP.txt',
    'BACKEND-COMPLETE.md',
    'BACKEND-INNOVATIONS.md',
    'BACKEND-PLANNING-PROMPT.md',
    'BACKEND-PLANNING-QUICK-START.txt',
    'BEST-DEPLOYMENT-SOLUTION.md',
    'COMPLETE-DEPLOYMENT-GUIDE.md',
    'COMPLETE.md',
    'CONTRIBUTING.md',
    'CPANEL-GIT-PULL-FIX.md',
    'DESIGN-VERSION-SYSTEM.md',
    'DEVELOPER-GUIDE.md',
    'DEVELOPMENT-WITH-UNDER-CONSTRUCTION.md',
    'FIX-503-ERROR.md',
    'FIX-IMAGE-PATHS.md',
    'FIX-IMAGES-NOT-LOADING.md',
    'FIX-UNDER-CONSTRUCTION.md',
    'GIT-AUTO-PUSH.md',
    'GIT-SETUP.md',
    'GITHUB-TOKEN-SETUP.md',
    'GREAT-FEATURES.md',
    'HOW-TO-CHANGE-SUPER-ADMIN-CREDENTIALS.md',
    'IMAGES-INFO.md',
    'IMPROVEMENTS.md',
    'LIST-IGNORED-FILES.md',
    'MORE-FEATURES.md',
    'MULTI-CHAT-WORKFLOW.md',
    'NEXT-STEPS-AFTER-PHP-FIX.md',
    'ONE-CLICK-DEPLOYMENT-PLAN.md',
    'ORDERS-MANAGEMENT-COMPLETE.md',
    'ORDERS-MANAGEMENT-SUMMARY.md',
    'PASSWORD-RESET-GUIDE.md',
    'PRODUCT-IMAGES-COMPLETE.md',
    'PRODUCT-IMAGES-SUMMARY.md',
    'PUSH-TO-GITHUB.md',
    'QUICK-DEVELOPMENT-GUIDE.md',
    'QUICK-FIX-LINKS.md',
    'QUICK-FIX-ORDERS.md',
    'QUICK-SETUP-DEPLOYMENT.md',
    'QUICK-START.md',
    'REPOSITORY-READY.md',
    'ROLE-MANAGEMENT-COMPLETE.md',
    'SAMPLE-DATA-INFO.md',
    'SETUP-ORDERS.md',
    'SETUP-ROLES.md',
    'SETUP.md',
    'SMART-DEPLOYMENT-IDEAS.md',
    'SMART-FEATURES-COMPLETE.md',
    'TROUBLESHOOTING.md',
    'ULTIMATE-FEATURES.md',
    'UNDER-CONSTRUCTION-COMPLETE.md',
    'UNDER-CONSTRUCTION-SETUP.md',
    'UPDATE-PRODUCTION-CONFIG.md',
    'UPLOAD-IMAGES-TO-CPANEL.md',
    'URLS.txt',
    'VIRTUAL-HOST-SETUP.md',
    'WHAT-GOES-TO-GITHUB.md',
    'WHAT-TO-DEVELOP-NEXT.md',
    'WHM-SETUP-GUIDE.md',
    'CRUD-ANALYSIS.md',
    'DEPLOYMENT-GUIDE.md',
    'DEPLOYMENT-SYSTEM-README.md',
    'FINAL-ADVANCED-FEATURES.md',
    'INFO.txt',
    'PRODUCTION-CLEANUP-PLAN.md', // This file itself (after review)
    
    // Temporary/Backup Files
    's3vgroup-deployment-20251130.zip',
    'deployment-exclude.txt',
    
    // PowerShell Scripts (Windows only)
    'setup-virtual-host.ps1',
];

// Directories to remove
$dirsToRemove = [
    'developer/', // Entire developer directory (development tools)
];

$baseDir = __DIR__;
$removedCount = 0;
$notFoundCount = 0;
$errors = [];

echo "Scanning files...\n\n";

// Process files
foreach ($filesToRemove as $file) {
    $filePath = $baseDir . '/' . $file;
    
    if (file_exists($filePath)) {
        if ($dryRun) {
            echo "✓ Would remove: $file\n";
        } else {
            if (unlink($filePath)) {
                echo "✓ Removed: $file\n";
                $removedCount++;
            } else {
                echo "✗ Error removing: $file\n";
                $errors[] = $file;
            }
        }
    } else {
        if (!$dryRun) {
            echo "⚠ Not found: $file (may already be removed)\n";
            $notFoundCount++;
        }
    }
}

// Process directories
foreach ($dirsToRemove as $dir) {
    $dirPath = $baseDir . '/' . $dir;
    
    if (is_dir($dirPath)) {
        if ($dryRun) {
            echo "✓ Would remove directory: $dir\n";
        } else {
            // Remove directory recursively
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            
            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
            
            if (rmdir($dirPath)) {
                echo "✓ Removed directory: $dir\n";
                $removedCount++;
            } else {
                echo "✗ Error removing directory: $dir\n";
                $errors[] = $dir;
            }
        }
    }
}

echo "\n========================================\n";
if ($dryRun) {
    echo "DRY RUN COMPLETE\n";
    echo "========================================\n";
    echo "Files that would be removed: " . count($filesToRemove) . "\n";
    echo "Directories that would be removed: " . count($dirsToRemove) . "\n";
    echo "\nTo actually remove files, run:\n";
    echo "  php cleanup-production.php --execute\n";
    echo "\nOr from browser:\n";
    echo "  cleanup-production.php?password=CLEANUP2024&execute=1\n";
} else {
    echo "CLEANUP COMPLETE\n";
    echo "========================================\n";
    echo "Files removed: $removedCount\n";
    echo "Files not found: $notFoundCount\n";
    if (!empty($errors)) {
        echo "Errors: " . count($errors) . "\n";
        foreach ($errors as $error) {
            echo "  - $error\n";
        }
    }
    echo "\n✅ Cleanup finished!\n";
    echo "⚠️  Remember to:\n";
    echo "   1. Test the website thoroughly\n";
    echo "   2. Check config files (database.php, app.php)\n";
    echo "   3. Verify .htaccess is correct\n";
    echo "   4. Set proper file permissions\n";
}
echo "========================================\n";

