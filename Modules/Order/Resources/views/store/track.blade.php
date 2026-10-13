@extends('order::store.layout')

@section('title', 'Lacak Pesanan')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 mb-3"><i class="bi bi-search text-brand"></i> Lacak Pesanan</h1>
                    <form method="GET">
                        <div class="form-group">
                            <label>Kode Pesanan</label>
                            <input type="text" name="code" value="{{ request('code') }}" class="form-control" placeholder="PSN-261013-XXXXX" required>
                        </div>
                        <button class="btn btn-brand btn-block">Lacak</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
