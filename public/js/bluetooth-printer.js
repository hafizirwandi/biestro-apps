/**
 * bluetoothPrinter.js
 * Shared Web Bluetooth ESC/POS printer module for Biestro POS.
 * Auto-reconnects on page load using navigator.bluetooth.getDevices()
 */

// ──────────────────────────────────────────────
// ESC/POS Builder
// ──────────────────────────────────────────────
const ESC = 0x1B, GS = 0x1D, LF = 0x0A;

class EscPos {
    constructor() { this.buffer = []; }
    init()               { this.buffer.push(ESC, 0x40); return this; }
    lf(n = 1)            { for (let i = 0; i < n; i++) this.buffer.push(LF); return this; }
    cut()                { this.buffer.push(GS, 0x56, 0x41, 0x05); return this; }
    align(a)             { this.buffer.push(ESC, 0x61, a); return this; }
    bold(on)             { this.buffer.push(ESC, 0x45, on ? 1 : 0); return this; }
    doubleWidth(on)      { this.buffer.push(ESC, 0x21, on ? 0x20 : 0x00); return this; }
    doubleSize(on)       { this.buffer.push(GS, 0x21, on ? 0x11 : 0x00); return this; }

    text(str) {
        // Support both real newlines and escaped \n (e.g. from DB settings)
        const lines = String(str).split(/\\n|\n/);
        lines.forEach((line, idx) => {
            const bytes = new TextEncoder().encode(line);
            bytes.forEach(b => this.buffer.push(b));
            if (idx < lines.length - 1) this.buffer.push(LF);
        });
        return this;
    }
    textLine(str)        { return this.text(str).lf(); }
    // Print multi-line string (splits on \n) with LF after each line
    textLines(str) {
        String(str).split(/\\n|\n/).forEach(line => this.textLine(line));
        return this;
    }
    dashedLine(ch = '-', len = 32) { return this.textLine(ch.repeat(len)); }

    // Print a QR code (ESC/POS GS ( k command set, model 2)
    qr(content, size = 5) {
        const data = new TextEncoder().encode(String(content));
        const storeLen = data.length + 3;
        const pL = storeLen & 0xFF;
        const pH = (storeLen >> 8) & 0xFF;

        this.buffer.push(GS, 0x28, 0x6B, 0x04, 0x00, 0x31, 0x41, 0x32, 0x00); // model 2
        this.buffer.push(GS, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x43, size); // module size
        this.buffer.push(GS, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x45, 48); // EC level L
        this.buffer.push(GS, 0x28, 0x6B, pL, pH, 0x31, 0x50, 0x30); // store data
        data.forEach(b => this.buffer.push(b));
        this.buffer.push(GS, 0x28, 0x6B, 0x03, 0x00, 0x31, 0x51, 0x30); // print
        return this;
    }

    itemRow(name, qty, price, subtotal) {
        const rp = n => 'Rp.' + Number(n).toLocaleString('id-ID');
        this.textLine(String(name).substring(0, 30));
        const left = `  ${qty} x ${rp(price)}`;
        const right = rp(subtotal);
        const space = ' '.repeat(Math.max(0, 32 - left.length - right.length));
        return this.textLine(left + space + right);
    }

    summaryRow(label, value) {
        const rp = n => 'Rp.' + Number(n).toLocaleString('id-ID');
        const v = rp(value);
        const space = ' '.repeat(Math.max(0, 32 - label.length - v.length));
        return this.textLine(label + space + v);
    }

    getBytes() { return new Uint8Array(this.buffer); }
}

// ──────────────────────────────────────────────
// BLE UUIDs (common thermal printer profiles)
// ──────────────────────────────────────────────
const BLE_SERVICE_UUIDS = [
    '000018f0-0000-1000-8000-00805f9b34fb',
    'e7810a71-73ae-499d-8c15-faa9aef0c3f2',
    '49535343-fe7d-4ae5-8fa9-9fafd205e455',
];
const BLE_CHAR_UUIDS = [
    '00002af1-0000-1000-8000-00805f9b34fb',
    'bef8d6c9-9c21-4c9e-b632-bd58c1009f9f',
    '49535343-8841-43f4-a8d4-ecbe34729bb3',
];

