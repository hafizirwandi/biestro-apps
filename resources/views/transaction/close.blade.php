@extends('layouts.main-layout.app-transaction')
@section('title', 'Shift Closed')
@section('css')
    <style>
        .closed-wrap {
            max-width: 600px;
            margin: 0 auto;
            padding: 30px 0;
        }

        .closed-header-card {
            background: linear-gradient(135deg, #1e1e2e 0%, #2d2d44 100%);
            color: #fff;
            border-radius: 14px;
            padding: 28px 28px 20px;
            margin-bottom: 20px;
            text-align: center;
        }

        .closed-header-card .icon-wrap {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            font-size: 28px;
        }

        .closed-header-card h3 {
            font-size: 1.4rem;
            margin: 0 0 4px;
        }

        .closed-header-card p {
            font-size: 13px;
            color: #aaa;
            margin: 0;
        }

        .recap-card {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .recap-card .recap-header {
            background: #f0f0f7;
            padding: 12px 18px;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #666;
        }

        .recap-table td {
            padding: 9px 18px;
            font-size: 13px;
            border-bottom: 1px solid #f1f1f1;
        }

        .recap-table td:first-child {
            color: #666;
        }

        .recap-table td:last-child {
            font-weight: 600;
            text-align: right;
        }

        .recap-table tr:last-child td {
            border-bottom: none;
        }

        .recap-section-header td {
            background: #f8f8fb;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #888;
            padding: 6px 18px;
        }

        .printer-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fff;
            border: 1px solid #e0e0ee;
            border-radius: 10px;
            padding: 12px 18px;
            margin-bottom: 16px;
        }

        .printer-status-info {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
        }

        .printer-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #ccc;
            transition: background 0.3s;
        }

        .printer-dot.connected {
            background: #28c76f;
            box-shadow: 0 0 6px #28c76f80;
        }
    </style>
@endsection

