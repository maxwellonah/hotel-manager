# Laravel Auto-start Script
# This script starts the Laravel development server automatically

$projectPath = "c:\Users\ANA H&A\Desktop\for new desktop\ANA\ana-hotel"
$logFile = "$projectPath\laravel-startup.log"

# Function to write to log file
function Write-Log {
    param([string]$message)
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    Add-Content -Path $logFile -Value "$timestamp - $message"
}

try {
    Write-Log "Starting Laravel auto-start script..."
    
    # Change to project directory
    Set-Location $projectPath
    Write-Log "Changed to directory: $projectPath"
    
    # Check if port 8000 is already in use
    $portInUse = Get-NetTCPConnection -LocalPort 8000 -ErrorAction SilentlyContinue
    if ($portInUse) {
        Write-Log "Port 8000 is already in use. Laravel server may already be running."
        exit 0
    }
    
    # Start Laravel development server
    Write-Log "Starting Laravel development server..."
    Start-Process -FilePath "php" -ArgumentList "artisan", "serve" -WindowStyle Minimized
    
    # Wait a moment to check if server started successfully
    Start-Sleep -Seconds 3
    
    # Check if server is running
    $serverRunning = Get-NetTCPConnection -LocalPort 8000 -ErrorAction SilentlyContinue
    if ($serverRunning) {
        Write-Log "Laravel server started successfully on http://127.0.0.1:8000"
    } else {
        Write-Log "Failed to start Laravel server"
    }
    
} catch {
    Write-Log "Error occurred: $($_.Exception.Message)"
}
