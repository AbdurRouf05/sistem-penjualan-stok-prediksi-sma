@extends('order::store.layout')

@section('title', 'Belanja Camilan')

@section('content')
    <div class="hero p-4 p-md-5 mb-4">
        <h1 class="h3 font-weight-bold">Aneka camilan enak, pesan dari rumah</h1>
        <p class="mb-0">Pilih camilan favoritmu, pesan online, lalu ambil di toko atau kami antar.</p>
    </div>

    <form method="GET" class="form-row mb-3">
        <div class="col-md-6 mb-2">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari camilan...">
        </div>
        <div class="col-md-4 mb-2">
            <select name="category" class="form-control" onchange="this.form.submit()">
                <option value="">Semua kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->category_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 mb-2">
            <button class="btn btn-brand btn-block"><i class="bi bi-search"></i> Cari</button>
        </div>
    </form>

    <div class="row">
        @forelse($products as $product)
            <div class="col-6 col-md-4 col-lg-3 mb-4">
                <div class="card product-card h-100">
                    <img src="{{ $product->getFirstMediaUrl('images') }}" class="card-img-top" alt="{{ $product->product_name }}">
                    <div class="card-body d-flex flex-column">
                        <small class="text-muted">{{ $product->category->category_name ?? '' }}</small>
                        <h6 class="mb-1">{{ $product->product_name }}</h6>
                        <div class="price mb-1">{{ format_currency($product->product_price) }}</div>
                        <small class="text-muted mb-3">
                            @if($product->product_quantity > 0)
                                Stok {{ $product->product_quantity }} {{ $product->product_unit }}
                            @else
                                <span class="text-danger">Stok habis</span>
                            @endif
                        </small>
                        <form action="{{ route('store.cart.add', $product) }}" method="POST" class="mt-auto">
                            @csrf
                            <button class="btn btn-brand btn-sm btn-block" {{ $product->product_quantity < 1 ? 'disabled' : '' }}>
                                <i class="bi bi-cart-plus"></i> Tambah
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center text-muted py-5">Produk tidak ditemukan.</div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center">{{ $products->links('pagination::bootstrap-4') }}</div>
@endsection
