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
    </style>
@endsection
@section('content')


    <div class="row mb-5">
        <div class="col-md-12">
            <a class="btn btn-primary" href="{{ route('transaction') }}">Back</a>

            <a href="javascript:;" onclick="confirmRePrint({{ $data->id }})" class="btn btn-primary">
                Re Print Ticket
            </a>

            {{-- Connect Printer button: visible when disconnected --}}
            <button id="btnConnectBT" class="btn btn-outline-secondary" onclick="connectBTPrinter()"
                title="Connect Bluetooth Printer">
                <i class="fas fa-bluetooth-b me-1"></i>
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
            <button onclick="printBill(`{{ $data->id }}`)" class="btn btn-primary w-100 mt-3" id="btnPrintBill">
                <i class="fas fa-print me-1"></i> Print Bill
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
            const btn = document.getElementById('btnConnectBT');
            const label = document.getElementById('btnConnectBTLabel');
            if (!btn) return;
            if (window.BTPrinter?.isConnected) {
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-outline-success');
                label.textContent = 'Printer Connected ✓';
            } else {
                btn.classList.remove('btn-outline-success');
                btn.classList.add('btn-outline-secondary');
                label.textContent = 'Connect Printer';
            }
        }

        async function connectBTPrinter() {
            const btn = document.getElementById('btnConnectBT');
            const label = document.getElementById('btnConnectBTLabel');
            btn.disabled = true;
            label.textContent = 'Connecting...';

            try {
                // Try silent reconnect first (uses getDevices + retry, no popup)
                await window.BTPrinter.connectOrReconnect();
                btn.disabled = false;
                updateConnectBtn();
                Swal.fire({
                    icon: 'success',
                    title: 'Printer terhubung!',
                    timer: 1200,
                    showConfirmButton: false
                });
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
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Mencetak...';

            try {
                // 1. Ensure connected (silent if already paired, popup only if first time)
                const connected = await window.BTPrinter.connectOrReconnect();
                if (!connected) throw new Error('Printer tidak terkoneksi.');

                // 2. Fetch bill data
                const res = await fetch(`{{ url('/printer/transaction-data') }}/${id}`);
                if (!res.ok) throw new Error('Gagal mengambil data struk.');
                const data = await res.json();

                // 2. Build ESC/POS bytes & send to printer
                const bytes = window.BTPrinter.buildReceipt(data);
                const ok = await window.BTPrinter.sendData(bytes);

                if (ok) {
                    // 3. Flag on server (no server-side print, just flagging)
                    await flagBill(id);
                    Swal.fire({
                        icon: 'success',
                        title: 'Struk berhasil dicetak!',
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
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-print me-1"></i> Print Bill';
            }
        }

        async function flagBill(id) {
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
                title: "Supervisor Approval",
                text: "Masukkan kode supervisor untuk reprint",
                input: 'password',
                inputAttributes: {
                    autocapitalize: 'off',
                    placeholder: 'Kode Supervisor'
                },
                showCancelButton: true,
                confirmButtonText: "Reprint",
                cancelButtonText: "Batal",
                showLoaderOnConfirm: true,
                preConfirm: (password) => {
                    if (!password) {
                        Swal.showValidationMessage("Kode supervisor wajib diisi");
                        return false;
                    }
                    document.getElementById(`spv-code-${id}`).value = password;
                    return true;
                },
                customClass: {
                    confirmButton: 'btn btn-primary me-2',
                    cancelButton: 'btn btn-secondary'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(`reprint-form-${id}`).submit();
                }
            });
        }
    </script>
@endsection
