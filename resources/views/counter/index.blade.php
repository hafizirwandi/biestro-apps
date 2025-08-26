 @extends('layouts.main-layout.app')
 @section('title', 'Counter')
 @section('content')

     @can('counter-create')
         <button onclick="create()" class="btn btn-primary mb-3 text-nowrap add-new-role waves-effect waves-light">
             <i class="ti ti-plus ti-sm me-2"></i>Add Counter
         </button>
     @endcan

     <div class="card mb-4">
         <div class="card-body">
             <div class="table-responsive">
                 <table class="datatable table">
                     <thead>
                         <tr>
                             <th>Location</th>
                             <th>Name</th>
                             <th>Description</th>
                             <th>Is Active</th>
                             <th>Action</th>
                         </tr>
                     </thead>
                     <tbody>
                         @foreach ($data as $r)
                             <tr>
                                 <td>{{ $r->location }}</td>
                                 <td>{{ $r->name }}</td>
                                 <td>{{ $r->description }}</td>
                                 <td>{!! isActive($r->is_active) !!}</td>

                                 <td>
                                     <div class="d-flex align-items-center">

                                         @can('counter-edit')
                                             <a href="javascript:;" onclick="edit(`{{ $r->id }}`)" class="text-body">
                                                 <i class="ti ti-edit ti-sm me-2"></i>
                                             </a>
                                         @endcan
                                         @can('counter-delete')
                                             <a href="javascript:;" onclick="confirmDelete({{ $r->id }})"
                                                 class="text-body">
                                                 <i class="ti ti-trash ti-sm me-2"></i>
                                             </a>

                                             <form id="delete-form-{{ $r->id }}" method="post"
                                                 action="{{ route('counter.destroy') }}" style="display: none;">
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
         <div class="modal-dialog modal-md modal-simple modal-dialog-centered">
             <div class="modal-content p-3 p-md-5">
                 <div class="modal-body">

                 </div>
             </div>
         </div>
     </div>
 @endsection
 @section('script')
     <script>
         @can('counter-create')
             function create() {

                 $("#myModal .modal-body").load("{{ route('counter.create') }}");
                 $("#myModal").modal("show");

             }
         @endcan
         @can('counter-edit')
             function edit(id) {

                 $("#myModal .modal-body").load("{{ route('counter.edit', ['id' => ':id']) }}".replace(':id', id));
                 $("#myModal").modal("show");

             }
         @endcan
         @can('counter-delete')
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
