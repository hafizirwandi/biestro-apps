@extends('layouts.main-layout.app')
@section('title', 'Report Shift')

@section('content')

    <div class="alert alert-primary" role="alert">
        <form action="">
            <div class="row g-3">
                <div class="col-md-3">
                    <label>Periode</label>
                    <input type="text" id="bs-rangepicker-basic" name="period" value="{{ request()->period }}"
                        class="form-control">
                </div>
                <div class="col-md-3">
                    <label>Cashier</label>
                    <select name="user_id" class="form-select">
                        <option value="all">All Cashiers</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="mt-4 btn btn-warning  text-nowrap  btn-sm waves-effect waves-light">
                        <i class="ti ti-search ti-sm me-2"></i>Cari
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="datatable table table-striped">
                    <thead>
                        <tr>
                            <th>Cashier</th>
                            <th>Opened At</th>
                            <th>Closed At</th>
                            <th>Opening Balance</th>
                            <th>Closing Balance</th>
                            <th>Difference</th>
                            <th>Status Balance</th>
                            <th>Status</th>
                            <th>Information</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $r)
                            <tr>
                                <td>{{ $r->user->name ?? '-' }}</td>
                                <td>{{ $r->opened_at }}</td>
                                <td>{{ $r->closed_at ?? '-' }}</td>
                                <td>{{ format_rupiah($r->opening_balance) }}</td>
                                <td>{{ format_rupiah($r->closing_balance) }}</td>
                                <td
                                    class="{{ $r->difference < 0 ? 'text-danger' : ($r->difference > 0 ? 'text-success' : '') }}">
                                    {{ format_rupiah($r->difference) }}
                                </td>
                                <td>
                                    @if ($r->status_balance == 'surplus')
                                        <span class="badge bg-success">Lebih</span>
                                    @elseif($r->status_balance == 'deficit')
                                        <span class="badge bg-danger">Kurang</span>
                                    @else
                                        <span class="badge bg-secondary">Pas</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $r->status == 'open' ? 'bg-info' : 'bg-secondary' }}">
                                        {{ ucfirst($r->status) }}
                                    </span>
                                </td>
                                <td>{{ $r->notes ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
