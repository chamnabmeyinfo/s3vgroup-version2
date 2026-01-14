# Simple GitHub Token Setup - Quick Version
# This script configures Git to use Windows Credential Manager with your token

param(
    [Parameter(Mandatory = $false)]
    [string]$Token = "",
    
    [Parameter(Mandatory = $false)]
    [string]$Username = "chamnabmeyinfo"
)

Write-Host "GitHub Token Setup (Simple)" -ForegroundColor Cyan
Write-Host "============================" -ForegroundColor Cyan
Write-Host ""

# Get token if not provided
if ([string]::IsNullOrWhiteSpace($Token)) {
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

# Configure credential helper
Write-Host ""
Write-Host "Configuring Git..." -ForegroundColor Cyan
git config --global credential.helper manager-core
Write-Host "✓ Credential helper configured" -ForegroundColor Green

# Store credentials
Write-Host "Storing credentials..." -ForegroundColor Cyan
$credentialInput = "protocol=https`nhost=github.com`nusername=$Username`npassword=$Token`n"
$credentialInput | git credential approve

Write-Host "✓ Credentials stored securely in Windows Credential Manager" -ForegroundColor Green
Write-Host ""

# Test connection
Write-Host "Testing connection..." -ForegroundColor Cyan
$test = git ls-remote origin 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Connection successful!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Setup complete! You can now push/pull without authentication popups." -ForegroundColor Green
}
else {
    Write-Host "✗ Connection test failed" -ForegroundColor Red
    Write-Host "Please verify your token is correct and has 'repo' scope" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Try: git push origin main" -ForegroundColor Yellow
