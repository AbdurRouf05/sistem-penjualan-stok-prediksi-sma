<?php

namespace Tests\Unit;

use Modules\Forecast\Services\SingleMovingAverage;
use PHPUnit\Framework\TestCase;

class SingleMovingAverageTest extends TestCase
{
    public function test_forecast_is_average_of_previous_n_periods()
    {
        $result = SingleMovingAverage::calculate(['Jan' => 10, 'Feb' => 20, 'Mar' => 30, 'Apr' => 40], 3);

        // Tiga periode pertama belum punya prediksi
        $this->assertNull($result['rows'][0]['forecast']);
        $this->assertNull($result['rows'][2]['forecast']);

        // F(Apr) = (10 + 20 + 30) / 3 = 20
        $this->assertEquals(20, $result['rows'][3]['forecast']);
        $this->assertEquals(20, $result['rows'][3]['error']);

        // F(Mei) = (20 + 30 + 40) / 3 = 30
        $this->assertEquals(30, $result['next_forecast']);
    }

    public function test_error_measures()
    {
        // n = 2: F3 = (10 + 20) / 2 = 15 (aktual 20, error 5), F4 = (20 + 20) / 2 = 20 (aktual 10, error -10)
        $result = SingleMovingAverage::calculate([10, 20, 20, 10], 2);

        // MAD = (5 + 10) / 2
        $this->assertEqualsWithDelta(7.5, $result['mad'], 1e-9);
        // MSE = (25 + 100) / 2
        $this->assertEqualsWithDelta(62.5, $result['mse'], 1e-9);
        // MAPE = (25% + 100%) / 2
        $this->assertEqualsWithDelta(62.5, $result['mape'], 1e-9);
    }

    public function test_periods_with_zero_actual_are_skipped_in_mape()
    {
        $result = SingleMovingAverage::calculate([4, 4, 0, 4], 2);

        // Periode ke-3 (aktual 0) tidak bisa dihitung persentase errornya
        $this->assertNull($result['rows'][2]['percent_error']);
        // Periode ke-4: F = 2, error 2 → 50%
        $this->assertEqualsWithDelta(50, $result['mape'], 1e-9);
    }

    public function test_not_enough_data()
    {
        $result = SingleMovingAverage::calculate([5, 6], 3);

        $this->assertNull($result['next_forecast']);
        $this->assertNull($result['mad']);
        $this->assertNull($result['mape']);
    }

    public function test_mape_category()
    {
        $this->assertSame('Sangat Baik', SingleMovingAverage::mapeCategory(5));
        $this->assertSame('Baik', SingleMovingAverage::mapeCategory(15));
        $this->assertSame('Cukup', SingleMovingAverage::mapeCategory(35));
        $this->assertSame('Buruk', SingleMovingAverage::mapeCategory(60));
        $this->assertSame('-', SingleMovingAverage::mapeCategory(null));
    }
}
