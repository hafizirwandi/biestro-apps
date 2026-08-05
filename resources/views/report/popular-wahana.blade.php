@extends('layouts.main-layout.app')
@section('title', 'Laporan Wahana Terpopuler')

@php
    $currentType = request('type', 'day');
@endphp

@section('content')

    <div class="alert alert-primary" role="alert">
        <form action="" method="get">
            <div class="row g-3">
                <div class="col-md-2">
                    <label>Tipe Periode</label>
                    <select name="type" id="periodType" class="form-control">
                        <option value="day" {{ $currentType == 'day' ? 'selected' : '' }}>Harian</option>
                        <option value="week" {{ $currentType == 'week' ? 'selected' : '' }}>Mingguan</option>
                        <option value="month" {{ $currentType == 'month' ? 'selected' : '' }}>Bulanan</option>
                        <option value="year" {{ $currentType == 'year' ? 'selected' : '' }}>Tahunan</option>
                        <option value="period" {{ $currentType == 'period' ? 'selected' : '' }}>Periode</option>
                    </select>
                </div>
                <div class="col-md-3 {{ $currentType == 'period' ? '' : 'd-none' }}" id="periodRangeWrapper">
                    <label>Rentang Tanggal</label>
                    <input type="text" id="bs-rangepicker-basic" name="period" value="{{ request('period') }}"
                        class="form-control">
                </div>
                <div class="col-md-2">
                    <label>Status Tiket</label>
                    <select name="status" class="form-control">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua</option>
                        <option value="sold" {{ request('status') == 'sold' ? 'selected' : '' }}>Berbayar</option>
                        <option value="free" {{ request('status') == 'free' ? 'selected' : '' }}>Gratis</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="mt-4 btn btn-warning text-nowrap btn-sm waves-effect waves-light">
                        <i class="ti ti-search ti-sm me-2"></i>Cari
                    </button>
                </div>
            </div>
        </form>
    </div>
    <script>
        document.getElementById('periodType').addEventListener('change', function() {
            document.getElementById('periodRangeWrapper').classList.toggle('d-none', this.value !== 'period');
        });
    </script>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Periode</h6>
                    <h5 class="fw-bold">{{ $label }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h6>Total Tiket</h6>
                    <h4 class="fw-bold">{{ number_format($grandTotal) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card mb-4">
                <div class="card-body">
                    <div id="chartPopularWahana"></div>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="datatable table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Wahana</th>
                                    <th>Jumlah Tiket</th>
                                    <th>Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $i => $r)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $r->wahana->name ?? '-' }}</td>
                                        <td>{{ number_format($r->total) }}</td>
                                        <td>{{ $grandTotal > 0 ? number_format($r->total / $grandTotal * 100, 1) : 0 }}%
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        const chartOptions = {
            chart: {
                type: 'bar',
                height: 350
            },
            series: [{
                name: 'Jumlah Tiket',
                data: @json($data->pluck('total'))
            }],
            xaxis: {
                categories: @json($data->map(fn($r) => $r->wahana->name ?? '-'))
            },
            plotOptions: {
                bar: {
                    horizontal: true
                }
            }
        };
        new ApexCharts(document.querySelector("#chartPopularWahana"), chartOptions).render();
    </script>
@endsection
