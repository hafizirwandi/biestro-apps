@extends('layouts.main-layout.app')
@section('title', 'Report Visitor')

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
                    <label>Wahana</label>
                    <select name="wahana" class="form-select">
                        <option value="all">All Wahana</option>
                        @foreach ($wahana as $w)
                            <option value="{{ $w->id }}" {{ request('wahana') == $w->id ? 'selected' : '' }}>
                                {{ $w->name }}</option>
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
                            <th>Wahana</th>
                            <th>Visitor Count (Tickets Used)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $r)
                            <tr>
                                <td>{{ $r['wahana'] }}</td>
                                <td>{{ number_format($r['count']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total</th>
                            <th>{{ number_format($data->sum('count')) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection
