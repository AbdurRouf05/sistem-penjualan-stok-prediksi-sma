<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | {{ settings()->company_name }}</title>
    <link rel="icon" href="{{ asset('images/favicon.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    <style>
        :root { --brand: #c2410c; --brand-dark: #9a3412; --brand-soft: #fff7ed; }
        body { background: #fafaf9; color: #1c1917; }
        .navbar-brand { font-weight: 700; color: var(--brand) !important; }
        .btn-brand { background: var(--brand); border-color: var(--brand); color: #fff; }
        .btn-brand:hover, .btn-brand:focus { background: var(--brand-dark); border-color: var(--brand-dark); color: #fff; }
        .btn-outline-brand { border-color: var(--brand); color: var(--brand); }
        .btn-outline-brand:hover { background: var(--brand); color: #fff; }
        .text-brand { color: var(--brand); }
        .hero { background: linear-gradient(135deg, #fb923c, #c2410c); color: #fff; border-radius: 1rem; }
        .product-card { border: 0; border-radius: .75rem; box-shadow: 0 1px 3px rgba(0,0,0,.08); transition: transform .15s, box-shadow .15s; }
        .product-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.1); }
        .product-card img { height: 160px; object-fit: cover; border-radius: .75rem .75rem 0 0; background: var(--brand-soft); }
        .price { color: var(--brand); font-weight: 700; }
        .cart-badge { position: relative; top: -8px; left: -4px; }
        footer { border-top: 1px solid #e7e5e4; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand" href="{{ route('store.index') }}">
            <i class="bi bi-basket2-fill"></i> {{ settings()->company_name }}
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav ml-auto align-items-md-center">
                <li class="nav-item"><a class="nav-link" href="{{ route('store.index') }}">Produk</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('store.track') }}">Lacak Pesanan</a></li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('store.cart') }}">
                        <i class="bi bi-cart3"></i> Keranjang
                        @if(count(session('cart', [])))
                            <span class="badge badge-pill badge-danger cart-badge">{{ array_sum(session('cart', [])) }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item ml-md-2">
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('login') }}"><i class="bi bi-person-lock"></i> Login Admin</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @yield('content')
</main>

<footer class="py-4 mt-4 bg-white">
    <div class="container text-center text-muted small">
        <strong>{{ settings()->company_name }}</strong> · {{ settings()->company_address }} · Telp/WA {{ settings()->company_phone }}
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
