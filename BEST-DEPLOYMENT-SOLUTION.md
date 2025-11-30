# Best Solution: Deploy Everything to cPanel

## 🚀 Recommended Method: Git + Manual Upload

This is the **best and most professional** approach.

---

## 📋 Step-by-Step Complete Deployment

### Step 1: Push Code to GitHub

1. **On Localhost:**
   ```bash
   git-auto-push.bat
   ```
   Or manually:
   ```bash
   git add .
   git commit -m "Deploy to production"
   git push origin main
   ```

### Step 2: Pull Code on cPanel

**Via cPanel Terminal or SSH:**
```bash
cd public_html
git pull origin main
```

**Or via cPanel File Manager:**
- Download ZIP from GitHub
- Upload and extract to `public_html`

### Step 3: Upload Images

**Option A: ZIP Upload (Easiest)**
1. **On Localhost:**
   - Go to: `C:\xampp\htdocs\s3vgroup\storage\uploads\`
   - Select all files → Right-click → Compress to ZIP
   - Name: `product-images.zip`

2. **On cPanel:**
   - File Manager → `public_html/storage/`
   - Upload `product-images.zip`
   - Right-click → Extract
   - Delete ZIP
   - Set permissions: `uploads` folder = 755

**Option B: FTP (Faster for large files)**
- Use FileZilla/WinSCP
- Upload entire `storage/uploads/` folder

### Step 4: Update Config Files

**On cPanel File Manager:**
1. Edit `config/database.php`:
   ```php
   'dbname' => 'your_cpanel_database',
   'username' => 'your_cpanel_user',
   'password' => 'your_cpanel_password',
   ```

2. Edit `config/app.php`:
   ```php
   'url' => 'https://s3vgroup.com',
   'debug' => false,
   ```

### Step 5: Import Database

1. **Export from localhost phpMyAdmin**
2. **Import to cPanel phpMyAdmin**
3. **Run fix script:** `admin/fix-image-paths.php`

---

## 🎯 Alternative: All-in-One ZIP Method

If you want to send **everything at once**:

### Step 1: Create Deployment ZIP

**On Localhost:**
1. Create a folder: `deployment-package`
2. Copy everything EXCEPT:
   - `storage/uploads/` (upload separately)
   - `storage/cache/`
   - `storage/logs/`
   - `.git/`
   - `node_modules/`
   - `vendor/`

3. ZIP the folder: `s3vgroup-deployment.zip`

### Step 2: Upload to cPanel

1. **Via File Manager:**
   - Upload `s3vgroup-deployment.zip`
   - Extract to `public_html/`
   - Delete ZIP

2. **Upload images separately:**
   - Upload `storage/uploads/` folder

3. **Update config files:**
   - Edit `config/database.php`
   - Edit `config/app.php`

---

## 🔧 Automated Script Solution

I'll create a script that prepares everything for deployment:

