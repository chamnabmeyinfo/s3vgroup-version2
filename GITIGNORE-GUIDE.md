# .gitignore Guide - What to Commit and What Not

## 📋 Overview

This guide explains what files should and shouldn't be committed to Git.

## ✅ Files That SHOULD Be Committed

### Source Code
- ✅ All PHP files (`*.php`)
- ✅ All CSS/JS files (`assets/css/*`, `assets/js/*`)
- ✅ All HTML/template files
- ✅ Configuration examples (`*.example` files)
- ✅ Documentation (`*.md` files)

### Configuration Examples
- ✅ `config/database.php.example`
- ✅ `config/database.live.php.example`
- ✅ `config/app.php.example`
- ✅ `deploy-config.example.json`
- ✅ `config/database.local.php` (local dev config, safe to commit)

### Project Files
- ✅ `composer.json` (dependency definitions)
- ✅ `composer.lock` (exact versions - recommended to commit)
- ✅ `artisan` (CLI tool)
- ✅ `README.md` and other docs
- ✅ `.gitignore` and `.gitattributes`

### Assets & Uploads
- ✅ `storage/uploads/` (images and assets - tracked in Git)
- ✅ `storage/design-backups/` (design version backups)

## ❌ Files That SHOULD NOT Be Committed

### Sensitive Configuration
- ❌ `config/database.php` (contains database credentials)
- ❌ `config/database.live.php` (contains live server credentials)
- ❌ `config/app.php` (may contain sensitive settings)
- ❌ `deploy-config.json` (contains FTP credentials)
- ❌ `.database-env` (current environment setting)

### Environment Files
- ❌ `.env` files (all variants)
- ❌ `.env.local`, `.env.*`

### Generated Files
- ❌ `vendor/` (Composer packages - install via `composer install`)
- ❌ `node_modules/` (Node packages)
- ❌ `composer.lock` (optional - see note below)

### Runtime Files
- ❌ `*.log` files (all log files)
- ❌ `*.cache` files (cache files)
- ❌ `storage/cache/*` (runtime cache)
- ❌ `storage/logs/*` (application logs)
- ❌ `storage/backups/*` (database backups)
- ❌ `storage/backups/file-backups/` (file pull backups)
- ❌ `storage/exports/` (database exports)

### IDE & Editor Files
- ❌ `.vscode/` (VS Code settings)
- ❌ `.idea/` (PhpStorm/IntelliJ settings)
- ❌ `*.sublime-project`, `*.sublime-workspace`

### OS Files
- ❌ `.DS_Store` (macOS)
- ❌ `Thumbs.db` (Windows)
- ❌ `Desktop.ini` (Windows)

### Temporary Files
- ❌ `*.tmp`, `*.temp`
- ❌ `*.bak`, `*.backup`
- ❌ `*.swp`, `*.swo` (Vim swap files)

### Test Files
- ❌ `test-*.php`
- ❌ `*test.php`
- ❌ `hello.php`, `fix-*.php`, `verify-*.php`, `check-*.php`

## 🤔 Special Cases

### composer.lock
**Recommendation: COMMIT IT** ✅
- Ensures everyone uses the same package versions
- Provides consistency across environments
- Currently ignored in `.gitignore` - you may want to track it

To track it, remove or comment out this line in `.gitignore`:
```
# composer.lock
```

### storage/uploads/
**Currently: TRACKED** ✅
- Images and assets are committed to Git
- This allows version control of assets
- If it gets too large, consider Git LFS or exclude it

### config/database.local.php
**Currently: TRACKED** ✅
- Local development config (usually safe)
- Contains localhost credentials (not sensitive)
- Can be committed for team consistency

## 📝 Quick Reference

### Always Commit:
```bash
git add composer.json
git add artisan
git add *.md
git add config/*.example
git add app/
git add assets/
```

### Never Commit:
```bash
# These are automatically ignored:
# - config/database.php
# - config/database.live.php
# - deploy-config.json
# - .database-env
# - vendor/
# - storage/cache/*
# - storage/logs/*
# - storage/backups/*
```

## 🔒 Security Checklist

Before committing, make sure:
- ✅ No database passwords in committed files
- ✅ No FTP credentials in committed files
- ✅ No API keys or secrets
- ✅ No `.env` files
- ✅ No `deploy-config.json` (only `.example` version)

## 🛠️ Common Commands

### Check what will be committed:
```bash
git status
```

### See ignored files:
```bash
git status --ignored
```

### Force add ignored file (if needed):
```bash
git add -f path/to/file
```

### Remove tracked file that should be ignored:
```bash
git rm --cached path/to/file
git commit -m "Remove file from tracking"
```

## 📚 Related Files

- `.gitignore` - This file defines what's ignored
- `.gitattributes` - Line ending and other Git attributes
- `composer.json` - Dependency definitions (committed)
- `deploy-config.example.json` - Example deployment config (committed)

---

**Remember:** When in doubt, check `.gitignore` or ask before committing sensitive files!
