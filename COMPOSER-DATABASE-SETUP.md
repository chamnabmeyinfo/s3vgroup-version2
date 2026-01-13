# Using Composer to Manage Live cPanel Database

## Overview

With Composer, you can use powerful database packages to directly modify, update, and manage your live cPanel phpMyAdmin database from Cursor terminal.

## Setup

### Step 1: Install Composer

If you don't have Composer installed:

**Windows:**
```bash
# Download from https://getcomposer.org/download/
# Or use: php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
```

**Or use existing PHP:**
```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
```

### Step 2: Install Dependencies

```bash
composer install
```

This installs:
- **Doctrine DBAL** - Advanced database abstraction layer
- **Symfony Console** - Better CLI command handling
- **PHP Dotenv** - Environment variable management

### Step 3: Use Composer Scripts

Now you can use Composer scripts to interact with your database:

```bash
# Switch to live database
composer db:live

# Run SQL queries on live database
composer db:query "SELECT * FROM products LIMIT 5;"

# Backup live database
composer db:backup

# Optimize live database
composer db:optimize

# Clear cache
composer cache:clear

# Run migrations
composer migrate
```

## Advanced Database Operations with Doctrine DBAL

### Using Doctrine DBAL Directly

Create a PHP script to use Doctrine DBAL features:

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

use App\Database\DatabaseManager;

// Get table schema
$schema = DatabaseManager::getTableSchema('products');
print_r($schema);

// Execute advanced query
$results = DatabaseManager::executeQuery(
    "SELECT * FROM products WHERE price > :price",
    ['price' => 1000]
);

// Export table data
$data = DatabaseManager::exportTable('products', ['is_active' => 1]);

// Import data
DatabaseManager::importTable('new_table', $data);
```

### Enhanced Artisan Commands with Composer

You can now add more powerful commands to `artisan`:

```bash
# Using Doctrine DBAL features
php artisan db:schema products          # Show table schema
php artisan db:export products          # Export table to JSON
php artisan db:import products data.json # Import from JSON
php artisan db:tables                   # List all tables with details
```

## Direct Database Modification Examples

### Example 1: Update Products via Composer Script

Create `scripts/update-products.php`:
```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../bootstrap/app.php';

use App\Database\DatabaseManager;

// Update all product prices
DatabaseManager::executeStatement(
    "UPDATE products SET price = price * 1.1 WHERE category_id = :cat_id",
    ['cat_id' => 1]
);

echo "Products updated!\n";
```

Run it:
```bash
php scripts/update-products.php
```

### Example 2: Bulk Import via Composer

Create `scripts/import-products.php`:
```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../bootstrap/app.php';

use App\Database\DatabaseManager;

$products = [
    ['name' => 'Product 1', 'price' => 1000, 'category_id' => 1],
    ['name' => 'Product 2', 'price' => 2000, 'category_id' => 1],
];

$inserted = DatabaseManager::importTable('products', $products);
echo "Inserted {$inserted} products!\n";
```

### Example 3: Database Schema Management

```php
<?php
use App\Database\DatabaseManager;

// Get all tables
$tables = DatabaseManager::getTables();

foreach ($tables as $table) {
    echo "Table: " . $table->getName() . "\n";
    $schema = DatabaseManager::getTableSchema($table->getName());
    print_r($schema);
}
```

## Composer Scripts Available

After running `composer install`, you can use:

```bash
composer db:live          # Switch to live database
composer db:local         # Switch to local database
composer db:query         # Run SQL query
composer db:backup        # Backup database
composer db:optimize      # Optimize database
composer cache:clear      # Clear cache
composer migrate          # Run migrations
composer serve            # Start dev server
```

## Benefits of Using Composer

✅ **Powerful Packages** - Access to Doctrine DBAL, Symfony Console, etc.  
✅ **Better Tools** - Advanced database operations  
✅ **Scripts** - Easy-to-run commands  
✅ **Autoloading** - Automatic class loading  
✅ **Dependency Management** - Easy package updates  
✅ **Cross-Platform** - Works on Windows, Mac, Linux  

## Workflow

### Working with Live Database:

1. **Switch to live:**
   ```bash
   composer db:live
   ```

2. **Make changes:**
   ```bash
   composer db:query "UPDATE products SET status = 'active' WHERE id = 1;"
   ```

3. **Verify changes:**
   ```bash
   composer db:query "SELECT * FROM products WHERE id = 1;"
   ```

4. **Backup before major changes:**
   ```bash
   composer db:backup
   ```

## Custom Composer Scripts

Add your own scripts to `composer.json`:

```json
{
    "scripts": {
        "custom:update-prices": "php scripts/update-prices.php",
        "custom:sync-data": "php scripts/sync-data.php"
    }
}
```

Then run:
```bash
composer custom:update-prices
composer custom:sync-data
```

## Security Notes

⚠️ **Important:**
- `composer.json` can be committed (no sensitive data)
- `composer.lock` tracks exact versions
- `vendor/` directory is in `.gitignore`
- Database credentials stay in `config/database.live.php` (not committed)

## Troubleshooting

### "composer: command not found"
- Install Composer globally or use `php composer.phar` instead
- On Windows, add Composer to PATH

### "Package not found"
- Run `composer install` to install dependencies
- Check `composer.json` for correct package names

### "Class not found"
- Run `composer dump-autoload` to regenerate autoloader

---

**Ready to use Composer?** Run `composer install` and start managing your live database! 🚀
