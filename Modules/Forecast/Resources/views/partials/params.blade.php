<div class="card d-print-none">
    <div class="card-body">
        <form action="{{ $action }}" method="GET" class="form-row align-items-end">
            <div class="col-md-2 mb-2">
                <label class="small font-weight-bold">Periode rata-rata (n)</label>
                <select name="n" class="form-control">
                    @foreach(range(2, 6) as $n)
                        <option value="{{ $n }}" {{ $params['n'] == $n ? 'selected' : '' }}>{{ $n }} bulan</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label class="small font-weight-bold">Jumlah data</label>
                <select name="months" class="form-control">
                    @foreach([6, 12, 18, 24] as $months)
                        <option value="{{ $months }}" {{ $params['months'] == $months ? 'selected' : '' }}>{{ $months }} bulan</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label class="small font-weight-bold">Data sampai bulan</label>
                <input type="month" name="end_month" class="form-control" value="{{ $params['end_month']->format('Y-m') }}">
            </div>
            @if(!empty($withCategory))
                <div class="col-md-3 mb-2">
                    <label class="small font-weight-bold">Kategori</label>
                    <select name="category_id" class="form-control">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-md-2 mb-2">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="bi bi-calculator"></i> Hitung
                </button>
            </div>
        </form>
    </div>
</div>
