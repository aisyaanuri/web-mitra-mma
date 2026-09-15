<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {
    Route::middleware('role:owner,admin_marketplace')->group(function () {
        Route::livewire('produk', 'pages::admin.products.index')->name('products.index');
        Route::livewire('produk/baru', 'pages::admin.products.form')->name('products.create');
        Route::livewire('produk/{product}/edit', 'pages::admin.products.form')->name('products.edit');
    });
});
