# How to Get GitHub Personal Access Token

## Quick Steps

### 1. Go to GitHub Token Settings
Visit: **https://github.com/settings/tokens**

Or navigate:
- Click your profile picture (top right)
- Click **Settings**
- In left sidebar, click **Developer settings**
- Click **Personal access tokens**
- Click **Tokens (classic)**

### 2. Generate New Token
1. Click **"Generate new token"** button
2. Select **"Generate new token (classic)"**

### 3. Configure Token
- **Note**: Enter a description like "Deployment Token" or "S3VGroup Deployment"
- **Expiration**: Choose:
  - **90 days** (recommended for security)
  - **1 year**
  - **No expiration** (less secure but convenient)
- **Select scopes**: Check the **`repo`** checkbox (this gives full control of private repositories)

### 4. Generate and Copy
1. Scroll down and click **"Generate token"** button
2. **IMPORTANT**: Copy the token immediately! It looks like: `ghp_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`
3. You won't be able to see it again after you leave this page

### 5. Add to deploy-config.json
Open `deploy-config.json` and replace `YOUR_GITHUB_TOKEN_HERE` with your actual token:

```json
"git": {
  "token": "ghp_your_actual_token_here"
}
```

## Security Notes

⚠️ **Keep your token secret!**
- Never commit the token to GitHub
- Don't share it publicly
- If token is exposed, revoke it immediately and create a new one

✅ **Token is already excluded from Git:**
- `deploy-config.json` should be in `.gitignore` or excluded from commits

## Verify Token Works

After adding the token, test it:
```bash
git push origin main
```

If it works without prompts, your token is configured correctly!

## Revoke Token (if needed)

If you need to revoke a token:
1. Go to: https://github.com/settings/tokens
2. Find your token in the list
3. Click **"Revoke"** button

## Alternative: Use SSH Instead

If you prefer not to use tokens, you can use SSH:

1. **Generate SSH key** (if you don't have one):
   ```bash
   ssh-keygen -t ed25519 -C "your_email@example.com"
   ```

2. **Add SSH key to GitHub**:
   - Copy your public key: `cat ~/.ssh/id_ed25519.pub`
   - Go to: https://github.com/settings/keys
   - Click "New SSH key"
   - Paste your public key

3. **Change remote URL to SSH**:
   ```bash
   git remote set-url origin git@github.com:chamnabmeyinfo/s3vgroup-version2.git
   ```

4. **Test SSH connection**:
   ```bash
   ssh -T git@github.com
   ```

## Troubleshooting

**Token not working?**
- Make sure you copied the entire token (starts with `ghp_`)
- Check that `repo` scope is selected
- Verify token hasn't expired
- Check token in deploy-config.json has no extra spaces

**Still getting prompts?**
- Make sure `suppress_prompts` is set to `true` in config
- Check that environment variables are being set (see deploy-git.php)
- Try using SSH instead (see Alternative section above)
