<div class="col-md-2">
    <label>Wahana</label>
    <select name="wahana" class="form-control">
        <option value="all" {{ request('wahana', 'all') == 'all' ? 'selected' : '' }}>Semua</option>
        @foreach ($wahanaList as $w)
            <option value="{{ $w->id }}" {{ (string) request('wahana') === (string) $w->id ? 'selected' : '' }}>
                {{ $w->name }}
            </option>
        @endforeach
    </select>
</div>
