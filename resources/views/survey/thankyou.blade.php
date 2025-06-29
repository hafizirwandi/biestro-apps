<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thankyou</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vuexy/assets/vendor/fonts/tabler-icons.css') }}" />

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
        <div class="mb-4">


            <p class="mt-4">Your answer has been recorded.</p>

            <a href="{{ url('/s/' . $survey->slug_link) }}">Submit another answer</a>
        </div>
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



                // Set nilai input hidden dengan rating yang dipilih
                $('input[name="answere_' + questionId + '"]').val(selectedIndex);

                // Ubah warna ikon yang diklik dan ikon sebelumnya
                $('.icon-selectable[data-id="' + questionId + '"]').each(function() {
                    var currentIndex = $(this).data('index');
                    if (currentIndex <= selectedIndex) {
                        $(this).addClass('icon-selected'); // Warna emas
                    } else {
                        $(this).removeClass('icon-selected'); // Warna abu
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
                        $(this).toggleClass('icon-selected', currentIndex <= selectedIndex);
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
