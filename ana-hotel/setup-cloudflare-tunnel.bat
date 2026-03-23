@echo off
echo Setting up Cloudflare Tunnel for ANA Hotel...
echo This will give you a secure domain that works anywhere!
echo.

REM Check if cloudflared is installed
if not exist "cloudflared.exe" (
    echo Downloading Cloudflare Tunnel...
    powershell -Command "Invoke-WebRequest -Uri 'https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe' -OutFile 'cloudflared.exe'"
)

echo.
echo Cloudflare Tunnel setup:
echo 1. Go to: https://dash.cloudflare.com/sign-up
echo 2. Create free account
echo 3. Go to: Access -> Tunnels
echo 4. Click "Create tunnel"
echo 5. Choose "Cloudflared" 
echo 6. Copy the tunnel token
echo 7. Run: cloudflared.exe tunnel --token YOUR_TOKEN
echo.
echo Your domain will be: https://your-name.trycloudflare.com
echo No router configuration needed!
echo.
pause
