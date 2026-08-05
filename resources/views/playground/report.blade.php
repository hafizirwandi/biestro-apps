@extends('layouts.main-layout.app')
@section('title', 'Laporan Playground')

@section('content')

    @include('report.partials.period-filter')

    <div class="card mb-4">
        <div class="card-body">
            <h6 class="mb-3">{{ $label }}</h6>
            <div class="table-responsive">
                <table class="datatable table table-striped">
                    <thead>
                        <tr>
                            <th>Nama Anak</th>
                            <th>Jenis Kelamin</th>
                            <th>Warna Baju</th>
                            <th>Wahana</th>
                            <th>Durasi</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Status</th>
                            <th>Petugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $r)
                            <tr>
                                <td>{{ $r->child_name }}</td>
                                <td>{{ $r->gender == 'male' ? 'Laki-laki' : 'Perempuan' }}</td>
                                <td>{{ $r->clothing_color }}</td>
                                <td>{{ $r->wahana->name ?? '-' }}</td>
                                <td>{{ $r->duration_minutes }} menit</td>
                                <td>{{ $r->started_at->format('d M Y H:i') }}</td>
                                <td>{{ $r->picked_up_at ? $r->picked_up_at->format('d M Y H:i') : '-' }}</td>
                                <td>
                                    @if ($r->status == 'picked_up')
                                        <span class="badge bg-success">Selesai</span>
                                    @elseif ($r->status == 'time_up')
                                        <span class="badge bg-danger">Waktu Habis</span>
                                    @else
                                        <span class="badge bg-primary">Bermain</span>
                                    @endif
                                </td>
                                <td>{{ $r->createdBy->name ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
