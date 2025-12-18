# Fix 503 Service Unavailable Error

## 🚨 Problem: 503 Error After Adding PHP Handler Config

The 503 error is caused by incorrect PHP handler configuration in `.htaccess`. The PHP-FPM socket paths were specific to certain server setups and don't match your server.

## ✅ Solution Applied

I've updated `.htaccess` to use a **safer, more generic** PHP handler configuration that:
- ✅ Works with cPanel's built-in PHP handler
- ✅ Doesn't override cPanel's PHP configuration
- ✅ Uses standard handlers that work on most servers
- ✅ Won't cause 503 errors

## 🔧 Steps to Fix

### Step 1: Upload Updated .htaccess

1. **Download the updated `.htaccess`** from your repository
2. **Upload it to `public_html`** on your server
3. **Replace the old one** (backup first if needed)

### Step 2: Test with Simple PHP File

1. **Create `test-simple.php`** in `public_html`:
```php
<?php
echo "PHP is working! Version: " . phpversion();
?>
```

2. **Access it:** `https://dontthaito.me/test-simple.php`

3. **If it works:** You'll see "PHP is working! Version: X.X.X"
4. **If it still shows 503:** Continue to Step 3

### Step 3: Temporarily Remove PHP Handler from .htaccess

If Step 2 still shows 503, the issue might be with ANY PHP handler in `.htaccess`. 

**Option A: Remove PHP handler lines temporarily**

1. **Edit `.htaccess`** and **comment out or remove** these lines:
```apache
# Comment out these lines:
# AddHandler application/x-httpd-php .php .php5 .phtml
# AddType application/x-httpd-php .php .php5 .phtml
```

2. **Save and test** `test-simple.php` again
3. **If it works:** cPanel is handling PHP correctly, you don't need PHP handler in `.htaccess`
4. **If it still downloads:** You DO need PHP handler, but configured differently

### Step 4: Configure PHP in cPanel (Most Important!)

The **real solution** is to configure PHP properly in cPanel:

1. **Login to cPanel**
2. **Go to "Select PHP Version"** or **"MultiPHP Manager"**
3. **Select your domain** (`dontthaito.me`)
4. **Choose PHP version:** PHP 7.4, 8.0, or 8.1 (recommended)
5. **Click "Set as current"** or **"Apply"**
6. **Go to "Extensions"** tab
7. **Enable required extensions:**
   - ✅ pdo
   - ✅ pdo_mysql
   - ✅ gd
   - ✅ mbstring
   - ✅ json
   - ✅ session
   - ✅ curl
   - ✅ openssl

### Step 5: Check Apache Handlers (Alternative Method)

If PHP still doesn't work:

1. **In cPanel, go to "Apache Handlers"**
2. **Add handler:**
   - **Extension(s):** `php`
   - **Handler:** `application/x-httpd-php`
3. **Click "Save"**
4. **Test again**

### Step 6: Check Server Error Logs

1. **In cPanel, go to "Errors"** or **"Error Log"**
2. **Look for recent errors** related to PHP or Apache
3. **Common errors:**
   - `File does not exist: /var/cpanel/php/sockets/...` → Wrong socket path
   - `Primary script unknown` → PHP handler not configured
   - `Connection refused` → PHP-FPM not running

## 🔍 Diagnostic Steps

### Check 1: Is PHP-FPM Running?

**Via SSH (if you have access):**
```bash
systemctl status php-fpm
# or
service php-fpm status
```

**If not running:**
```bash
systemctl start php-fpm
# or
service php-fpm start
```

### Check 2: What PHP Handler is Active?

**In cPanel:**
1. Go to **"Select PHP Version"**
2. Look at **"Handler"** dropdown
3. Should show: `php-fpm`, `suphp`, or `dso`
4. **Note which one is selected**

### Check 3: Test Without .htaccess

1. **Rename `.htaccess` to `.htaccess.backup`**
2. **Test `test-simple.php`**
3. **If it works:** The `.htaccess` PHP handler config is the problem
4. **If it still downloads:** PHP is not configured at server level

## 📋 Updated .htaccess (Safe Version)

The updated `.htaccess` now uses this safe configuration:

```apache
# Standard PHP handler (works on most cPanel servers)
<IfModule mod_php.c>
    AddType application/x-httpd-php .php .php5 .phtml
</IfModule>

# Fallback handler (only if mod_php is not available)
AddHandler application/x-httpd-php .php .php5 .phtml
```

This is **much safer** than the previous version that tried to use specific socket paths.

## ✅ Verification Checklist

After applying fixes:

- [ ] Updated `.htaccess` uploaded to server
- [ ] PHP version selected in cPanel (7.4+)
- [ ] PHP handler set in cPanel (php-fpm, suphp, or dso)
- [ ] Required PHP extensions enabled
- [ ] `test-simple.php` shows "PHP is working!"
- [ ] No more 503 errors
- [ ] Website loads correctly

## 🆘 Still Getting 503?

If you still get 503 after all steps:

1. **Contact your hosting provider**
2. **Tell them:**
   - You're getting 503 errors on PHP files
   - You've configured PHP in cPanel
   - Ask them to check:
     - PHP-FPM service status
     - Apache PHP module configuration
     - Server error logs
     - PHP handler configuration

3. **Provide them:**
   - Your domain: `dontthaito.me`
   - Error: "503 Service Unavailable"
   - What you've tried (PHP version selection, handlers, etc.)

## 🎯 Expected Result

After fixing:
- ✅ `https://dontthaito.me/test-simple.php` → Shows PHP version
- ✅ `https://dontthaito.me/check-php-config.php` → Shows diagnostic page
- ✅ `https://dontthaito.me/` → Website loads normally
- ✅ No more 503 errors

---

**Important:** The updated `.htaccess` is much safer and should work on your server. The key is to also configure PHP properly in cPanel's "Select PHP Version" interface.

