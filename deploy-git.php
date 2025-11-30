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
    exec("git commit -m {$commitMessageEscaped} 2>&1", $output, $returnCode);
    
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
    
    if ($token) {
        // Get current remote URL
        exec('git remote get-url origin', $remoteOutput, $remoteReturnCode);
        if ($remoteReturnCode === 0 && !empty($remoteOutput)) {
            $currentUrl = trim($remoteOutput[0]);
            
            // If URL doesn't already contain token, add it
            if (strpos($currentUrl, '@') === false || strpos($currentUrl, '://' . $token . '@') === false) {
                // Extract repo path from URL
                if (preg_match('#https?://(?:[^@]+@)?github\.com/(.+)#', $currentUrl, $matches)) {
                    $repoPath = $matches[1];
                    $remoteUrl = "https://{$token}@github.com/{$repoPath}";
                    
                    // Temporarily set remote URL with token
                    exec("git remote set-url origin {$remoteUrl}", $setUrlOutput, $setUrlReturnCode);
                    if ($setUrlReturnCode !== 0) {
                        $log->warning("  ⚠️  Could not set remote URL with token, trying without...");
                    }
                }
            }
        }
    }
    
    // Push with token in URL (if configured)
    exec("git push origin {$branch} 2>&1", $output, $returnCode);
    
    // Restore original remote URL if we changed it
    if ($token && $remoteUrl) {
        exec('git remote get-url origin', $checkOutput);
        $currentUrl = trim($checkOutput[0] ?? '');
        if (strpos($currentUrl, $token) !== false) {
            // Remove token from URL for security
            $cleanUrl = preg_replace('#https://[^@]+@github\.com/#', 'https://github.com/', $currentUrl);
            exec("git remote set-url origin {$cleanUrl}", $restoreOutput, $restoreReturnCode);
        }
    }
    
    if ($returnCode !== 0) {
        $errorMsg = implode("\n", $output);
        // Don't expose token in error messages
        $errorMsg = preg_replace('#https://[^@]+@github\.com/#', 'https://github.com/', $errorMsg);
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

