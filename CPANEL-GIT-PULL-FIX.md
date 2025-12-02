# Fix cPanel Git Pull Error

## Problem
When pulling from Git to cPanel, you get this error:
```
error: Your local changes to the following files would be overwritten by merge
error: The following untracked working tree files would be overwritten by merge
```

## Solution

### Option 1: Use the Safe Pull Script (Recommended)

1. **Upload `cpanel-safe-pull.php` to your cPanel root directory**

2. **Run it via SSH:**
   ```bash
   cd /home/username/public_html
   php cpanel-safe-pull.php
   ```

3. **Or run it via browser:**
   - Visit: `https://yourdomain.com/cpanel-safe-pull.php?token=YOUR_SECRET_TOKEN`
   - **Important:** Change `YOUR_SECRET_TOKEN_HERE` in the script to a secure token first!

### Option 2: Manual Fix via cPanel SSH

1. **SSH into your cPanel server**

2. **Navigate to your website root:**
   ```bash
   cd ~/public_html
   ```

3. **Stash local changes:**
   ```bash
   /usr/local/cpanel/3rdparty/bin/git stash push -m "Backup before pull"
   ```

4. **Remove untracked files that would conflict:**
   ```bash
   /usr/local/cpanel/3rdparty/bin/git clean -fd
   ```

5. **Pull the latest changes:**
   ```bash
   /usr/local/cpanel/3rdparty/bin/git pull origin main
   ```

6. **Restore stashed changes (if needed):**
   ```bash
   /usr/local/cpanel/3rdparty/bin/git stash pop
   ```

### Option 3: Reset and Pull (⚠️ WARNING: Loses Local Changes)

**Only use this if you don't need the local changes on cPanel!**

```bash
cd ~/public_html
/usr/local/cpanel/3rdparty/bin/git reset --hard origin/main
/usr/local/cpanel/3rdparty/bin/git clean -fd
/usr/local/cpanel/3rdparty/bin/git pull origin main
```

## Prevention: Always Push from Local First

**Before pulling on cPanel, make sure all changes are pushed from local:**

1. **On your local machine:**
   ```bash
   git add -A
   git commit -m "Your commit message"
   git push origin main
   ```

2. **Then pull on cPanel** (using one of the methods above)

## Files That Need to Be Committed

Based on the error, these files need to be committed and pushed:

### New Files (Untracked):
- `admin/hero-slider-edit.php`
- `admin/hero-sliders.php`
- `api/load-more-products.php`
- `app/Services/SmartProductImporter.php`
- `assets/js/lazy-load.js`
- `database/create-hero-sliders-table.sql`
- `includes/hero-slider.php`
- `setup-hero-sliders-direct.php`
- `setup-hero-sliders.php`
- `cpanel-safe-pull.php` (the fix script)

### Modified Files:
- `admin/includes/header.php`
- `app/Support/functions.php`
- `assets/css/style.css`
- `includes/hero-slider.php`
- And others...

## Quick Fix Command (Local Machine)

Run this on your local machine to commit and push everything:

```bash
git add -A
git commit -m "Add hero slider system and smart importer"
git push origin main
```

Then use Option 1 or 2 on cPanel to pull safely.

