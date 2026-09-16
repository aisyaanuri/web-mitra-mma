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
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-700 via-brand-800 to-brand-900">
        <div class="absolute inset-0 bg-dot-grid text-white/[0.07]"></div>
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <span class="animate-blob-float absolute -right-24 -top-24 h-96 w-96 rounded-full bg-brand-400/20 blur-3xl"></span>
            <span class="animate-blob-float absolute -bottom-32 -left-16 h-80 w-80 rounded-full bg-brand-300/10 blur-3xl [animation-delay:3s]"></span>
            <span class="animate-bubble-float absolute left-[8%] top-[22%] h-16 w-16 rounded-full bg-white/10 blur-sm"></span>
            <span class="animate-bubble-float absolute left-[78%] top-[18%] h-10 w-10 rounded-full bg-white/10 blur-sm [animation-delay:1.5s]"></span>
            <span class="animate-bubble-float absolute left-[65%] top-[65%] h-6 w-6 rounded-full bg-white/10 blur-sm [animation-delay:2.2s]"></span>
        </div>

        <div class="relative mx-auto max-w-6xl px-4 pb-28 pt-16 sm:px-6 sm:pt-20" data-aos="fade-up">
            <div class="flex flex-wrap gap-2.5 text-xs font-semibold">
                <span class="rounded-full bg-white/10 px-3.5 py-1.5 text-brand-100 ring-1 ring-white/20 backdrop-blur">{{ SiteSetting::get('outlets_count') }}+ OUTLET</span>
                <span class="rounded-full bg-white/10 px-3.5 py-1.5 text-brand-100 ring-1 ring-white/20 backdrop-blur">{{ SiteSetting::get('staff_count') }} TENAGA AHLI</span>
                <span class="rounded-full bg-white/10 px-3.5 py-1.5 text-brand-100 ring-1 ring-white/20 backdrop-blur">SEJAK {{ SiteSetting::get('established_year') }}</span>
            </div>

            <h1 class="heading-display mt-7 max-w-3xl text-5xl font-extrabold text-white sm:text-6xl lg:text-7xl">
                {{ $this->hero?->title }}
            </h1>

            <p class="mt-7 max-w-xl text-lg leading-relaxed text-brand-100">
                {{ $this->hero?->body }}
            </p>

            <div class="mt-9 flex flex-wrap gap-3">
                <a href="{{ route('tentang-kami') }}" wire:navigate class="group inline-flex items-center gap-2 rounded-full bg-white px-6 py-3.5 text-sm font-bold text-brand-800 shadow-lg shadow-black/10 transition hover:bg-brand-50">
                    Tentang Kami
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                </a>
                <a href="{{ route('kontak') }}" wire:navigate class="inline-flex items-center gap-2 rounded-full border border-white/25 px-6 py-3.5 text-sm font-bold text-white transition hover:bg-white/10">
                    Ajukan Kerja Sama
                </a>
            </div>
        </div>

        {{-- Stat strip: overlaps the wave divider for depth --}}
        <div class="relative z-10 mx-auto -mb-10 max-w-6xl px-4 sm:px-6" data-aos="fade-up" data-aos-delay="150">
            <div class="grid grid-cols-3 divide-x divide-zinc-100 rounded-2xl bg-white shadow-xl shadow-brand-900/10 dark:divide-zinc-800 dark:bg-zinc-900">
                <x-stat-counter :target="(int) SiteSetting::get('outlets_count', 0)" suffix="+" label="Outlet" class="px-4 py-6 text-center sm:px-8" />
                <x-stat-counter :target="(int) SiteSetting::get('staff_count', 0)" label="Tenaga Ahli" class="px-4 py-6 text-center sm:px-8" />
                <x-stat-counter :target="8" label="Wilayah" class="px-4 py-6 text-center sm:px-8" />
            </div>
        </div>

        <x-section-wave color="text-white dark:text-zinc-900" />
    </section>

    {{-- Sarana Distribusi + Armada --}}
    @if ($this->distribusi)
        <section class="bg-white pt-20 dark:bg-zinc-900">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="grid gap-10 lg:grid-cols-5 lg:gap-16">
                    <div class="lg:col-span-2" data-aos="fade-up">
                        <p class="text-xs font-bold tracking-widest text-brand-600 uppercase dark:text-brand-400">Distribusi</p>
                        <h2 class="heading-display mt-3 text-3xl font-extrabold text-zinc-900 sm:text-4xl dark:text-white">{{ $this->distribusi->title }}</h2>
                        <p class="mt-4 text-zinc-600 dark:text-zinc-400">{{ $this->distribusi->body }}</p>

                        <dl class="mt-8 space-y-5 border-t border-zinc-100 pt-6 dark:border-zinc-800">
                            <div>
                                <dt class="text-xs font-semibold tracking-wide text-zinc-400 uppercase">Gratis Ongkir</dt>
                                <dd class="mt-1 text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ SiteSetting::get('distribution_free_area') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold tracking-wide text-zinc-400 uppercase">Jadwal Pengiriman</dt>
                                <dd class="mt-1 text-sm font-medium text-zinc-800 dark:text-zinc-200">Senin – Sabtu</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="lg:col-span-3" data-aos="fade-up" data-aos-delay="100">
                        <p class="text-sm font-bold text-zinc-900 dark:text-white">Armada Kami</p>
                        <div class="mt-4 space-y-4">
                            @foreach ($this->fleet as $index => $vehicle)
                                <div class="group flex items-center gap-5 rounded-2xl border border-zinc-100 bg-gradient-to-br from-brand-50/80 to-white p-5 transition hover:border-brand-200 hover:shadow-lg hover:shadow-brand-900/5 dark:border-zinc-800 dark:from-zinc-800/50 dark:to-zinc-900">
                                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-brand-600 text-white shadow-md shadow-brand-600/30 transition group-hover:scale-105">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
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

                {{-- Jangkauan Distribusi --}}
                <div class="mt-16 border-t border-zinc-100 py-16 dark:border-zinc-800" data-aos="fade-up">
                    <p class="text-xs font-bold tracking-widest text-brand-600 uppercase dark:text-brand-400">Jangkauan</p>
                    <h2 class="heading-display mt-3 text-3xl font-extrabold text-zinc-900 sm:text-4xl dark:text-white">Jangkauan Distribusi</h2>
                    <p class="mt-3 max-w-xl text-zinc-600 dark:text-zinc-400">
                        Produk kami telah menjangkau outlet modern & tradisional di berbagai wilayah Indonesia.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        @foreach ($this->distributionRegions as $region)
                            <div class="group flex items-center gap-2 rounded-full border border-zinc-200 bg-white px-5 py-2.5 transition hover:border-brand-300 hover:bg-brand-50 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:bg-brand-900/30">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4.5-4.2-7-7.9-7-11a7 7 0 1 1 14 0c0 3.1-2.5 6.8-7 11Z" />
                                    <circle cx="12" cy="10" r="2.5" />
                                </svg>
                                <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $region }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Kenapa Memilih Kami --}}
    <section class="border-t border-zinc-100 bg-zinc-50 py-20 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="mx-auto max-w-6xl px-4 sm:px-6" data-aos="fade-up">
            <p class="text-xs font-bold tracking-widest text-brand-600 uppercase dark:text-brand-400">Keunggulan</p>
            <h2 class="heading-display mt-3 max-w-lg text-3xl font-extrabold text-zinc-900 sm:text-4xl dark:text-white">Kenapa Memilih Kami</h2>
        </div>

        <div class="mx-auto mt-10 grid max-w-6xl gap-px overflow-hidden rounded-2xl bg-zinc-200 px-4 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 dark:bg-zinc-800">
            @foreach ($this->differentiators as $index => $item)
                <div class="group relative bg-white p-7 transition hover:bg-brand-600 dark:bg-zinc-900" data-aos="fade-up" data-aos-delay="{{ $index * 75 }}">
                    <span class="text-4xl font-black text-zinc-100 transition group-hover:text-white/20 dark:text-zinc-800">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                    <p class="mt-4 font-bold text-zinc-900 transition group-hover:text-white dark:text-white">{{ $item['title'] }}</p>
                    <p class="mt-2 text-sm text-zinc-600 transition group-hover:text-brand-50 dark:text-zinc-400">{{ $item['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Produk Unggulan --}}
    @if ($this->featuredProducts->isNotEmpty())
        <section class="py-20">
            <div class="mx-auto max-w-6xl px-4 sm:px-6" data-aos="fade-up">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold tracking-widest text-brand-600 uppercase dark:text-brand-400">Katalog</p>
                        <h2 class="heading-display mt-3 text-3xl font-extrabold text-zinc-900 sm:text-4xl dark:text-white">Produk Unggulan</h2>
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                            Contoh sebagian katalog kami — foto produk resmi menyusul.
                        </p>
                    </div>
                    <a href="{{ route('produk') }}" wire:navigate class="inline-flex items-center gap-1.5 text-sm font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                        Lihat Semua Produk
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                    </a>
                </div>

                <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($this->featuredProducts as $product)
                        <div class="group overflow-hidden rounded-2xl border border-zinc-100 bg-white transition hover:-translate-y-1 hover:shadow-xl hover:shadow-zinc-900/5 dark:border-zinc-800 dark:bg-zinc-900">
                            <div class="flex aspect-square items-center justify-center bg-gradient-to-br from-brand-50 to-brand-100 text-brand-600 transition group-hover:scale-[1.03] dark:from-brand-900 dark:to-brand-800 dark:text-brand-300">
                                <span class="text-4xl font-black">{{ mb_substr($product->name, 0, 1) }}</span>
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

    {{-- Galeri (masonry-style, varied tile heights) --}}
    <section class="border-t border-zinc-100 bg-zinc-50 py-20 dark:border-zinc-800 dark:bg-zinc-950">
        <div class="mx-auto max-w-6xl px-4 sm:px-6" data-aos="fade-up">
            <p class="text-xs font-bold tracking-widest text-brand-600 uppercase dark:text-brand-400">Dokumentasi</p>
            <h2 class="heading-display mt-3 text-3xl font-extrabold text-zinc-900 sm:text-4xl dark:text-white">Galeri</h2>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Sekilas aktivitas dan fasilitas kami.</p>

            <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3">
                @foreach ($this->galleryPlaceholders as $index => $item)
                    <div class="flex flex-col items-center justify-center gap-2 rounded-2xl bg-gradient-to-br from-brand-100 to-brand-300 text-center dark:from-brand-800 dark:to-brand-900 {{ $index === 0 ? 'col-span-2 aspect-[2/1] sm:col-span-1 sm:aspect-square sm:row-span-2 sm:aspect-auto' : 'aspect-square' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-brand-600 dark:text-brand-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="5" width="18" height="14" rx="2" />
                            <circle cx="8.5" cy="10" r="1.5" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 15-5-5-9 9" />
                        </svg>
                        <span class="px-3 text-xs font-semibold text-brand-700 dark:text-brand-200">{{ $item }}</span>
                        <span class="text-[10px] text-brand-600/70 dark:text-brand-300/70">Foto segera hadir</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA Penutup --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-600 to-brand-800">
        <div class="absolute inset-0 bg-dot-grid text-white/[0.06]"></div>
        <div class="relative mx-auto max-w-4xl px-4 py-20 text-center sm:px-6" data-aos="fade-up">
            <h2 class="heading-display text-3xl font-extrabold text-white sm:text-4xl">Tertarik Menjadi Mitra Kami?</h2>
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
