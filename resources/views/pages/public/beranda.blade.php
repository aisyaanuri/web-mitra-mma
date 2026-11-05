<?php

use App\Enums\ProductCategory;
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
        SEOMeta::setDescription('Distributor resmi Yummy Bites dan beras premium di Sidoarjo, melayani 355+ outlet di Indonesia bagian timur.');
        OpenGraph::setTitle('PT Mustika Makmur Abadi');
        OpenGraph::setDescription('Distributor resmi Yummy Bites dan beras premium di Sidoarjo.');
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
        return Product::where('is_active', true)->where('is_featured', true)->with('images')->take(8)->get();
    }

    #[Computed]
    public function distributionRegions(): array
    {
        return ['Jawa Timur', 'Sulawesi', 'Kalimantan', 'Timor Leste', 'Maluku', 'NTT', 'NTB', 'Bali'];
    }

    #[Computed]
    public function yummyBitesCount(): int
    {
        return Product::where('is_active', true)->where('category', ProductCategory::YummyBites->value)->count();
    }

    #[Computed]
    public function berasImporCount(): int
    {
        return Product::where('is_active', true)->where('category', ProductCategory::BerasImpor->value)->count();
    }
}; ?>

<div>
    {{-- ============================================================
         SECTION 1: HERO — Full-width illustration background
         Overlay gradien gelap di kiri agar teks terbaca.
         Stats badge floating di atas gambar (seperti Gojek).
         ============================================================ --}}
    <section class="relative min-h-[520px] overflow-hidden sm:min-h-[600px] lg:min-h-[680px]">
        <img
            src="/images/illustrations/hero-distribusi.webp?v={{ file_exists(public_path('images/illustrations/hero-distribusi.webp')) ? filemtime(public_path('images/illustrations/hero-distribusi.webp')) : '2' }}"
            alt="Gedung operasional PT Mustika Makmur Abadi di Kawasan Industri SIRIE, Sidoarjo"
            class="absolute inset-0 h-full w-full object-cover object-right"
            fetchpriority="high"
        >
        {{-- Gradient kuat di kiri agar teks terbaca di atas foto nyata --}}
        <div class="absolute inset-0 bg-gradient-to-r from-slate-900/90 via-slate-900/60 to-slate-900/10"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/40 via-transparent to-transparent"></div>

        <div class="relative z-10 mx-auto flex max-w-6xl flex-col justify-center px-4 py-20 sm:px-6 sm:py-28 lg:py-32">
            <div class="max-w-xl reveal">
                <h1 class="heading-display font-display text-4xl font-extrabold text-white sm:text-5xl lg:text-6xl">
                    {{ $this->hero?->title ?? 'Distributor Resmi Yummy Bites & Beras Premium' }}
                </h1>
                <p class="mt-5 max-w-md text-lg leading-relaxed text-white/85">
                    {{ $this->hero?->body ?? 'Menyuplai 355+ outlet di 8 wilayah Indonesia bagian timur, langsung dari gudang di Sidoarjo.' }}
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('produk') }}" wire:navigate class="rounded-xl bg-sun-500 px-7 py-3.5 text-sm font-extrabold text-slate-900 shadow-lg shadow-sun-500/30 transition hover:bg-sun-400 hover:shadow-xl focus:ring-2 focus:ring-sun-300 focus:ring-offset-2 focus:ring-offset-brand-900 focus:outline-none">
                        Lihat Katalog Produk
                    </a>
                    <a href="{{ route('kontak') }}" wire:navigate class="rounded-xl border-2 border-white/40 bg-white/10 px-7 py-3.5 text-sm font-extrabold text-white backdrop-blur-sm transition hover:border-white/70 hover:bg-white/20 focus:ring-2 focus:ring-white/50 focus:ring-offset-2 focus:ring-offset-brand-900 focus:outline-none">
                        Ajukan Kerja Sama
                    </a>
                </div>
            </div>

            {{-- Floating stat badges — integrated into hero --}}
            <div class="mt-12 flex flex-wrap gap-3 reveal">
                <div class="rounded-xl bg-white/15 px-5 py-3 backdrop-blur-md">
                    <p class="font-display text-2xl font-extrabold text-sun-400">{{ SiteSetting::get('established_year', '2019') }}</p>
                    <p class="text-xs font-semibold text-white/70">Tahun Berdiri</p>
                </div>
                <div class="rounded-xl bg-white/15 px-5 py-3 backdrop-blur-md">
                    <p class="font-display text-2xl font-extrabold text-sun-400">{{ SiteSetting::get('outlets_count', '355') }}+</p>
                    <p class="text-xs font-semibold text-white/70">Outlet Aktif</p>
                </div>
                <div class="rounded-xl bg-white/15 px-5 py-3 backdrop-blur-md">
                    <p class="font-display text-2xl font-extrabold text-sun-400">8</p>
                    <p class="text-xs font-semibold text-white/70">Wilayah Distribusi</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         SECTION 2: YUMMY BITES — Split 50/50, illustration left
         Background oranye-kuning di kanan.
         ============================================================ --}}
    <section class="overflow-hidden">
        <div class="grid lg:grid-cols-2">
            <div class="relative min-h-[360px] lg:min-h-[480px]">
                <img
                    src="/images/illustrations/yummy-bites.webp"
                    alt="Ilustrasi ibu dan bayi menikmati snack Yummy Bites"
                    class="absolute inset-0 h-full w-full object-cover"
                    loading="lazy"
                >
            </div>
            <div class="flex flex-col justify-center bg-gradient-to-br from-amber-500 via-amber-600 to-orange-600 px-8 py-14 sm:px-12 lg:px-16 lg:py-20 reveal">
                <span class="inline-block w-fit rounded-lg bg-white/20 px-3.5 py-1 text-xs font-bold tracking-wide text-white border border-white/20">MPASI & Snack Bayi</span>
                <h2 class="heading-display font-display mt-4 text-3xl font-extrabold text-white sm:text-4xl lg:text-5xl">Yummy Bites</h2>
                <p class="mt-4 max-w-md text-base sm:text-lg leading-relaxed text-white/95">
                    Camilan MPASI untuk si kecil usia 6 bulan+ hingga 1 tahun+. {{ $this->yummyBitesCount }} varian produk aktif. Distributor resmi langsung dari principal, bukan reseller.
                </p>
                <div class="mt-8">
                    <a href="{{ route('produk', ['category' => 'yummy_bites']) }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl bg-white px-7 py-3.5 text-sm font-extrabold text-slate-900 shadow-lg shadow-black/10 transition hover:bg-amber-50 hover:shadow-xl focus:ring-2 focus:ring-white/50 focus:outline-none">
                        Lihat Produk Yummy Bites
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7" /></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         SECTION 3: BERAS PREMIUM — Split 50/50 terbalik (mirrored)
         Background biru tua di kiri, ilustrasi sawah di kanan.
         ============================================================ --}}
    <section class="overflow-hidden">
        <div class="grid lg:grid-cols-2">
            <div class="flex flex-col justify-center bg-gradient-brand px-8 py-14 sm:px-12 lg:order-first lg:px-16 lg:py-20 reveal">
                <span class="inline-block w-fit rounded-lg bg-white/15 px-3.5 py-1 text-xs font-bold tracking-wide text-white border border-white/20">Beras Premium & Lokal</span>
                <h2 class="heading-display font-display mt-4 text-3xl font-extrabold text-white sm:text-4xl lg:text-5xl">Beras Premium</h2>
                <p class="mt-4 max-w-md text-base sm:text-lg leading-relaxed text-white/95">
                    Beras pilihan berkualitas: Beras Jepang, Basmati, Jasmin, Organik, Lokal, dan Lautan Mas. {{ $this->berasImporCount }} produk aktif, untuk kebutuhan rumah tangga, restoran, dan hotel.
                </p>
                <div class="mt-8">
                    <a href="{{ route('produk', ['category' => 'beras_impor']) }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl bg-white px-7 py-3.5 text-sm font-extrabold text-brand-900 shadow-lg shadow-black/10 transition hover:bg-slate-50 hover:shadow-xl focus:ring-2 focus:ring-white/50 focus:outline-none">
                        Lihat Produk Beras
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7" /></svg>
                    </a>
                </div>
            </div>
            <div class="relative min-h-[360px] lg:min-h-[480px]">
                <img
                    src="/images/illustrations/beras-impor.webp"
                    alt="Ilustrasi berbagai jenis beras premium dari PT Mustika Makmur Abadi"
                    class="absolute inset-0 h-full w-full object-cover"
                    loading="lazy"
                >
            </div>
        </div>
    </section>

    {{-- ============================================================
         SECTION 4: SARANA & JANGKAUAN DISTRIBUSI — Clean Light Logistics Architecture
         Harmonious with PT Mustika white & corporate blue palette:
         - Soft slate-50 background with subtle border separation
         - High-contrast crisp white cards with soft elevation
         - Official distributor status & transparent SLA
         ============================================================ --}}
    <section class="border-y border-slate-200/80 bg-slate-50/80 py-16 sm:py-20 dark:border-zinc-800 dark:bg-zinc-900/60">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            {{-- Section Header --}}
            <div class="max-w-3xl reveal">
                <span class="inline-flex items-center gap-2 rounded-lg border border-blue-200/80 bg-blue-50 px-3 py-1 text-xs font-bold tracking-wide text-blue-700 dark:border-blue-800/40 dark:bg-blue-950/40 dark:text-blue-300">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V4.875c0-.621-.504-1.125-1.125-1.125H4.5a1.125 1.125 0 0 0-1.125 1.125v10.5c0 .621.504 1.125 1.125 1.125h.75"/></svg>
                    Sistem Rantai Pasok & Distribusi
                </span>
                <h2 class="heading-display font-display mt-4 text-3xl font-extrabold text-slate-900 sm:text-4xl lg:text-5xl dark:text-white">
                    {{ $this->distribusi?->title ?? 'Sarana & Jangkauan Distribusi' }}
                </h2>
                <p class="mt-4 text-base sm:text-lg leading-relaxed text-slate-600 dark:text-zinc-300">
                    {{ $this->distribusi?->body ?? 'Kami memiliki armada pengiriman sendiri (1 truk box besar dan 1 truk box kecil) yang melayani pengiriman gratis ongkir untuk wilayah Surabaya, Sidoarjo, Gresik, Malang, dan Mojokerto, Senin–Sabtu. Untuk wilayah di luar area tersebut, pengiriman dilakukan melalui ekspedisi rekanan.' }}
                </p>
            </div>

            {{-- 3 Feature Cards --}}
            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 reveal">
                {{-- Card 1: Armada Sendiri --}}
                <div class="group rounded-2xl border border-slate-200/90 bg-white p-7 shadow-xs transition duration-200 hover:-translate-y-0.5 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-800">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V4.875c0-.621-.504-1.125-1.125-1.125H4.5a1.125 1.125 0 0 0-1.125 1.125v10.5c0 .621.504 1.125 1.125 1.125h.75"/></svg>
                    </div>
                    <div class="mt-5">
                        <span class="inline-block rounded-md border border-amber-200/80 bg-amber-50 px-2.5 py-0.5 text-xs font-semibold text-amber-800 dark:border-amber-800/40 dark:bg-amber-950/40 dark:text-amber-300">Armada Sendiri</span>
                        <h3 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">2 Unit Truk Box</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-zinc-400">
                            1 unit truk box besar dan 1 unit truk box kecil untuk pengiriman higienis, terlindung cuaca, dan proteksi produk prima.
                        </p>
                    </div>
                    <div class="mt-6 border-t border-slate-100 pt-4 dark:border-zinc-700/60">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 dark:text-zinc-400">Jadwal Operasional:</span>
                            <span class="font-bold text-amber-700 dark:text-amber-400">Senin – Sabtu</span>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Gratis Ongkir --}}
                <div class="group relative overflow-hidden rounded-2xl border border-emerald-200 bg-white p-7 shadow-xs transition duration-200 hover:-translate-y-0.5 hover:shadow-md dark:border-emerald-800/60 dark:bg-zinc-800">
                    <div class="absolute top-0 left-0 h-1.5 w-full bg-emerald-500"></div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </div>
                    <div class="mt-5">
                        <span class="inline-block rounded-md border border-emerald-200/80 bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 dark:border-emerald-800/40 dark:bg-emerald-950/40 dark:text-emerald-300">Bebas Biaya Kirim</span>
                        <h3 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">Area Surabaya Raya</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-zinc-400">
                            Pengiriman langsung dari pusat pergudangan tanpa biaya kirim untuk mitra ritel & grosir di wilayah strategis Jawa Timur.
                        </p>
                    </div>
                    <div class="mt-6 border-t border-slate-100 pt-4 dark:border-zinc-700/60">
                        <p class="text-xs font-bold text-emerald-700 dark:text-emerald-400">
                            {{ SiteSetting::get('distribution_free_area', 'Surabaya, Sidoarjo, Gresik, Malang, Mojokerto') }}
                        </p>
                    </div>
                </div>

                {{-- Card 3: 8 Wilayah Distribusi --}}
                <div class="group rounded-2xl border border-slate-200/90 bg-white p-7 shadow-xs transition duration-200 hover:-translate-y-0.5 hover:shadow-md sm:col-span-2 lg:col-span-1 dark:border-zinc-800 dark:bg-zinc-800">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z"/></svg>
                    </div>
                    <div class="mt-5">
                        <span class="inline-block rounded-md border border-blue-200/80 bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-800 dark:border-blue-800/40 dark:bg-blue-950/40 dark:text-blue-300">Jangkauan Luas</span>
                        <h3 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">8 Wilayah Indonesia Timur</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-zinc-400">
                            Bermitra dengan ekspedisi kargo darat dan laut terpercaya untuk menyuplai 355+ outlet aktif di kepulauan timur nusantara.
                        </p>
                    </div>
                    <div class="mt-6 border-t border-slate-100 pt-4 dark:border-zinc-700/60">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 dark:text-zinc-400">Jalur Distribusi:</span>
                            <span class="font-bold text-blue-700 dark:text-blue-400">Ekspedisi Rekanan Resmi</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Regional Coverage Chips Grid --}}
            <div class="mt-10 rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-xs dark:border-zinc-800 dark:bg-zinc-800/90 reveal">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h4 class="text-xs sm:text-sm font-bold tracking-wider text-slate-800 uppercase dark:text-white">Wilayah Cakupan Distribusi Aktif</h4>
                        <p class="text-xs text-slate-500 mt-0.5 dark:text-zinc-400">Pengiriman terjadwal ke mitra ritel, supermarket, hotel, dan restoran.</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800 dark:border-emerald-800/40 dark:bg-emerald-950/40 dark:text-emerald-300">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Hub Utama: SIRIE Sidoarjo
                    </span>
                </div>

                <div class="mt-6 flex flex-wrap gap-2.5">
                    @foreach ($this->distributionRegions as $region)
                        <div class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-2xs transition hover:border-blue-400 hover:bg-blue-50/60 hover:text-blue-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700">
                            <svg class="h-3.5 w-3.5 text-blue-600 shrink-0 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            <span>{{ $region }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         SECTION 4a: MENGAPA KAMI — Keunggulan perusahaan + foto tim
         Split layout: kiri foto tim asli, kanan list keunggulan.
         ============================================================ --}}
    <section class="overflow-hidden bg-slate-50 dark:bg-zinc-800">
        <div class="grid lg:grid-cols-2">
            {{-- Foto tim asli yang dipercantik --}}
            <div class="relative min-h-[380px] lg:min-h-[520px]">
                <img
                    src="/images/gudang/gudang-tim.webp"
                    alt="Tim PT Mustika Makmur Abadi sedang memproses pesanan di gudang Sidoarjo"
                    class="absolute inset-0 h-full w-full object-cover"
                    loading="lazy"
                >
                <div class="absolute inset-0 bg-gradient-to-r from-transparent to-slate-50/30 lg:to-transparent dark:to-zinc-800/30"></div>
            </div>

            {{-- Keunggulan perusahaan --}}
            <div class="flex flex-col justify-center px-8 py-14 sm:px-12 lg:px-16 lg:py-20 reveal">
                <span class="inline-block w-fit rounded-lg bg-brand-50 px-3 py-1 text-xs font-bold text-brand-700 dark:bg-brand-900/40 dark:text-brand-300">Mengapa Memilih Kami</span>
                <h2 class="heading-display font-display mt-4 text-3xl font-extrabold text-slate-900 sm:text-4xl dark:text-white">Mitra Distribusi Terpercaya sejak 2019</h2>
                <p class="mt-4 text-lg leading-relaxed text-slate-600 dark:text-zinc-300">Kami bukan sekadar distributor, kami adalah mitra bisnis jangka panjang yang memastikan produk sampai tepat waktu dan dalam kondisi prima.</p>

                <ul class="mt-8 space-y-4">
                    <li class="flex items-start gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-100 dark:bg-brand-900/40">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <p class="font-bold text-slate-900 dark:text-white">Distributor Resmi Langsung dari Principal</p>
                            <p class="mt-0.5 text-sm text-slate-500 dark:text-zinc-400">Bukan reseller. Harga kompetitif, stok terjamin, produk original.</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sun-100 dark:bg-sun-900/30">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-sun-600 dark:text-sun-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        </div>
                        <div>
                            <p class="font-bold text-slate-900 dark:text-white">Pengiriman Cepat Senin–Sabtu</p>
                            <p class="mt-0.5 text-sm text-slate-500 dark:text-zinc-400">Armada truk box sendiri. Gratis ongkir area Sidoarjo & sekitarnya.</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/30">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </div>
                        <div>
                            <p class="font-bold text-slate-900 dark:text-white">Jangkauan 8 Wilayah Indonesia Timur</p>
                            <p class="mt-0.5 text-sm text-slate-500 dark:text-zinc-400">Melayani Jawa Timur, Sulawesi, Kalimantan, Maluku, NTT, NTB, Bali, Timor Leste.</p>
                        </div>
                    </li>
                    <li class="flex items-start gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-coral-100 dark:bg-zinc-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-coral-600 dark:text-coral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        </div>
                        <div>
                            <p class="font-bold text-slate-900 dark:text-white">Gudang Terorganisir di Kawasan Industri SIRIE</p>
                            <p class="mt-0.5 text-sm text-slate-500 dark:text-zinc-400">Stok aman, terorganisir, dan selalu tersedia untuk memenuhi pesanan Anda.</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    {{-- ============================================================
         SECTION 4b: FASILITAS GUDANG — Real warehouse photos gallery
         Foto asli gudang PT Mustika untuk membangun kepercayaan mitra.
         ============================================================ --}}
    <section class="bg-white py-16 sm:py-20 dark:bg-zinc-900">

        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="mb-12 text-center reveal">
                <span class="inline-block rounded-lg bg-brand-50 px-3 py-1 text-xs font-bold text-brand-700 dark:bg-brand-900/40 dark:text-brand-300">Fasilitas Kami</span>
                <h2 class="heading-display font-display mt-3 text-3xl font-extrabold text-slate-900 sm:text-4xl dark:text-white">Gudang Distribusi Sidoarjo</h2>
                <p class="mx-auto mt-3 max-w-xl text-lg text-slate-500 dark:text-zinc-400">Kawasan Industri dan Pergudangan SIRIE, Blok G, pusat operasi distribusi kami di Jawa Timur.</p>
            </div>

            {{-- Main gallery grid --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 reveal">
                {{-- Large feature photo: pintu masuk --}}
                <div class="group relative overflow-hidden rounded-2xl sm:col-span-2 lg:col-span-2 lg:row-span-1">
                    <img
                        src="/images/gudang/gudang-eksterior.webp?v={{ file_exists(public_path('images/gudang/gudang-eksterior.webp')) ? filemtime(public_path('images/gudang/gudang-eksterior.webp')) : '2' }}"
                        alt="Eksterior gudang PT Mustika Makmur Abadi, Kawasan Industri SIRIE Sidoarjo"
                        class="h-72 w-full object-cover transition duration-500 group-hover:scale-105 sm:h-80"
                        loading="lazy"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent"></div>
                    <div class="absolute bottom-0 left-0 p-5">
                        <p class="text-sm font-bold text-white">Gedung Operasional</p>
                        <p class="text-xs text-white/70">Kawasan Industri SIRIE, Sidoarjo</p>
                    </div>
                </div>

                {{-- Pintu masuk close-up --}}
                <div class="group relative overflow-hidden rounded-2xl">
                    <img
                        src="/images/gudang/gudang-pintu-masuk.webp"
                        alt="Pintu masuk kantor PT Mustika Makmur Abadi dengan papan nama resmi"
                        class="h-72 w-full object-cover transition duration-500 group-hover:scale-105 sm:h-80"
                        loading="lazy"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent"></div>
                    <div class="absolute bottom-0 left-0 p-5">
                        <p class="text-sm font-bold text-white">Kantor & Penerimaan</p>
                        <p class="text-xs text-white/70">Jl. SIRIE Blok G, Sidoarjo</p>
                    </div>
                </div>

                {{-- Interior gudang --}}
                <div class="group relative overflow-hidden rounded-2xl sm:col-span-2 lg:col-span-3">
                    <img
                        src="/images/gudang/gudang-interior.webp"
                        alt="Interior gudang PT Mustika Makmur Abadi yang penuh dengan stok produk siap distribusi"
                        class="h-64 w-full object-cover transition duration-500 group-hover:scale-105"
                        loading="lazy"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/50 via-transparent to-transparent"></div>
                    <div class="absolute bottom-0 left-0 p-5">
                        <p class="text-sm font-bold text-white">Ruang Stok & Distribusi</p>
                        <p class="text-xs text-white/70">Gudang luas berkapasitas tinggi dengan sistem penyimpanan terorganisir</p>
                    </div>
                </div>
            </div>

            {{-- Facility stats row --}}
            <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-4 reveal">
                <div class="rounded-2xl bg-brand-50 p-5 text-center dark:bg-brand-900/20">
                    <p class="font-display text-3xl font-extrabold text-brand-700 dark:text-brand-300">≥500 m²</p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-zinc-400">Luas Gudang</p>
                </div>
                <div class="rounded-2xl bg-sun-50 p-5 text-center dark:bg-zinc-800">
                    <p class="font-display text-3xl font-extrabold text-sun-600 dark:text-sun-400">2 Unit</p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-zinc-400">Truk Box Sendiri</p>
                </div>
                <div class="rounded-2xl bg-emerald-50 p-5 text-center dark:bg-zinc-800">
                    <p class="font-display text-3xl font-extrabold text-emerald-700 dark:text-emerald-400">Senin–Sabtu</p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-zinc-400">Jadwal Pengiriman</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-5 text-center dark:bg-zinc-800">
                    <p class="font-display text-3xl font-extrabold text-slate-700 dark:text-zinc-200">Sidoarjo</p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-zinc-400">Pusat Distribusi</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         SECTION 5: PRODUK UNGGULAN — Gradient sky background + carousel
         ============================================================ --}}
    @if ($this->featuredProducts->count() > 0)
        <section class="bg-gradient-sky py-16 sm:py-20 dark:bg-zinc-900">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="flex flex-wrap items-end justify-between gap-4 reveal">
                    <div>
                        <h2 class="heading-display font-display text-3xl font-extrabold text-slate-900 sm:text-4xl dark:text-white">Produk Unggulan</h2>
                        <p class="mt-2 text-slate-600 dark:text-zinc-400">Pilihan terbaik dari katalog kami</p>
                    </div>
                    <a href="{{ route('produk') }}" wire:navigate class="text-sm font-extrabold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                        Lihat Semua →
                    </a>
                </div>

                <div class="swiper mt-10 reveal" x-data x-init="
                    new Swiper($el, {
                        modules: [SwiperModules.Navigation, SwiperModules.Pagination],
                        slidesPerView: 1.2,
                        spaceBetween: 16,
                        grabCursor: true,
                        pagination: { el: $el.querySelector('.swiper-pagination'), clickable: true },
                        breakpoints: {
                            640: { slidesPerView: 2.2, spaceBetween: 20 },
                            1024: { slidesPerView: 3.5, spaceBetween: 24 },
                        }
                    })
                ">
                    <div class="swiper-wrapper">
                        @foreach ($this->featuredProducts as $product)
                            <div class="swiper-slide">
                                <a
                                    href="{{ route('produk.detail', $product) }}"
                                    wire:navigate
                                    class="group block overflow-hidden rounded-2xl bg-white shadow-md shadow-slate-200/50 transition duration-200 hover:-translate-y-1 hover:shadow-xl dark:bg-zinc-800 dark:shadow-none"
                                >
                                    <div class="flex aspect-square items-center justify-center bg-slate-50 p-4 dark:bg-zinc-700/50">
                                        @if ($product->images->isNotEmpty())
                                            <img src="{{ $product->images->first()->url() }}" alt="{{ $product->images->first()->alt_text ?? $product->name }}" class="h-full w-full object-contain p-2 transition-transform duration-300 group-hover:scale-105" loading="lazy">
                                        @else
                                            <span class="font-display text-5xl font-bold text-brand-200">{{ mb_substr($product->name, 0, 1) }}</span>
                                        @endif
                                    </div>
                                    <div class="p-5">
                                        <p class="font-bold text-slate-900 group-hover:text-brand-600 dark:text-white dark:group-hover:text-brand-400">{{ $product->name }}</p>
                                        <div class="mt-2 flex items-center justify-between">
                                            <span class="inline-block rounded-lg bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-700 dark:bg-brand-900/50 dark:text-brand-300">
                                                {{ $product->category->label() }}
                                            </span>
                                            @if ($product->weight)
                                                <span class="text-xs font-medium text-slate-500 dark:text-zinc-400">
                                                    {{ $product->weight }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                    <div class="swiper-pagination mt-8 !relative"></div>
                </div>
            </div>
        </section>
    @endif

    {{-- ============================================================
         SECTION 6: CTA PENUTUP — Gradient oranye bold + pattern
         ============================================================ --}}
    <section class="bg-gradient-sun relative overflow-hidden">
        {{-- Decorative circles --}}
        <div class="absolute -top-20 -right-20 h-64 w-64 rounded-full bg-white/10" aria-hidden="true"></div>
        <div class="absolute -bottom-16 -left-16 h-48 w-48 rounded-full bg-white/10" aria-hidden="true"></div>

        <div class="relative z-10 mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 sm:py-20 reveal">
            <h2 class="heading-display font-display text-3xl font-extrabold text-slate-900 sm:text-4xl lg:text-5xl">Tertarik Menjadi Mitra Kami?</h2>
            <p class="mx-auto mt-4 max-w-lg text-lg text-slate-800/80">Hubungi kami untuk informasi kerja sama distribusi Yummy Bites dan beras premium di wilayah Anda.</p>
            <div class="mt-10 flex flex-wrap justify-center gap-4">
                @if ($whatsapp = SiteSetting::get('whatsapp_number'))
                    <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2.5 rounded-xl bg-slate-900 px-8 py-4 text-sm font-extrabold text-white shadow-xl transition hover:bg-slate-800 focus:ring-2 focus:ring-slate-700 focus:ring-offset-2 focus:ring-offset-sun-500 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M12.04 2c-5.46 0-9.9 4.44-9.9 9.9 0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.9-4.44 9.9-9.9 0-2.64-1.03-5.12-2.9-6.99A9.82 9.82 0 0 0 12.04 2zm5.8 14.07c-.24.68-1.4 1.3-1.93 1.36-.5.06-1 .27-3.36-.7-2.84-1.17-4.66-4.05-4.8-4.24-.14-.19-1.15-1.53-1.15-2.92s.72-2.06.98-2.35c.26-.29.56-.36.75-.36h.54c.17 0 .4-.03.62.48.24.56.8 1.95.87 2.09.07.14.11.3.02.49-.09.19-.14.3-.28.46-.14.16-.29.36-.42.48-.14.13-.29.28-.12.55.17.28.75 1.24 1.61 2.01 1.11 1 2.04 1.31 2.32 1.46.28.14.44.12.6-.07.17-.19.72-.84.91-1.13.19-.28.38-.23.63-.14.26.09 1.65.78 1.93.92.28.14.47.21.54.33.07.12.07.68-.17 1.36z"/></svg>
                        Hubungi via WhatsApp
                    </a>
                @endif
                <a href="{{ route('kontak') }}" wire:navigate class="rounded-xl bg-white px-8 py-4 text-sm font-extrabold text-slate-900 shadow-xl transition hover:bg-slate-50 focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-sun-500 focus:outline-none">
                    Kirim Pesan
                </a>
            </div>
        </div>
    </section>
</div>
