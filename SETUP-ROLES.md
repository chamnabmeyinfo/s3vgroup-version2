# Role Management Setup Guide

## Issue Fixed ✅

The error "Table 'forklift_equipment.admin_users' doesn't exist" has been resolved. The code now handles missing tables gracefully.

## What Changed

1. **PermissionService** - Now checks if tables exist before querying
2. **auth.php** - Handles missing tables gracefully
3. **login.php** - Works even if role tables don't exist yet
4. **header.php** - Only shows role management menu if tables exist

## Setup Steps

### Option 1: Automatic Setup (Recommended)

1. Make sure your database is set up first:
   - Run `database/schema.sql` if you haven't already
   - This creates the `admin_users` table and other basic tables

2. Login to admin panel (username: admin, password: admin)

3. Visit `admin/setup-roles.php` in your browser

4. Click "Run Setup" button

5. Done! Role management is now active.

### Option 2: Manual Setup

1. Make sure `admin_users` table exists:
   ```sql
   -- Check if table exists
   SHOW TABLES LIKE 'admin_users';
   ```

2. If it doesn't exist, run the basic schema first:
   ```sql
   -- Run database/schema.sql
   ```

3. Then run the role management SQL:
   ```sql
   -- Run database/role-management.sql
   ```

   Or via command line:
   ```bash
   mysql -u root -p forklift_equipment < database/role-management.sql
   ```

## Verification

After setup, you should see:
- ✅ "Roles & Permissions" menu item in admin panel
- ✅ "Users" menu item in admin panel
- ✅ No errors when accessing admin pages

## Troubleshooting

### Error: "Table doesn't exist"
- Make sure you've run `database/schema.sql` first
- This creates the basic `admin_users` table

### Error: "Cannot access roles page"
- The code now gracefully handles missing tables
- Just run the setup and try again

### Want to remove role management?
- The code works fine without role tables
- Pages will work normally, just without permission checking
- Menu items won't show if tables don't exist

## Current Status

✅ Code is now defensive - won't crash if tables don't exist
✅ Graceful fallback - works without role management
✅ Easy setup - just run one SQL file or use setup page

