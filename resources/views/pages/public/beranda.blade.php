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
        return Product::where('is_active', true)->where('is_featured', true)->with('images')->take(4)->get();
    }

    /**
     * @return array<int, string>
     */
    #[Computed]
    public function distributionRegions(): array
    {
        return ['Jawa Timur', 'Sulawesi', 'Kalimantan', 'Timor Leste', 'Maluku', 'NTT', 'NTB', 'Bali'];
    }

    /**
     * @return array<int, array{name: string, desc: string}>
     */
    #[Computed]
    public function fleet(): array
    {
        return [
            ['name' => 'Truk Box Besar', 'desc' => 'Untuk pengiriman volume besar ke luar kota'],
            ['name' => 'Truk Box Kecil', 'desc' => 'Untuk pengiriman rutin dalam kota'],
        ];
    }

    /**
     * @return array<int, array{title: string, desc: string}>
     */
    #[Computed]
    public function differentiators(): array
    {
        return [
            ['title' => 'Distributor Resmi', 'desc' => 'Mitra resmi Yummy Bites & beras impor dari berbagai negara, bukan reseller.'],
            ['title' => 'Armada Sendiri', 'desc' => 'Gratis ongkir untuk area Surabaya Raya tanpa bergantung ekspedisi pihak ketiga.'],
            ['title' => '355+ Outlet', 'desc' => 'Jaringan distribusi aktif di 8 wilayah Indonesia bagian timur.'],
            ['title' => 'Berpengalaman', 'desc' => 'Melayani mitra bisnis sejak 2019 dengan 18 tenaga ahli distribusi.'],
        ];
    }

    /**
     * @return array<int, string>
     */
    #[Computed]
    public function galleryPlaceholders(): array
    {
        return ['Gudang Kami', 'Tim Kami', 'Proses Packing', 'Booth Pameran', 'Armada Pengiriman'];
    }
}; ?>

