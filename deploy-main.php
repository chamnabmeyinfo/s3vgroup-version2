<?php
/**
 * One-Click Deployment System - Main Script
 * Orchestrates Git push and FTP upload
 */

// Load configuration
$configFile = __DIR__ . '/deploy-config.json';
if (!file_exists($configFile)) {
    die("ERROR: deploy-config.json not found!\n");
}

$config = json_decode(file_get_contents($configFile), true);
if (!$config) {
    die("ERROR: Invalid deploy-config.json format!\n");
}

// Load utilities
require_once __DIR__ . '/deploy-utils.php';

$log = new DeploymentLogger();
$log->info("========================================");
$log->info("One-Click Deployment Started");
$log->info("========================================");

$errors = [];

// Step 0: Pre-Deployment Validation
if ($config['validation']['enabled'] ?? true) {
    $log->info("\n[0/4] Pre-Deployment Validation...");
    try {
        require_once __DIR__ . '/deploy-validation.php';
        $validationResult = validateBeforeDeploy($config, $log);
        
        if (!$validationResult['success']) {
            $log->error("\nValidation failed! Please fix errors before deploying.");
            $log->error("Deployment cancelled.");
            exit(1);
        }
        
        if (!empty($validationResult['warnings'])) {
            $log->warning("\nValidation passed with warnings. Continuing deployment...");
        }
    } catch (Exception $e) {
        $log->warning("Validation error: " . $e->getMessage());
        $log->warning("Continuing deployment anyway...");
    }
} else {
    $log->info("\n[0/4] Pre-deployment validation skipped (disabled in config)");
}

// Step 1: Git Push
if ($config['git']['enabled'] ?? true) {
    $log->info("\n[1/4] Pushing to GitHub...");
    try {
        require_once __DIR__ . '/deploy-git.php';
        $gitResult = deployGit($config, $log);
        if (!$gitResult['success']) {
            $errors[] = "Git push failed: " . $gitResult['message'];
        }
    } catch (Exception $e) {
        $errors[] = "Git error: " . $e->getMessage();
        $log->error("Git error: " . $e->getMessage());
    }
} else {
    $log->info("\n[1/4] Git push skipped (disabled in config)");
}

// Step 2: FTP Upload (Smart)
if ($config['ftp']['enabled'] ?? true) {
    $log->info("\n[2/4] Uploading via FTP (Smart Mode)...");
    try {
        // Use smart FTP module (with change detection, conflict detection, backup)
        require_once __DIR__ . '/deploy-smart-ftp.php';
        $ftpResult = deployFTP($config, $log);
        if (!$ftpResult['success']) {
            $errors[] = "FTP upload failed: " . $ftpResult['message'];
        } else {
            // Show smart stats
            if (isset($ftpResult['files_skipped']) && $ftpResult['files_skipped'] > 0) {
                $log->info("  💡 Smart: Skipped " . $ftpResult['files_skipped'] . " unchanged file(s)");
            }
            if (isset($ftpResult['files_conflicted']) && $ftpResult['files_conflicted'] > 0) {
                $log->info("  💡 Smart: Handled " . $ftpResult['files_conflicted'] . " conflict(s)");
            }
        }
    } catch (Exception $e) {
        $errors[] = "FTP error: " . $e->getMessage();
        $log->error("FTP error: " . $e->getMessage());
    }
} else {
    $log->info("\n[2/4] FTP upload skipped (disabled in config)");
}

// Step 3: Summary
$log->info("\n[3/4] Finalizing...");
$log->info("========================================");

if (empty($errors)) {
    $log->info("Deployment Complete!");
    $log->info("========================================");
    exit(0);
} else {
    $log->error("Deployment completed with errors:");
    foreach ($errors as $error) {
        $log->error("  - " . $error);
    }
    $log->error("========================================");
    exit(1);
}

