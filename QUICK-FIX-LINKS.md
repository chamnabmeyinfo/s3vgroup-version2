# Quick Fix: Links Still Showing localhost:8080

## 🚨 Immediate Fix

After pulling from GitHub, you need to update the config file on your **cPanel server**:

### Via cPanel File Manager:

1. **Go to:** cPanel → File Manager
2. **Navigate to:** `public_html/config/`
3. **Edit:** `app.php`
4. **Change this line:**
   ```php
   'url' => 'http://localhost:8080',  // ❌ Wrong
   ```
   **To:**
   ```php
   'url' => 'https://s3vgroup.com',  // ✅ Correct
   ```
5. **Also change:**
   ```php
   'debug' => true,  // ❌ Wrong for production
   ```
   **To:**
   ```php
   'debug' => false,  // ✅ Correct for production
   ```
6. **Save the file**

---

## ✅ Complete File Should Look Like:

```php
<?php
return [
    'name' => 'S3VGROUP',
    'url' => 'https://s3vgroup.com',
    'timezone' => 'UTC',
    'debug' => false,
    'uploads_dir' => __DIR__ . '/../storage/uploads',
    'cache_dir' => __DIR__ . '/../storage/cache',
];
```

---

## 🔍 Why This Happens

Config files are **gitignored** (not in GitHub) to protect:
- Database passwords
- API keys  
- Production URLs

So when you pull from GitHub, config files stay the same on your server.

---

## ✅ After Fixing

1. **Clear browser cache** (Ctrl+F5)
2. **Visit:** `https://s3vgroup.com`
3. **Check:** All links should now use `https://s3vgroup.com`

---

**That's it! Takes 30 seconds! ⚡**

