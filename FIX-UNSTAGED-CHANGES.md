# Fix Git Unstaged Changes Error

## 🔴 Problem

**Error Message:**
```
error: cannot pull with rebase: You have unstaged changes.
error: Please commit or stash them.
```

**Repository:** `/home/s3vtgroup/dev.s3vtgroup.com.kh`

## 🔍 What This Means

You're trying to pull code from GitHub, but:
1. Git is configured to use **rebase** when pulling
2. You have **uncommitted changes** in your working directory
3. Git won't allow pulling with rebase when there are unstaged changes (to prevent conflicts)

## ✅ Solutions

### Option 1: Stash Changes (Recommended for Server)

**Best for:** When you have uncommitted changes that you might need later

```bash
cd /home/s3vtgroup/dev.s3vtgroup.com.kh

# Stash your changes
git stash push -m "Stashed before pull - $(date)"

# Pull latest code
git pull origin main

# Reapply stashed changes (if needed)
git stash pop
```

### Option 2: Commit Changes

**Best for:** When changes are important and should be saved

```bash
cd /home/s3vtgroup/dev.s3vtgroup.com.kh

# Add all changes
git add -A

# Commit changes
git commit -m "Server changes before pull"

# Pull latest code
git pull origin main
```

### Option 3: Discard Changes

**Best for:** When changes are not needed (⚠️ PERMANENTLY DELETES changes)

```bash
cd /home/s3vtgroup/dev.s3vtgroup.com.kh

# Discard all uncommitted changes
git reset --hard HEAD

# Remove untracked files
git clean -fd

# Pull latest code
git pull origin main
```

### Option 4: Use Merge Instead of Rebase

**Best for:** When you want to keep changes but avoid rebase

```bash
cd /home/s3vtgroup/dev.s3vtgroup.com.kh

# Pull with merge instead of rebase
git pull origin main --no-rebase

# Or configure Git to always use merge
git config pull.rebase false
git pull origin main
```

## 🚀 Quick Fix Script

I've created a script `fix-unstaged-changes.sh` that automates this process.

**To use it:**

1. **Upload the script to your server:**
   ```bash
   # On your local machine, upload the script
   scp fix-unstaged-changes.sh user@your-server:/home/s3vtgroup/
   ```

2. **Make it executable:**
   ```bash
   chmod +x /home/s3vtgroup/fix-unstaged-changes.sh
   ```

3. **Run it:**
   ```bash
   /home/s3vtgroup/fix-unstaged-changes.sh
   ```

The script will:
- Show you what files have changes
- Give you options (stash, commit, discard, or auto)
- Automatically stash, pull, and reapply changes (in auto mode)

## 📋 Manual Step-by-Step (cPanel File Manager)

If you prefer to use cPanel's File Manager:

1. **Check what files have changes:**
   ```bash
   cd /home/s3vtgroup/dev.s3vtgroup.com.kh
   git status
   ```

2. **Stash changes:**
   ```bash
   git stash push -m "Before pull"
   ```

3. **Pull code:**
   ```bash
   git pull origin main
   ```

4. **Reapply stash (if needed):**
   ```bash
   git stash pop
   ```

## ⚠️ Important Notes

### Why This Happens on Servers

**Best Practice:** Servers should NOT have uncommitted changes. All changes should be:
1. Made locally
2. Committed locally
3. Pushed to GitHub
4. Pulled on the server

### Common Causes of Unstaged Changes on Server

1. **Manual file edits** on the server (should be avoided)
2. **File permissions** changed (should use `.gitignore`)
3. **Generated files** (cache, logs) - should be in `.gitignore`
4. **Config files** with server-specific values (should use `.example` files)

### Prevent This in the Future

1. **Never edit files directly on the server** - always edit locally and push
2. **Use `.gitignore`** for generated files (cache, logs, etc.)
3. **Use config examples** - copy `config/database.php.example` to `config/database.php` on server (not in Git)
4. **Set up proper workflow:**
   ```
   Local → Commit → Push to GitHub → Pull on Server
   ```

## 🔧 Check Your Git Configuration

```bash
cd /home/s3vtgroup/dev.s3vtgroup.com.kh

# Check if rebase is enabled
git config pull.rebase

# If it returns "true", you can disable it:
git config pull.rebase false

# Or use merge strategy:
git config pull.ff only  # Only fast-forward (safest)
```

## 📊 See What Changed

Before fixing, you can see what files have changes:

```bash
cd /home/s3vtgroup/dev.s3vtgroup.com.kh

# See modified files
git status

# See detailed changes
git diff

# See untracked files
git status --untracked-files=all
```

## ✅ Recommended Workflow

**For Development Server (`dev.s3vtgroup.com.kh`):**

```bash
# 1. Stash any local changes
git stash

# 2. Pull latest code
git pull origin main

# 3. If stash had important changes, reapply
git stash pop
```

**For Production Server (`public_html`):**

```bash
# 1. Always discard local changes (server should match GitHub)
git reset --hard HEAD
git clean -fd

# 2. Pull latest code
git pull origin main
```

## 🆘 Still Having Issues?

If you're still getting errors:

1. **Check for file permissions:**
   ```bash
   ls -la /home/s3vtgroup/dev.s3vtgroup.com.kh
   ```

2. **Check Git configuration:**
   ```bash
   git config --list | grep pull
   ```

3. **Try pulling with specific strategy:**
   ```bash
   git pull origin main --no-rebase --no-ff
   ```

4. **Check for locked files:**
   ```bash
   find . -name "*.lock" -o -name ".git/index.lock"
   ```

---

**Quick Command Summary:**

```bash
# Quick fix (stash, pull, reapply)
cd /home/s3vtgroup/dev.s3vtgroup.com.kh && \
git stash && \
git pull origin main && \
git stash pop
```
