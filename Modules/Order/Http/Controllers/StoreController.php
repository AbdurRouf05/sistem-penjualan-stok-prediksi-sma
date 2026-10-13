<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Order\Entities\Order;
use Modules\Order\Services\OrderService;
use Modules\Product\Entities\Category;
use Modules\Product\Entities\Product;

/**
 * Halaman toko online untuk pelanggan (tanpa login).
 */
class StoreController extends Controller
{
    public function index(Request $request) {
        $products = Product::with('category')
            ->when($request->q, fn ($q) => $q->where('product_name', 'like', '%' . $request->q . '%'))
            ->when($request->category, fn ($q) => $q->where('category_id', $request->category))
            ->orderBy('product_name')
            ->paginate(12)
            ->withQueryString();

        return view('order::store.index', [
            'products'   => $products,
            'categories' => Category::orderBy('category_name')->get(),
        ]);
    }

    public function cart() {
        return view('order::store.cart', ['items' => $this->cartItems()]);
    }

    public function addToCart(Request $request, Product $product) {
        $request->validate(['quantity' => 'nullable|integer|min:1']);
        $cart = session('cart', []);
        $qty = ($cart[$product->id] ?? 0) + (int) $request->input('quantity', 1);

        if ($qty > $product->product_quantity) {
            return back()->with('error', "Stok {$product->product_name} hanya tersisa {$product->product_quantity}.");
        }

        $cart[$product->id] = $qty;
        session(['cart' => $cart]);

        return back()->with('success', "{$product->product_name} ditambahkan ke keranjang.");
    }

    public function updateCart(Request $request) {
        $request->validate(['quantities' => 'array', 'quantities.*' => 'integer|min:0']);
        $cart = [];
        $products = Product::whereIn('id', array_keys($request->input('quantities', [])))->get()->keyBy('id');

        foreach ($request->input('quantities', []) as $productId => $qty) {
            if ($qty > 0 && isset($products[$productId])) {
                $cart[$productId] = min((int) $qty, $products[$productId]->product_quantity);
            }
        }
        session(['cart' => $cart]);

        return redirect()->route('store.cart')->with('success', 'Keranjang diperbarui.');
    }

    public function removeFromCart(Product $product) {
        $cart = session('cart', []);
        unset($cart[$product->id]);
        session(['cart' => $cart]);

        return redirect()->route('store.cart');
    }

    public function checkout() {
        $items = $this->cartItems();
        if ($items->isEmpty()) {
            return redirect()->route('store.index')->with('error', 'Keranjang masih kosong.');
        }

        return view('order::store.checkout', ['items' => $items]);
    }

    public function placeOrder(Request $request) {
        $data = $request->validate([
            'customer_name'    => 'required|string|max:100',
            'customer_phone'   => 'required|string|max:20',
            'customer_address' => 'required|string|max:500',
            'delivery_method'  => ['required', Rule::in(Order::DELIVERY_METHODS)],
            'payment_method'   => ['required', Rule::in(Order::PAYMENT_METHODS)],
            'note'             => 'nullable|string|max:500',
        ]);

        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('store.index')->with('error', 'Keranjang masih kosong.');
        }

        $order = OrderService::place($cart, $data);
        session()->forget('cart');

        return redirect()->route('store.order', $order->order_code)
            ->with('success', 'Pesanan berhasil dibuat. Simpan kode pesanan Anda untuk melacak status.');
    }

    public function order(string $code) {
        $order = Order::with('orderDetails')->where('order_code', $code)->firstOrFail();

        return view('order::store.order', ['order' => $order]);
    }

    public function track(Request $request) {
        if ($request->filled('code')) {
            $order = Order::where('order_code', trim($request->code))->first();
            if ($order) {
                return redirect()->route('store.order', $order->order_code);
            }
            session()->flash('error', 'Kode pesanan tidak ditemukan.');
        }

        return view('order::store.track');
    }

    private function cartItems() {
        $cart = session('cart', []);
        $products = Product::whereIn('id', array_keys($cart))->get();

        return $products->map(fn ($product) => (object) [
            'product'   => $product,
            'quantity'  => $cart[$product->id],
            'sub_total' => $product->product_price * $cart[$product->id],
        ]);
    }
}
