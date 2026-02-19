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
        height: 45vh;
        overflow: scroll;
    }

    .card-order2 {
        height: 45vh;
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

{{-- Action buttons --}}
<div class="row mb-3">
    <div class="col-12 d-flex gap-2 flex-wrap">
        <a href="javascript:;" onclick="confirmRePrint({{ $data->id }})" class="btn btn-primary">
            Re Print Ticket
        </a>

        <button id="btnConnectBT" class="btn btn-outline-secondary" onclick="connectBTPrinter()"
            title="Connect Bluetooth Printer">
            <i class="fas fa-bluetooth-b me-1"></i>
            <span id="btnConnectBTLabel">Connect Printer</span>
        </button>

        <form id="reprint-form-{{ $data->id }}" method="post" action="{{ route('transaction.reprint') }}"
            style="display:none;">
            @csrf
            <input type="hidden" name="id" value="{{ $data->id }}">
            <input type="hidden" name="spv_code" id="spv-code-{{ $data->id }}">
        </form>
    </div>
</div>

{{-- Order header --}}
<div class="row justify-content-center mb-2">
    <div class="col-12">
        <div class="title-order">
            <span>Order : {{ $data->paid_at }}</span>
            <strong>{{ $data->transaction_code }}</strong>
        </div>
    </div>
</div>

{{-- Order items & tickets --}}
<div class="row justify-content-center">

    {{-- Left: Bill items --}}
    <div class="col-md-8">
        <div class="shadow-sm border-top border-primary rounded-top border-5 card-order">
            <div class="cart-list mt-3 px-3">
                @foreach ($data->details as $r)
                    <div
                        class="cart-item d-flex justify-content-between align-items-center flex-wrap py-2 border-bottom order-item">
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
                    x{{ $r->quantity }}</div>
                @php $wahanas = $r->freeGiftRule?->wahanas; @endphp
                @foreach ($wahanas as $w)
                    <div class="cart-list px-3">
                        <div
                            class="cart-item d-flex justify-content-between align-items-center flex-wrap py-2 border-bottom order-item">
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

        <div class="shadow-sm p-2 py-3 mt-3 rounded">
            <div class="d-flex justify-content-between">
                <strong>TOTAL</strong>
                <span class="fw-bold">{{ format_rupiah($data->total_amount) }}</span>
            </div>
        </div>

        <button onclick="printBill(`{{ $data->id }}`)" class="btn btn-primary w-100 mt-3" id="btnPrintBill">
            <i class="fas fa-print me-1"></i> Print Bill
        </button>
    </div>

    {{-- Right: Tickets --}}
    <div class="col-md-4">
        <div class="shadow-sm border-top border-primary rounded-top border-5 card-order">
            <div class="cart-list mt-3 px-3">
                @php $totalPrint = $ticket->where('count_print', 0)->count(); @endphp
                @foreach ($ticket as $r)
                    <div class="cart-item d-flex justify-content-between align-items-center flex-wrap py-2 border-bottom order-item"
                        data-id="{{ $r->id }}">
                        <div class="flex-grow-1">
                            <div class="fw-bold {{ $r->count_print != 0 ? 'ticket-used' : '' }}">{{ $r->ticket_code }}
                            </div>
                            <div class="text-muted small {{ $r->count_print != 0 ? 'ticket-used' : '' }}">
                                {{ $r->wahana->name }}</div>
                        </div>
                        <div>
                            <a href="javascript:;" onclick="printTicket(`{{ $r->id }}`, this)"
                                class="ms-3 btn-remove-order {{ $r->count_print != 0 ? 'ticket-used' : '' }}">
                                <i class="ti ti-printer ti-sm"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="shadow-sm p-2 py-3 mt-3 rounded">
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

{{-- Supervisor reprint form (hidden) --}}
<script>
    // Re-init connect button state (injected dynamically, listeners already set on index page)
    if (typeof updateConnectBtn === 'function') updateConnectBtn();
</script>
