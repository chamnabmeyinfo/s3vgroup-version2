# Role Management System ✅

## Overview
A comprehensive role-based access control (RBAC) system has been implemented to manage admin users, roles, and permissions. This system allows you to create custom roles with specific permissions, assign roles to users, and control access to different parts of the admin panel.

---

## 🎯 Features Implemented

### 1. **Database Structure**
   - ✅ `roles` table - Stores role information
   - ✅ `permissions` table - Stores all available permissions
   - ✅ `role_permissions` table - Many-to-many relationship between roles and permissions
   - ✅ `admin_users` table updated with `role_id` column

### 2. **Default Roles**
   - ✅ **Super Administrator** - Full system access (all permissions)
   - ✅ **Administrator** - Full administrative access (most permissions)
   - ✅ **Manager** - Can manage products, orders, and content
   - ✅ **Editor** - Can edit products and content
   - ✅ **Viewer** - Read-only access to dashboard and reports
   - ✅ **Support** - Can manage quotes, messages, and customer support

### 3. **Default Permissions (40+ permissions)**
   Organized into categories:
   - Dashboard: View Dashboard
   - Products: View, Create, Edit, Delete
   - Categories: View, Create, Edit, Delete
   - Quotes: View, Manage, Export
   - Messages: View, Manage
   - Reviews: View, Manage
   - Newsletter: View, Manage
   - Settings: View, Manage
   - Users: View, Create, Edit, Delete
   - Roles: View, Create, Edit, Delete
   - Analytics: View, View Advanced
   - Backups: View, Manage
   - Logs: View
   - Images: View, Upload, Delete
   - API: Use, Manage

### 4. **Models Created**
   - ✅ `App\Models\Role` - Role management
   - ✅ `App\Models\Permission` - Permission management
   - ✅ `App\Services\PermissionService` - Permission checking service

### 5. **Admin Pages Created**
   - ✅ `admin/roles.php` - View all roles
   - ✅ `admin/role-edit.php` - Create/edit roles with permission assignment
   - ✅ `admin/users.php` - View all admin users
   - ✅ `admin/user-edit.php` - Create/edit users with role assignment

### 6. **Authentication Updates**
   - ✅ Updated `admin/includes/auth.php` to load role information
   - ✅ Added `hasPermission()` helper function
   - ✅ Added `requirePermission()` helper function
   - ✅ Updated login to store role information in session

### 7. **Permission Checking**
   - ✅ Permission-based access control on all admin pages
   - ✅ Role-based menu items in admin header
   - ✅ Protection against unauthorized access

---

## 📋 Setup Instructions

### Step 1: Run Database Migration

Run the SQL file to create the role management tables:

```sql
-- Execute database/role-management.sql
```

Or via command line:
```bash
mysql -u root -p forklift_equipment < database/role-management.sql
```

### Step 2: Verify Setup

1. Login to admin panel
2. Check that "Roles & Permissions" appears in the menu (if you have permission)
3. Check that "Users" appears in the menu (if you have permission)

### Step 3: Assign Role to Default Admin

The default admin user should automatically get the Super Administrator role (ID: 1). If not, run:

```sql
UPDATE admin_users SET role_id = 1 WHERE username = 'admin';
```

---

## 🛠️ Usage Guide

### Managing Roles

1. **View Roles**
   - Navigate to `admin/roles.php`
   - See all roles with permission counts and user counts

2. **Create Role**
   - Click "Add New Role"
   - Enter role name and description
   - Select permissions to assign
   - Save

3. **Edit Role**
   - Click edit icon on any role
   - Modify name, description, status
   - Add/remove permissions
   - Save

4. **Delete Role**
   - Only non-system roles can be deleted
   - Roles with assigned users cannot be deleted
   - System roles are protected

### Managing Users

1. **View Users**
   - Navigate to `admin/users.php`
   - See all admin users with their roles

2. **Create User**
   - Click "Add New User"
   - Enter username, email, name
   - Select role
   - Set password
   - Save

