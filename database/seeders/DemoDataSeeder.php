<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Order\Entities\Order;
use Modules\Order\Services\OrderService;

/**
 * Data contoh Toko Aneka Camilan Dua Putra: kategori, produk, pelanggan, supplier,
 * riwayat pembelian & penjualan 18 bulan terakhir (untuk perhitungan Single Moving Average),
 * dan beberapa pesanan online.
 *
 * Data dibangkitkan dengan seed acak tetap sehingga hasilnya selalu sama.
 * Ganti/hapus seeder ini bila sudah memakai data asli toko.
 */
class DemoDataSeeder extends Seeder
{
    private const CATEGORIES = [
        'KRP' => 'Keripik',
        'KCG' => 'Kacang-kacangan',
        'KUE' => 'Kue Kering',
        'TRD' => 'Camilan Tradisional',
    ];

    // kode, nama, kategori, satuan, harga beli, harga jual, rata-rata terjual per bulan, tren per bulan
    private const PRODUCTS = [
        ['KRP-001', 'Keripik Singkong Balado 250 g', 'KRP', 'Bks', 9000, 12000, 120, 0.00],
        ['KRP-002', 'Keripik Pisang Manis 250 g', 'KRP', 'Bks', 11000, 15000, 90, 0.00],
        ['KRP-003', 'Keripik Kentang Original 200 g', 'KRP', 'Bks', 14000, 18000, 60, -0.01],
        ['KRP-004', 'Keripik Tempe 200 g', 'KRP', 'Bks', 10000, 14000, 75, 0.00],
        ['KRP-005', 'Makaroni Pedas 250 g', 'KRP', 'Bks', 7000, 10000, 150, 0.01],
        ['KCG-001', 'Kacang Atom 250 g', 'KCG', 'Bks', 9000, 12500, 80, 0.00],
        ['KCG-002', 'Kacang Bawang 250 g', 'KCG', 'Bks', 12000, 16000, 70, 0.00],
        ['KCG-003', 'Kacang Mete Goreng 250 g', 'KCG', 'Bks', 45000, 55000, 25, 0.00],
        ['KUE-001', 'Nastar Keju 500 g', 'KUE', 'Tpl', 55000, 70000, 15, 0.00],
        ['KUE-002', 'Kastengel 500 g', 'KUE', 'Tpl', 60000, 75000, 12, 0.00],
        ['KUE-003', 'Putri Salju 500 g', 'KUE', 'Tpl', 45000, 60000, 12, 0.00],
        ['TRD-001', 'Rengginang Mentah 500 g', 'TRD', 'Bks', 12000, 16000, 40, 0.00],
        ['TRD-002', 'Emping Melinjo 250 g', 'TRD', 'Bks', 20000, 26000, 35, 0.00],
        ['TRD-003', 'Basreng Pedas 200 g', 'TRD', 'Bks', 8000, 12000, 140, 0.03],
        ['TRD-004', 'Kerupuk Bawang 500 g', 'TRD', 'Bks', 10000, 14000, 55, 0.00],
    ];

    private const SUPPLIERS = [
        'KRP' => 'CV Sumber Rasa Snack',
        'KCG' => 'UD Kacang Makmur',
        'KUE' => 'Rumah Kue Bu Sari',
        'TRD' => 'UD Kerupuk Jaya',
    ];

    private const CUSTOMERS = [
        ['Pelanggan Umum', '-'],
        ['Siti Aminah', '081234500001'],
        ['Budi Santoso', '081234500002'],
        ['Warung Bu Tini', '081234500003'],
        ['Rina Wulandari', '081234500004'],
        ['Agus Prasetyo', '081234500005'],
        ['Toko Berkah Jaya', '081234500006'],
        ['Dewi Lestari', '081234500007'],
    ];

