# Troubleshooting Guide

## "Not Found" Error - Solutions

### Solution 1: Access Files Directly with .php Extension

Try accessing files with the full `.php` extension:

- **Homepage:** `http://localhost:8080/s3vgroup/index.php`
- **Setup:** `http://localhost:8080/s3vgroup/setup.php`
- **Test PHP:** `http://localhost:8080/s3vgroup/hello.php`
- **Admin:** `http://localhost:8080/s3vgroup/admin/login.php`

### Solution 2: Check XAMPP Apache Configuration

1. Open **XAMPP Control Panel**
2. Make sure **Apache** is running (green status)
3. If Apache won't start, check the error logs

### Solution 3: Verify Directory Location

Your files should be in:
```
C:\xampp\htdocs\s3vgroup\
```

Verify this by checking if these files exist:
- `C:\xampp\htdocs\s3vgroup\index.php`
- `C:\xampp\htdocs\s3vgroup\hello.php`

### Solution 4: Check Apache Port

1. In XAMPP Control Panel, click **Config** next to Apache
2. Select **httpd.conf**
3. Look for: `Listen 8080`
4. If it says `Listen 80`, change it to `Listen 8080` and restart Apache

### Solution 5: Temporary Fix - Disable .htaccess

If `.htaccess` is causing issues:

1. Rename `.htaccess` to `.htaccess.backup`
2. Try accessing `http://localhost:8080/s3vgroup/index.php` again

### Solution 6: Database Connection Error

If you see a database error on index.php:

1. First run: `http://localhost:8080/s3vgroup/setup.php`
2. This will create the database automatically
3. Then try index.php again

### Solution 7: Test Step by Step

**Step 1:** Test if PHP works:
```
http://localhost:8080/s3vgroup/hello.php
```
You should see "Hello! PHP is Working!"

**Step 2:** Run database setup:
```
http://localhost:8080/s3vgroup/setup.php
```

**Step 3:** Access homepage:
```
http://localhost:8080/s3vgroup/index.php
```

## Common Issues

### Issue: "Access Denied" or "Forbidden"
- Check folder permissions
- Make sure files are readable

### Issue: "500 Internal Server Error"
- Check Apache error logs
- Look at `C:\xampp\apache\logs\error.log`

### Issue: "Database Connection Failed"
- Run `setup.php` first to create database
- Check database credentials in `config/database.php`

## Quick Checklist

- [ ] Apache is running in XAMPP Control Panel
- [ ] Using port 8080 in URL (`http://localhost:8080/...`)
- [ ] Files are in `C:\xampp\htdocs\s3vgroup\`
- [ ] PHP is working (test with hello.php)
- [ ] Database setup completed (run setup.php)
- [ ] Using full path with `.php` extension

## Still Having Issues?

1. Check Apache error logs: `C:\xampp\apache\logs\error.log`
2. Check PHP error logs: `C:\xampp\php\logs\php_error_log`
3. Try accessing: `http://localhost:8080/s3vgroup/hello.php`
4. Verify folder exists: `C:\xampp\htdocs\s3vgroup\`

