#!/bin/bash
# Server-Side Git Divergence Fix
# Run this on your server via SSH

echo "=== Fixing Git Divergence on Server ==="
echo ""

# Navigate to your website directory (adjust path as needed)
cd /home/yourusername/public_html/s3vtgroup.com.kh || cd /home/yourusername/domains/s3vtgroup.com.kh/public_html

# Fetch latest changes
echo "Fetching latest changes..."
git fetch origin

# Check current status
echo ""
echo "Current status:"
git status

# Option A: Merge remote changes (safest - preserves all changes)
echo ""
echo "Merging remote changes..."
git pull origin main --no-ff -m "Merge remote changes"

# If merge conflicts occur, resolve them, then:
# git add .
# git commit -m "Resolve merge conflicts"

# Push the merged result
echo ""
echo "Pushing merged changes..."
git push origin main

echo ""
echo "Done! Your branches should now be in sync."
