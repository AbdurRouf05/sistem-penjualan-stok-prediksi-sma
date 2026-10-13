<?php

namespace Modules\Forecast\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Modules\Forecast\Services\SalesHistory;
use Modules\Forecast\Services\SingleMovingAverage;
use Modules\Product\Entities\Category;
use Modules\Product\Entities\Product;

class ForecastController extends Controller
{
    /**
     * Ringkasan prediksi kebutuhan stok bulan berikutnya untuk semua produk.
     */
    public function index(Request $request) {
        $params = $this->params($request);
        $periods = SalesHistory::periods($params['end_month'], $params['months']);
        $history = SalesHistory::monthlyQuantities($periods);
        $emptySeries = array_fill_keys($periods, 0);

        $products = Product::with('category')
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->orderBy('product_name')
            ->get();

        $results = $products->map(function ($product) use ($history, $emptySeries, $params) {
            $series = $history[$product->id] ?? $emptySeries;
            $sma = SingleMovingAverage::calculate($series, $params['n']);
            $forecast = (int) ceil($sma['next_forecast'] ?? 0);

            return (object) [
                'product'        => $product,
                'last_actual'    => end($series),
                'total_sold'     => array_sum($series),
                'forecast'       => $forecast,
                'mape'           => $sma['mape'],
                'stock'          => $product->product_quantity,
                'recommendation' => max(0, $forecast - $product->product_quantity),
            ];
        });

        return view('forecast::index', [
            'params'       => $params,
            'results'      => $results,
            'categories'   => Category::orderBy('category_name')->get(),
            'targetPeriod' => SalesHistory::label($params['end_month']->copy()->addMonthNoOverflow()->format('Y-m'), true),
            'firstPeriod'  => SalesHistory::label($periods[0], true),
            'lastPeriod'   => SalesHistory::label(end($periods), true),
        ]);
    }

    /**
     * Detail perhitungan SMA untuk satu produk.
     */
    public function show(Request $request, Product $product) {
        $params = $this->params($request);
        $periods = SalesHistory::periods($params['end_month'], $params['months']);
        $series = SalesHistory::monthlyQuantities($periods, $product->id)[$product->id]
            ?? array_fill_keys($periods, 0);

        $labels = array_map(fn ($p) => SalesHistory::label($p), $periods);
        $labelled = array_combine($labels, array_values($series));

        $sma = SingleMovingAverage::calculate($labelled, $params['n']);
        $forecast = (int) ceil($sma['next_forecast'] ?? 0);
        $targetPeriod = SalesHistory::label($params['end_month']->copy()->addMonthNoOverflow()->format('Y-m'), true);

        // Perbandingan akurasi untuk beberapa nilai n, membantu memilih n terbaik
        $comparison = [];
        foreach (range(2, 6) as $n) {
            if ($n >= count($periods)) {
                break;
            }
            $result = SingleMovingAverage::calculate($labelled, $n);
            $comparison[$n] = [
                'forecast' => $result['next_forecast'],
                'mad'      => $result['mad'],
                'mse'      => $result['mse'],
                'mape'     => $result['mape'],
            ];
        }
        $bestN = null;
        foreach ($comparison as $n => $result) {
            if ($result['mape'] !== null && ($bestN === null || $result['mape'] < $comparison[$bestN]['mape'])) {
                $bestN = $n;
            }
        }

        return view('forecast::show', [
            'product'      => $product,
            'params'       => $params,
            'sma'          => $sma,
            'forecast'     => $forecast,
            'targetPeriod' => $targetPeriod,
            'comparison'   => $comparison,
            'bestN'        => $bestN,
            'recommendation' => max(0, $forecast - $product->product_quantity),
            'chart'        => [
                'labels'   => array_merge($labels, [SalesHistory::label($params['end_month']->copy()->addMonthNoOverflow()->format('Y-m'))]),
                'actual'   => array_merge(array_values($series), [null]),
                'forecast' => array_merge(array_column($sma['rows'], 'forecast'), [$sma['next_forecast']]),
            ],
        ]);
    }

    /**
     * Parameter perhitungan: n (periode rata-rata), jumlah bulan data, dan bulan terakhir data.
     * Bawaan: data 12 bulan sampai bulan lalu (bulan lengkap terakhir), n = 3.
     */
    private function params(Request $request): array {
        $validated = $request->validate([
            'n'         => 'nullable|integer|min:1|max:12',
            'months'    => 'nullable|integer|min:3|max:36',
            'end_month' => 'nullable|date_format:Y-m',
        ]);

        $n = (int) ($validated['n'] ?? config('forecast.default_n', 3));
        $months = (int) ($validated['months'] ?? config('forecast.default_months', 12));
        $endMonth = isset($validated['end_month'])
            ? Carbon::createFromFormat('Y-m-d', $validated['end_month'] . '-01')->startOfMonth()
            : Carbon::now()->startOfMonth()->subMonth();

        // n harus lebih kecil dari jumlah data agar ada periode yang bisa diuji
        $n = min($n, $months - 1);

        return ['n' => $n, 'months' => $months, 'end_month' => $endMonth];
    }
}
