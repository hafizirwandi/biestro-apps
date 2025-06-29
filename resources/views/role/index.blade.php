 @extends('layouts.main-layout.app')
 @section('title', 'Role')
 @section('content')

     @can('role-create')
         <button onclick="create()" class="btn btn-primary mb-3 text-nowrap add-new-role waves-effect waves-light">
             <i class="ti ti-plus ti-sm me-2"></i>Tambah Role
         </button>
     @endcan

     <div class="card mb-4">
         <div class="card-body">
             <div class="table-responsive">
                 <table class="datatable table">
                     <thead>
                         <tr>
                             <th>Name</th>
                             <th>Created at</th>
                             <th>Action</th>
                         </tr>
                     </thead>
                     <tbody>
                         @foreach ($data as $r)
                             <tr>
                                 <td>{{ ucwords($r->name) }}</td>
                                 <td>{{ \Carbon\Carbon::parse($r->created_at)->isoFormat('dddd, D MMM YYYY, HH:mm:ss') }}
                                 </td>
                                 <td>
                                     <div class="d-flex align-items-center">
                                         @can('role-add-permission')
                                             <a href="{{ route('role.detail', $r->id) }}" class="text-body">
                                                 <i class="ti ti-eye ti-sm me-2"></i>
                                             </a>
                                         @endcan
                                         @can('role-edit')
                                             <a href="javascript:;" onclick="edit(`{{ $r->id }}`)" class="text-body">
                                                 <i class="ti ti-edit ti-sm me-2"></i>
                                             </a>
                                         @endcan
                                         @can('role-delete')
                                             <form method="post" action="{{ route('role.destroy') }}">
                                                 @csrf
                                                 @method('delete')
                                                 <input type="hidden" name="id" value="{{ $r->id }}">
                                                 <button type="submit"
                                                     onclick="return confirm('Are you sure you want to proceed?')"
                                                     class="text-body no-style">
                                                     <i class="ti ti-trash ti-sm me-2"></i>
                                                 </button>
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
         @can('role-create')
             function create() {

                 $("#myModal .modal-body").load("{{ route('role.create') }}");
                 $("#myModal").modal("show");

             }
         @endcan
         @can('role-edit')
             function edit(id) {

                 $("#myModal .modal-body").load("{{ route('role.edit', ['id' => ':id']) }}".replace(':id', id));
                 $("#myModal").modal("show");

             }
         @endcan
     </script>

 @endsection
