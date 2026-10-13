@extends('layouts.app')

@section('title', 'Prediksi Stok ' . $product->product_name)

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ route('forecast.index', request()->only('n', 'months', 'end_month')) }}">Prediksi Stok</a></li>
        <li class="breadcrumb-item active">{{ $product->product_name }}</li>
    </ol>
@endsection

@php
    $num = fn ($value, $decimals = 2) => $value === null ? '-' : number_format($value, $decimals, ',', '.');
@endphp

@section('content')
    <div class="container-fluid">
        @include('forecast::partials.params', ['action' => route('forecast.show', $product)])

        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><strong>{{ $product->product_name }}</strong> <small class="text-muted">({{ $product->product_code }})</small></div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td>Kategori</td><td class="text-right">{{ $product->category->category_name ?? '-' }}</td></tr>
                            <tr><td>Stok saat ini</td><td class="text-right">{{ $product->product_quantity }} {{ $product->product_unit }}</td></tr>
                            <tr><td>Stok minimum</td><td class="text-right">{{ $product->product_stock_alert }} {{ $product->product_unit }}</td></tr>
                            <tr class="border-top">
                                <td>Prediksi {{ $targetPeriod }}<br><small class="text-muted">{{ $num($sma['next_forecast']) }} dibulatkan ke atas</small></td>
                                <td class="text-right h5 text-primary">{{ $forecast }} {{ $product->product_unit }}</td>
                            </tr>
                            <tr>
                                <td>Rekomendasi restock</td>
                                <td class="text-right h5 {{ $recommendation > 0 ? 'text-danger' : 'text-success' }}">{{ $recommendation }} {{ $product->product_unit }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><strong>Akurasi Peramalan (n = {{ $params['n'] }})</strong></div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr><td>MAD</td><td class="text-right">{{ $num($sma['mad']) }}</td></tr>
                            <tr><td>MSE</td><td class="text-right">{{ $num($sma['mse']) }}</td></tr>
                            <tr><td>MAPE</td><td class="text-right">{{ $sma['mape'] === null ? '-' : $num($sma['mape']) . '%' }}</td></tr>
                            <tr>
                                <td>Kategori MAPE</td>
                                <td class="text-right"><span class="badge badge-info">{{ \Modules\Forecast\Services\SingleMovingAverage::mapeCategory($sma['mape']) }}</span></td>
                            </tr>
                        </table>
                        <small class="text-muted">MAPE &lt;10% sangat baik, 10–20% baik, 20–50% cukup, &gt;50% buruk (Lewis, 1982).</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header"><strong>Grafik Data Aktual vs Prediksi</strong></div>
                    <div class="card-body">
                        <canvas id="forecastChart" height="140"></canvas>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><strong>Perbandingan Nilai n</strong></div>
                    <div class="card-body">
                        <table class="table table-bordered table-sm mb-0 text-center">
                            <thead class="thead-light">
                                <tr><th>n</th><th>Prediksi {{ $targetPeriod }}</th><th>MAD</th><th>MSE</th><th>MAPE</th></tr>
                            </thead>
                            <tbody>
                                @foreach($comparison as $n => $row)
                                    <tr class="{{ $n == $bestN ? 'table-success font-weight-bold' : '' }}">
                                        <td>{{ $n }} {{ $n == $bestN ? '(terbaik)' : '' }}</td>
                                        <td>{{ $num($row['forecast']) }}</td>
                                        <td>{{ $num($row['mad']) }}</td>
                                        <td>{{ $num($row['mse']) }}</td>
                                        <td>{{ $row['mape'] === null ? '-' : $num($row['mape']) . '%' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <small class="text-muted">Nilai n terbaik adalah yang menghasilkan MAPE terkecil.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Tabel Perhitungan Single Moving Average (n = {{ $params['n'] }})</strong>
                <button onclick="window.print()" class="btn btn-sm btn-outline-secondary d-print-none">
                    <i class="bi bi-printer"></i> Cetak
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm text-center mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>t</th>
                                <th>Periode</th>
                                <th>Aktual (X)</th>
                                <th>Prediksi (F)</th>
                                <th>Error (X − F)</th>
                                <th>|X − F|</th>
                                <th>(X − F)²</th>
                                <th>|X − F| / X × 100%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sma['rows'] as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row['period'] }}</td>
                                    <td>{{ $row['actual'] }}</td>
                                    <td>{{ $num($row['forecast']) }}</td>
                                    <td>{{ $num($row['error']) }}</td>
                                    <td>{{ $num($row['abs_error']) }}</td>
                                    <td>{{ $num($row['squared_error']) }}</td>
                                    <td>{{ $row['percent_error'] === null ? '-' : $num($row['percent_error']) . '%' }}</td>
                                </tr>
                            @endforeach
                            <tr class="table-primary font-weight-bold">
                                <td>{{ count($sma['rows']) + 1 }}</td>
                                <td>{{ $targetPeriod }}</td>
                                <td>?</td>
                                <td>{{ $num($sma['next_forecast']) }}</td>
                                <td colspan="4" class="text-left">← prediksi periode berikutnya</td>
                            </tr>
                        </tbody>
                        <tfoot class="font-weight-bold">
                            <tr>
                                <td colspan="5" class="text-right">Rata-rata (MAD / MSE / MAPE)</td>
                                <td>{{ $num($sma['mad']) }}</td>
                                <td>{{ $num($sma['mse']) }}</td>
                                <td>{{ $sma['mape'] === null ? '-' : $num($sma['mape']) . '%' }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <small class="text-muted d-block mt-2">
                    Rumus: F<sub>t+1</sub> = (X<sub>t</sub> + X<sub>t−1</sub> + … + X<sub>t−n+1</sub>) / n.
                    Prediksi baru tersedia mulai periode ke-{{ $params['n'] + 1 }} karena membutuhkan {{ $params['n'] }} data sebelumnya.
                </small>
            </div>
        </div>
    </div>
@endsection

@section('third_party_scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.5.0/chart.min.js"
            integrity="sha512-asxKqQghC1oBShyhiBwA+YgotaSYKxGP1rcSYTDrB0U6DxwlJjU59B67U8+5/++uFjcuVM8Hh5cokLjZlhm3Vg=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
@endsection

@push('page_scripts')
    <script>
        const chartData = @json($chart);
        new Chart(document.getElementById('forecastChart'), {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    {label: 'Aktual', data: chartData.actual, borderColor: '#2563EB', backgroundColor: '#2563EB', tension: 0},
                    {label: 'Prediksi SMA', data: chartData.forecast, borderColor: '#F59E0B', backgroundColor: '#F59E0B', borderDash: [6, 4], tension: 0}
                ]
            },
            options: {scales: {y: {beginAtZero: true}}}
        });
    </script>
@endpush
