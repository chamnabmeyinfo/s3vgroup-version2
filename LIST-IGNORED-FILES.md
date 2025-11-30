# List Files NOT Pushed to GitHub

## 🚀 Quick Ways to See Ignored Files

### Option 1: Run the Script (Easiest)

**Windows:**
```bash
show-ignored-files.bat
```

**Linux/Mac:**
```bash
chmod +x show-ignored-files.sh
./show-ignored-files.sh
```

### Option 2: Git Command

```bash
git status --ignored
```

### Option 3: See All Ignored Files

```bash
git status --ignored --short
```

### Option 4: Check Specific File

```bash
git check-ignore -v path/to/file
```

---

## 📋 Complete List of Ignored Items

Based on your `.gitignore` file:

### 1. **Sensitive Configuration Files**
```
❌ config/database.php
❌ config/app.php
❌ config/under-construction.php
❌ .env
❌ .env.local
❌ *.env
```

### 2. **User-Generated Content**
```
❌ storage/uploads/*          (all product images)
❌ storage/cache/*            (cached files)
❌ storage/logs/*             (log files)
❌ storage/backups/*          (backup files)
```

### 3. **Dependencies**
```
❌ vendor/                    (Composer packages)
❌ node_modules/              (NPM packages)
❌ composer.lock
❌ package-lock.json
❌ yarn.lock
```

### 4. **Temporary Files**
```
❌ *.log
❌ *.cache
❌ *.tmp
❌ *.temp
❌ *.bak
❌ *.backup
```

### 5. **System Files**
```
❌ .DS_Store                  (Mac)
❌ Thumbs.db                  (Windows)
❌ .vscode/                   (VS Code settings)
❌ .idea/                     (PHPStorm settings)
❌ *.sublime-project
❌ *.sublime-workspace
```

### 6. **Test Files**
```
❌ test-*.php
❌ *test.php
❌ hello.php
❌ fix-*.php
❌ verify-*.php
❌ check-*.php
```

### 7. **Build Files**
```
❌ dist/
❌ build/
```

### 8. **Documentation (Generated)**
```
❌ *.docx
❌ *.pdf
```

---

## 🔍 How to Check What's Ignored

### See All Ignored Files:
```bash
git status --ignored
```

### See Ignored Files in Specific Directory:
```bash
git status --ignored storage/uploads/
```

### Check if Specific File is Ignored:
```bash
git check-ignore -v config/database.php
```

### See Only Ignored Patterns (from .gitignore):
```bash
cat .gitignore | grep -v "^#" | grep -v "^$"
```

---

## 📊 Summary

**Total Ignored Categories:** 8
- Configuration files (3 types)
- Storage files (4 directories)
- Dependencies (5 types)
- Temporary files (6 types)
- System files (6 types)
- Test files (6 patterns)
- Build files (2 directories)
- Generated docs (2 types)

---

## 💡 Why These Are Ignored

| Category | Reason |
|----------|--------|
| **Config Files** | Contains passwords & sensitive data |
| **Images** | Large files, user-generated |
| **Dependencies** | Can be reinstalled |
| **Logs/Cache** | Temporary, auto-generated |
| **System Files** | OS/IDE specific, not needed |
| **Test Files** | Temporary debugging files |

---

## ✅ What IS Pushed

- ✅ All `.php` code files
- ✅ Configuration examples (`.example` files)
- ✅ Documentation (`.md` files)
- ✅ SQL schema files
- ✅ Project structure

---

**Run the script to see the full list! 📋**

