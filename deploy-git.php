<?php
/**
 * Git Deployment Module
 * Handles pushing code to GitHub
 */

function deployGit($config, $log) {
    $result = ['success' => false, 'message' => '', 'files_pushed' => 0];
    
    // Check if git is initialized
    if (!is_dir('.git')) {
        $result['message'] = 'Not a git repository';
        return $result;
    }
    
    // Check for changes
    $log->info("  Checking for changes...");
    exec('git status --porcelain', $output, $returnCode);
    
    if (empty($output) && $returnCode === 0) {
        $log->info("  ✓ No changes to commit");
        $result['success'] = true;
        $result['message'] = 'No changes';
        return $result;
    }
    
    $changedFiles = count(array_filter($output, function($line) {
        return !empty(trim($line));
    }));
    
    $log->info("  Found {$changedFiles} changed file(s)");
    
    // Add all changes
    $log->info("  Adding files...");
    exec('git add -A', $output, $returnCode);
    if ($returnCode !== 0) {
        $result['message'] = 'Failed to add files';
        return $result;
    }
    $log->info("  ✓ Files added");
    
    // Commit
    $commitMessage = $config['git']['commit_message'] ?? 'Auto deploy: ' . date('Y-m-d H:i:s');
    $commitMessage = str_replace('{timestamp}', date('Y-m-d H:i:s'), $commitMessage);
    
    $log->info("  Committing changes...");
    $branch = $config['git']['branch'] ?? 'main';
    
    // Properly escape commit message for Windows/PowerShell
    $commitMessageEscaped = escapeshellarg($commitMessage);
    exec("git commit -m " . $commitMessageEscaped . " 2>&1", $output, $returnCode);
    
    if ($returnCode !== 0) {
        // Check if commit failed because no changes (already committed)
        $outputStr = implode("\n", $output);
        if (strpos($outputStr, 'nothing to commit') !== false || 
            strpos($outputStr, 'no changes added to commit') !== false) {
            $log->info("  ✓ Already committed");
        } else {
            // Show actual error
            $errorMsg = trim($outputStr);
            if (empty($errorMsg)) {
                $errorMsg = 'Unknown commit error (exit code: ' . $returnCode . ')';
            }
            $result['message'] = 'Failed to commit: ' . $errorMsg;
            $log->error("  ✗ Commit failed: " . $errorMsg);
            return $result;
        }
    } else {
        $log->info("  ✓ Committed");
    }
    
    // Push
    $log->info("  Pushing to GitHub...");
    
    // Check if token is configured
    $token = $config['git']['token'] ?? null;
    $remoteUrl = null;
    $originalUrl = null; // Store original URL for restoration
    $urlModifiedSuccessfully = false; // Track if we successfully modified the URL
    $attemptedModification = false; // Track if we attempted to modify the URL
    
    if ($token) {
        // Get current remote URL
        exec('git remote get-url origin', $remoteOutput, $remoteReturnCode);
        if ($remoteReturnCode === 0 && !empty($remoteOutput)) {
            $currentUrl = trim($remoteOutput[0]);
            $originalUrl = $currentUrl; // Store original for restoration
            
            // If URL doesn't already contain token, add it
            if (strpos($currentUrl, '@') === false || strpos($currentUrl, '://' . $token . '@') === false) {
                // Extract protocol and repo path from URL
                if (preg_match('#(https?)://(?:[^@]+@)?github\.com/(.+)#', $currentUrl, $matches)) {
                    $protocol = $matches[1]; // Preserve original protocol (http or https)
                    $repoPath = $matches[2];
                    $remoteUrl = "{$protocol}://{$token}@github.com/{$repoPath}";
                    
                    // Temporarily set remote URL with token
                    $attemptedModification = true; // We attempted to modify
                    exec("git remote set-url origin " . escapeshellarg($remoteUrl), $setUrlOutput, $setUrlReturnCode);
                    if ($setUrlReturnCode === 0) {
                        $urlModifiedSuccessfully = true; // Successfully modified URL
                    } else {
                        $log->warning("  ⚠️  Could not set remote URL with token, trying without...");
                        $remoteUrl = null; // Don't try to restore if we didn't change it
                    }
                }
            } else {
                // Token already in URL (from previous deployment or manual config)
                // We didn't modify it, but we'll use it for push and clean it up after
                $remoteUrl = null; // Don't modify before push
                $attemptedModification = false; // We didn't attempt to modify
                // Note: $urlModifiedSuccessfully remains false - we didn't modify it
            }
        }
    }
    
    // Push with token in URL (if configured)
    exec("git push origin " . escapeshellarg($branch) . " 2>&1", $output, $returnCode);
    
    // Restore/clean remote URL if token was used (CRITICAL for security)
    // Only clean up if we successfully modified the URL OR if token was already present (we used it)
    if ($token && $originalUrl) {
        // Check if original URL contains any token (indicated by @ in URL)
        $originalHadToken = (strpos($originalUrl, '@') !== false);
        
        // Clean up only if:
        // 1. We successfully modified the URL (added our token), OR
        // 2. Original URL had a token AND we didn't attempt to modify it (token was already there, we used it)
        //    BUT NOT if we attempted modification and failed (don't touch original token if modification failed)
        if ($urlModifiedSuccessfully || ($originalHadToken && !$attemptedModification)) {
            // Try to get current URL to verify token is present
            exec('git remote get-url origin', $checkOutput, $checkReturnCode);
            
            if ($checkReturnCode === 0 && !empty($checkOutput)) {
                $currentUrl = trim($checkOutput[0]);
                // Check if current URL contains any token (indicated by @ in URL)
                if (strpos($currentUrl, '@') !== false) {
                    // Remove ANY token from URL for security (our token or previously existing token)
                    // Match both http:// and https:// protocols
                    $cleanUrl = preg_replace('#(https?)://[^@]+@github\.com/#', '$1://github.com/', $currentUrl);
                    exec("git remote set-url origin " . escapeshellarg($cleanUrl), $restoreOutput, $restoreReturnCode);
                    
                    if ($restoreReturnCode !== 0) {
                        $log->error("  ⚠️  SECURITY WARNING: Failed to remove token from git remote URL!");
                        // If original URL didn't have a token, restore it; otherwise suggest manual cleanup
                        if (strpos($originalUrl, '@') === false) {
                            $log->error("  ⚠️  Please manually run: git remote set-url origin " . escapeshellarg($originalUrl));
                        } else {
                            $log->error("  ⚠️  Please manually run: git remote set-url origin " . escapeshellarg($cleanUrl));
                        }
                    } else {
                        $log->info("  ✓ Token removed from git remote URL");
                    }
                } else {
                    // No token found in URL - check if we need to restore original (if it was different)
                    if ($currentUrl !== $originalUrl && strpos($originalUrl, '@') === false) {
                        // Original URL didn't have token, current doesn't either, but they differ - restore original
                        $log->warning("  ⚠️  URLs differ, restoring original URL...");
                        exec("git remote set-url origin " . escapeshellarg($originalUrl), $restoreOutput, $restoreReturnCode);
                        
                        if ($restoreReturnCode !== 0) {
                            $log->error("  ⚠️  SECURITY WARNING: Failed to restore original git remote URL!");
                        } else {
                            $log->info("  ✓ Restored original git remote URL");
                        }
                    }
                }
            } else {
                // get-url failed, but we must still restore - use original URL
                if ($originalUrl) {
                    $log->warning("  ⚠️  Could not verify current URL, restoring original...");
                    exec("git remote set-url origin " . escapeshellarg($originalUrl), $restoreOutput, $restoreReturnCode);
                    
                    if ($restoreReturnCode !== 0) {
                        $log->error("  ⚠️  SECURITY WARNING: Failed to restore original git remote URL!");
                        $log->error("  ⚠️  Token may still be in git config. Please manually check and fix.");
                    } else {
                        $log->info("  ✓ Restored original git remote URL");
                    }
                } else {
                    $log->error("  ⚠️  SECURITY WARNING: Could not restore git remote URL - token may remain!");
                }
            }
        }
    }
    
    if ($returnCode !== 0) {
        $errorMsg = implode("\n", $output);
        // Don't expose token in error messages (match both http:// and https://)
        $errorMsg = preg_replace('#(https?)://[^@]+@github\.com/#', '$1://github.com/', $errorMsg);
        $result['message'] = 'Failed to push: ' . $errorMsg;
        $log->error("  ✗ Push failed");
        return $result;
    }
    
    $log->info("  ✓ Pushed to origin/{$branch}");
    $result['success'] = true;
    $result['files_pushed'] = $changedFiles;
    $result['message'] = "Pushed {$changedFiles} file(s)";
    
    return $result;
}

