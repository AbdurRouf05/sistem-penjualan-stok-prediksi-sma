<?php

use Illuminate\Support\Facades\Route;

// Toko online untuk pelanggan
Route::prefix('toko')->name('store.')->group(function () {
    Route::get('/', 'StoreController@index')->name('index');
    Route::get('/keranjang', 'StoreController@cart')->name('cart');
    Route::post('/keranjang/tambah/{product}', 'StoreController@addToCart')->name('cart.add');
    Route::post('/keranjang/ubah', 'StoreController@updateCart')->name('cart.update');
    Route::post('/keranjang/hapus/{product}', 'StoreController@removeFromCart')->name('cart.remove');
    Route::get('/checkout', 'StoreController@checkout')->name('checkout');
    Route::post('/checkout', 'StoreController@placeOrder')->name('checkout.store');
    Route::get('/lacak', 'StoreController@track')->name('track');
    Route::get('/pesanan/{code}', 'StoreController@order')->name('order');
});

// Pengelolaan pesanan oleh admin
Route::group(['middleware' => ['auth', 'can:access_orders']], function () {
    Route::get('/orders', 'OrderController@index')->name('orders.index');
    Route::get('/orders/{order}', 'OrderController@show')->name('orders.show');
    Route::patch('/orders/{order}/status', 'OrderController@updateStatus')->name('orders.status');
});
