# cPanel Git Pull Fix Guide

## Problem
When pulling from Git on cPanel, you may encounter errors like:
```
error: Your local changes to the following files would be overwritten by merge
error: The following untracked working tree files would be overwritten by merge
```

## Solution

### Option 1: Use the Safe Pull Script (Recommended)
1. Upload `cpanel-safe-pull.php` to your cPanel root directory
2. Access it via browser: `https://yourdomain.com/cpanel-safe-pull.php?token=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`
3. Or run via SSH: `php cpanel-safe-pull.php`

The script will:
- Stash local changes
- Backup untracked files
- Pull latest changes
- Restore stashed changes

### Option 2: Manual Fix via SSH
```bash
cd /home/username/public_html
git stash
git clean -fd
git pull origin main
git stash pop
```

### Option 3: Commit Local Changes First
If you want to keep local changes:
```bash
cd /home/username/public_html
git add .
git commit -m "Local server changes"
git pull origin main
```

## Important Notes
- Always backup before pulling
- The safe pull script creates backups in `storage/backups/git-pull-*`
- Untracked files are backed up before removal
- Stashed changes are automatically restored after pull
