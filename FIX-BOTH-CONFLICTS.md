# Fix Both Git Conflicts: .htaccess + storage/uploads/

## 🔴 The Problem

You have **TWO** conflicts happening at once:
1. `.htaccess` has local changes that would be overwritten
2. `storage/uploads/` has untracked files that would be overwritten

## ✅ Complete Fix (All-in-One Solution)

### Option 1: Use the Fix Script (Easiest)

I've created a script that fixes both issues automatically:

```bash
# 1. Upload fix-git-pull-conflicts.sh to your server
# 2. Make it executable
chmod +x fix-git-pull-conflicts.sh

# 3. Run it
cd /home/s3vtgroup/public_html
./fix-git-pull-conflicts.sh
```

### Option 2: Manual Fix (Step-by-Step)

Run these commands in cPanel Terminal/SSH:

```bash
# 1. Go to your website directory
cd /home/s3vtgroup/public_html

# 2. Stash .htaccess changes (saves them temporarily)
git stash push -m "Stash .htaccess changes" .htaccess

# 3. Add all upload files to Git
git add storage/uploads/

# 4. Commit the upload files
git commit -m "Add uploaded images and assets from server"

# 5. Pull the latest code
git pull origin main

# 6. Reapply .htaccess changes (if needed)
git stash pop
```

## 📋 What Each Step Does

1. **`git stash push .htaccess`** - Saves your `.htaccess` changes temporarily
2. **`git add storage/uploads/`** - Adds all upload files to Git tracking
3. **`git commit`** - Commits the upload files so they're tracked
4. **`git pull`** - Downloads latest code (now safe because conflicts are resolved)
5. **`git stash pop`** - Reapplies your `.htaccess` changes

## ⚠️ If You Get Merge Conflicts After Pull

If Git reports conflicts when reapplying `.htaccess`:

```bash
# See what conflicts exist
git status

# Option 1: Keep your server version
git checkout --ours .htaccess
git add .htaccess
git commit -m "Keep server .htaccess version"

# Option 2: Use the remote version
git checkout --theirs .htaccess
git add .htaccess
git commit -m "Use remote .htaccess version"

# Option 3: Manually edit .htaccess to combine both
# Then:
git add .htaccess
git commit -m "Merge .htaccess changes"
```

## 🎯 Quick One-Liner (Discard Local Changes)

If you want to discard local changes and use Git versions:

```bash
cd /home/s3vtgroup/public_html && \
git checkout -- .htaccess && \
git add storage/uploads/ && \
git commit -m "Add uploads" && \
git pull origin main
```

**⚠️ Warning:** This will lose your local `.htaccess` changes!

## 🔄 Long-Term Solutions

### For .htaccess Conflicts:

Use `.htaccess.local` for server-specific rules:

1. Create `.htaccess.local` (already in `.gitignore`)
2. Move server-specific rules there
3. Include it in main `.htaccess`:

```apache
# At end of .htaccess
<IfModule mod_include.c>
    IncludeOptional .htaccess.local
</IfModule>
```

### For storage/uploads/ Conflicts:

**Option A: Keep Tracking Uploads** (Current Setup)
- Always commit uploads after uploading files
- Use the fix script above when conflicts occur

**Option B: Stop Tracking Uploads** (Prevent Conflicts)

1. Update `.gitignore`:
   ```gitignore
   storage/uploads/*
   !storage/uploads/.gitkeep
   !storage/uploads/.htaccess
   ```

2. Remove from Git:
   ```bash
   git rm -r --cached storage/uploads/
   git commit -m "Stop tracking storage/uploads/ files"
   git push origin main
   ```

3. Future pulls won't conflict with uploads

## 📝 Recommended Workflow

To prevent these conflicts:

```bash
# After uploading files on server:
git add storage/uploads/
git commit -m "Add new uploads"

# Before pulling:
git status  # Check for changes
git stash   # Stash any .htaccess changes
git pull origin main
git stash pop  # Reapply if needed
```

## 🚨 Emergency: Can't Access Terminal?

If you can't use SSH:

1. **Use cPanel File Manager:**
   - Backup `.htaccess` and `storage/uploads/`
   - Download them to your local machine
   - Resolve conflicts locally
   - Re-upload

2. **Use cPanel Git Interface:**
   - Go to **Git Version Control**
   - Use **"Stash Changes"** for `.htaccess`
   - Add `storage/uploads/` files manually
   - Commit and pull

---

**The script handles everything automatically! Just run it and you're done!**
