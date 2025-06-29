 @extends('layouts.main-layout.app')
 @section('title', 'Survey')
 @section('css')
     <style>
         .drag-handle {
             cursor: grab;
             /* ikon drag */
         }

         .drag-handle:active {
             cursor: grabbing;
             /* saat sedang drag */
         }
     </style>
 @endsection
 @section('content')
     <div class="row mb-3">
         <div class="col-md-6 ">
             <div class="d-flex justify-content-between mb-3 ">
                 @can('survey-create')
                     <button onclick="create(`{{ $survey->id }}`)" class="btn btn-primary  ">
                         <i class="ti ti-plus ti-sm me-2"></i>Add Question
                     </button>
                 @endcan
                 <div class="d-flex ">
                     <a href="{{ route('survey') }}" class="btn btn-secondary ">Back</a>
                     &nbsp;
                 </div>

             </div>
             <div class="d-flex " style="justify-content: right">
                 <label class="switch">
                     <input type="checkbox" class="switch-input toggle-status" data-id="{{ $survey->id }}"
                         {{ $survey->status == 1 ? 'checked' : '' }} />
                     <span class="switch-toggle-slider">
                         <span class="switch-on"></span>
                         <span class="switch-off"></span>
                     </span>
                     <span class="switch-label">Status Active</span>
                 </label>
             </div>
         </div>
     </div>
     <div class="row">
         <div class="col-md-6">
             <div class="input-group mb-3">
                 <input type="text" id="surveyLink" class="form-control" value="{{ url('s/' . $survey->slug_link) }}"
                     readonly>
                 <button class="btn btn-outline-secondary" type="button" id="copyButton">Copy</button>
                 <a href="{{ url('s/' . $survey->slug_link) }}" class="btn btn-outline-secondary">Preview</a>
             </div>
         </div>
     </div>
     @if ($survey->question->isNotEmpty())
         <div class="row mt-3">
             <div class="col-md-6 ">
                 <ul id="sortable-cards" style="margin-left : 0px; padding-left: 0px;">

                     @foreach ($survey->question as $r)
                         <li data-id="{{ $r->id }}" style="list-style-type: none; ">
                             <div class="card mb-4">
                                 <div class="d-flex justify-content-center drag-handle ">
                                     <i class="ti ti-grip-horizontal ti-sm me-2 "></i>
                                 </div>
                                 <div class="card-body">

                                     <div class="mb-3">{{ $r->question_text }}</div>
                                     @if ($r->type == 'text')
                                         <input type="text" class="form-control">
                                     @elseif ($r->type == 'email')
                                         <input type="email" class="form-control">
                                     @elseif ($r->type == 'date')
                                         <input type="date" class="form-control">
                                     @elseif ($r->type == 'month')
                                         <input type="month" class="form-control">
                                     @elseif ($r->type == 'datetime-local')
                                         <input type="datetime-local" class="form-control">
                                     @elseif ($r->type == 'textarea')
                                         <textarea class="form-control" rows="10"></textarea>
                                     @elseif ($r->type == 'rate')
                                         <div class="d-flex gap-4 flex-wrap justify-content-center align-items-center">
                                             @for ($i = 1; $i <= $r->rate; $i++)
                                                 <div class="item d-flex flex-column align-items-center">
                                                     <span>{{ $i }}</span>
                                                     <i class="ti {{ $r->icon }} ti-md"></i>
                                                 </div>
                                             @endfor
                                         </div>
                                     @elseif ($r->type == 'checkbox')
                                         @foreach ($r->option as $o)
                                             <div class="d-flex gap-3">
                                                 <input type="checkbox" name="radio-{{ $r->id }}[]">
                                                 <span>{{ $o->option_text }}</span>
                                             </div>
                                         @endforeach
                                     @elseif ($r->type == 'radio')
                                         @foreach ($r->option as $o)
                                             <div class="d-flex gap-3">
                                                 <input type="radio" name="radio-{{ $r->id }}">
                                                 <span>{{ $o->option_text }}</span>
                                             </div>
                                         @endforeach
                                     @elseif ($r->type == 'dropdown')
                                         <select name="" class="form-control">
                                             <option value="">Choose</option>
                                             @foreach ($r->option as $o)
                                                 <option value="{{ $o->option_text }}">{{ $o->option_text }}</option>
                                             @endforeach
                                         </select>
                                     @endif


                                 </div>
                                 <div class="card-footer p-3 " style="border-top:2px solid #f2efef;">
                                     <div class="d-flex" style="justify-content:space-between">
                                         <div class="d-flex">
                                             <a href="javascript:;" onclick="edit({{ $r->id }})"
                                                 class="btn btn-default btn-sm">
                                                 Edit
                                             </a>
                                             @can('survey-delete')
                                                 <a href="javascript:;" onclick="confirmDelete({{ $r->id }})"
                                                     class="btn btn-default btn-sm">
                                                     Delete
                                                 </a>

                                                 <form id="delete-form-{{ $r->id }}" method="post"
                                                     action="{{ route('survey-question.destroy') }}" style="display: none;">
                                                     @csrf
                                                     @method('delete')
                                                     <input type="hidden" name="id" value="{{ $r->id }}">
                                                 </form>
                                             @endcan
                                         </div>
                                         <div class="d-flex">
                                             <div
                                                 class="badge bg-{{ $r->is_required == '1' ? 'primary' : 'secondary' }} mx-2">
                                                 required :
                                                 {{ $r->is_required == '1' ? 'true' : 'false' }}
                                             </div>
                                             <div class="badge bg-{{ $r->is_unique == '1' ? 'primary' : 'secondary' }}">
                                                 unique :
                                                 {{ $r->is_unique == '1' ? 'true' : 'false' }}
                                             </div>
                                         </div>
                                     </div>

                                 </div>
                             </div>
                         </li>
                     @endforeach
                 </ul>
             </div>
         </div>
     @else
         <h5>Empty Question</h5>
     @endif

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
     <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

     <script>
         const sortable = new Sortable(document.getElementById('sortable-cards'), {
             handle: '.drag-handle',
             animation: 150,
             onEnd: function( /**Event*/ evt) {
                 saveOrder(); // simpan posisi setelah drag selesai
             }
         });

         function saveOrder() {
             const order = [];

             $('#sortable-cards [data-id]').each(function(index) {
                 order.push({
                     id: $(this).data('id'),
                     position: index + 1
                 });
             });
             console.log(order);
             $.ajax({
                 url: "{{ route('survey-question.reorder') }}",
                 type: 'POST',
                 data: {
                     order: order,
                     _token: '{{ csrf_token() }}'
                 },
                 success: function(response) {
                     console.log(response);
                     //  alert('Urutan berhasil disimpan!');
                 },
                 error: function(xhr) {
                     console.error(xhr.responseText);
                     //  alert('Gagal menyimpan urutan.');
                 }
             });
         }

         @can('survey-create')
             function create(id) {

                 $("#myModal .modal-body").load("{{ route('survey-question.create', ['id' => ':id']) }}".replace(':id',
                     id));
                 $("#myModal").modal("show");

             }
         @endcan
         @can('survey-edit')
             function edit(id) {

                 $("#myModal .modal-body").load("{{ route('survey-question.edit', ['id' => ':id']) }}".replace(':id', id));
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
     <script>
         document.getElementById("copyButton").addEventListener("click", function() {
             var copyText = document.getElementById("surveyLink");
             copyText.select();
             copyText.setSelectionRange(0, 99999); // Untuk mobile

             document.execCommand("copy");

             // Opsional: ubah teks tombol untuk memberi feedback
             this.textContent = "Copied!";
             setTimeout(() => {
                 this.textContent = "Copy";
             }, 1500);
         });
         $('.toggle-status').on('change', function() {
             var surveyId = $(this).data('id');
             var newStatus = $(this).is(':checked') ? 1 : 0;
             var token = '{{ csrf_token() }}';

             $.ajax({
                 url: `/survey/${surveyId}/toggle-status`,
                 method: 'POST',
                 data: {
                     _token: token,
                     status: newStatus
                 },
                 success: function() {
                     console.log('sukses diubah');

                 },
                 error: function() {
                     console.log('gagal diubah');

                 }
             });
         });
     </script>

 @endsection
