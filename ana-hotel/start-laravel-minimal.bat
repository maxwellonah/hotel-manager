@echo off
cd /d "c:\Users\ANA H&A\Desktop\for new desktop\ANA\ana-hotel"
echo Starting ANA Hotel Server...
echo Access your app at: http://anahotel.ddns.net:8000
echo Local access: http://localhost:8000
echo.
php artisan serve --host=0.0.0.0 --port=8000
