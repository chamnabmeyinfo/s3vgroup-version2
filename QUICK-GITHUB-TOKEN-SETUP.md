# Quick GitHub Token Setup

## Fast Setup (3 Steps)

### 1. Create Token
- Go to: https://github.com/settings/tokens
- Click "Generate new token (classic)"
- Name: `S3VGroup Token`
- Check: **repo** scope
- Generate and **COPY THE TOKEN**

### 2. Run Setup Script
```powershell
.\setup-github-token.ps1
```
- Paste your token when prompted
- Choose method 1 (Windows Credential Manager - Recommended)

### 3. Test
```powershell
git push origin main
```

## Manual Setup (Alternative)

If you prefer to do it manually:

```powershell
# 1. Set credential helper
git config --global credential.helper manager-core

# 2. When you push/pull next time, use:
# Username: chamnabmeyinfo
# Password: [paste your token here]
```

## Your Repository
- **Remote**: https://github.com/chamnabmeyinfo/s3vgroup-version2.git
- **Username**: chamnabmeyinfo

## Need Help?
See `setup-github-token.md` for detailed instructions.
