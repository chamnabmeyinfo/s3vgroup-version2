@echo off
REM Auto Git Push Script for Windows
REM This script automatically commits and pushes all changes to GitHub

echo ========================================
echo Auto Git Push Script
echo ========================================
echo.

REM Check if git is initialized
if not exist ".git" (
    echo ERROR: Not a git repository!
    echo Please run: git init
    pause
    exit /b 1
)

REM Get current branch
for /f "tokens=*" %%i in ('git branch --show-current') do set CURRENT_BRANCH=%%i
echo Current branch: %CURRENT_BRANCH%
echo.

REM Check for changes
git status --porcelain >nul 2>&1
if %errorlevel% neq 0 (
    echo No changes to commit.
    pause
    exit /b 0
)

REM Show status
echo Changes detected:
git status --short
echo.

REM Add all changes
echo Adding all changes...
git add -A
if %errorlevel% neq 0 (
    echo ERROR: Failed to add files!
    pause
    exit /b 1
)

REM Generate commit message with timestamp
for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value') do set datetime=%%I
set timestamp=%datetime:~0,4%-%datetime:~4,2%-%datetime:~6,2% %datetime:~8,2%:%datetime:~10,2%:%datetime:~12,2%

set COMMIT_MSG=Auto commit: %timestamp% - Updated files

REM Commit changes
echo Committing changes...
git commit -m "%COMMIT_MSG%"
if %errorlevel% neq 0 (
    echo ERROR: Failed to commit!
    pause
    exit /b 1
)

REM Push to GitHub
echo Pushing to GitHub...
git push origin %CURRENT_BRANCH%
if %errorlevel% neq 0 (
    echo ERROR: Failed to push!
    echo You may need to set up remote: git remote add origin YOUR_REPO_URL
    pause
    exit /b 1
)

echo.
echo ========================================
echo Successfully pushed to GitHub!
echo ========================================
pause