// ──────────────────────────────────────────────
// Global state
// ──────────────────────────────────────────────
window.BTPrinter = {
    device: null,
    characteristic: null,
    isConnected: false,
    deviceName: '',

    // ── Internal: establish GATT connection to a device ──────
    async _connectDevice(device, logFn) {
        const log = logFn || (() => {});

        device.removeEventListener('gattserverdisconnected', this._disconnectHandler);
        this._disconnectHandler = () => this._onDisconnected(logFn);
        device.addEventListener('gattserverdisconnected', this._disconnectHandler);

        const server = await device.gatt.connect();

        // find service
        let service = null;
        for (const uuid of BLE_SERVICE_UUIDS) {
            try { service = await server.getPrimaryService(uuid); break; }
            catch (_) {}
        }
        if (!service) {
            const svcs = await server.getPrimaryServices();
            if (svcs.length) {
                service = svcs[0];
                log(`⚠️ Service: ${service.uuid}`, 'warn');
            } else throw new Error('Tidak ada BLE service ditemukan.');
        }

        // find writable characteristic
        this.characteristic = null;
        for (const uuid of BLE_CHAR_UUIDS) {
            try { this.characteristic = await service.getCharacteristic(uuid); break; }
            catch (_) {}
        }
        if (!this.characteristic) {
            const chars = await service.getCharacteristics();
            for (const c of chars) {
                if (c.properties.write || c.properties.writeWithoutResponse) {
                    this.characteristic = c;
                    log(`⚠️ Char: ${c.uuid}`, 'warn');
                    break;
                }
            }
        }
        if (!this.characteristic) throw new Error('Tidak ada writable characteristic.');

        this.device = device;
        this.isConnected = true;
        this.deviceName = device.name ?? 'Unknown';
        localStorage.setItem('btPrinterConnected', '1');
        localStorage.setItem('btPrinterName', this.deviceName);
        return true;
    },

    // ── Connect (manual scan — requires user gesture) ──────────
    async connect(logFn) {
        const log = logFn || (() => {});
        if (!navigator.bluetooth) {
            log('❌ Web Bluetooth tidak didukung. Gunakan Chrome/Edge.', 'error');
            return false;
        }
        try {
            log('🔍 Scanning BLE devices...', 'info');
            const device = await navigator.bluetooth.requestDevice({
                acceptAllDevices: true,
                optionalServices: BLE_SERVICE_UUIDS,
            });
            log(`✅ Device ditemukan: <b>${device.name ?? '(no name)'}</b>`, 'info');
            await this._connectDevice(device, logFn);
            log(`🖨️ Printer siap: <b>${this.deviceName}</b>`, 'info');
            document.dispatchEvent(new CustomEvent('btPrinterConnected'));
            return true;
        } catch (err) {
            if (err.name !== 'NotFoundError') {
                log(`❌ Connect gagal: ${err.message}`, 'error');
            } else {
                log('ℹ️ Dibatalkan.', 'warn');
            }
            this.isConnected = false;
            return false;
        }
    },

    // ── Smart connect: silent if paired, popup ONLY if no device known ──
    // Call this before every print action.
    async connectOrReconnect(logFn) {
        const log = logFn || (() => {});

        // Already connected — nothing to do
        if (this.isConnected && this.characteristic) return true;

        // Try silent reconnect via previously paired/granted devices (no popup)
        if (navigator.bluetooth && typeof navigator.bluetooth.getDevices === 'function') {
            const devices = await navigator.bluetooth.getDevices();

            if (devices.length > 0) {
                const savedName = localStorage.getItem('btPrinterName') ?? '';
                const target = devices.find(d => d.name === savedName) ?? devices[0];

                // Retry up to 3 times — GATT can fail transiently after page reload
                // if the printer is not yet advertising. Exponential backoff: 0ms, 600ms, 1200ms
                const MAX_ATTEMPTS = 3;
                let lastErr;
                for (let attempt = 1; attempt <= MAX_ATTEMPTS; attempt++) {
                    try {
                        if (attempt > 1) {
                            const delay = (attempt - 1) * 600;
                            log(`⏳ Retry ${attempt}/${MAX_ATTEMPTS} (menunggu ${delay}ms)...`, 'info');
                            await new Promise(r => setTimeout(r, delay));
                        }
                        log(`🔄 Menghubungkan ke <b>${target.name ?? 'printer'}</b> (attempt ${attempt})...`, 'info');
                        await this._connectDevice(target, logFn);
                        log(`🖨️ Printer siap.`, 'info');
                        document.dispatchEvent(new CustomEvent('btPrinterConnected'));
                        return true;
                    } catch (e) {
                        lastErr = e;
                        log(`⚠️ Attempt ${attempt} gagal: ${e.message}`, 'warn');
                    }
                }

                // All retries exhausted — DO NOT open scan popup.
                // Propagate error to caller (print fn will show Swal).
                // User can click 'Connect Printer' button to retry manually.
                throw new Error(`Printer tidak bisa terhubung setelah ${MAX_ATTEMPTS}x percobaan. Coba klik Reconnect Printer.`);
            }
        }

        // No previously granted device — first time, must scan.
        log('🔍 Belum ada printer. Silakan pilih printer...', 'info');
        return await this.connect(logFn);
    },

    // Uses navigator.bluetooth.getDevices() — returns previously paired devices
    async autoReconnect(logFn) {
        const log = logFn || (() => {});

        if (!localStorage.getItem('btPrinterConnected')) return false;
        if (!navigator.bluetooth) return false;

        // getDevices() is available in Chrome 85+ with Web Bluetooth flag / Chrome 89+ stable
        if (typeof navigator.bluetooth.getDevices !== 'function') {
            log('ℹ️ Auto-reconnect tidak didukung versi browser ini.', 'warn');
            return false;
        }

        try {
            const devices = await navigator.bluetooth.getDevices();
            if (!devices || devices.length === 0) {
                log('ℹ️ Tidak ada device Bluetooth yang dikenal.', 'warn');
                return false;
            }

            const savedName = localStorage.getItem('btPrinterName') ?? '';
            // Prefer the previously used printer by name, else first device
            const target = devices.find(d => d.name === savedName) ?? devices[0];

            log(`🔄 Auto-reconnect ke <b>${target.name ?? '(no name)'}</b>...`, 'info');
            await this._connectDevice(target, logFn);
            log(`🖨️ Printer reconnected: <b>${this.deviceName}</b>`, 'info');
            document.dispatchEvent(new CustomEvent('btPrinterConnected'));
            return true;
        } catch (err) {
            // Silently fail — user can manually reconnect
            log(`⚠️ Auto-reconnect gagal: ${err.message}`, 'warn');
            this.isConnected = false;
            return false;
        }
    },

    // ── Disconnect ───────────────────────────────────────────────
    disconnect() {
        if (this.device?.gatt?.connected) this.device.gatt.disconnect();
        this._onDisconnected();
    },

    _disconnectHandler: null,

    _onDisconnected(logFn) {
        this.characteristic = null;
        this.isConnected = false;
        this.deviceName = '';
        localStorage.removeItem('btPrinterConnected');
        localStorage.removeItem('btPrinterName');
        if (logFn) logFn('🔌 Printer disconnected.', 'warn');
        document.dispatchEvent(new CustomEvent('btPrinterDisconnected'));
    },

    // ── Send raw bytes ────────────────────────────────────────────
    async sendData(bytes, logFn) {
        const log = logFn || (() => {});
        if (!this.characteristic) {
            log('❌ Printer belum terkoneksi!', 'error');
            throw new Error('Printer belum terkoneksi');
        }
        
        // Printer thermal POS Bluetooth BLE 4.0 seringkali memiliki limit MTU absolut 20 bytes per paket!
        // Jika browser/OS HP tidak otomatis memecah MTU, pengiriman > 20 bytes akan langsung "GATT operation failed".
        const CHUNK = 20;
        try {
            for (let i = 0; i < bytes.length; i += CHUNK) {
                const chunk = bytes.slice(i, i + CHUNK);
                if (this.characteristic.properties.write) {
                    // writeValue mengunggu balasan konfirmasi dari printer, ini paling stabil!
                    await this.characteristic.writeValue(chunk);
                } else if (this.characteristic.properties.writeWithoutResponse) {
                    await this.characteristic.writeValueWithoutResponse(chunk);
                    // Karena tanpa response, kita paksa delay agar buffer printer tidak meluap
                    await new Promise(r => setTimeout(r, 10));
                }
            }
            return true;
        } catch (e) {
            log(`❌ Send gagal: ${e.message}`, 'error');
            // WAJIB lempar (throw) error bawaan browser agar kelihatan aslinya
            throw new Error(`Koneksi Bluetooth terputus/gagal: ${e.message}`);
        }
    },

    // ── Build receipt ESC/POS ─────────────────────────────────────
    buildReceipt(data) {
        const esc = new EscPos().init().lf(2);
        // Header (supports \n for multi-line, e.g. "BIESTRO\nINDONESIA")
        esc.align(1).bold(true).doubleWidth(true);
        esc.textLines(data.header ?? 'RECEIPT');
        esc.doubleWidth(false).bold(false);
        if (data.subheader) esc.textLines(data.subheader);
        esc.lf().textLine('RECEIPT').lf();
        if (data.code)         esc.align(0).textLine(`Kode : ${data.code}`);
        if (data.paid_at)      esc.textLine(`Tgl  : ${data.paid_at}`);
        if (data.payment_type) esc.textLine(`Bayar: ${data.payment_type.toUpperCase()}`);
        esc.dashedLine();
        esc.align(0);
        (data.items ?? []).forEach(it => esc.itemRow(it.name, it.qty, it.price, it.subtotal));
        esc.dashedLine('=').bold(true).summaryRow('TOTAL', data.total).bold(false).dashedLine('=');
        esc.lf().align(1);
        if (data.footer) esc.textLines(data.footer);
        esc.lf(4).cut();
        return esc.getBytes();
    },

    // ── Build ticket ESC/POS ──────────────────────────────────────
    buildTicket(data) {
        const esc = new EscPos().init().lf(2);

        // ── COPY label (cetak kembali) ──────────────
        if (data.is_copy) {
            esc.align(1).bold(true).textLine('*** CETAK KEMBALI ***').bold(false);
        }

        // Header (supports \n for multi-line)
        esc.align(1).bold(true).doubleWidth(true);
        esc.textLines(data.header ?? '');
        esc.doubleWidth(false).bold(false);
        if (data.subheader) esc.textLines(data.subheader);
        esc.lf().dashedLine();
        esc.align(1).doubleSize(true).bold(true).textLine(data.wahana_name ?? '').bold(false).doubleSize(false);
        esc.textLine('-------').textLine(data.wahana_key ?? '').textLine('-------');
        esc.lf();
        esc.align(0).textLine(data.ticket_code ?? '');
        if (data.created_at) esc.textLine(data.created_at);
        esc.lf().align(1);
        if (data.ticket_code) esc.qr(data.ticket_code);
        esc.align(0).lf().dashedLine();
        esc.align(1);
        if (data.footer) esc.textLines(data.footer);
        esc.lf(4).cut();
        return esc.getBytes();
    },

    // ── Build shift recap ESC/POS ─────────────────────────────────
    buildShiftRecap(d) {
        const rp = (v) => 'Rp ' + parseInt(v || 0).toLocaleString('id-ID');
        const esc = new EscPos().init().lf();

        esc.align(1).bold(true).doubleWidth(true).textLine('REKAP SHIFT').doubleWidth(false).bold(false);
        esc.dashedLine();
        esc.align(0);
        esc.textLine(`Kasir   : ${d.cashier}`);
        esc.textLine(`Counter : ${d.counter}`);
        esc.textLine(`Buka    : ${d.opened_at}`);
        esc.textLine(`Tutup   : ${d.closed_at}`);
        esc.dashedLine();
        esc.textLine(`Opening Balance : ${rp(d.opening_balance)}`);
        esc.textLine(`System Balance  : ${rp(d.system_balance)}`);
        esc.textLine(`Closing Balance : ${rp(d.closing_balance)}`);
        esc.textLine(`Difference      : ${rp(d.difference)}`);
        esc.textLine(`Status Balance  : ${d.status_balance}`);
        esc.dashedLine();
        esc.textLine(`Total Cash      : ${rp(d.total_cash)}`);
        esc.textLine(`Total Non-Cash  : ${rp(d.total_noncash)}`);
        esc.bold(true).textLine(`Grand Total     : ${rp(d.grand_total)}`).bold(false);

        if (d.by_method && Object.keys(d.by_method).length) {
            esc.dashedLine('-', 32);
            esc.textLine('Non-Cash per Metode:');
            Object.entries(d.by_method).forEach(([k, v]) => esc.textLine(`  ${k || '-'} : ${rp(v)}`));
        }
        if (d.by_channel && Object.keys(d.by_channel).length) {
            esc.dashedLine('-', 32);
            esc.textLine('Non-Cash per Channel:');
            Object.entries(d.by_channel).forEach(([k, v]) => esc.textLine(`  ${k || '-'} : ${rp(v)}`));
        }

        esc.dashedLine().align(1).textLine('- TERIMA KASIH -');
        esc.lf(4).cut();
        return esc.getBytes();
    },
};

// Pages should call window.BTPrinter.autoReconnect() explicitly
// after registering their 'btPrinterConnected' event listeners.
