# Upload Images to cPanel - Complete Guide

## 🔍 Why Images Aren't in GitHub

Images are in `.gitignore` because:
- ✅ They're large files (slow to push/pull)
- ✅ They're user-generated content
- ✅ They change frequently
- ✅ They don't need version control

**This is correct!** Images should NOT be in GitHub.

---

## 📤 How to Upload Images to cPanel

### Option 1: Upload via cPanel File Manager (Recommended)

1. **On Localhost:**
   - Go to: `C:\xampp\htdocs\s3vgroup\storage\uploads\`
   - Select all image files
   - Create a ZIP file: `uploads.zip`

2. **On cPanel:**
   - Go to: cPanel → File Manager
   - Navigate to: `public_html/storage/`
   - Upload `uploads.zip`
   - Right-click ZIP → Extract
   - Delete the ZIP file

3. **Set Permissions:**
   - Right-click `uploads` folder → Change Permissions → `755`
   - Select all files in `uploads` → Change Permissions → `644`

### Option 2: Upload via FTP

1. **Use FTP Client** (FileZilla, WinSCP, etc.)
2. **Connect to your cPanel server**
3. **Navigate to:** `public_html/storage/uploads/`
4. **Upload all files** from `C:\xampp\htdocs\s3vgroup\storage\uploads\`

### Option 3: Upload via SSH (Terminal)

```bash
# Connect via SSH
ssh username@yourdomain.com

# Navigate to project
cd public_html

# Create uploads directory if needed
mkdir -p storage/uploads

# Upload files (from your local machine)
# Use SCP or SFTP to transfer files
scp -r C:\xampp\htdocs\s3vgroup\storage\uploads\* username@yourdomain.com:public_html/storage/uploads/
```

### Option 4: Re-upload via Admin Panel

1. **Go to:** `https://s3vgroup.com/admin/products.php`
2. **Edit each product**
3. **Go to Images & Gallery tab**
4. **Upload images** using the uploader
5. **Save product**

**Note:** This works but is slow if you have many products.

---

## 🚀 Quick Steps (Recommended)

### Step 1: Create ZIP on Localhost

1. **Open:** `C:\xampp\htdocs\s3vgroup\storage\uploads\`
2. **Select all files** (Ctrl+A)
3. **Right-click → Send to → Compressed (zipped) folder**
4. **Name it:** `product-images.zip`

### Step 2: Upload to cPanel

1. **Login to cPanel**
2. **Go to File Manager**
3. **Navigate to:** `public_html/storage/`
4. **Click Upload**
5. **Select `product-images.zip`**
6. **Wait for upload to complete**

### Step 3: Extract

1. **Right-click `product-images.zip`**
2. **Click Extract**
3. **Extract to:** `storage/uploads/`
4. **Delete the ZIP file**

### Step 4: Set Permissions

1. **Right-click `uploads` folder**
2. **Change Permissions → `755`**
3. **Select all files in `uploads`**
4. **Change Permissions → `644`**

### Step 5: Verify

1. **Visit:** `https://s3vgroup.com/admin/check-images.php`
2. **Check if files are detected**
3. **Test a product page**

---

## 📋 Checklist

- [ ] All image files uploaded to `public_html/storage/uploads/`
- [ ] `uploads` folder has 755 permissions
- [ ] Image files have 644 permissions
- [ ] Database image paths are just filenames (no localhost)
- [ ] Test product page - images load correctly

---

## 🔍 Verify Images Are Uploaded

### Via cPanel File Manager:
1. Go to: `public_html/storage/uploads/`
2. You should see all your image files
3. Count should match your localhost folder

### Via Browser:
1. Try accessing: `https://s3vgroup.com/storage/uploads/your-image-name.png`
2. If 404 → File doesn't exist
3. If 403 → Permission issue
4. If loads → File exists and is accessible

---

## ⚠️ Important Notes

1. **Keep images out of Git:**
   - ✅ This is correct behavior
   - ✅ Images should be uploaded separately
   - ✅ Don't remove from `.gitignore`

2. **Backup images:**
   - Keep a backup of your `storage/uploads/` folder
   - Store it separately (not in Git)

3. **Future uploads:**
   - New images uploaded via admin panel will go to cPanel
   - They won't be in GitHub (this is fine)

---

## 🆘 Troubleshooting

### Issue: Can't upload ZIP (too large)

**Solution:**
- Split into smaller ZIPs (100MB each)
- Or use FTP instead
- Or increase upload limit in cPanel

### Issue: Files uploaded but not showing

**Check:**
1. File permissions (should be 644)
2. Folder permissions (should be 755)
3. Database paths (should be just filename)
4. Clear browser cache

### Issue: Some images missing

**Solution:**
- Check which products have missing images
- Re-upload those specific images via admin panel

---

## 💡 Pro Tip: Automated Sync

For future updates, you can:
1. **Keep localhost as backup**
2. **Upload new images manually** when needed
3. **Or set up automated sync** (advanced)

---

**That's it! Upload your images and they'll work! 🖼️**

