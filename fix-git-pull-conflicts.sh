#!/bin/bash
# Fix Git Pull Conflicts - Handles both .htaccess and storage/uploads/ issues
# Usage: Run this script in your website directory on the server

echo "🔧 Fixing Git Pull Conflicts..."
echo ""

# Navigate to website directory
cd /home/s3vtgroup/public_html

echo "📋 Step 1: Checking current status..."
git status

echo ""
echo "📦 Step 2: Stashing .htaccess changes..."
git stash push -m "Stash .htaccess changes before pull" .htaccess

echo ""
echo "📁 Step 3: Adding all upload files to Git..."
git add storage/uploads/

echo ""
echo "💾 Step 4: Committing upload files..."
git commit -m "Add uploaded images and assets from server" || echo "No new files to commit or already committed"

echo ""
echo "⬇️  Step 5: Pulling latest code..."
git pull origin main

echo ""
echo "🔄 Step 6: Reapplying .htaccess changes (if any)..."
git stash pop || echo "No stashed changes to reapply"

echo ""
echo "✅ Done! Checking final status..."
git status

echo ""
echo "🎉 All conflicts resolved!"
