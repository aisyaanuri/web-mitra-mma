<?php

use App\Models\CompanyContent;
use App\Models\Product;
use App\Models\SiteSetting;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::public')] class extends Component
{
    public function mount(): void
    {
        SEOMeta::setTitle('Beranda');
        SEOMeta::setDescription('Distributor resmi Yummy Bites dan beras impor di Sidoarjo, melayani 355+ outlet di Indonesia bagian timur.');
        OpenGraph::setTitle('PT Mustika Makmur Abadi');
        OpenGraph::setDescription('Distributor resmi Yummy Bites dan beras impor di Sidoarjo.');
    }

    #[Computed]
    public function hero(): ?CompanyContent
    {
        return CompanyContent::section('beranda_hero');
    }

    #[Computed]
    public function distribusi(): ?CompanyContent
    {
        return CompanyContent::section('beranda_distribusi');
    }

    #[Computed]
    public function featuredProducts()
    {
        return Product::where('is_active', true)->where('is_featured', true)->take(4)->get();
    }
}; ?>

<div>
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-24" data-aos="fade-up">
        <div class="flex flex-wrap gap-3 text-sm font-medium text-amber-700 dark:text-amber-400">
            <span class="rounded-full bg-amber-50 px-3 py-1 dark:bg-amber-950">{{ SiteSetting::get('outlets_count') }}+ outlet</span>
            <span class="rounded-full bg-amber-50 px-3 py-1 dark:bg-amber-950">{{ SiteSetting::get('staff_count') }} tenaga ahli</span>
            <span class="rounded-full bg-amber-50 px-3 py-1 dark:bg-amber-950">Sejak {{ SiteSetting::get('established_year') }}</span>
        </div>

        <h1 class="mt-6 max-w-3xl text-4xl font-bold tracking-tight text-zinc-900 sm:text-5xl dark:text-white">
            {{ $this->hero?->title }}
        </h1>

        <p class="mt-6 max-w-2xl text-lg text-zinc-600 dark:text-zinc-400">
            {{ $this->hero?->body }}
        </p>

        <div class="mt-8 flex flex-wrap gap-4">
            <a href="{{ route('tentang-kami') }}" wire:navigate class="rounded-lg bg-zinc-900 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                Tentang Kami
            </a>
            <a href="{{ route('kontak') }}" wire:navigate class="rounded-lg border border-zinc-300 px-5 py-3 text-sm font-semibold text-zinc-900 hover:bg-zinc-50 dark:border-zinc-700 dark:text-white dark:hover:bg-zinc-800">
                Ajukan Kerja Sama
            </a>
        </div>
    </section>

    @if ($this->distribusi)
        <section class="border-t border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950" data-aos="fade-up">
            <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
                <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $this->distribusi->title }}</h2>
                <p class="mt-4 max-w-2xl text-zinc-600 dark:text-zinc-400">{{ $this->distribusi->body }}</p>

                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Gratis Ongkir</p>
                        <p class="mt-1 text-sm text-zinc-800 dark:text-zinc-200">{{ SiteSetting::get('distribution_free_area') }}</p>
                    </div>
                    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Jangkauan Distribusi</p>
                        <p class="mt-1 text-sm text-zinc-800 dark:text-zinc-200">{{ SiteSetting::get('distribution_area') }}</p>
                    </div>
                    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Jadwal Pengiriman</p>
                        <p class="mt-1 text-sm text-zinc-800 dark:text-zinc-200">Senin – Sabtu</p>
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if ($this->featuredProducts->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6" data-aos="fade-up">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">Produk Unggulan</h2>
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                        Contoh sebagian katalog kami — foto produk resmi menyusul.
                    </p>
                </div>
                <a href="{{ route('produk') }}" wire:navigate class="text-sm font-semibold text-amber-600 hover:text-amber-700">
                    Lihat Semua Produk &rarr;
                </a>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($this->featuredProducts as $product)
                    <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                        <div class="flex aspect-square items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-950 dark:text-amber-400">
                            <span class="text-3xl font-bold">{{ mb_substr($product->name, 0, 1) }}</span>
                        </div>
                        <p class="mt-3 text-sm font-semibold text-zinc-900 dark:text-white">{{ $product->name }}</p>
                        <span class="mt-1 inline-block rounded-full bg-zinc-100 px-2 py-0.5 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                            {{ $product->category->label() }}
                        </span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
