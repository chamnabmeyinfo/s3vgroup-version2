# Fix: Git Pull Conflict with storage/uploads/ Files

## 🔴 The Problem

When pulling from Git in cPanel, you get this error:

```
error: The following untracked working tree files would be overwritten by merge:
storage/uploads/AUTO-BARRIER-GATE___-4__2__6944c41c82d82.jpg
storage/uploads/Cable_6944b6d046428.jpg
...
Please move or remove them before you merge. Aborting
```

**What this means:**
- Your server has untracked files in `storage/uploads/`
- The Git repository has different files with the same names
- Git won't overwrite untracked files during merge

## ✅ Solution: Add and Commit the Files

Since `storage/uploads/` is tracked in Git (for asset version control), you need to add these files:

### In cPanel Terminal/SSH:

```bash
# 1. Navigate to your website directory
cd /home/username/public_html  # Replace with your actual path

# 2. Check what files are untracked
git status

# 3. Add all the upload files to Git
git add storage/uploads/

# 4. Commit them
git commit -m "Add uploaded images and assets from server"

# 5. Now pull (this will merge properly)
git pull origin main
```

### If There Are Conflicts After Pull:

If Git reports merge conflicts:

```bash
# See what files have conflicts
git status

# For each conflicted file, you can:
# Option 1: Keep your server version
git checkout --ours storage/uploads/filename.jpg

# Option 2: Use the remote version
git checkout --theirs storage/uploads/filename.jpg

# Option 3: Manually resolve (edit the file)

# After resolving conflicts:
git add storage/uploads/
git commit -m "Resolve upload conflicts"
```

## 🔄 Alternative: If You Want to Ignore Uploads

If you prefer NOT to track uploads in Git (to avoid this issue):

### Step 1: Update `.gitignore`

Add this line to `.gitignore`:

```gitignore
# Storage uploads (user-uploaded files)
storage/uploads/*
!storage/uploads/.gitkeep
!storage/uploads/.htaccess
```

### Step 2: Remove Uploads from Git Tracking

```bash
# Remove from Git (but keep files on disk)
git rm -r --cached storage/uploads/

# Commit the change
git commit -m "Stop tracking storage/uploads/ files"

# Push to remote
git push origin main
```

### Step 3: Then Pull on Server

```bash
# Now pull should work
git pull origin main
```

**Note:** This means uploads won't be version controlled, but it prevents merge conflicts.

## 🎯 Recommended Approach

**For this specific error, use Solution 1** (Add and Commit):
- Your `.gitignore` indicates uploads ARE tracked
- This keeps assets in version control
- Just add the files and commit them

## 🚨 Quick One-Liner Fix

If you just want to add all uploads and pull:

```bash
cd /home/username/public_html && git add storage/uploads/ && git commit -m "Add server uploads" && git pull origin main
```

## 📋 Step-by-Step: Using cPanel Git Interface

If you're using cPanel's Git Version Control:

1. **Go to Git Version Control** in cPanel
2. **Click on your repository**
3. **Check "Status"** - you'll see untracked files
4. **Add files:**
   - Select `storage/uploads/` directory
   - Click "Add to Git" or "Stage"
5. **Commit:**
   - Enter commit message: "Add uploaded images"
   - Click "Commit"
6. **Pull:**
   - Click "Pull" or "Update from Remote"
   - This should work now

## 💡 Prevention Tips

1. **Regular commits** - Commit uploads regularly to avoid large batches
2. **Consistent workflow** - Always add uploads after uploading files
3. **Consider Git LFS** - For large files, use Git Large File Storage
4. **Or ignore uploads** - If you don't need version control for uploads, ignore them

## 🔍 Why This Happens

- Files uploaded directly on server are untracked
- Remote repository has different files with same names
- Git protects untracked files from being overwritten
- Need to explicitly add them to Git first

---

**Choose the solution that fits your workflow!**
