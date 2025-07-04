 <!-- Core JS -->
 <!-- build:js assets/vendor/js/core.js -->

 <script src="{{ asset('vuexy/assets/vendor/libs/jquery/jquery.js') }}"></script>
 <script src="https://cdn.jsdelivr.net/npm/autonumeric@4.5.4/dist/autoNumeric.min.js"></script>
 <script src="{{ asset('vuexy/assets/vendor/libs/popper/popper.js') }}"></script>
 <script src="{{ asset('vuexy/assets/vendor/js/bootstrap.js') }}"></script>
 <script src="{{ asset('vuexy/assets/vendor/libs/node-waves/node-waves.js') }}"></script>
 <script src="{{ asset('vuexy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
 <script src="{{ asset('vuexy/assets/vendor/libs/hammer/hammer.js') }}"></script>

 <script src="{{ asset('vuexy/assets/vendor/js/menu.js') }}"></script>

 <!-- endbuild -->

 <!-- Vendors JS -->
 <script src="{{ asset('vuexy/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
 <script src="{{ asset('vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
 <script src="{{ asset('vuexy/assets/vendor/libs/select2/select2.js') }}"></script>

 <!-- Main JS -->
 <script src="{{ asset('vuexy/assets/js/main.js') }}"></script>
 <script src="{{ asset('vuexy/assets/js/forms-selects.js') }}"></script>
 <script src="{{ asset('vuexy') }}/assets/vendor/libs/moment/moment.js"></script>
 <script src="{{ asset('vuexy') }}/assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.js"></script>
 <script src="{{ asset('vuexy') }}/assets/vendor/libs/bootstrap-daterangepicker/bootstrap-daterangepicker.js"></script>

 <script>
     var bsRangePickerBasic = $('#bs-rangepicker-basic');
     if (bsRangePickerBasic.length) {
         bsRangePickerBasic.daterangepicker({
             opens: isRtl ? 'left' : 'right'
         });
     }
 </script>

 <script>
     $(document).ready(function() {
         $('.auto-numeric').each(function() {
             if (!AutoNumeric.getAutoNumericElement(this)) {
                 new AutoNumeric(this, {
                     digitGroupSeparator: ',',
                     decimalPlaces: 0,
                     currencySymbol: 'Rp. ',
                     unformatOnSubmit: true
                 });
             }
         });
         $('#myModal').on('shown.bs.modal', function() {
             $('.auto-numeric').each(function() {
                 if (!AutoNumeric.getAutoNumericElement(this)) {
                     new AutoNumeric(this, {
                         digitGroupSeparator: ',',
                         decimalPlaces: 0,
                         currencySymbol: 'Rp. ',
                         unformatOnSubmit: true
                     });
                 }
             });
         });
         $(".select2").select2();
         $(".datatable").DataTable({

             dom: '<<"dt-action-buttons text-end pt-3 pt-md-0"B>><"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',

             buttons: [{
                     extend: 'collection',
                     className: 'btn btn-label-primary dropdown-toggle me-2 waves-effect waves-light',
                     text: '<i class="ti ti-file-export me-sm-1"></i> <span class="d-none d-sm-inline-block">Export</span>',
                     buttons: [{
                             extend: 'print',
                             text: '<i class="ti ti-printer me-1" ></i>Print',
                             className: 'dropdown-item',


                         },
                         {
                             extend: 'csv',
                             text: '<i class="ti ti-file-text me-1" ></i>Csv',
                             className: 'dropdown-item',

                         },
                         {
                             extend: 'excel',
                             text: '<i class="ti ti-file-spreadsheet me-1"></i>Excel',
                             className: 'dropdown-item',

                         },
                         {
                             extend: 'pdf',
                             text: '<i class="ti ti-file-description me-1"></i>Pdf',
                             className: 'dropdown-item',

                         },
                         {
                             extend: 'copy',
                             text: '<i class="ti ti-copy me-1" ></i>Copy',
                             className: 'dropdown-item',

                         }
                     ]
                 }

             ]
         });
     });
 </script>

 @if (session('success'))
     <script>
         Swal.fire({
             icon: 'success',
             title: 'Success!',
             text: '{{ session('success') }}',
             customClass: {
                 confirmButton: 'btn btn-primary waves-effect waves-light'
             },
             buttonsStyling: false
         });
     </script>
 @endif
 @if (session('error'))
     <script>
         Swal.fire({
             icon: 'error',
             title: 'Fail!',
             text: '{{ session('error') }}',
             customClass: {
                 confirmButton: 'btn btn-primary waves-effect waves-light'
             },
             buttonsStyling: false
         });
     </script>
 @endif
 @hasSection('script')
     @yield('script')
 @endif
 <!-- Page JS -->
