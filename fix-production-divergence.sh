#!/bin/bash
# Fix Git Divergence on Production Server (www.s3vtgroup.com.kh)
# Run this via SSH on your production server

cd /home/s3vtgroup/public_html

echo "=== Fixing Git Divergence on Production ==="
echo "Directory: /home/s3vtgroup/public_html"
echo "Website: www.s3vtgroup.com.kh"
echo ""

# Check current status
echo "=== Current Git Status ==="
git status

echo ""
echo "=== Fetching latest changes from GitHub ==="
git fetch origin

echo ""
echo "=== Checking branch status ==="
git log --oneline --graph --all -5

echo ""
echo "=== Checking for unstaged changes ==="
if ! git diff-index --quiet HEAD --; then
    echo "⚠️  Unstaged changes detected! Stashing..."
    git stash save "Production changes before merge - $(date)"
    STASHED=true
else
    echo "✅ No unstaged changes"
    STASHED=false
fi

echo ""
echo "=== Merging remote changes (fixing divergence) ==="
# Use git merge instead of git pull to allow custom merge message
git merge origin/main --no-ff -m "Merge remote changes from GitHub - $(date '+%Y-%m-%d %H:%M:%S')"

if [ $? -eq 0 ]; then
    echo ""
    echo "=== Success! Divergence fixed. ==="
    echo ""
    echo "=== Pushing merged result ==="
    git push origin main
    
    if [ $? -eq 0 ]; then
        echo ""
        echo "=== Complete! Production is now in sync with GitHub. ==="
    else
        echo ""
        echo "=== Warning: Merge successful but push failed. ==="
        echo "You may need to push manually or check permissions."
    fi
else
    echo ""
    echo "=== Merge conflicts detected! ==="
    echo ""
    echo "Please resolve conflicts manually:"
    echo "1. Check conflicted files: git status"
    echo "2. Edit files to resolve conflicts (look for <<<<<< markers)"
    echo "3. Stage resolved files: git add ."
    echo "4. Complete merge: git commit"
    echo "5. Push: git push origin main"
    echo ""
    echo "Or if you want to discard server changes and use GitHub version:"
    echo "  git reset --hard origin/main"
    exit 1
fi

echo ""
if [ "$STASHED" = true ]; then
    echo "=== Reapplying Stashed Changes ==="
    git stash pop
    if [ $? -ne 0 ]; then
        echo "⚠️  Warning: There were conflicts when reapplying stash"
        echo "Resolve conflicts manually, then run: git stash drop"
    else
        echo "✅ Stashed changes reapplied successfully"
    fi
    echo ""
fi
echo "=== Stash Information ==="
echo "To see all stashed changes: git stash list"
echo "To restore a stash: git stash pop"
echo "To delete a stash: git stash drop stash@{0}"
