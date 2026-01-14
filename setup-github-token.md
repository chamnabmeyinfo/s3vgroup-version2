# GitHub Token Authentication Setup Guide

## Step 1: Create a GitHub Personal Access Token (PAT)

1. Go to GitHub: https://github.com/settings/tokens
2. Click **"Generate new token"** → **"Generate new token (classic)"**
3. Give it a name: `S3VGroup Project Token`
4. Set expiration: Choose your preferred duration (90 days, 1 year, or no expiration)
5. Select scopes (permissions):
   - ✅ **repo** (Full control of private repositories)
     - This includes: repo:status, repo_deployment, public_repo, repo:invite, security_events
   - ✅ **workflow** (if you use GitHub Actions)
6. Click **"Generate token"**
7. **IMPORTANT**: Copy the token immediately - you won't be able to see it again!
   - Example: `ghp_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`

## Step 2: Configure Git to Use the Token

### Option A: Store Token in Git Credential Helper (Recommended)

This method stores your token securely in Windows Credential Manager.

1. Run the setup script: `setup-github-token.ps1`
2. Or manually:
   ```powershell
   # Set credential helper to manager (Windows Credential Manager)
   git config --global credential.helper manager-core
   
   # When you push/pull next time, use your GitHub username and the token as password
   # Username: chamnabmeyinfo
   # Password: [paste your token here]
   ```

### Option B: Embed Token in Remote URL (Quick but less secure)

⚠️ **Warning**: This stores the token in `.git/config` file (visible in repository)

```powershell
# Replace YOUR_TOKEN with your actual token
git remote set-url origin https://YOUR_TOKEN@github.com/chamnabmeyinfo/s3vgroup-version2.git
```

### Option C: Use Token in URL Format (Recommended for scripts)

```powershell
# Format: https://USERNAME:TOKEN@github.com/OWNER/REPO.git
git remote set-url origin https://chamnabmeyinfo:YOUR_TOKEN@github.com/chamnabmeyinfo/s3vgroup-version2.git
```

## Step 3: Test the Setup

```powershell
# Test by fetching (won't actually change anything)
git fetch origin

# If successful, try pushing
git push origin main
```

## Security Notes

- ✅ **DO**: Store tokens in Windows Credential Manager (Option A)
- ✅ **DO**: Use tokens with minimal required permissions
- ✅ **DO**: Set token expiration dates
- ❌ **DON'T**: Commit tokens to Git repositories
- ❌ **DON'T**: Share tokens publicly
- ❌ **DON'T**: Use passwords - GitHub deprecated password authentication

## Troubleshooting

### If you get "Authentication failed":
1. Verify your token is correct
2. Check token hasn't expired
3. Ensure token has `repo` scope
4. Try regenerating the token

### If you get "Permission denied":
1. Verify you have access to the repository
2. Check the token has correct scopes
3. Ensure you're using the correct username

### To remove stored credentials:
```powershell
# Remove from Windows Credential Manager
# Go to: Control Panel → Credential Manager → Windows Credentials
# Find: git:https://github.com
# Delete it

# Or reset remote URL
git remote set-url origin https://github.com/chamnabmeyinfo/s3vgroup-version2.git
```
