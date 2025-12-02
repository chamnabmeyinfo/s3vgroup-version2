# Design Version Management System

A comprehensive rollback system for managing front-end design changes with version control, snapshots, and rollback capabilities.

## 🎯 Overview

This system allows you to:
- **Create snapshots** of all front-end files before making design changes
- **Rollback** to any previous version if you're not happy with changes
- **Delete** versions you no longer need
- **Track** version history with descriptions and timestamps

## 📍 Access

**Location:** Developer Panel → Design Versions  
**URL:** `http://localhost:8080/developer/design-versions.php`

## 🚀 Quick Start

### Before Making Design Changes

1. **Go to Developer Panel** → **Design Versions**
2. **Click "Create Snapshot"**
3. **Add a description** (e.g., "Before modern redesign")
4. **Click "Create Snapshot"**

Now you can safely make design changes. If you don't like the result, simply rollback!

### Rolling Back

1. Find the version you want to restore
2. Click **"Rollback"** button
3. Confirm the action
4. All files will be restored to that version

### Deleting Versions

1. Find the version you want to delete
2. Click **"Delete"** button
3. Confirm the action
4. Version and all its files will be permanently removed

## 📁 Files That Are Versioned

The system automatically backs up:

### CSS Files
- `assets/css/style.css`
- `assets/css/product-images.css`

### JavaScript Files
- `assets/js/main.js`
- `assets/js/advanced-search.js`
- `assets/js/advanced-ux.js`
- `assets/js/smart-search.js`

### Include Files
- `includes/header.php`
- `includes/footer.php`
- `includes/message.php`

### Main Templates
- `index.php`
- `products.php`
- `product.php`
- `contact.php`
- `quote.php`
- `checkout.php`
- `cart.php`

## 💻 Programmatic Usage

### Create Snapshot via Code

```php
use App\Services\DesignVersionService;

$service = new DesignVersionService();
$result = $service->createVersion('Before redesign', 'developer');
```

### Using Helper Function

```php
use App\Helpers\DesignVersionHelper;

// Before making changes
DesignVersionHelper::snapshotBeforeChanges('Before modern redesign', 'developer');

// Or quick snapshot
DesignVersionHelper::quickSnapshot('developer');
```

### Rollback via Code

```php
use App\Services\DesignVersionService;

$service = new DesignVersionService();
$result = $service->rollbackToVersion('v20241202143000_abc123');
```

## 📊 Version Storage

- **Location:** `storage/design-backups/`
- **Structure:** Each version has its own directory with version ID
- **Database:** Version metadata stored in `design_versions` table

## 🔒 Safety Features

1. **Automatic Directory Creation:** Backup directory is created automatically
2. **File Verification:** Only existing files are backed up
3. **Confirmation Dialogs:** Rollback and delete require confirmation
4. **Status Tracking:** Versions track their status (active/rolled_back)

## 📝 Best Practices

1. **Always create a snapshot** before major design changes
2. **Use descriptive names** for snapshots (e.g., "Before color scheme update")
3. **Keep recent versions** - delete old ones you're sure you won't need
4. **Test rollback** on a development environment first if possible

## 🛠️ Technical Details

### Service Class
- `App\Services\DesignVersionService` - Main service class

### Helper Class
- `App\Helpers\DesignVersionHelper` - Convenience functions

### Database Table
```sql
CREATE TABLE design_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version_id VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    created_at DATETIME NOT NULL,
    created_by VARCHAR(100),
    files_count INT DEFAULT 0,
    status VARCHAR(20) DEFAULT 'active',
    rolled_back_at DATETIME NULL
);
```

## ❓ FAQ

**Q: Can I add more files to versioning?**  
A: Yes! Edit `$filesToVersion` array in `app/Services/DesignVersionService.php`

**Q: Where are backups stored?**  
A: `storage/design-backups/` directory

**Q: Can I restore individual files?**  
A: Currently, rollback restores all files. Individual file restore can be added if needed.

**Q: How much disk space do versions use?**  
A: Each version stores copies of all versioned files. Monitor `storage/design-backups/` size.

**Q: Are versions included in git?**  
A: No, the `storage/design-backups/` directory is typically in `.gitignore`

## 🎨 Workflow Example

1. **Before redesign:**
   ```
   Create Snapshot → "Before modern redesign"
   ```

2. **Make design changes:**
   ```
   Edit CSS, templates, etc.
   ```

3. **Not happy with result?**
   ```
   Go to Design Versions → Click Rollback → Confirm
   ```

4. **Happy with result?**
   ```
   Create new snapshot → "After modern redesign"
   ```

5. **Clean up:**
   ```
   Delete old versions you don't need
   ```

## 🔄 Integration with Design Process

When starting a new design upgrade:

1. **Create snapshot** with description like "Before [design name] upgrade"
2. **Make your design changes**
3. **Test thoroughly**
4. **If not satisfied:** Rollback and try again
5. **If satisfied:** Create new snapshot "After [design name] upgrade"
6. **Delete old snapshots** if needed

---

**Ready to start?** Go to Developer Panel → Design Versions and create your first snapshot!

