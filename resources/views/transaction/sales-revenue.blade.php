 @extends('layouts.main-layout.app-without-menu')
 @section('title', 'Ticket')
 @section('content')
     <div class="row mb-5">
         <div class="col-md-12">
             <a class="btn btn-primary" href="{{ route('transaction') }}">Back</a>
             <a class="btn btn-danger" href="{{ route('transaction.close-shift') }}">Cashier Closing</a>
         </div>
     </div>

     <div class="mb-3">
         <label for="filterPayment" class="form-label">Filter Cara Bayar</label>
         <select id="filterPayment" class="form-select" style="width: 200px;">
             <option value="">Semua</option>
             <option value="cash">Cash</option>
             <option value="qris">QRIS</option>
             <option value="transfer">Transfer Bank</option>
             <option value="debit">Kartu Debit</option>
             <option value="lainnya">Lainnya</option>
         </select>
     </div>
     <div class="row">
         <div class="col-md-12">
             <div class="card">
                 <div class="card-body">

                     <table class="table table-responsive table-striped datatable2">
                         <thead>
                             <tr>

                                 <th>Transaction code</th>
                                 <th>Payment Status</th>
                                 <th>Total Amount</th>
                                 <th>Payment Type</th>
                                 <th>Amount Given</th>
                                 <th>Non Cash Method</th>
                                 <th>Bank</th>
                                 <th>Paid At</th>
                                 <th>Action</th>
                             </tr>
                         </thead>
                         <tbody>
                             @php
                                 $total = $transaction->sum('total_amount');
                                 $totalCash = $transaction->where('payment_type', 'cash')->sum('total_amount');
                                 $totalNonCash = $transaction->where('payment_type', 'noncash')->sum('total_amount');
                             @endphp
                             @foreach ($transaction as $r)
                                 <tr>

                                     <td>{{ $r->transaction_code }}</td>
                                     <td>{{ $r->payment_status }}</td>
                                     <td>{{ format_rupiah($r->total_amount) }}</td>
                                     <td>{{ $r->payment_type }}</td>
                                     <td>{{ format_rupiah($r->amount_given) }}</td>
                                     <td>{{ $r->noncash_method }}</td>
                                     <td>{{ $r->bank }}</td>
                                     <td>{{ $r->paid_at }}</td>
                                     <td><a class="btn btn-primary btn-xs" href="{{ route('transaction.view', $r->id) }}">
                                             <i class="ti ti-printer ti-xs"></i></a></td>
                                 </tr>
                             @endforeach
                         </tbody>

                     </table>
                 </div>
             </div>

         </div>
     </div>
     <div class="mt-3 p-3 border rounded">

         <p>Total Cash: <strong>{{ format_rupiah($totalCash) }}</strong></p>
         <p>Total Non-Cash: <strong>{{ format_rupiah($totalNonCash) }}</strong></p>
         <p class="mb-0">Grand Total: <strong>{{ format_rupiah($total) }}</strong></p>
         <hr>

         <p>Opening Balance : <strong>{{ format_rupiah($shift->opening_balance) }}</strong></p>
         <p>Opening Balance + Total Cash =<strong> {{ format_rupiah($shift->opening_balance + $totalCash) }} </strong></p>
     </div>
 @endsection
 @section('script')
     <script>
         document.addEventListener("DOMContentLoaded", function() {
             const table = $('.datatable2').DataTable();

             // filter saat select berubah
             document.getElementById('filterPayment').addEventListener('change', function() {
                 let val = this.value;
                 // reset dulu semua filter
                 table.column(3).search('');
                 table.column(5).search('');

                 if (val) {
                     if (val.toLowerCase() === "cash") {
                         // filter di kolom Payment Type (3)
                         table.column(3).search('^' + val + '$', true, false).draw();
                     } else {
                         // filter di kolom Non Cash Method (5)
                         table.column(5).search('^' + val + '$', true, false).draw();
                     }
                 } else {
                     table.draw(); // tampilkan semua
                 }
             });
         });
     </script>
 @endsection
