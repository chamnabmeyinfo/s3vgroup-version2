# Quick Fix: Production Server Divergence Error

## 🔴 Your Error

```
Error: Diverging branches can't be fast-forwarded
fatal: Not possible to fast-forward, aborting.
```

**Repository:** `/home/s3vtgroup/public_html`  
**Website:** `www.s3vtgroup.com.kh`

## ✅ Quick Fix (Copy & Paste)

Run this command via **SSH** or **cPanel Terminal**:

```bash
cd /home/s3vtgroup/public_html && \
git fetch origin && \
git stash && \
git pull origin main --no-ff && \
git push origin main
```

This will:
1. ✅ Fetch latest from GitHub
2. ✅ Stash any local changes
3. ✅ Merge remote changes (fixes divergence)
4. ✅ Push the merged result

---

## 📋 Step-by-Step Solution

### Option 1: Merge (Recommended - Safest)

```bash
# 1. Navigate to production directory
cd /home/s3vtgroup/public_html

# 2. Fetch latest changes
git fetch origin

# 3. Stash any local changes (if any)
git stash save "Production changes before merge - $(date)"

# 4. Merge with no fast-forward (fixes divergence)
git pull origin main --no-ff -m "Merge remote changes from GitHub"

# 5. Push the merged result
git push origin main

# 6. If you stashed changes and need them back:
git stash pop
```

### Option 2: Use the Fix Script (Easiest)

I've already created `fix-production-divergence.sh` for you!

**To use it:**

1. **Make sure the script is on your server** (upload via cPanel File Manager or FTP)
2. **Make it executable:**
   ```bash
   chmod +x fix-production-divergence.sh
   ```
3. **Run it:**
   ```bash
   bash fix-production-divergence.sh
   ```

The script will automatically:
- ✅ Check Git status
- ✅ Stash any changes
- ✅ Merge remote changes
- ✅ Push the result
- ✅ Handle conflicts if any

---

## 🔧 Alternative Solutions

### Option 3: Rebase (Cleaner History)

```bash
cd /home/s3vtgroup/public_html
git fetch origin
git stash
git pull --rebase origin main
git push origin main
git stash pop
```

### Option 4: Reset to Match GitHub (⚠️ Use with Caution)

**WARNING:** This will **PERMANENTLY DELETE** any changes made directly on the server!

```bash
cd /home/s3vtgroup/public_html
git fetch origin
git reset --hard origin/main
```

**Only use this if:**
- You're 100% sure server changes are not needed
- You want the server to exactly match GitHub

---

## 🚀 One-Line Quick Fix

**For Production Server (Recommended):**

```bash
cd /home/s3vtgroup/public_html && git fetch origin && git stash && git pull origin main --no-ff -m "Merge from GitHub" && git push origin main
```

**Or use the script:**

```bash
bash fix-production-divergence.sh
```

---

## ⚠️ Why This Happens

**Root Cause:**
- Your production server has commits that GitHub doesn't have
- GitHub has commits that your server doesn't have
- Git can't fast-forward (one branch is not directly ahead of the other)

**Common Causes:**
1. Manual edits made directly on the server
2. Pulls from different sources
3. Force pushes or resets
4. Multiple people working on the same branch

---

## 💡 Prevention Tips

**Best Practice for Production:**

1. **Never edit files directly on production** - always work locally
2. **Always pull before making changes:**
   ```bash
   cd /home/s3vtgroup/public_html
   git pull origin main
   ```
3. **Use the pull script regularly:**
   ```bash
   bash pull-production.sh
   ```
4. **Workflow should be:**
   ```
   Local → Commit → Push to GitHub → Pull on Production
   ```

---

## 📊 Check Status Before Fixing

See what's happening:

```bash
cd /home/s3vtgroup/public_html

# Check current status
git status

# See commit history
git log --oneline --graph --all -10

# See what's different
git fetch origin
git log HEAD..origin/main  # Commits on GitHub not on server
git log origin/main..HEAD  # Commits on server not on GitHub
```

---

## 🆘 If You Get Merge Conflicts

If the merge shows conflicts:

1. **Check which files have conflicts:**
   ```bash
   git status
   ```

2. **Edit conflicted files** (look for `<<<<<<<`, `=======`, `>>>>>>>` markers)

3. **After resolving conflicts:**
   ```bash
   git add .
   git commit -m "Resolve merge conflicts"
   git push origin main
   ```

4. **Or discard server changes and use GitHub version:**
   ```bash
   git reset --hard origin/main
   ```

---

## ✅ Summary

**For Production Server:**

```bash
# Quick fix (recommended)
cd /home/s3vtgroup/public_html && \
git fetch origin && \
git stash && \
git pull origin main --no-ff && \
git push origin main

# Or use the script
bash fix-production-divergence.sh
```

**That's it!** Your production server will be in sync with GitHub. ✅

---

## 📝 Files Available

I've created these files to help you:

1. **`fix-production-divergence.sh`** - Automated fix script
2. **`pull-production.sh`** - Safe pull script for regular use
3. **`PRODUCTION-GIT-FIX.md`** - Detailed instructions
4. **`QUICK-FIX-PRODUCTION-DIVERGENCE.md`** - This quick reference

All scripts are ready to use! Just upload them to your server and run them.
