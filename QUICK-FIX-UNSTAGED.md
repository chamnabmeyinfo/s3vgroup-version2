# Quick Fix: Unstaged Changes Error

## 🔴 Your Error

```
error: cannot pull with rebase: You have unstaged changes.
error: Please commit or stash them.
```

**Repository:** `/home/s3vtgroup/dev.s3vtgroup.com.kh`

## ✅ Quick Fix (Copy & Paste)

Run this command via **SSH** or **cPanel Terminal**:

```bash
cd /home/s3vtgroup/dev.s3vtgroup.com.kh && \
git stash && \
git pull origin main --no-rebase && \
git stash pop
```

This will:
1. ✅ Stash your uncommitted changes
2. ✅ Pull latest code (using merge instead of rebase)
3. ✅ Reapply your stashed changes

---

## 📋 Step-by-Step Explanation

### Step 1: Navigate to Repository
```bash
cd /home/s3vtgroup/dev.s3vtgroup.com.kh
```

### Step 2: Stash Changes (Save Temporarily)
```bash
git stash
```
This saves your uncommitted changes temporarily.

### Step 3: Pull Latest Code
```bash
git pull origin main --no-rebase
```
The `--no-rebase` flag uses merge instead of rebase, which avoids this error.

### Step 4: Reapply Stashed Changes
```bash
git stash pop
```
This restores your stashed changes.

---

## 🔧 Alternative Solutions

### Option 1: Commit Changes First
If your changes are important:

```bash
cd /home/s3vtgroup/dev.s3vtgroup.com.kh
git add -A
git commit -m "Server changes before pull"
git pull origin main
```

### Option 2: Discard Changes
If changes are not needed (⚠️ **PERMANENTLY DELETES**):

```bash
cd /home/s3vtgroup/dev.s3vtgroup.com.kh
git reset --hard HEAD
git clean -fd
git pull origin main
```

### Option 3: Disable Rebase
To prevent this error in the future:

```bash
cd /home/s3vtgroup/dev.s3vtgroup.com.kh
git config pull.rebase false
git pull origin main
```

---

## 🚀 Using the Fix Script

I've updated `fix-server-git.sh` to automatically handle unstaged changes.

**To use it:**

1. **Upload to server** (if not already there)
2. **Make executable:**
   ```bash
   chmod +x fix-server-git.sh
   ```
3. **Run it:**
   ```bash
   bash fix-server-git.sh
   ```

The script will:
- ✅ Automatically stash unstaged changes
- ✅ Pull latest code
- ✅ Reapply stashed changes
- ✅ Handle conflicts if any

---

## ⚠️ Why This Happens

**Root Cause:**
- Git is configured to use **rebase** when pulling
- Rebase requires a clean working directory
- You have uncommitted changes

**Common Causes:**
1. Manual file edits on the server
2. Generated files (cache, logs) not in `.gitignore`
3. Config files with server-specific values
4. File permission changes

---

## 💡 Prevention

**Best Practice:** Servers should NOT have uncommitted changes.

**Workflow:**
```
Local Development → Commit → Push to GitHub → Pull on Server
```

**Never:**
- ❌ Edit files directly on the server
- ❌ Make changes without committing
- ❌ Leave uncommitted changes

**Always:**
- ✅ Work locally
- ✅ Commit and push
- ✅ Pull on server

---

## 📊 Check What Changed

Before fixing, see what files have changes:

```bash
cd /home/s3vtgroup/dev.s3vtgroup.com.kh

# See modified files
git status

# See detailed changes
git diff

# See untracked files
git status --untracked-files=all
```

---

## 🆘 Still Having Issues?

If the quick fix doesn't work:

1. **Check Git configuration:**
   ```bash
   git config pull.rebase
   # If "true", disable it:
   git config pull.rebase false
   ```

2. **Check for locked files:**
   ```bash
   find . -name ".git/index.lock"
   # If found, remove it:
   rm .git/index.lock
   ```

3. **Try manual merge:**
   ```bash
   git fetch origin
   git merge origin/main
   ```

---

## ✅ Summary

**Quick Command:**
```bash
cd /home/s3vtgroup/dev.s3vtgroup.com.kh && git stash && git pull origin main --no-rebase && git stash pop
```

**Or use the script:**
```bash
bash fix-server-git.sh
```

**That's it!** Your repository should now be updated. ✅
