<?php
/**
 * Safe Git Pull Script for cPanel
 * This script handles Git pull safely by stashing local changes
 * Upload this to your cPanel root directory and run it via browser or SSH
 * 
 * SECURITY WARNING: 
 * - The token below is visible in the URL - this is NOT secure for production!
 * - For better security, use SSH instead: php cpanel-safe-pull.php
 * - Or change this token to a long random string and keep it secret
 */

// Security: Only allow execution from command line or localhost
// For web access, require authentication token
// ⚠️ SECURITY WARNING: Using a GitHub token in URL is NOT secure!
// Generate a random token instead: openssl rand -hex 32
$allowedToken = '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';

if (php_sapi_name() !== 'cli' && $_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1') {
    // For web access, require authentication
    if (!isset($_GET['token']) || $_GET['token'] !== $allowedToken) {
        http_response_code(403);
        die('Access denied. Invalid token.');
    }
}

$output = [];
$errors = [];

function runCommand($command, &$output, &$errors) {
    exec($command . ' 2>&1', $cmdOutput, $returnCode);
    $output[] = '$ ' . $command;
    $output[] = implode("\n", $cmdOutput);
    if ($returnCode !== 0) {
        $errors[] = "Command failed (exit code: $returnCode): $command";
    }
    return $returnCode === 0;
}

// Find Git executable
$gitPath = 'git';
if (file_exists('/usr/local/cpanel/3rdparty/bin/git')) {
    $gitPath = '/usr/local/cpanel/3rdparty/bin/git';
} elseif (file_exists('/usr/bin/git')) {
    $gitPath = '/usr/bin/git';
}

$output[] = "Using Git: $gitPath";
$output[] = "";

// Step 1: Check Git status
$output[] = "=== Step 1: Checking Git Status ===";
runCommand("$gitPath status --porcelain", $output, $errors);
$output[] = "";

// Step 2: Stash local changes (if any)
$output[] = "=== Step 2: Stashing Local Changes ===";
// Check if there are changes to stash
$hasChanges = false;
exec("$gitPath status --porcelain 2>&1", $statusOutput, $statusCode);
if (!empty($statusOutput)) {
    foreach ($statusOutput as $line) {
        if (preg_match('/^[MADRC]/', $line)) {
            $hasChanges = true;
            break;
        }
    }
}

if ($hasChanges) {
    $output[] = "Local changes detected, stashing...";
    runCommand("$gitPath stash push -m 'Auto-stash before pull: " . date('Y-m-d H:i:s') . "'", $output, $errors);
} else {
    $output[] = "No local changes to stash.";
}
$output[] = "";

// Step 3: Handle untracked files that would conflict
$output[] = "=== Step 3: Handling Untracked Files ===";
// First, check what untracked files exist
runCommand("$gitPath status --porcelain | grep '^??'", $output, $errors);
$output[] = "";

// Backup untracked files that would be overwritten
$output[] = "Backing up untracked files...";
$untrackedFiles = [
    'CPANEL-GIT-PULL-FIX.md',
    'database/add-hero-slider-options.sql',
    'setup-hero-slider-options.php'
];

$backupDir = 'storage/backups/git-pull-' . date('Y-m-d-H-i-s');
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

foreach ($untrackedFiles as $file) {
    if (file_exists($file)) {
        $backupPath = $backupDir . '/' . basename($file);
        if (copy($file, $backupPath)) {
            $output[] = "Backed up: $file -> $backupPath";
        }
    }
}
$output[] = "";

// Now remove untracked files that would conflict
$output[] = "Removing conflicting untracked files...";
runCommand("$gitPath clean -fd", $output, $errors);
$output[] = "";

// Step 4: Fetch latest changes
$output[] = "=== Step 4: Fetching Latest Changes ===";
runCommand("$gitPath fetch origin", $output, $errors);
$output[] = "";

// Step 5: Pull changes
$output[] = "=== Step 5: Pulling Changes ===";
$branch = 'main'; // Change to 'master' if needed
$pullSuccess = runCommand("$gitPath pull origin $branch", $output, $errors);
$output[] = "";

// Step 6: Apply stashed changes back (if any were stashed)
$output[] = "=== Step 6: Restoring Stashed Changes ===";
runCommand("$gitPath stash pop", $output, $errors);
$output[] = "";

// Final status
$output[] = "=== Final Status ===";
runCommand("$gitPath status", $output, $errors);

// Output results
if (php_sapi_name() === 'cli') {
    // Command line output
    echo implode("\n", $output);
    if (!empty($errors)) {
        echo "\n\n=== ERRORS ===\n";
        echo implode("\n", $errors);
        exit(1);
    }
} else {
    // Web output
    header('Content-Type: text/plain');
    echo "<pre>";
    echo implode("\n", $output);
    if (!empty($errors)) {
        echo "\n\n=== ERRORS ===\n";
        echo implode("\n", $errors);
    }
    echo "</pre>";
}

