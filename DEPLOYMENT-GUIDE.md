# Deployment Guide: Localhost to cPanel

This guide will help you deploy your website from localhost to cPanel hosting.

## 📋 Pre-Deployment Checklist

- [ ] Code is pushed to GitHub
- [ ] Code is pulled to cPanel `public_html`
- [ ] Database export is ready
- [ ] cPanel database is created
- [ ] Configuration files are updated

---

## 🗄️ Step 1: Export Database from Localhost phpMyAdmin

### Option A: Export via phpMyAdmin (Recommended)

1. **Open phpMyAdmin:**
   - Go to `http://localhost:8080/phpmyadmin`
   - Login with your credentials

2. **Select Database:**
   - Click on `forklift_equipment` in the left sidebar

3. **Export:**
   - Click the **"Export"** tab at the top
   - Select **"Quick"** export method (or "Custom" for more options)
   - Choose format: **SQL**
   - Click **"Go"** button
   - Save the file (e.g., `forklift_equipment.sql`)

### Option B: Export via Command Line

```bash
# Navigate to your project directory
cd C:\xampp\htdocs\s3vgroup

# Export database
mysqldump -u root -p forklift_equipment > forklift_equipment.sql
```

**Important Notes:**
- ✅ Include all tables
- ✅ Include structure and data
- ✅ Use UTF-8 encoding
- ✅ File size should be reasonable (if > 50MB, use compression)

---

## 📤 Step 2: Import Database to cPanel

### Method 1: Via cPanel phpMyAdmin (Recommended)

1. **Access cPanel:**
   - Login to your cPanel account
   - Find **"phpMyAdmin"** in the **"Databases"** section
   - Click to open

