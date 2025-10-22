@echo off
cd /d "C:\inetpub\wwwroot\assetuploadtool"
php artisan queue:work --sleep=3 --tries=3 --timeout=120
pause
