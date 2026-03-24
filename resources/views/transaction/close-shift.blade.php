@extends('layouts.main-layout.app-transaction')
@section('title', 'Close Shift')
@section('css')
    <style>
        .closing-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .recap-table td {
            padding: 8px 12px;
            font-size: 14px;
            border-bottom: 1px solid #f1f1f1;
        }

        .recap-table td:first-child {
            color: #555;
        }

        .recap-table td:last-child {
            font-weight: 600;
            text-align: right;
        }

        .recap-section-header td {
            background: #f8f8fb;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #888;
            padding: 6px 12px;
        }
    </style>
@endsection

@section('content')

    {{-- Session Bar (includes Connect Printer button functionality via _session-bar) --}}
    @include('transaction.partials._session-bar', [
        'shift' => $shift,
        'showPrinterBtn' => true,
        'includeCss' => true,
        'pageContext' => 'close-shift',
    ])
    @include('transaction.partials._ticket-sold-modal')
    @include('transaction.partials._wahana-sold-modal')

    <div class="d-flex gap-2 mb-3 flex-wrap">
        <a class="btn btn-outline-secondary" href="{{ route('transaction.sales-revenue') }}">
            <i class="fas fa-arrow-left me-1"></i> Back to Sales
        </a>
    </div>

    <div class="closing-container pb-5">
        <div class="alert alert-warning mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Setelah shift ditutup, shift baru hanya bisa dibuka besok.
        </div>

        <div class="card shadow-sm">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0"><i class="fas fa-lock me-2 text-primary"></i> Tutup Kasir</h5>
            </div>
            <div class="card-body bg-light">
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <label class="form-label text-muted small">Kasir</label>
                        <p class="fw-bold mb-0">{{ $shift->user->name ?? '-' }}</p>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label text-muted small">Counter</label>
                        <p class="fw-bold mb-0">{{ $shift->counter->name ?? '-' }}</p>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label text-muted small">Jam Buka</label>
                        <p class="fw-bold mb-0">
                            {{ $shift->opened_at ? \Carbon\Carbon::parse($shift->opened_at)->format('d M Y H:i') : '-' }}
                        </p>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label text-muted small">Jam Tutup (Sekarang)</label>
                        <p class="fw-bold mb-0" id="current_closed_at">{{ date('d M Y H:i') }}</p>
                    </div>
                </div>

                <div class="card mb-4 bg-white border-0 shadow-sm">
                    <div class="card-body p-0">
                        <table class="table table-sm table-borderless recap-table mb-0 w-100">
                            <tbody>
                                <tr class="table-light border-bottom">
                                    <td>Opening Balance</td>
                                    <td class="text-end fw-bold">{{ format_rupiah($shift->opening_balance) }}</td>
                                </tr>
                                <tr class="table-light border-bottom">
                                    <td>System Balance</td>
                                    <td class="text-end fw-bold text-primary">{{ format_rupiah($system_balance) }}</td>
                                </tr>
                                <tr class="table-light border-bottom">
                                    <td>Total Cash</td>
                                    <td class="text-end">{{ format_rupiah($totalCash) }}</td>
                                </tr>
                                <tr class="table-light border-bottom">
                                    <td>Total Non-Cash</td>
                                    <td class="text-end">{{ format_rupiah($totalNonCash) }}</td>
                                </tr>

                                @if (count($byMethod))
                                    <tr class="recap-section-header">
                                        <td colspan="2" class="fw-bold small">Non-Cash per Metode</td>
                                    </tr>
                                    @foreach ($byMethod as $method => $amount)
                                        <tr>
                                            <td class="ps-4 small">{{ $method ?: '-' }}</td>
                                            <td class="text-end small">{{ format_rupiah($amount) }}</td>
                                        </tr>
                                    @endforeach
                                @endif

                                @if (count($byChannel))
                                    <tr class="recap-section-header">
                                        <td colspan="2" class="fw-bold small">Non-Cash per Channel</td>
                                    </tr>
                                    @foreach ($byChannel as $channel => $amount)
                                        <tr>
                                            <td class="ps-4 small">{{ $channel ?: '-' }}</td>
                                            <td class="text-end small">{{ format_rupiah($amount) }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <form id="closeShiftForm" method="post" action="{{ route('transaction.set-close-shift') }}">
                    @csrf
                    <input type="hidden" name="cashier_shift_id" id="cs_shift_id" value="{{ $shift->id }}">

                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Closing Balance <span class="text-danger">*</span></label>
                            <input type="text" id="cs_closing_balance" name="closing_balance"
                                class="form-control form-control-lg text-primary" placeholder="0" required />
                            <small class="text-muted">Masukkan uang fisik yang ada di laci.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Difference</label>
                            <input type="text" id="cs_difference" name="difference"
                                class="form-control form-control-lg bg-light" readonly />
                            <small class="text-muted">Closing dikurang System Balance.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Status Balance</label>
                            <input type="text" id="cs_status_balance" class="form-control form-control-lg bg-light"
                                readonly placeholder="Auto" />
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Notes / Catatan Singkat</label>
                            <textarea id="cs_notes" name="notes" class="form-control" rows="2"
                                placeholder="Catatan opsional (mis. alasan defisit/surplus)..."></textarea>
                        </div>
                    </div>

                    <div class="my-4">
                        <button type="button" class="btn btn-danger btn-lg w-100 shadow-sm" id="btnDoCloseShift">
                            <i class="fas fa-print me-2"></i> Tutup Shift & Cetak Rekap
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('js/bluetooth-printer.js') }}"></script>
    <script>
        // ── Connect Printer helpers ────────────────────────────────────
        function updateConnectBtn() {
            const btns = document.querySelectorAll('#btnConnectBT, .btnConnectBT');
            btns.forEach(btn => {
                const label = btn.querySelector('#btnConnectBTLabel, .btnConnectBTLabel') || btn.nextElementSibling;
                const dot = btn.querySelector('#printerDot, .printerDot');

                if (window.BTPrinter?.isConnected) {
                    btn.classList.remove('btn-outline-secondary');
                    btn.classList.add('btn-outline-success', 'connected');
                    if (dot) dot.classList.add('connected');
                    if (label) label.textContent = 'Printer Connected ✓';
                } else {
                    btn.classList.remove('btn-outline-success', 'connected');
                    btn.classList.add('btn-outline-secondary');
                    if (dot) dot.classList.remove('connected');
                    if (label) label.textContent = 'Connect Printer';
                }
            });
        }

        async function connectBTPrinter() {
            const btn = document.getElementById('btnConnectBT');
            const label = document.getElementById('btnConnectBTLabel');
            if (!btn) return;
            btn.disabled = true;
            label.textContent = 'Connecting...';
            try {
                const connected = await window.BTPrinter.connectOrReconnect();
                btn.disabled = false;
                updateConnectBtn();
                if (connected) Swal.fire({
                    icon: 'success',
                    title: 'Printer terhubung!',
                    timer: 1200,
                    showConfirmButton: false
                });
            } catch (e) {
                btn.disabled = false;
                label.textContent = 'Connect Printer';
                const result = await Swal.fire({
                    icon: 'warning',
                    title: 'Gagal reconnect',
                    text: 'Printer tidak merespons. Buka scan Bluetooth untuk pair ulang?',
                    showCancelButton: true,
                    confirmButtonText: 'Buka Scan',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'btn btn-primary waves-effect waves-light'
                    },
                    buttonsStyling: false
                });
                if (result.isConfirmed) {
                    const ok = await window.BTPrinter.connect();
                    updateConnectBtn();
                    if (ok) Swal.fire({
                        icon: 'success',
                        title: 'Printer terhubung!',
                        timer: 1200,
                        showConfirmButton: false
                    });
                }
            }
        }

        document.addEventListener('btPrinterConnected', updateConnectBtn);
        document.addEventListener('btPrinterDisconnected', updateConnectBtn);

        // Data rekap untuk printer di-encode dari PHP
        const _systemBalance = {{ $system_balance }};
        const _openingBalance = {{ $shift->opening_balance }};

        const recapDataToPrint = {
            cashier: "{{ $shift->user->name ?? '-' }}",
            counter: "{{ $shift->counter->name ?? '-' }}",
            opened_at: "{{ $shift->opened_at ? \Carbon\Carbon::parse($shift->opened_at)->format('d M Y H:i') : '-' }}",
            closed_at: "-", // Diisi saat cetak
            opening_balance: _openingBalance,
            system_balance: _systemBalance,
            closing_balance: 0,
            difference: 0,
            status_balance: "",
            total_cash: {{ $totalCash }},
            total_noncash: {{ $totalNonCash }},
            grand_total: {{ $grandTotal }},
            by_method: {!! json_encode($byMethod) !!},
            by_channel: {!! json_encode($byChannel) !!}
        };

        const formatRupiahJs = (number) => {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(number);
        };

        // Autonumeric initialization
        document.addEventListener("DOMContentLoaded", function() {
            const closingAN = new AutoNumeric(document.getElementById('cs_closing_balance'), {
                digitGroupSeparator: '.',
                decimalCharacter: ',',
                decimalPlaces: 0,
                currencySymbol: 'Rp ',
                unformatOnSubmit: true
            });
            const diffAN = new AutoNumeric(document.getElementById('cs_difference'), {
                digitGroupSeparator: '.',
                decimalCharacter: ',',
                decimalPlaces: 0,
                currencySymbol: 'Rp ',
                unformatOnSubmit: true,
                readOnly: true
            });

            document.getElementById('cs_closing_balance').addEventListener('input', function() {
                const closing = closingAN.getNumber() || 0;
                const sys = _systemBalance;
                const diff = closing - sys;

                diffAN.set(diff);
                const el = document.getElementById('cs_status_balance');

                if (diff > 0) {
                    el.value = 'Surplus';
                    el.className = 'form-control form-control-lg bg-light text-success fw-bold';
                } else if (diff < 0) {
                    el.value = 'Deficit';
                    el.className = 'form-control form-control-lg bg-light text-danger fw-bold';
                } else {
                    el.value = 'Balanced';
                    el.className = 'form-control form-control-lg bg-light text-primary fw-bold';
                }
            });

            // Action Close Shift Form
            document.getElementById('btnDoCloseShift').addEventListener('click', async function(e) {
                e.preventDefault();

                const closingEl = document.getElementById('cs_closing_balance');
                let closingVal = 0;
                if (closingAN) {
                    closingVal = closingAN.getNumber() || 0;
                }

                if (!closingVal || closingVal <= 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Closing Balance kosong!',
                        text: 'Harap input hitungan fisik uang di laci kasir.',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    });
                    return;
                }

                // Cek koneksi printer terlebih dahulu jika belum
                let connected = false;
                try {
                    connected = await window.BTPrinter.connectOrReconnect();
                } catch (e) {
                    console.log('Printer check failed on close:', e);
                }

                if (!connected) {
                    const resultPrinter = await Swal.fire({
                        title: 'Printer Tidak Terhubung',
                        text: 'Printer belum aktif. Lanjutkan tutup shift tanpa mencetak atau coba hubungkan?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Hubungkan Printer',
                        cancelButtonText: 'Lanjut Tanpa Cetak',
                        customClass: {
                            confirmButton: 'btn btn-primary me-2',
                            cancelButton: 'btn btn-outline-secondary'
                        },
                        buttonsStyling: false
                    });

                    if (resultPrinter.isConfirmed) {
                        try {
                            const paired = await window.BTPrinter.connect();
                            if (!paired) return; // Batal tutup shift
                        } catch (e) {
                            return;
                        }
                    }
                }

                const confirmed = await Swal.fire({
                    title: 'Tutup Shift?',
                    text: 'Pastikan uang direkap dengan benar. Shift ini akan permanen ditutup.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Tutup',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'btn btn-danger waves-effect waves-light me-2',
                        cancelButton: 'btn btn-secondary waves-effect waves-light'
                    },
                    buttonsStyling: false
                });

                if (!confirmed.isConfirmed) return;

                const btn = this;
                btn.disabled = true;

                try {
                    const difference = closingVal - _systemBalance;
                    const statusBalance = difference > 0 ? 'Surplus' : (difference < 0 ? 'Deficit' :
                        'Balanced');

                    const closedDate = new Date();
                    const closedAtFormatted = closedDate.toLocaleString('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    // Prepare recap data untuk dicetak
                    recapDataToPrint.closed_at = closedAtFormatted;
                    recapDataToPrint.closing_balance = closingVal;
                    recapDataToPrint.difference = difference;
                    recapDataToPrint.status_balance = statusBalance;

                    // PRINT PROSES
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mencetak Rekap...';
                    await printShiftRecap(recapDataToPrint);

                    // SETELAH PRINT, SUBMIT FORM 
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...';

                    // Unformat number before submit normally
                    document.getElementById('cs_closing_balance').value = closingVal;
                    document.getElementById('cs_difference').value = difference;

                    document.getElementById('closeShiftForm').submit();

                } catch (err) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-print me-2"></i> Tutup Shift & Cetak Rekap';
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: err.message
                    });
                }
            });
        });

        // ──────────────────────────────────────────────
        // Print Logic for Shift Recap
        // ──────────────────────────────────────────────
        async function printShiftRecap(d) {
            try {
                if (!window.BTPrinter || !window.BTPrinter.isConnected) {
                    console.log('Printer belum tehubung, skip local print.');
                    return false;
                }

                const bytes = window.BTPrinter.buildShiftRecap(d);
                await window.BTPrinter.sendData(bytes);
                return true;
            } catch (err) {
                console.error('Print shift recap error:', err);
                throw new Error('Gagal mencetak struk: ' + err.message);
            }
        }
    </script>
@endsection
