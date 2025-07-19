<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
<div class="text-center mb-4">
    <h3 class="mb-2 modal-title">Detail Transaction</h3>
</div>

<table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    @foreach ($data as $r)
        <tr>
            <td>{{ isset($r->ticket_id) ? $r->ticket->name : $r->ticketPackage->name }}</td>
            <td>{{ $r->quantity }}</td>
            <td>{{ format_rupiah($r->price_each) }}</td>
            <td>{{ format_rupiah($r->subtotal) }}</td>
        </tr>
    @endforeach

</table>
