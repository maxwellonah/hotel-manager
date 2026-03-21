@echo off
cd /d "c:\Users\ANA H&A\Desktop\for new desktop\ANA\ana-hotel"

echo Starting Laravel auto-start script... >> laravel-startup.log
echo %date% %time% - Starting Laravel auto-start script... >> laravel-startup.log

REM Check if port 8000 is already in use
netstat -ano | findstr :8000 > nul
if %errorlevel% == 0 (
    echo %date% %time% - Port 8000 is already in use. Laravel server may already be running. >> laravel-startup.log
    exit /b
)

REM Start Laravel development server
echo %date% %time% - Starting Laravel development server... >> laravel-startup.log
start /min cmd /c "php artisan serve"

REM Wait a moment
timeout /t 3 /nobreak > nul

REM Check if server started
netstat -ano | findstr :8000 > nul
if %errorlevel% == 0 (
    echo %date% %time% - Laravel server started successfully on http://127.0.0.1:8000 >> laravel-startup.log
) else (
    echo %date% %time% - Failed to start Laravel server >> laravel-startup.log
)
