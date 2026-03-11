@echo off
REM Script di cache per il deploy in produzione (Windows).
REM Eseguire dopo ogni deploy: deploy-cache.bat

cd /d "%~dp0"

echo Esecuzione: php artisan config:cache
php artisan config:cache
if errorlevel 1 exit /b 1

echo Esecuzione: php artisan route:cache
php artisan route:cache
if errorlevel 1 exit /b 1

echo Esecuzione: php artisan view:cache
php artisan view:cache
if errorlevel 1 exit /b 1

echo.
echo Cache artefact completata con successo.
