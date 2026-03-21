Set objShell = CreateObject("WScript.Shell")
Set objFSO = CreateObject("Scripting.FileSystemObject")

' Log file path
logFile = "C:\Users\ANA H&A\Desktop\for new desktop\ANA\ana-hotel\laravel-startup.log"

' Function to write to log
Sub WriteLog(message)
    Set objFile = objFSO.OpenTextFile(logFile, 8, True)
    timestamp = Year(Now) & "-" & Right("0" & Month(Now), 2) & "-" & Right("0" & Day(Now), 2) & " " & Right("0" & Hour(Now), 2) & ":" & Right("0" & Minute(Now), 2) & ":" & Right("0" & Second(Now), 2)
    objFile.WriteLine timestamp & " - " & message
    objFile.Close
End Sub

On Error Resume Next

WriteLog "Starting Laravel auto-start script (VBScript)..."

' Change to project directory
objShell.CurrentDirectory = "C:\Users\ANA H&A\Desktop\for new desktop\ANA\ana-hotel"
WriteLog "Changed to directory: " & objShell.CurrentDirectory

' Check if port 8000 is already in use (simple check)
Set objExec = objShell.Exec("netstat -ano | findstr :8000")
strOutput = objExec.StdOut.ReadAll

If InStr(strOutput, ":8000") > 0 Then
    WriteLog "Port 8000 is already in use. Laravel server may already be running."
    WScript.Quit
End If

' Start Laravel development server
WriteLog "Starting Laravel development server..."
objShell.Run "php artisan serve", 0, False

' Wait a moment
WScript.Sleep 3000

' Check again if port 8000 is now in use
Set objExec = objShell.Exec("netstat -ano | findstr :8000")
strOutput = objExec.StdOut.ReadAll

If InStr(strOutput, ":8000") > 0 Then
    WriteLog "Laravel server started successfully on http://127.0.0.1:8000"
Else
    WriteLog "Failed to start Laravel server"
End If

If Err.Number <> 0 Then
    WriteLog "Error occurred: " & Err.Description
End If
