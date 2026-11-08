<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicSiteController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

Route::get('/', [PublicSiteController::class, 'home'])->name('home');
Route::get('/produk', [PublicSiteController::class, 'products'])->name('produk');
Route::get('/produk/{product:slug}', [PublicSiteController::class, 'product'])->name('produk.detail');
Route::get('/tentang-kami', [PublicSiteController::class, 'about'])->name('tentang-kami');
Route::get('/kontak', [PublicSiteController::class, 'contact'])->name('kontak');
Route::post('/kontak', [PublicSiteController::class, 'submitContact'])->name('kontak.store');
Route::get('/kebijakan-privasi', [PublicSiteController::class, 'privacy'])->name('kebijakan-privasi');

Route::get('/sitemap.xml', function () {
    $sitemap = Sitemap::create()
        ->add(Url::create(route('home'))->setPriority(1))
        ->add(Url::create(route('produk'))->setPriority(0.9))
        ->add(Url::create(route('tentang-kami'))->setPriority(0.8))
        ->add(Url::create(route('kontak'))->setPriority(0.8))
        ->add(Url::create(route('kebijakan-privasi'))->setPriority(0.3));

    foreach (Product::where('is_active', true)->cursor() as $product) {
        $sitemap->add(Url::create(route('produk.detail', $product))->setPriority(0.7));
    }

    return $sitemap->toResponse(request());
})->name('sitemap');

require __DIR__.'/admin.php';

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});

require __DIR__.'/settings.php';