2. **Select Database:**
   - Click on your database name in the left sidebar
   - (If database doesn't exist, create it first in cPanel → MySQL Databases)

3. **Import:**
   - Click the **"Import"** tab at the top
   - Click **"Choose File"** button
   - Select your `forklift_equipment.sql` file
   - Make sure **"SQL"** format is selected
   - Click **"Go"** button
   - Wait for import to complete (may take a few minutes for large databases)

### Method 2: Via cPanel File Manager

1. **Upload SQL File:**
   - Go to cPanel → **File Manager**
   - Navigate to `public_html` or root directory
   - Upload `forklift_equipment.sql` file

2. **Import via phpMyAdmin:**
   - Open phpMyAdmin in cPanel
   - Select your database
   - Go to **Import** tab
   - Select the uploaded file
   - Click **Go**

### Method 3: Via Command Line (SSH)

```bash
# Connect via SSH to your cPanel server
ssh username@yourdomain.com

# Navigate to your project
cd public_html

# Import database
mysql -u cpanel_username -p cpanel_database_name < forklift_equipment.sql
```

---

## ⚙️ Step 3: Update Configuration Files

### 3.1 Update Database Configuration

Edit `config/database.php` on your cPanel server:

```php
<?php
return [
    'host' => 'localhost',  // Usually 'localhost' on cPanel
    'dbname' => 'cpanel_username_database_name',  // Your cPanel database name
    'username' => 'cpanel_username_db_user',  // Your cPanel database user
    'password' => 'your_cpanel_db_password',  // Your cPanel database password
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
```

**How to find cPanel database credentials:**
1. Go to cPanel → **MySQL Databases**
2. You'll see:
   - **Database Name:** Usually `username_dbname`
   - **Database User:** Usually `username_dbuser`
   - **Password:** The one you set when creating the user

### 3.2 Update Application Configuration

Edit `config/app.php` on your cPanel server:

```php
<?php
return [
    'name' => 'Forklift & Equipment Pro',
    'url' => 'https://s3vgroup.com',  // ⚠️ CHANGE THIS to your domain
    'timezone' => 'UTC',
    'debug' => false,  // ⚠️ Set to false in production
    'uploads_dir' => __DIR__ . '/../storage/uploads',
    'cache_dir' => __DIR__ . '/../storage/cache',
];
```

**Important Changes:**
- ✅ Change `url` to your actual domain (e.g., `https://s3vgroup.com`)
- ✅ Set `debug` to `false` in production
- ✅ Use `https://` if you have SSL certificate

---

## 📁 Step 4: Upload Files to cPanel

### Via Git (Recommended - Already Done)

If you already pulled from GitHub:
```bash
# In cPanel terminal or via SSH
cd public_html
git pull origin main
```

### Via File Manager

1. **Go to cPanel → File Manager**
2. **Navigate to `public_html`**
3. **Upload all files** (or use Git as above)

---

## 🔒 Step 5: Set File Permissions

Set proper permissions for directories:

```bash
# Via SSH or cPanel Terminal
cd public_html

# Set permissions for storage directories
chmod 755 storage
chmod 755 storage/uploads
chmod 755 storage/cache
chmod 755 storage/logs
chmod 755 storage/backups

# Make sure uploads directory is writable
chmod 777 storage/uploads  # Or 755 if possible
```

**Via cPanel File Manager:**
1. Right-click on `storage` folder → **Change Permissions**
2. Set to `755` (or `777` for uploads if needed)
3. Apply to subdirectories

---

## ✅ Step 6: Post-Deployment Checks

### 6.1 Test Database Connection

1. Visit your website: `https://s3vgroup.com`
2. Check if pages load correctly
3. Try logging into admin panel: `https://s3vgroup.com/admin/login.php`

### 6.2 Verify File Paths

Check that these directories exist and are writable:
- `storage/uploads/` - For product images
- `storage/cache/` - For caching
- `storage/logs/` - For logs
- `storage/backups/` - For backups

### 6.3 Test Admin Login

1. Go to: `https://s3vgroup.com/admin/login.php`
2. Login with your admin credentials
3. Check if dashboard loads

### 6.4 Test Image Uploads

1. Go to Admin → Products → Edit a product
2. Try uploading an image
3. Verify it saves correctly

### 6.5 Check .htaccess

Make sure `.htaccess` file is in `public_html` root and contains:
```apache
RewriteEngine On
RewriteBase /
```

---

## 🔧 Common Issues & Solutions

### Issue 1: Database Connection Error

**Error:** `SQLSTATE[HY000] [2002] Connection refused`

**Solution:**
- Check database credentials in `config/database.php`
- Verify database user has proper permissions
- Make sure database name is correct (cPanel format: `username_dbname`)

### Issue 2: 500 Internal Server Error

**Solution:**
- Check file permissions (should be 755 for folders, 644 for files)
- Check `.htaccess` file exists
- Check PHP error logs in cPanel → Errors
- Verify PHP version (should be 7.4+)

### Issue 3: Images Not Loading

**Solution:**
- Check `storage/uploads/` directory permissions (should be 755 or 777)
- Verify image paths in database are correct
- Check if `storage/uploads/` folder exists

### Issue 4: Admin Panel Not Accessible

**Solution:**
- Verify `admin/` folder exists
- Check file permissions
- Try accessing directly: `https://s3vgroup.com/admin/login.php`

### Issue 5: URL Redirect Issues

**Solution:**
- Update `config/app.php` with correct domain
- Check `.htaccess` RewriteBase (should be `/` for root domain)
- Clear browser cache

---

## 🔐 Security Checklist

After deployment:

- [ ] Change admin password
- [ ] Set `debug => false` in `config/app.php`
- [ ] Verify file permissions (755 for folders, 644 for files)
- [ ] Check `.htaccess` is protecting sensitive files
- [ ] Enable SSL/HTTPS if available
- [ ] Remove any test/debug files
- [ ] Update admin email addresses

---

## 📝 Quick Reference

### Database Export (Localhost)
```
phpMyAdmin → Select Database → Export → SQL → Go
```

### Database Import (cPanel)
```
cPanel → phpMyAdmin → Select Database → Import → Choose File → Go
```

### Configuration Files to Update
1. `config/database.php` - Database credentials
2. `config/app.php` - Site URL and debug mode

### File Permissions
- Folders: `755`
- Files: `644`
- Uploads folder: `755` or `777` (if needed)

---

## 🆘 Need Help?

If you encounter issues:
1. Check cPanel error logs
2. Check PHP error logs
3. Verify database connection
4. Check file permissions
5. Review `.htaccess` file

---

**Good luck with your deployment! 🚀**

