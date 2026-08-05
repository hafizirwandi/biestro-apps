@extends('layouts.main-layout.app')
@section('title', 'Laporan Omset')

@section('content')

    @include('report.partials.period-filter')

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Periode</h6>
                    <h5 class="fw-bold">{{ $label }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Jumlah Transaksi</h6>
                    <h4 class="fw-bold">{{ number_format($grandCount) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Total Omset</h6>
                    <h4 class="fw-bold text-success">{{ format_rupiah($grandTotal) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="datatable table table-striped">
                    <thead>
                        <tr>
                            <th>Periode</th>
                            <th>Jumlah Transaksi</th>
                            <th>Total Omset</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $r)
                            <tr>
                                <td>{{ $r['label'] }}</td>
                                <td>{{ number_format($r['count']) }}</td>
                                <td>{{ format_rupiah($r['total']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total</th>
                            <th>{{ number_format($grandCount) }}</th>
                            <th>{{ format_rupiah($grandTotal) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
