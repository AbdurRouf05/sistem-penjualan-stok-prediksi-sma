@extends('order::store.layout')

@section('title', 'Checkout')

@section('content')
    <h1 class="h4 mb-3">Checkout</h1>

    <div class="row">
        <div class="col-lg-7 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('store.checkout.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label>Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" value="{{ old('customer_name') }}" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>No. HP / WhatsApp <span class="text-danger">*</span></label>
                            <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Alamat <span class="text-danger">*</span></label>
                            <textarea name="customer_address" rows="3" class="form-control" required>{{ old('customer_address') }}</textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Pengambilan</label>
                                <select name="delivery_method" class="form-control">
                                    @foreach(\Modules\Order\Entities\Order::DELIVERY_METHODS as $method)
                                        <option {{ old('delivery_method') == $method ? 'selected' : '' }}>{{ $method }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Pembayaran</label>
                                <select name="payment_method" class="form-control">
                                    @foreach(\Modules\Order\Entities\Order::PAYMENT_METHODS as $method)
                                        <option {{ old('payment_method') == $method ? 'selected' : '' }}>{{ $method }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Catatan (opsional)</label>
                            <textarea name="note" rows="2" class="form-control" placeholder="Mis. titip di pos satpam">{{ old('note') }}</textarea>
                        </div>
                        <button class="btn btn-brand btn-block btn-lg"><i class="bi bi-bag-check"></i> Buat Pesanan</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white font-weight-bold">Ringkasan Pesanan</div>
                <ul class="list-group list-group-flush">
                    @foreach($items as $item)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $item->product->product_name }} × {{ $item->quantity }}</span>
                            <span>{{ format_currency($item->sub_total) }}</span>
                        </li>
                    @endforeach
                    <li class="list-group-item d-flex justify-content-between font-weight-bold">
                        <span>Total</span><span class="price">{{ format_currency($items->sum('sub_total')) }}</span>
                    </li>
                </ul>
            </div>
            <p class="small text-muted mt-2">Pesanan akan dikonfirmasi oleh admin toko. Pembayaran transfer dilakukan setelah pesanan dikonfirmasi.</p>
        </div>
    </div>
@endsection
