@extends('layouts.main-layout.app-transaction')
@section('title', 'Ticket')
@section('content')
    <div class="row mb-5">
        <div class="col-md-12">
            <a class="btn btn-default" href="{{ route('transaction') }}">Back</a>
            <a class="btn btn-primary" href="{{ route('transaction.close-shift') }}">Cashier Closing</a>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label d-block">Filter Cara Bayar:</label>
        <div class="btn-group flex-wrap" role="group">
            <a href="{{ route('transaction.sales-revenue') }}"
                class="btn btn-outline-primary {{ request('filter') == '' ? 'active' : '' }}">Semua</a>
            <a href="{{ route('transaction.sales-revenue', ['filter' => 'cash']) }}"
                class="btn btn-outline-primary {{ request('filter') == 'cash' ? 'active' : '' }}">Cash</a>
            <a href="{{ route('transaction.sales-revenue', ['filter' => 'noncash']) }}"
                class="btn btn-outline-primary {{ request('filter') == 'noncash' ? 'active' : '' }}">Non-Cash (All)</a>
            <a href="{{ route('transaction.sales-revenue', ['filter' => 'qris']) }}"
                class="btn btn-outline-secondary {{ request('filter') == 'qris' ? 'active' : '' }}">QRIS</a>
            <a href="{{ route('transaction.sales-revenue', ['filter' => 'transfer']) }}"
                class="btn btn-outline-secondary {{ request('filter') == 'transfer' ? 'active' : '' }}">Transfer</a>
            <a href="{{ route('transaction.sales-revenue', ['filter' => 'edc']) }}"
                class="btn btn-outline-secondary {{ request('filter') == 'edc' ? 'active' : '' }}">EDC</a>
            <a href="{{ route('transaction.sales-revenue', ['filter' => 'lainnya']) }}"
                class="btn btn-outline-secondary {{ request('filter') == 'lainnya' ? 'active' : '' }}">Lainnya</a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">

                    <table class="table table-responsive table-striped datatable2">
                        <thead>
                            <tr>

                                <th>Transaction code</th>
                                <th>Payment Status</th>
                                <th>Total Amount</th>
                                <th>Payment Type</th>
                                <th>Amount Given</th>
                                <th>Non Cash Method</th>
                                <th>Non Cash Channel</th>
                                <th>Paid At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transaction as $r)
                                <tr>

                                    <td>{{ $r->transaction_code }}</td>
                                    <td>{{ $r->payment_status }}</td>
                                    <td>{{ format_rupiah($r->total_amount) }}</td>
                                    <td>{{ $r->payment_type }}</td>
                                    <td>{{ format_rupiah($r->amount_given) }}</td>
                                    <td>{{ $r->noncash_method }}</td>
                                    <td>{{ $r->noncash_channel }}</td>
                                    <td>{{ $r->paid_at }}</td>
                                    <td><a class="btn btn-primary btn-xs" href="{{ route('transaction.view', $r->id) }}">
                                            <i class="ti ti-printer ti-xs"></i></a></td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
            </div>

        </div>
    </div>
    <div class="mt-3 p-3 border rounded bg-white">
        <div class="row">
            <div class="col-md-6">
                <h5 class="mb-3">Summary Sales Shift Ini</h5>
                <table class="table table-borderless table-sm">
                    <tr>
                        <td>Total Cash</td>
                        <td class="text-end fw-bold">{{ format_rupiah($totalCash) }}</td>
                    </tr>
                    <tr>
                        <td>Total Non-Cash</td>
                        <td class="text-end fw-bold">{{ format_rupiah($totalNonCash) }}</td>
                    </tr>
                    <tr class="border-top">
                        <td><strong>Grand Total Sales</strong></td>
                        <td class="text-end fw-bold fs-5 text-primary">{{ format_rupiah($grandTotal) }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h5 class="mb-3">Detail Non-Cash Channel</h5>
                @if (isset($nonCashBreakdown) && count($nonCashBreakdown) > 0)
                    <ul class="list-group">
                        @foreach ($nonCashBreakdown as $channel => $amount)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-1">
                                {{ $channel ?: 'Unspecified' }}
                                <span class="badge bg-label-primary">{{ format_rupiah($amount) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted small fst-italic">Tidak ada transaksi non-cash pada shift ini.</p>
                @endif
            </div>
        </div>
        <hr>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="text-muted">Opening Balance:</span>
                <strong>{{ format_rupiah($shift->opening_balance) }}</strong>
            </div>
            <div>
                <span class="text-muted">Total Cash in Drawer (Opening + Sales):</span> <strong
                    class="fs-5 text-success">{{ format_rupiah($shift->opening_balance + $totalCash) }}</strong>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            $('.datatable2').DataTable({
                paging: false,
                searching: false, // disable search karena sudah ada filter server side query link
                info: false
            });
        });
    </script>
@endsection
