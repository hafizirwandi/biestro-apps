@extends('layouts.main-layout.app-transaction')
@section('title', 'Ticket')
@section('css')
    <style>
        .cart-item {
            gap: 10px;
        }

        .cart-item>div {
            min-width: fit-content;
        }

        @media (max-width: 576px) {
            .cart-item {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .cart-item .text-end {
                align-self: flex-end;
            }
        }

        .title-order {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
        }

        .card-order {
            height: 55vh;
            overflow: scroll;
        }

        .card-order2 {
            height: 55vh;
            overflow: scroll;
        }

        .ticket-used {
            text-decoration-line: line-through;
        }

        a.ticket-used {
            pointer-events: none;
            opacity: 0.5;
            color: gray;
        }

        .underline {
            text-decoration: underline !important;
        }

        /* ── BT Connect Button ── */
        #btnConnectBT {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.25s ease;
            border: 1.5px solid #dee2e6;
            background: #f8f9fa;
            color: #495057;
        }

        #btnConnectBT:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }

        #btnConnectBT.connected {
            background: rgba(40, 199, 111, 0.1);
            border-color: #28c76f;
            color: #1a9e57;
            box-shadow: 0 0 10px rgba(40, 199, 111, 0.2);
        }

        #btnConnectBT.connected:hover {
            background: rgba(40, 199, 111, 0.2);
        }

        .printer-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #adb5bd;
            flex-shrink: 0;
            transition: background 0.3s;
        }

        #btnConnectBT.connected .printer-dot {
            background: #28c76f;
            box-shadow: 0 0 0 0 rgba(40, 199, 111, 0.7);
            animation: printerPulse 1.8s infinite;
        }

        @keyframes printerPulse {
            0% {
                box-shadow: 0 0 0 0 rgba(40, 199, 111, 0.7);
            }

            70% {
                box-shadow: 0 0 0 6px rgba(40, 199, 111, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(40, 199, 111, 0);
            }
        }
    </style>
@endsection
@section('content')


    <div class="row mb-5">
        <div class="col-md-12 d-flex align-items-center gap-2 flex-wrap">
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('transaction') }}"><i
                    class="ti ti-arrow-left me-1"></i>Back</a>

            <a href="javascript:;" onclick="confirmRePrint({{ $data->id }})" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-redo me-1"></i> Re Print Ticket
            </a>

            {{-- Connect Printer button --}}
            <button id="btnConnectBT" onclick="connectBTPrinter()" title="Connect / Disconnect Bluetooth Printer">
                <span class="printer-dot" id="printerDot"></span>
                <i class="fas fa-bluetooth-b"></i>
                <span id="btnConnectBTLabel">Connect Printer</span>
            </button>

            <form id="reprint-form-{{ $data->id }}" method="post" action="{{ route('transaction.reprint') }}"
                style="display: none;">
                @csrf
                <input type="hidden" name="id" value="{{ $data->id }}">
                <input type="hidden" name="spv_code" id="spv-code-{{ $data->id }}">
            </form>
        </div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="title-order ">
                <span>Order : {{ $data->paid_at }}</span>
                <strong>{{ $data->transaction_code }}</strong>
            </div>
        </div>

    </div>
    <div class="row justify-content-center">


        <div class="col-md-8">


            <div class="shadow-sm border-top border-primary rounded-top border-5 card-order ">

                <div class="cart-list mt-3 px-3">
                    @foreach ($data->details as $r)
                        <div
                            class="cart-item d-flex justify-content-between align-items-center flex-wrap py-2 border-bottom order-item ">
                            <div class="flex-grow-1">
                                <div class="fw-bold">
                                    {{ $r->ticket_id != null ? $r->ticket->name : $r->ticketPackage->name }}</div>
                                <div class="text-muted small">x{{ $r->quantity }}</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold">{{ format_rupiah($r->subtotal) }}</div>
                            </div>

                        </div>
                    @endforeach
                </div>

                @foreach ($data->freeGifts as $r)
                    <div class="mt-3 px-3 fw-bold underline">Free Voucher {{ $r->freeGiftRule?->name }}
                        x{{ $r->quantity }}
                    </div>

                    @php
                        $wahanas = $r->freeGiftRule?->wahanas;

                    @endphp
                    @foreach ($wahanas as $w)
                        <div class="cart-list px-3">
                            <div
                                class="cart-item d-flex justify-content-between align-items-center flex-wrap py-2 border-bottom order-item ">
                                <div class="flex-grow-1">
                                    <div class="fw-bold">{{ $w->name }}</div>
                                    <div class="text-muted small">{{ '@' . $w->pivot->qty }} x {{ $r->quantity }} =
                                        {{ $w->pivot->qty * $r->quantity }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold">Free</div>
                                </div>

                            </div>

                        </div>
                    @endforeach
                @endforeach

            </div>
            <div class="shadow-sm p-2 py-3 mt-3 rounded ">
                <div class="d-flex justify-content-between">
                    <strong>TOTAL</strong>
                    <span class="fw-bold">{{ format_rupiah($data->total_amount) }}</span>
                </div>
            </div>
            @php $isBillPrinted = $data->bill_count_print > 0; @endphp
            <button onclick="printBill(`{{ $data->id }}`)"
                class="btn btn-primary w-100 mt-3 {{ $isBillPrinted ? 'disabled' : '' }}" id="btnPrintBill"
                {{ $isBillPrinted ? 'disabled' : '' }}>
                @if ($isBillPrinted)
                    <i class="fas fa-check me-1"></i> Sudah Dicetak
                @else
                    <i class="fas fa-print me-1"></i> Print Bill
                @endif
            </button>

        </div>
        <div class=" col-md-4 ">
            <div class="shadow-sm border-top border-primary rounded-top border-5 card-order">
                <div class="cart-list mt-3 px-3">

                    @php
                        $totalPrint = $ticket->where('count_print', 0)->count();
                    @endphp
                    @foreach ($ticket as $r)
                        <div class="cart-item d-flex justify-content-between align-items-center flex-wrap py-2 border-bottom order-item "
                            data-id="{{ $r->id }}">
                            <div class="flex-grow-1">
                                <div class="fw-bold {{ $r->count_print != 0 ? 'ticket-used' : '' }}">
                                    {{ $r->ticket_code }}
                                </div>
                                <div class="text-muted small {{ $r->count_print != 0 ? 'ticket-used' : '' }}">
                                    {{ $r->wahana->name }}
                                </div>
                            </div>

                            <div>
                                <a href="javascript:;" onclick="printTicket(`{{ $r->id }}`, this)"
                                    class="ms-3 btn-remove-order {{ $r->count_print != 0 ? 'ticket-used' : '' }} ">
                                    <i class="ti ti-printer ti-sm"></i>
                                </a>
                            </div>

                        </div>
                    @endforeach
                </div>
            </div>
            <div class="shadow-sm p-2 py-3 mt-3 rounded ">
                <div class="d-flex justify-content-between">
                    <strong>TOTAL TICKET</strong>
                    <span class="fw-bold">{{ count($ticket) }}</span>
                </div>
            </div>

            <button onclick="printTicketAll(`{{ $data->id }}`)" class="btn btn-primary w-100 mt-3"
                id="btnPrintTicketAll" {{ $totalPrint === 0 ? 'disabled' : '' }}>
                <i class="fas fa-print me-1"></i> Print Ticket
            </button>

        </div>
    </div>

@endsection
@section('script')
    <script src="{{ asset('js/bluetooth-printer.js') }}"></script>
    <script>
        // ─────────────────────────────────────────────────────
        // Connect Printer button helpers
        // ─────────────────────────────────────────────────────
        function updateConnectBtn() {
            const btns = document.querySelectorAll('#btnConnectBT');
            btns.forEach(btn => {
                const label = btn.querySelector('#btnConnectBTLabel');
                const dot = btn.querySelector('#printerDot, .printer-dot');

                if (window.BTPrinter?.isConnected) {
                    btn.classList.add('connected');
                    if (dot) dot.classList.add('connected');
                    if (label) label.textContent = 'Printer Terhubung ✓';
                } else {
                    btn.classList.remove('connected');
                    if (dot) dot.classList.remove('connected');
                    if (label) label.textContent = 'Connect Printer';
                }
            });
        }

        async function connectBTPrinter() {
            const btn = document.getElementById('btnConnectBT');
            const label = document.getElementById('btnConnectBTLabel');
            btn.disabled = true;
            label.textContent = 'Connecting...';

            try {
                // Try silent reconnect first (uses getDevices + retry, no popup)
                const connected = await window.BTPrinter.connectOrReconnect();
                btn.disabled = false;
                updateConnectBtn();

                if (connected) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Printer terhubung!',
                        timer: 1200,
                        showConfirmButton: false
                    });
                }
            } catch (e) {
                // Silent reconnect failed after all retries.
                // Ask user if they want to open scan popup to re-pair.
                btn.disabled = false;
                label.textContent = 'Connect Printer';
                const result = await Swal.fire({
                    icon: 'warning',
                    title: 'Gagal reconnect otomatis',
                    text: 'Printer tidak merespons. Buka scan Bluetooth untuk pair ulang?',
                    showCancelButton: true,
                    confirmButtonText: 'Buka Scan',
                    cancelButtonText: 'Batal'
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

        // Keep button in sync when connection state changes
        document.addEventListener('btPrinterConnected', updateConnectBtn);
        document.addEventListener('btPrinterDisconnected', updateConnectBtn);
        updateConnectBtn();

        // ────────────────────────────────────────────────────
        // Print Bill (receipt)
        // ────────────────────────────────────────────────────
        async function printBill(id) {
            const btn = document.getElementById('btnPrintBill');
            let billPrinted = false;
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Mencetak...';
            }

            try {
                // 1. Pastikan printer terkoneksi
                let connected = false;
                try {
                    connected = await window.BTPrinter.connectOrReconnect();
                } catch (connErr) {
                    // Silent reconnect gagal, tawarkan scan manual
                    const result = await Swal.fire({
                        icon: 'warning',
                        title: 'Printer tidak terkoneksi',
                        text: 'Buka scan Bluetooth untuk pair printer?',
                        showCancelButton: true,
                        confirmButtonText: 'Buka Scan',
                        cancelButtonText: 'Batal',
                        customClass: {
                            confirmButton: 'btn btn-primary',
                            cancelButton: 'btn btn-outline-secondary'
                        },
                        buttonsStyling: false
                    });
                    if (result.isConfirmed) {
                        connected = await window.BTPrinter.connect();
                        if (typeof updateConnectBtn === 'function') updateConnectBtn();
                    }
                }

                if (!connected) throw new Error('Printer tidak terkoneksi.');

                // 2. Ambil data struk dari server
                const res = await fetch(`{{ url('/printer/transaction-data') }}/${id}`);
                if (!res.ok) throw new Error('Gagal mengambil data struk.');
                const data = await res.json();

                // 3. Build ESC/POS & kirim ke printer
                const bytes = window.BTPrinter.buildReceipt(data);
                const ok = await window.BTPrinter.sendData(bytes);

                if (!ok) throw new Error('Data gagal dikirim ke printer. Coba reconnect printer.');

                // 4. Flag di server
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
                    title: 'Struk berhasil dicetak!',
                    timer: 1500,
                    showConfirmButton: false
                });

            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Cetak',
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

        // ────────────────────────────────────────────────────
        // Print Single Ticket
        // ────────────────────────────────────────────────────
        async function printTicket(id, el) {
            const icon = el?.querySelector('i');
            const origClass = icon?.className;
            if (icon) icon.className = 'fas fa-spinner fa-spin ti-sm';

            try {
                // 1. Ensure connected (silent reconnect or scan popup)
                const connected = await window.BTPrinter.connectOrReconnect();
                if (!connected) throw new Error('Printer tidak terkoneksi.');

                // 2. Fetch ticket data (JSON)
                const res = await fetch(`{{ url('/printer/ticket-data') }}/${id}`);
                if (!res.ok) throw new Error('Gagal mengambil data tiket.');
                const data = await res.json();

                // 3. Build & send ESC/POS
                const bytes = window.BTPrinter.buildTicket(data);
                const ok = await window.BTPrinter.sendData(bytes);

                if (ok) {
                    // 4. Flag on server
                    const flagRes = await fetch('{{ route('transaction.print-ticket') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            id: id,
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

                        // Disable Print All Ticket btn if no more active tickets
                        const activeTickets = document.querySelectorAll('.btn-remove-order:not(.ticket-used)');
                        if (activeTickets.length === 0) {
                            const btnAll = document.getElementById('btnPrintTicketAll');
                            if (btnAll) btnAll.disabled = true;
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

        // ────────────────────────────────────────────────────
        // Print All Tickets
        // ────────────────────────────────────────────────────
        async function printTicketAll(transactionId) {
            const btn = document.getElementById('btnPrintTicketAll');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Mencetak...';

            let permanentlyDisabled = false;

            try {
                // 1. Ensure connected (silent reconnect or scan popup)
                const connected = await window.BTPrinter.connectOrReconnect();
                if (!connected) throw new Error('Printer tidak terkoneksi.');

                // 2. Fetch all tickets data
                const res = await fetch(`{{ url('/printer/tickets-data') }}/${transactionId}`);
                if (!res.ok) throw new Error('Gagal mengambil data tiket.');
                const {
                    tickets
                } = await res.json();

                // 3. Print one by one
                let printed = 0;
                for (const t of tickets) {
                    const bytes = window.BTPrinter.buildTicket(t);
                    const ok = await window.BTPrinter.sendData(bytes);
                    if (ok) {
                        printed++;
                        await sleep(400);
                    }
                }

                // 3. Flag all on server
                if (printed > 0) {
                    const flagRes = await fetch('{{ route('transaction.print-ticket-all') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            transaction_id: transactionId,
                            _bt: 1
                        })
                    });
                    const flagData = await flagRes.json();
                    if (flagData.status === 'success') {
                        // Mark all as printed in UI
                        document.querySelectorAll('.order-item').forEach(row => {
                            row.querySelectorAll('.fw-bold, .text-muted').forEach(e => e.classList.add(
                                'ticket-used'));
                            const link = row.querySelector('a');
                            if (link) link.classList.add('ticket-used');
                        });
                        permanentlyDisabled = true; // keep button disabled
                    }
                    Swal.fire({
                        icon: 'success',
                        title: `${printed} tiket dicetak!`,
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
                if (permanentlyDisabled) {
                    // Stay disabled, update label
                    btn.innerHTML = '<i class="fas fa-check me-1"></i> Sudah Dicetak';
                    btn.disabled = true;
                } else {
                    // Re-enable on failure so user can retry
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-print me-1"></i> Print Ticket';
                }
            }
        }

        function sleep(ms) {
            return new Promise(r => setTimeout(r, ms));
        }

        // ────────────────────────────────────────────────────
        // Re-Print (supervisor approval)
        // ────────────────────────────────────────────────────
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
    </script>
@endsection
