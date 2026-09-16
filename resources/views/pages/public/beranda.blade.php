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
            ['title' => 'Distributor Resmi', 'desc' => 'Yummy Bites & beras impor dari berbagai negara'],
            ['title' => 'Armada Sendiri', 'desc' => 'Gratis ongkir untuk area Surabaya Raya'],
            ['title' => '355+ Outlet', 'desc' => 'Tersebar di 8 wilayah Indonesia'],
            ['title' => 'Berpengalaman', 'desc' => 'Melayani mitra sejak 2019'],
        ];
    }

    /**
     * @return array<int, string>
     */
    #[Computed]
    public function galleryPlaceholders(): array
    {
        return ['Gudang Kami', 'Tim Kami', 'Proses Packing', 'Booth Pameran'];
    }
}; ?>

<div>
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-700 via-brand-800 to-brand-900">
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <span class="animate-bubble-float absolute left-[8%] top-[20%] h-16 w-16 rounded-full bg-white/10 blur-sm"></span>
            <span class="animate-bubble-float absolute left-[75%] top-[15%] h-10 w-10 rounded-full bg-white/10 blur-sm [animation-delay:1.5s]"></span>
            <span class="animate-bubble-float absolute left-[60%] top-[60%] h-24 w-24 rounded-full bg-white/5 blur-md [animation-delay:0.8s]"></span>
            <span class="animate-bubble-float absolute left-[15%] top-[70%] h-8 w-8 rounded-full bg-white/10 blur-sm [animation-delay:2.2s]"></span>
        </div>

        <div class="relative mx-auto max-w-6xl px-4 py-20 sm:px-6 sm:py-28" data-aos="fade-up">
            <div class="flex flex-wrap gap-3 text-sm font-medium">
                <span class="rounded-full bg-white/10 px-3 py-1 text-brand-100 ring-1 ring-white/20">{{ SiteSetting::get('outlets_count') }}+ outlet</span>
                <span class="rounded-full bg-white/10 px-3 py-1 text-brand-100 ring-1 ring-white/20">{{ SiteSetting::get('staff_count') }} tenaga ahli</span>
                <span class="rounded-full bg-white/10 px-3 py-1 text-brand-100 ring-1 ring-white/20">Sejak {{ SiteSetting::get('established_year') }}</span>
            </div>

            <h1 class="mt-6 max-w-3xl text-4xl font-bold tracking-tight text-white sm:text-5xl">
                {{ $this->hero?->title }}
            </h1>

            <p class="mt-6 max-w-2xl text-lg text-brand-100">
                {{ $this->hero?->body }}
            </p>

            <div class="mt-8 flex flex-wrap gap-4">
                <a href="{{ route('tentang-kami') }}" wire:navigate class="rounded-lg bg-white px-5 py-3 text-sm font-semibold text-brand-800 transition hover:bg-brand-50">
                    Tentang Kami
                </a>
                <a href="{{ route('kontak') }}" wire:navigate class="rounded-lg border border-white/30 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                    Ajukan Kerja Sama
                </a>
            </div>

            <div class="mt-14 grid grid-cols-3 gap-6 border-t border-white/10 pt-8 sm:max-w-lg">
                <x-stat-counter :target="(int) SiteSetting::get('outlets_count', 0)" suffix="+" label="Outlet" number-class="text-white" label-class="text-brand-200" />
                <x-stat-counter :target="(int) SiteSetting::get('staff_count', 0)" label="Tenaga Ahli" number-class="text-white" label-class="text-brand-200" />
                <x-stat-counter :target="8" label="Wilayah" number-class="text-white" label-class="text-brand-200" />
            </div>
        </div>

        <x-section-wave color="text-white dark:text-zinc-900" />
    </section>

    {{-- Sarana Distribusi + Armada --}}
    @if ($this->distribusi)
        <section class="bg-white dark:bg-zinc-900" data-aos="fade-up">
            <div class="mx-auto max-w-6xl px-4 pb-16 sm:px-6">
                <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $this->distribusi->title }}</h2>
                <p class="mt-4 max-w-2xl text-zinc-600 dark:text-zinc-400">{{ $this->distribusi->body }}</p>

                <div class="mt-8 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-xl border border-zinc-200 bg-white p-5 transition hover:-translate-y-1 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900">
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Gratis Ongkir</p>
                        <p class="mt-1 text-sm text-zinc-800 dark:text-zinc-200">{{ SiteSetting::get('distribution_free_area') }}</p>
                    </div>
                    <div class="rounded-xl border border-zinc-200 bg-white p-5 transition hover:-translate-y-1 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900">
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Jangkauan Distribusi</p>
                        <p class="mt-1 text-sm text-zinc-800 dark:text-zinc-200">{{ SiteSetting::get('distribution_area') }}</p>
                    </div>
                    <div class="rounded-xl border border-zinc-200 bg-white p-5 transition hover:-translate-y-1 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900">
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Jadwal Pengiriman</p>
                        <p class="mt-1 text-sm text-zinc-800 dark:text-zinc-200">Senin – Sabtu</p>
                    </div>
                </div>

                {{-- Armada Kami --}}
                <div class="mt-10">
                    <p class="text-sm font-semibold uppercase tracking-wide text-brand-600 dark:text-brand-400">Armada Kami</p>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        @foreach ($this->fleet as $index => $vehicle)
                            <div
                                class="flex items-start gap-4 rounded-xl border border-zinc-200 bg-brand-50/50 p-5 transition hover:-translate-y-1 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-800/50"
                                data-aos="fade-up"
                                data-aos-delay="{{ $index * 100 }}"
                            >
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-600 dark:bg-brand-900 dark:text-brand-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V6a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v10.5M3 16.5h11M3 16.5H1.5M14 16.5h2.5m0 0H20a1 1 0 0 0 1-1v-3.4a1 1 0 0 0-.29-.7l-2.6-2.6a1 1 0 0 0-.7-.3H16.5m0 8v-8" />
                                        <circle cx="6.5" cy="18" r="1.75" />
                                        <circle cx="17.5" cy="18" r="1.75" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="font-semibold text-zinc-900 dark:text-white">{{ $vehicle['name'] }}</p>
                                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $vehicle['desc'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Jangkauan Distribusi --}}
    <section class="border-t border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950" data-aos="fade-up">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">Jangkauan Distribusi</h2>
            <p class="mt-2 max-w-2xl text-zinc-600 dark:text-zinc-400">
                Produk kami telah menjangkau outlet modern & tradisional di berbagai wilayah Indonesia.
            </p>

            <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ($this->distributionRegions as $region)
                    <div class="flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-3 transition hover:-translate-y-1 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4.5-4.2-7-7.9-7-11a7 7 0 1 1 14 0c0 3.1-2.5 6.8-7 11Z" />
                            <circle cx="12" cy="10" r="2.5" />
                        </svg>
                        <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $region }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Kenapa Memilih Kami --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6" data-aos="fade-up">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">Kenapa Memilih Kami</h2>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($this->differentiators as $item)
                <div class="rounded-xl border border-zinc-200 p-5 text-center transition hover:-translate-y-1 hover:shadow-lg dark:border-zinc-800" data-aos="zoom-in">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand-100 text-brand-600 dark:bg-brand-900 dark:text-brand-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </span>
                    <p class="mt-4 font-semibold text-zinc-900 dark:text-white">{{ $item['title'] }}</p>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $item['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Produk Unggulan --}}
    @if ($this->featuredProducts->isNotEmpty())
        <section class="border-t border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950" data-aos="fade-up">
            <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">Produk Unggulan</h2>
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                            Contoh sebagian katalog kami — foto produk resmi menyusul.
                        </p>
                    </div>
                    <a href="{{ route('produk') }}" wire:navigate class="text-sm font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                        Lihat Semua Produk &rarr;
                    </a>
                </div>

                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($this->featuredProducts as $product)
                        <div class="rounded-xl border border-zinc-200 bg-white p-4 transition hover:-translate-y-1 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900">
                            <div class="flex aspect-square items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-900 dark:text-brand-300">
                                <span class="text-3xl font-bold">{{ mb_substr($product->name, 0, 1) }}</span>
                            </div>
                            <p class="mt-3 text-sm font-semibold text-zinc-900 dark:text-white">{{ $product->name }}</p>
                            <span class="mt-1 inline-block rounded-full bg-zinc-100 px-2 py-0.5 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                {{ $product->category->label() }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Galeri --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6" data-aos="fade-up">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">Galeri</h2>
        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Sekilas aktivitas dan fasilitas kami.</p>

        <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-4">
            @foreach ($this->galleryPlaceholders as $item)
                <div class="flex aspect-square flex-col items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-brand-100 to-brand-300 text-center dark:from-brand-800 dark:to-brand-900">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-brand-600 dark:text-brand-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="5" width="18" height="14" rx="2" />
                        <circle cx="8.5" cy="10" r="1.5" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 15-5-5-9 9" />
                    </svg>
                    <span class="px-3 text-xs font-medium text-brand-700 dark:text-brand-200">{{ $item }}</span>
                    <span class="text-[10px] text-brand-600/70 dark:text-brand-300/70">Foto segera hadir</span>
                </div>
            @endforeach
        </div>
    </section>

    {{-- CTA Penutup --}}
    <section class="bg-brand-700">
        <div class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6" data-aos="fade-up">
            <h2 class="text-2xl font-bold text-white sm:text-3xl">Tertarik Menjadi Mitra Kami?</h2>
            <p class="mt-3 text-brand-100">Hubungi kami untuk informasi kerja sama distribusi Yummy Bites dan beras impor.</p>
            <div class="mt-6 flex flex-wrap justify-center gap-4">
                <a href="{{ route('kontak') }}" wire:navigate class="rounded-lg bg-white px-6 py-3 text-sm font-semibold text-brand-800 transition hover:bg-brand-50">
                    Hubungi Kami
                </a>
                <a href="{{ route('produk') }}" wire:navigate class="rounded-lg border border-white/30 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                    Lihat Produk
                </a>
            </div>
        </div>
    </section>
</div>
