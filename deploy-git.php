<?php
/**
 * Git Deployment Module
 * Handles pushing code to GitHub
 */

function deployGit($config, $log) {
    $result = ['success' => false, 'message' => '', 'files_pushed' => 0];
    
    // Find Git executable
    $gitPath = findGitExecutable();
    if (!$gitPath) {
        $result['message'] = 'Git executable not found. Please ensure Git is installed and in PATH.';
        $log->error("  ✗ Git not found");
        return $result;
    }
    
    // Check if git is initialized
    if (!is_dir('.git')) {
        $result['message'] = 'Not a git repository';
        return $result;
    }
    
    // Check for changes
    $log->info("  Checking for changes...");
    exec(escapeshellarg($gitPath) . ' status --porcelain', $output, $returnCode);
    
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
    exec(escapeshellarg($gitPath) . ' add -A', $output, $returnCode);
    if ($returnCode !== 0) {
        $result['message'] = 'Failed to add files';
        return $result;
    }
    $log->info("  ✓ Files added");
    
    // Commit with timestamp (matching user's preferred format)
    $timestamp = date('Y-m-d H:i:s');
    $commitMessage = $config['git']['commit_message'] ?? 'Auto deploy: ' . $timestamp;
    $commitMessage = str_replace('{timestamp}', $timestamp, $commitMessage);
    // Also replace $(date) pattern if user prefers that format
    $commitMessage = str_replace('$(date)', $timestamp, $commitMessage);
    
    $log->info("  Committing changes...");
    $branch = $config['git']['branch'] ?? 'main';
    
    // Properly escape commit message for Windows/PowerShell
    $commitMessageEscaped = escapeshellarg($commitMessage);
    exec(escapeshellarg($gitPath) . " commit -m " . $commitMessageEscaped . " 2>&1", $output, $returnCode);
    
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
    
    // Push using standard Git commands (rely on credential helper)
    $log->info("  Pushing to GitHub...");
    
    // Configure Git to use credential helper (Windows Credential Manager)
    // This allows Git to use stored credentials instead of tokens
    if ($config['git']['use_credential_helper'] ?? true) {
        // Try Windows Credential Manager first (most common on Windows)
        @exec(escapeshellarg($gitPath) . ' config --global credential.helper manager-core 2>&1', $credHelperOutput, $credHelperReturnCode);
        if ($credHelperReturnCode !== 0) {
            // Try wincred (older Windows)
            @exec(escapeshellarg($gitPath) . ' config --global credential.helper wincred 2>&1', $credHelperOutput2, $credHelperReturnCode2);
            if ($credHelperReturnCode2 !== 0) {
                // Fallback to store (works everywhere but less secure)
                @exec(escapeshellarg($gitPath) . ' config --global credential.helper store 2>&1', $credHelperOutput3, $credHelperReturnCode3);
            }
        }
    }
    
    // Set environment variables to suppress prompts
    putenv('GIT_TERMINAL_PROMPT=0');
    putenv('GCM_INTERACTIVE=never');
    
    // Use retry logic for network errors (common on Windows)
    $maxRetries = 3;
    $retryDelay = 2; // seconds
    $returnCode = 1;
    $output = [];
    $lastError = '';
    $attempt = 0; // Track which attempt succeeded
    
    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        if ($attempt > 1) {
            $log->info("  Retry attempt {$attempt}/{$maxRetries} (waiting {$retryDelay}s)...");
            sleep($retryDelay);
            // Increase delay for subsequent retries
            $retryDelay += 1;
        }
        
        // Use proc_open for better environment variable control on Windows
        $descriptorspec = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w']   // stderr
        ];
        
        $env = $_ENV;
        $env['GIT_TERMINAL_PROMPT'] = '0';
        $env['GCM_INTERACTIVE'] = 'never';
        $env['GIT_ASKPASS'] = 'echo';
        // Add timeout for network operations
        $env['GIT_HTTP_LOW_SPEED_LIMIT'] = '1000';
        $env['GIT_HTTP_LOW_SPEED_TIME'] = '30';
        
        $pushCommand = escapeshellarg($gitPath) . " push origin " . escapeshellarg($branch) . " 2>&1";
        
        $process = @proc_open($pushCommand, $descriptorspec, $pipes, null, $env);
        
        if (is_resource($process)) {
            // Close stdin
            fclose($pipes[0]);
            
            // Read output
            $output = [];
            $outputStr = stream_get_contents($pipes[1]);
            $errorStr = stream_get_contents($pipes[2]);
            
            if ($outputStr) {
                $output = explode("\n", trim($outputStr));
            }
            if ($errorStr) {
                $output = array_merge($output, explode("\n", trim($errorStr)));
            }
            
            fclose($pipes[1]);
            fclose($pipes[2]);
            
            // Get return code
            $returnCode = proc_close($process);
        } else {
            // Fallback to exec if proc_open fails
            exec($pushCommand, $output, $returnCode);
        }
        
        // Check if it's a network error that we should retry
        $outputStr = implode("\n", $output);
        $isNetworkError = (
            strpos($outputStr, 'getaddrinfo') !== false ||
            strpos($outputStr, 'unable to access') !== false ||
            strpos($outputStr, 'Connection timed out') !== false ||
            strpos($outputStr, 'Failed to connect') !== false ||
            strpos($outputStr, 'Name or service not known') !== false ||
            strpos($outputStr, 'Network is unreachable') !== false
        );
        
        if ($returnCode === 0) {
            // Success!
            break;
        } elseif ($isNetworkError && $attempt < $maxRetries) {
            // Network error - will retry
            $lastError = $outputStr;
            continue;
        } else {
            // Other error or max retries reached
            $lastError = $outputStr;
            break;
        }
    }
    
    // Use last error if we have one
    if (!empty($lastError) && empty($output)) {
        $output = explode("\n", $lastError);
    }
    
    if ($returnCode !== 0) {
        $errorMsg = implode("\n", $output);
        
        // Check if it's a network error
        $isNetworkError = (
            strpos($errorMsg, 'getaddrinfo') !== false ||
            strpos($errorMsg, 'unable to access') !== false ||
            strpos($errorMsg, 'Connection timed out') !== false
        );
        
        if ($isNetworkError) {
            $result['message'] = 'Network error: Unable to connect to GitHub. Please check your internet connection and try again.';
            $log->warning("  ⚠️  Push failed due to network error (tried {$maxRetries} times)");
        } else {
            $result['message'] = 'Failed to push: ' . $errorMsg;
            $log->error("  ✗ Push failed");
        }
        return $result;
    }
    
    if ($attempt > 1) {
        $log->info("  ✓ Pushed to origin/{$branch} (succeeded on attempt {$attempt})");
    } else {
        $log->info("  ✓ Pushed to origin/{$branch}");
    }
    $result['success'] = true;
    $result['files_pushed'] = $changedFiles;
    $result['message'] = "Pushed {$changedFiles} file(s)";
    
    return $result;
}

