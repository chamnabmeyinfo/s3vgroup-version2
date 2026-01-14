# GitHub Token Authentication Setup Script
# This script helps you configure Git to use a Personal Access Token

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "GitHub Token Authentication Setup" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if we're in a git repository
if (-not (Test-Path .git)) {
    Write-Host "Error: Not in a Git repository!" -ForegroundColor Red
    Write-Host "Please run this script from your project root directory." -ForegroundColor Yellow
    exit 1
}

# Get current remote URL
$currentRemote = git remote get-url origin
Write-Host "Current remote URL: $currentRemote" -ForegroundColor Yellow
Write-Host ""

# Check current credential helper
$credentialHelper = git config --global credential.helper
Write-Host "Current credential helper: $credentialHelper" -ForegroundColor Yellow
Write-Host ""

Write-Host "Step 1: Create a GitHub Personal Access Token" -ForegroundColor Green
Write-Host "------------------------------------------------" -ForegroundColor Green
Write-Host "1. Go to: https://github.com/settings/tokens" -ForegroundColor White
Write-Host "2. Click 'Generate new token' → 'Generate new token (classic)'" -ForegroundColor White
Write-Host "3. Name: S3VGroup Project Token" -ForegroundColor White
Write-Host "4. Expiration: Choose your preference" -ForegroundColor White
Write-Host "5. Scopes: Check 'repo' (Full control of private repositories)" -ForegroundColor White
Write-Host "6. Click 'Generate token'" -ForegroundColor White
Write-Host "7. COPY THE TOKEN - you won't see it again!" -ForegroundColor Red
Write-Host ""

$token = Read-Host "Paste your GitHub Personal Access Token here"

if ([string]::IsNullOrWhiteSpace($token)) {
    Write-Host "Error: Token cannot be empty!" -ForegroundColor Red
    exit 1
}

# Validate token format (GitHub tokens start with ghp_)
if (-not $token.StartsWith("ghp_")) {
    Write-Host "Warning: Token doesn't start with 'ghp_'. Make sure it's correct." -ForegroundColor Yellow
    $continue = Read-Host "Continue anyway? (y/n)"
    if ($continue -ne "y") {
        exit 1
    }
}

Write-Host ""
Write-Host "Step 2: Choose authentication method" -ForegroundColor Green
Write-Host "------------------------------------------------" -ForegroundColor Green
Write-Host "1. Windows Credential Manager (Recommended - Secure)" -ForegroundColor White
Write-Host "2. Embed token in remote URL (Quick but less secure)" -ForegroundColor White
Write-Host ""

$method = Read-Host "Choose method (1 or 2)"

if ($method -eq "1") {
    # Method 1: Use Windows Credential Manager
    Write-Host ""
    Write-Host "Configuring Windows Credential Manager..." -ForegroundColor Cyan
    
    # Set credential helper to manager-core (Windows Credential Manager)
    git config --global credential.helper manager-core
    
    Write-Host "✓ Credential helper set to manager-core" -ForegroundColor Green
    
    # Get GitHub username
    $username = git config user.name
    if ([string]::IsNullOrWhiteSpace($username)) {
        $username = Read-Host "Enter your GitHub username"
    } else {
        Write-Host "Using Git username: $username" -ForegroundColor Yellow
        $useThis = Read-Host "Use this username? (y/n)"
        if ($useThis -ne "y") {
            $username = Read-Host "Enter your GitHub username"
        }
    }
    
    # Store credentials using git credential fill/store
    $credentialUrl = "https://github.com"
    $credentialInput = "protocol=https`nhost=github.com`nusername=$username`npassword=$token`n"
    
    # Use git credential approve to store
    $credentialInput | git credential approve
    
    Write-Host "✓ Credentials stored in Windows Credential Manager" -ForegroundColor Green
    Write-Host ""
    Write-Host "Your credentials are now stored securely!" -ForegroundColor Green
    Write-Host "You can test with: git fetch origin" -ForegroundColor Yellow
    
} elseif ($method -eq "2") {
    # Method 2: Embed token in URL
    Write-Host ""
    Write-Host "Embedding token in remote URL..." -ForegroundColor Cyan
    
    # Extract username from current remote or ask
    if ($currentRemote -match "https://([^/@]+)@") {
        $username = $matches[1]
    } elseif ($currentRemote -match "github\.com/([^/]+)/") {
        $username = $matches[1]
    } else {
        $username = Read-Host "Enter your GitHub username"
    }
    
    # Update remote URL with token
    $newRemote = "https://$username`:$token@github.com/chamnabmeyinfo/s3vgroup-version2.git"
    git remote set-url origin $newRemote
    
    Write-Host "✓ Remote URL updated with token" -ForegroundColor Green
    Write-Host ""
    Write-Host "Warning: Token is stored in .git/config file" -ForegroundColor Yellow
    Write-Host "Make sure .git/config is in .gitignore or never commit it!" -ForegroundColor Yellow
    
} else {
    Write-Host "Invalid choice. Exiting." -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Step 3: Testing connection..." -ForegroundColor Green
Write-Host "------------------------------------------------" -ForegroundColor Green

# Test the connection
Write-Host "Testing Git connection..." -ForegroundColor Cyan
$testResult = git ls-remote origin 2>&1

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Connection successful!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Setup complete! You can now push/pull without popup authentication." -ForegroundColor Green
} else {
    Write-Host "✗ Connection failed!" -ForegroundColor Red
    Write-Host "Error: $testResult" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please check:" -ForegroundColor Yellow
    Write-Host "1. Token is correct and not expired" -ForegroundColor White
    Write-Host "2. Token has 'repo' scope enabled" -ForegroundColor White
    Write-Host "3. You have access to the repository" -ForegroundColor White
}

Write-Host ""
Write-Host "Current remote URL:" -ForegroundColor Cyan
git remote get-url origin

Write-Host ""
Write-Host "To test pushing:" -ForegroundColor Yellow
Write-Host "  git push origin main" -ForegroundColor White
