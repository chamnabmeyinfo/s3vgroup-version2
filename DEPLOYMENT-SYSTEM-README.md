# One-Click Deployment System - User Guide

## 🚀 Quick Start

### Step 1: Setup Configuration

1. **Copy example config:**
   ```bash
   copy deploy-config.example.json deploy-config.json
   ```

2. **Edit `deploy-config.json`:**
   - Add your FTP credentials
   - Configure upload settings
   - Set Git branch

### Step 2: Deploy!

**Just double-click:** `deploy.bat`

That's it! The system will:
1. ✅ Push code to GitHub
2. ✅ Upload images via FTP
3. ✅ Show progress
4. ✅ Log everything

---

## ⚙️ Configuration

### FTP Settings:
```json
"ftp": {
  "host": "ftp.s3vgroup.com",
  "username": "your_username",
  "password": "your_password",
  "port": 21,
  "remote_path": "/public_html"
}
```

### Upload Settings:
```json
"upload": {
  "images": true,      // Upload product images
  "configs": false,    // Upload config files (usually false)
  "others": false,     // Upload other ignored files
  "dry_run": false     // Test mode (doesn't actually upload)
}
```

---

## 📋 What Gets Uploaded

### Automatically Uploaded:
- ✅ All images in `storage/uploads/`
- ✅ Files detected from `.gitignore`

### Never Uploaded:
- ❌ `.git/` folder
- ❌ `node_modules/`
- ❌ `vendor/`
- ❌ Log files
- ❌ Cache files

### Optional (Configurable):
- ⚠️ Config files (set `"configs": true` to enable)

---

## 🔍 Features

### Smart Detection:
- ✅ Reads `.gitignore` automatically
- ✅ Only uploads changed files
- ✅ Categorizes files (images, configs, etc.)

### Safety:
- ✅ Logs all operations
- ✅ Shows progress
- ✅ Error handling
- ✅ Dry-run mode

### Progress Display:
```
[1/3] Pushing to GitHub...
  ✓ Checking changes
  ✓ Adding files
  ✓ Committing
  ✓ Pushing to origin/main

[2/3] Uploading via FTP...
  ✓ Connecting to server
  ✓ Found 25 file(s) to upload
  ✓ Uploading: image1.jpg...
  ✓ Uploaded

[3/3] Finalizing...
  ✓ Deployment Complete!
```

---

## 🆘 Troubleshooting

### Error: "PHP not found"
**Fix:** Install PHP and add to PATH, or use full path to PHP

### Error: "FTP connection failed"
**Fix:** 
- Check FTP credentials
- Verify FTP server is accessible
- Check firewall settings

### Error: "Git push failed"
**Fix:**
- Check Git is initialized
- Verify remote is set: `git remote -v`
- Check GitHub credentials

### Files not uploading
**Fix:**
- Check `upload` settings in config
- Verify files exist locally
- Check exclude patterns

---

## 📝 Logs

All operations are logged to: `deploy-log.txt`

Check this file for:
- What was pushed
- What was uploaded
- Any errors
- Timestamps

---

## 🔐 Security

### FTP Password:
- Stored in `deploy-config.json`
- **Don't commit this file to Git!**
- Add to `.gitignore` if needed

### Best Practice:
- Keep `deploy-config.json` local only
- Use strong FTP passwords
- Consider SFTP if available

---

## 🎯 Usage Examples

### Normal Deployment:
```bash
deploy.bat
```

### Test Mode (Dry Run):
Edit `deploy-config.json`:
```json
"upload": {
  "dry_run": true
}
```

### Upload Only Images:
Edit `deploy-config.json`:
```json
"upload": {
  "images": true,
  "configs": false,
  "others": false
}
```

---

## ✅ Checklist

Before first use:
- [ ] PHP installed and in PATH
- [ ] Git initialized and remote set
- [ ] FTP credentials configured
- [ ] `deploy-config.json` created
- [ ] Test with dry-run mode

---

## 💡 Tips

1. **Test First:** Use `dry_run: true` to test
2. **Check Logs:** Review `deploy-log.txt` after each deploy
3. **Backup:** System creates backups automatically
4. **Incremental:** Only uploads changed files
5. **Fast:** Smart detection = faster uploads

---

## 🚀 That's It!

**One click = Code pushed + Images uploaded!**

Enjoy your automated deployment! 🎉

