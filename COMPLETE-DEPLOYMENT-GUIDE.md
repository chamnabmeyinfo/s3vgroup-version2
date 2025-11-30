# Complete Deployment Guide - Best Solution

## 🎯 Best Method: Git + Manual Upload (Recommended)

This is the **professional standard** approach used by developers worldwide.

---

## 📦 Method 1: Git Pull + Manual Upload (BEST)

### Advantages:
- ✅ Version control
- ✅ Easy updates
- ✅ Professional workflow
- ✅ Rollback capability

### Steps:

#### 1. Push Code to GitHub
```bash
git-auto-push.bat
```

#### 2. Pull on cPanel (SSH/Terminal)
```bash
cd public_html
git pull origin main
```

#### 3. Upload Images (ZIP)
- ZIP `storage/uploads/` from localhost
- Upload to cPanel → Extract

#### 4. Update Config Files
- Edit `config/database.php` on server
- Edit `config/app.php` on server

**Time:** ~5 minutes  
**Best for:** Regular updates

---

## 📦 Method 2: All-in-One ZIP (EASIEST)

### Advantages:
- ✅ One file to upload
- ✅ No Git needed
- ✅ Simple for beginners

### Steps:

#### 1. Create Deployment Package
```bash
prepare-deployment.bat
```
This creates: `s3vgroup-deployment-YYYYMMDD.zip`

#### 2. Upload to cPanel
- File Manager → Upload ZIP
- Extract to `public_html/`

#### 3. Upload Images Separately
- ZIP `storage/uploads/` folder
- Upload and extract

#### 4. Update Config Files
- Edit `config/database.php`
- Edit `config/app.php`

**Time:** ~10 minutes  
**Best for:** First-time deployment

---

## 📦 Method 3: FTP Upload (FASTEST)

### Advantages:
- ✅ Fast for large files
- ✅ Direct upload
- ✅ Good for images

### Steps:

#### 1. Setup FTP Client
- Download FileZilla (free)
- Connect to cPanel FTP

#### 2. Upload Everything
- Upload all files to `public_html/`
- Skip: `.git/`, `node_modules/`, `vendor/`

#### 3. Update Config Files
- Edit on server via File Manager

**Time:** ~15-30 minutes  
**Best for:** Large files, slow internet

---

## 🚀 Quick Start (Choose Your Method)

### For Developers (Recommended):
**Use Method 1: Git Pull**
- Fast updates
- Version control
- Professional

### For Beginners:
**Use Method 2: ZIP Upload**
- Simple
- One file
- No Git knowledge needed

### For Large Files:
**Use Method 3: FTP**
- Fast upload
- Resume capability
- Good for images

---

## 📋 Complete Checklist

### Before Deployment:
- [ ] Code is working on localhost
- [ ] All changes committed to Git
- [ ] Database exported from localhost
- [ ] Images ready to upload

### During Deployment:
- [ ] Code uploaded to cPanel
- [ ] Images uploaded to `storage/uploads/`
- [ ] Database imported to cPanel
- [ ] Config files updated
- [ ] File permissions set (755 for folders, 644 for files)

### After Deployment:
- [ ] Website loads correctly
- [ ] Images display
- [ ] Admin panel works
- [ ] Database connection works
- [ ] No errors in logs

---

## 🔧 Automated Scripts

### 1. Prepare Deployment Package
```bash
prepare-deployment.bat
```
Creates ZIP with all code (no images/configs)

### 2. Auto Push to GitHub
```bash
git-auto-push.bat
```
Commits and pushes code automatically

### 3. Show Ignored Files
```bash
show-ignored-files.bat
```
Lists what won't be pushed

---

## 📤 What to Upload

### ✅ Upload to cPanel:
1. **Code files** (via Git or ZIP)
2. **Images** (via ZIP or FTP)
3. **Database** (via phpMyAdmin)
4. **Config files** (edit on server)

### ❌ Don't Upload:
- `.git/` folder
- `node_modules/`
- `vendor/`
- Log files
- Cache files

---

## 🎯 Recommended Workflow

### First Time Deployment:
1. Use **Method 2: ZIP** (easiest)
2. Run `prepare-deployment.bat`
3. Upload ZIP + images
4. Update configs

### Regular Updates:
1. Use **Method 1: Git** (best)
2. Push code → Pull on server
3. Upload new images if any
4. Done!

---

## 💡 Pro Tips

1. **Keep localhost as backup** - Don't delete
2. **Test on staging first** - If possible
3. **Backup before updates** - Always!
4. **Use Git for code** - Professional standard
5. **Upload images separately** - Faster

---

## 🆘 Troubleshooting

### Issue: Files missing after upload
**Fix:** Check file permissions (755 for folders)

### Issue: Images not loading
**Fix:** 
1. Upload images to `storage/uploads/`
2. Run `admin/fix-image-paths.php`
3. Check permissions

### Issue: Database connection error
**Fix:** Update `config/database.php` with cPanel credentials

---

## ✅ Summary

**Best Solution:**
1. **Code:** Git push/pull
2. **Images:** ZIP upload
3. **Database:** phpMyAdmin import
4. **Config:** Edit on server

**Time:** 5-10 minutes  
**Difficulty:** Easy  
**Professional:** Yes ✅

---

**Choose the method that works best for you! 🚀**

