# Under Construction Feature - Setup Guide

## Overview

The Under Construction feature allows you to show a professional "Coming Soon" page to public visitors while keeping your admin panel and API accessible for development work.

## Features

✅ **Professional Design** - Modern, responsive under construction page  
✅ **Email Notifications** - Visitors can subscribe to be notified when site launches  
✅ **Admin Control** - Easy toggle in admin panel  
✅ **Admin Access** - Admin panel remains accessible even when enabled  
✅ **API Access** - API endpoints still work for development  
✅ **Statistics** - Track email subscriptions and progress  

## Setup Instructions

### 1. Enable Under Construction Mode

**Option A: Via Admin Panel (Recommended)**
1. Login to admin panel: `http://localhost:8080/admin/login.php`
2. Go to **Under Construction** in the sidebar
3. Click **"Enable Under Construction Mode"**
4. Done! Public users will now see the construction page

**Option B: Via Config File**
1. Edit `config/under-construction.php`
2. Change `'enabled' => true`
3. Save file

### 2. Customize the Page

Edit `config/under-construction.php`:

```php
return [
    'enabled' => true,
    'message' => 'Website is under construction',  // Change this
    'progress' => 85,                              // Update progress percentage
    'contact_email' => 'info@s3vgroup.com',        // Your email
    'contact_phone' => '+1 (234) 567-890',         // Your phone
];
```

### 3. View Email Subscriptions

- Login to admin panel
- Go to **Under Construction**
- View list of email subscriptions
- Export list if needed (future feature)

## How It Works

### What's Protected (Shows Construction Page)
- ✅ Homepage (`/`)
- ✅ Products page (`/products.php`)
- ✅ Product detail pages
- ✅ Contact page
- ✅ All public-facing pages

### What's Still Accessible
- ✅ Admin panel (`/admin/*`)
- ✅ API endpoints (`/api/*`)
- ✅ Setup scripts
- ✅ Logged-in admin users can access full site

## Database Table

The system automatically creates a `construction_notifications` table to store email subscriptions:

```sql
CREATE TABLE construction_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_email (email)
);
```

## Disable Under Construction

When ready to go live:

1. Login to admin panel
2. Go to **Under Construction**
3. Click **"Go Live (Disable Under Construction)"**

Or edit `config/under-construction.php` and set `'enabled' => false`

## Customization

### Change Design
- Edit `under-construction.php` for layout changes
- Colors, fonts, and content can be customized

### Add Custom Content
- Add your logo
- Update company information
- Add social media links
- Customize features preview

### Email Notifications
- Email addresses are stored in database
- Can export list for marketing (future feature)
- Can send launch notification emails (future feature)

## Testing

1. **Test as Public User:**
   - Open browser in incognito/private mode
   - Visit `http://localhost:8080/`
   - Should see construction page

2. **Test Admin Access:**
   - Login to admin panel
   - Should have full access
   - Visit public pages while logged in
   - Should see full website

3. **Test API:**
   - API endpoints should work normally
   - Useful for mobile app development

## Security Notes

- Admin authentication is checked
- IP addresses are logged for email subscriptions
- No sensitive data is exposed
- API endpoints remain functional

## Troubleshooting

**Q: Construction page not showing?**
- Check `config/under-construction.php` has `'enabled' => true`
- Clear browser cache
- Check admin is not logged in (try incognito mode)

**Q: Admin can't access site?**
- Check you're logged into admin panel
- Verify session is active
- Try logging out and back in

**Q: Email notifications not saving?**
- Check database connection
- Table is auto-created on first use
- Check PHP error logs

## Files Created

- `under-construction.php` - Construction page template
- `app/Helpers/UnderConstruction.php` - Helper class
- `config/under-construction.php` - Configuration file
- `admin/under-construction.php` - Admin control panel
- `api/under-construction-notify.php` - Email notification API

## Going Live Checklist

Before disabling under construction:

- [ ] Test all pages
- [ ] Verify all features work
- [ ] Check mobile responsiveness
- [ ] Test checkout process
- [ ] Verify email notifications
- [ ] Check SEO settings
- [ ] Review content accuracy
- [ ] Test contact forms
- [ ] Verify payment processing (if applicable)
- [ ] Check analytics setup

---

**Ready to go live?** Disable under construction mode from the admin panel!

