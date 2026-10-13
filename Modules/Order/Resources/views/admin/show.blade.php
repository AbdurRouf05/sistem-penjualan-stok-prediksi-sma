@extends('layouts.app')

@section('title', 'Pesanan ' . $order->order_code)

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">Pesanan Online</a></li>
        <li class="breadcrumb-item active">{{ $order->order_code }}</li>
    </ol>
@endsection

@php
    use Modules\Order\Entities\Order;
    $actions = [
        Order::STATUS_WAITING    => [Order::STATUS_PROCESSING => ['success', 'Konfirmasi & Proses'], Order::STATUS_CANCELLED => ['danger', 'Tolak Pesanan']],
        Order::STATUS_PROCESSING => [Order::STATUS_SHIPPING => ['primary', 'Tandai Dikirim'], Order::STATUS_DONE => ['success', 'Tandai Selesai'], Order::STATUS_CANCELLED => ['danger', 'Batalkan']],
        Order::STATUS_SHIPPING   => [Order::STATUS_DONE => ['success', 'Tandai Selesai'], Order::STATUS_CANCELLED => ['danger', 'Batalkan']],
    ][$order->status] ?? [];
@endphp

@section('content')
    <div class="container-fluid">
        @if($errors->any())
            <div class="alert alert-danger">@foreach($errors->all() as $error){{ $error }}<br>@endforeach</div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $order->order_code }}</strong>
                            <small class="text-muted ml-2">{{ $order->created_at->format('d/m/Y H:i') }}</small>
                        </div>
                        <span class="badge badge-{{ $order->status_badge }} p-2">{{ $order->status }}</span>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="thead-light">
                                <tr><th>Produk</th><th>Kode</th><th class="text-right">Harga</th><th class="text-center">Jumlah</th><th class="text-center">Stok Saat Ini</th><th class="text-right">Subtotal</th></tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderDetails as $detail)
                                    @php $lowStock = $order->status == Order::STATUS_WAITING && $detail->product && $detail->product->product_quantity < $detail->quantity; @endphp
                                    <tr class="{{ $lowStock ? 'table-danger' : '' }}">
                                        <td>{{ $detail->product_name }}</td>
                                        <td>{{ $detail->product_code }}</td>
                                        <td class="text-right">{{ format_currency($detail->price) }}</td>
                                        <td class="text-center">{{ $detail->quantity }}</td>
                                        <td class="text-center">{{ $detail->product->product_quantity ?? '-' }}</td>
                                        <td class="text-right">{{ format_currency($detail->sub_total) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="font-weight-bold"><td colspan="5" class="text-right">Total</td><td class="text-right">{{ format_currency($order->total_amount) }}</td></tr>
                            </tfoot>
                        </table>
                        @if($order->sale)
                            <p class="small text-muted mt-2 mb-0">
                                Tercatat sebagai penjualan
                                <a href="{{ route('sales.show', $order->sale) }}">{{ $order->sale->reference }}</a>
                                pada {{ $order->confirmed_at?->format('d/m/Y H:i') }}.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header"><strong>Data Pemesan</strong></div>
                    <div class="card-body">
                        <p class="mb-1"><strong>{{ $order->customer_name }}</strong></p>
                        <p class="mb-1"><i class="bi bi-telephone"></i> {{ $order->customer_phone }}</p>
                        <p class="mb-2"><i class="bi bi-geo-alt"></i> {{ $order->customer_address }}</p>
                        <p class="mb-1">Pengambilan: <strong>{{ $order->delivery_method }}</strong></p>
                        <p class="mb-1">Pembayaran: <strong>{{ $order->payment_method }}</strong></p>
                        @if($order->note)<p class="mb-0">Catatan: {{ $order->note }}</p>@endif
                    </div>
                </div>

                @if($actions)
                    <div class="card">
                        <div class="card-header"><strong>Proses Pesanan</strong></div>
                        <div class="card-body">
                            <form action="{{ route('orders.status', $order) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <div class="form-group">
                                    <label class="small">Catatan untuk pelanggan (opsional)</label>
                                    <textarea name="admin_note" rows="2" class="form-control">{{ $order->admin_note }}</textarea>
                                </div>
                                @foreach($actions as $status => [$color, $label])
                                    <button name="status" value="{{ $status }}" class="btn btn-{{ $color }} btn-block"
                                            onclick="return confirm('{{ $label }}?')">{{ $label }}</button>
                                @endforeach
                            </form>
                            @if($order->status == Order::STATUS_WAITING)
                                <small class="text-muted d-block mt-2">Konfirmasi akan mengurangi stok dan mencatat pesanan sebagai penjualan.</small>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
