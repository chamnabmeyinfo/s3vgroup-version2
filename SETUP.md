# Quick Setup Guide

## Step 1: Database Setup

1. Open phpMyAdmin or MySQL command line
2. Create database:
```sql
CREATE DATABASE forklift_equipment CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. Import schema:
   - Open `database/schema.sql` in phpMyAdmin
   - Select the `forklift_equipment` database
   - Click "Import" and select the file
   - Or run: `mysql -u root -p forklift_equipment < database/schema.sql`

## Step 2: Configure Database

Edit `config/database.php` and update:
```php
'host' => 'localhost',
'dbname' => 'forklift_equipment',
'username' => 'root',
'password' => 'your_password_here',
```

## Step 3: Configure Site URL

Edit `config/app.php` and update:
```php
'url' => 'http://localhost:8080',
```

## Step 4: Test Installation

1. Open browser: `http://localhost:8080`
2. You should see the homepage

## Step 5: Access Admin Panel

1. Go to: `http://localhost:8080/admin/login.php`
2. Login with:
   - Username: `admin`
   - Password: `admin123`

⚠️ **CHANGE THE PASSWORD IMMEDIATELY!**

## Step 6: Upload Product Images

1. Upload product images to `storage/uploads/` folder
2. In admin panel, when adding products, enter the filename (e.g., `forklift-001.jpg`)

## Troubleshooting

### Database Connection Error
- Check database credentials in `config/database.php`
- Verify database exists and user has permissions

### Page Not Found
- Check if `config/app.php` has correct URL
- Ensure Apache mod_rewrite is enabled

### Images Not Showing
- Check file permissions on `storage/uploads/`
- Verify image filenames match exactly (case-sensitive)

### Admin Login Not Working
- Check database was imported correctly
- Verify admin_users table has data
- Default password is: `admin123`

## Next Steps

1. Change admin password
2. Add your products through admin panel
3. Customize site settings
4. Upload product images
5. Test all features

Enjoy your new website! 🚀

