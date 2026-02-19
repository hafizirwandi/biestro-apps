@extends('layouts.main-layout.app-transaction')
@section('title', 'Ticket')
@section('content')


    <div class="d-flex justify-content-center align-items-center" style="height: 80vh;">
        <div class="col-md-4">
            <div class="alert alert-danger " role="alert">
                <h4 class="alert-heading">Are you sure?</h4>
                <p>
                    Once closed, you can only start a new shift tomorrow.<br>
                    Please make sure your balance is correct before closing.
                </p>

            </div>
            <div class="card">
                <div class="card-body">
                    <h3 class="text-center">Close Shift</h3>
                    <form class="row g-3" method="post" action="{{ route('transaction.set-close-shift') }}">
                        @csrf

                        <input type="hidden" name="cashier_shift_id" value="{{ $shift->id }}">
                        <div class="col-12 col-md-12">
                            <label class="form-label">System Balance</label>
                            <input type="text" name="system_balance" class="form-control auto-numeric"
                                placeholder="Enter Text" value="{{ $system_balance }}" required readonly />
                        </div>
                        <div class="col-12 col-md-12">
                            <label class="form-label">Closing Balance</label>
                            <input type="text" name="closing_balance" class="form-control auto-numeric"
                                placeholder="Enter Text" required />
                        </div>

                        <div class="col-12 col-md-12">
                            <label class="form-label">Difference</label>
                            <input type="text" name="difference" class="form-control auto-numeric"
                                placeholder="Enter Text" required readonly />
                        </div>
                        <div class="col-12 col-md-12">
                            <label class="form-label">Note</label>
                            <textarea name="notes" rows="5" class="form-control"></textarea>
                        </div>

                        <div class="col-12 text-center">
                            <a href="{{ route('transaction') }}" class="btn btn-default me-sm-3 me-1">
                                Back</a>
                            <button type="submit" class="btn btn-primary me-sm-3 me-1" id="btn-close-shift">Close
                                Shift</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Init semua input dengan class auto-numeric
            document.querySelectorAll('.auto-numeric').forEach(el => {
                if (!AutoNumeric.getAutoNumericElement(el)) {
                    new AutoNumeric(el, {
                        digitGroupSeparator: ',',
                        decimalPlaces: 0,
                        currencySymbol: 'Rp. ',
                        unformatOnSubmit: true
                    });
                }
            });

            const systemAuto = AutoNumeric.getAutoNumericElement(document.querySelector(
                'input[name="system_balance"]'));
            const closingAuto = AutoNumeric.getAutoNumericElement(document.querySelector(
                'input[name="closing_balance"]'));
            const diffAuto = AutoNumeric.getAutoNumericElement(document.querySelector('input[name="difference"]'));

            function updateDifference() {
                const systemVal = systemAuto ? systemAuto.getNumber() : 0;
                const closingVal = closingAuto ? closingAuto.getNumber() : 0;
                const diff = closingVal - systemVal;

                if (diffAuto) {
                    diffAuto.set(diff);
                }
            }

            if (closingAuto) {
                closingAuto.domElement.addEventListener('input', updateDifference);
            }

            // langsung hitung sekali saat halaman load
            updateDifference();

            document.getElementById('btn-close-shift').addEventListener('click', function(e) {
                e.preventDefault(); // cegah submit langsung

                const closingInput = document.querySelector('input[name="closing_balance"]');
                let closingVal = closingInput.value.replace(/\D/g, ''); // buang Rp, koma, dll

                if (!closingVal || closingVal === "0") {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Closing Balance kosong!',
                        text: 'Harap isi Closing Balance sebelum menutup shift.',
                        customClass: {
                            confirmButton: 'btn btn-primary waves-effect waves-light',
                        },
                    });
                    return;
                }


                Swal.fire({
                    title: "Are you sure?",
                    text: "Once closed, you can only start a new shift tomorrow. Please make sure your balance is correct before closing.",
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
                        let form = $(this).closest('form');

                        form.find('.autonumeric').each(function() {
                            $(this).val(AutoNumeric.unformat($(this).val(), {
                                digitGroupSeparator: ',',
                                decimalCharacter: '.'
                            }));
                        });

                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
