<?php

namespace Modules\Forecast\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Mengambil data historis jumlah barang terjual per bulan dari transaksi penjualan
 * berstatus Completed (termasuk pesanan online yang sudah dikonfirmasi).
 */
class SalesHistory
{
    /**
     * Daftar bulan (format Y-m) dari $months bulan terakhir hingga $endMonth.
     */
    public static function periods(Carbon $endMonth, int $months): array
    {
        $periods = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $periods[] = $endMonth->copy()->startOfMonth()->subMonths($i)->format('Y-m');
        }

        return $periods;
    }

    /**
     * @return array [product_id => ['Y-m' => qty, ...]] untuk semua periode (bulan tanpa penjualan bernilai 0)
     */
    public static function monthlyQuantities(array $periods, ?int $productId = null): array
    {
        $start = Carbon::createFromFormat('Y-m-d', $periods[0] . '-01')->startOfMonth()->format('Y-m-d');
        $end = Carbon::createFromFormat('Y-m-d', end($periods) . '-01')->endOfMonth()->format('Y-m-d');

        $rows = DB::table('sale_details')
            ->join('sales', 'sales.id', '=', 'sale_details.sale_id')
            ->where('sales.status', 'Completed')
            ->whereBetween('sales.date', [$start, $end])
            ->whereNotNull('sale_details.product_id')
            ->when($productId, fn ($q) => $q->where('sale_details.product_id', $productId))
            ->get(['sale_details.product_id', 'sales.date', 'sale_details.quantity']);

        $empty = array_fill_keys($periods, 0);
        $result = [];
        foreach ($rows as $row) {
            $period = substr($row->date, 0, 7);
            $result[$row->product_id] ??= $empty;
            $result[$row->product_id][$period] += $row->quantity;
        }

        return $result;
    }

    /**
     * Label bulan dalam bahasa Indonesia, mis. "Okt 2026".
     */
    public static function label(string $period, bool $long = false): string
    {
        return Carbon::createFromFormat('Y-m-d', $period . '-01')->locale('id')
            ->translatedFormat($long ? 'F Y' : 'M Y');
    }
}
