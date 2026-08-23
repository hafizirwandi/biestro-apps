@extends('layouts.main-layout.app-transaction')
@section('title', 'Sales Revenue')
@section('css')
    <style>
        .badge-voided {
            background: #ff4d4f;
            color: #fff;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .badge-paid {
            background: #28c76f;
            color: #fff;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .ticket-used {
            text-decoration-line: line-through;
        }

        a.ticket-used {
            pointer-events: none;
            opacity: 0.5;
            color: gray;
        }

        .card-order {
            height: 45vh;
            overflow: scroll;
        }

        .underline {
            text-decoration: underline !important;
        }

        .cart-item {
            gap: 10px;
        }

        .cart-item>div {
            min-width: fit-content;
        }

        .title-order {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
        }

        .action-icon {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            color: inherit;
            font-size: 11px;
            text-decoration: none;
            padding: 4px 8px;
            border-radius: 6px;
            transition: background 0.15s;
            line-height: 1;
        }

        .action-icon:hover {
            background: rgba(115, 103, 240, 0.08);
        }

        .action-icon i {
            font-size: 16px;
        }

        .action-icon.action-danger {
            color: #ea5455;
        }

        .action-icon.action-danger:hover {
            background: rgba(234, 84, 85, 0.08);
        }
    </style>
@endsection

