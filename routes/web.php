<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\StoreSearchController;
use App\Http\Controllers\StoreDeliveryController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/stores', [StoreController::class, 'addStore']);
Route::get('/search/nearByStores', [StoreSearchController::class, 'getNearbyStores']);
Route::get('/search/deliveryStores', [StoreDeliveryController::class, 'getStoresDeliveringToPostcode']);