3. **Edit User**
   - Click edit icon on any user
   - Update information
   - Change role
   - Change password (leave blank to keep current)
   - Save

4. **Delete User**
   - Cannot delete your own account
   - Click delete icon to remove user

---

## 🔐 Permission Checking

### In PHP Code

```php
// Check if user has permission
if (hasPermission('edit_products')) {
    // Allow editing products
}

// Require permission (will show error if not authorized)
requirePermission('delete_users');
```

### In Templates

```php
<?php if (hasPermission('view_analytics')): ?>
    <a href="analytics.php">View Analytics</a>
<?php endif; ?>
```

### Permission Service

```php
$permissionService = new \App\Services\PermissionService();

// Check permission
if ($permissionService->hasPermission('view_products')) {
    // ...
}

// Check multiple permissions (any)
if ($permissionService->hasAnyPermission(['edit_products', 'create_products'])) {
    // ...
}

// Check multiple permissions (all)
if ($permissionService->hasAllPermissions(['edit_products', 'delete_products'])) {
    // ...
}
```

---

## 🎨 Role-Based Menu

The admin menu now shows/hides items based on user permissions:

- Products menu - Requires `view_products`
- Categories menu - Requires `view_categories`
- Quotes menu - Requires `view_quotes`
- Messages menu - Requires `view_messages`
- Reviews menu - Requires `view_reviews`
- Newsletter menu - Requires `view_newsletter`
- Analytics menu - Requires `view_analytics`
- Advanced Analytics - Requires `view_advanced_analytics`
- Backup - Requires `view_backups`
- Logs - Requires `view_logs`
- Images - Requires `view_images`
- Users menu - Requires `view_users`
- Roles menu - Requires `view_roles`
- API Testing - Requires `use_api`

---

## 📊 Permission Matrix

| Permission | Super Admin | Admin | Manager | Editor | Viewer | Support |
|------------|-------------|-------|---------|--------|--------|---------|
| View Dashboard | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| View Products | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Create Products | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| Edit Products | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| Delete Products | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| View Quotes | ✅ | ✅ | ✅ | ❌ | ✅ | ✅ |
| Manage Quotes | ✅ | ✅ | ✅ | ❌ | ❌ | ✅ |
| View Users | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Create Users | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| View Roles | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |

*Full matrix available in role-management.sql*

---

## 🔒 Security Features

1. **System Role Protection**
   - System roles cannot be deleted
   - Prevents accidental removal of critical roles

2. **User Protection**
   - Users cannot delete their own account
   - Users cannot deactivate their own account
   - Prevents accidental lockout

3. **Role Assignment Validation**
   - Cannot delete role with assigned users
   - Must reassign users before deletion

4. **Permission-Based Access**
   - All pages check permissions
   - Unauthorized access shows 403 error
   - Menu items hidden based on permissions

---

## 📝 Adding New Permissions

To add a new permission:

1. **Add to Database:**
```sql
INSERT INTO permissions (name, slug, description, category) VALUES
('New Permission Name', 'new_permission_slug', 'Description', 'category');
```

2. **Assign to Roles:**
```sql
INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions WHERE slug = 'new_permission_slug';
```

3. **Use in Code:**
```php
requirePermission('new_permission_slug');
```

---

## 🚀 Future Enhancements

Potential additions:
- Permission groups/hierarchies
- Custom permission definitions
- Role cloning/duplication
- Permission audit log
- Bulk role assignment
- Role templates
- Permission inheritance
- API permission tokens

---

## ✅ Status

All features implemented and tested:
- ✅ Database tables created
- ✅ Models created
- ✅ Permission service created
- ✅ Role management pages
- ✅ User management pages
- ✅ Permission checking system
- ✅ Role-based menu items
- ✅ Authentication integration
- ✅ Security protections

---

**Created:** $(date)
**Version:** 1.0
**Status:** Complete ✅

