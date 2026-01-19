# Git Tracking Guide - What to Push/Pull

## ✅ SHOULD BE PUSHED TO GITHUB (Tracked in Git)

### Source Code
- ✅ All PHP files (`.php`)
- ✅ All JavaScript files (`.js`)
- ✅ All CSS files (`.css`)
- ✅ HTML templates
- ✅ Database schema files (`database/*.sql`)
- ✅ Composer files (`composer.json`, `composer.lock`)

### Configuration Examples
- ✅ `config/*.example` files (templates)
- ✅ `config/database.php.example`
- ✅ `config/app.php.example`
- ✅ `config/under-construction.php.example`
- ✅ `deploy-config.example.json`

### Deployment Scripts
- ✅ `pull-production.sh` (useful for server deployment)
- ✅ `fix-server-git.sh` (useful for fixing Git issues)
- ✅ `fix-production-divergence.sh` (useful for fixing divergence)

### Documentation
- ✅ `README.md`
- ✅ `LICENSE`
- ✅ `GIT-FIX-INSTRUCTIONS.md`
- ✅ `PRODUCTION-GIT-FIX.md`
- ✅ `GIT-TRACKING-GUIDE.md` (this file)

### Assets (Currently Tracked)
- ✅ `storage/uploads/*` - **DECISION NEEDED** (see below)
- ✅ `storage/design-backups/*` - **DECISION NEEDED** (see below)

---

## ❌ SHOULD NOT BE PUSHED (Ignored by Git)

### Sensitive Configuration Files
- ❌ `config/database.php` (contains live database credentials)
- ❌ `config/app.php` (may contain sensitive URLs/settings)
- ❌ `config/under-construction.php` (may contain settings)
- ❌ `config/database.live.php` (contains production credentials)
- ❌ `.env` files (environment variables)
- ❌ `deploy-config.json` (may contain deployment secrets)

### Runtime Generated Files
- ❌ `storage/cache/*` (generated at runtime)
- ❌ `storage/logs/*` (generated at runtime)
- ❌ `storage/backups/*` (generated, can be large)
- ❌ `storage/exports/*` (database exports)

### Developer Files
- ❌ `chamnabnote/*` (developer notes - personal)
- ❌ `developer/chamnab notepad/` (developer notes)
- ❌ `developer/*/note` (developer notes)
- ❌ Test files (`test-*.php`, `*test.php`, `hello.php`, `fix-*.php`, `verify-*.php`, `check-*.php`)

### IDE & OS Files
- ❌ `.vscode/`, `.idea/` (IDE settings)
- ❌ `.DS_Store`, `Thumbs.db` (OS files)
- ❌ `*.sublime-project`, `*.sublime-workspace`

### Dependencies
- ❌ `vendor/` (Composer dependencies - install via `composer install`)
- ❌ `node_modules/` (if any - install via `npm install`)

### Temporary Files
- ❌ `*.tmp`, `*.temp`, `*.bak`, `*.backup`
- ❌ `*.log`, `*.cache`

### Security Files
- ❌ `*.token`, `*.pat` (API tokens)
- ❌ `config.local`

---

## 🤔 DECISIONS NEEDED

### 1. Storage/Uploads Directory

**Current Status:** Tracked in Git

**Options:**

**Option A: Keep Tracking (Current)**
- ✅ Pros: All images/assets are version controlled, easy to restore
- ❌ Cons: Repository can become very large, slower clones/pulls

**Option B: Ignore Uploads**
- ✅ Pros: Smaller repository, faster operations
- ❌ Cons: Need separate backup strategy for images

**Recommendation:** If uploads are < 100MB, keep tracking. If larger, consider ignoring and using a separate backup solution.

### 2. Storage/Design-Backups Directory

**Current Status:** Tracked in Git

**Options:**

**Option A: Keep Tracking**
- ✅ Pros: Version history of design changes
- ❌ Cons: Can make repository large

**Option B: Ignore**
- ✅ Pros: Smaller repository
- ❌ Cons: Lose version history

**Recommendation:** Ignore if backups are large. Keep only if they're small and valuable for history.

### 3. Deployment Scripts

**Current Status:** Tracked (good!)

**Recommendation:** ✅ Keep tracking - these are useful for deployment

---

## 📋 Recommended .gitignore Updates

Based on the analysis, here are recommended additions:

```gitignore
# Large upload files (if you decide to ignore uploads)
# storage/uploads/*.jpg
# storage/uploads/*.png
# storage/uploads/*.webp
# !storage/uploads/.gitkeep
# !storage/uploads/.htaccess

# Design backups (if you decide to ignore)
# storage/design-backups/*

# Additional temporary directories
storage/catalogs/*
!storage/catalogs/.gitkeep
storage/qrcodes/*
!storage/qrcodes/.gitkeep

# Scripts that might contain sensitive info (review these)
# fix-*.sh (keep if they don't contain secrets)
# pull-*.sh (keep if they don't contain secrets)
```

---

## 🔒 Security Checklist

Before pushing to GitHub, ensure:

- [ ] No database passwords in tracked files
- [ ] No API keys in tracked files
- [ ] No `.env` files committed
- [ ] No `config/database.php` committed
- [ ] No `config/app.php` committed (unless it's safe)
- [ ] No token files (`.token`, `.pat`) committed
- [ ] Review all shell scripts for hardcoded credentials

---

## 🚀 Deployment Workflow

### Local Development → GitHub
1. Commit code changes
2. Push to GitHub
3. Sensitive configs stay local (ignored by Git)

### GitHub → Production Server (cPanel)
1. SSH to server
2. Run: `cd /home/s3vtgroup/public_html && git pull origin main`
3. Config files (`config/database.php`, etc.) remain on server (not overwritten)

---

## 💡 Best Practices

1. **Always use `.example` files** for configuration templates
2. **Never commit sensitive data** - use environment variables or ignored config files
3. **Keep repository size manageable** - ignore large generated files
4. **Document what's ignored** - update this guide when adding new ignores
5. **Review before committing** - use `git status` to see what will be committed
