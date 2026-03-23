@echo off
echo Starting ANA Hotel Management System...
echo.

REM Start Laravel Server
cd /d "c:\Users\ANA H&A\Desktop\for new desktop\ANA\ana-hotel"
start /min "Laravel Server" cmd /c "php artisan serve --host=0.0.0.0 --port=8000"

REM Wait for server to start
timeout /t 5 /nobreak >nul

REM Open browser with your domain
start http://anahotel.ddns.net:8000

echo.
echo 🏨 ANA Hotel is now accessible at:
echo 🌐 Global: http://anahotel.ddns.net:8000
echo 🏠 Local: http://localhost:8000
echo.
echo Press any key to stop the server...
pause >nul

REM Stop server (optional)
taskkill /f /im php.exe >nul 2>&1
echo Server stopped.
