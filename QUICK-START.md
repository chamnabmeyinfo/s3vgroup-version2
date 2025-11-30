# 🚀 Quick Start Guide

Your website is **100% ready**! Follow these simple steps to get started:

## Step 1: Set Up Database (2 minutes)

### Option A: Automatic Setup (Recommended)
1. Open your browser
2. Go to: `http://localhost:8080/setup.php`
3. The script will automatically:
   - Create the database
   - Import all tables
   - Set up default admin user
   - Create sample categories

### Option B: Manual Setup
1. Open phpMyAdmin (`http://localhost/phpmyadmin`)
2. Create new database: `forklift_equipment`
3. Select the database
4. Click "Import" tab
5. Choose file: `database/schema.sql`
6. Click "Go"

## Step 2: Test Your Website (30 seconds)

1. Open: `http://localhost:8080`
   - You should see the beautiful homepage!

2. Test connection: `http://localhost:8080/test-connection.php`
   - Should show all green checkmarks ✓

## Step 3: Login to Admin Panel (1 minute)

1. Go to: `http://localhost:8080/admin/login.php`
2. Login with:
   - **Username:** `admin`
   - **Password:** `admin123`

⚠️ **IMPORTANT:** Change password immediately after first login!

## Step 4: Add Sample Products (Optional)

1. While logged into admin panel
2. Go to: `http://localhost:8080/admin/add-sample-products.php`
3. This will add 6 sample products for testing

## Step 5: Customize Your Site

### Add Your Products:
1. Admin Panel → Products → Add New Product
2. Fill in details
3. Upload images to `storage/uploads/` folder
4. Enter image filename in product form

### Add Categories:
1. Admin Panel → Categories → Add New Category
2. Default categories are already created!

### View Quote Requests:
1. Admin Panel → Quote Requests
2. View and manage customer inquiries

### Manage Messages:
1. Admin Panel → Messages
2. View contact form submissions

## ✅ What's Included:

### Frontend Pages:
- ✅ Homepage with hero section
- ✅ Product catalog with search & filters
- ✅ Product detail pages
- ✅ Contact form
- ✅ Quote request form
- ✅ Mobile responsive design

### Admin Panel:
- ✅ Dashboard with statistics
- ✅ Product management (add/edit/delete)
- ✅ Category management
- ✅ Quote request management
- ✅ Contact message management
- ✅ Featured products
- ✅ Stock status tracking

### Features:
- ✅ Fast & optimized
- ✅ SEO-friendly
- ✅ Secure admin area
- ✅ Modern design
- ✅ Mobile responsive
- ✅ Search functionality

## 🔧 Configuration Files:

- `config/database.php` - Database settings
- `config/app.php` - Application settings (URL, etc.)

## 📁 Important Folders:

- `storage/uploads/` - Upload product images here
- `storage/logs/` - Error logs
- `admin/` - Admin panel files

## 🎨 Customization Tips:

### Change Site Name:
Edit `config/app.php` and database `settings` table

### Change Colors:
Modify Tailwind classes in template files or edit `assets/css/style.css`

### Add Features:
Code is well-organized! Add new models in `app/Models/`

## 🆘 Troubleshooting:

### Database Connection Error?
- Check `config/database.php`
- Make sure MySQL is running in XAMPP
- Verify database exists

### Images Not Showing?
- Check file permissions on `storage/uploads/`
- Verify image filenames match exactly (case-sensitive)
- Make sure images are uploaded to `storage/uploads/`

### Admin Login Not Working?
- Verify database was imported correctly
- Default password is: `admin123`
- Check if admin_users table has data

### Page Not Found?
- Check URL in `config/app.php`
- Make sure Apache is running

## 🎯 Next Steps:

1. ✅ Run setup.php
2. ✅ Login to admin
3. ✅ Change admin password
4. ✅ Add your products
5. ✅ Upload product images
6. ✅ Customize content
7. ✅ Test all features
8. ✅ Delete setup.php (security)

## 📞 Need Help?

- Check `README.md` for detailed documentation
- Check `SETUP.md` for setup instructions
- Review code comments in files

---

**You're all set! 🎉** Your professional forklift & equipment website is ready to use!

