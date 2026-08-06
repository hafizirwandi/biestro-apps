@echo off
cd /d "%~dp0"

if not exist printer-bridge.pid (
    echo Print Bridge tidak sedang jalan ^(file PID tidak ditemukan^).
    goto :eof
)

set /p PID=<printer-bridge.pid
taskkill /PID %PID% /F >nul 2>&1
if errorlevel 1 (
    echo PID %PID% tidak ditemukan ^(mungkin sudah berhenti^).
) else (
    echo Print Bridge ^(PID %PID%^) dihentikan.
)

del printer-bridge.pid
