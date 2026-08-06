#!/bin/bash
# Hentikan Print Bridge yang dijalankan lewat run.sh (macOS/Linux).
cd "$(dirname "$0")"

if [ -f printer-bridge.pid ]; then
    PID=$(cat printer-bridge.pid)
    if kill "$PID" 2>/dev/null; then
        echo "Print Bridge (PID $PID) dihentikan."
    else
        echo "PID $PID tidak ditemukan (mungkin sudah berhenti)."
    fi
    rm -f printer-bridge.pid
else
    # Fallback kalau file PID hilang tapi proses masih nyangkut di port 9100.
    PIDS=$(lsof -ti:9100 -sTCP:LISTEN 2>/dev/null)
    if [ -n "$PIDS" ]; then
        echo "$PIDS" | xargs kill
        echo "Print Bridge di port 9100 dihentikan (PID: $PIDS)."
    else
        echo "Print Bridge tidak sedang jalan."
    fi
fi
