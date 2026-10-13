@extends('layouts.app')

@section('title', 'Prediksi Kebutuhan Stok')

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
        <li class="breadcrumb-item active">Prediksi Stok</li>
    </ol>
@endsection

@section('content')
    @php
        $needRestock = $results->where('recommendation', '>', 0)->count();
    @endphp
    <div class="container-fluid">
        @include('forecast::partials.params', ['action' => route('forecast.index'), 'withCategory' => true])

        <div class="row">
            <div class="col-md-4">
                <div class="card border-0">
                    <div class="card-body p-0 d-flex align-items-center shadow-sm">
                        <div class="bg-gradient-primary p-4 mfe-3 rounded-left">
                            <i class="bi bi-calendar-event font-2xl"></i>
                        </div>
                        <div>
                            <div class="text-value text-primary">{{ $targetPeriod }}</div>
                            <div class="text-muted text-uppercase font-weight-bold small">Periode Prediksi</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0">
                    <div class="card-body p-0 d-flex align-items-center shadow-sm">
                        <div class="bg-gradient-warning p-4 mfe-3 rounded-left">
                            <i class="bi bi-exclamation-triangle font-2xl"></i>
                        </div>
                        <div>
                            <div class="text-value text-warning">{{ $needRestock }} produk</div>
                            <div class="text-muted text-uppercase font-weight-bold small">Perlu Restock</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0">
                    <div class="card-body p-0 d-flex align-items-center shadow-sm">
                        <div class="bg-gradient-success p-4 mfe-3 rounded-left">
                            <i class="bi bi-box-seam font-2xl"></i>
                        </div>
                        <div>
                            <div class="text-value text-success">{{ number_format($results->sum('forecast'), 0, ',', '.') }}</div>
                            <div class="text-muted text-uppercase font-weight-bold small">Total Prediksi Kebutuhan</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <strong>Prediksi Kebutuhan Stok — {{ $targetPeriod }}</strong><br>
                    <small class="text-muted">
                        Metode Single Moving Average n = {{ $params['n'] }},
                        data penjualan {{ $firstPeriod }} s/d {{ $lastPeriod }} ({{ $params['months'] }} bulan)
                    </small>
                </div>
                <button onclick="window.print()" class="btn btn-sm btn-outline-secondary d-print-none">
                    <i class="bi bi-printer"></i> Cetak
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm mb-0">
                        <thead class="thead-light">
                            <tr class="text-center">
                                <th>No</th>
                                <th>Kode</th>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th>Terjual {{ $lastPeriod }}</th>
                                <th>Total Terjual</th>
                                <th>Prediksi {{ $targetPeriod }}</th>
                                <th>MAPE</th>
                                <th>Stok Saat Ini</th>
                                <th>Rekomendasi Restock</th>
                                <th>Status</th>
                                <th class="d-print-none">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($results as $row)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-nowrap">{{ $row->product->product_code }}</td>
                                    <td>{{ $row->product->product_name }}</td>
                                    <td>{{ $row->product->category->category_name ?? '-' }}</td>
                                    <td class="text-right">{{ $row->last_actual }}</td>
                                    <td class="text-right">{{ $row->total_sold }}</td>
                                    <td class="text-right font-weight-bold">{{ $row->forecast }} {{ $row->product->product_unit }}</td>
                                    <td class="text-right">{{ $row->mape === null ? '-' : number_format($row->mape, 2, ',', '.') . '%' }}</td>
                                    <td class="text-right">{{ $row->stock }} {{ $row->product->product_unit }}</td>
                                    <td class="text-right font-weight-bold {{ $row->recommendation > 0 ? 'text-danger' : '' }}">
                                        {{ $row->recommendation }} {{ $row->product->product_unit }}
                                    </td>
                                    <td class="text-center">
                                        @if($row->recommendation > 0)
                                            <span class="badge badge-danger">Perlu Restock</span>
                                        @else
                                            <span class="badge badge-success">Aman</span>
                                        @endif
                                    </td>
                                    <td class="text-center d-print-none">
                                        <a href="{{ route('forecast.show', ['product' => $row->product->id] + request()->only('n', 'months', 'end_month')) }}"
                                           class="btn btn-info btn-sm">
                                            <i class="bi bi-graph-up"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center text-muted">Belum ada data produk.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <small class="text-muted d-block mt-2">
                    Rekomendasi restock = Prediksi kebutuhan − Stok saat ini (bila hasilnya negatif, stok dianggap cukup).
                </small>
            </div>
        </div>
    </div>
@endsection
