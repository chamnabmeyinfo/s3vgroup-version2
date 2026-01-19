# How to Fix Git Divergence Error in cPanel

## The Problem
You're getting this error when trying to push from cPanel:
```
Error: Diverging branches can't be fast-forwarded
fatal: Not possible to fast-forward, aborting.
```

This happens when your server's Git branch has different commits than what you're trying to push.

## Solution Options

### Option 1: Fix via cPanel File Manager (Easiest)

1. **Login to cPanel**
2. **Go to File Manager**
3. **Navigate to your website root directory** (usually `public_html` or `domains/yourdomain.com/public_html`)
4. **Open Terminal** (if available in cPanel) or use **SSH Access**

### Option 2: Fix via SSH (Recommended)

If you have SSH access, connect to your server and run:

```bash
# Navigate to your website directory
cd ~/public_html/s3vtgroup.com.kh
# OR
cd ~/domains/s3vtgroup.com.kh/public_html

# Fetch latest changes
git fetch origin

# Merge remote changes (safest option)
git pull origin main --no-ff -m "Merge remote changes"

# If there are conflicts, resolve them, then:
# git add .
# git commit -m "Resolve merge conflicts"

# Push the merged result
git push origin main
```

### Option 3: Rebase (Cleaner History)

If you prefer a cleaner commit history:

```bash
cd ~/public_html/s3vtgroup.com.kh

# Fetch and rebase
git pull --rebase origin main

# Push
git push origin main
```

### Option 4: Reset Server to Match Local (Use with Caution)

**WARNING:** This will discard any changes made directly on the server!

```bash
cd ~/public_html/s3vtgroup.com.kh

# Fetch latest
git fetch origin

# Reset to match your local branch
git reset --hard origin/main

# Pull your local changes
git pull origin main
```

### Option 5: Force Push (Last Resort)

**WARNING:** Only use if you're 100% sure you want to overwrite server changes!

```bash
# From your LOCAL machine (not server)
git push --force-with-lease origin main
```

## Prevention Tips

1. **Always pull before pushing:**
   ```bash
   git pull origin main
   git push origin main
   ```

2. **Don't make changes directly on the server** - always work locally and push

3. **Use a staging branch** for testing before merging to main

## Quick Fix Script

I've created a script `fix-server-git-divergence.sh` that you can upload to your server and run via SSH.

## Still Having Issues?

If none of these work, you may need to:
1. Contact your hosting provider
2. Use cPanel's Git interface to reset the repository
3. Create a fresh clone and copy your files
