# Complete GitHub Token Setup
# This script stores your token in Windows Credential Manager
# 
# Usage: .\setup-github-token-complete.ps1 [-Token "your-token"] [-Username "your-username"]

param(
    [Parameter(Mandatory = $false)]
    [string]$Token = "",
    
    [Parameter(Mandatory = $false)]
    [string]$Username = "chamnabmeyinfo"
)

# Get token if not provided as parameter
if ([string]::IsNullOrWhiteSpace($Token)) {
    Write-Host "GitHub Token Setup" -ForegroundColor Cyan
    Write-Host "==================" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Step 1: Get your GitHub Personal Access Token" -ForegroundColor Yellow
    Write-Host "  Go to: https://github.com/settings/tokens" -ForegroundColor White
    Write-Host "  Create token with 'repo' scope" -ForegroundColor White
    Write-Host ""
    $Token = Read-Host "Paste your token here" -AsSecureString
    $Token = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($Token))
}

if ([string]::IsNullOrWhiteSpace($Token)) {
    Write-Host "Error: Token is required!" -ForegroundColor Red
    exit 1
}

Write-Host "Configuring GitHub Token Authentication..." -ForegroundColor Cyan
Write-Host ""

# Method 1: Use Windows Credential Manager (if available)
Write-Host "Method 1: Configuring Windows Credential Manager..." -ForegroundColor Yellow
git config --global credential.helper manager-core

# Method 2: Store credentials using git credential fill/store
Write-Host "Storing credentials..." -ForegroundColor Yellow

# Create credential input
$credentialInput = @"
protocol=https
host=github.com
username=$username
password=$token
"@

# Store using git credential
$credentialInput | git credential approve

Write-Host "✓ Credentials configured" -ForegroundColor Green
Write-Host ""

# Test the connection
Write-Host "Testing connection..." -ForegroundColor Cyan
$testResult = git ls-remote origin 2>&1

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Connection successful!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Setup complete! You can now push/pull without authentication popups." -ForegroundColor Green
    Write-Host ""
    Write-Host "Test with: git push origin main" -ForegroundColor Yellow
}
else {
    Write-Host "✗ Connection test failed" -ForegroundColor Red
    Write-Host "Trying alternative method..." -ForegroundColor Yellow
    
    # Alternative: Update remote URL with token
    $newRemote = "https://$username`:$token@github.com/chamnabmeyinfo/s3vgroup-version2.git"
    git remote set-url origin $newRemote
    
    Write-Host "✓ Remote URL updated with token" -ForegroundColor Green
    Write-Host "Testing again..." -ForegroundColor Cyan
    
    $testResult2 = git ls-remote origin 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ Connection successful!" -ForegroundColor Green
        Write-Host ""
        Write-Host "Setup complete using embedded token method." -ForegroundColor Green
        Write-Host "Note: Token is stored in .git/config (local only, not committed)" -ForegroundColor Yellow
    }
    else {
        Write-Host "✗ Still failed. Error: $testResult2" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "Current remote URL:" -ForegroundColor Cyan
git remote get-url origin
