# Fix Image Paths After Database Migration

## 🔍 Problem
After importing database from localhost to cPanel, image paths still reference `http://localhost:8080/storage/uploads/...` instead of using relative paths.

## ✅ Solution Options

### Option 1: Use Admin Script (Recommended)

1. **Login to Admin Panel:**
   - Go to: `https://s3vgroup.com/admin/login.php`

2. **Run Fix Script:**
   - Go to: `https://s3vgroup.com/admin/fix-image-paths.php`
   - Review the preview of changes
   - Click **"Fix Image Paths"** button
   - Done!

**This script will:**
- ✅ Fix all image paths in products table
- ✅ Fix gallery JSON fields
- ✅ Fix category images
- ✅ Fix variant images
- ✅ Fix settings table images
- ✅ Show preview before making changes
- ✅ Use transactions (safe rollback if error)

### Option 2: Run SQL Directly in phpMyAdmin

1. **Open phpMyAdmin in cPanel:**
   - Go to cPanel → phpMyAdmin
   - Select your database

2. **Run SQL:**
   - Click **"SQL"** tab
   - Copy and paste the SQL from `database/fix-image-paths.sql`
   - Click **"Go"**

**Note:** SQL method only fixes simple image fields, not JSON gallery fields.

### Option 3: Manual SQL Queries

Run these queries one by one in phpMyAdmin:

```sql
-- Fix products.image
UPDATE products 
SET image = REPLACE(REPLACE(image, 'http://localhost:8080/storage/uploads/', ''), 'storage/uploads/', '')
WHERE image LIKE '%localhost%';

-- Fix categories.image
UPDATE categories 
SET image = REPLACE(REPLACE(image, 'http://localhost:8080/storage/uploads/', ''), 'storage/uploads/', '')
WHERE image LIKE '%localhost%';

-- Fix product_variants.image (if exists)
UPDATE product_variants 
SET image = REPLACE(REPLACE(image, 'http://localhost:8080/storage/uploads/', ''), 'storage/uploads/', '')
WHERE image LIKE '%localhost%';
```

---

## 🔍 Verify It Worked

1. **Check a product page:**
   - Visit: `https://s3vgroup.com/product.php?slug=your-product`
   - Images should load correctly

2. **Check admin panel:**
   - Go to Products → Edit a product
   - Images should display correctly

3. **Check database:**
   ```sql
   SELECT id, name, image FROM products LIMIT 5;
   ```
   - Image field should contain only filename (e.g., `img_692b6e850a1386.91083690.png`)
   - Should NOT contain `http://localhost` or `storage/uploads/`

---

## 📝 What Gets Fixed

### Before (Wrong):
```
http://localhost:8080/storage/uploads/img_692b6e850a1386.91083690.png
storage/uploads/img_692b6e850a1386.91083690.png
http://localhost:8080/img_692b6e850a1386.91083690.png
```

### After (Correct):
```
img_692b6e850a1386.91083690.png
```

---

## ⚠️ Important Notes

1. **Image Files Must Be Uploaded:**
   - Make sure all image files are in `storage/uploads/` on cPanel
   - The script only fixes database paths, not the actual files

2. **Backup First:**
   - Always backup your database before running fixes
   - The admin script uses transactions (safe), but SQL queries don't

3. **Gallery Fields:**
   - JSON gallery fields require the PHP script
   - SQL queries won't fix JSON fields properly

---

## 🆘 Troubleshooting

### Issue: Images still not loading

**Check:**
1. Are image files in `storage/uploads/` folder on cPanel?
2. Are file permissions correct? (755 for folder, 644 for files)
3. Is `config/app.php` URL set correctly?
4. Check browser console for 404 errors

### Issue: Some images still show localhost

**Fix:**
- Run the fix script again
- Or manually update specific records:
  ```sql
  UPDATE products SET image = 'filename.png' WHERE id = 123;
  ```

---

## ✅ Quick Checklist

- [ ] Image files uploaded to `storage/uploads/` on cPanel
- [ ] Database paths fixed (using script or SQL)
- [ ] Test product page - images load
- [ ] Test admin panel - images display
- [ ] Verify no localhost URLs in database

---

**That's it! Your images should work correctly now! 🖼️**

