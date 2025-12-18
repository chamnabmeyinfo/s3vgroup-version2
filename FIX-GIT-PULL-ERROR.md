# Fix Git Pull Error on cPanel

## 🔍 Problem

When trying to pull from Git on cPanel, you get this error:

```
error: Your local changes to the following files would be overwritten by merge
error: The following untracked working tree files would be overwritten by merge
Please commit your changes or stash them before you merge.
```

## ✅ Solution: Clean Up and Pull

You have two options:

---

## Option 1: Force Pull (Recommended - Clean Start)

This will discard all local changes on cPanel and pull fresh code from GitHub.

### Step 1: Access cPanel Terminal (or use File Manager)

1. Login to **cPanel**
2. Go to **Terminal** (if available) OR use **File Manager**

### Step 2: Navigate to Your Directory

If your Git path is set to `public_html`:

```bash
cd ~/public_html
```

If it's still in `repositories/s3vgroup-version2`:

```bash
cd ~/repositories/s3vgroup-version2
```

### Step 3: Force Reset and Pull

**⚠️ WARNING: This will delete all local changes!**

```bash
# Discard all local changes
git reset --hard HEAD

# Remove untracked files (the conflicting ones)
git clean -fd

# Pull latest code
git pull origin main
```

---

## Option 2: Stash Changes and Pull (Keep Local Changes)

If you want to keep your local changes temporarily:

```bash
cd ~/public_html  # or your Git directory

# Stash local changes
git stash

# Remove conflicting untracked files
rm -f CACHE-FIX-GUIDE.md CLEANUP-AND-PUSH-GUIDE.md GIT-PULL-SAFETY.md
rm -f admin/api/products-load.php admin/clear-cache.php admin/forgot-password.php admin/reset-password.php
rm -f database/password-reset.sql

# Pull latest code
git pull origin main

# If you want your stashed changes back later:
# git stash pop
```

---

## Option 3: Use cPanel File Manager (No Terminal Needed)

If you don't have Terminal access:

### Step 1: Delete Conflicting Files

1. Go to **File Manager** in cPanel
2. Navigate to your Git directory (`public_html` or `repositories/s3vgroup-version2`)
3. Delete these conflicting files:
   - `CACHE-FIX-GUIDE.md`
   - `CLEANUP-AND-PUSH-GUIDE.md`
   - `GIT-PULL-SAFETY.md`
   - `admin/api/products-load.php` (if it exists and conflicts)
   - `admin/clear-cache.php` (if it exists and conflicts)
   - `admin/forgot-password.php` (if it exists and conflicts)
   - `admin/reset-password.php` (if it exists and conflicts)
   - `database/password-reset.sql` (if it exists and conflicts)

### Step 2: Try Pull Again

1. Go back to **Git™ Version Control**
2. Click **"Pull or Deploy"** on your repository
3. It should work now!

---

## Option 4: Create Safe Pull Script (Best for Future)

Create a file `cpanel-safe-pull.sh` in your repository:

```bash
#!/bin/bash
cd ~/public_html  # Change to your Git directory

# Backup current state (optional)
echo "Backing up current state..."
cp -r . ../backup-$(date +%Y%m%d-%H%M%S) 2>/dev/null || true

# Discard local changes
echo "Discarding local changes..."
git reset --hard HEAD

# Remove untracked files
echo "Cleaning untracked files..."
git clean -fd

# Pull latest code
echo "Pulling latest code..."
git pull origin main

echo "Done! Your code is now up to date."
```

**Upload this script to cPanel and run it via Terminal or Cron Job.**

---

## 🎯 Recommended Steps

1. **First, commit and push deletions from your local machine:**

   ```bash
   cd c:\xampp\htdocs\s3vgroup
   git add -A
   git commit -m "Clean up: Remove unnecessary .md files and old scripts"
   git push origin main
   ```

2. **Then on cPanel, use Option 1 (Force Pull):**
   - Access Terminal or File Manager
   - Run the force reset commands
   - Pull the code

---

## ⚠️ Important Notes

1. **Backup First:** Always backup your `public_html` before force pulling
2. **Config Files:** Make sure `config/database.php` and `config/app.php` are not overwritten (they should be in `.gitignore`)
3. **Uploads:** Files in `storage/uploads/` should be safe (they're in `.gitignore`)

---

## 🔧 After Fixing

Once the pull succeeds:

1. **Verify the path:** Make sure Git is pulling to `public_html` (not `repositories/`)
2. **Check your website:** Visit `https://s3vtgroup.com.kh/admin/products.php`
3. **You should see:** The new redesigned admin products page!

---

## 📝 Quick Checklist

- [ ] Backup `public_html` directory
- [ ] Access cPanel Terminal or File Manager
- [ ] Navigate to Git directory (`public_html` or `repositories/s3vgroup-version2`)
- [ ] Run force reset: `git reset --hard HEAD`
- [ ] Clean untracked: `git clean -fd`
- [ ] Pull code: `git pull origin main`
- [ ] Verify website shows updates
- [ ] (Optional) Fix Git path to `public_html` if still wrong

---

**Need Help?** If you're still having issues, check:

- File permissions (should be 644 for files, 755 for directories)
- Disk space (make sure you have enough)
- Git credentials (make sure they're correct in cPanel)
