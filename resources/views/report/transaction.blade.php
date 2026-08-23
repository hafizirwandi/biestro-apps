 @extends('layouts.main-layout.app')
 @section('title', 'Report Transaction')

 @section('content')


     <div class="alert alert-primary" role="alert">
         <form action="">
             <div class="row g-3">
                 <div class="col-md-2">
                     <label>Periode</label>
                     <input type="text" id="bs-rangepicker-basic" name="period" value="{{ request()->period }}"
                         class="form-control">
                 </div>
                 <div class="col-md-2">
                     <label>Method</label>
                     <select name="method" class="form-control">
                         <option value="all" {{ request('method') == 'all' ? 'selected' : '' }}>All</option>
                         <option value="cash" {{ request('method') == 'cash' ? 'selected' : '' }}>Cash</option>
                         <option value="noncash" {{ request('method') == 'noncash' ? 'selected' : '' }}>Noncash</option>
                     </select>

                 </div>

                 <div class="col-md-1">
                     <button type="submit" class="mt-4 btn btn-warning  text-nowrap  btn-sm waves-effect waves-light">
                         <i class="ti ti-search ti-sm me-2"></i>Cari
                     </button>
                 </div>
             </div>
         </form>
     </div>
     <div class="card mb-4">
         <div class="card-body">

             <div class="table-responsive">

                 <table class="datatable table">
                     <thead>
                         <tr>
                             <th>Transaction code</th>
                             <th>Status</th>
                             <th>Paid at</th>
                             <th>Payment Type</th>
                             <th>Non Cash Method</th>
                             <th>Alasan Batal</th>
                             <th>Total </th>
                             <th>Info </th>


                         </tr>
                     </thead>

                     <tbody>
                         @foreach ($data as $r)
                             <tr>
                                 <td>{{ $r->transaction_code }}</td>
                                 <td>
                                     @if ($r->payment_status === 'voided')
                                         <span class="badge bg-danger">VOID</span>
                                     @else
                                         <span class="badge bg-success">PAID</span>
                                     @endif
                                 </td>
                                 <td>{{ $r->paid_at }}</td>
                                 <td>{{ $r->payment_type }}</td>
                                 <td>{{ $r->noncash_method ?? '-' }}</td>
                                 <td class="text-danger small">{{ $r->void_reason ?? '-' }}</td>
                                 <td>{{ format_rupiah($r->total_amount) }}</td>
                                 <td>
                                     <div class="d-flex">
                                         <button class="btn btn-icon" onclick="info(`{{ $r->id }}`)"><i
                                                 class="ti ti-info-circle"></i></button>

                                         @can('transaction-delete')
                                             <a href="javascript:;" onclick="confirmDelete({{ $r->id }})"
                                                 class="btn btn-icon text-danger">
                                                 <i class="ti ti-trash ti-sm"></i>
                                             </a>

                                             <form id="delete-form-{{ $r->id }}" method="post"
                                                 action="{{ route('report.transaction.destroy') }}"
                                                 style="display: none;">
                                                 @csrf
                                                 @method('delete')
                                                 <input type="hidden" name="id" value="{{ $r->id }}">
                                             </form>
                                         @endcan
                                     </div>
                                 </td>
                             </tr>
                         @endforeach
                     </tbody>

                 </table>
             </div>
         </div>
     </div>
     <div class="modal fade" id="myModal" tabindex="-1" aria-hidden="true">
         <div class="modal-dialog modal-lg modal-simple modal-dialog-centered">
             <div class="modal-content p-3 p-md-5">
                 <div class="modal-body">

                 </div>
             </div>
         </div>
     </div>

 @endsection
 @section('script')
     <script>
         function info(id) {

             $("#myModal .modal-body").load("{{ route('report.detail-transaction-modal', ['id' => ':id']) }}".replace(
                 ':id', id));
             $("#myModal").modal("show");

         }

         @can('transaction-delete')
             function confirmDelete(id) {
                 Swal.fire({
                     title: "Hapus transaksi ini?",
                     text: "Data transaksi, detail item, free gift, dan tiket terkait akan dihapus permanen dan tidak bisa dikembalikan!",
                     icon: "warning",
                     customClass: {
                         confirmButton: 'btn btn-danger waves-effect waves-light',
                         cancelButton: 'btn btn-secondary waves-effect waves-light',
                     },
                     buttonsStyling: false,
                     showCancelButton: true,
                     confirmButtonText: "Ya, Hapus",
                     cancelButtonText: "Batal"
                 }).then((result) => {
                     if (result.isConfirmed) {
                         document.getElementById(`delete-form-${id}`).submit();
                     }
                 });
             }
         @endcan
     </script>
 @endsection
