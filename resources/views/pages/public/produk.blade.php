<?php

use App\Enums\ProductCategory;
use App\Models\Product;
use App\Models\SiteSetting;
use Artesaos\SEOTools\Facades\SEOMeta;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::public')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $country = '';

    public ?int $selectedProductId = null;

    public function mount(): void
    {
        SEOMeta::setTitle('Katalog Produk | PT Mustika Makmur Abadi');
        SEOMeta::setDescription('Katalog resmi snack & MPASI bayi Yummy Bites dan beras premium impor & lokal dari PT Mustika Makmur Abadi.');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->country = '';
        $this->resetPage();
    }

    public function updatedCountry(): void
    {
        $this->resetPage();
    }

    public function openDetail(int $productId): void
    {
        $this->selectedProductId = $productId;
    }

    public function closeDetail(): void
    {
        $this->selectedProductId = null;
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->category = '';
        $this->country = '';
        $this->resetPage();
    }

    #[Computed]
    public function selectedProduct(): ?Product
    {
        if (! $this->selectedProductId) {
            return null;
        }

        return Product::with('images')->find($this->selectedProductId);
    }

    #[Computed]
    public function countries()
    {
        return Product::query()
            ->where('category', ProductCategory::BerasImpor->value)
            ->whereNotNull('country_of_origin')
            ->distinct()
            ->orderBy('country_of_origin')
            ->pluck('country_of_origin');
    }

    #[Computed]
    public function products()
    {
        return Product::query()
            ->where('is_active', true)
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%')
                        ->orWhere('sku', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->category, fn ($q) => $q->where('category', $this->category))
            ->when($this->country, fn ($q) => $q->where('country_of_origin', $this->country))
            ->with('images')
            ->orderBy('name')
            ->paginate(9);
    }

    #[Computed]
    public function categoryIntro(): ?string
    {
        return match ($this->category) {
            ProductCategory::YummyBites->value => 'Camilan dan MPASI sehat terpercaya untuk si kecil mulai usia 6 bulan hingga 1 tahun ke atas. Bebas gluten, tanpa bahan pengawet, dan bergizi tinggi.',
            ProductCategory::BerasImpor->value => 'Koleksi beras pilihan berkualitas tinggi: Beras Jepang Koshihikari, Beras Basmati India, Beras Melati Jasmine Thailand, Beras Organik Indonesia, dan Beras Lautan Mas.',
            default => null,
        };
    }

    public function getWhatsappLink(Product $product): string
    {
        $number = SiteSetting::get('whatsapp_number', '6281234567890');
        $text = rawurlencode(
            "Halo PT Mustika Makmur Abadi, saya tertarik untuk menanyakan produk berikut:\n\n" .
            "Produk: {$product->name}\n" .
            "SKU: {$product->sku}\n" .
            "Kemasan: " . ($product->weight ?? '-') . "\n\n" .
            "Mohon info ketersediaan stok dan harga grosirnya. Terima kasih."
        );

        return "https://wa.me/{$number}?text={$text}";
    }
}; ?>

