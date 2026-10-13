@extends('order::store.layout')

@section('title', 'Pesanan ' . $order->order_code)

@section('content')
    @php
        $steps = [
            \Modules\Order\Entities\Order::STATUS_WAITING,
            \Modules\Order\Entities\Order::STATUS_PROCESSING,
            \Modules\Order\Entities\Order::STATUS_SHIPPING,
            \Modules\Order\Entities\Order::STATUS_DONE,
        ];
        $current = array_search($order->status, $steps);
    @endphp

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between flex-wrap">
                <div>
                    <small class="text-muted">Kode Pesanan</small>
                    <h2 class="h4 font-weight-bold text-brand mb-0">{{ $order->order_code }}</h2>
                    <small class="text-muted">{{ $order->created_at->locale('id')->translatedFormat('d F Y H:i') }}</small>
                </div>
                <div class="text-md-right mt-2 mt-md-0">
                    <small class="text-muted d-block">Status</small>
                    <span class="badge badge-{{ $order->status_badge }} p-2" style="font-size: 1rem">{{ $order->status }}</span>
                </div>
            </div>

            @if($order->status != \Modules\Order\Entities\Order::STATUS_CANCELLED)
                <div class="progress mt-4" style="height: 8px">
                    <div class="progress-bar" style="width: {{ ($current + 1) / count($steps) * 100 }}%; background: var(--brand)"></div>
                </div>
                <div class="d-flex justify-content-between small text-muted mt-1">
                    @foreach($steps as $i => $step)
                        <span class="{{ $i <= $current ? 'text-brand font-weight-bold' : '' }}">{{ $step }}</span>
                    @endforeach
                </div>
            @endif

            @if($order->admin_note)
                <div class="alert alert-info mt-3 mb-0"><strong>Catatan toko:</strong> {{ $order->admin_note }}</div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-7 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white font-weight-bold">Rincian Barang</div>
                <ul class="list-group list-group-flush">
                    @foreach($order->orderDetails as $detail)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $detail->product_name }} × {{ $detail->quantity }}</span>
                            <span>{{ format_currency($detail->sub_total) }}</span>
                        </li>
                    @endforeach
                    <li class="list-group-item d-flex justify-content-between font-weight-bold">
                        <span>Total</span><span class="price">{{ format_currency($order->total_amount) }}</span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white font-weight-bold">Data Pemesan</div>
                <div class="card-body small">
                    <p class="mb-1"><strong>{{ $order->customer_name }}</strong> · {{ $order->customer_phone }}</p>
                    <p class="mb-2">{{ $order->customer_address }}</p>
                    <p class="mb-1">Pengambilan: {{ $order->delivery_method }}</p>
                    <p class="mb-0">Pembayaran: {{ $order->payment_method }}</p>
                    @if($order->note)<p class="mt-2 mb-0">Catatan: {{ $order->note }}</p>@endif
                </div>
            </div>
        </div>
    </div>
@endsection
