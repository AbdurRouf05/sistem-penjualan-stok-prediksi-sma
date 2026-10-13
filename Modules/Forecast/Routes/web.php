<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth', 'can:access_forecasting']], function () {
    Route::get('/prediksi-stok', 'ForecastController@index')->name('forecast.index');
    Route::get('/prediksi-stok/{product}', 'ForecastController@show')->name('forecast.show');
});
