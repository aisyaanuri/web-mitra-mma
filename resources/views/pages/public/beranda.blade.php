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
            src="/images/illustrations/hero-distribusi.jpg"
            alt="Ilustrasi gudang distribusi PT Mustika Makmur Abadi"
            class="absolute inset-0 h-full w-full object-cover"
            fetchpriority="high"
        >
        <div class="overlay-left absolute inset-0"></div>

        <div class="relative z-10 mx-auto flex max-w-6xl flex-col justify-center px-4 py-20 sm:px-6 sm:py-28 lg:py-32">
            <div class="max-w-xl reveal">
                <h1 class="heading-display font-display text-4xl font-extrabold text-white sm:text-5xl lg:text-6xl">
                    {{ $this->hero?->title ?? 'Distributor Resmi Yummy Bites & Beras Impor' }}
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
         Background oranye-kuning di kanan. Tidak ada border/kotak.
         ============================================================ --}}
    <section class="overflow-hidden">
        <div class="grid lg:grid-cols-2">
            <div class="relative min-h-[360px] lg:min-h-[480px]">
                <img
                    src="/images/illustrations/yummy-bites.jpg"
                    alt="Ilustrasi ibu dan bayi menikmati snack Yummy Bites"
                    class="absolute inset-0 h-full w-full object-cover"
                    loading="lazy"
                >
            </div>
            <div class="flex flex-col justify-center bg-gradient-to-br from-sun-500 to-coral-500 px-8 py-14 sm:px-12 lg:px-16 lg:py-20 reveal">
                <span class="inline-block w-fit rounded-lg bg-white/20 px-3 py-1 text-xs font-bold text-white">MPASI & Snack Bayi</span>
                <h2 class="heading-display font-display mt-4 text-3xl font-extrabold text-white sm:text-4xl lg:text-5xl">Yummy Bites</h2>
                <p class="mt-4 max-w-md text-lg leading-relaxed text-white/90">
                    Camilan MPASI untuk si kecil usia 6 bulan+ hingga 1 tahun+. {{ $this->yummyBitesCount }} produk aktif. Distributor resmi, bukan reseller.
                </p>
                <a href="{{ route('produk', ['category' => 'yummy_bites']) }}" wire:navigate class="mt-8 inline-flex w-fit items-center gap-2 rounded-xl bg-white px-7 py-3.5 text-sm font-extrabold text-slate-900 shadow-lg transition hover:bg-slate-50 hover:shadow-xl">
                    Lihat Produk Yummy Bites
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7" /></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- ============================================================
         SECTION 3: BERAS IMPOR — Split 50/50 terbalik (mirrored)
         Background biru tua di kiri, ilustrasi sawah di kanan.
         ============================================================ --}}
    <section class="overflow-hidden">
        <div class="grid lg:grid-cols-2">
            <div class="flex flex-col justify-center bg-gradient-brand px-8 py-14 sm:px-12 lg:order-first lg:px-16 lg:py-20 reveal">
                <span class="inline-block w-fit rounded-lg bg-white/15 px-3 py-1 text-xs font-bold text-white">Beras Premium</span>
                <h2 class="heading-display font-display mt-4 text-3xl font-extrabold text-white sm:text-4xl lg:text-5xl">Beras Impor</h2>
                <p class="mt-4 max-w-md text-lg leading-relaxed text-white/90">
                    Beras impor pilihan dari Jepang, Korea, India, Vietnam, dan Thailand. {{ $this->berasImporCount }} produk aktif, untuk kebutuhan restoran dan hotel.
                </p>
                <a href="{{ route('produk', ['category' => 'beras_impor']) }}" wire:navigate class="mt-8 inline-flex w-fit items-center gap-2 rounded-xl bg-white px-7 py-3.5 text-sm font-extrabold text-brand-700 shadow-lg transition hover:bg-brand-50 hover:shadow-xl">
                    Lihat Produk Beras Impor
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7" /></svg>
                </a>
            </div>
            <div class="relative min-h-[360px] lg:min-h-[480px]">
                <img
                    src="/images/illustrations/beras-impor.jpg"
                    alt="Ilustrasi sawah padi dan beras impor dari berbagai negara"
                    class="absolute inset-0 h-full w-full object-cover"
                    loading="lazy"
                >
            </div>
        </div>
    </section>

    {{-- ============================================================
         SECTION 4: JANGKAUAN DISTRIBUSI — Map illustration background
         Overlay gradient + teks putih. Wilayah sebagai chip/tag.
         ============================================================ --}}
    <section class="relative min-h-[480px] overflow-hidden sm:min-h-[520px]">
        <img
            src="/images/illustrations/peta-distribusi.jpg"
            alt="Peta distribusi PT Mustika Makmur Abadi di Indonesia bagian timur"
            class="absolute inset-0 h-full w-full object-cover"
            loading="lazy"
        >
        <div class="overlay-bottom absolute inset-0"></div>

        <div class="relative z-10 mx-auto flex max-w-6xl flex-col justify-end px-4 py-14 sm:px-6 sm:py-20">
            <div class="reveal">
                <h2 class="heading-display font-display text-3xl font-extrabold text-white sm:text-4xl lg:text-5xl">
                    {{ $this->distribusi?->title ?? 'Jangkauan Distribusi' }}
                </h2>
                <p class="mt-3 max-w-lg text-lg text-white/85">
                    {{ $this->distribusi?->body ?? 'Dari pusat distribusi di Sidoarjo, produk kami menjangkau 8 wilayah Indonesia bagian timur.' }}
                </p>
            </div>

            <div class="mt-8 flex flex-wrap gap-2 reveal">
                @foreach ($this->distributionRegions as $region)
                    <span class="rounded-lg bg-white/20 px-4 py-2 text-sm font-bold text-white backdrop-blur-sm">{{ $region }}</span>
                @endforeach
            </div>

            <div class="mt-6 flex flex-wrap gap-6 reveal">
                @if ($freeArea = SiteSetting::get('distribution_free_area'))
                    <div class="rounded-xl bg-sun-500/90 px-5 py-3 backdrop-blur-sm">
                        <p class="text-sm font-extrabold text-slate-900">Gratis Ongkir</p>
                        <p class="text-xs text-slate-800">{{ $freeArea }}</p>
                    </div>
                @endif
                <div class="rounded-xl bg-white/15 px-5 py-3 backdrop-blur-sm">
                    <p class="text-sm font-extrabold text-white">Senin – Sabtu</p>
                    <p class="text-xs text-white/70">Jadwal Pengiriman</p>
                </div>
                <div class="rounded-xl bg-white/15 px-5 py-3 backdrop-blur-sm">
                    <p class="text-sm font-extrabold text-white">2 Unit Truk Box</p>
                    <p class="text-xs text-white/70">Armada Sendiri</p>
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
                                <div class="overflow-hidden rounded-2xl bg-white shadow-md shadow-slate-200/50 transition hover:shadow-xl dark:bg-zinc-800 dark:shadow-none">
                                    <div class="flex aspect-square items-center justify-center bg-slate-50 dark:bg-zinc-700/50">
                                        @if ($product->images->isNotEmpty())
                                            <img src="{{ $product->images->first()->url() }}" alt="{{ $product->images->first()->alt_text ?? $product->name }}" class="h-full w-full object-contain p-4" loading="lazy">
                                        @else
                                            <span class="font-display text-5xl font-bold text-brand-200">{{ mb_substr($product->name, 0, 1) }}</span>
                                        @endif
                                    </div>
                                    <div class="p-5">
                                        <p class="font-bold text-slate-900 dark:text-white">{{ $product->name }}</p>
                                        <span class="mt-2 inline-block rounded-lg bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-700 dark:bg-brand-900/50 dark:text-brand-300">
                                            {{ $product->category->label() }}
                                        </span>
                                    </div>
                                </div>
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
            <p class="mx-auto mt-4 max-w-lg text-lg text-slate-800/80">Hubungi kami untuk informasi kerja sama distribusi Yummy Bites dan beras impor di wilayah Anda.</p>
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
