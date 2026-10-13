<?php

namespace Modules\Order\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Modules\Order\Entities\Order;
use Modules\Order\Services\OrderService;

/**
 * Pengelolaan pesanan online oleh admin/kasir.
 */
class OrderController extends Controller
{
    public function index(Request $request) {
        $orders = Order::withCount('orderDetails')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->q, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('order_code', 'like', '%' . $request->q . '%')
                    ->orWhere('customer_name', 'like', '%' . $request->q . '%')
                    ->orWhere('customer_phone', 'like', '%' . $request->q . '%');
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = Order::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('order::admin.index', compact('orders', 'counts'));
    }

    public function show(Order $order) {
        $order->load('orderDetails.product', 'sale');

        return view('order::admin.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order) {
        $data = $request->validate([
            'status'     => ['required', Rule::in(Order::STATUSES)],
            'admin_note' => 'nullable|string|max:500',
        ]);

        OrderService::changeStatus($order, $data['status'], $data['admin_note'] ?? null);

        toast("Status pesanan menjadi {$data['status']}.", 'success');

        return redirect()->route('orders.show', $order);
    }
}
