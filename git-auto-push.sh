#!/bin/bash
# Auto Git Push Script for Linux/Mac
# This script automatically commits and pushes all changes to GitHub

echo "========================================"
echo "Auto Git Push Script"
echo "========================================"
echo ""

# Check if git is initialized
if [ ! -d ".git" ]; then
    echo "ERROR: Not a git repository!"
    echo "Please run: git init"
    exit 1
fi

# Get current branch
CURRENT_BRANCH=$(git branch --show-current)
echo "Current branch: $CURRENT_BRANCH"
echo ""

# Check for changes
if [ -z "$(git status --porcelain)" ]; then
    echo "No changes to commit."
    exit 0
fi

# Show status
echo "Changes detected:"
git status --short
echo ""

# Add all changes
echo "Adding all changes..."
git add -A

# Generate commit message with timestamp
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
COMMIT_MSG="Auto commit: $TIMESTAMP - Updated files"

# Commit changes
echo "Committing changes..."
git commit -m "$COMMIT_MSG"

# Push to GitHub
echo "Pushing to GitHub..."
git push origin "$CURRENT_BRANCH"

if [ $? -eq 0 ]; then
    echo ""
    echo "========================================"
    echo "Successfully pushed to GitHub!"
    echo "========================================"
else
    echo "ERROR: Failed to push!"
    echo "You may need to set up remote: git remote add origin YOUR_REPO_URL"
    exit 1
fi

