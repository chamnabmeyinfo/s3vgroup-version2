#!/bin/bash
# Quick Fix for Production Server Divergence Error
# Repository: /home/s3vtgroup/public_html
# Run via SSH: bash PRODUCTION-QUICK-FIX.sh

echo "=========================================="
echo "Quick Fix: Production Server Divergence"
echo "Repository: /home/s3vtgroup/public_html"
echo "=========================================="
echo ""

# Navigate to repository
cd /home/s3vtgroup/public_html || {
    echo "❌ Error: Cannot access repository directory"
    exit 1
}

echo "📂 Current directory: $(pwd)"
echo ""

# Check Git status
echo "📊 Checking Git status..."
git status --short
echo ""

# Fetch latest
echo "⬇️  Fetching latest from GitHub..."
git fetch origin
echo ""

# Stash changes if any
echo "📦 Checking for uncommitted changes..."
if ! git diff-index --quiet HEAD --; then
    echo "⚠️  Unstaged changes detected! Stashing..."
    git stash save "Auto-stash before merge - $(date '+%Y-%m-%d %H:%M:%S')"
    STASHED=true
else
    echo "✅ No uncommitted changes"
    STASHED=false
fi
echo ""

# Merge with no fast-forward
echo "🔄 Merging remote changes (fixing divergence)..."
git pull origin main --no-ff -m "Merge remote changes from GitHub - $(date '+%Y-%m-%d %H:%M:%S')"

if [ $? -eq 0 ]; then
    echo "✅ Merge successful!"
    echo ""
    
    # Push the merged result
    echo "⬆️  Pushing merged result to GitHub..."
    git push origin main
    
    if [ $? -eq 0 ]; then
        echo "✅ Push successful!"
        echo ""
        echo "=========================================="
        echo "✅ SUCCESS! Production is now in sync."
        echo "=========================================="
    else
        echo "⚠️  Warning: Merge successful but push failed"
        echo "You may need to push manually or check permissions"
    fi
else
    echo ""
    echo "❌ Merge conflicts detected!"
    echo ""
    echo "Please resolve conflicts manually:"
    echo "1. Check conflicted files: git status"
    echo "2. Edit files to resolve conflicts (look for <<<<<< markers)"
    echo "3. Stage resolved files: git add ."
    echo "4. Complete merge: git commit"
    echo "5. Push: git push origin main"
    echo ""
    echo "Or discard server changes and use GitHub version:"
    echo "  git reset --hard origin/main"
    exit 1
fi

# Reapply stash if needed
if [ "$STASHED" = true ]; then
    echo ""
    echo "📦 Reapplying stashed changes..."
    git stash pop
    if [ $? -ne 0 ]; then
        echo "⚠️  Warning: There were conflicts when reapplying stash"
        echo "Resolve conflicts manually, then run: git stash drop"
    else
        echo "✅ Stashed changes reapplied successfully"
    fi
fi

echo ""
echo "=========================================="
echo "✅ Process completed!"
echo "=========================================="
echo ""
echo "Current Git status:"
git status --short
echo ""
