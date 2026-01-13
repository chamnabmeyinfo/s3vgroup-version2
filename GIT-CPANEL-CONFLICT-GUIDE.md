# Fixing Git Pull Conflicts in cPanel

## 🔴 The Problem

When you try to pull from Git in cPanel, you get this error:

```
error: Your local changes to the following files would be overwritten by merge: .htaccess
Please commit your changes or stash them before you merge. Aborting
```

**What this means:**
- Your `.htaccess` file on the server has been modified
- Git won't overwrite your local changes when pulling
- You need to handle the conflict first

## ✅ Solutions

### Solution 1: Stash Changes (Recommended for Temporary Changes)

**In cPanel Terminal/SSH:**
```bash
cd /home/username/public_html  # or your website path
git stash
git pull
git stash pop  # Reapply your changes after pull
```

**Or via cPanel Git Interface:**
1. Go to **Git Version Control** in cPanel
2. Click on your repository
3. Use **"Stash Changes"** option (if available)
4. Then pull again

### Solution 2: Commit Your Changes First

**In cPanel Terminal/SSH:**
```bash
cd /home/username/public_html
git add .htaccess
git commit -m "Update .htaccess for server configuration"
git pull
```

**Note:** This will create a commit with your server-specific changes.

### Solution 3: Discard Local Changes (If You Don't Need Them)

**⚠️ WARNING: This will lose your local changes!**

```bash
cd /home/username/public_html
git checkout -- .htaccess
git pull
```

### Solution 4: Use Merge Strategy (Keep Both Changes)

```bash
cd /home/username/public_html
git stash
git pull
git stash pop
# Manually resolve any conflicts in .htaccess
```

## 🎯 Best Practice: Separate Server-Specific Config

### Option A: Use `.htaccess.local` for Server-Specific Rules

1. **Update `.gitignore`** to ignore server-specific files:
   ```gitignore
   .htaccess.local
   .htaccess.server
   ```

2. **Create `.htaccess.local`** on server with server-specific rules

3. **Include it in main `.htaccess`**:
   ```apache
   # Server-specific configuration
   <IfModule mod_rewrite.c>
       # Include server-specific rules if they exist
       # (cPanel will handle this automatically)
   </IfModule>
   
   # Include local overrides if file exists
   <IfModule mod_include.c>
       IncludeOptional .htaccess.local
   </IfModule>
   ```

### Option B: Make `.htaccess` Server-Specific Only

If `.htaccess` should be different on each server:

1. **Add to `.gitignore`:**
   ```gitignore
   .htaccess
   !.htaccess.example
   ```

2. **Create `.htaccess.example`** with template

3. **Copy on each server:**
   ```bash
   cp .htaccess.example .htaccess
   # Then customize for that server
   ```

## 🛠️ Quick Fix Commands

### For cPanel Terminal/SSH:

```bash
# Navigate to your website directory
cd /home/username/public_html  # Replace with your actual path

# See what changed
git status

# Option 1: Stash and pull (safest)
git stash
git pull origin main  # or master, depending on your branch
git stash pop

# Option 2: Commit changes first
git add .htaccess
git commit -m "Server-specific .htaccess changes"
git pull origin main

# Option 3: Discard changes (if not needed)
git checkout -- .htaccess
git pull origin main
```

## 📋 Step-by-Step: Using cPanel Git Interface

If you're using cPanel's Git Version Control interface:

1. **Go to Git Version Control** in cPanel
2. **Click on your repository**
3. **Check "Status"** to see modified files
4. **Choose an action:**
   - **Stash** - Temporarily save changes
   - **Commit** - Save changes to Git
   - **Discard** - Remove local changes
5. **Then Pull** again

## 🔍 Why This Happens

Common reasons `.htaccess` gets modified on server:

1. **cPanel auto-updates** - cPanel may modify `.htaccess` automatically
2. **Manual edits** - You or someone edited it directly on server
3. **Plugin/script changes** - Some scripts modify `.htaccess`
4. **Server-specific rules** - Different server configurations

## 💡 Prevention Tips

1. **Always use Git** - Don't edit files directly on server
2. **Use `.htaccess.example`** - Template file in Git
3. **Document changes** - Note why server-specific rules exist
4. **Regular sync** - Pull/push regularly to avoid large conflicts

## 🚨 Emergency: Can't Access Terminal?

If you can't access SSH/Terminal, you can:

1. **Use cPanel File Manager:**
   - Backup `.htaccess` first
   - Download it to your local machine
   - Edit and re-upload

2. **Use FTP:**
   - Download `.htaccess`
   - Make a backup
   - Edit locally
   - Re-upload

3. **Contact Hosting Support:**
   - They can help with Git commands
   - Or restore from backup

## 📝 Recommended Workflow

```bash
# 1. Before pulling, always check status
git status

# 2. If there are changes, decide:
#    - Keep them? → Commit first
#    - Temporary? → Stash
#    - Not needed? → Discard

# 3. Then pull
git pull origin main

# 4. If you stashed, reapply
git stash pop
```

## 🔗 Related Files

- `.gitignore` - Controls what Git tracks
- `.htaccess` - Apache configuration (currently tracked)
- `.htaccess.local` - Server-specific overrides (can be ignored)

---

**Need help?** Check which solution fits your situation and follow the steps above!
