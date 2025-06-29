<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ setting('apps_name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vuexy/assets/vendor/fonts/tabler-icons.css') }}" />

    <link rel="shortcut icon" href="{{ setting('favicon') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('vuexy/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
    <style>
        .icon-selectable {
            cursor: pointer;
            color: rgb(225, 224, 224);
            border: 1px solid transition: color 0.2s ease;
        }

        .icon-selected {
            color: gold;
        }
    </style>
</head>

<body>
    <div class="container py-5"
        style="max-width: 720px; box-shadow:5px 10px 20px rgb(190, 190, 190); border-radius:10px">
        <center><img style="width: 100%" src="{{ setting('logo') }}" alt=""></center>
        <hr>
        <div class="mb-4 mt-5">
            <h2 class="fw-bold">{{ $survey->title }}</h2>
            <p class="text-muted">{{ $survey->description }}</p>
        </div>

        <form action="{{ route('submit-survey', $survey->slug_link) }}" method="post" enctype="multipart/form-data">

            @csrf
            @if ($survey->question->isNotEmpty())

                @foreach ($survey->question as $r)
                    <div class="card mb-4"
                        @error("answere_{$r->id}")
                            style="border: 1px solid red"
                            @enderror>
                        <div class="card-body">
                            <label class="form-label fw-semibold">{{ $loop->iteration }}.
                                {{ $r->question_text }}</label>
                            @if ($r->type == 'text')
                                <input type="text" name="answere_{{ $r->id }}"
                                    value="{{ old("answere_{$r->id}") }}" class="form-control"
                                    {{ $r->is_required ? 'required' : '' }}>
                            @elseif ($r->type == 'email')
                                <input type="email" name="answere_{{ $r->id }}"
                                    value="{{ old("answere_{$r->id}") }}" class="form-control"
                                    {{ $r->is_required ? 'required' : '' }}>
                            @elseif ($r->type == 'date')
                                <input type="date" name="answere_{{ $r->id }}"
                                    value="{{ old("answere_{$r->id}") }}" class="form-control"
                                    {{ $r->is_required ? 'required' : '' }}>
                            @elseif ($r->type == 'month')
                                <input type="month" name="answere_{{ $r->id }}"
                                    value="{{ old("answere_{$r->id}") }}" class="form-control"
                                    {{ $r->is_required ? 'required' : '' }}>
                            @elseif ($r->type == 'datetime-local')
                                <input type="datetime-local" name="answere_{{ $r->id }}"
                                    value="{{ old("answere_{$r->id}") }}" class="form-control"
                                    {{ $r->is_required ? 'required' : '' }}>
                            @elseif ($r->type == 'textarea')
                                <textarea name="answere_{{ $r->id }}" class="form-control" rows="5"
                                    {{ $r->is_required ? 'required' : '' }}>{{ old("answere_{$r->id}") }}</textarea>
                            @elseif ($r->type == 'rate')
                                <div class="d-flex gap-4 flex-wrap justify-content-center align-items-center">
                                    @for ($i = 1; $i <= $r->rate; $i++)
                                        <div class="item d-flex flex-column align-items-center">
                                            <span>{{ $i }}</span>
                                            <i class="ti {{ $r->icon }}-filled ti-md icon-selectable"
                                                data-index="{{ $i }}" data-id="{{ $r->id }}"
                                                data-icon="{{ $r->icon }}"></i>
                                        </div>
                                    @endfor
                                </div>
                                <input type="hidden" name="answere_{{ $r->id }}"
                                    value="{{ old("answere_{$r->id}", '0') }}">
                            @elseif ($r->type == 'checkbox')
                                @foreach ($r->option as $o)
                                    <div class="d-flex gap-3">
                                        <input type="checkbox" name="answere_{{ $r->id }}[]"
                                            value="{{ $o->option_text }}"
                                            {{ is_array(old("answere_{$r->id}")) && in_array($o->option_text, old("answere_{$r->id}")) ? 'checked' : '' }}>
                                        <span>{{ $o->option_text }}</span>
                                    </div>
                                @endforeach
                            @elseif ($r->type == 'radio')
                                @foreach ($r->option as $o)
                                    <div class="d-flex gap-3">
                                        <input type="radio" name="answere_{{ $r->id }}"
                                            value="{{ $o->option_text }}"
                                            {{ old("answere_{$r->id}") == $o->option_text ? 'checked' : '' }}
                                            {{ $r->is_required ? 'required' : '' }}>
                                        <span>{{ $o->option_text }}</span>
                                    </div>
                                @endforeach
                            @elseif ($r->type == 'dropdown')
                                <select name="answere_{{ $r->id }}" class="form-control"
                                    {{ $r->is_required ? 'required' : '' }}>
                                    <option value="">Choose</option>
                                    @foreach ($r->option as $o)
                                        <option value="{{ $o->option_text }}"
                                            {{ old("answere_{$r->id}") == $o->option_text ? 'selected' : '' }}>
                                            {{ $o->option_text }}</option>
                                    @endforeach
                                </select>
                            @endif

                            @error("answere_{$r->id}")
                                <div class="text-danger mt-2">{{ sanitizeErrorMessage($message) }}</div>
                            @enderror


                        </div>
                    </div>
                @endforeach
            @else
            @endif


            <!-- Tombol Kirim -->
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">Send</button>
            </div>
        </form>
    </div>

    <script src="{{ asset('vuexy/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="{{ asset('vuexy/assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Ketika ikon diklik
            $('.icon-selectable').click(function() {
                var selectedIndex = $(this).data('index'); // Ambil data-index dari ikon yang diklik
                var questionId = $(this).data('id'); // Ambil ID pertanyaan yang diklik
                var icon = $(this).data('icon'); // Ambil ID pertanyaan yang diklik



                // Set nilai input hidden dengan rating yang dipilih
                $('input[name="answere_' + questionId + '"]').val(selectedIndex);

                // Ubah warna ikon yang diklik dan ikon sebelumnya
                $('.icon-selectable[data-id="' + questionId + '"]').each(function() {
                    var currentIndex = $(this).data('index');
                    if (currentIndex <= selectedIndex) {
                        // Terapkan warna berdasarkan ikon
                        if (icon === 'ti-star') {
                            $(this).css('color', 'gold');
                        } else if (icon === 'ti-heart') {
                            $(this).css('color', 'red');
                        } else if (icon === 'ti-thumb-up') {
                            $(this).css('color', 'blue');
                        }
                    } else {
                        // Reset warna jika tidak terpilih
                        $(this).css('color', '#ccc'); // atau warna default
                    }
                });
            });
            // Inisialisasi ulang jika ada old input (untuk validasi gagal)
            $('.icon-selectable').each(function() {
                var questionId = $(this).data('id');
                var selectedIndex = $('input[name="answere_' + questionId + '"]').val();

                if (selectedIndex) {
                    $('.icon-selectable[data-id="' + questionId + '"]').each(function() {
                        var currentIndex = $(this).data('index');
                        var icon = $(this).data('icon');
                        if (currentIndex <= selectedIndex) {
                            // $(this).toggleClass('icon-selected', currentIndex <= selectedIndex);
                            if (icon === 'ti-star') {
                                $(this).css('color', 'gold');
                            } else if (icon === 'ti-heart') {
                                $(this).css('color', 'red');
                            } else if (icon === 'ti-thumb-up') {
                                $(this).css('color', 'blue');
                            }
                        }
                    });
                }
            });
        });
    </script>

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
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
                title: 'Gagal!',
                text: '{{ session('error') }}',
                customClass: {
                    confirmButton: 'btn btn-primary waves-effect waves-light'
                },
                buttonsStyling: false
            });
        </script>
    @endif
</body>

</html>