<div>
    {{-- Hero: asymmetric split — headline left, "shipping manifest" fact card right --}}
    <section class="relative overflow-hidden bg-brand-900">
        <span class="pointer-events-none absolute -right-32 -top-32 h-96 w-96 rounded-full bg-brand-600/20 blur-3xl"></span>

        <div class="relative mx-auto grid max-w-6xl gap-12 px-4 pb-24 pt-16 sm:px-6 sm:pt-20 lg:grid-cols-5 lg:items-center lg:gap-10">
            <div class="lg:col-span-3" data-aos="fade-up">
                <h1 class="heading-display font-display max-w-xl text-5xl font-bold text-white sm:text-6xl lg:text-7xl">
                    {{ $this->hero?->title }}
                </h1>

                <p class="mt-6 max-w-lg text-lg leading-relaxed text-brand-100">
                    {{ $this->hero?->body }}
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('tentang-kami') }}" wire:navigate class="rounded-full bg-white px-6 py-3.5 text-sm font-bold text-brand-800 shadow-lg shadow-black/10 transition hover:bg-brand-50">
                        Tentang Kami
                    </a>
                    <a href="{{ route('kontak') }}" wire:navigate class="rounded-full border border-white/25 px-6 py-3.5 text-sm font-bold text-white transition hover:bg-white/10">
                        Ajukan Kerja Sama
                    </a>
                </div>
            </div>

            <div class="lg:col-span-2" data-aos="zoom-in" data-aos-delay="150">
                <div class="relative -rotate-2 rounded-2xl border border-brand-200 bg-parchment-50 p-6 text-brand-900 shadow-2xl shadow-black/40 sm:p-7">
                    <div class="flex items-start justify-between gap-4 border-b border-dashed border-brand-300 pb-4">
                        <div>
                            <p class="font-display text-xl font-bold tracking-tight">Manifest Distribusi</p>
                            <p class="mt-0.5 text-xs text-brand-600">PT Mustika Makmur Abadi &middot; Sidoarjo</p>
                        </div>
                        <span class="flex h-14 w-14 shrink-0 rotate-6 items-center justify-center rounded-full border-2 border-stamp-500 text-center text-[9px] leading-tight font-bold tracking-wide text-stamp-500 uppercase">
                            Resmi<br>{{ SiteSetting::get('established_year') }}
                        </span>
                    </div>

                    <dl class="mt-5 grid grid-cols-3 gap-4">
                        <x-stat-counter :target="(int) SiteSetting::get('outlets_count', 0)" suffix="+" label="Outlet" number-class="font-display text-3xl text-brand-900" label-class="text-xs text-brand-600" />
                        <x-stat-counter :target="(int) SiteSetting::get('staff_count', 0)" label="Tenaga Ahli" number-class="font-display text-3xl text-brand-900" label-class="text-xs text-brand-600" />
                        <x-stat-counter :target="8" label="Wilayah" number-class="font-display text-3xl text-brand-900" label-class="text-xs text-brand-600" />
                    </dl>

                    <div class="mt-5 flex items-center justify-between border-t border-dashed border-brand-300 pt-4 text-xs text-brand-600">
                        <span>Armada: 2 unit truk box</span>
                        <span>Berdiri {{ SiteSetting::get('established_year') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <x-section-wave color="text-white dark:text-zinc-900" />
    </section>

    {{-- Sarana Distribusi + Armada --}}
    @if ($this->distribusi)
        <section class="bg-white pt-16 dark:bg-zinc-900">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="grid gap-10 lg:grid-cols-5 lg:gap-16">
                    <div class="lg:col-span-2" data-aos="fade-up">
                        <h2 class="heading-display font-display text-3xl font-bold text-zinc-900 sm:text-4xl dark:text-white">{{ $this->distribusi->title }}</h2>
                        <p class="mt-4 text-zinc-600 dark:text-zinc-400">{{ $this->distribusi->body }}</p>

                        <dl class="mt-8 space-y-5 border-t border-zinc-100 pt-6 dark:border-zinc-800">
                            <div>
                                <dt class="text-xs font-semibold tracking-wide text-zinc-400">Gratis Ongkir</dt>
                                <dd class="mt-1 text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ SiteSetting::get('distribution_free_area') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold tracking-wide text-zinc-400">Jadwal Pengiriman</dt>
                                <dd class="mt-1 text-sm font-medium text-zinc-800 dark:text-zinc-200">Senin – Sabtu</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="lg:col-span-3" data-aos="fade-up" data-aos-delay="100">
                        <p class="font-display text-lg font-bold text-zinc-900 dark:text-white">Armada Kami</p>
                        <div class="mt-2 divide-y divide-zinc-100 border-y border-zinc-100 dark:divide-zinc-800 dark:border-zinc-800">
                            @foreach ($this->fleet as $vehicle)
                                <div class="flex items-center gap-5 py-5">
                                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-900/50 dark:text-brand-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V6a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v10.5M3 16.5h11M3 16.5H1.5M14 16.5h2.5m0 0H20a1 1 0 0 0 1-1v-3.4a1 1 0 0 0-.29-.7l-2.6-2.6a1 1 0 0 0-.7-.3H16.5m0 8v-8" />
                                            <circle cx="6.5" cy="18" r="1.75" />
                                            <circle cx="17.5" cy="18" r="1.75" />
                                        </svg>
                                    </span>
                                    <div>
                                        <p class="font-bold text-zinc-900 dark:text-white">{{ $vehicle['name'] }}</p>
                                        <p class="mt-0.5 text-sm text-zinc-600 dark:text-zinc-400">{{ $vehicle['desc'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Jangkauan Distribusi: routing list, not a badge grid --}}
                <div class="mt-16 border-t border-zinc-100 py-16 dark:border-zinc-800" data-aos="fade-up">
                    <h2 class="heading-display font-display text-3xl font-bold text-zinc-900 sm:text-4xl dark:text-white">Jangkauan Distribusi</h2>
                    <p class="mt-3 max-w-xl text-zinc-600 dark:text-zinc-400">
                        Dari pusat distribusi di <span class="font-semibold text-brand-700 dark:text-brand-400">Sidoarjo</span>, produk kami menjangkau outlet modern & tradisional di:
                    </p>

                    <div class="route-line mt-6 text-brand-200 dark:text-brand-800"></div>

                    <div class="mt-6 grid grid-cols-2 gap-x-8 gap-y-4 sm:grid-cols-4">
                        @foreach ($this->distributionRegions as $region)
                            <div class="flex items-center gap-2.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4.5-4.2-7-7.9-7-11a7 7 0 1 1 14 0c0 3.1-2.5 6.8-7 11Z" />
                                    <circle cx="12" cy="10" r="2.5" />
                                </svg>
                                <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $region }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Kenapa Memilih Kami: manifest-style spec list --}}
    <section class="border-t border-zinc-100 bg-zinc-50 py-16 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="mx-auto max-w-6xl px-4 sm:px-6" data-aos="fade-up">
            <h2 class="heading-display font-display text-3xl font-bold text-zinc-900 sm:text-4xl dark:text-white">Kenapa Memilih Kami</h2>

            <div class="mt-8 divide-y divide-zinc-200 border-y-2 border-zinc-900 dark:divide-zinc-800 dark:border-white">
                @foreach ($this->differentiators as $item)
                    <div class="grid gap-1.5 py-6 sm:grid-cols-4 sm:items-center sm:gap-6">
                        <p class="font-display text-xl font-bold text-zinc-900 dark:text-white">{{ $item['title'] }}</p>
                        <p class="text-zinc-600 sm:col-span-3 dark:text-zinc-400">{{ $item['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Produk Unggulan --}}
    @if ($this->featuredProducts->isNotEmpty())
        <section class="py-16">
            <div class="mx-auto max-w-6xl px-4 sm:px-6" data-aos="fade-up">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <h2 class="heading-display font-display text-3xl font-bold text-zinc-900 sm:text-4xl dark:text-white">Produk Unggulan</h2>
                    <a href="{{ route('produk') }}" wire:navigate class="text-sm font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                        Lihat Semua Produk
                    </a>
                </div>

                <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($this->featuredProducts as $product)
                        <div class="overflow-hidden rounded-xl border border-zinc-100 bg-white transition hover:border-brand-200 dark:border-zinc-800 dark:bg-zinc-900">
                            <div class="flex aspect-square items-center justify-center bg-zinc-50 dark:bg-zinc-800">
                                @if ($product->images->isNotEmpty())
                                    <img src="{{ $product->images->first()->url() }}" alt="{{ $product->images->first()->alt_text ?? $product->name }}" class="h-full w-full object-contain p-4">
                                @else
                                    <span class="font-display text-4xl text-brand-300">{{ mb_substr($product->name, 0, 1) }}</span>
                                @endif
                            </div>
                            <div class="p-4">
                                <p class="text-sm font-bold text-zinc-900 dark:text-white">{{ $product->name }}</p>
                                <span class="mt-1.5 inline-block rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                    {{ $product->category->label() }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Galeri --}}
    <section class="border-t border-zinc-100 bg-zinc-50 py-16 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="mx-auto max-w-6xl px-4 sm:px-6" data-aos="fade-up">
            <h2 class="heading-display font-display text-3xl font-bold text-zinc-900 sm:text-4xl dark:text-white">Galeri</h2>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Sekilas aktivitas dan fasilitas kami — foto asli menyusul.</p>

            <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3">
                @foreach ($this->galleryPlaceholders as $index => $item)
                    <div class="flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-brand-200 bg-white text-center dark:border-brand-800 dark:bg-zinc-900 {{ $index === 0 ? 'col-span-2 aspect-[2/1] sm:col-span-1 sm:row-span-2 sm:aspect-auto' : 'aspect-square' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-brand-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="5" width="18" height="14" rx="2" />
                            <circle cx="8.5" cy="10" r="1.5" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 15-5-5-9 9" />
                        </svg>
                        <span class="px-3 text-xs font-semibold text-zinc-700 dark:text-zinc-300">{{ $item }}</span>
                        <span class="text-[10px] text-zinc-400">Foto segera hadir</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA Penutup --}}
    <section class="bg-brand-900">
        <div class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6" data-aos="fade-up">
            <h2 class="heading-display font-display text-3xl font-bold text-white sm:text-4xl">Tertarik Menjadi Mitra Kami?</h2>
            <p class="mt-4 text-brand-100">Hubungi kami untuk informasi kerja sama distribusi Yummy Bites dan beras impor.</p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="{{ route('kontak') }}" wire:navigate class="rounded-full bg-white px-7 py-3.5 text-sm font-bold text-brand-800 shadow-lg shadow-black/10 transition hover:bg-brand-50">
                    Hubungi Kami
                </a>
                <a href="{{ route('produk') }}" wire:navigate class="rounded-full border border-white/25 px-7 py-3.5 text-sm font-bold text-white transition hover:bg-white/10">
                    Lihat Produk
                </a>
            </div>
        </div>
    </section>
</div>