<div class="bg-slate-50/50 pb-20 dark:bg-zinc-950">
    {{-- Header Banner & Integrated Filter Bar --}}
    <section class="border-b border-slate-200/80 bg-white pt-10 pb-8 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-lg bg-brand-50 px-3 py-1 text-xs font-bold text-brand-700 dark:bg-brand-950/60 dark:text-brand-300">
                        <span>Katalog Resmi Distributor</span>
                    </div>
                    <h1 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl dark:text-white">
                        Produk Kami
                    </h1>
                    <p class="mt-2 max-w-2xl text-base text-slate-600 dark:text-zinc-400">
                        Penyalur resmi snack & MPASI bayi Yummy Bites dan beras premium pilihan (lokal & impor) untuk kebutuhan ritel, supermarket, restoran, dan grosir.
                    </p>
                </div>

                {{-- Live Search Input --}}
                <div class="w-full md:max-w-xs">
                    <label for="product-search" class="sr-only">Cari Produk</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input
                            id="product-search"
                            type="search"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Cari nama atau varian..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/60 py-2.5 pr-4 pl-10 text-sm text-slate-900 placeholder-slate-400 transition focus:border-brand-500 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-brand-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white dark:placeholder-zinc-500 dark:focus:border-brand-400"
                        >
                        @if ($search)
                            <button
                                wire:click="$set('search', '')"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200"
                                aria-label="Hapus pencarian"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Category Filter Tabs --}}
            <div class="mt-6 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-5 dark:border-zinc-800">
                <button
                    wire:click="$set('category', '')"
                    class="rounded-xl px-4 py-2 text-xs sm:text-sm font-bold transition {{ $category === '' ? 'bg-brand-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700' }}"
                >
                    Semua Produk
                </button>
                <button
                    wire:click="$set('category', '{{ ProductCategory::YummyBites->value }}')"
                    class="rounded-xl px-4 py-2 text-xs sm:text-sm font-bold transition {{ $category === ProductCategory::YummyBites->value ? 'bg-brand-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700' }}"
                >
                    Yummy Bites
                </button>
                <button
                    wire:click="$set('category', '{{ ProductCategory::BerasImpor->value }}')"
                    class="rounded-xl px-4 py-2 text-xs sm:text-sm font-bold transition {{ $category === ProductCategory::BerasImpor->value ? 'bg-brand-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700' }}"
                >
                    Beras Pilihan (Lokal & Impor)
                </button>

                @if ($search || $category || $country)
                    <button
                        wire:click="resetFilters"
                        class="ml-auto inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/30"
                    >
                        <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/></svg>
                        <span>Reset Filter</span>
                    </button>
                @endif
            </div>

            {{-- Category Description Callout --}}
            @if ($this->categoryIntro)
                <div class="mt-4 rounded-xl border border-slate-200/60 bg-slate-50 px-4 py-3 text-xs sm:text-sm text-slate-600 dark:border-zinc-800 dark:bg-zinc-800/40 dark:text-zinc-300">
                    {{ $this->categoryIntro }}
                </div>
            @endif

            {{-- Country Filter Pills (if Rice category is active) --}}
            @if ($category === ProductCategory::BerasImpor->value && $this->countries->isNotEmpty())
                <div class="mt-3 flex flex-wrap items-center gap-1.5 pt-1">
                    <span class="mr-1 text-xs font-semibold text-slate-400 dark:text-zinc-500">Asal Negara:</span>
                    <button
                        wire:click="$set('country', '')"
                        class="rounded-lg px-2.5 py-1 text-xs font-bold transition {{ $country === '' ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-300' }}"
                    >
                        Semua
                    </button>
                    @foreach ($this->countries as $originCountry)
                        <button
                            wire:click="$set('country', '{{ $originCountry }}')"
                            class="rounded-lg px-2.5 py-1 text-xs font-bold transition {{ $country === $originCountry ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-zinc-800 dark:text-zinc-300' }}"
                        >
                            {{ $originCountry }}
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Main Catalog Grid Container --}}
    <main class="mx-auto max-w-6xl px-4 pt-8 sm:px-6">
        {{-- Product Summary Info Bar --}}
        <div class="flex items-center justify-between pb-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-zinc-500">
                Menampilkan {{ $this->products->total() }} produk
                @if ($search)
                    untuk pencarian "<span class="text-slate-700 dark:text-zinc-200">{{ $search }}</span>"
                @endif
            </p>

            <div wire:loading class="text-xs font-medium text-brand-600 dark:text-brand-400">
                Memuat katalog...
            </div>
        </div>

        {{-- Product Grid --}}
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($this->products as $product)
                @php
                    $parsed = $product->parsedDetails();
                @endphp
                <div
                    wire:key="product-card-{{ $product->id }}"
                    class="group flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs transition duration-200 hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700"
                >
                    <div>
                        {{-- Image Area --}}
                        <div class="relative flex aspect-square w-full items-center justify-center overflow-hidden bg-slate-50/80 p-5 dark:bg-zinc-800/60">
                            @if ($product->images->isEmpty())
                                <div class="flex aspect-square items-center justify-center text-brand-300 dark:text-brand-600">
                                    <span class="font-display text-5xl font-bold">{{ mb_substr($product->name, 0, 1) }}</span>
                                </div>
                            @elseif ($product->images->count() === 1)
                                <img
                                    src="{{ $product->images->first()->url() }}"
                                    alt="{{ $product->images->first()->alt_text ?? $product->name }}"
                                    class="h-full w-full object-contain transition-transform duration-300 group-hover:scale-105"
                                    loading="lazy"
                                >
                            @else
                                <div
                                    x-data
                                    x-init="new Swiper($refs.swiper, { modules: [SwiperModules.Navigation, SwiperModules.Pagination], navigation: true, pagination: { el: $refs.swiper.querySelector('.swiper-pagination') } })"
                                    class="relative h-full w-full"
                                >
                                    <div class="swiper h-full w-full" x-ref="swiper">
                                        <div class="swiper-wrapper">
                                            @foreach ($product->images as $image)
                                                <div class="swiper-slide flex items-center justify-center">
                                                    <img
                                                        src="{{ $image->url() }}"
                                                        alt="{{ $image->alt_text ?? $product->name }}"
                                                        class="h-full w-full object-contain p-2"
                                                        loading="lazy"
                                                    >
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="swiper-pagination"></div>
                                    </div>
                                </div>
                            @endif

                            {{-- Floating Weight / Packaging Badge --}}
                            @if ($product->weight)
                                <span class="absolute top-3 right-3 rounded-md bg-white/95 px-2 py-0.5 text-xs font-bold text-slate-700 shadow-xs backdrop-blur-xs dark:bg-zinc-800/95 dark:text-zinc-200">
                                    {{ $product->weight }}
                                </span>
                            @endif
                        </div>

                        {{-- Card Details --}}
                        <div class="p-5">
                            {{-- Badges --}}
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="rounded-md bg-brand-50 px-2 py-0.5 text-xs font-bold text-brand-700 dark:bg-brand-950/60 dark:text-brand-300">
                                    {{ $product->category->label() }}
                                </span>
                                @if ($product->country_of_origin)
                                    <span class="rounded-md bg-amber-50 px-2 py-0.5 text-xs font-bold text-amber-800 dark:bg-amber-950/50 dark:text-amber-300">
                                        {{ $product->country_of_origin }}
                                    </span>
                                @endif
                            </div>

                            {{-- Title --}}
                            <h2 class="mt-2.5 font-display text-base font-bold text-slate-900 group-hover:text-brand-600 dark:text-white dark:group-hover:text-brand-400">
                                <a href="{{ route('produk.detail', $product) }}" wire:navigate>
                                    {{ $product->name }}
                                </a>
                            </h2>

                            {{-- Description Excerpt --}}
                            <p class="mt-2 line-clamp-2 text-xs leading-relaxed text-slate-500 dark:text-zinc-400">
                                {{ $parsed['main'] }}
                            </p>
                        </div>
                    </div>

                    {{-- Card Footer Actions --}}
                    <div class="border-t border-slate-100 p-4 dark:border-zinc-800">
                        <div class="flex items-center gap-2">
                            {{-- Quick Detail Modal Trigger --}}
                            <button
                                type="button"
                                wire:click="openDetail({{ $product->id }})"
                                class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-brand-50 py-2.5 text-xs font-bold text-brand-700 transition hover:bg-brand-100 focus:outline-hidden focus:ring-2 focus:ring-brand-500 dark:bg-brand-950/60 dark:text-brand-300 dark:hover:bg-brand-900"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Detail Cepat</span>
                            </button>

                            {{-- Dedicated Page Link --}}
                            <a
                                href="{{ route('produk.detail', $product) }}"
                                wire:navigate
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-hidden focus:ring-2 focus:ring-slate-300 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800"
                                title="Buka Halaman Lengkap"
                                aria-label="Buka Halaman Lengkap {{ $product->name }}"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>

                            {{-- WhatsApp Order Action --}}
                            <a
                                href="{{ $this->getWhatsappLink($product) }}"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-green-50 text-green-700 transition hover:bg-green-100 focus:outline-hidden focus:ring-2 focus:ring-green-500 dark:bg-green-950/50 dark:text-green-300 dark:hover:bg-green-900"
                                title="Pesan via WhatsApp"
                                aria-label="Pesan {{ $product->name }} via WhatsApp"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4"><path d="M12.04 2c-5.46 0-9.9 4.44-9.9 9.9 0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.9-4.44 9.9-9.9 0-2.64-1.03-5.12-2.9-6.99A9.82 9.82 0 0 0 12.04 2zm5.8 14.07c-.24.68-1.4 1.3-1.93 1.36-.5.06-1 .27-3.36-.7-2.84-1.17-4.66-4.05-4.8-4.24-.14-.19-1.15-1.53-1.15-2.92s.72-2.06.98-2.35c.26-.29.56-.36.75-.36h.54c.17 0 .4-.03.62.48.24.56.8 1.95.87 2.09.07.14.11.3.02.49-.09.19-.14.3-.28.46-.14.16-.29.36-.42.48-.14.13-.29.28-.12.55.17.28.75 1.24 1.61 2.01 1.11 1 2.04 1.31 2.32 1.46.28.14.44.12.6-.07.17-.19.72-.84.91-1.13.19-.28.38-.23.63-.14.26.09 1.65.78 1.93.92.28.14.47.21.54.33.07.12.07.68-.17 1.36z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400 dark:bg-zinc-800 dark:text-zinc-500">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <h3 class="mt-4 text-base font-bold text-slate-900 dark:text-white">Tidak ada produk ditemukan</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">
                        Coba gunakan kata kunci lain atau setel ulang filter pencarian Anda.
                    </p>
                    <div class="mt-6">
                        <button
                            type="button"
                            wire:click="resetFilters"
                            class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-700"
                        >
                            Reset Semua Filter
                        </button>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-10">
            {{ $this->products->links() }}
        </div>
    </main>

    {{-- Interactive Quick View Detail Modal --}}
    @if ($this->selectedProduct)
        @php
            $prod = $this->selectedProduct;
            $modalParsed = $prod->parsedDetails();
        @endphp
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
            role="dialog"
            aria-modal="true"
            aria-labelledby="modal-title"
            x-data
            @keydown.window.escape="$wire.closeDetail()"
        >
            {{-- Backdrop --}}
            <div
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                wire:click="closeDetail"
            ></div>

            {{-- Modal Dialog Panel --}}
            <div class="relative max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-zinc-800 dark:bg-zinc-900">
                {{-- Header with Close Button --}}
                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-100 bg-white/95 px-6 py-4 backdrop-blur-xs dark:border-zinc-800 dark:bg-zinc-900/95">
                    <div class="flex items-center gap-2">
                        <span class="rounded-md bg-brand-50 px-2.5 py-1 text-xs font-bold text-brand-700 dark:bg-brand-950/60 dark:text-brand-300">
                            {{ $prod->category->label() }}
                        </span>
                        @if ($prod->country_of_origin)
                            <span class="rounded-md bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-800 dark:bg-amber-950/50 dark:text-amber-300">
                                {{ $prod->country_of_origin }}
                            </span>
                        @endif
                    </div>

                    <button
                        type="button"
                        wire:click="closeDetail"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 focus:outline-hidden focus:ring-2 focus:ring-slate-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                        aria-label="Tutup modal"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>
                </div>

                {{-- Modal Body Content --}}
                <div class="p-6 sm:p-8">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-12">
                        {{-- Image Area --}}
                        <div class="flex aspect-square w-full items-center justify-center rounded-2xl bg-slate-50 p-6 md:col-span-5 dark:bg-zinc-800/60">
                            @if ($prod->images->isNotEmpty())
                                <img
                                    src="{{ $prod->images->first()->url() }}"
                                    alt="{{ $prod->images->first()->alt_text ?? $prod->name }}"
                                    class="h-full w-full object-contain"
                                >
                            @else
                                <span class="font-display text-5xl font-bold text-brand-300 dark:text-brand-600">
                                    {{ mb_substr($prod->name, 0, 1) }}
                                </span>
                            @endif
                        </div>

                        {{-- Main Info --}}
                        <div class="flex flex-col justify-between md:col-span-7">
                            <div>
                                <h3 id="modal-title" class="font-display text-xl font-extrabold text-slate-900 sm:text-2xl dark:text-white">
                                    {{ $prod->name }}
                                </h3>

                                <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-slate-500 dark:text-zinc-400">
                                    @if ($prod->weight)
                                        <span>Kemasan: <strong class="text-slate-700 dark:text-zinc-200">{{ $prod->weight }}</strong></span>
                                        <span>•</span>
                                    @endif
                                    <span>SKU: <strong class="text-slate-700 dark:text-zinc-200">{{ $prod->sku }}</strong></span>
                                </div>

                                <div class="mt-4 border-t border-slate-100 pt-3 dark:border-zinc-800">
                                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">
                                        Informasi Produk
                                    </p>
                                    <p class="mt-1.5 text-sm leading-relaxed text-slate-700 dark:text-zinc-300">
                                        {{ $modalParsed['main'] }}
                                    </p>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="mt-6 flex flex-col gap-2.5 sm:flex-row">
                                <a
                                    href="{{ $this->getWhatsappLink($prod) }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-green-600 px-4 py-3 text-center text-xs sm:text-sm font-bold text-white transition hover:bg-green-700 focus:outline-hidden focus:ring-2 focus:ring-green-600"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4"><path d="M12.04 2c-5.46 0-9.9 4.44-9.9 9.9 0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.9-4.44 9.9-9.9 0-2.64-1.03-5.12-2.9-6.99A9.82 9.82 0 0 0 12.04 2zm5.8 14.07c-.24.68-1.4 1.3-1.93 1.36-.5.06-1 .27-3.36-.7-2.84-1.17-4.66-4.05-4.8-4.24-.14-.19-1.15-1.53-1.15-2.92s.72-2.06.98-2.35c.26-.29.56-.36.75-.36h.54c.17 0 .4-.03.62.48.24.56.8 1.95.87 2.09.07.14.11.3.02.49-.09.19-.14.3-.28.46-.14.16-.29.36-.42.48-.14.13-.29.28-.12.55.17.28.75 1.24 1.61 2.01 1.11 1 2.04 1.31 2.32 1.46.28.14.44.12.6-.07.17-.19.72-.84.91-1.13.19-.28.38-.23.63-.14.26.09 1.65.78 1.93.92.28.14.47.21.54.33.07.12.07.68-.17 1.36z"/></svg>
                                    <span>Pesan via WhatsApp</span>
                                </a>

                                <a
                                    href="{{ route('produk.detail', $prod) }}"
                                    wire:navigate
                                    class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-3 text-center text-xs sm:text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-750"
                                >
                                    <span>Halaman Lengkap</span>
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Structured Details Grid in Modal --}}
                    @if (!empty($modalParsed['details']))
                        <div class="mt-6 border-t border-slate-100 pt-6 dark:border-zinc-800">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">
                                Spesifikasi dan Keunggulan
                            </h4>
                            <div class="mt-3 grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                                @foreach ($modalParsed['details'] as $detail)
                                    <div class="rounded-xl border border-slate-100 bg-slate-50/80 p-3.5 dark:border-zinc-800 dark:bg-zinc-800/40">
                                        <p class="text-xs font-bold text-slate-500 dark:text-zinc-400">
                                            {{ $detail['label'] }}
                                        </p>
                                        <p class="mt-1 text-xs font-medium text-slate-800 dark:text-zinc-200">
                                            {{ $detail['value'] }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
