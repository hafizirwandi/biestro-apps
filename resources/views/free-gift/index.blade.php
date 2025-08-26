 @extends('layouts.main-layout.app')
 @section('title', 'Ticket package')
 @section('content')

     @can('free-gift-create')
         <a href="{{ route('free-gift.create') }}" class="btn btn-primary mb-3 text-nowrap add-new-role waves-effect waves-light">
             <i class="ti ti-plus ti-sm me-2"></i>Add Ticket package
         </a>
     @endcan

     <div class="card mb-4">
         <div class="card-body">
             <div class="table-responsive">
                 <table class="datatable table">
                     <thead>
                         <tr>

                             <th>Name</th>
                             <th>Description</th>
                             <th style="width: 150px">List Wahana</th>
                             <th>Min Purchase</th>
                             <th>Is Active</th>
                             <th>Is Multiple</th>
                             <th>Action</th>
                         </tr>
                     </thead>
                     <tbody>
                         @foreach ($data as $r)
                             <tr>
                                 <td>{{ $r->name }}</td>
                                 <td>{{ $r->description }}</td>
                                 <td>
                                     <div style="display: flex; flex-direction:column; gap:5px">
                                         @foreach ($r->wahanas as $d)
                                             <div class="badge bg-secondary">
                                                 <span>{{ $d->name }}</span>
                                                 <span>({{ $d->pivot->qty }})</span>
                                             </div>
                                         @endforeach
                                     </div>
                                 </td>
                                 <td>{{ format_rupiah($r->min_purchase) }}</td>
                                 <td>{!! isActive($r->is_active) !!}</td>
                                 <td>{!! isMultiple($r->is_multiple) !!}</td>


                                 <td>
                                     <div class="d-flex align-items-center">

                                         @can('free-gift-edit')
                                             <a href="{{ route('free-gift.edit', $r->id) }}" class="text-body">
                                                 <i class="ti ti-edit ti-sm me-2"></i>
                                             </a>
                                         @endcan
                                         @can('free-gift-delete')
                                             <a href="javascript:;" onclick="confirmDelete({{ $r->id }})"
                                                 class="text-body">
                                                 <i class="ti ti-trash ti-sm me-2"></i>
                                             </a>

                                             <form id="delete-form-{{ $r->id }}" method="post"
                                                 action="{{ route('free-gift.destroy') }}" style="display: none;">
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
         @can('free-gift-delete')
             function confirmDelete(id) {
                 Swal.fire({
                     title: "Are you sure?",
                     text: "Data will be deleted and cannot be restored!",
                     icon: "warning",
                     customClass: {
                         confirmButton: 'btn btn-primary waves-effect waves-light',
                         cancelButton: 'btn btn-danger waves-effect waves-light',
                     },
                     buttonsStyling: false,
                     showCancelButton: true,
                     confirmButtonText: "Yes",
                     cancelButtonText: "Cancel"
                 }).then((result) => {
                     if (result.isConfirmed) {
                         document.getElementById(`delete-form-${id}`).submit();
                     }
                 });
             }
         @endcan
     </script>

 @endsection