@section('content')

    {{-- ░░ Session Header Bar (component) ░░ --}}
    @include('transaction.partials._session-bar', [
        'shift' => $shift,
        'showPrinterBtn' => true,
        'includeCss' => true,
        'pageContext' => 'sales-revenue',
    ])
    @include('transaction.partials._ticket-sold-modal')
    @include('transaction.partials._wahana-sold-modal')



    {{-- Filter --}}
    <div class="mb-3">
        <label class="form-label d-block">Filter:</label>
        <div class="btn-group flex-wrap" role="group">
            <a href="{{ route('transaction.sales-revenue') }}"
                class="btn btn-outline-primary {{ request('filter') == '' ? 'active' : '' }}">Semua</a>
            <a href="{{ route('transaction.sales-revenue', ['filter' => 'cash']) }}"
                class="btn btn-outline-primary {{ request('filter') == 'cash' ? 'active' : '' }}">Cash</a>
            <a href="{{ route('transaction.sales-revenue', ['filter' => 'noncash']) }}"
                class="btn btn-outline-primary {{ request('filter') == 'noncash' ? 'active' : '' }}">Non-Cash</a>
            <a href="{{ route('transaction.sales-revenue', ['filter' => 'qris']) }}"
                class="btn btn-outline-secondary {{ request('filter') == 'qris' ? 'active' : '' }}">QRIS</a>
            <a href="{{ route('transaction.sales-revenue', ['filter' => 'transfer']) }}"
                class="btn btn-outline-secondary {{ request('filter') == 'transfer' ? 'active' : '' }}">Transfer</a>
            <a href="{{ route('transaction.sales-revenue', ['filter' => 'edc']) }}"
                class="btn btn-outline-secondary {{ request('filter') == 'edc' ? 'active' : '' }}">EDC</a>
            <a href="{{ route('transaction.sales-revenue', ['filter' => 'lainnya']) }}"
                class="btn btn-outline-secondary {{ request('filter') == 'lainnya' ? 'active' : '' }}">Lainnya</a>
            <a href="{{ route('transaction.sales-revenue', ['filter' => 'voided']) }}"
                class="btn btn-outline-danger {{ request('filter') == 'voided' ? 'active' : '' }}">
                Void <span class="badge bg-danger ms-1">{{ $totalVoided }}</span>
            </a>
        </div>
    </div>

    {{-- Tabel Transaksi --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <table class="table table-responsive  datatable">
                        <thead>
                            <tr>
                                <th>Kode Transaksi</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Metode</th>
                                <th>Dibayar</th>
                                <th>Non-Cash Method</th>
                                <th>Non-Cash Channel</th>
                                <th>Waktu</th>
                                <th>Alasan Void</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transaction as $r)
                                <tr class="{{ $r->payment_status === 'voided' ? 'table-danger opacity-75' : '' }}">
                                    <td>{{ $r->transaction_code }}</td>
                                    <td>
                                        @if ($r->payment_status === 'voided')
                                            <span class="badge-voided">VOID</span>
                                        @else
                                            <span class="badge-paid">PAID</span>
                                        @endif
                                    </td>
                                    <td>{{ format_rupiah($r->total_amount) }}</td>
                                    <td>{{ $r->payment_type }}</td>
                                    <td>{{ format_rupiah($r->amount_given) }}</td>
                                    <td>{{ $r->noncash_method }}</td>
                                    <td>{{ $r->noncash_channel }}</td>
                                    <td>{{ $r->paid_at }}</td>
                                    <td class="text-muted small" style="max-width:180px;">
                                        @if ($r->payment_status === 'voided' && $r->void_reason)
                                            <span title="{{ $r->void_reason }}"
                                                style="cursor:help; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:block;">
                                                {{ \Illuminate\Support\Str::limit($r->void_reason, 40) }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        <div class="d-flex align-items-center gap-1">
                                            {{-- Tombol Print → buka modal --}}
                                            <a href="javascript:;" onclick="openTransactionViewModal({{ $r->id }})"
                                                class="action-icon text-body" title="Lihat & Print">
                                                <i class="ti ti-printer"></i>
                                                <span>Print</span>
                                            </a>

                                            {{-- Tombol Void (hanya jika paid) --}}
                                            @if ($r->payment_status === 'paid')
                                                <a href="javascript:;"
                                                    onclick="confirmVoid({{ $r->id }}, '{{ $r->transaction_code }}')"
                                                    class="action-icon action-danger" title="Batalkan / Void">
                                                    <i class="ti ti-ban"></i>
                                                    <span>Void</span>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Footer --}}
    <div class="mt-3 p-3 border rounded bg-white">
        <div class="row">
            <div class="col-md-6">
                <h5 class="mb-3">Summary Sales Shift Ini</h5>
                <table class="table table-borderless table-sm">
                    <tr>
                        <td>Total Cash</td>
                        <td class="text-end fw-bold">{{ format_rupiah($totalCash) }}</td>
                    </tr>
                    <tr>
                        <td>Total Non-Cash</td>
                        <td class="text-end fw-bold">{{ format_rupiah($totalNonCash) }}</td>
                    </tr>
                    <tr class="border-top">
                        <td><strong>Grand Total Sales</strong></td>
                        <td class="text-end fw-bold fs-5 text-primary">{{ format_rupiah($grandTotal) }}</td>
                    </tr>
                    @if ($totalVoided > 0)
                        <tr>
                            <td class="text-danger">Transaksi Void</td>
                            <td class="text-end text-danger fw-bold">{{ $totalVoided }} transaksi</td>
                        </tr>
                    @endif
                </table>
            </div>
            <div class="col-md-6">
                <h5 class="mb-3">Detail Non-Cash Channel</h5>
                @if (isset($nonCashBreakdown) && count($nonCashBreakdown) > 0)
                    <ul class="list-group">
                        @foreach ($nonCashBreakdown as $channel => $amount)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-1">
                                {{ $channel ?: 'Unspecified' }}
                                <span class="badge bg-label-primary">{{ format_rupiah($amount) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted small fst-italic">Tidak ada transaksi non-cash pada shift ini.</p>
                @endif
            </div>
        </div>
        <hr>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="text-muted">Opening Balance:</span>
                <strong>{{ format_rupiah($shift->opening_balance) }}</strong>
            </div>
            <div>
                <span class="text-muted">Total Cash in Drawer (Opening + Sales):</span>
                <strong class="fs-5 text-success">{{ format_rupiah($shift->opening_balance + $totalCash) }}</strong>
            </div>
        </div>
    </div>

    {{-- ─────────────────────────────────────────────────────────────
         Transaction View Modal (sama seperti di /transaction index)
    ───────────────────────────────────────────────────────────────── --}}
    <div class="modal fade" id="transactionViewModal">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="transactionViewBody">
                    <div class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script src="{{ asset('js/bluetooth-printer.js') }}"></script>
    <script>
        // ── DataTable ─────────────────────────────────────────────────
        document.addEventListener("DOMContentLoaded", function() {
            $('.datatable2').DataTable({
                paging: false,
                searching: false,
                info: false
            });

            // Auto-reconnect printer
            window.BTPrinter.autoReconnect();
        });

        // ── Connect Printer helpers ────────────────────────────────────
        function updateConnectBtn() {
            const btns = document.querySelectorAll('#btnConnectBT, .btnConnectBT');
            btns.forEach(btn => {
                const label = btn.querySelector('#btnConnectBTLabel, .btnConnectBTLabel') || btn.nextElementSibling;
                const dot = btn.querySelector('#printerDot, .printerDot');

                if (window.PRINT_MODE === 'bridge') {
                    btn.classList.remove('btn-outline-secondary');
                    btn.classList.add('btn-outline-success', 'connected');
                    if (dot) dot.classList.add('connected');
                    if (label) label.textContent = 'Print Bridge Aktif';
                } else if (window.BTPrinter?.isConnected) {
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

                // Bridge mode has no BT device to scan for — show the bridge
                // error as-is instead of offering a Bluetooth pairing popup.
                if (window.PRINT_MODE === 'bridge') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Print Bridge tidak terdeteksi',
                        text: e.message,
                        customClass: {
                            confirmButton: 'btn btn-primary waves-effect waves-light'
                        },
                        buttonsStyling: false
                    });
                    return;
                }

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

        // ── Load Transaction View Modal ────────────────────────────────
        async function openTransactionViewModal(id) {
            const body = document.getElementById('transactionViewBody');
            const modal = new bootstrap.Modal(document.getElementById('transactionViewModal'));
            body.innerHTML =
                '<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2 text-muted">Memuat data transaksi...</p></div>';
            modal.show();
            try {
                const res = await fetch(`{{ url('/transaction/view-partial') }}/${id}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!res.ok) throw new Error('Gagal memuat detail transaksi.');
                body.innerHTML = await res.text();
                if (typeof updateConnectBtn === 'function') updateConnectBtn();
            } catch (err) {
                body.innerHTML = `<div class="alert alert-danger">${err.message}</div>`;
            }
        }

        // ── Print Bill (receipt) ───────────────────────────────────────
        async function printBill(id) {
            const btn = document.getElementById('btnPrintBill');
            let billPrinted = false;
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Mencetak...';
            }
            try {
                const connected = await window.BTPrinter.connectOrReconnect();
                if (!connected) throw new Error('Printer tidak terkoneksi.');
                const res = await fetch(`{{ url('/printer/transaction-data') }}/${id}`);
                if (!res.ok) throw new Error('Gagal mengambil data struk.');
                const data = await res.json();
                const bytes = window.BTPrinter.buildReceipt(data);
                const ok = await window.BTPrinter.sendData(bytes);
                if (ok) {
                    await fetch('{{ route('transaction.print-bill') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            transaction_id: id,
                            _bt: 1
                        })
                    });
                    billPrinted = true;
                    Swal.fire({
                        icon: 'success',
                        title: 'Struk dicetak!',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: e.message
                });
            } finally {
                if (btn) {
                    if (billPrinted) {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fas fa-check me-1"></i> Sudah Dicetak';
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-print me-1"></i> Print Bill';
                    }
                }
            }
        }

        // ── Print Single Ticket ────────────────────────────────────────
        async function printTicket(id, el) {
            const icon = el?.querySelector('i');
            const origClass = icon?.className;
            if (icon) icon.className = 'fas fa-spinner fa-spin ti-sm';
            try {
                const connected = await window.BTPrinter.connectOrReconnect();
                if (!connected) throw new Error('Printer tidak terkoneksi.');
                const res = await fetch(`{{ url('/printer/ticket-data') }}/${id}`);
                if (!res.ok) throw new Error('Gagal mengambil data tiket.');
                const data = await res.json();
                const bytes = window.BTPrinter.buildTicket(data);
                const ok = await window.BTPrinter.sendData(bytes);
                if (ok) {
                    const flagRes = await fetch('{{ route('transaction.print-ticket') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            id,
                            _bt: 1
                        })
                    });
                    const flagData = await flagRes.json();
                    if (flagData.status === 'success') {
                        const row = el?.closest('.order-item');
                        if (row) {
                            row.querySelectorAll('.fw-bold, .text-muted').forEach(e => e.classList.add('ticket-used'));
                            el.classList.add('ticket-used');
                        }
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Tiket dicetak!',
                        timer: 1000,
                        showConfirmButton: false
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: e.message
                });
            } finally {
                if (icon) icon.className = origClass;
            }
        }

        // ── Print All Tickets ──────────────────────────────────────────
        async function printTicketAll(transactionId) {
            const btn = document.getElementById('btnPrintTicketAll');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Mencetak...';
            let permanentlyDisabled = false;
            try {
                const connected = await window.BTPrinter.connectOrReconnect();
                if (!connected) throw new Error('Printer tidak terkoneksi.');
                const res = await fetch(`{{ url('/printer/tickets-data') }}/${transactionId}`);
                if (!res.ok) throw new Error('Gagal mengambil data tiket.');
                const {
                    tickets: allTickets
                } = await res.json();

                // Only print tickets that haven't succeeded yet — a retry after a
                // partial failure shouldn't reprint ones that already came out.
                const tickets = allTickets.filter(t => !t.count_print);
                if (tickets.length === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Semua tiket sudah dicetak',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    permanentlyDisabled = true;
                    return;
                }

                // Print — bridge mode sends all tickets as ONE job (fast, avoids
                // per-job spooler overhead); Bluetooth mode stays ticket-by-ticket
                // with retry (BLE can't safely batch — MTU-limited, no partial retry).
                let printedIds = [];
                if (window.PRINT_MODE === 'bridge') {
                    printedIds = await window.BTPrinter.printTicketsBatchViaBridge(tickets);
                } else {
                    for (const t of tickets) {
                        const ok = await window.BTPrinter.printTicketWithRetry(t);
                        if (ok) printedIds.push(t.id);
                    }
                }
                const printed = printedIds.length;

                if (printed > 0) {
                    const flagRes = await fetch('{{ route('transaction.print-ticket-all') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            transaction_id: transactionId,
                            ticket_ids: printedIds,
                            _bt: 1
                        })
                    });
                    const flagData = await flagRes.json();
                    if (flagData.status === 'success' && printed === tickets.length) {
                        document.querySelectorAll('.order-item').forEach(row => {
                            row.querySelectorAll('.fw-bold, .text-muted').forEach(e => e.classList.add(
                                'ticket-used'));
                            const link = row.querySelector('a');
                            if (link) link.classList.add('ticket-used');
                        });
                        permanentlyDisabled = true;
                    }
                }

                if (printed === tickets.length) {
                    Swal.fire({
                        icon: 'success',
                        title: `${printed} tiket dicetak!`,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: `${printed} dari ${tickets.length} tiket berhasil dicetak`,
                        text: 'Sisanya gagal terkirim ke printer. Klik tombol lagi untuk mencetak sisa tiket.'
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: e.message
                });
            } finally {
                if (permanentlyDisabled) {
                    btn.innerHTML = '<i class="fas fa-check me-1"></i> Sudah Dicetak';
                    btn.disabled = true;
                } else {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-print me-1"></i> Print Ticket';
                }
            }
        }

        // ── Re-print with SPV PIN ──────────────────────────────────────
        function confirmRePrint(id) {
            Swal.fire({
                title: 'Supervisor Approval',
                html: '<p class="mb-0">Masukkan PIN Supervisor untuk mencetak kembali tiket ini.</p>',
                target: document.getElementById('transactionViewModal') || document.body,
                input: 'password',
                inputAttributes: {
                    autocapitalize: 'off',
                    placeholder: 'PIN Password (4–6 digit)',
                    minlength: '4',
                    maxlength: '6'
                },
                customClass: {
                    confirmButton: 'btn btn-primary waves-effect waves-light me-2',
                    cancelButton: 'btn btn-outline-secondary'
                },
                buttonsStyling: false,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-print me-1"></i> Reprint',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                preConfirm: async (password) => {
                    if (!password) {
                        Swal.showValidationMessage('PIN wajib diisi');
                        return false;
                    }
                    if (password.length < 4 || password.length > 6) {
                        Swal.showValidationMessage('PIN harus 4–6 karakter');
                        return false;
                    }

                    try {
                        const res = await fetch('{{ route('transaction.reprint') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                id: id,
                                spv_code: password
                            })
                        });
                        const data = await res.json();

                        if (res.ok && data.status === 'success') {
                            return data;
                        } else {
                            Swal.showValidationMessage(data.message || 'Gagal mereset tiket');
                            return false;
                        }
                    } catch (e) {
                        Swal.showValidationMessage('Terjadi kesalahan jaringan');
                        return false;
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Unflag everything in UI
                    document.querySelectorAll('.order-item').forEach(row => {
                        row.querySelectorAll('.ticket-used').forEach(e => e.classList.remove(
                            'ticket-used'));
                    });

                    // Re-enable Print All Ticket button
                    const btnAll = document.getElementById('btnPrintTicketAll');
                    if (btnAll) btnAll.disabled = false;

                    // Re-enable Print Bill button
                    const btnBill = document.getElementById('btnPrintBill');
                    if (btnBill) {
                        btnBill.disabled = false;
                        btnBill.className = 'btn btn-primary w-100 mt-3';
                        btnBill.innerHTML = '<i class="fas fa-print me-1"></i> Print Bill';
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Siap Cetak Ulang',
                        text: 'Silakan cetak kembali tiket atau struk.',
                        timer: 1500,
                        showConfirmButton: false,
                        target: document.getElementById('transactionViewModal') || document.body,
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        },
                        buttonsStyling: false
                    });
                }
            });
        }

        // ── Void Transaction ───────────────────────────────────────────
        async function confirmVoid(id, code) {
            const {
                value: formValues,
                isConfirmed
            } = await Swal.fire({
                title: 'Void Transaksi',
                html: `
                    <p class="mb-3 text-danger fw-bold fs-5">${code}</p>
                    <div class="text-start mb-3">
                        <label class="form-label mb-1">Alasan Pembatalan <span class="text-danger">*</span></label>
                        <textarea id="voidReasonInput" class="form-control" rows="2"
                            placeholder="Contoh: Salah input, permintaan customer..."
                            style="resize:vertical;"></textarea>
                    </div>
                    <div class="text-start mb-2">
                        <label class="form-label mb-1">PIN Supervisor <span class="text-danger">*</span></label>
                        <input id="voidSpvPinInput" type="password" class="form-control"
                            placeholder="PIN Password (4–6 digit)" minlength="4" maxlength="6">
                    </div>`,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-ban me-1"></i> Void Sekarang',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-danger waves-effect waves-light me-2',
                    cancelButton: 'btn btn-outline-secondary'
                },
                buttonsStyling: false,
                preConfirm: () => {
                    const reason = document.getElementById('voidReasonInput').value.trim();
                    const pin = document.getElementById('voidSpvPinInput').value.trim();

                    if (!reason) {
                        Swal.showValidationMessage('Alasan wajib diisi');
                        return false;
                    }
                    if (reason.length > 500) {
                        Swal.showValidationMessage('Alasan maksimal 500 karakter');
                        return false;
                    }
                    if (!pin) {
                        Swal.showValidationMessage('PIN SPV wajib diisi');
                        return false;
                    }
                    if (pin.length < 4 || pin.length > 6) {
                        Swal.showValidationMessage('PIN SPV harus 4–6 karakter');
                        return false;
                    }

                    return {
                        reason,
                        pin
                    };
                }
            });

            if (!isConfirmed || !formValues) return;

            // ── Submit ─────────────────────────────────────────────────────
            try {
                const res = await fetch('{{ route('transaction.void') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        id,
                        spv_code: formValues.pin,
                        void_reason: formValues.reason
                    })
                });
                const data = await res.json();
                if (data.status === 'ok') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Transaksi di-void!',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    setTimeout(() => location.reload(), 1600);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: e.message
                });
            }
        }
    </script>
@endsection
