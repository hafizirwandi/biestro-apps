@extends('layouts.main-layout.app')
@section('title', 'Laporan Penggunaan Tiket')

@php
    $extraFilters = view('report.partials.ticket-usage-wahana-filter', ['wahanaList' => $wahanaList])->render();
@endphp

@section('content')

    @include('report.partials.period-filter')

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Periode</h6>
                    <h5 class="fw-bold">{{ $label }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Tiket Terbit</h6>
                    <h4 class="fw-bold">{{ number_format($grandTotal) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Sudah Dipakai</h6>
                    <h4 class="fw-bold text-success">{{ number_format($grandUsed) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Belum Dipakai</h6>
                    <h4 class="fw-bold text-danger">{{ number_format($grandTotal - $grandUsed) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-7">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Penggunaan per Periode</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="datatable table table-striped">
                            <thead>
                                <tr>
                                    <th>Periode</th>
                                    <th>Terbit</th>
                                    <th>Dipakai</th>
                                    <th>Belum Dipakai</th>
                                    <th>% Dipakai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rows as $r)
                                    <tr>
                                        <td>{{ $r['label'] }}</td>
                                        <td>{{ number_format($r['total']) }}</td>
                                        <td class="text-success">{{ number_format($r['used']) }}</td>
                                        <td class="text-danger">{{ number_format($r['unused']) }}</td>
                                        <td>{{ $r['pct'] }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Total</th>
                                    <th>{{ number_format($grandTotal) }}</th>
                                    <th>{{ number_format($grandUsed) }}</th>
                                    <th>{{ number_format($grandTotal - $grandUsed) }}</th>
                                    <th>{{ $grandTotal > 0 ? round($grandUsed / $grandTotal * 100, 1) : 0 }}%</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Penggunaan per Wahana</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="datatable table table-striped">
                            <thead>
                                <tr>
                                    <th>Wahana</th>
                                    <th>Terbit</th>
                                    <th>Dipakai</th>
                                    <th>% Dipakai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($byWahana as $r)
                                    <tr>
                                        <td>{{ $r['wahana'] }}</td>
                                        <td>{{ number_format($r['total']) }}</td>
                                        <td class="text-success">{{ number_format($r['used']) }}</td>
                                        <td>{{ $r['pct'] }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-muted text-center">Tidak ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
