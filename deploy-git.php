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
    exec("git push origin {$branch}", $output, $returnCode);
    
    if ($returnCode !== 0) {
        $result['message'] = 'Failed to push: ' . implode("\n", $output);
        return $result;
    }
    
    $log->info("  ✓ Pushed to origin/{$branch}");
    $result['success'] = true;
    $result['files_pushed'] = $changedFiles;
    $result['message'] = "Pushed {$changedFiles} file(s)";
    
    return $result;
}

