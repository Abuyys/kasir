<?php

use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

/* NOTE: Do Not Remove
/ Livewire asset handling if using sub folder in domain
*/

Livewire::setUpdateRoute(function ($handle) {
    return Route::post(config('app.asset_prefix') . '/livewire/update', $handle);
});

Livewire::setScriptRoute(function ($handle) {
    return Route::get(config('app.asset_prefix') . '/livewire/livewire.js', $handle);
});
/*
/ END
*/

// Homepage redirect based on role
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->role === 'owner'
            ? redirect('/admin')
            : redirect('/cashier');
    }
    return redirect('/admin/login');
});

// Receipt routes
Route::get('/receipt/{transaction}', [ReceiptController::class, 'show'])
    ->middleware('auth')
    ->name('receipt.show');

Route::get('/receipt/{transaction}/pdf', [ReceiptController::class, 'pdf'])
    ->middleware('auth')
    ->name('receipt.pdf');

// Barcode label print route
Route::get('/admin/products/{product}/barcode', [ProductController::class, 'barcode'])
    ->middleware('auth')
    ->name('product.barcode');
