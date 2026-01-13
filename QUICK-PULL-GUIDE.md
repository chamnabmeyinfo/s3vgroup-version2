# Quick Guide: Pull Code from cPanel

## 🚀 3 Simple Steps

### Step 1: Setup FTP Config
```bash
cp deploy-config.example.json deploy-config.json
```
Then edit `deploy-config.json` with your cPanel FTP credentials.

### Step 2: Preview (Optional)
```bash
php artisan pull:dry-run
```
See what will be pulled without downloading.

### Step 3: Pull All Files
```bash
php artisan pull
```
All code from cPanel is now on your local machine!

## 📋 What You Need

- **FTP Host**: Usually `ftp.yourdomain.com` or your server IP
- **FTP Username**: Your cPanel username
- **FTP Password**: Your cPanel password
- **Remote Path**: Usually `/public_html` or `/public_html/your-folder`

## ✅ What Gets Pulled

- All PHP files
- All CSS/JS files  
- All images and assets
- All directories

## ❌ What Gets Skipped

- Log files (*.log)
- Cache files (*.cache)
- .git directory
- node_modules
- vendor (Composer packages)

## 💡 Tips

- **Always preview first**: Use `pull:dry-run` to see what will happen
- **Local configs preserved**: Your local database config won't be overwritten
- **Backup created**: Local files are backed up before pulling
- **Safe to run**: Won't delete anything, only downloads/updates

## 🔧 Troubleshooting

**"deploy-config.json not found"**
→ Copy from `deploy-config.example.json`

**"FTP connection failed"**
→ Check your FTP credentials in `deploy-config.json`

**"Permission denied"**
→ Check write permissions in your local directory

---

**That's it!** Run `php artisan pull` to sync all code from cPanel! 🎉
