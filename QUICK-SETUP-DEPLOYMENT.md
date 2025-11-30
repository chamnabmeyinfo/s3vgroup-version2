# Quick Setup: One-Click Deployment System

## 🚀 Setup in 3 Steps

### Step 1: Create Config File

1. **Copy example:**
   ```bash
   copy deploy-config.example.json deploy-config.json
   ```

2. **Edit `deploy-config.json`:**
   - Open in text editor
   - Add your FTP credentials:
     ```json
     "host": "ftp.s3vgroup.com",
     "username": "your_ftp_username",
     "password": "your_ftp_password",
     "remote_path": "/public_html"
     ```

### Step 2: Verify PHP

Make sure PHP is installed:
```bash
php --version
```

If not installed, download from: https://windows.php.net/download/

### Step 3: Deploy!

**Double-click:** `deploy.bat`

---

## ✅ That's It!

The system will:
1. Push code to GitHub
2. Upload images via FTP
3. Show progress
4. Log everything

---

## 📋 First-Time Checklist

- [ ] PHP installed (`php --version` works)
- [ ] Git initialized (`git remote -v` shows your repo)
- [ ] `deploy-config.json` created with FTP credentials
- [ ] Test: Run `deploy.bat`

---

## 🎯 What Happens When You Run

```
Click deploy.bat
  ↓
[1] Check PHP & Config
  ↓
[2] Push Code to GitHub
  - Check changes
  - Add files
  - Commit
  - Push
  ↓
[3] Upload Images via FTP
  - Connect to server
  - Find images
  - Upload files
  - Set permissions
  ↓
[4] Show Summary
  - Files pushed
  - Files uploaded
  - Any errors
```

---

## 🔧 Configuration Options

### Upload Images Only (Default):
```json
"upload": {
  "images": true,
  "configs": false,
  "others": false
}
```

### Test Mode (Dry Run):
```json
"upload": {
  "dry_run": true
}
```

### Disable Git Push:
```json
"git": {
  "enabled": false
}
```

### Disable FTP Upload:
```json
"ftp": {
  "enabled": false
}
```

---

## 🆘 Common Issues

### "PHP not found"
- Install PHP
- Add to Windows PATH
- Or edit `deploy.bat` with full PHP path

### "FTP connection failed"
- Check credentials in `deploy-config.json`
- Verify FTP server is accessible
- Try connecting with FileZilla first

### "Git push failed"
- Check: `git remote -v`
- Verify GitHub credentials
- Make sure you have changes to push

---

## 📝 Files Created

- `deploy.bat` - Main script (double-click this)
- `deploy-main.php` - Orchestrator
- `deploy-git.php` - Git operations
- `deploy-ftp.php` - FTP operations
- `deploy-utils.php` - Helper functions
- `deploy-config.example.json` - Config template
- `deploy-log.txt` - Log file (auto-created)

---

## 🎉 Ready to Use!

**Just configure and click!** 🚀

