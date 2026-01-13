# How to Pull All Code from cPanel to Local

## Quick Start

### Step 1: Setup FTP Configuration

1. Copy the example config:
   ```bash
   cp deploy-config.example.json deploy-config.json
   ```

2. Edit `deploy-config.json` with your cPanel FTP credentials:
   ```json
   {
     "ftp": {
       "enabled": true,
       "host": "ftp.s3vgroup.com",
       "username": "your_ftp_username",
       "password": "your_ftp_password",
       "port": 21,
       "remote_path": "/public_html"
     }
   }
   ```

### Step 2: Pull All Files

```bash
# Pull all files from cPanel
php artisan pull

# Or preview first (dry run)
php artisan pull:dry-run
```

## What Gets Pulled

✅ **All PHP files** from your website  
✅ **All CSS/JS files**  
✅ **All images and assets**  
✅ **All configuration files** (with options to preserve local ones)  
✅ **All directories and subdirectories**  

## What Gets Excluded

❌ `*.log` files  
❌ `*.cache` files  
❌ `.git` directory  
❌ `node_modules`  
❌ `vendor` (Composer packages)  
❌ `storage/cache/*`  
❌ `storage/logs/*`  
❌ `storage/backups/*`  
❌ Local config files (if preserve_config is enabled)  

## Options

### Dry Run (Preview)
See what would be pulled without actually downloading:
```bash
php artisan pull:dry-run
```

### No Backup
Skip creating local backup before pulling:
```bash
php artisan pull --no-backup
```

### Overwrite Config Files
Allow overwriting local config files:
```bash
php artisan pull --overwrite-config
```

## Complete Example

```bash
# 1. Preview what will be pulled
php artisan pull:dry-run

# 2. Pull all files (with backup)
php artisan pull

# 3. Check the results
# All files from cPanel are now in your local directory!
```

## How It Works

1. **Connects to FTP** using credentials from `deploy-config.json`
2. **Creates local backup** (optional) before pulling
3. **Recursively downloads** all files and directories
4. **Preserves local config** files (optional) to keep your local settings
5. **Excludes** unnecessary files (logs, cache, etc.)

## File Structure After Pull

```
your-project/
├── admin/              ← Pulled from cPanel
├── api/                ← Pulled from cPanel
├── app/                ← Pulled from cPanel
├── assets/             ← Pulled from cPanel
├── config/             ← Pulled (but local configs preserved)
├── includes/            ← Pulled from cPanel
├── storage/            ← Pulled from cPanel
│   ├── uploads/        ← All images from cPanel
│   └── ...
└── *.php               ← All PHP files from cPanel
```

## Troubleshooting

### "deploy-config.json not found"
```bash
cp deploy-config.example.json deploy-config.json
# Then edit it with your FTP credentials
```

### "FTP connection failed"
- Check your FTP host, username, and password
- Verify FTP port (usually 21)
- Check if your firewall allows FTP connections
- Try using SFTP if available

### "Permission denied"
- Make sure you have write permissions in your local directory
- Check FTP user permissions on remote server

### "Some files not pulled"
- Check the exclude patterns in `deploy-config.json`
- Files matching exclude patterns won't be downloaded
- This is intentional to avoid pulling unnecessary files

## Advanced Usage

### Custom Exclude Patterns

Edit `deploy-config.json`:
```json
{
  "exclude": [
    "*.log",
    "*.cache",
    ".git",
    "custom-pattern/*"
  ]
}
```

### Pull Specific Directory Only

You can modify the `FileSyncService` to pull only specific directories, or use FTP client for selective downloads.

## Safety Features

✅ **Local backup** created before pulling  
✅ **Config preservation** - won't overwrite your local database config  
✅ **Dry run mode** - preview before pulling  
✅ **Smart exclusions** - skips unnecessary files  
✅ **Error handling** - stops on critical errors  

## After Pulling

1. **Review changes** - Check what was updated
2. **Test locally** - Make sure everything works
3. **Commit to Git** - Save your synced code
4. **Update dependencies** - Run `composer install` if needed

## Workflow Example

```bash
# 1. Setup (one time)
cp deploy-config.example.json deploy-config.json
# Edit deploy-config.json with FTP credentials

# 2. Preview pull
php artisan pull:dry-run

# 3. Pull all files
php artisan pull

# 4. Check what changed
git status

# 5. Test locally
php artisan serve

# 6. Commit changes
git add .
git commit -m "Synced code from cPanel"
```

---

**Ready to pull?** Run `php artisan pull` to sync all code from cPanel to local! 🚀
