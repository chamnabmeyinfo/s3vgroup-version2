#!/bin/bash
# Show files that are NOT pushed to GitHub (ignored by Git)

echo "========================================"
echo "Files NOT Pushed to GitHub (Ignored)"
echo "========================================"
echo ""

# Check if git is initialized
if [ ! -d ".git" ]; then
    echo "ERROR: Not a git repository!"
    exit 1
fi

echo "Showing all ignored files and patterns..."
echo ""

# Show ignored files
echo "[Ignored Files and Patterns]"
echo "----------------------------------------"
git status --ignored --short

echo ""
echo "========================================"
echo "Summary"
echo "========================================"

# Count ignored files
IGNORED_COUNT=$(git status --ignored --short 2>/dev/null | wc -l)
echo "Total ignored items: $IGNORED_COUNT"

echo ""
echo "========================================"
echo "Ignored Patterns from .gitignore"
echo "========================================"
grep -v "^#" .gitignore | grep -v "^$"

echo ""
echo "========================================"
echo "Note: These files will NOT be pushed to GitHub"
echo "========================================"

