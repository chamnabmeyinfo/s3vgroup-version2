# PowerShell script to configure Apache Virtual Host for S3VGroup
# This allows accessing the site at http://localhost:8080/ instead of http://localhost:8080/s3vgroup/

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "S3VGroup Virtual Host Setup" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$vhostsFile = "C:\xampp\apache\conf\extra\httpd-vhosts.conf"
$projectPath = "C:\xampp\htdocs\s3vgroup"

# Check if vhosts file exists
if (-not (Test-Path $vhostsFile)) {
    Write-Host "ERROR: Virtual hosts file not found at: $vhostsFile" -ForegroundColor Red
    Write-Host "Please make sure XAMPP is installed correctly." -ForegroundColor Yellow
    exit 1
}

# Check if project directory exists
if (-not (Test-Path $projectPath)) {
    Write-Host "ERROR: Project directory not found at: $projectPath" -ForegroundColor Red
    exit 1
}

Write-Host "Found virtual hosts file: $vhostsFile" -ForegroundColor Green
Write-Host "Project path: $projectPath" -ForegroundColor Green
Write-Host ""

# Read current vhosts file
$vhostsContent = Get-Content $vhostsFile -Raw

# Check if our virtual host already exists
if ($vhostsContent -match "s3vgroup|DocumentRoot.*s3vgroup") {
    Write-Host "WARNING: Virtual host configuration for s3vgroup may already exist." -ForegroundColor Yellow
    Write-Host ""
    $overwrite = Read-Host "Do you want to add/update it anyway? (y/n)"
    if ($overwrite -ne "y" -and $overwrite -ne "Y") {
        Write-Host "Cancelled." -ForegroundColor Yellow
        exit 0
    }
}

# Virtual host configuration
$vhostConfig = @"

# S3VGroup Virtual Host - Added by setup script
<VirtualHost *:8080>
    DocumentRoot "$projectPath"
    ServerName localhost
    <Directory "$projectPath">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>

"@

# Backup original file
$backupFile = "$vhostsFile.backup.$(Get-Date -Format 'yyyyMMdd-HHmmss')"
Copy-Item $vhostsFile $backupFile
Write-Host "Backup created: $backupFile" -ForegroundColor Green

# Append virtual host configuration
Add-Content -Path $vhostsFile -Value $vhostConfig
Write-Host "Virtual host configuration added!" -ForegroundColor Green
Write-Host ""

# Check if httpd.conf includes vhosts
$httpdConf = "C:\xampp\apache\conf\httpd.conf"
if (Test-Path $httpdConf) {
    $httpdContent = Get-Content $httpdConf -Raw
    if ($httpdContent -notmatch "httpd-vhosts.conf") {
        Write-Host "WARNING: Virtual hosts may not be enabled in httpd.conf" -ForegroundColor Yellow
        Write-Host "Please check that this line exists in httpd.conf:" -ForegroundColor Yellow
        Write-Host "  Include conf/extra/httpd-vhosts.conf" -ForegroundColor Cyan
        Write-Host ""
    }
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Setup Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Open XAMPP Control Panel" -ForegroundColor White
Write-Host "2. Stop Apache (if running)" -ForegroundColor White
Write-Host "3. Start Apache again" -ForegroundColor White
Write-Host "4. Visit: http://localhost:8080/" -ForegroundColor White
Write-Host ""
Write-Host "If you encounter issues:" -ForegroundColor Yellow
Write-Host "- Check Apache error logs in: C:\xampp\apache\logs\error.log" -ForegroundColor White
Write-Host "- Make sure port 8080 is configured in httpd.conf" -ForegroundColor White
Write-Host "- Restore backup if needed: $backupFile" -ForegroundColor White
Write-Host ""