    public function run() {
        mt_srand(2026);
        $now = now();

        DB::transaction(function () use ($now) {
            $categoryIds = [];
            foreach (self::CATEGORIES as $code => $name) {
                $categoryIds[$code] = DB::table('categories')->insertGetId([
                    'category_code' => $code, 'category_name' => $name, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }

            $supplierIds = [];
            foreach (self::SUPPLIERS as $code => $name) {
                $supplierIds[$code] = DB::table('suppliers')->insertGetId([
                    'supplier_name' => $name, 'supplier_email' => '-', 'supplier_phone' => '0821' . mt_rand(10000000, 99999999),
                    'city' => '-', 'country' => 'Indonesia', 'address' => '-', 'created_at' => $now, 'updated_at' => $now,
                ]);
            }

            $customers = [];
            foreach (self::CUSTOMERS as [$name, $phone]) {
                $customers[DB::table('customers')->insertGetId([
                    'customer_name' => $name, 'customer_email' => '-', 'customer_phone' => $phone,
                    'city' => '-', 'country' => 'Indonesia', 'address' => '-', 'created_at' => $now, 'updated_at' => $now,
                ])] = $name;
            }

            $products = [];
            foreach (self::PRODUCTS as [$code, $name, $category, $unit, $cost, $price, $base, $trend]) {
                $id = DB::table('products')->insertGetId([
                    'category_id' => $categoryIds[$category], 'product_name' => $name, 'product_code' => $code,
                    'product_barcode_symbology' => 'C128', 'product_quantity' => 0,
                    'product_cost' => $cost * 100, 'product_price' => $price * 100, 'product_unit' => $unit,
                    'product_stock_alert' => (int) round($base * 0.25), 'product_order_tax' => 0, 'product_tax_type' => 1,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                $products[] = compact('id', 'code', 'name', 'category', 'cost', 'price', 'base', 'trend');
            }

            $this->seedHistory($products, $customers, $supplierIds, $now);
        });

        $this->seedOnlineOrders();
    }

    /**
     * Riwayat pembelian dan penjualan 18 bulan terakhir hingga kemarin.
     */
    private function seedHistory(array $products, array $customers, array $supplierIds, Carbon $now) {
        $start = $now->copy()->startOfMonth()->subMonths(18);
        $yesterday = $now->copy()->subDay();
        $stocks = array_fill_keys(array_column($products, 'id'), 0);

        for ($month = $start->copy(), $m = 0; $month->lte($yesterday); $month->addMonth(), $m++) {
            $lastDay = $month->isSameMonth($yesterday) ? $yesterday->day : $month->daysInMonth;
            $monthShare = $lastDay / $month->daysInMonth;

            // Banyaknya barang terjual per produk bulan ini
            $demand = [];
            foreach ($products as $p) {
                $qty = $p['base'] * (1 + $p['trend'] * $m) * $this->seasonality($month, $p['category'])
                    * (mt_rand(88, 112) / 100) * $monthShare;
                $demand[$p['id']] = max(0, (int) round($qty));
            }

            // Pembelian ke supplier di awal bulan, cukup untuk kebutuhan bulan ini
            foreach (self::SUPPLIERS as $category => $supplierName) {
                $items = [];
                foreach ($products as $p) {
                    if ($p['category'] == $category) {
                        $need = $demand[$p['id']] - $stocks[$p['id']] + (int) round($p['base'] * 0.3);
                        if ($need > 0) {
                            $items[] = [$p, (int) ceil($need / 10) * 10];
                        }
                    }
                }
                if ($items) {
                    $this->insertPurchase($month->copy()->day(mt_rand(1, 3)), $supplierIds[$category], $supplierName, $items);
                    foreach ($items as [$p, $qty]) {
                        $stocks[$p['id']] += $qty;
                    }
                }
            }

            // Penjualan: kebutuhan per produk dipecah ke beberapa transaksi harian
            $transactions = [];
            foreach ($products as $p) {
                $remaining = $demand[$p['id']];
                while ($remaining > 0) {
                    $qty = min($remaining, mt_rand(1, max(1, (int) ceil($p['base'] / 12))));
                    $key = mt_rand(1, $lastDay) . '-' . mt_rand(0, 2);
                    $transactions[$key][$p['id']] = ($transactions[$key][$p['id']] ?? 0) + $qty;
                    $remaining -= $qty;
                }
            }
            ksort($transactions, SORT_NATURAL);

            $productsById = array_column($products, null, 'id');
            foreach ($transactions as $key => $items) {
                $date = $month->copy()->day((int) explode('-', $key)[0]);
                $customerId = array_keys($customers)[mt_rand(0, count($customers) - 1)];
                $this->insertSale($date, $customerId, $customers[$customerId], $items, $productsById);
                foreach ($items as $productId => $qty) {
                    $stocks[$productId] -= $qty;
                }
            }
        }

        // Stok akhir: sisa stok dibuat bervariasi agar sebagian produk perlu restock
        foreach ($products as $p) {
            $stock = max(0, $stocks[$p['id']]) + (int) round($p['base'] * mt_rand(10, 90) / 100);
            DB::table('products')->where('id', $p['id'])->update(['product_quantity' => $stock]);
        }
    }

    /**
     * Pengali musiman: penjualan naik saat Lebaran (Maret 2025 & Maret 2026) dan akhir tahun.
     */
    private function seasonality(Carbon $month, string $category): float {
        $ym = $month->format('Y-m');
        $lebaran = in_array($ym, ['2025-03', '2026-03']);
        $beforeLebaran = in_array($ym, ['2025-02', '2026-02']);
        $yearEnd = $month->month == 12;

        if ($category == 'KUE') {
            return $lebaran ? 3.5 : ($beforeLebaran ? 1.6 : ($yearEnd ? 1.4 : 1.0));
        }
        if ($category == 'KCG') {
            return $lebaran ? 2.0 : ($beforeLebaran ? 1.3 : ($yearEnd ? 1.2 : 1.0));
        }

        return $lebaran ? 1.4 : ($yearEnd ? 1.15 : 1.0);
    }

    private function insertPurchase(Carbon $date, int $supplierId, string $supplierName, array $items) {
        $total = array_sum(array_map(fn ($item) => $item[0]['cost'] * $item[1], $items));
        $id = DB::table('purchases')->insertGetId([
            'date' => $date->format('Y-m-d'), 'reference' => 'TMP', 'supplier_id' => $supplierId, 'supplier_name' => $supplierName,
            'total_amount' => $total * 100, 'paid_amount' => $total * 100, 'due_amount' => 0,
            'status' => 'Completed', 'payment_status' => 'Paid', 'payment_method' => 'Bank Transfer',
            'created_at' => $date, 'updated_at' => $date,
        ]);
        $reference = make_reference_id('PR', $id);
        DB::table('purchases')->where('id', $id)->update(['reference' => $reference]);

        DB::table('purchase_details')->insert(array_map(fn ($item) => [
            'purchase_id' => $id, 'product_id' => $item[0]['id'], 'product_name' => $item[0]['name'],
            'product_code' => $item[0]['code'], 'quantity' => $item[1], 'price' => $item[0]['cost'] * 100,
            'unit_price' => $item[0]['cost'] * 100, 'sub_total' => $item[0]['cost'] * $item[1] * 100,
            'product_discount_amount' => 0, 'product_tax_amount' => 0, 'created_at' => $date, 'updated_at' => $date,
        ], $items));

        DB::table('purchase_payments')->insert([
            'purchase_id' => $id, 'amount' => $total * 100, 'date' => $date->format('Y-m-d'),
            'reference' => 'INV/' . $reference, 'payment_method' => 'Bank Transfer', 'created_at' => $date, 'updated_at' => $date,
        ]);
    }

    private function insertSale(Carbon $date, int $customerId, string $customerName, array $items, array $productsById) {
        $total = 0;
        foreach ($items as $productId => $qty) {
            $total += $productsById[$productId]['price'] * $qty;
        }

        $id = DB::table('sales')->insertGetId([
            'date' => $date->format('Y-m-d'), 'reference' => 'TMP', 'customer_id' => $customerId, 'customer_name' => $customerName,
            'total_amount' => $total * 100, 'paid_amount' => $total * 100, 'due_amount' => 0,
            'status' => 'Completed', 'payment_status' => 'Paid', 'payment_method' => 'Cash',
            'created_at' => $date, 'updated_at' => $date,
        ]);
        $reference = make_reference_id('SL', $id);
        DB::table('sales')->where('id', $id)->update(['reference' => $reference]);

        $details = [];
        foreach ($items as $productId => $qty) {
            $p = $productsById[$productId];
            $details[] = [
                'sale_id' => $id, 'product_id' => $productId, 'product_name' => $p['name'], 'product_code' => $p['code'],
                'quantity' => $qty, 'price' => $p['price'] * 100, 'unit_price' => $p['price'] * 100,
                'sub_total' => $p['price'] * $qty * 100, 'product_discount_amount' => 0, 'product_tax_amount' => 0,
                'created_at' => $date, 'updated_at' => $date,
            ];
        }
        DB::table('sale_details')->insert($details);

        DB::table('sale_payments')->insert([
            'sale_id' => $id, 'amount' => $total * 100, 'date' => $date->format('Y-m-d'),
            'reference' => 'INV/' . $reference, 'payment_method' => 'Cash', 'created_at' => $date, 'updated_at' => $date,
        ]);
    }

    /**
     * Contoh pesanan online dengan berbagai status.
     */
    private function seedOnlineOrders() {
        $ids = DB::table('products')->pluck('id', 'product_code');
        $orders = [
            [['KRP-001' => 3, 'TRD-003' => 2], 'Rina Wulandari', '081234500004', Order::STATUS_DONE],
            [['KUE-001' => 1, 'KUE-002' => 1], 'Dewi Lestari', '081234500007', Order::STATUS_DONE],
            [['KCG-001' => 5, 'KCG-002' => 5, 'KRP-005' => 10], 'Toko Berkah Jaya', '081234500006', Order::STATUS_SHIPPING],
            [['TRD-002' => 2], 'Agus Prasetyo', '081234500005', Order::STATUS_CANCELLED],
            [['KRP-002' => 2, 'KRP-004' => 2], 'Siti Aminah', '081234500001', Order::STATUS_WAITING],
            [['TRD-003' => 4, 'KRP-005' => 4], 'Fajar Nugroho', '085712340001', Order::STATUS_WAITING],
            [['KUE-003' => 2], 'Lina Marlina', '085712340002', Order::STATUS_WAITING],
        ];

        foreach ($orders as [$items, $name, $phone, $status]) {
            $cart = [];
            foreach ($items as $code => $qty) {
                $cart[$ids[$code]] = $qty;
            }
            $order = OrderService::place($cart, [
                'customer_name'    => $name,
                'customer_phone'   => $phone,
                'customer_address' => 'Jl. Melati No. ' . mt_rand(1, 99),
                'delivery_method'  => Order::DELIVERY_METHODS[mt_rand(0, 1)],
                'payment_method'   => Order::PAYMENT_METHODS[mt_rand(0, 1)],
            ]);

            $path = [
                Order::STATUS_WAITING   => [],
                Order::STATUS_CANCELLED => [Order::STATUS_CANCELLED],
                Order::STATUS_SHIPPING  => [Order::STATUS_PROCESSING, Order::STATUS_SHIPPING],
                Order::STATUS_DONE      => [Order::STATUS_PROCESSING, Order::STATUS_DONE],
            ][$status];
            foreach ($path as $next) {
                OrderService::changeStatus($order->fresh(), $next);
            }
        }
    }
}
