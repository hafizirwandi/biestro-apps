 @extends('layouts.main-layout.app-without-menu')
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
             {{-- <hr class=" border-primary rounded border-1 "> --}}
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
             <button onclick="printBill(`{{ $data->id }}`)" class="btn btn-primary w-100 mt-3">Print Bill</button>

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
                 {{ $totalPrint === 0 ? 'disabled' : '' }}>Print
                 Ticket</button>

         </div>
     </div>
 @endsection
 @section('script')
     <script>
         function printBill(id) {
             $.ajax({
                 url: "{{ route('transaction.print-bill') }}",
                 method: 'POST',
                 data: {
                     _token: '{{ csrf_token() }}',
                     transaction_id: id
                 },
                 success: function(res) {
                     if (res.status == 'success') {
                         alert('Struk berhasil dicetak!');
                     } else {
                         alert('Struk gagal dicetak!');
                     }

                 },
                 error: function(xhr) {
                     alert('Gagal mencetak struk: ' + (xhr.responseJSON?.message ?? 'Error'));
                 }
             });
         }

         function printTicket(id, el) {
             $.ajax({
                 url: "{{ route('transaction.print-ticket') }}",
                 method: 'POST',
                 data: {
                     _token: '{{ csrf_token() }}',
                     id: id
                 },
                 success: function(res) {

                     if (res.status == 'success') {


                         alert('Ticket berhasil dicetak!');
                         window.location.reload();
                     } else {
                         alert('Ticket gagal dicetak!');
                     }

                 },
                 error: function(xhr) {
                     alert('Gagal mencetak tiket: ' + (xhr.responseJSON?.message ?? 'Error'));
                 }
             });
         }

         function printTicketAll(id) {
             $.ajax({
                 url: "{{ route('transaction.print-ticket-all') }}",
                 method: 'POST',
                 data: {
                     _token: '{{ csrf_token() }}',
                     transaction_id: id
                 },
                 success: function(res) {
                     if (res.status == 'success') {
                         alert('Ticket berhasil dicetak!');
                         window.location.reload();
                     } else {
                         alert('Ticket gagal dicetak!');
                     }

                 },
                 error: function(xhr) {
                     alert('Gagal mencetak tiket: ' + (xhr.responseJSON?.message ?? 'Error'));
                 }
             });
         }


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
                     // masukkan password ke hidden input form
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
         //  function printTicketAll() {
         //      const ids = [];

         //      // Ambil semua data-id dari .order-item
         //      document.querySelectorAll('.cart-list .order-item').forEach(el => {
         //          const id = el.getAttribute('data-id');
         //          if (id) ids.push(id);
         //      });

         //      if (!ids.length) {
         //          alert('Tidak ada tiket untuk dicetak!');
         //          return;
         //      }

         //      // Cetak satu per satu dengan jeda (delay)
         //      asyncPrintTickets(ids);
         //  }

         //  function asyncPrintTickets(ids) {
         //      let delay = 1000; // 1 detik antar cetakan (bisa disesuaikan)

         //      ids.forEach((id, index) => {
         //          setTimeout(() => {
         //              printTicket(id);
         //          }, index * delay);
         //      });
         //  }
     </script>
 @endsection
