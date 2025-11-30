@echo off
REM Show files that are NOT pushed to GitHub (ignored by Git)

echo ========================================
echo Files NOT Pushed to GitHub (Ignored)
echo ========================================
echo.

REM Check if git is initialized
if not exist ".git" (
    echo ERROR: Not a git repository!
    pause
    exit /b 1
)

echo Showing all ignored files and patterns...
echo.

REM Show ignored files
echo [Ignored Files and Patterns]
echo ----------------------------------------
git status --ignored --short

echo.
echo ========================================
echo Summary
echo ========================================

REM Count ignored files
for /f %%i in ('git status --ignored --short 2^>nul ^| find /c /v ""') do set IGNORED_COUNT=%%i
echo Total ignored items: %IGNORED_COUNT%

echo.
echo ========================================
echo Ignored Patterns from .gitignore
echo ========================================
type .gitignore | findstr /v "^#" | findstr /v "^$"

echo.
echo ========================================
echo Note: These files will NOT be pushed to GitHub
echo ========================================
pause

