# Pre-Deployment Security Checklist

## 🔒 CRITICAL SECURITY CHECKS

Before deploying to production (s3vtgroup.com.kh), verify these items:

### 1. Configuration Files
- [ ] **config/app.php** - Debug mode is `false` for production (auto-detected)
- [ ] **config/database.php** - Uses production database credentials
- [ ] **.htaccess** - Security headers are enabled
- [ ] **config/app.php** - URL is set to `https://s3vtgroup.com.kh`

### 2. Remove Test/Debug Files
- [ ] Run cleanup script: `php cleanup-production.php --dry-run` (review first)
- [ ] Remove all `test-*.php` files
- [ ] Remove all `check-*.php` files
- [ ] Remove `check-php-config.php` (exposes PHP info - SECURITY RISK)

### 3. Remove Setup Scripts
- [ ] Remove all `setup-*.php` files
- [ ] Remove `admin/setup-*.php` files
- [ ] These can be used to modify database if left on server

### 4. File Permissions
- [ ] PHP files: `644` (readable by web server, writable by owner)
- [ ] Directories: `755` (executable by web server)
- [ ] `storage/` directory: `755` (writable for uploads)
- [ ] `config/database.php`: `600` or `640` (restrict access)

### 5. Sensitive Files
- [ ] Remove `deploy-config.json` (may contain credentials)
- [ ] Remove `developer/` directory (development tools)
- [ ] Remove `.env` files if any exist
- [ ] Ensure `.gitignore` is working (sensitive files not in Git)

### 6. Database Security
- [ ] Use strong database passwords
- [ ] Database user has minimal required permissions
- [ ] No root database user in production
- [ ] Regular backups configured

### 7. Error Handling
- [ ] Error display is OFF (`display_errors = 0`)
- [ ] Error logging is ON (log errors, don't show to users)
- [ ] Custom error pages configured (404, 500, etc.)

### 8. Admin Panel Security
- [ ] Default admin password changed
- [ ] Strong passwords for all admin users
- [ ] Admin panel accessible only via HTTPS
- [ ] Rate limiting on login page (if possible)

### 9. SSL/HTTPS
- [ ] SSL certificate installed and valid
- [ ] All URLs use HTTPS
- [ ] HTTP redirects to HTTPS
- [ ] Mixed content warnings resolved

### 10. Backup
- [ ] Full backup of files before cleanup
- [ ] Database backup before deployment
- [ ] Backup location verified

---

## 📋 CLEANUP STEPS

### Step 1: Review Cleanup Plan
```bash
# Read the cleanup plan
cat PRODUCTION-CLEANUP-PLAN.md
```

### Step 2: Dry Run (See What Will Be Deleted)
```bash
# From command line
php cleanup-production.php --dry-run

# Or from browser
# cleanup-production.php?password=CLEANUP2024
```

### Step 3: Execute Cleanup
```bash
# From command line (after reviewing dry run)
php cleanup-production.php --execute

# Or from browser
# cleanup-production.php?password=CLEANUP2024&execute=1
```

### Step 4: Verify Website Still Works
- [ ] Test homepage
- [ ] Test product pages
- [ ] Test admin login
- [ ] Test admin features
- [ ] Check error logs

---

## ✅ POST-DEPLOYMENT VERIFICATION

After cleanup and deployment:

1. **Website Functionality**
   - [ ] Homepage loads
   - [ ] Products display correctly
   - [ ] Shopping cart works
   - [ ] Checkout process works
   - [ ] Admin panel accessible

2. **Security**
   - [ ] No test files accessible
   - [ ] No setup scripts accessible
   - [ ] Error messages don't expose system info
   - [ ] HTTPS working correctly

3. **Performance**
   - [ ] Page load times acceptable
   - [ ] Images load correctly
   - [ ] No 404 errors for missing files

4. **Monitoring**
   - [ ] Error logs being written
   - [ ] Access logs working
   - [ ] Database connections stable

---

## 🚨 IF SOMETHING BREAKS

1. **Restore from Backup**
   - Restore files from backup
   - Restore database if needed

2. **Check Error Logs**
   - Check `storage/logs/` directory
   - Check server error logs
   - Check PHP error logs

3. **Verify File Permissions**
   - Ensure files are readable
   - Ensure directories are executable

4. **Check Database Connection**
   - Verify `config/database.php` credentials
   - Test database connection

---

## 📝 NOTES

- **Keep in Git:** All removed files are still in Git repository
- **Can Restore:** Files can be restored from Git if needed
- **Documentation:** All docs are in Git, can be accessed later
- **Safe Process:** Cleanup script has dry-run mode for safety

---

**Last Updated:** For production deployment to s3vtgroup.com.kh
**Status:** Ready for review and execution

