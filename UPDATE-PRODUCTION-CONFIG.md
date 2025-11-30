# Update Production Config After Git Pull

## 🔍 Problem
After pulling from GitHub, links still show `localhost:8080` because config files are gitignored and need to be updated manually on the server.

## ✅ Quick Fix

### Step 1: Update config/app.php on cPanel

1. **Via cPanel File Manager:**
   - Go to cPanel → File Manager
   - Navigate to `public_html/config/`
   - Edit `app.php`

2. **Update the file content:**
   ```php
   <?php
   return [
       'name' => 'S3VGROUP',
       'url' => 'https://s3vgroup.com',  // ⚠️ Make sure this is your live domain
       'timezone' => 'UTC',
       'debug' => false,  // ⚠️ Set to false in production
       'uploads_dir' => __DIR__ . '/../storage/uploads',
       'cache_dir' => __DIR__ . '/../storage/cache',
   ];
   ```

3. **Save the file**

### Step 2: Update config/database.php (if needed)

Make sure your database config is correct:
```php
<?php
return [
    'host' => 'localhost',
    'dbname' => 'your_cpanel_database_name',
    'username' => 'your_cpanel_database_user',
    'password' => 'your_cpanel_database_password',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
```

### Step 3: Clear Cache (if any)

If you have caching enabled, clear it:
- Delete files in `storage/cache/` folder
- Or restart PHP if using OPcache

---

## 🔍 Verify It Worked

1. **Visit your website:** `https://s3vgroup.com`
2. **Check page source:** Right-click → View Source
3. **Search for "localhost":** Should find none (except in comments)
4. **Check links:** All links should use `https://s3vgroup.com`

---

## 📝 Why This Happens

Config files are in `.gitignore` to protect sensitive data:
- Database passwords
- API keys
- Production URLs

So when you pull from GitHub, config files are NOT updated automatically.

---

## 💡 Pro Tip: Create a Config Template

You can create `config/app.php.production` in your repo with production settings, then copy it on the server:

```bash
# On cPanel server
cp config/app.php.production config/app.php
```

---

## 🆘 Still Not Working?

1. **Check file permissions:** Should be 644
2. **Check file path:** Make sure you edited the right file
3. **Clear browser cache:** Hard refresh (Ctrl+F5)
4. **Check PHP errors:** Look in cPanel error logs

---

**That's it! Your links should now use the correct domain! 🚀**

