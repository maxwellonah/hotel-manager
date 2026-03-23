@echo off
echo Setting up No-IP for anahotel.local access...
echo.

REM Check if No-IP DUC is already installed
if exist "C:\Program Files (x86)\No-IP\DUC.exe" (
    echo No-IP DUC already installed
    goto :start
)

echo Downloading No-IP DUC...
powershell -Command "Invoke-WebRequest -Uri 'https://www.noip.com/client/DUCSetup.exe' -OutFile 'DUCSetup.exe'"

echo Installing No-IP DUC...
start /wait DUCSetup.exe /S

:start
echo Starting No-IP DUC...
start "" "C:\Program Files (x86)\No-IP\DUC.exe"

echo.
echo Setup complete! Your app will be accessible at:
echo http://anahotel.ddns.net:8000
echo.
echo Make sure your Laravel server is running with:
echo php artisan serve --host=0.0.0.0 --port=8000
echo.
pause
