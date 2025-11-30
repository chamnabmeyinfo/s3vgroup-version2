# Fix: Images Not Loading on Products

## 🔍 Common Causes

1. **Image files not uploaded to cPanel**
2. **Wrong file permissions**
3. **Database still has localhost URLs** (not fixed yet)
4. **storage/uploads folder doesn't exist**
5. **.htaccess blocking access**

---

## ✅ Step-by-Step Fix

### Step 1: Check if Images Exist in Database

1. **Open phpMyAdmin in cPanel**
2. **Run this query:**
   ```sql
   SELECT id, name, image FROM products LIMIT 5;
   ```
3. **Check the `image` column:**
   - ✅ Should be: `img_692b6e850a1386.91083690.png` (just filename)
   - ❌ Wrong: `http://localhost:8080/storage/uploads/img_692b6e850a1386.91083690.png`

**If you see localhost URLs:**
- Run the fix script: `admin/fix-image-paths.php`
- Or run SQL: `database/fix-image-paths.sql`

### Step 2: Check if Image Files Exist on Server

1. **Via cPanel File Manager:**
   - Go to: `public_html/storage/uploads/`
   - Check if image files exist there
   - Example: `img_692b6e850a1386.91083690.png`

**If files don't exist:**
- You need to upload them from localhost
- Or re-upload via admin panel

### Step 3: Check File Permissions

1. **Via cPanel File Manager:**
   - Right-click on `storage` folder → **Change Permissions**
   - Set to: `755`
   - Apply to subdirectories

2. **Or via SSH:**
   ```bash
   chmod 755 storage
   chmod 755 storage/uploads
   chmod 644 storage/uploads/*
   ```

### Step 4: Verify Folder Structure

Make sure these folders exist:
```
public_html/
  └── storage/
      └── uploads/
          └── (your image files here)
```

**If folders don't exist:**
- Create them via File Manager
- Set permissions to 755

### Step 5: Check .htaccess

Make sure `.htaccess` in `public_html` doesn't block image access.

**Check if this exists in .htaccess:**
```apache
# Allow images
<FilesMatch "\.(jpg|jpeg|png|gif|webp)$">
    Order allow,deny
    Allow from all
</FilesMatch>
```

---

## 🚀 Quick Fix Checklist

- [ ] Database image paths are just filenames (no localhost)
- [ ] Image files exist in `storage/uploads/` on cPanel
- [ ] `storage/uploads/` folder has 755 permissions
- [ ] Image files have 644 permissions
- [ ] `.htaccess` allows image access
- [ ] `config/app.php` has correct URL

---

## 📤 Upload Images from Localhost

If images are missing on cPanel:

### Option 1: Upload via FTP/cPanel File Manager

1. **On localhost:** Copy all files from `storage/uploads/`
2. **On cPanel:** Upload to `public_html/storage/uploads/`

### Option 2: Create a ZIP and Upload

1. **On localhost:** ZIP the `storage/uploads/` folder
2. **On cPanel:** Upload ZIP and extract

### Option 3: Use Admin Panel

1. Go to: `https://s3vgroup.com/admin/products.php`
2. Edit each product
3. Re-upload images via the image uploader

---

## 🔍 Test Image URL

1. **Check a product image URL:**
   - Should be: `https://s3vgroup.com/storage/uploads/img_692b6e850a1386.91083690.png`
   - Open this URL directly in browser
   - If 404 → file doesn't exist
   - If 403 → permission issue
   - If loads → URL is correct, check why page doesn't show it

---

## 🆘 Still Not Working?

### Check Browser Console:
1. Open browser DevTools (F12)
2. Go to **Console** tab
3. Look for 404 errors on image URLs
4. This tells you exactly what's wrong

### Check Server Error Logs:
1. Go to cPanel → **Errors** or **Error Log**
2. Look for PHP errors related to images

---

## ✅ Most Common Fix

**90% of the time, it's one of these:**

1. **Images not uploaded** → Upload files to `storage/uploads/`
2. **Database has localhost URLs** → Run `fix-image-paths.php`
3. **Wrong permissions** → Set folder to 755, files to 644

---

**Try these steps and let me know which one fixes it! 🖼️**

