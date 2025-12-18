# WHM/cPanel Server Setup Guide

## 🚨 Problem: PHP Files Downloading Instead of Executing

If your website prompts to download PHP files instead of displaying them, PHP is not properly configured on your WHM/cPanel server.

## ✅ Quick Fix Steps

### Step 1: Check PHP Version in cPanel

1. **Login to cPanel**
2. Navigate to **"Select PHP Version"** or **"MultiPHP Manager"**
3. Select **PHP 7.4 or higher** (recommended: **PHP 8.0** or **8.1**)
4. Click **"Set as current"** or **"Apply"**
5. Ensure **"php-fpm"** or **"suphp"** handler is selected

### Step 2: Verify .htaccess File

1. **Check that `.htaccess` exists** in your `public_html` directory
2. The `.htaccess` file should now include PHP handler configuration (already updated)
3. If `.htaccess` doesn't exist, create it with the content from the repository

### Step 3: Check Apache Handlers (if Step 1 doesn't work)

1. In cPanel, go to **"Apache Handlers"**
2. Add handler: `php` → `application/x-httpd-php`
3. Or add: `php5` → `application/x-httpd-php`
4. Save changes

### Step 4: Verify File Permissions

1. **PHP files** should have permissions: `644`
2. **Directories** should have permissions: `755`
3. **.htaccess** should have permissions: `644`

**To fix via cPanel File Manager:**
- Right-click file → Change Permissions → Set to 644

**To fix via SSH:**
```bash
cd ~/public_html
find . -type f -name "*.php" -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 644 .htaccess
```

### Step 5: Test PHP Configuration

1. Upload `check-php-config.php` to your `public_html` directory
2. Access it via browser: `https://yourdomain.com/check-php-config.php`
3. Check if PHP is working
4. **DELETE the file after checking** (security)

## 🔧 Advanced Troubleshooting

### If PHP Still Doesn't Work:

#### Option A: Check via SSH

```bash
# Check PHP version
php -v

# Check if PHP-FPM is running
systemctl status php-fpm

# Check Apache error logs
tail -f /usr/local/apache/logs/error_log
```

#### Option B: Create a Simple Test File

Create `test.php` in `public_html`:
```php
<?php
phpinfo();
?>
```

If this also downloads instead of executing, PHP handler is definitely not configured.

#### Option C: Check WHM Configuration

1. **Login to WHM** (root access required)
2. Go to **"MultiPHP Manager"**
3. Select your domain
4. Set PHP version to **PHP 8.0** or higher
5. Set handler to **"php-fpm"**

#### Option D: Contact Hosting Support

If none of the above works:
- Contact your hosting provider
- Ask them to:
  1. Verify PHP is installed
  2. Configure PHP handler for your domain
  3. Check Apache configuration for PHP module
  4. Verify `AllowOverride All` is set for your directory

## 📋 Required PHP Extensions

Ensure these PHP extensions are enabled in cPanel:

- ✅ **PDO** - Database connectivity
- ✅ **pdo_mysql** - MySQL support
- ✅ **GD** - Image processing
- ✅ **mbstring** - String functions
- ✅ **json** - JSON support
- ✅ **session** - Session management
- ✅ **curl** - HTTP requests
- ✅ **openssl** - Security

**To enable in cPanel:**
1. Go to **"Select PHP Version"**
2. Click **"Extensions"**
3. Enable all required extensions listed above

## 🔐 Security Checklist After Setup

- [ ] Delete `check-php-config.php` (if uploaded)
- [ ] Delete `test.php` (if created)
- [ ] Change default admin password
- [ ] Set proper file permissions (644 for files, 755 for directories)
- [ ] Enable SSL/HTTPS
- [ ] Update database credentials
- [ ] Review `.htaccess` security settings

## 📝 Database Setup

After PHP is working:

1. **Create Database in cPanel:**
   - Go to **"MySQL Databases"**
   - Create new database
   - Create database user
   - Add user to database with ALL PRIVILEGES

2. **Import Database:**
   - Go to **"phpMyAdmin"**
   - Select your database
   - Import `database/schema.sql`
   - Import other SQL files as needed

3. **Update Database Config:**
   - Edit `config/database.php`
   - Update with your database credentials

4. **Run Setup:**
   - Visit `https://yourdomain.com/setup.php`
   - Follow setup wizard

## 🌐 URL Configuration

After setup, update `config/app.php`:

```php
'url' => 'https://yourdomain.com',
```

## ✅ Verification

Your site should work when:
- ✅ PHP files execute (not download)
- ✅ `check-php-config.php` shows "PHP IS WORKING"
- ✅ Database connection successful
- ✅ All required extensions loaded
- ✅ File permissions correct

## 🆘 Still Having Issues?

1. Check Apache error logs: `/usr/local/apache/logs/error_log`
2. Check PHP error logs: Usually in cPanel → "Errors" section
3. Verify `.htaccess` syntax is correct
4. Test with a simple `<?php echo "Hello"; ?>` file
5. Contact hosting support with error logs

---

**Note:** The `.htaccess` file has been updated with PHP handler configurations that should work on most WHM/cPanel servers. If you still have issues, the problem is likely at the server level (PHP not installed, wrong handler, etc.).

