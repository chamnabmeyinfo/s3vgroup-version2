# Fix cPanel Git Path - Move from repositories/ to public_html

## 🔍 Problem

Your cPanel Git Version Control is pulling code to:

- ❌ **Wrong:** `repositories/s3vgroup-version2`
- ✅ **Correct:** `public_html` (or `public_html/s3vgroup` if subdirectory)

This means your code is being pulled to the wrong location and not being served by the web server!

## ✅ Solution 1: Change Git Path in cPanel (Recommended)

### Step 1: Access Git Version Control

1. Login to **cPanel**
2. Go to **Git™ Version Control** (under "Files" section)
3. Find your repository: `s3vgroup-version2`

### Step 2: Edit Repository Settings

1. Click **"Manage"** or **"Edit"** next to your repository
2. Look for **"Repository Path"** or **"Directory"** field
3. Change from:
   ```
   repositories/s3vgroup-version2
   ```
   To:
   ```
   public_html
   ```
   Or if you want it in a subdirectory:
   ```
   public_html/s3vgroup
   ```

### Step 3: Save and Pull

1. Click **"Update"** or **"Save"**
2. Click **"Pull or Deploy"** to pull the latest code
3. Your code should now be in `public_html` and visible on your website!

---

## ✅ Solution 2: Auto-Deploy Script (If You Want to Keep Repository Separate)

If you prefer to keep the repository in `repositories/s3vgroup-version2` and auto-deploy to `public_html`, create a post-receive hook:

### Create Deployment Script

**File:** `repositories/s3vgroup-version2/.git/hooks/post-receive`

```bash
#!/bin/bash
REPO_DIR="/home/your_username/repositories/s3vgroup-version2"
WEB_DIR="/home/your_username/public_html"

# Go to repository directory
cd $REPO_DIR

# Pull latest changes
git --git-dir=$REPO_DIR/.git --work-tree=$REPO_DIR pull origin main

# Copy files to public_html (excluding .git, node_modules, etc.)
rsync -av --exclude='.git' \
          --exclude='node_modules' \
          --exclude='vendor' \
          --exclude='*.log' \
          --exclude='.env' \
          --exclude='storage/cache/*' \
          $REPO_DIR/ $WEB_DIR/

echo "Deployment completed!"
```

**Make it executable:**

```bash
chmod +x repositories/s3vgroup-version2/.git/hooks/post-receive
```

---

## 🎯 Recommended: Use Solution 1

**Why?**

- Simpler setup
- Direct deployment
- No extra scripts needed
- Standard cPanel workflow

**Steps:**

1. Change Git path in cPanel from `repositories/s3vgroup-version2` to `public_html`
2. Pull the code
3. Done! Your website will update automatically on each pull.

---

## ⚠️ Important Notes

1. **Backup First:** Before changing paths, backup your current `public_html` directory
2. **Check Domain:** Make sure your domain points to `public_html` (usually it does by default)
3. **Permissions:** After pulling, ensure file permissions are correct (usually 644 for files, 755 for directories)

---

## 🔧 After Fixing the Path

Once you've changed the path to `public_html`:

1. **Pull the code** in cPanel Git Version Control
2. **Check your website** - changes should appear immediately
3. **Verify:** Visit `https://s3vtgroup.com.kh/admin/products.php` - you should see the new design!

---

## 📝 Quick Checklist

- [ ] Login to cPanel
- [ ] Go to Git™ Version Control
- [ ] Edit repository `s3vgroup-version2`
- [ ] Change path from `repositories/s3vgroup-version2` to `public_html`
- [ ] Save changes
- [ ] Pull latest code
- [ ] Verify website shows updates

---

**Need Help?** If you're not sure which path to use, check with your hosting provider or use `public_html` (standard web root).
