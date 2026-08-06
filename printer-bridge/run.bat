@echo off
setlocal enabledelayedexpansion
cd /d "%~dp0"

if exist printer-bridge.pid (
    set /p OLDPID=<printer-bridge.pid
    tasklist /FI "PID eq !OLDPID!" | find "!OLDPID!" >nul
    if not errorlevel 1 (
        echo Print Bridge sudah jalan ^(PID !OLDPID!^).
        goto :eof
    )
)

powershell -NoProfile -Command "$p = Start-Process -FilePath 'javaw' -ArgumentList '-jar','printer-bridge.jar' -WindowStyle Hidden -PassThru; $p.Id | Out-File -Encoding ascii -NoNewline printer-bridge.pid"

set /p NEWPID=<printer-bridge.pid
echo Print Bridge dijalankan ^(PID !NEWPID!^). Cek: http://127.0.0.1:9100/health
