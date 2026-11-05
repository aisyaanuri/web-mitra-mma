<?php

use App\Exports\OrdersExport;
use App\Exports\ReconciliationExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

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

        Route::get('gudang/export', function (Request $request) {
            return Excel::download(
                new OrdersExport(
                    $request->query('status'),
                    $request->query('search'),
                    $request->query('date_from'),
                    $request->query('date_to'),
                    $request->query('month'),
                ),
                'pesanan-'.now()->format('Y-m-d').'.xlsx',
            );
        })->name('warehouse.export');
    });

    Route::middleware('role:owner,admin_marketplace')->group(function () {
        Route::livewire('rekonsiliasi', 'pages::admin.reconciliation.index')->name('reconciliation.index');
        Route::livewire('sinkronisasi', 'pages::admin.sync.index')->name('sync.index');
        Route::livewire('pesan', 'pages::admin.messages.index')->name('messages.index');

        Route::get('rekonsiliasi/export', function (Request $request) {
            return Excel::download(
                new ReconciliationExport($request->query('period'), $request->query('matchStatus'), $request->query('search')),
                'rekonsiliasi-'.now()->format('Y-m-d').'.xlsx',
            );
        })->name('reconciliation.export');
    });

    Route::middleware('role:owner,admin_marketplace,admin_gudang')->group(function () {
        Route::livewire('retur', 'pages::admin.returns.index')->name('returns.index');
    });
});
