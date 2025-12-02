<?php
/**
 * Safe Git Pull Script for cPanel
 * This script handles Git pull safely by stashing local changes
 * Upload this to your cPanel root directory and run it via browser or SSH
 */

// Security: Only allow execution from command line or localhost
if (php_sapi_name() !== 'cli' && $_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1') {
    // For web access, require authentication
    if (!isset($_GET['token']) || $_GET['token'] !== 'YOUR_SECRET_TOKEN_HERE') {
        die('Access denied. Set a secure token in the script.');
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
runCommand("$gitPath stash push -m 'Auto-stash before pull: " . date('Y-m-d H:i:s') . "'", $output, $errors);
$output[] = "";

// Step 3: Remove untracked files that would conflict
$output[] = "=== Step 3: Cleaning Untracked Files ===";
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

