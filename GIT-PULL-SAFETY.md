# Git Pull Safety Guide for cPanel

## ⚠️ Important: How Git Pull Works with File Deletions

### What Happens When You Pull from GitHub?

**YES, `git pull` WILL delete files in cPanel if:**

- ✅ The file was **committed to Git** (tracked by Git)
- ✅ The file was **deleted in a commit** on GitHub
- ✅ You run `git pull` on cPanel

**NO, `git pull` will NOT delete files if:**

- ✅ The file is in `.gitignore` (not tracked by Git)
- ✅ The file was never committed to Git
- ✅ The file exists only on your server (user uploads, local configs)

## 🛡️ Protected Files (Safe from Deletion)

Based on your `.gitignore`, these files are **SAFE** and won't be deleted:

### User-Generated Content (Protected)

- `storage/uploads/*` - All uploaded images and files
- `storage/cache/*` - Cache files
- `storage/logs/*` - Log files
- `storage/backups/*` - Backup files

### Configuration Files (Protected)

- `config/database.php` - Your database credentials
- `config/app.php` - Your app configuration
- `config/under-construction.php` - Under construction settings

### Other Protected Files

- Any files matching: `test-*.php`, `*.log`, `*.cache`, `*.tmp`
- Files in `storage/design-backups/` (if not committed)

## ⚠️ Files That CAN Be Deleted

These files are tracked by Git and WILL be deleted if removed on GitHub:

- All PHP application files (frontend & admin)
- CSS and JavaScript files in `assets/`
- SQL schema files in `database/`
- Configuration example files (`.example`)
- README.md and other documentation

## 🔒 Safe Pull Process for cPanel

### Option 1: Safe Pull (Recommended)

```bash
# 1. Check what will change (dry run)
git fetch origin
git diff HEAD origin/main --name-status

# 2. Review the changes (especially deletions)
# Look for 'D' (deleted) files

# 3. If safe, pull
git pull origin main
```

### Option 2: Backup Before Pull

```bash
# 1. Create backup of important files
cp -r storage/uploads storage/uploads.backup
cp config/database.php config/database.php.backup

# 2. Pull changes
git pull origin main

# 3. Restore if needed
# (only if something went wrong)
```

### Option 3: Stash Local Changes

```bash
# If you have local changes you want to keep
git stash

# Pull changes
git pull origin main

# Restore your local changes
git stash pop
```

## 📋 Pre-Pull Checklist

Before running `git pull` on cPanel:

- [ ] **Backup your database** (if schema changes expected)
- [ ] **Backup `storage/uploads/`** (user images - though protected by .gitignore)
- [ ] **Check recent commits** on GitHub for deletions
- [ ] **Review `.gitignore`** to ensure important files are protected
- [ ] **Test on local first** if possible

## 🚨 What to Do If Files Are Deleted

If important files are accidentally deleted:

1. **Check Git History:**

   ```bash
   git log --all --full-history -- <file-path>
   ```

2. **Restore from Git:**

   ```bash
   git checkout HEAD~1 -- <file-path>
   ```

3. **Restore from Backup:**
   - Use your cPanel backup
   - Restore from `storage/backups/`

## 💡 Best Practices

1. **Always review commits before pulling** - Check GitHub for file deletions
2. **Keep backups** - Regular backups of database and uploads
3. **Test locally first** - Pull on local machine before cPanel
4. **Use branches** - Create feature branches for major changes
5. **Monitor `.gitignore`** - Ensure user content is protected

## 📝 Example: What Happens with Recent Cleanup

The files we just deleted locally (like `CLEANUP-COMPLETED.md`, `config/developer.php`, etc.):

- ✅ **If you commit and push these deletions** → They will be deleted on GitHub
- ✅ **If someone pulls** → Those files will be deleted on their server
- ✅ **If you pull from GitHub** → Those files will be deleted on your server

**But:**

- ✅ `storage/uploads/*` files are **SAFE** (in .gitignore)
- ✅ `config/database.php` is **SAFE** (in .gitignore)
- ✅ User-generated content is **SAFE**

---

**Remember:** Git only manages files that are **tracked** (committed). Files in `.gitignore` are **never affected** by `git pull`.
