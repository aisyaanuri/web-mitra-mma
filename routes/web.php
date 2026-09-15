<?php

use Illuminate\Support\Facades\Route;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

Route::livewire('/', 'pages::public.beranda')->name('home');
Route::livewire('/produk', 'pages::public.produk')->name('produk');
Route::livewire('/tentang-kami', 'pages::public.tentang-kami')->name('tentang-kami');
Route::livewire('/kontak', 'pages::public.kontak')->name('kontak');

Route::get('/sitemap.xml', function () {
    return Sitemap::create()
        ->add(Url::create(route('home'))->setPriority(1))
        ->add(Url::create(route('produk'))->setPriority(0.9))
        ->add(Url::create(route('tentang-kami'))->setPriority(0.8))
        ->add(Url::create(route('kontak'))->setPriority(0.8))
        ->toResponse(request());
})->name('sitemap');

require __DIR__.'/admin.php';

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
