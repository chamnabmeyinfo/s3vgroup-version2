# Quick Fix: Git Pull Conflict in cPanel

## 🚀 Fastest Solution

### If you're using cPanel Terminal/SSH:

```bash
# 1. Go to your website directory
cd /home/username/public_html

# 2. Stash your changes (saves them temporarily)
git stash

# 3. Pull the latest code
git pull origin main

# 4. Reapply your changes (if needed)
git stash pop
```

## 📋 What Each Command Does

- `git stash` - Saves your local changes temporarily
- `git pull` - Downloads latest code from repository
- `git stash pop` - Reapplies your saved changes

## ⚠️ If You Need to Keep Your Server Changes

If your `.htaccess` has important server-specific rules:

```bash
# 1. Commit your changes first
git add .htaccess
git commit -m "Server-specific .htaccess configuration"

# 2. Then pull (may need to merge)
git pull origin main

# 3. If there's a merge conflict, resolve it manually
```

## 🔄 Alternative: Use .htaccess.local

**Better long-term solution:**

1. **Move server-specific rules to `.htaccess.local`** (already in .gitignore)
2. **Keep main `.htaccess`** in sync with Git
3. **Include local file** in main `.htaccess` if needed

This way, `.htaccess` stays in sync, and server-specific rules are separate.

## 🎯 One-Liner Fix

If you just want to discard local changes and use the Git version:

```bash
git checkout -- .htaccess && git pull origin main
```

**⚠️ Warning:** This will lose your local `.htaccess` changes!

---

**Choose the solution that fits your needs!**
