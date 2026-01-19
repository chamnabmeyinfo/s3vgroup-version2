#!/bin/bash
# Fix Git Unstaged Changes Error on cPanel Server
# Repository: /home/s3vtgroup/dev.s3vtgroup.com.kh
# Error: cannot pull with rebase: You have unstaged changes

echo "=========================================="
echo "Fixing Git Unstaged Changes Error"
echo "Repository: /home/s3vtgroup/dev.s3vtgroup.com.kh"
echo "=========================================="
echo ""

# Navigate to repository
cd /home/s3vtgroup/dev.s3vtgroup.com.kh || {
    echo "❌ Error: Cannot access repository directory"
    exit 1
}

echo "📂 Current directory: $(pwd)"
echo ""

# Check Git status
echo "📊 Checking Git status..."
git status --short
echo ""

# Show what files have changes
echo "📝 Files with changes:"
git status --porcelain
echo ""

# Ask what to do
echo "=========================================="
echo "OPTIONS:"
echo "=========================================="
echo "1. STASH changes (recommended) - saves changes temporarily"
echo "2. COMMIT changes - saves changes permanently"
echo "3. DISCARD changes - permanently deletes uncommitted changes"
echo "4. AUTO - stash, pull, then reapply stash"
echo ""
read -p "Choose option (1-4) [default: 4]: " option
option=${option:-4}

case $option in
    1)
        echo ""
        echo "📦 Stashing changes..."
        git stash push -m "Stashed changes before pull - $(date '+%Y-%m-%d %H:%M:%S')"
        echo "✅ Changes stashed successfully!"
        echo ""
        echo "To reapply stashed changes later, run:"
        echo "  git stash pop"
        ;;
    2)
        echo ""
        echo "💾 Committing changes..."
        read -p "Enter commit message [default: 'Server changes']: " commit_msg
        commit_msg=${commit_msg:-"Server changes"}
        git add -A
        git commit -m "$commit_msg"
        echo "✅ Changes committed successfully!"
        ;;
    3)
        echo ""
        echo "⚠️  WARNING: This will PERMANENTLY DELETE all uncommitted changes!"
        read -p "Are you sure? Type 'yes' to confirm: " confirm
        if [ "$confirm" = "yes" ]; then
            echo "🗑️  Discarding changes..."
            git reset --hard HEAD
            git clean -fd
            echo "✅ Changes discarded successfully!"
        else
            echo "❌ Operation cancelled."
            exit 0
        fi
        ;;
    4)
        echo ""
        echo "🔄 AUTO MODE: Stashing, pulling, then reapplying..."
        echo ""
        
        # Stash changes
        echo "📦 Step 1: Stashing changes..."
        git stash push -m "Auto-stash before pull - $(date '+%Y-%m-%d %H:%M:%S')"
        if [ $? -eq 0 ]; then
            echo "✅ Changes stashed"
        else
            echo "⚠️  No changes to stash (or stash failed)"
        fi
        echo ""
        
        # Pull latest code
        echo "⬇️  Step 2: Pulling latest code from GitHub..."
        git pull origin main
        if [ $? -eq 0 ]; then
            echo "✅ Code pulled successfully"
        else
            echo "❌ Pull failed. Check the error above."
            echo "💡 You can reapply stashed changes with: git stash pop"
            exit 1
        fi
        echo ""
        
        # Reapply stash if there was one
        if git stash list | grep -q "Auto-stash"; then
            echo "📦 Step 3: Reapplying stashed changes..."
            git stash pop
            if [ $? -eq 0 ]; then
                echo "✅ Stashed changes reapplied successfully"
            else
                echo "⚠️  Warning: There were conflicts when reapplying stash"
                echo "💡 Resolve conflicts manually, then run: git stash drop"
            fi
        else
            echo "ℹ️  No stashed changes to reapply"
        fi
        echo ""
        ;;
    *)
        echo "❌ Invalid option. Exiting."
        exit 1
        ;;
esac

echo ""
echo "=========================================="
echo "✅ Process completed!"
echo "=========================================="
echo ""
echo "Current Git status:"
git status --short
echo ""
