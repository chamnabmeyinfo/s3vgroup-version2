# GitHub Token Setup for Deployment

## Why Use a Token?

Using a GitHub Personal Access Token (PAT) instead of password authentication:
- ✅ No popup asking to choose account
- ✅ Works with automated scripts
- ✅ More secure than passwords
- ✅ Can be revoked easily
- ✅ Better for CI/CD

---

## Step 1: Create a GitHub Personal Access Token

1. **Go to GitHub Settings:**
   - Visit: https://github.com/settings/tokens
   - Or: GitHub → Your Profile → Settings → Developer settings → Personal access tokens → Tokens (classic)

2. **Generate New Token:**
   - Click "Generate new token" → "Generate new token (classic)"
   - Give it a name: `S3VGroup Deployment`
   - Set expiration (recommend: 90 days or custom)

3. **Select Permissions:**
   - ✅ **repo** (Full control of private repositories)
     - This includes: `repo:status`, `repo_deployment`, `public_repo`, `repo:invite`, `security_events`
   - That's all you need for pushing code!

4. **Generate Token:**
   - Click "Generate token" at the bottom
   - **⚠️ IMPORTANT:** Copy the token immediately! You won't see it again!
   - It looks like: `ghp_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`

---

## Step 2: Add Token to Config

1. **Open `deploy-config.json`** (not the example file)

2. **Add your token:**
   ```json
   {
     "git": {
       "enabled": true,
       "branch": "main",
       "auto_commit": true,
       "commit_message": "Auto deploy: {timestamp}",
       "token": "ghp_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
     }
   }
   ```

3. **Save the file**

---

## Step 3: Test It!

Run:
```bash
deploy.bat
```

You should **NOT** see any popup asking to choose a GitHub account!

---

## Security Notes

### ✅ Good Practices:
- ✅ Token is stored in `deploy-config.json` (which is in `.gitignore`)
- ✅ Token is never exposed in logs or error messages
- ✅ Token is removed from remote URL after push
- ✅ Use token with minimal required permissions

### ⚠️ Important:
- **Never commit `deploy-config.json` to Git!**
- Keep your token secret
- If token is exposed, revoke it immediately on GitHub
- Rotate tokens periodically (every 90 days recommended)

---

## Troubleshooting

### Still seeing popup?
- Check that token is in `deploy-config.json` (not just example file)
- Verify token has `repo` permissions
- Check token hasn't expired

### "Authentication failed" error?
- Token might be expired → Generate new one
- Token might not have `repo` permission → Regenerate with correct permissions
- Check token is copied correctly (no extra spaces)

### "Permission denied" error?
- Make sure token has `repo` scope enabled
- Verify you have push access to the repository

---

## Alternative: Use SSH Instead

If you prefer SSH keys instead of tokens:

1. **Set up SSH key** (if not already):
   ```bash
   ssh-keygen -t ed25519 -C "your_email@example.com"
   ```

2. **Add to GitHub:**
   - Copy `~/.ssh/id_ed25519.pub`
   - GitHub → Settings → SSH and GPG keys → New SSH key

3. **Change remote URL:**
   ```bash
   git remote set-url origin git@github.com:chamnabmeyinfo/s3vgroup-version2.git
   ```

4. **Remove token from config** (not needed with SSH)

---

## Quick Reference

**Token Format:** `ghp_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`

**Required Permission:** `repo` (Full control of private repositories)

**Config Location:** `deploy-config.json` → `git.token`

**Test Command:** `deploy.bat`

---

## Need Help?

If you're having issues:
1. Check token is valid: https://github.com/settings/tokens
2. Verify token has `repo` permission
3. Make sure `deploy-config.json` has the token (not just example file)
4. Check deploy log: `deploy-log.txt`

