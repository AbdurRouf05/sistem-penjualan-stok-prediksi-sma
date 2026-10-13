<?php

namespace Modules\Order\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Order\Entities\Order;
use Modules\People\Entities\Customer;
use Modules\Product\Entities\Product;
use Modules\Sale\Entities\Sale;
use Modules\Sale\Entities\SaleDetails;
use Modules\Sale\Entities\SalePayment;

class OrderService
{
    /**
     * Membuat pesanan baru dari isi keranjang [product_id => qty].
     */
    public static function place(array $cart, array $customer): Order {
        return DB::transaction(function () use ($cart, $customer) {
            $products = Product::whereIn('id', array_keys($cart))->lockForUpdate()->get()->keyBy('id');
            self::ensureStockAvailable($cart, $products);

            $order = Order::create([
                'customer_name'    => $customer['customer_name'],
                'customer_phone'   => $customer['customer_phone'],
                'customer_address' => $customer['customer_address'],
                'delivery_method'  => $customer['delivery_method'],
                'payment_method'   => $customer['payment_method'],
                'note'             => $customer['note'] ?? null,
                'total_amount'     => 0,
                'status'           => Order::STATUS_WAITING,
            ]);

            $total = 0;
            foreach ($cart as $productId => $qty) {
                $product = $products[$productId];
                $subTotal = $product->product_price * $qty;
                $total += $subTotal;

                $order->orderDetails()->create([
                    'product_id'   => $product->id,
                    'product_name' => $product->product_name,
                    'product_code' => $product->product_code,
                    'quantity'     => $qty,
                    'price'        => $product->product_price,
                    'sub_total'    => $subTotal,
                ]);
            }

            $order->update(['total_amount' => $total]);

            return $order;
        });
    }

    /**
     * Mengubah status pesanan.
     *  - Menunggu Konfirmasi → Diproses: stok dikurangi dan pesanan dicatat sebagai penjualan.
     *  - Diproses/Dikirim → Dibatalkan: stok dikembalikan dan catatan penjualan dihapus.
     */
    public static function changeStatus(Order $order, string $status, ?string $adminNote = null): void {
        $allowed = [
            Order::STATUS_WAITING    => [Order::STATUS_PROCESSING, Order::STATUS_CANCELLED],
            Order::STATUS_PROCESSING => [Order::STATUS_SHIPPING, Order::STATUS_DONE, Order::STATUS_CANCELLED],
            Order::STATUS_SHIPPING   => [Order::STATUS_DONE, Order::STATUS_CANCELLED],
        ];

        if (!in_array($status, $allowed[$order->status] ?? [])) {
            throw ValidationException::withMessages([
                'status' => "Status pesanan tidak bisa diubah dari {$order->status} menjadi {$status}.",
            ]);
        }

        DB::transaction(function () use ($order, $status, $adminNote) {
            if ($order->status == Order::STATUS_WAITING && $status == Order::STATUS_PROCESSING) {
                self::recordSale($order);
            }

            if ($status == Order::STATUS_CANCELLED && $order->sale_id) {
                self::revertSale($order);
            }

            $order->update([
                'status'     => $status,
                'admin_note' => $adminNote ?? $order->admin_note,
            ]);
        });
    }

    private static function recordSale(Order $order): void {
        $order->load('orderDetails');
        $cart = $order->orderDetails->pluck('quantity', 'product_id')->all();
        $products = Product::whereIn('id', array_keys($cart))->lockForUpdate()->get()->keyBy('id');
        self::ensureStockAvailable($cart, $products);

        $customer = Customer::firstOrCreate(
            ['customer_phone' => $order->customer_phone],
            [
                'customer_name'  => $order->customer_name,
                'customer_email' => '-',
                'city'           => '-',
                'country'        => 'Indonesia',
                'address'        => $order->customer_address,
            ]
        );

        // Nilai uang pada tabel penjualan disimpan dikali 100 (mengikuti modul Sale)
        $sale = Sale::create([
            'date'                => now()->format('Y-m-d'),
            'customer_id'         => $customer->id,
            'customer_name'       => $order->customer_name,
            'tax_percentage'      => 0,
            'tax_amount'          => 0,
            'discount_percentage' => 0,
            'discount_amount'     => 0,
            'shipping_amount'     => 0,
            'total_amount'        => $order->total_amount * 100,
            'paid_amount'         => $order->total_amount * 100,
            'due_amount'          => 0,
            'status'              => 'Completed',
            'payment_status'      => 'Paid',
            'payment_method'      => $order->payment_method,
            'note'                => 'Pesanan online ' . $order->order_code,
        ]);

        foreach ($order->orderDetails as $detail) {
            SaleDetails::create([
                'sale_id'                 => $sale->id,
                'product_id'              => $detail->product_id,
                'product_name'            => $detail->product_name,
                'product_code'            => $detail->product_code,
                'quantity'                => $detail->quantity,
                'price'                   => $detail->price * 100,
                'unit_price'              => $detail->price * 100,
                'sub_total'               => $detail->sub_total * 100,
                'product_discount_amount' => 0,
                'product_discount_type'   => 'fixed',
                'product_tax_amount'      => 0,
            ]);

            $product = $products[$detail->product_id];
            $product->update(['product_quantity' => $product->product_quantity - $detail->quantity]);
        }

        SalePayment::create([
            'date'           => now()->format('Y-m-d'),
            'reference'      => 'INV/' . $sale->reference,
            'amount'         => $order->total_amount,
            'sale_id'        => $sale->id,
            'payment_method' => $order->payment_method,
        ]);

        $order->update([
            'customer_id'  => $customer->id,
            'sale_id'      => $sale->id,
            'confirmed_at' => now(),
        ]);
    }

    private static function revertSale(Order $order): void {
        foreach ($order->orderDetails as $detail) {
            if ($detail->product) {
                $detail->product->increment('product_quantity', $detail->quantity);
            }
        }

        Sale::whereKey($order->sale_id)->delete();
        $order->update(['sale_id' => null]);
    }

    private static function ensureStockAvailable(array $cart, $products): void {
        foreach ($cart as $productId => $qty) {
            $product = $products[$productId] ?? null;
            if (!$product) {
                throw ValidationException::withMessages(['stock' => 'Ada produk yang sudah tidak tersedia.']);
            }
            if ($product->product_quantity < $qty) {
                throw ValidationException::withMessages([
                    'stock' => "Stok {$product->product_name} tidak cukup (tersisa {$product->product_quantity}).",
                ]);
            }
        }
    }
}
