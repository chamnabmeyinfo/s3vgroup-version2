#!/bin/bash
# Fix Git Divergence Issue for cPanel
# This script helps resolve the "diverging branches" error

echo "=== Git Divergence Fix Script ==="
echo ""
echo "This script will help you fix the diverging branches issue."
echo ""

# Option 1: Merge approach (recommended for most cases)
echo "Option 1: Merge remote changes (RECOMMENDED)"
echo "This will create a merge commit combining both branches."
echo ""
echo "Commands to run:"
echo "  git pull origin main --no-ff"
echo "  git push origin main"
echo ""

# Option 2: Rebase approach (cleaner history)
echo "Option 2: Rebase your changes on top of remote (CLEANER HISTORY)"
echo "This will replay your commits on top of remote changes."
echo ""
echo "Commands to run:"
echo "  git pull --rebase origin main"
echo "  git push origin main"
echo ""

# Option 3: Force push (DANGEROUS - only if you're sure)
echo "Option 3: Force push (USE WITH CAUTION)"
echo "WARNING: This will overwrite remote changes!"
echo "Only use if you're certain the remote changes should be discarded."
echo ""
echo "Commands to run:"
echo "  git push --force-with-lease origin main"
echo ""

echo "=== For cPanel Server-Side Fix ==="
echo ""
echo "If the error persists, you may need to fix it on the server:"
echo ""
echo "1. SSH into your server"
echo "2. Navigate to your website directory"
echo "3. Run one of these commands:"
echo ""
echo "   # Option A: Merge (safest)"
echo "   git pull origin main --no-ff"
echo ""
echo "   # Option B: Reset to match remote (if you want to discard server changes)"
echo "   git fetch origin"
echo "   git reset --hard origin/main"
echo ""
echo "   # Option C: Rebase"
echo "   git pull --rebase origin main"
echo ""
