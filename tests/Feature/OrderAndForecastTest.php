<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SuperUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Currency\Database\Seeders\CurrencyDatabaseSeeder;
use Modules\Order\Entities\Order;
use Modules\Product\Entities\Category;
use Modules\Product\Entities\Product;
use Modules\Sale\Entities\Sale;
use Modules\Setting\Database\Seeders\SettingDatabaseSeeder;
use Modules\User\Database\Seeders\PermissionsTableSeeder;
use Tests\TestCase;

class OrderAndForecastTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionsTableSeeder::class, SuperUserSeeder::class, CurrencyDatabaseSeeder::class, SettingDatabaseSeeder::class]);
        cache()->forget('settings');

        $category = Category::create(['category_code' => 'KRP', 'category_name' => 'Keripik']);
        $this->product = Product::create([
            'category_id' => $category->id, 'product_name' => 'Keripik Uji', 'product_code' => 'UJI-1',
            'product_barcode_symbology' => 'C128', 'product_quantity' => 10, 'product_cost' => 8000,
            'product_price' => 12000, 'product_unit' => 'Bks', 'product_stock_alert' => 2,
            'product_order_tax' => 0, 'product_tax_type' => 1,
        ]);
    }

    private function placeOrder(int $qty): Order
    {
        $this->post(route('store.cart.add', $this->product), ['quantity' => $qty])->assertRedirect();
        $this->post(route('store.checkout.store'), [
            'customer_name' => 'Pembeli', 'customer_phone' => '0811111111', 'customer_address' => 'Jl. Uji',
            'delivery_method' => Order::DELIVERY_METHODS[0], 'payment_method' => Order::PAYMENT_METHODS[0],
        ])->assertRedirect();

        return Order::latest('id')->firstOrFail();
    }

    public function test_customer_can_place_order_and_admin_confirmation_reduces_stock()
    {
        $order = $this->placeOrder(3);
        $this->assertSame(Order::STATUS_WAITING, $order->status);
        $this->assertEquals(36000, $order->total_amount);
        $this->get(route('store.order', $order->order_code))->assertOk()->assertSee($order->order_code);

        $admin = User::where('email', 'admin@duaputra.test')->first();
        $this->actingAs($admin)
            ->patch(route('orders.status', $order), ['status' => Order::STATUS_PROCESSING])
            ->assertRedirect(route('orders.show', $order));

        $order->refresh();
        $this->assertSame(Order::STATUS_PROCESSING, $order->status);
        $this->assertEquals(7, $this->product->fresh()->product_quantity);
        $this->assertEquals(36000, Sale::findOrFail($order->sale_id)->total_amount);

        // Pembatalan mengembalikan stok dan menghapus penjualan
        $this->actingAs($admin)->patch(route('orders.status', $order), ['status' => Order::STATUS_CANCELLED]);
        $this->assertEquals(10, $this->product->fresh()->product_quantity);
        $this->assertSame(0, Sale::count());
    }

    public function test_cannot_add_more_than_stock_to_cart()
    {
        $this->post(route('store.cart.add', $this->product), ['quantity' => 11])->assertSessionHas('error');
        $this->assertEmpty(session('cart', []));
    }

    public function test_invalid_status_transition_is_rejected()
    {
        $order = $this->placeOrder(1);
        $admin = User::where('email', 'admin@duaputra.test')->first();

        $this->actingAs($admin)
            ->patch(route('orders.status', $order), ['status' => Order::STATUS_DONE])
            ->assertSessionHasErrors('status');
        $this->assertSame(Order::STATUS_WAITING, $order->fresh()->status);
    }

    public function test_forecast_pages_and_access_control()
    {
        $admin = User::where('email', 'admin@duaputra.test')->first();
        $cashier = User::where('email', 'kasir@duaputra.test')->first();

        $this->actingAs($admin)->get(route('forecast.index'))->assertOk()->assertSee('Keripik Uji');
        $this->actingAs($admin)->get(route('forecast.show', $this->product))->assertOk()->assertSee('MAPE');

        $this->actingAs($cashier)->get(route('forecast.index'))->assertForbidden();
        $this->actingAs($cashier)->get(route('orders.index'))->assertOk();
    }
}
