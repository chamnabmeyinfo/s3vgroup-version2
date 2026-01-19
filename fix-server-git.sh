#!/bin/bash
# Script to fix Git pull issues and divergence on server
# Run this via SSH on your server: bash fix-server-git.sh

cd /home/s3vtgroup/dev.s3vtgroup.com.kh

echo "=== Checking Git Status ==="
git status

echo ""
echo "=== Fetching latest changes ==="
git fetch origin

echo ""
echo "=== Checking for divergence ==="
LOCAL=$(git rev-parse @)
REMOTE=$(git rev-parse @{u})
BASE=$(git merge-base @ @{u})

if [ $LOCAL = $REMOTE ]; then
    echo "Branches are in sync. No action needed."
    exit 0
elif [ $LOCAL = $BASE ]; then
    echo "Local branch is behind. Pulling changes..."
    git pull origin main
elif [ $REMOTE = $BASE ]; then
    echo "Local branch is ahead. Pushing changes..."
    git push origin main
else
    echo "Branches have diverged. Merging changes..."
    echo ""
    echo "=== Stashing any uncommitted changes ==="
    git stash save "Server changes before merge - $(date)"
    
    echo ""
    echo "=== Merging remote changes ==="
    git pull origin main --no-ff -m "Merge remote changes"
    
    if [ $? -ne 0 ]; then
        echo ""
        echo "=== Merge conflicts detected! ==="
        echo "Please resolve conflicts manually:"
        echo "1. Check conflicted files: git status"
        echo "2. Edit files to resolve conflicts"
        echo "3. Stage resolved files: git add ."
        echo "4. Complete merge: git commit"
        echo "5. Push: git push origin main"
        exit 1
    fi
    
    echo ""
    echo "=== Merge successful! Pushing changes ==="
    git push origin main
    
    echo ""
    echo "=== Done! ==="
    echo "If you need the stashed changes back, run: git stash pop"
    echo "To see stashed changes: git stash list"
fi
