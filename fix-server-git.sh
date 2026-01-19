#!/bin/bash
# Script to fix Git pull issues on server
# Run this via SSH on your server: bash fix-server-git.sh

cd /home/s3vtgroup/dev.s3vtgroup.com.kh

echo "=== Checking Git Status ==="
git status

echo ""
echo "=== Files that have changed ==="
git diff --name-only

echo ""
echo "=== Stashing changes and pulling ==="
# Stash any changes (saves them temporarily)
git stash save "Server changes before pull - $(date)"

# Pull latest changes
git pull origin main

echo ""
echo "=== Done! ==="
echo "If you need the stashed changes back, run: git stash pop"
echo "To see stashed changes: git stash list"
