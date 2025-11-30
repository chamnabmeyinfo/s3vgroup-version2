# Fix Under Construction Mode on cPanel

## 🔍 Issue
Under Construction mode is not working after deployment to cPanel.

## ✅ Quick Fix

### Option 1: Enable via Admin Panel (Recommended)

1. **Login to Admin Panel:**
   - Go to: `https://s3vgroup.com/admin/login.php`
   - Login with your admin credentials

2. **Access Under Construction Control:**
   - Look for **"Under Construction"** in the admin menu
   - Or go directly to: `https://s3vgroup.com/admin/under-construction.php`

3. **Enable It:**
   - Click **"Enable Under Construction Mode"** button
   - The mode will be enabled immediately

### Option 2: Manual File Upload

If the admin panel doesn't work, manually create/update the config file:

1. **Via cPanel File Manager:**
   - Go to cPanel → File Manager
   - Navigate to `public_html/config/`
   - Create or edit `under-construction.php`

2. **File Content:**
   ```php
   <?php
   return array (
     'enabled' => true,  // ⚠️ Set to true to enable
     'message' => 'Website is under construction',
     'progress' => 85,
     'contact_email' => 'info@s3vgroup.com',
     'contact_phone' => '+1 (234) 567-890',
   );
   ```

3. **Save the file**

### Option 3: Via SSH/Terminal

```bash
# Connect via SSH
ssh username@yourdomain.com

# Navigate to project
cd public_html/config

# Create/edit the file
nano under-construction.php
```

Paste this content:
```php
<?php
return array (
  'enabled' => true,
  'message' => 'Website is under construction',
  'progress' => 85,
  'contact_email' => 'info@s3vgroup.com',
  'contact_phone' => '+1 (234) 567-890',
);
```

Save and exit (Ctrl+X, then Y, then Enter)

---

## 🔍 Verify Files Exist

Make sure these files exist on your cPanel server:

1. ✅ `config/under-construction.php` - Configuration file
2. ✅ `under-construction.php` - The actual construction page
3. ✅ `app/Helpers/UnderConstruction.php` - Helper class
4. ✅ `admin/under-construction.php` - Admin control panel

---

## 🧪 Test It

1. **Logout from admin** (or use incognito/private window)
2. **Visit your website:** `https://s3vgroup.com`
3. **You should see:** The "Under Construction" page
4. **Admin should still work:** `https://s3vgroup.com/admin/` should be accessible

---

## 🛠️ Troubleshooting

### Issue: Still seeing normal website

**Check:**
- Is `config/under-construction.php` file present?
- Does it have `'enabled' => true`?
- Check file permissions (should be 644)
- Clear browser cache

### Issue: Admin panel also shows construction page

**Fix:**
- The admin panel should bypass construction mode
- Check that you're accessing `/admin/` path
- Verify `app/Helpers/UnderConstruction.php` exists

### Issue: Config file not found

**Fix:**
- Create the file manually in `config/under-construction.php`
- Use the content provided above
- Set proper permissions (644)

---

## 📝 Quick Reference

**Enable:** Set `'enabled' => true` in `config/under-construction.php`  
**Disable:** Set `'enabled' => false` in `config/under-construction.php`  
**Admin Control:** `https://s3vgroup.com/admin/under-construction.php`

---

**That's it! Your Under Construction mode should work now! 🚧**

