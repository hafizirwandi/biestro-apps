@extends('layouts.main-layout.app')
@section('title', 'Report Item Sales')

@section('content')

    <div class="alert alert-primary" role="alert">
        <form action="">
            <div class="row g-3">
                <div class="col-md-3">
                    <label>Periode</label>
                    <input type="text" id="bs-rangepicker-basic" name="period" value="{{ request()->period }}"
                        class="form-control">
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
                            <th>Item Name</th>
                            <th>Type</th>
                            <th>Qty Sold</th>
                            <th>Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $r)
                            <tr>
                                <td>{{ $r['name'] }}</td>
                                <td>
                                    <span class="badge {{ $r['type'] == 'Ticket' ? 'bg-primary' : 'bg-info' }}">
                                        {{ $r['type'] }}
                                    </span>
                                </td>
                                <td>{{ number_format($r['qty']) }}</td>
                                <td>{{ format_rupiah($r['total']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">Total</th>
                            <th>{{ number_format($data->sum('qty')) }}</th>
                            <th>{{ format_rupiah($data->sum('total')) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
