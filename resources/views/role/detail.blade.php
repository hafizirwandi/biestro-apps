 @extends('layouts.main-layout.app')
 @section('title', 'Role')
 @section('content')


     <div class="card mb-4">
         <div class="card-header header-elements">
             <h5 class=" me-2">Permissions</h5>

             <div class="card-header-elements ms-auto">
                 <input type="text" class="form-control form-control-sm" id="search-permission" placeholder="Search">
             </div>
         </div>
         <div class="card-body">
             <form method="post">
                 <div class="form-group">
                     <div class="checkbox-list my-2">
                         <div class="row" id="list-permission">


                             @foreach ($data as $r)
                                 <div class="col-3 mb-3">
                                     <div class="form-check form-check-primary mt-3">
                                         <input class="form-check-input permission to-remove" type="checkbox" value=""
                                             data-id="{{ $r->name }}" {{ $r->check_id == $r->id ? 'checked' : '' }}>
                                         <label class="form-check-label">{{ $r->name }}</label>
                                     </div>
                                 </div>
                             @endforeach
                         </div>
                     </div>

                 </div>
             </form>
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
         function create() {

             $("#myModal .modal-body").load("{{ route('role.create') }}");
             $("#myModal").modal("show");

         }

         function edit(id) {

             $("#myModal .modal-body").load("{{ route('role.edit', ['id' => ':id']) }}".replace(':id', id));
             $("#myModal").modal("show");

         }
         $(document).ready(function() {
             $("#search-permission").on('keyup', function() {
                 var value = $(this).val().toLowerCase();
                 console.log(value);
                 if (value.length >= 3) {
                     $("#list-permission *").filter(function() {
                         $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                         $(".to-remove").removeAttr("style");
                     });
                 } else {
                     $("#list-permission *").show();
                 }
             })

         })

         $('.permission').on('change', function(e) {
             e.preventDefault();

             var id = $(this).data('id');
             var is_add = false;
             var role_id = {{ $role['id'] }}; // Mengambil nilai dari variabel PHP di view menggunakan Blade
             if ($(this).is(':checked')) {
                 is_add = true;
             }

             $.ajax({
                 type: "POST",
                 dataType: "JSON",
                 url: "{{ route('role.savePermission') }}", // Menggunakan route dengan nama "pengguna.savePermission"
                 data: {
                     '_token': '{{ csrf_token() }}', // Menggunakan fungsi Blade csrf_token() untuk mendapatkan CSRF token
                     'permission_name': id,
                     'role_id': role_id,
                     'is_add': is_add
                 },
                 success: function(data) {

                     if (data.status == 'success') {
                         Swal.fire({
                             icon: 'success',
                             title: 'Berhasil!',
                             text: data.msg,
                             customClass: {
                                 confirmButton: 'btn btn-primary waves-effect waves-light'
                             },
                             buttonsStyling: false
                         });
                     } else if (data.status == 'error') {
                         Swal.fire({
                             icon: "error",
                             title: 'Gagal!',
                             text: data.msg,
                             customClass: {
                                 confirmButton: 'btn btn-primary waves-effect waves-light'
                             },
                             buttonsStyling: false
                         });
                     }


                 }
             });
         });
     </script>



 @endsection
