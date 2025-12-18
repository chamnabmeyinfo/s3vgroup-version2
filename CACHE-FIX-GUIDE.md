# Cache Fix Guide - Why Changes Don't Appear After Git Pull

## 🔍 Problem
You pulled the code from Git, but when you refresh the page, you don't see the changes. This is a **caching issue**.

## ✅ Solutions (Try in Order)

### Solution 1: Clear Browser Cache (Easiest)
1. **Hard Refresh:**
   - Windows: Press `Ctrl + Shift + R` or `Ctrl + F5`
   - Mac: Press `Cmd + Shift + R`
   - This forces browser to reload everything

2. **Or Use Incognito/Private Window:**
   - Open browser in incognito/private mode
   - Visit: `https://s3vtgroup.com.kh/admin/products.php`
   - This bypasses all browser cache

### Solution 2: Use Cache Clearing Utility
1. **Visit the cache clearing page:**
   ```
   https://s3vtgroup.com.kh/admin/clear-cache.php
   ```

2. **Click "Clear All Cache" button**
   - This clears OPcache, APCu, and file cache
   - Shows file modification time to verify update

3. **Then refresh products page**

### Solution 3: Add Version Parameter to URL
Add `?v=timestamp` to force reload:
```
https://s3vtgroup.com.kh/admin/products.php?v=<?= time() ?>
```

Or manually:
```
https://s3vtgroup.com.kh/admin/products.php?v=1234567890
```

### Solution 4: Clear Server-Side Cache (cPanel)
If you have access to cPanel:

1. **Clear OPcache:**
   - Go to cPanel → Select PHP Version
   - Or use terminal: `php -r "opcache_reset();"`
   - Or restart PHP-FPM

2. **Check File Modification Time:**
   - In cPanel File Manager, check `admin/products.php`
   - Right-click → Properties
   - Verify "Last Modified" matches when you pulled from Git

### Solution 5: Verify Files Were Actually Updated
1. **Check file modification time:**
   - Visit: `https://s3vtgroup.com.kh/admin/clear-cache.php`
   - Look at "Last Modified" time
   - It should match when you pulled from Git

2. **Check file content:**
   - In cPanel File Manager, open `admin/products.php`
   - Search for: `Modern Header with Glassmorphism Effect`
   - If you find it, file is updated (cache issue)
   - If not found, Git pull didn't work properly

### Solution 6: Force Git Pull Again
Sometimes Git pull doesn't complete properly:

1. **In cPanel Git Version Control:**
   - Click "Pull or Deploy"
   - Make sure you're pulling from `main` branch
   - Check for any error messages

2. **Or use SSH (if available):**
   ```bash
   cd /path/to/your/site
   git pull origin main
   git status
   ```

## 🎯 Quick Test
To verify if it's a cache issue:

1. **Add a test change:**
   - Edit `admin/products.php` directly on server
   - Add a comment: `<!-- TEST CACHE BUST <?= time() ?> -->`
   - Save and refresh page
   - If you see the comment, file is loading (cache is the issue)
   - If you don't see it, file wasn't updated (Git pull issue)

## 📋 Checklist
- [ ] Hard refreshed browser (Ctrl+Shift+R)
- [ ] Tried incognito/private window
- [ ] Visited `admin/clear-cache.php` and cleared cache
- [ ] Checked file modification time matches Git pull time
- [ ] Verified file content has new code (search for "Glassmorphism")
- [ ] Cleared server-side cache (OPcache)
- [ ] Added `?v=timestamp` to URL

## 🚨 If Still Not Working

### Check These:
1. **Git Pull Status:**
   - Did Git pull show any errors?
   - Are you pulling from the correct branch (`main`)?

2. **File Permissions:**
   - Files should be readable (644)
   - Check if PHP can read the files

3. **Multiple Environments:**
   - Are you checking the right server?
   - Local vs Production confusion?

4. **CDN/Proxy Cache:**
   - If using Cloudflare or similar, clear their cache
   - Check if there's a reverse proxy caching

## 💡 Prevention
The code now includes:
- ✅ Aggressive cache-busting headers
- ✅ Meta tags to prevent browser caching
- ✅ Cache clearing utility
- ✅ `.htaccess` rules to prevent PHP caching

After pulling, always:
1. Clear cache using `admin/clear-cache.php`
2. Hard refresh browser (Ctrl+Shift+R)
3. Check file modification time

---

**Most Common Solution:** Hard refresh (Ctrl+Shift+R) + Clear cache utility
