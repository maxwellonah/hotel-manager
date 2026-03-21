@echo off
echo Creating Laravel auto-start task...
schtasks /create /tn "Laravel Auto-Start" /tr "cmd /c cd /d \"c:\Users\ANA H&A\Desktop\for new desktop\ANA\ana-hotel\" && php artisan serve" /sc onlogon /f
echo Task created successfully!
pause
