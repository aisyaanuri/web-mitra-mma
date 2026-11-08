<?php

use App\Exports\OrdersExport;
use App\Exports\ReconciliationExport;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReconciliationController;
use App\Http\Controllers\Admin\ReturnController;
use App\Http\Controllers\Admin\SyncController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WarehouseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {
    Route::middleware('role:owner,admin_marketplace')->group(function () {
        Route::get('produk', [ProductController::class, 'index'])->name('products.index');
        Route::get('produk/baru', [ProductController::class, 'create'])->name('products.create');
        Route::post('produk', [ProductController::class, 'store'])->name('products.store');
        Route::get('produk/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('produk/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::patch('produk/{product}/status', [ProductController::class, 'toggleActive'])->name('products.toggle-active');
        Route::delete('produk/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::delete('produk/{product}/images/{image}', [ProductController::class, 'destroyImage'])->name('products.images.destroy');
    });

    Route::middleware('role:owner')->group(function () {
        Route::get('pengguna', [UserController::class, 'index'])->name('users.index');
        Route::get('pengguna/baru', [UserController::class, 'create'])->name('users.create');
        Route::post('pengguna', [UserController::class, 'store'])->name('users.store');
        Route::get('pengguna/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('pengguna/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('pengguna/{user}/status', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::patch('pengguna/{user}/unlock', [UserController::class, 'unlock'])->name('users.unlock');
    });

    Route::middleware('role:owner,admin_gudang')->group(function () {
        Route::get('gudang', [WarehouseController::class, 'index'])->name('warehouse.index');
        Route::patch('gudang/{order}/diterima', [WarehouseController::class, 'markReceived'])->name('warehouse.receive');
        Route::patch('gudang/{order}/lanjut', [WarehouseController::class, 'advance'])->name('warehouse.advance');
        Route::patch('gudang/bulk', [WarehouseController::class, 'bulkUpdate'])->name('warehouse.bulk-update');

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
        Route::get('rekonsiliasi', [ReconciliationController::class, 'index'])->name('reconciliation.index');
        Route::post('rekonsiliasi/jalankan', [ReconciliationController::class, 'run'])->name('reconciliation.run');
        Route::patch('rekonsiliasi/{record}/catatan', [ReconciliationController::class, 'saveNote'])->name('reconciliation.note');
        Route::patch('rekonsiliasi/{record}/gudang', [ReconciliationController::class, 'resolveWarehouseStatus'])->name('reconciliation.warehouse-status');
        Route::get('sinkronisasi', [SyncController::class, 'index'])->name('sync.index');
        Route::post('sinkronisasi/jalankan', [SyncController::class, 'run'])->name('sync.run');
        Route::get('pesan', [MessageController::class, 'index'])->name('messages.index');
        Route::patch('pesan/{message}/status', [MessageController::class, 'updateStatus'])->name('messages.update-status');
        Route::delete('pesan/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');

        Route::get('rekonsiliasi/export', function (Request $request) {
            return Excel::download(
                new ReconciliationExport($request->query('period'), $request->query('matchStatus'), $request->query('search')),
                'rekonsiliasi-'.now()->format('Y-m-d').'.xlsx',
            );
        })->name('reconciliation.export');
    });

    Route::middleware('role:owner,admin_marketplace,admin_gudang')->group(function () {
        Route::get('retur', [ReturnController::class, 'index'])->name('returns.index');
        Route::patch('retur/{orderReturn}/verifikasi', [ReturnController::class, 'verify'])->name('returns.verify');
    });
});
