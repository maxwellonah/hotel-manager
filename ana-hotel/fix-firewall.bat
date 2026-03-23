@echo off
echo Fixing Windows Firewall for ANA Hotel access...
echo.

REM Allow port 8000 for PHP
netsh advfirewall firewall add rule name="ANA Hotel Laravel" dir=in action=allow protocol=TCP localport=8000
netsh advfirewall firewall add rule name="ANA Hotel Laravel Out" dir=out action=allow protocol=TCP localport=8000

echo.
echo Firewall rules added!
echo.
echo Now test from your phone:
echo http://10.103.30.250:8000
echo.
pause
