 @extends('layouts.main-layout.app')
 @section('title', 'Setting Content')

 @section('css')

 @endsection
 @section('content')


     <div class="d-flex" style="gap: 20px;">
         @foreach ($mods as $mod)
             <a href="{{ route('setting', ['mod' => $mod['mod']]) }}"
                 class="btn btn-{{ $activeMod === $mod['mod'] ? 'primary' : 'default' }}">
                 {{ $mod['label'] }}
             </a>
         @endforeach
     </div>

     <div class="row">
         <div class="col-md-6">
             <div class="card mb-4 mt-4">
                 <div class="card-body">
                     <form class="row g-3" method="post" action="{{ route('setting.save') }}" enctype="multipart/form-data">
                         @csrf
                         @foreach ($input as $r)
                             <div class="col-12 col-md-12">
                                 <label class="form-label">{{ $r['label'] }}</label>
                                 @if ($r['type'] == 'text')
                                     <input type="{{ $r['type'] }}" name="{{ $r['name'] }}" class="form-control"
                                         placeholder="Enter Text"
                                         value="{{ old($r['name'], $settings[$r['name']] ?? '') }}" />
                                 @elseif($r['type'] == 'textarea')
                                     <textarea name="{{ $r['name'] }}" class="form-control" placeholder="Enter Text">{{ old($r['name'], $settings[$r['name']] ?? '') }}</textarea>
                                 @elseif($r['type'] == 'select')
                                     <select name="{{ $r['name'] }}" class="form-control" placeholder="Enter Text">
                                         @foreach ($r['options'] as $key => $value)
                                             <option value="{{ $key }}"
                                                 {{ old($r['name'], $settings[$r['name']] ?? '') == $key ? 'selected' : '' }}>
                                                 {{ $value }}</option>
                                         @endforeach
                                     </select>
                                 @endif
                                 @if (!empty($r['open_fm']))
                                     <button type="button" onclick="selectImage('{{ $r['name'] }}')"
                                         class="btn btn-sm btn-secondary mt-1">
                                         Pilih Gambar
                                     </button>

                                     {{-- Preview gambar --}}
                                     <img id="preview-{{ $r['name'] }}"
                                         src="{{ old($r['name'], $settings[$r['name']] ?? '') }}" alt="Preview"
                                         class="img-thumbnail mt-2"
                                         style="max-height: 100px; {{ empty($settings[$r['name']]) ? 'display:none;' : '' }}" />
                                 @endif
                             </div>
                         @endforeach

                         <div class="col-12 ">
                             <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>

                         </div>
                     </form>

                 </div>
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
         function selectImage(targetInputName) {
             window.open('/filemanager?type=Images', 'FileManager', 'width=900,height=600');
             window.SetUrl = function(items) {
                 const filePath = items[0].url;
                 const input = document.querySelector(`[name="${targetInputName}"]`);
                 if (input) {
                     input.value = filePath;
                 } else {
                     console.error(`Input with name="${targetInputName}" not found.`);
                 }

                 const previewImg = document.getElementById(`preview-${targetInputName}`);
                 if (previewImg) {
                     previewImg.src = filePath;
                     previewImg.style.display = 'block';
                 }
             };
         }
     </script>
 @endsection
