<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {
    Route::middleware('role:owner,admin_marketplace')->group(function () {
        Route::livewire('produk', 'pages::admin.products.index')->name('products.index');
        Route::livewire('produk/baru', 'pages::admin.products.form')->name('products.create');
        Route::livewire('produk/{product}/edit', 'pages::admin.products.form')->name('products.edit');
    });

    Route::middleware('role:owner')->group(function () {
        Route::livewire('pengguna', 'pages::admin.users.index')->name('users.index');
        Route::livewire('pengguna/baru', 'pages::admin.users.form')->name('users.create');
        Route::livewire('pengguna/{user}/edit', 'pages::admin.users.form')->name('users.edit');
    });

    Route::middleware('role:owner,admin_gudang')->group(function () {
        Route::livewire('gudang', 'pages::admin.warehouse.index')->name('warehouse.index');
    });
});
