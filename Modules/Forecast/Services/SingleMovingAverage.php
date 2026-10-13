<?php

namespace Modules\Forecast\Services;

/**
 * Perhitungan peramalan dengan metode Single Moving Average (SMA).
 *
 *   F(t+1) = (X(t) + X(t-1) + ... + X(t-n+1)) / n
 *
 * Keterangan:
 *   F(t+1) = nilai prediksi untuk periode berikutnya
 *   X(t)   = data aktual (jumlah terjual) pada periode t
 *   n      = jumlah periode rata-rata bergerak (ordo)
 *
 * Akurasi diukur dengan:
 *   MAD  = Σ|X - F| / jumlah data
 *   MSE  = Σ(X - F)² / jumlah data
 *   MAPE = Σ(|X - F| / X × 100%) / jumlah data   (periode dengan X = 0 dilewati)
 */
class SingleMovingAverage
{
    /**
     * @param array $actuals data aktual berurutan, ['label' => jumlah]
     * @param int   $n       jumlah periode rata-rata bergerak
     * @return array rows, next_forecast, mad, mse, mape
     */
    public static function calculate(array $actuals, int $n): array
    {
        $labels = array_keys($actuals);
        $values = array_values($actuals);
        $count = count($values);

        $rows = [];
        $absErrors = [];
        $squaredErrors = [];
        $percentErrors = [];

        for ($t = 0; $t < $count; $t++) {
            $row = [
                'period'         => $labels[$t],
                'actual'         => $values[$t],
                'forecast'       => null,
                'error'          => null,
                'abs_error'      => null,
                'squared_error'  => null,
                'percent_error'  => null,
            ];

            // Prediksi baru bisa dihitung jika sudah ada n data sebelumnya
            if ($t >= $n) {
                $forecast = array_sum(array_slice($values, $t - $n, $n)) / $n;
                $error = $values[$t] - $forecast;

                $row['forecast'] = $forecast;
                $row['error'] = $error;
                $row['abs_error'] = abs($error);
                $row['squared_error'] = $error ** 2;

                $absErrors[] = abs($error);
                $squaredErrors[] = $error ** 2;

                if ($values[$t] != 0) {
                    $row['percent_error'] = abs($error) / $values[$t] * 100;
                    $percentErrors[] = $row['percent_error'];
                }
            }

            $rows[] = $row;
        }

        $nextForecast = $count >= $n
            ? array_sum(array_slice($values, $count - $n, $n)) / $n
            : null;

        return [
            'rows'          => $rows,
            'next_forecast' => $nextForecast,
            'mad'           => self::average($absErrors),
            'mse'           => self::average($squaredErrors),
            'mape'          => self::average($percentErrors),
        ];
    }

    /**
     * Kategori akurasi MAPE menurut Lewis (1982).
     */
    public static function mapeCategory(?float $mape): string
    {
        if ($mape === null) {
            return '-';
        }
        if ($mape < 10) {
            return 'Sangat Baik';
        }
        if ($mape < 20) {
            return 'Baik';
        }
        if ($mape < 50) {
            return 'Cukup';
        }

        return 'Buruk';
    }

    private static function average(array $values): ?float
    {
        return count($values) ? array_sum($values) / count($values) : null;
    }
}
