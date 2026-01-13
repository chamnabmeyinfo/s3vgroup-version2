# Quick Start: Using Composer with Live Database

## 🚀 Quick Setup (3 Steps)

### Step 1: Install Composer Dependencies
```bash
composer install
```

### Step 2: Setup Live Database Config
```bash
# Copy the example file
cp config/database.live.php.example config/database.live.php

# Edit it with your cPanel credentials
# (Open config/database.live.php and update the values)
```

### Step 3: Switch to Live Database
```bash
composer db:live
```

## ✅ Now You Can Use Composer Commands!

### Basic Commands:
```bash
# Switch environments
composer db:live          # Switch to live database
composer db:local        # Switch to local database

# Database operations
composer db:query "SELECT * FROM products LIMIT 5;"
composer db:backup       # Backup database
composer db:optimize     # Optimize tables
composer migrate         # Run migrations
composer cache:clear     # Clear cache
```

### Advanced Commands (Using Doctrine DBAL):
```bash
# Show table schema
php scripts/db-manage.php schema products

# List all tables
php scripts/db-manage.php tables

# Export table to JSON
php scripts/db-manage.php export products

# Execute SQL query
php scripts/db-manage.php query "SELECT * FROM products;"
```

## 📦 What Composer Gives You

1. **Doctrine DBAL** - Advanced database operations
2. **Symfony Console** - Better CLI commands
3. **PHP Dotenv** - Environment management
4. **Autoloading** - Automatic class loading

## 💡 Example Workflow

```bash
# 1. Switch to live database
composer db:live

# 2. Check current data
composer db:query "SELECT COUNT(*) as total FROM products;"

# 3. Make changes
composer db:query "UPDATE products SET price = 1500 WHERE id = 1;"

# 4. Verify changes
composer db:query "SELECT * FROM products WHERE id = 1;"

# 5. Backup before major changes
composer db:backup
```

## 🎯 Key Benefits

✅ **Same commands** work on local and server  
✅ **Powerful packages** via Composer  
✅ **Easy switching** between environments  
✅ **Advanced features** with Doctrine DBAL  
✅ **Script management** via Composer  

## 📚 Full Documentation

- See `COMPOSER-DATABASE-SETUP.md` for detailed guide
- See `SETUP-LIVE-DATABASE.md` for environment setup

---

**Ready?** Run `composer install` and start managing your live database! 🚀