@section('content')
    <div class="closed-wrap">

        {{-- Header --}}
        <div class="closed-header-card">
            <div class="icon-wrap">
                <i class="fas fa-lock"></i>
            </div>
            <h3>Shift Sudah Ditutup</h3>
            <p>Shift hari ini sudah ditutup. Shift baru bisa dibuka besok.</p>
        </div>

        {{-- Shift Info Recap --}}
        @if ($shift)
            <div class="recap-card bg-white">
                <div class="recap-header"><i class="fas fa-file-alt me-2"></i>Rekap Shift</div>
                <table class="recap-table w-100">
                    <tbody>
                        <tr>
                            <td>Kasir</td>
                            <td>{{ $shift->user->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Counter</td>
                            <td>{{ $shift->counter->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Jam Buka</td>
                            <td>{{ $shift->opened_at ? \Carbon\Carbon::parse($shift->opened_at)->format('d M Y H:i') : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td>Jam Tutup</td>
                            <td>{{ $shift->closed_at ? \Carbon\Carbon::parse($shift->closed_at)->format('d M Y H:i') : '-' }}
                            </td>
                        </tr>
                        <tr class="recap-section-header">
                            <td colspan="2">Saldo</td>
                        </tr>
                        <tr>
                            <td>Opening Balance</td>
                            <td>{{ format_rupiah($shift->opening_balance) }}</td>
                        </tr>
                        <tr>
                            <td>System Balance</td>
                            <td>{{ format_rupiah($shift->system_balance) }}</td>
                        </tr>
                        <tr>
                            <td>Closing Balance</td>
                            <td>{{ format_rupiah($shift->closing_balance) }}</td>
                        </tr>
                        <tr>
                            <td>Difference</td>
                            <td
                                class="{{ $shift->difference < 0 ? 'text-danger' : ($shift->difference > 0 ? 'text-success' : '') }}">
                                {{ format_rupiah($shift->difference) }}
                            </td>
                        </tr>
                        <tr>
                            <td>Status Balance</td>
                            <td>
                                @if ($shift->status_balance === 'surplus')
                                    <span class="badge bg-success">Surplus</span>
                                @elseif($shift->status_balance === 'deficit')
                                    <span class="badge bg-danger">Deficit</span>
                                @else
                                    <span class="badge bg-secondary">Balanced</span>
                                @endif
                            </td>
                        </tr>
                        <tr class="recap-section-header">
                            <td colspan="2">Transaksi</td>
                        </tr>
                        <tr>
                            <td>Total Cash</td>
                            <td>{{ format_rupiah($totalCash) }}</td>
                        </tr>
                        <tr>
                            <td>Total Non-Cash</td>
                            <td>{{ format_rupiah($totalNonCash) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Grand Total</strong></td>
                            <td><strong>{{ format_rupiah($grandTotal) }}</strong></td>
                        </tr>

                        @if (count($byMethod))
                            <tr class="recap-section-header">
                                <td colspan="2">Non-Cash per Metode</td>
                            </tr>
                            @foreach ($byMethod as $method => $amount)
                                <tr>
                                    <td class="ps-4">{{ $method ?: '-' }}</td>
                                    <td>{{ format_rupiah($amount) }}</td>
                                </tr>
                            @endforeach
                        @endif

                        @if (count($byChannel))
                            <tr class="recap-section-header">
                                <td colspan="2">Non-Cash per Channel</td>
                            </tr>
                            @foreach ($byChannel as $channel => $amount)
                                <tr>
                                    <td class="ps-4">{{ $channel ?: '-' }}</td>
                                    <td>{{ format_rupiah($amount) }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Printer Bar --}}
        <div class="printer-bar">
            <div class="printer-status-info">
                <span class="printer-dot" id="printerDot"></span>
                <span id="printerStatusText" class="text-muted">Printer belum terhubung</span>
            </div>
            <button class="btn btn-outline-secondary btn-sm" id="btnPairPrinter" onclick="pairPrinter()">
                <i class="fas fa-bluetooth me-1"></i> Pair Printer
            </button>
        </div>

        {{-- Action Buttons --}}
        <div class="d-flex gap-3">
            <button class="btn btn-primary flex-grow-1" id="btnCetakRekap" onclick="cetakRekap()">
                <i class="fas fa-print me-2"></i>Cetak Rekap Shift
            </button>
            <a href="{{ route('logout') }}" class="btn btn-outline-secondary">
                <i class="fas fa-sign-out-alt me-1"></i>Logout
            </a>
        </div>

    </div>
@endsection

@section('script')
    <script src="{{ asset('js/bluetooth-printer.js') }}"></script>
    <script>
        // Shift data for printing
        @php
            $recapJson = json_encode([
                'cashier' => optional(optional($shift)->user)->name ?? '-',
                'counter' => optional(optional($shift)->counter)->name ?? '-',
                'opened_at' => $shift && $shift->opened_at ? \Carbon\Carbon::parse($shift->opened_at)->format('d M Y H:i') : '-',
                'closed_at' => $shift && $shift->closed_at ? \Carbon\Carbon::parse($shift->closed_at)->format('d M Y H:i') : '-',
                'opening_balance' => $shift->opening_balance ?? 0,
                'system_balance' => $shift->system_balance ?? 0,
                'closing_balance' => $shift->closing_balance ?? 0,
                'difference' => $shift->difference ?? 0,
                'status_balance' => ucfirst($shift->status_balance ?? '-'),
                'total_cash' => $totalCash ?? 0,
                'total_noncash' => $totalNonCash ?? 0,
                'grand_total' => $grandTotal ?? 0,
                'by_method' => $byMethod ?? [],
                'by_channel' => $byChannel ?? [],
            ]);
        @endphp
        const shiftRecapData = {!! $recapJson !!};

        // ─── Printer state UI ───────────────────────────────────────────
        function updatePrinterUI() {
            const dot = document.getElementById('printerDot');
            const status = document.getElementById('printerStatusText');
            const btn = document.getElementById('btnPairPrinter');
            if (window.BTPrinter?.isConnected) {
                dot.classList.add('connected');
                status.textContent = 'Printer terhubung ✓';
                status.classList.replace('text-muted', 'text-success');
                btn.textContent = 'Pair Ulang';
            } else {
                dot.classList.remove('connected');
                status.textContent = 'Printer belum terhubung';
                status.classList.replace('text-success', 'text-muted');
            }
        }

        async function pairPrinter() {
            const btn = document.getElementById('btnPairPrinter');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Menghubungkan...';
            try {
                const ok = await window.BTPrinter.connect();
                updatePrinterUI();
                if (ok) Swal.fire({
                    icon: 'success',
                    title: 'Printer terhubung!',
                    timer: 1200,
                    showConfirmButton: false
                });
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: e.message,
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-bluetooth me-1"></i>Pair Printer';
            }
        }

        document.addEventListener('btPrinterConnected', updatePrinterUI);
        document.addEventListener('btPrinterDisconnected', updatePrinterUI);
        updatePrinterUI();

        // ─── Print helpers (same as index.blade.php) ────────────────────
        const formatRupiah = (v) => 'Rp ' + parseInt(v || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        async function cetakRekap() {
            const btn = document.getElementById('btnCetakRekap');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mencetak...';

            try {
                if (window.BTPrinter) {
                    const connected = await window.BTPrinter.connectOrReconnect().catch(() => false);
                    if (connected) {
                        const bytes = window.BTPrinter.buildShiftRecap(shiftRecapData);
                        await window.BTPrinter.sendData(bytes);
                        Swal.fire({
                            icon: 'success',
                            title: 'Rekap dicetak!',
                            timer: 1400,
                            showConfirmButton: false
                        });
                        return;
                    }
                }
                // fallback
                printShiftRecapWindow(shiftRecapData);
            } catch (e) {
                console.warn(e);
                printShiftRecapWindow(shiftRecapData);
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-print me-2"></i>Cetak Rekap Shift';
            }
        }

        function printShiftRecapWindow(d) {
            const rp = (v) => 'Rp ' + parseInt(v || 0).toLocaleString('id-ID');
            const row = (l, r) =>
                `<tr><td style="padding:3px 6px">${l}</td><td style="text-align:right;padding:3px 6px"><b>${r}</b></td></tr>`;
            const sec = (t) =>
                `<tr style="background:#eee"><td colspan="2" style="padding:3px 6px;font-size:10px;text-transform:uppercase;letter-spacing:.5px"><b>${t}</b></td></tr>`;

            let methodRows = Object.entries(d.by_method).map(([k, v]) => row('&nbsp;&nbsp;' + (k || '-'), rp(v))).join('');
            let channelRows = Object.entries(d.by_channel).map(([k, v]) => row('&nbsp;&nbsp;' + (k || '-'), rp(v))).join(
                '');

            const html = `<!DOCTYPE html><html><head><meta charset="utf-8"><title>Rekap Shift</title>
        <style>body{font-family:monospace;font-size:12px;margin:8px;width:300px}
        h2{text-align:center;font-size:14px;margin:4px 0}
        hr{border:none;border-top:1px dashed #000;margin:6px 0}
        table{width:100%;border-collapse:collapse}
        @media print{button{display:none}}</style></head><body>
        <h2>REKAP SHIFT</h2><hr>
        <table>
            ${row('Kasir', d.cashier)}
            ${row('Counter', d.counter)}
            ${row('Jam Buka', d.opened_at)}
            ${row('Jam Tutup', d.closed_at)}
        </table><hr>
        <table>
            ${sec('Saldo')}
            ${row('Opening Balance', rp(d.opening_balance))}
            ${row('System Balance', rp(d.system_balance))}
            ${row('Closing Balance', rp(d.closing_balance))}
            ${row('Difference', rp(d.difference))}
            ${row('Status Balance', d.status_balance)}
        </table><hr>
        <table>
            ${sec('Transaksi')}
            ${row('Total Cash', rp(d.total_cash))}
            ${row('Total Non-Cash', rp(d.total_noncash))}
            ${row('<b>Grand Total</b>', rp(d.grand_total))}
        </table>
        ${ Object.keys(d.by_method).length ? `<hr><table>${sec('Non-Cash per Metode')}${methodRows}</table>` : '' }
        ${ Object.keys(d.by_channel).length ? `<hr><table>${sec('Non-Cash per Channel')}${channelRows}</table>` : '' }
        <hr><p style="text-align:center;margin:4px 0">- TERIMA KASIH -</p>
        <script>window.onload=()=>window.print();<\/script>
        </body></html>`;

            const w = window.open('', '_blank', 'width=420,height=650');
            if (w) {
                w.document.write(html);
                w.document.close();
            }
        }
    </script>
@endsection
