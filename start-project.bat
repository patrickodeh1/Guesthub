@echo off
cd /d "%~dp0"
echo Starting Laravel Welcome Guide on http://127.0.0.1:8000
echo.
"D:\Xampp\php\php.exe" artisan optimize:clear
"D:\Xampp\php\php.exe" artisan serve --host=127.0.0.1 --port=8000
