<?php

use App\Models\CompanyContent;
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
        SEOMeta::setTitle('Tentang Kami');
        SEOMeta::setDescription('Profil PT Mustika Makmur Abadi, distributor resmi Yummy Bites dan beras premium sejak 2019.');
        OpenGraph::setTitle('Tentang Kami - PT Mustika Makmur Abadi');
    }

    #[Computed]
    public function content(): ?CompanyContent
    {
        return CompanyContent::section('tentang_kami');
    }

    #[Computed]
    public function visiMisi(): ?CompanyContent
    {
        return CompanyContent::section('tentang_kami_visi_misi');
    }

    /** @return array<int, string> */
    #[Computed]
    public function misiPoints(): array
    {
        if (! $this->visiMisi) {
            return [];
        }

        preg_match_all('/^\d+\.\s*(.+)$/m', $this->visiMisi->body, $points);

        return array_map('trim', $points[1] ?? []);
    }

    #[Computed]
    public function visiText(): ?string
    {
        if (! $this->visiMisi) {
            return null;
        }

        preg_match('/Visi\s*\n(.*?)\n\s*\n\s*Misi/is', $this->visiMisi->body, $matches);

        return isset($matches[1]) ? trim($matches[1]) : null;
    }

    /** @return array<int, array{year: string, title: string, desc: string}> */
    #[Computed]
    public function timeline(): array
    {
        return [
            [
                'year' => SiteSetting::get('established_year', '2019'),
                'title' => 'Berdiri',
                'desc' => 'Memulai usaha sebagai distributor resmi Yummy Bites dan beras premium di Sidoarjo, Jawa Timur.',
            ],
            [
                'year' => 'Kini',
                'title' => 'Terus Berkembang',
                'desc' => SiteSetting::get('outlets_count', '355').'+ outlet di 8 wilayah Indonesia bagian timur, didukung '.SiteSetting::get('staff_count', '18').' tenaga ahli dan armada pengiriman sendiri.',
            ],
        ];
    }

    /** @return array<int, array{title: string, desc: string}> */
    #[Computed]
    public function differentiators(): array
    {
        return [
            ['title' => 'Distributor Resmi', 'desc' => 'Mitra resmi Yummy Bites & beras premium (impor & lokal) dari berbagai sumber terpercaya, bukan reseller.'],
            ['title' => 'Armada Sendiri', 'desc' => 'Gratis ongkir untuk area Surabaya Raya tanpa bergantung ekspedisi pihak ketiga.'],
            ['title' => '355+ Outlet', 'desc' => 'Jaringan distribusi aktif di 8 wilayah Indonesia bagian timur.'],
            ['title' => 'Berpengalaman', 'desc' => 'Melayani mitra bisnis sejak 2019 dengan 18 tenaga ahli distribusi.'],
        ];
    }
}; ?>

<div>
    {{-- ============================================================
         SECTION 1: HERO — Team illustration background with overlay
         ============================================================ --}}
    <section class="relative min-h-[400px] overflow-hidden sm:min-h-[480px]">
        <img
            src="/images/illustrations/tim-mustika.jpg"
            alt="Ilustrasi tim PT Mustika Makmur Abadi"
            class="absolute inset-0 h-full w-full object-cover object-top"
            fetchpriority="high"
        >
        <div class="overlay-left absolute inset-0"></div>

        <div class="relative z-10 mx-auto flex max-w-5xl flex-col justify-center px-4 py-20 sm:px-6 sm:py-28">
            <div class="max-w-lg reveal">
                <h1 class="heading-display font-display text-4xl font-extrabold text-white sm:text-5xl lg:text-6xl">
                    {{ $this->content?->title ?? 'Tentang Kami' }}
                </h1>
                <p class="mt-5 text-lg leading-relaxed text-white/85">
                    {{ Str::limit($this->content?->body, 200) }}
                </p>
            </div>
        </div>
    </section>

    {{-- ============================================================
         SECTION 2: PROFIL LENGKAP — Full text on light background
         ============================================================ --}}
    <section class="py-14 sm:py-20 dark:bg-zinc-900">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 reveal">
            <div class="space-y-4 whitespace-pre-line text-lg leading-relaxed text-slate-600 dark:text-zinc-300">
                {{ $this->content?->body }}
            </div>
        </div>
    </section>

    {{-- ============================================================
         SECTION 3: TIMELINE — Brand gradient background
         ============================================================ --}}
    <section class="bg-gradient-brand py-16 sm:py-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 reveal">
            <h2 class="heading-display font-display text-2xl font-extrabold text-white sm:text-3xl">Perjalanan Kami</h2>

            <div class="mt-10 grid gap-6 sm:grid-cols-2">
                @foreach ($this->timeline as $milestone)
                    <div class="rounded-2xl bg-white/10 p-6 backdrop-blur-sm">
                        <p class="font-display text-3xl font-extrabold text-sun-400">{{ $milestone['year'] }}</p>
                        <p class="mt-2 text-xl font-bold text-white">{{ $milestone['title'] }}</p>
                        <p class="mt-3 leading-relaxed text-white/80">{{ $milestone['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
         SECTION 4: VISI & MISI — Split visual
         ============================================================ --}}
    @if ($this->visiMisi)
        <section class="py-16 sm:py-20 dark:bg-zinc-900">
            <div class="mx-auto max-w-5xl px-4 sm:px-6">
                <h2 class="heading-display font-display text-2xl font-extrabold text-slate-900 sm:text-3xl dark:text-white reveal">{{ $this->visiMisi->title }}</h2>

                <div class="mt-10 grid gap-6 lg:grid-cols-5">
                    <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 p-8 text-white lg:col-span-2 reveal">
                        <p class="font-display text-xl font-extrabold">Visi</p>
                        <p class="mt-4 text-lg leading-relaxed text-brand-100">{{ $this->visiText }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-8 lg:col-span-3 reveal dark:border-zinc-700 dark:bg-zinc-800">
                        <p class="font-display text-xl font-extrabold text-slate-900 dark:text-white">Misi</p>
                        <ul class="mt-4 space-y-3">
                            @foreach ($this->misiPoints as $point)
                                <li class="flex gap-3">
                                    <span class="mt-1 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-mint-500 text-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    </span>
                                    <span class="text-slate-700 dark:text-zinc-300">{{ $point }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ============================================================
         SECTION 5: KENAPA MUSTIKA — Sun gradient background
         ============================================================ --}}
    <section class="bg-gradient-to-br from-sun-500 to-coral-500 py-16 sm:py-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 reveal">
            <h2 class="heading-display font-display text-2xl font-extrabold text-white sm:text-3xl">Kenapa Memilih Kami</h2>

            <div class="mt-8 grid gap-4 sm:grid-cols-2 reveal-stagger">
                @foreach ($this->differentiators as $item)
                    <div class="rounded-2xl bg-white/15 p-6 backdrop-blur-sm reveal">
                        <p class="font-display text-xl font-extrabold text-white">{{ $item['title'] }}</p>
                        <p class="mt-2 leading-relaxed text-white/85">{{ $item['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
