@echo off
REM Prepare Deployment Package for cPanel
REM This creates a ZIP with all files needed for deployment

echo ========================================
echo Prepare Deployment Package for cPanel
echo ========================================
echo.

set DEPLOY_DIR=deployment-package
set ZIP_NAME=s3vgroup-deployment-%date:~-4,4%%date:~-10,2%%date:~-7,2%.zip

echo Step 1: Creating deployment directory...
if exist "%DEPLOY_DIR%" rmdir /s /q "%DEPLOY_DIR%"
mkdir "%DEPLOY_DIR%"

echo Step 2: Copying files...
echo.

REM Copy all files except ignored ones
xcopy /E /I /Y /EXCLUDE:deployment-exclude.txt . "%DEPLOY_DIR%\" >nul 2>&1

REM Manual copy of important directories
echo Copying admin...
xcopy /E /I /Y admin "%DEPLOY_DIR%\admin\" >nul 2>&1

echo Copying app...
xcopy /E /I /Y app "%DEPLOY_DIR%\app\" >nul 2>&1

echo Copying assets...
xcopy /E /I /Y assets "%DEPLOY_DIR%\assets\" >nul 2>&1

echo Copying bootstrap...
xcopy /E /I /Y bootstrap "%DEPLOY_DIR%\bootstrap\" >nul 2>&1

echo Copying config...
xcopy /E /I /Y config "%DEPLOY_DIR%\config\" >nul 2>&1

echo Copying database...
xcopy /E /I /Y database "%DEPLOY_DIR%\database\" >nul 2>&1

echo Copying includes...
xcopy /E /I /Y includes "%DEPLOY_DIR%\includes\" >nul 2>&1

echo Copying storage structure (without files)...
mkdir "%DEPLOY_DIR%\storage"
mkdir "%DEPLOY_DIR%\storage\uploads"
mkdir "%DEPLOY_DIR%\storage\cache"
mkdir "%DEPLOY_DIR%\storage\logs"
mkdir "%DEPLOY_DIR%\storage\backups"
copy storage\uploads\.gitkeep "%DEPLOY_DIR%\storage\uploads\" >nul 2>&1
copy storage\cache\.gitkeep "%DEPLOY_DIR%\storage\cache\" >nul 2>&1
copy storage\logs\.gitkeep "%DEPLOY_DIR%\storage\logs\" >nul 2>&1
copy storage\backups\.gitkeep "%DEPLOY_DIR%\storage\backups\" >nul 2>&1

echo Copying PHP files...
copy *.php "%DEPLOY_DIR\" >nul 2>&1

echo Copying config files...
copy .htaccess "%DEPLOY_DIR\" >nul 2>&1
copy .gitignore "%DEPLOY_DIR\" >nul 2>&1

echo Copying documentation...
copy *.md "%DEPLOY_DIR\" >nul 2>&1

echo.
echo Step 3: Creating ZIP file...
powershell -Command "Compress-Archive -Path '%DEPLOY_DIR%\*' -DestinationPath '%ZIP_NAME%' -Force"

echo.
echo Step 4: Cleaning up...
rmdir /s /q "%DEPLOY_DIR%"

echo.
echo ========================================
echo Deployment Package Created!
echo ========================================
echo.
echo File: %ZIP_NAME%
echo.
echo Next Steps:
echo 1. Upload %ZIP_NAME% to cPanel
echo 2. Upload storage/uploads/ folder separately
echo 3. Extract ZIP in public_html/
echo 4. Update config files on server
echo.
pause

