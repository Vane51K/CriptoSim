@echo off
title CriptoSim - apagar servidor

set "found="
for /f "tokens=5" %%p in ('netstat -ano ^| findstr "LISTENING" ^| findstr ":8080"') do (
    set "found=1"
    taskkill /f /pid %%p >nul 2>&1
)

if defined found (
    echo CriptoSim apagado.
) else (
    echo CriptoSim no estaba encendido.
)
pause
