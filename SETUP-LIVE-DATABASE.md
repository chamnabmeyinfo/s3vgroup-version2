# Setup Guide: Working with Live Database from Cursor

## Overview

This setup allows you to use the same `artisan` commands on both **local** and **live server** terminals. You can easily switch between environments.

## Quick Start

### Step 1: Setup Live Database Config

1. Copy the example file:
   ```bash
   cp config/database.live.php.example config/database.live.php
   ```

2. Edit `config/database.live.php` with your cPanel credentials:
   ```php
   return [
       'host' => 'localhost',  // Usually 'localhost' on cPanel
       'dbname' => 'your_cpanel_database_name',  // e.g., 'username_forklift'
       'username' => 'your_cpanel_database_user',  // e.g., 'username_dbuser'
       'password' => 'your_cpanel_database_password',
       // ...
   ];
   ```

### Step 2: Switch to Live Database

From your **local terminal** (Cursor):
```bash
php artisan env:live
```

This will:
- Switch your database connection to live server
- Test the connection
- All subsequent `artisan` commands will use the live database

### Step 3: Use Artisan Commands

Now all commands work on the **live database**:

```bash
# Run SQL queries on live database
php artisan db:query "SELECT * FROM products LIMIT 5;"

# Optimize live database
php artisan db:optimize

# Backup live database
php artisan db:backup

# Clear cache
php artisan cache:clear
```

### Step 4: Switch Back to Local

When you want to work locally again:
```bash
php artisan env:local
```

## How It Works

1. **Environment File**: Creates `.database-env` file that stores current environment (`local` or `live`)
2. **Auto-Switch**: `config/database.php` automatically loads the correct config based on `.database-env`
3. **Same Commands**: All `artisan` commands work the same way, just pointing to different databases

## Commands Reference

### Environment Commands
```bash
php artisan env:local    # Switch to local database
php artisan env:live     # Switch to live database  
php artisan env:show     # Show current environment
```

### Database Commands
```bash
php artisan db:query "SQL"     # Run SQL query
php artisan db:optimize        # Optimize tables
php artisan db:backup          # Create backup
php artisan migrate            # Run migrations
```

## Usage Scenarios

### Scenario 1: Working on Live Database from Local Terminal
```bash
# Switch to live
php artisan env:live

# Make changes directly on live database
php artisan db:query "UPDATE products SET price = 1500 WHERE id = 1;"

# Check results
php artisan db:query "SELECT * FROM products WHERE id = 1;"
```

### Scenario 2: Working on Server Terminal
If you SSH into your server and run `artisan` there:
- It will use the server's `config/database.php` 
- Which automatically points to the live database
- Same commands work the same way!

### Scenario 3: Switching Between Environments
```bash
# Work on live
php artisan env:live
php artisan db:query "SELECT COUNT(*) FROM products;"

# Switch to local for testing
php artisan env:local
php artisan db:query "SELECT COUNT(*) FROM products;"

# Switch back to live
php artisan env:live
```

## Security Notes

⚠️ **Important:**
- `config/database.live.php` is in `.gitignore` (won't be committed)
- `.database-env` is in `.gitignore` (won't be committed)
- Never commit live database credentials to Git
- Always backup before making changes to live database

## Troubleshooting

### "Live database config not found"
- Make sure you copied `config/database.live.php.example` to `config/database.live.php`
- Update it with your actual cPanel credentials

### "Connection failed"
- Verify your cPanel database credentials
- Check if database user has proper permissions
- Ensure database exists in cPanel

### "Command not found"
- Make sure you're in the project root directory
- Use: `php artisan list` to see all commands

## Benefits

✅ **Same commands** work on both local and server  
✅ **Easy switching** between environments  
✅ **Direct database access** from Cursor terminal  
✅ **Safe** - credentials never committed to Git  
✅ **Flexible** - work locally or on server, your choice

---

**Ready to start?** Run `php artisan env:live` and begin working with your live database! 🚀
