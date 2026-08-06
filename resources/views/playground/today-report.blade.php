@extends('layouts.main-layout.app-transaction')
@section('title', 'Report Playground Hari Ini')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Report Playground — {{ $date }}</h4>
        <span class="badge bg-label-primary">{{ $data->count() }} anak bermain</span>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nama Anak</th>
                            <th>Jenis Kelamin</th>
                            <th>Warna Baju</th>
                            <th>Durasi</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Status</th>
                            <th>Petugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $r)
                            <tr>
                                <td>{{ $r->child_name }}</td>
                                <td>{{ $r->gender == 'male' ? 'Laki-laki' : 'Perempuan' }}</td>
                                <td>{{ $r->clothing_color }}</td>
                                <td>{{ $r->duration_minutes }} menit</td>
                                <td>{{ $r->started_at->format('H:i') }}</td>
                                <td>{{ $r->picked_up_at ? $r->picked_up_at->format('H:i') : '-' }}</td>
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
                        @empty
                            <tr>
                                <td colspan="8" class="text-muted text-center">Belum ada anak bermain hari ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
