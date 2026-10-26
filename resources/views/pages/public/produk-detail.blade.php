<?php

use App\Enums\ProductCategory;
use App\Models\Product;
use App\Models\SiteSetting;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::public')] class extends Component
{
    public Product $product;

    public function mount(Product $product): void
    {
        abort_unless($product->is_active, 404);

        $this->product = $product->load('images');

        $title = $product->name . ' | PT Mustika Makmur Abadi';
        $desc = mb_substr(str_replace("\n", ' ', $product->description ?? 'Katalog produk resmi PT Mustika Makmur Abadi'), 0, 160);

        SEOMeta::setTitle($title);
        SEOMeta::setDescription($desc);
        SEOMeta::setCanonical(route('produk.detail', $product));

        OpenGraph::setTitle($title);
        OpenGraph::setDescription($desc);
        OpenGraph::setUrl(route('produk.detail', $product));
        OpenGraph::addProperty('type', 'product');

        if ($firstImage = $product->images->first()) {
            OpenGraph::addImage($firstImage->url());
        }
    }

    #[Computed]
    public function relatedProducts()
    {
        return Product::query()
            ->where('is_active', true)
            ->where('id', '!=', $this->product->id)
            ->where('category', $this->product->category)
            ->with('images')
            ->inRandomOrder()
            ->take(3)
            ->get();
    }

    #[Computed]
    public function whatsappLink(): string
    {
        $number = SiteSetting::get('whatsapp_number', '6281234567890');
        $text = rawurlencode(
            "Halo PT Mustika Makmur Abadi, saya tertarik dan ingin menanyakan informasi pemesanan produk:\n\n" .
            "Produk: {$this->product->name}\n" .
            "SKU: {$this->product->sku}\n" .
            "Kemasan: " . ($this->product->weight ?? '-') . "\n\n" .
            "Mohon info ketersediaan stok dan penawaran terbaiknya. Terima kasih."
        );

        return "https://wa.me/{$number}?text={$text}";
    }
}; ?>