/**
 * Find Git executable path
 * Tries common locations and PATH
 */
function findGitExecutable() {
    // Common Git installation paths on Windows
    $commonPaths = [
        'C:\\Program Files\\Git\\cmd\\git.exe',
        'C:\\Program Files (x86)\\Git\\cmd\\git.exe',
        'C:\\Program Files\\Git\\bin\\git.exe',
        'C:\\Program Files (x86)\\Git\\bin\\git.exe',
    ];
    
    // Check common paths first
    foreach ($commonPaths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    
    // Try to find git in PATH
    $pathEnv = getenv('PATH');
    if ($pathEnv) {
        $paths = explode(PATH_SEPARATOR, $pathEnv);
        foreach ($paths as $path) {
            $gitPath = rtrim($path, '\\/') . DIRECTORY_SEPARATOR . 'git.exe';
            if (file_exists($gitPath)) {
                return $gitPath;
            }
        }
    }
    
    // Try exec with 'where' command (Windows)
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        exec('where git 2>nul', $whereOutput, $whereReturnCode);
        if ($whereReturnCode === 0 && !empty($whereOutput)) {
            $foundPath = trim($whereOutput[0]);
            if (file_exists($foundPath)) {
                return $foundPath;
            }
        }
    }
    
    // Last resort: try 'git' directly (might work if PATH is set correctly)
    exec('git --version 2>&1', $versionOutput, $versionReturnCode);
    if ($versionReturnCode === 0) {
        return 'git'; // Return just 'git' if it works
    }
    
    return null;
}

