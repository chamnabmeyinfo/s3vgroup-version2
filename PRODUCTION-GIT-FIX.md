# Fix Git Divergence on Production Server

## Quick Fix Command

Run this command via SSH on your production server:

```bash
cd /home/s3vtgroup/public_html && git stash && git fetch origin && git pull origin main --no-ff
```

## Step-by-Step Instructions

### Option 1: Merge (Recommended - Preserves All Changes)

```bash
# 1. Navigate to production directory
cd /home/s3vtgroup/public_html

# 2. Stash any local changes
git stash save "Production changes before merge - $(date)"

# 3. Fetch latest from GitHub
git fetch origin

# 4. Merge with no fast-forward (fixes divergence)
git pull origin main --no-ff -m "Merge remote changes from GitHub"

# 5. If successful, push the merge
git push origin main
```

### Option 2: Rebase (Cleaner History)

```bash
cd /home/s3vtgroup/public_html
git stash
git pull --rebase origin main
git push origin main
```

### Option 3: Reset to Match GitHub (Use with Caution)

**WARNING:** This will discard any changes made directly on the server!

```bash
cd /home/s3vtgroup/public_html
git fetch origin
git reset --hard origin/main
```

## Using the Script

I've created `fix-production-divergence.sh` that you can:

1. **Upload to your server** via cPanel File Manager or FTP
2. **Make it executable:**
   ```bash
   chmod +x fix-production-divergence.sh
   ```
3. **Run it:**
   ```bash
   bash fix-production-divergence.sh
   ```

## If You Get Merge Conflicts

If the merge shows conflicts:

1. **Check which files have conflicts:**
   ```bash
   git status
   ```

2. **Edit the conflicted files** (look for `<<<<<<<`, `=======`, `>>>>>>>` markers)

3. **After resolving conflicts:**
   ```bash
   git add .
   git commit -m "Resolve merge conflicts"
   git push origin main
   ```

## Prevention

To avoid this in the future:

1. **Always pull before making changes on server:**
   ```bash
   cd /home/s3vtgroup/public_html
   git pull origin main
   ```

2. **Don't make changes directly on production** - always work locally and push

3. **Use the pull script regularly:**
   ```bash
   cd /home/s3vtgroup/public_html && git stash && git pull origin main
   ```

## Quick Reference

- **Production Path:** `/home/s3vtgroup/public_html`
- **Website:** `www.s3vtgroup.com.kh`
- **Branch:** `main`
- **Remote:** `origin` (GitHub)
