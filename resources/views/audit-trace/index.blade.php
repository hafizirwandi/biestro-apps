 @extends('layouts.main-layout.app')
 @section('title', 'Log System Personal')
 @section('content')
     <div class="alert alert-primary" role="alert">
         <form action="">
             <div class="row g-3">
                 <div class="col-md-2">
                     <label>Period</label>
                     <input type="month" name="period" required value="{{ $period }}" class="form-control">
                 </div>
                 <div class="col-md-2">
                     <label>User</label>
                     <input type="text" name="user" required value="{{ $user }}" class="form-control">
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
                 <table class="table" id="log-table">
                     <thead>
                         <tr>
                             <th>Action</th>
                             <th>Modul</th>
                             <th>Name</th>
                             <th>Time</th>
                             <th>Detail</th>
                         </tr>
                     </thead>
                     <tbody>
                         @foreach ($data as $r)
                             <tr>
                                 <td>{{ $r->description }}</td>
                                 <td>{{ $r->log_name }}</td>
                                 <td>{{ $r->causer->name }} (
                                     @if ($r->causer_type == 'App\Models\User')
                                         Admin
                                     @elseif ($r->causer_type == 'App\Models\Employee')
                                         Employee
                                     @else
                                         Other
                                     @endif
                                     )
                                 </td>
                                 <td>{{ $r->created_at }}</td>
                                 <td>
                                     <button class="btn btn-outline-primary btn-sm p-2"
                                         onclick="detail({{ $r->id }})"><i class="ti ti-eye"></i></button>
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
         $('#log-table').DataTable({
             "order": [
                 [3, "desc"]
             ]
         });

         function detail(id) {
             $("#myModal .modal-body").load("{{ route('audit-trace.detail', ['id' => ':id']) }}".replace(':id',
                 id));
             $("#myModal").modal("show");
         }
     </script>
 @endsection
