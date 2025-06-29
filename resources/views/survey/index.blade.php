 @extends('layouts.main-layout.app')
 @section('title', 'Survey')
 @section('content')

     @can('survey-create')
         <button onclick="create()" class="btn btn-primary mb-3 text-nowrap add-new-role waves-effect waves-light">
             <i class="ti ti-plus ti-sm me-2"></i>Tambah Survey
         </button>
     @endcan

     <div class="card mb-4">
         <div class="card-body">
             <div class="table-responsive">
                 <table class="datatable table">
                     <thead>
                         <tr>
                             <th>Title</th>
                             <th>Description</th>
                             <th>Time</th>
                             <th>Answer</th>
                             <th>Action</th>
                         </tr>
                     </thead>
                     <tbody>
                         @foreach ($data as $r)
                             <tr>
                                 <td>{{ $r->title }}</td>
                                 <td>{{ $r->description }}</td>
                                 <td>Start at : {{ $r->start_at }} <br> End at : {{ $r->end_at }}</td>

                                 <td>
                                     <a href="{{ route('survey-answer.summary', $r->id) }}" class="btn btn-primary btn-xs">
                                         Answer
                                     </a>
                                 </td>
                                 <td>
                                     <div class="d-flex align-items-center">
                                         @can('survey-create')
                                             <a href="{{ route('survey.detail', $r->id) }}" class="text-body">
                                                 <i class="ti ti-eye ti-sm me-2"></i>
                                             </a>
                                         @endcan
                                         @can('survey-edit')
                                             <a href="javascript:;" onclick="edit(`{{ $r->id }}`)" class="text-body">
                                                 <i class="ti ti-edit ti-sm me-2"></i>
                                             </a>
                                         @endcan
                                         @can('survey-delete')
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
         @can('survey-create')
             function create() {

                 $("#myModal .modal-body").load("{{ route('survey.create') }}");
                 $("#myModal").modal("show");

             }
         @endcan
         @can('survey-edit')
             function edit(id) {

                 $("#myModal .modal-body").load("{{ route('survey.edit', ['id' => ':id']) }}".replace(':id', id));
                 $("#myModal").modal("show");

             }
         @endcan
         @can('survey-delete')
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