<div class="bg-slate-50/60 pb-16 dark:bg-zinc-950">
    {{-- Breadcrumb Navigation --}}
    <div class="border-b border-slate-200/80 bg-white py-3.5 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <nav class="flex items-center gap-2 text-sm text-slate-500 dark:text-zinc-400" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" wire:navigate class="transition hover:text-brand-600 dark:hover:text-brand-300">
                    Beranda
                </a>
                <span class="text-slate-300 dark:text-zinc-600">/</span>
                <a href="{{ route('produk') }}" wire:navigate class="transition hover:text-brand-600 dark:hover:text-brand-300">
                    Produk
                </a>
                <span class="text-slate-300 dark:text-zinc-600">/</span>
                <span class="truncate font-semibold text-slate-900 dark:text-white" aria-current="page">
                    {{ $product->name }}
                </span>
            </nav>
        </div>
    </div>

    {{-- Main Product Detail Container --}}
    <main class="mx-auto max-w-6xl px-4 pt-8 sm:px-6 sm:pt-10">
        <div class="overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
            <div class="grid grid-cols-1 lg:grid-cols-12">
                {{-- Left: Product Visual Presentation --}}
                <div class="border-b border-slate-100 bg-slate-50/50 p-6 sm:p-10 lg:col-span-5 lg:border-b-0 lg:border-r dark:border-zinc-800 dark:bg-zinc-900/60">
                    <div class="sticky top-24">
                        @if ($product->images->isEmpty())
                            <div class="flex aspect-square w-full items-center justify-center rounded-2xl bg-white shadow-inner dark:bg-zinc-800">
                                <span class="font-display text-6xl font-bold text-brand-300 dark:text-brand-600">
                                    {{ mb_substr($product->name, 0, 1) }}
                                </span>
                            </div>
                        @elseif ($product->images->count() === 1)
                            <div class="group relative flex aspect-square w-full items-center justify-center rounded-2xl bg-white p-6 shadow-xs ring-1 ring-slate-100 dark:bg-zinc-800 dark:ring-zinc-700/60">
                                <img
                                    src="{{ $product->images->first()->url() }}"
                                    alt="{{ $product->images->first()->alt_text ?? $product->name }}"
                                    class="h-full w-full object-contain transition-transform duration-300 group-hover:scale-105"
                                >
                            </div>
                        @else
                            <div
                                x-data
                                x-init="new Swiper($refs.detailSwiper, { modules: [SwiperModules.Navigation, SwiperModules.Pagination], navigation: true, pagination: { el: $refs.detailSwiper.querySelector('.swiper-pagination'), clickable: true } })"
                                class="relative rounded-2xl bg-white p-6 shadow-xs ring-1 ring-slate-100 dark:bg-zinc-800 dark:ring-zinc-700/60"
                            >
                                <div class="swiper aspect-square" x-ref="detailSwiper">
                                    <div class="swiper-wrapper">
                                        @foreach ($product->images as $image)
                                            <div class="swiper-slide flex items-center justify-center">
                                                <img
                                                    src="{{ $image->url() }}"
                                                    alt="{{ $image->alt_text ?? $product->name }}"
                                                    class="h-full w-full object-contain p-2"
                                                >
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="swiper-pagination"></div>
                                </div>
                            </div>
                        @endif

                        {{-- Quality Trust Marks --}}
                        <div class="mt-6 grid grid-cols-2 gap-3 text-center">
                            <div class="rounded-xl border border-slate-100 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-800/70">
                                <p class="text-xs font-semibold text-slate-500 dark:text-zinc-400">Jaminan Mutu</p>
                                <p class="mt-0.5 text-xs font-bold text-slate-800 dark:text-zinc-200">100% Produk Asli</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-800/70">
                                <p class="text-xs font-semibold text-slate-500 dark:text-zinc-400">Distribusi Resmi</p>
                                <p class="mt-0.5 text-xs font-bold text-slate-800 dark:text-zinc-200">PT Mustika Makmur</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right: Product Details & Specifications --}}
                <div class="p-6 sm:p-10 lg:col-span-7">
                    {{-- Category & Country Badges --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-lg bg-brand-50 px-3 py-1 text-xs font-bold text-brand-700 ring-1 ring-brand-600/20 dark:bg-brand-950/60 dark:text-brand-300 dark:ring-brand-500/30">
                            {{ $product->category->label() }}
                        </span>

                        @if ($product->country_of_origin)
                            <span class="inline-flex items-center gap-1 rounded-lg bg-amber-50 px-3 py-1 text-xs font-bold text-amber-800 ring-1 ring-amber-600/20 dark:bg-amber-950/50 dark:text-amber-300 dark:ring-amber-500/30">
                                <span>Asal:</span>
                                <span>{{ $product->country_of_origin }}</span>
                            </span>
                        @endif

                        @if ($product->weight)
                            <span class="inline-flex items-center rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 dark:bg-zinc-800 dark:text-zinc-300">
                                Kemasan {{ $product->weight }}
                            </span>
                        @endif

                        <span class="ml-auto text-xs font-mono text-slate-400 dark:text-zinc-500">
                            SKU: {{ $product->sku }}
                        </span>
                    </div>

                    {{-- Product Name --}}
                    <h1 class="mt-4 font-display text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                        {{ $product->name }}
                    </h1>

                    @php
                        $parsed = $product->parsedDetails();
                    @endphp

                    {{-- Main Story Description --}}
                    <div class="mt-4 border-t border-slate-100 pt-4 dark:border-zinc-800">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">
                            Deskripsi Produk
                        </h2>
                        <p class="mt-2 text-base leading-relaxed text-slate-700 dark:text-zinc-300">
                            {{ $parsed['main'] }}
                        </p>
                    </div>

                    {{-- Structured Attributes / Highlights --}}
                    @if (!empty($parsed['details']))
                        <div class="mt-6 space-y-3">
                            @foreach ($parsed['details'] as $detail)
                                <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-4 transition-colors hover:border-slate-200 dark:border-zinc-800 dark:bg-zinc-800/50 dark:hover:border-zinc-700">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-brand-100 text-brand-700 dark:bg-brand-900/60 dark:text-brand-300">
                                            @if ($detail['label'] === 'Komposisi')
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                            @elseif ($detail['label'] === 'Alergen')
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                            @elseif ($detail['label'] === 'Tekstur')
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                                            @else
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 12 2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                            @endif
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">
                                                {{ $detail['label'] }}
                                            </h3>
                                            <p class="mt-1 text-sm font-medium text-slate-800 dark:text-zinc-200">
                                                {{ $detail['value'] }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Call to Action: WhatsApp Order & Distributor Consultation --}}
                    <div class="mt-8 border-t border-slate-100 pt-6 dark:border-zinc-800">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <a
                                href="{{ $this->whatsappLink }}"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex flex-1 items-center justify-center gap-2.5 rounded-xl bg-green-600 px-6 py-3.5 text-center text-sm font-bold text-white shadow-sm transition hover:bg-green-700 focus:outline-hidden focus:ring-2 focus:ring-green-600 focus:ring-offset-2"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M12.04 2c-5.46 0-9.9 4.44-9.9 9.9 0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.9-4.44 9.9-9.9 0-2.64-1.03-5.12-2.9-6.99A9.82 9.82 0 0 0 12.04 2zm5.8 14.07c-.24.68-1.4 1.3-1.93 1.36-.5.06-1 .27-3.36-.7-2.84-1.17-4.66-4.05-4.8-4.24-.14-.19-1.15-1.53-1.15-2.92s.72-2.06.98-2.35c.26-.29.56-.36.75-.36h.54c.17 0 .4-.03.62.48.24.56.8 1.95.87 2.09.07.14.11.3.02.49-.09.19-.14.3-.28.46-.14.16-.29.36-.42.48-.14.13-.29.28-.12.55.17.28.75 1.24 1.61 2.01 1.11 1 2.04 1.31 2.32 1.46.28.14.44.12.6-.07.17-.19.72-.84.91-1.13.19-.28.38-.23.63-.14.26.09 1.65.78 1.93.92.28.14.47.21.54.33.07.12.07.68-.17 1.36z"/></svg>
                                <span>Pesan via WhatsApp</span>
                            </a>

                            <a
                                href="{{ route('produk') }}"
                                wire:navigate
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3.5 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-hidden focus:ring-2 focus:ring-slate-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-750"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                <span>Semua Produk</span>
                            </a>
                        </div>

                        <p class="mt-3 text-center text-xs text-slate-400 sm:text-left dark:text-zinc-500">
                            Melayani pesanan toko, supermarket, restoran, hotel, instansi, maupun pembelian grosir terpercaya.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Related Products Section --}}
        @if ($this->relatedProducts->isNotEmpty())
            <section class="mt-14" aria-labelledby="related-heading">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 id="related-heading" class="font-display text-xl font-bold text-slate-900 sm:text-2xl dark:text-white">
                            Produk Terkait Lainnya
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">
                            Pilihan produk berkualitas lainnya dalam kategori {{ $product->category->label() }}.
                        </p>
                    </div>
                    <a
                        href="{{ route('produk', ['category' => $product->category->value]) }}"
                        wire:navigate
                        class="text-sm font-bold text-brand-600 transition hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300"
                    >
                        Lihat Semua
                    </a>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($this->relatedProducts as $rel)
                        <div class="group flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs transition hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900">
                            <div>
                                <div class="flex aspect-square w-full items-center justify-center rounded-xl bg-slate-50 p-4 dark:bg-zinc-800">
                                    @if ($rel->images->isNotEmpty())
                                        <img
                                            src="{{ $rel->images->first()->url() }}"
                                            alt="{{ $rel->images->first()->alt_text ?? $rel->name }}"
                                            class="h-full w-full object-contain transition-transform duration-200 group-hover:scale-105"
                                            loading="lazy"
                                        >
                                    @else
                                        <span class="font-display text-4xl font-bold text-brand-300 dark:text-brand-600">
                                            {{ mb_substr($rel->name, 0, 1) }}
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-4 flex flex-wrap gap-1.5">
                                    <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600 dark:bg-zinc-800 dark:text-zinc-300">
                                        {{ $rel->weight ?? $rel->category->label() }}
                                    </span>
                                    @if ($rel->country_of_origin)
                                        <span class="rounded-md bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                                            {{ $rel->country_of_origin }}
                                        </span>
                                    @endif
                                </div>

                                <h3 class="mt-2 font-display text-base font-bold text-slate-900 group-hover:text-brand-600 dark:text-white dark:group-hover:text-brand-400">
                                    {{ $rel->name }}
                                </h3>

                                <p class="mt-1.5 line-clamp-2 text-xs leading-relaxed text-slate-500 dark:text-zinc-400">
                                    {{ $rel->parsedDetails()['main'] }}
                                </p>
                            </div>

                            <div class="mt-5 pt-3 border-t border-slate-100 dark:border-zinc-800">
                                <a
                                    href="{{ route('produk.detail', $rel) }}"
                                    wire:navigate
                                    class="inline-flex w-full items-center justify-center rounded-lg bg-brand-50 py-2.5 text-center text-xs font-bold text-brand-700 transition hover:bg-brand-100 dark:bg-brand-950/60 dark:text-brand-300 dark:hover:bg-brand-900"
                                >
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </main>
</div>
