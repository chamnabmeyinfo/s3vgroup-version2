@echo off
REM One-Click Deployment System
REM Pushes code to GitHub + Uploads non-Git files via FTP

echo ========================================
echo One-Click Deployment System
echo ========================================
echo.

REM Check if PHP is available
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: PHP is not installed or not in PATH!
    echo Please install PHP or add it to your PATH.
    pause
    exit /b 1
)

REM Check if config exists
if not exist "deploy-config.json" (
    echo.
    echo Config file not found!
    echo.
    echo Creating from example...
    if exist "deploy-config.example.json" (
        copy "deploy-config.example.json" "deploy-config.json" >nul
        echo.
        echo Please edit deploy-config.json with your FTP credentials.
        echo Then run deploy.bat again.
        pause
        exit /b 1
    ) else (
        echo ERROR: deploy-config.example.json not found!
        pause
        exit /b 1
    )
)

echo Starting deployment...
echo.

REM Run PHP deployment script
php deploy-main.php

if %errorlevel% equ 0 (
    echo.
    echo ========================================
    echo Deployment Complete!
    echo ========================================
) else (
    echo.
    echo ========================================
    echo Deployment Failed!
    echo Check deploy-log.txt for details.
    echo ========================================
)

echo.
pause

