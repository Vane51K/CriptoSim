@echo off
title CriptoSim - servidor local

netstat -ano | findstr "LISTENING" | findstr ":8080" >nul
if not errorlevel 1 (
    echo CriptoSim ya esta encendido.
    echo Abrir http://127.0.0.1:8080 en el navegador.
    pause
    exit /b
)

echo Encendiendo CriptoSim...
start "" /min powershell -WindowStyle Hidden -Command "Start-Sleep 2; Start-Process 'http://127.0.0.1:8080'"
"C:\xampp\php\php.exe" -S 127.0.0.1:8080 -t "%~dp0."
