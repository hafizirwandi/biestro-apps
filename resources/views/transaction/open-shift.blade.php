@extends('layouts.main-layout.app-transaction')
@section('title', 'Buka Shift')

@section('css')
    <style>
        /* ── full-page centering ── */
        .open-shift-wrap {
            min-height: 82vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        /* ── card ── */
        .shift-card {
            width: 100%;
            max-width: 460px;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.14);
            background: #fff;
        }

        /* ── dark header ── */
        .shift-card-header {
            background: linear-gradient(135deg, #1e1e2e 0%, #2d2d44 100%);
            color: #fff;
            padding: 30px 28px 24px;
            text-align: center;
        }

        .shift-card-header .icon-wrap {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            font-size: 26px;
            color: #a8d8a0;
        }

        .shift-card-header h2 {
            font-size: 1.35rem;
            font-weight: 700;
            margin: 0 0 6px;
        }

        .shift-card-header p {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.55);
            margin: 0;
            line-height: 1.5;
        }

        /* ── body ── */
        .shift-card-body {
            padding: 28px 28px 30px;
        }

        /* ── info row ── */
        .shift-info-row {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f5f5fb;
            border-radius: 10px;
            padding: 11px 14px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #555;
        }

        .shift-info-row i {
            color: #7367f0;
            font-size: 15px;
            flex-shrink: 0;
        }

        /* ── form fields ── */
        .shift-form-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #888;
            margin-bottom: 6px;
            display: block;
        }

        .shift-form-control {
            border-radius: 10px;
            border: 1.5px solid #e0e0ee;
            padding: 10px 14px;
            font-size: 14px;
            width: 100%;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: #fafafa;
        }

        .shift-form-control:focus {
            outline: none;
            border-color: #7367f0;
            box-shadow: 0 0 0 3px rgba(115, 103, 240, 0.12);
            background: #fff;
        }

        /* ── submit button ── */
        .btn-open-shift {
            width: 100%;
            padding: 13px;
            border-radius: 12px;
            background: linear-gradient(135deg, #7367f0, #8a7bf4);
            color: #fff;
            border: none;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-open-shift:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(115, 103, 240, 0.4);
        }

        .btn-open-shift:active {
            transform: translateY(0);
        }

        /* ── divider ── */
        .shift-divider {
            border: none;
            border-top: 1px solid #eee;
            margin: 20px 0;
        }
    </style>
@endsection

@section('content')
    <div class="open-shift-wrap">
        <div class="shift-card">

            {{-- Header --}}
            <div class="shift-card-header">
                <div class="icon-wrap">
                    <i class="fas fa-store"></i>
                </div>
                <h2>Buka Shift Kasir</h2>
                <p>Pastikan modal awal sudah disiapkan sebelum<br>memulai sesi transaksi hari ini.</p>
            </div>

            {{-- Body --}}
            <div class="shift-card-body">

                {{-- Cashier info row --}}
                <div class="shift-info-row">
                    <i class="fas fa-user-circle"></i>
                    <span>
                        Login sebagai <strong>{{ auth()->user()->name }}</strong> &mdash;
                        {{ now()->translatedFormat('l, d F Y') }}
                    </span>
                </div>

                {{-- Validation errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger py-2 mb-3" style="border-radius:10px;font-size:13px">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('transaction.set-open-shift') }}" id="openShiftForm">
                    @csrf

                    {{-- Counter select --}}
                    <div class="mb-4">
                        <label class="shift-form-label" for="counter_id">
                            <i class="fas fa-cash-register me-1"></i> Counter
                        </label>
                        <select name="counter_id" id="counter_id" class="shift-form-control" required>
                            <option value="" disabled selected>-- Pilih Counter --</option>
                            @foreach ($counter as $r)
                                <option value="{{ $r->id }}" {{ old('counter_id') == $r->id ? 'selected' : '' }}>
                                    {{ $r->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Opening Balance --}}
                    <div class="mb-2">
                        <label class="shift-form-label" for="opening_balance">
                            <i class="fas fa-money-bill-wave me-1"></i> Opening Balance (Modal Awal)
                        </label>
                        <input type="text" name="opening_balance" id="opening_balance"
                            class="shift-form-control auto-numeric" placeholder="Rp 0" value="{{ old('opening_balance') }}"
                            required autocomplete="off" />
                    </div>

                    <hr class="shift-divider">

                    {{-- Submit --}}
                    <button type="submit" class="btn-open-shift" id="btnOpenShift">
                        <i class="fas fa-play-circle"></i>
                        Mulai Shift Sekarang
                    </button>
                </form>

            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // Style the auto-numeric input on focus (overrides global style if needed)
        const balanceInput = document.getElementById('opening_balance');
        if (balanceInput && !AutoNumeric.getAutoNumericElement(balanceInput)) {
            new AutoNumeric(balanceInput, {
                digitGroupSeparator: '.',
                decimalCharacter: ',',
                decimalPlaces: 0,
                currencySymbol: 'Rp ',
                currencySymbolPlacement: 'p',
                unformatOnSubmit: true,
            });
        }

        // Loading state on submit
        document.getElementById('openShiftForm').addEventListener('submit', function() {
            const btn = document.getElementById('btnOpenShift');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Membuka Shift...';
        });
    </script>
@endsection
