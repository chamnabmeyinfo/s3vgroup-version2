# Production Cleanup Plan for s3vtgroup.com.kh

## 🎯 Purpose

This document outlines which files should be **KEPT** and which should be **REMOVED** before deploying to production to ensure security, performance, and maintainability.

---

## ✅ FILES TO KEEP (Essential for Website Function)

### Core Application Files

- ✅ **All PHP files in root** (index.php, product.php, cart.php, etc.) - Required for frontend
- ✅ **admin/** directory - Complete admin panel (required)
- ✅ **api/** directory - API endpoints (if used)
- ✅ **app/** directory - Core application logic (REQUIRED)
- ✅ **bootstrap/** directory - Application bootstrap (REQUIRED)
- ✅ **config/** directory - Configuration files (REQUIRED)
- ✅ **includes/** directory - Shared includes (REQUIRED)
- ✅ **assets/** directory - CSS, JS, images (REQUIRED)
- ✅ **storage/** directory - Uploads, cache, logs (REQUIRED)
- ✅ **cron/** directory - Cron jobs (if used)

### Essential Configuration

- ✅ **.htaccess** - Apache configuration (REQUIRED)
- ✅ **config/database.php** - Database config (REQUIRED - but ensure secure)
- ✅ **config/app.php** - Application config (REQUIRED)

### Essential Documentation (Keep Only These)

- ✅ **README.md** - Main project documentation
- ✅ **LICENSE** - License file

### Database Files

- ✅ **database/** directory - SQL files (keep for reference/backup)

---

## ❌ FILES TO REMOVE (Security & Cleanup)

### 1. Test & Debug Files (SECURITY RISK)

**Reason:** These expose system information and should never be on production.

- ❌ `test-simple.php` - Test file
- ❌ `test-connection.php` - Database connection test
- ❌ `check-php-config.php` - Exposes PHP configuration (SECURITY RISK)
- ❌ `admin/check-images.php` - Debug tool
- ❌ `admin/api-test.php` - API testing
- ❌ `developer/debug-test.php` - Debug file

### 2. Setup Scripts (No Longer Needed)

**Reason:** These are one-time setup scripts. Once database is set up, they're not needed.

- ❌ `setup.php` - Initial setup wizard
- ❌ `setup-advanced.php` - Advanced setup
- ❌ `setup-direct.php` - Direct setup
- ❌ `setup-hero-slider-options.php` - One-time setup
- ❌ `setup-hero-sliders.php` - One-time setup
- ❌ `setup-hero-sliders-direct.php` - One-time setup
- ❌ `setup-password-reset.php` - One-time database setup (already run)
- ❌ `admin/setup-roles.php` - One-time role setup (already run)
- ❌ `admin/setup-orders.php` - One-time orders setup (already run)
- ❌ `admin/setup-variants.php` - One-time variants setup (already run)

### 3. Development/Demo Files

**Reason:** Not needed in production.

- ❌ `message-demo.php` - Demo file
- ❌ `start-here.php` - Development helper
- ❌ `NEW-PAGE-TEMPLATE.php` - Template file
- ❌ `developer/` directory - Development tools (remove entire directory)

### 4. Deployment Scripts (Keep Only If Needed)

**Reason:** These are for deployment process, not needed on live server.

- ❌ `deploy-*.php` - All deployment scripts (if not using automated deployment)
- ❌ `deploy.bat` - Windows deployment script
- ❌ `deploy-config.json` - Deployment config (contains sensitive data)
- ❌ `deploy-log.txt` - Deployment logs
- ❌ `prepare-deployment.bat` - Deployment helper
- ❌ `cpanel-safe-pull.php` - Git pull helper (if not using)

**⚠️ KEEP IF:** You're using automated deployment from this server

### 5. Git/Version Control Scripts

**Reason:** Not needed on production server.

- ❌ `git-auto-push.bat` - Git automation
- ❌ `git-auto-push.sh` - Git automation
- ❌ `show-ignored-files.bat` - Development tool
- ❌ `show-ignored-files.sh` - Development tool

### 6. Documentation Files (Keep Only Essential)

**Reason:** Too many documentation files clutter the project. Keep only essential ones.

**REMOVE THESE:**

- ❌ `ADVANCED-BACKEND-COMPLETE.md`
- ❌ `ADVANCED-BACKEND.md`
- ❌ `ADVANCED-FEATURES.md`
- ❌ `ADVANCED-FILTERS-COMPLETE.md`
- ❌ `ADVANCED-UX-FEATURES.md`
- ❌ `ALL-FEATURES.md`
- ❌ `APACHE-SETUP.txt`
- ❌ `BACKEND-COMPLETE.md`
- ❌ `BACKEND-INNOVATIONS.md`
- ❌ `BACKEND-PLANNING-PROMPT.md`
- ❌ `BACKEND-PLANNING-QUICK-START.txt`
- ❌ `BEST-DEPLOYMENT-SOLUTION.md`
- ❌ `COMPLETE-DEPLOYMENT-GUIDE.md`
- ❌ `COMPLETE.md`
- ❌ `CONTRIBUTING.md`
- ❌ `CPANEL-GIT-PULL-FIX.md`
- ❌ `DESIGN-VERSION-SYSTEM.md`
- ❌ `DEVELOPER-GUIDE.md`
- ❌ `DEVELOPMENT-WITH-UNDER-CONSTRUCTION.md`
- ❌ `FIX-503-ERROR.md`
- ❌ `FIX-IMAGE-PATHS.md`
- ❌ `FIX-IMAGES-NOT-LOADING.md`
- ❌ `FIX-UNDER-CONSTRUCTION.md`
- ❌ `GIT-AUTO-PUSH.md`
- ❌ `GIT-SETUP.md`
- ❌ `GITHUB-TOKEN-SETUP.md`
- ❌ `GREAT-FEATURES.md`
- ❌ `HOW-TO-CHANGE-SUPER-ADMIN-CREDENTIALS.md`
- ❌ `IMAGES-INFO.md`
- ❌ `IMPROVEMENTS.md`
- ❌ `LIST-IGNORED-FILES.md`
- ❌ `MORE-FEATURES.md`
- ❌ `MULTI-CHAT-WORKFLOW.md`
- ❌ `NEXT-STEPS-AFTER-PHP-FIX.md`
- ❌ `ONE-CLICK-DEPLOYMENT-PLAN.md`
- ❌ `ORDERS-MANAGEMENT-COMPLETE.md`
- ❌ `ORDERS-MANAGEMENT-SUMMARY.md`
- ❌ `PASSWORD-RESET-GUIDE.md`
- ❌ `PRODUCT-IMAGES-COMPLETE.md`
- ❌ `PRODUCT-IMAGES-SUMMARY.md`
- ❌ `PUSH-TO-GITHUB.md`
- ❌ `QUICK-DEVELOPMENT-GUIDE.md`
- ❌ `QUICK-FIX-LINKS.md`
- ❌ `QUICK-FIX-ORDERS.md`
- ❌ `QUICK-SETUP-DEPLOYMENT.md`
- ❌ `QUICK-START.md`
- ❌ `REPOSITORY-READY.md`
- ❌ `ROLE-MANAGEMENT-COMPLETE.md`
- ❌ `SAMPLE-DATA-INFO.md`
- ❌ `SETUP-ORDERS.md`
- ❌ `SETUP-ROLES.md`
- ❌ `SETUP.md`
- ❌ `SMART-DEPLOYMENT-IDEAS.md`
- ❌ `SMART-FEATURES-COMPLETE.md`
- ❌ `TROUBLESHOOTING.md`
- ❌ `ULTIMATE-FEATURES.md`
- ❌ `UNDER-CONSTRUCTION-COMPLETE.md`
- ❌ `UNDER-CONSTRUCTION-SETUP.md`
- ❌ `UPDATE-PRODUCTION-CONFIG.md`
- ❌ `UPLOAD-IMAGES-TO-CPANEL.md`
- ❌ `URLS.txt`
- ❌ `VIRTUAL-HOST-SETUP.md`
- ❌ `WHAT-GOES-TO-GITHUB.md`
- ❌ `WHAT-TO-DEVELOP-NEXT.md`
- ❌ `WHM-SETUP-GUIDE.md`
- ❌ `CRUD-ANALYSIS.md`
- ❌ `DEPLOYMENT-GUIDE.md`
- ❌ `DEPLOYMENT-SYSTEM-README.md`
- ❌ `FINAL-ADVANCED-FEATURES.md`
- ❌ `INFO.txt`

**KEEP THESE:**

- ✅ `README.md` - Main documentation
- ✅ `LICENSE` - License file

### 7. Temporary/Backup Files

**Reason:** Not needed in production.

- ❌ `s3vgroup-deployment-20251130.zip` - Old deployment archive
- ❌ `deployment-exclude.txt` - Deployment config
- ❌ Any `*.bak`, `*.backup`, `*.tmp` files

### 8. PowerShell Scripts (Windows Only)

**Reason:** Not needed on Linux production server.

- ❌ `setup-virtual-host.ps1` - Windows PowerShell script

---

## 🔒 SECURITY CHECKLIST

Before deploying, ensure:

1. ✅ **Remove all test files** - They expose system information
2. ✅ **Remove setup scripts** - Prevent unauthorized database access
3. ✅ **Check config/database.php** - Ensure production credentials
4. ✅ **Check config/app.php** - Ensure `debug => false`
5. ✅ **Check .htaccess** - Ensure proper security headers
6. ✅ **Remove developer/ directory** - Contains development tools
7. ✅ **Remove deploy-config.json** - May contain sensitive data
8. ✅ **Set proper file permissions** - 644 for files, 755 for directories

---

## 📋 CLEANUP SUMMARY

### Files to Remove: ~100+ files

- Test/Debug files: ~6 files
- Setup scripts: ~10 files
- Documentation: ~70 files
- Deployment scripts: ~8 files
- Development files: ~5 files
- Temporary files: ~5 files

### Files to Keep: All core application files

- All PHP application files
- All directories (admin, app, assets, etc.)
- Essential config files
- README.md and LICENSE only

---

## ⚠️ IMPORTANT NOTES

1. **BACKUP FIRST:** Always backup before cleanup
2. **Test Locally:** Test the cleanup on a local copy first
3. **Gradual Removal:** Remove files in batches and test
4. **Keep Git History:** These files are still in Git, just removed from production
5. **Documentation:** All documentation is in Git, can be restored if needed

---

## 🚀 RECOMMENDED CLEANUP ORDER

1. **Phase 1:** Remove test/debug files (highest security risk)
2. **Phase 2:** Remove setup scripts (no longer needed)
3. **Phase 3:** Remove documentation files (cleanup)
4. **Phase 4:** Remove deployment scripts (if not using)
5. **Phase 5:** Remove development files
6. **Phase 6:** Final security check

---

## ✅ VERIFICATION

After cleanup, verify:

- [ ] Website loads correctly
- [ ] Admin panel works
- [ ] Database connections work
- [ ] No error logs mentioning deleted files
- [ ] All features still functional

---

**Created:** For production deployment to s3vtgroup.com.kh
**Purpose:** Safe cleanup without breaking functionality
