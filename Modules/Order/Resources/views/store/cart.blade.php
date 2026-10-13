@extends('order::store.layout')

@section('title', 'Keranjang')

@section('content')
    <h1 class="h4 mb-3">Keranjang Belanja</h1>

    @if($items->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-cart-x display-4 text-muted"></i>
                <p class="text-muted mt-3">Keranjang masih kosong.</p>
                <a href="{{ route('store.index') }}" class="btn btn-brand">Mulai Belanja</a>
            </div>
        </div>
    @else
        <form action="{{ route('store.cart.update') }}" method="POST">
            @csrf
            <div class="card border-0 shadow-sm mb-3">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="thead-light">
                            <tr><th>Produk</th><th>Harga</th><th style="width: 120px">Jumlah</th><th class="text-right">Subtotal</th><th></th></tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                <tr>
                                    <td>{{ $item->product->product_name }}<br><small class="text-muted">Stok {{ $item->product->product_quantity }}</small></td>
                                    <td>{{ format_currency($item->product->product_price) }}</td>
                                    <td>
                                        <input type="number" name="quantities[{{ $item->product->id }}]" value="{{ $item->quantity }}"
                                               min="0" max="{{ $item->product->product_quantity }}" class="form-control form-control-sm">
                                    </td>
                                    <td class="text-right">{{ format_currency($item->sub_total) }}</td>
                                    <td class="text-right">
                                        <button formaction="{{ route('store.cart.remove', $item->product) }}" class="btn btn-sm btn-link text-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-weight-bold">
                                <td colspan="3" class="text-right">Total</td>
                                <td class="text-right price">{{ format_currency($items->sum('sub_total')) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="d-flex justify-content-between flex-wrap">
                <a href="{{ route('store.index') }}" class="btn btn-outline-secondary mb-2"><i class="bi bi-arrow-left"></i> Lanjut Belanja</a>
                <div>
                    <button class="btn btn-outline-brand mb-2"><i class="bi bi-arrow-repeat"></i> Perbarui Keranjang</button>
                    <a href="{{ route('store.checkout') }}" class="btn btn-brand mb-2">Checkout <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </form>
    @endif
@endsection
