<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printer Test - Web Bluetooth</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', sans-serif;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .status-dot {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 6px;
        }

        .status-connected {
            background: #28a745;
        }

        .status-disconnected {
            background: #dc3545;
        }

        .status-connecting {
            background: #ffc107;
            animation: blink 1s infinite;
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: 0.3
            }
        }

        #log {
            background: #1e1e1e;
            color: #50fa7b;
            font-family: monospace;
            font-size: 13px;
            border-radius: 8px;
            padding: 16px;
            height: 200px;
            overflow-y: auto;
        }

        #log .error {
            color: #ff5555;
        }

        #log .info {
            color: #8be9fd;
        }

        #log .warn {
            color: #ffb86c;
        }

        .printer-icon {
            font-size: 64px;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <div class="container py-4" style="max-width:700px">

        <div class="d-flex align-items-center mb-4">
            <a href="{{ url('/home') }}" class="btn btn-outline-secondary btn-sm me-3">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h4 class="mb-0 fw-bold">🖨️ Bluetooth Printer Test</h4>
        </div>

        {{-- Status Card --}}
        <div class="card mb-4">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-1 text-muted">Printer Status</h6>
                    <div id="statusText">
                        <span class="status-dot status-disconnected"></span>
                        <strong>Disconnected</strong>
                    </div>
                    <small class="text-muted" id="deviceName">No device connected</small>
                </div>
                <div class="text-center">
                    <i class="fas fa-print printer-icon" id="printerIcon"></i>
                </div>
            </div>
        </div>

        {{-- Controls --}}
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="fas fa-bluetooth-b text-primary me-2"></i>Bluetooth Control</h6>

                @if (!request()->isSecure() && app()->environment('production'))
                    <div class="alert alert-warning">
                        ⚠️ <strong>HTTPS Required:</strong> Web Bluetooth API hanya berjalan di HTTPS atau localhost.
                    </div>
                @endif

                <div class="row g-2">
                    <div class="col-6 col-md-3">
                        <button id="btnConnect" class="btn btn-primary w-100" onclick="connectPrinter()">
                            <i class="fas fa-bluetooth-b me-1"></i> Connect
                        </button>
                    </div>
                    <div class="col-6 col-md-3">
                        <button id="btnDisconnect" class="btn btn-outline-secondary w-100" onclick="disconnectPrinter()"
                            disabled>
                            <i class="fas fa-times me-1"></i> Disconnect
                        </button>
                    </div>
                    <div class="col-6 col-md-3">
                        <button id="btnTestPrint" class="btn btn-success w-100" onclick="testPrint()" disabled>
                            <i class="fas fa-print me-1"></i> Test Print
                        </button>
                    </div>
                    <div class="col-6 col-md-3">
                        <button id="btnReceiptPrint" class="btn btn-warning w-100" onclick="printDummyReceipt()"
                            disabled>
                            <i class="fas fa-receipt me-1"></i> Receipt
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Transaction Test --}}
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="fas fa-file-invoice me-2 text-success"></i>Print From Transaction
                </h6>
                <div class="input-group">
                    <input type="number" id="transactionId" class="form-control"
                        placeholder="Masukkan Transaction ID...">
                    <button class="btn btn-outline-success" onclick="printTransaction()" id="btnPrintTx">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
                <small class="text-muted">Masukkan ID transaksi untuk mencetak receipt asli.</small>
            </div>
        </div>

        {{-- Log Console --}}
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <h6 class="fw-bold mb-0"><i class="fas fa-terminal me-2"></i>Console Log</h6>
                    <button class="btn btn-sm btn-outline-secondary" onclick="clearLog()">Clear</button>
                </div>
                <div id="log"><span class="info">// Siap untuk koneksi Bluetooth...<br></span></div>
            </div>
        </div>

    </div>

    <script>
        // ============================================================
        // ESC/POS Helper - generates raw bytes for thermal printer
        // ============================================================
        const ESC = 0x1B;
        const GS = 0x1D;
        const LF = 0x0A;

        class EscPos {
            constructor() {
                this.buffer = [];
            }

            init() {
                this.buffer.push(ESC, 0x40);
                return this;
            }
            lf(n = 1) {
                for (let i = 0; i < n; i++) this.buffer.push(LF);
                return this;
            }
            cut() {
                this.buffer.push(GS, 0x56, 0x41, 0x05);
                return this;
            }

            align(a) {
                // 0=left, 1=center, 2=right
                this.buffer.push(ESC, 0x61, a);
                return this;
            }

            bold(on) {
                this.buffer.push(ESC, 0x45, on ? 1 : 0);
                return this;
            }

            doubleWidth(on) {
                this.buffer.push(ESC, 0x21, on ? 0x20 : 0x00);
                return this;
            }

            text(str) {
                const enc = new TextEncoder();
                const bytes = enc.encode(str);
                bytes.forEach(b => this.buffer.push(b));
                return this;
            }

            textLine(str) {
                return this.text(str).lf();
            }

            dashedLine(char = '-', len = 32) {
                return this.textLine(char.repeat(len));
            }

            // Print item row: name, qty x price = subtotal
            itemRow(name, qty, price, subtotal) {
                const fmtRp = n => 'Rp.' + n.toLocaleString('id-ID');
                this.textLine(name.substring(0, 30));
                const qtyPrice = `  ${qty} x ${fmtRp(price)}`;
                const sub = fmtRp(subtotal);
                const pad = ' '.repeat(Math.max(0, 32 - qtyPrice.length - sub.length));
                return this.textLine(qtyPrice + pad + sub);
            }

            summaryRow(label, value) {
                const fmtRp = n => 'Rp.' + n.toLocaleString('id-ID');
                const v = fmtRp(value);
                const pad = ' '.repeat(Math.max(0, 32 - label.length - v.length));
                return this.textLine(label + pad + v);
            }

            getBytes() {
                return new Uint8Array(this.buffer);
            }
        }

        // ============================================================
        // Bluetooth Printer State
        // ============================================================
        let bluetoothDevice = null;
        let characteristic = null;

        // Common BLE UUIDs for ESC/POS thermal printers
        // Cashino C58BT / Xprinter / generic BLE printers use these:
        const PRINTER_SERVICE_UUIDS = [
            '000018f0-0000-1000-8000-00805f9b34fb', // Standard BLE Printer
            'e7810a71-73ae-499d-8c15-faa9aef0c3f2', // Common thermal
            '49535343-fe7d-4ae5-8fa9-9fafd205e455', // Kassen/Symcode
        ];
        const PRINTER_CHAR_UUIDS = [
            '00002af1-0000-1000-8000-00805f9b34fb',
            'bef8d6c9-9c21-4c9e-b632-bd58c1009f9f',
            '49535343-8841-43f4-a8d4-ecbe34729bb3',
        ];

        // ============================================================
        // Connect / Disconnect
        // ============================================================
        async function connectPrinter() {
            if (!navigator.bluetooth) {
                log('❌ Web Bluetooth tidak didukung di browser ini. Gunakan Chrome/Edge.', 'error');
                return;
            }

            try {
                setStatus('connecting');
                log('🔍 Scanning BLE devices...', 'info');

                bluetoothDevice = await navigator.bluetooth.requestDevice({
                    acceptAllDevices: true,
                    optionalServices: PRINTER_SERVICE_UUIDS
                });

                log(`✅ Device ditemukan: <strong>${bluetoothDevice.name ?? '(no name)'}</strong>`, 'info');
                bluetoothDevice.addEventListener('gattserverdisconnected', onDisconnected);

                log('⚡ Connecting to GATT server...', 'info');
                const server = await bluetoothDevice.gatt.connect();

                // Try to find a matching service
                let service = null;
                for (const uuid of PRINTER_SERVICE_UUIDS) {
                    try {
                        service = await server.getPrimaryService(uuid);
                        log(`✅ Service ditemukan: ${uuid}`, 'info');
                        break;
                    } catch (e) {
                        /* try next */ }
                }

                if (!service) {
                    // Last resort: grab any available services
                    const services = await server.getPrimaryServices();
                    if (services.length > 0) {
                        service = services[0];
                        log(`⚠️ Menggunakan service: ${service.uuid}`, 'warn');
                    } else {
                        throw new Error('Tidak ada BLE service yang ditemukan.');
                    }
                }

                // Try to find writable characteristic
                characteristic = null;
                for (const uuid of PRINTER_CHAR_UUIDS) {
                    try {
                        characteristic = await service.getCharacteristic(uuid);
                        log(`✅ Characteristic ditemukan: ${uuid}`, 'info');
                        break;
                    } catch (e) {
                        /* try next */ }
                }

                if (!characteristic) {
                    const chars = await service.getCharacteristics();
                    for (const c of chars) {
                        const props = c.properties;
                        if (props.write || props.writeWithoutResponse) {
                            characteristic = c;
                            log(`⚠️ Menggunakan characteristic: ${c.uuid}`, 'warn');
                            break;
                        }
                    }
                }

                if (!characteristic) throw new Error('Tidak ada writable characteristic ditemukan.');

                setStatus('connected', bluetoothDevice.name ?? 'Unknown Device');
                log('🖨️ Printer siap digunakan!', 'info');

            } catch (err) {
                setStatus('disconnected');
                if (err.name === 'NotFoundError') {
                    log('ℹ️ Pemilihan device dibatalkan.', 'warn');
                } else {
                    log(`❌ Gagal connect: ${err.message}`, 'error');
                }
            }
        }

        function disconnectPrinter() {
            if (bluetoothDevice && bluetoothDevice.gatt.connected) {
                bluetoothDevice.gatt.disconnect();
            }
            onDisconnected();
        }

        function onDisconnected() {
            characteristic = null;
            setStatus('disconnected');
            log('🔌 Printer disconnected.', 'warn');
        }

        // ============================================================
        // Send data to printer
        // ============================================================
        async function sendData(bytes) {
            if (!characteristic) {
                log('❌ Printer belum terkoneksi!', 'error');
                return false;
            }
            const CHUNK_SIZE = 512; // Bluetooth MTU limit
            try {
                for (let i = 0; i < bytes.length; i += CHUNK_SIZE) {
                    const chunk = bytes.slice(i, i + CHUNK_SIZE);
                    if (characteristic.properties.writeWithoutResponse) {
                        await characteristic.writeValueWithoutResponse(chunk);
                    } else {
                        await characteristic.writeValue(chunk);
                    }
                }
                return true;
            } catch (e) {
                log(`❌ Gagal send data: ${e.message}`, 'error');
                return false;
            }
        }

        // ============================================================
        // Print functions
        // ============================================================
        async function testPrint() {
            log('📤 Mengirim test print...', 'info');
            const esc = new EscPos()
                .init()
                .lf(2)
                .align(1)
                .bold(true)
                .doubleWidth(true)
                .textLine('TEST PRINTER')
                .doubleWidth(false)
                .bold(false)
                .lf()
                .textLine('Web Bluetooth API')
                .textLine('C58BT - ESC/POS')
                .lf()
                .dashedLine()
                .align(0)
                .textLine('Status   : OK')
                .textLine(`Waktu    : ${new Date().toLocaleString('id-ID')}`)
                .dashedLine()
                .align(1)
                .lf()
                .textLine('Koneksi Berhasil!')
                .lf(4)
                .cut();

            const success = await sendData(esc.getBytes());
            if (success) log('✅ Test print berhasil!', 'info');
        }

        async function printDummyReceipt() {
            log('📋 Mengambil data dummy receipt...', 'info');
            try {
                const res = await fetch('{{ route('printer.receipt-data') }}');
                const data = await res.json();
                await buildAndPrint(data);
            } catch (e) {
                log(`❌ Gagal mengambil data: ${e.message}`, 'error');
            }
        }

        async function printTransaction() {
            const id = document.getElementById('transactionId').value;
            if (!id) {
                log('⚠️ Masukkan ID transaksi!', 'warn');
                return;
            }

            log(`📋 Mengambil data transaksi #${id}...`, 'info');
            try {
                const res = await fetch(`{{ url('/printer/transaction-data') }}/${id}`);
                if (!res.ok) throw new Error(`Transaksi #${id} tidak ditemukan.`);
                const data = await res.json();
                await buildAndPrint(data);
            } catch (e) {
                log(`❌ ${e.message}`, 'error');
            }
        }

        async function buildAndPrint(data) {
            log('🖨️ Membangun ESC/POS commands...', 'info');

            const esc = new EscPos().init().lf(2);

            // Header
            esc.align(1).bold(true).doubleWidth(true).textLine(data.header ?? 'RECEIPT').doubleWidth(false).bold(false);
            if (data.subheader) esc.textLine(data.subheader);
            esc.lf().textLine('RECEIPT').lf();
            if (data.code) esc.align(0).textLine(`Kode: ${data.code}`);
            if (data.paid_at) esc.textLine(`Tgl : ${data.paid_at}`);
            if (data.payment_type) esc.textLine(`Bayar: ${data.payment_type.toUpperCase()}`);
            esc.dashedLine();

            // Items
            esc.align(0);
            (data.items ?? []).forEach(item => {
                esc.itemRow(item.name, item.qty, item.price, item.subtotal);
            });

            // Total
            esc.dashedLine('=').bold(true).summaryRow('TOTAL', data.total).bold(false).dashedLine('=');

            // Footer
            esc.lf().align(1);
            if (data.footer) esc.textLine(data.footer);
            esc.lf(4).cut();

            const success = await sendData(esc.getBytes());
            if (success) log('✅ Receipt berhasil dicetak!', 'info');
        }

        // ============================================================
        // UI helpers
        // ============================================================
        function setStatus(state, deviceName = '') {
            const statusText = document.getElementById('statusText');
            const deviceNameEl = document.getElementById('deviceName');
            const btnConnect = document.getElementById('btnConnect');
            const btnDisconnect = document.getElementById('btnDisconnect');
            const btnTestPrint = document.getElementById('btnTestPrint');
            const btnReceiptPrint = document.getElementById('btnReceiptPrint');
            const printerIcon = document.getElementById('printerIcon');

            if (state === 'connected') {
                statusText.innerHTML = '<span class="status-dot status-connected"></span><strong>Connected</strong>';
                deviceNameEl.textContent = deviceName;
                printerIcon.style.color = '#28a745';
                btnConnect.disabled = true;
                btnDisconnect.disabled = false;
                btnTestPrint.disabled = false;
                btnReceiptPrint.disabled = false;
            } else if (state === 'connecting') {
                statusText.innerHTML = '<span class="status-dot status-connecting"></span><strong>Connecting...</strong>';
                deviceNameEl.textContent = 'Scanning...';
                printerIcon.style.color = '#ffc107';
                btnConnect.disabled = true;
            } else {
                statusText.innerHTML = '<span class="status-dot status-disconnected"></span><strong>Disconnected</strong>';
                deviceNameEl.textContent = 'No device connected';
                printerIcon.style.color = '#6c757d';
                btnConnect.disabled = false;
                btnDisconnect.disabled = true;
                btnTestPrint.disabled = true;
                btnReceiptPrint.disabled = true;
            }
        }

        function log(msg, type = '') {
            const el = document.getElementById('log');
            const cls = type ? ` class="${type}"` : '';
            el.innerHTML += `<span${cls}>${msg}</span><br>`;
            el.scrollTop = el.scrollHeight;
        }

        function clearLog() {
            document.getElementById('log').innerHTML = '<span class="info">// Log cleared.<br></span>';
        }
    </script>
</body>

</html>
