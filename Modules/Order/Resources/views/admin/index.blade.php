@extends('layouts.app')

@section('title', 'Pesanan Online')

@section('breadcrumb')
    <ol class="breadcrumb border-0 m-0">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
        <li class="breadcrumb-item active">Pesanan Online</li>
    </ol>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-pills mb-3">
                    <li class="nav-item">
                        <a class="nav-link {{ !request('status') ? 'active' : '' }}" href="{{ route('orders.index') }}">
                            Semua <span class="badge badge-light">{{ $counts->sum() }}</span>
                        </a>
                    </li>
                    @foreach(\Modules\Order\Entities\Order::STATUSES as $status)
                        <li class="nav-item">
                            <a class="nav-link {{ request('status') == $status ? 'active' : '' }}" href="{{ route('orders.index', ['status' => $status]) }}">
                                {{ $status }} <span class="badge badge-light">{{ $counts[$status] ?? 0 }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                <form method="GET" class="form-inline mb-3">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control mr-2" placeholder="Cari kode / nama / HP">
                    <button class="btn btn-primary"><i class="bi bi-search"></i> Cari</button>
                    <a href="{{ route('store.index') }}" target="_blank" class="btn btn-outline-secondary ml-auto">
                        <i class="bi bi-shop"></i> Buka Toko Online
                    </a>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Kode</th>
                                <th>Tanggal</th>
                                <th>Pelanggan</th>
                                <th>No. HP</th>
                                <th class="text-center">Item</th>
                                <th class="text-right">Total</th>
                                <th>Pembayaran</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td class="font-weight-bold">{{ $order->order_code }}</td>
                                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $order->customer_name }}</td>
                                    <td>{{ $order->customer_phone }}</td>
                                    <td class="text-center">{{ $order->order_details_count }}</td>
                                    <td class="text-right">{{ format_currency($order->total_amount) }}</td>
                                    <td>{{ $order->payment_method }}</td>
                                    <td class="text-center"><span class="badge badge-{{ $order->status_badge }}">{{ $order->status }}</span></td>
                                    <td class="text-center">
                                        <a href="{{ route('orders.show', $order) }}" class="btn btn-info btn-sm"><i class="bi bi-eye"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted">Belum ada pesanan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $orders->links('pagination::bootstrap-4') }}</div>
            </div>
        </div>
    </div>
@endsection
