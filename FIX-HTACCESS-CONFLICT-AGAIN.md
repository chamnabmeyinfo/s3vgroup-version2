# Quick Fix: .htaccess Conflict (Again)

## 🚀 Fastest Solution

### In cPanel Terminal/SSH:

```bash
# 1. Go to your website directory
cd /home/username/public_html  # Replace with your actual path

# 2. Stash your .htaccess changes (saves them temporarily)
git stash

# 3. Pull the latest code
git pull origin main

# 4. Reapply your changes (if needed)
git stash pop
```

## 📋 What Each Command Does

- `git stash` - Saves your local `.htaccess` changes temporarily
- `git pull` - Downloads latest code from repository
- `git stash pop` - Reapplies your saved changes (if you need them)

## ⚠️ If You Need to Keep Your Server Changes

If your `.htaccess` has important server-specific rules that you want to keep:

```bash
# 1. Commit your changes first
git add .htaccess
git commit -m "Server-specific .htaccess configuration"

# 2. Then pull (may need to merge)
git pull origin main

# 3. If there's a merge conflict, resolve it manually
# Edit .htaccess to combine both versions
git add .htaccess
git commit -m "Merge .htaccess changes"
```

## 🎯 One-Liner Fix (Discard Local Changes)

If you just want to use the Git version and discard local changes:

```bash
git checkout -- .htaccess && git pull origin main
```

**⚠️ Warning:** This will lose your local `.htaccess` changes!

## 🔄 Better Long-Term Solution: Use .htaccess.local

To prevent this from happening repeatedly:

### Step 1: Create `.htaccess.local` for Server-Specific Rules

```bash
# Create a file for server-specific rules
touch .htaccess.local
```

### Step 2: Move Server-Specific Rules

Edit `.htaccess.local` and add any server-specific rules there.

### Step 3: Include in Main `.htaccess`

Add this to the end of your main `.htaccess`:

```apache
# Include server-specific configuration
<IfModule mod_include.c>
    IncludeOptional .htaccess.local
</IfModule>
```

### Step 4: Update `.gitignore`

Make sure `.htaccess.local` is ignored (it already is):

```gitignore
.htaccess.local
```

Now:
- Main `.htaccess` stays in sync with Git
- Server-specific rules go in `.htaccess.local` (ignored by Git)
- No more conflicts!

## 🚨 Emergency: Can't Access Terminal?

If you can't access SSH/Terminal:

1. **Use cPanel File Manager:**
   - Backup `.htaccess` first
   - Download it to your local machine
   - Edit and re-upload

2. **Use cPanel Git Interface:**
   - Go to **Git Version Control**
   - Click on your repository
   - Use **"Stash Changes"** option
   - Then pull again

## 💡 Why This Keeps Happening

Common reasons:
1. **cPanel auto-updates** `.htaccess` automatically
2. **Manual edits** on server
3. **Plugin/script changes** modify `.htaccess`
4. **Server-specific rules** needed for your hosting

## 📝 Recommended Workflow

```bash
# Before pulling, always:
git status

# If .htaccess is modified:
# Option 1: Stash (if temporary)
git stash
git pull origin main
git stash pop

# Option 2: Commit (if important)
git add .htaccess
git commit -m "Update .htaccess"
git pull origin main
```

---

**Choose the solution that fits your needs!**
