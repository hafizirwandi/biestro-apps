#!/bin/bash
# Jalankan Print Bridge di background (macOS/Linux). Untuk Windows pakai run.bat.
cd "$(dirname "$0")"

if [ -f printer-bridge.pid ] && kill -0 "$(cat printer-bridge.pid)" 2>/dev/null; then
    echo "Print Bridge sudah jalan (PID $(cat printer-bridge.pid))."
    exit 0
fi

nohup java -jar printer-bridge.jar > printer-bridge.out.log 2>&1 &
echo $! > printer-bridge.pid
sleep 1

if kill -0 "$(cat printer-bridge.pid)" 2>/dev/null; then
    echo "Print Bridge dijalankan (PID $(cat printer-bridge.pid)). Cek: curl http://127.0.0.1:9100/health"
else
    echo "Gagal start — lihat printer-bridge.out.log untuk detail error."
    rm -f printer-bridge.pid
    exit 1
fi
