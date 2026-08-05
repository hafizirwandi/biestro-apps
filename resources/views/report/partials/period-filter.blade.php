@php
    $currentType = request('type', 'day');
@endphp
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
            @isset($extraFilters)
                {!! $extraFilters !!}
            @endisset
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
