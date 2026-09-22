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
        SEOMeta::setDescription('Profil PT Mustika Makmur Abadi, distributor resmi Yummy Bites dan beras impor sejak 2019.');
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

    /**
     * @return array<int, string>
     */
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

    /**
     * @return array<int, array{year: string, title: string, desc: string}>
     */
    #[Computed]
    public function timeline(): array
    {
        return [
            [
                'year' => SiteSetting::get('established_year', '2019'),
                'title' => 'Berdiri',
                'desc' => 'Memulai usaha sebagai distributor resmi Yummy Bites dan beras impor di Sidoarjo, Jawa Timur.',
            ],
            [
                'year' => 'Kini',
                'title' => 'Terus Berkembang',
                'desc' => SiteSetting::get('outlets_count', '355').'+ outlet di 8 wilayah Indonesia bagian timur, didukung '.SiteSetting::get('staff_count', '18').' tenaga ahli dan armada pengiriman sendiri.',
            ],
        ];
    }
}; ?>

<div>
    {{-- Hero --}}
    <section class="bg-brand-900">
        <div class="mx-auto max-w-5xl px-4 py-20 sm:px-6" data-aos="fade-up">
            <h1 class="heading-display font-display max-w-2xl text-4xl font-bold text-white sm:text-5xl">
                {{ $this->content?->title ?? 'Tentang Kami' }}
            </h1>
            <div class="mt-6 max-w-2xl space-y-4 whitespace-pre-line text-brand-100">
                {{ $this->content?->body }}
            </div>
        </div>
    </section>

    {{-- Stats strip overlapping hero --}}
    <div class="relative z-10 mx-auto -mt-8 max-w-5xl px-4 sm:px-6" data-aos="fade-up" data-aos-delay="100">
        <div class="grid grid-cols-3 divide-x divide-zinc-100 rounded-2xl bg-white shadow-xl shadow-brand-900/10 dark:divide-zinc-800 dark:bg-zinc-900">
            <div class="px-3 py-6 text-center sm:px-8">
                <p class="font-display text-3xl text-brand-600 sm:text-4xl dark:text-brand-300">{{ SiteSetting::get('established_year') }}</p>
                <p class="mt-1 text-xs font-medium text-zinc-500 sm:text-sm dark:text-zinc-400">Tahun Berdiri</p>
            </div>
            <x-stat-counter :target="(int) SiteSetting::get('outlets_count', 0)" suffix="+" label="Outlet Terjangkau" number-class="font-display text-3xl text-brand-600 dark:text-brand-300" class="px-3 py-6 text-center sm:px-8" />
            <x-stat-counter :target="(int) SiteSetting::get('staff_count', 0)" label="Tenaga Ahli Distribusi" number-class="font-display text-3xl text-brand-600 dark:text-brand-300" class="px-3 py-6 text-center sm:px-8" />
        </div>
    </div>

    {{-- Timeline --}}
    <section class="mx-auto max-w-5xl px-4 py-20 sm:px-6">
        <div class="grid gap-10 lg:grid-cols-3 lg:gap-16">
            <div data-aos="fade-up">
                <h2 class="heading-display font-display text-3xl font-bold text-zinc-900 sm:text-4xl dark:text-white">Perjalanan Kami</h2>
                <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">Dari usaha distribusi rintisan menjadi jaringan yang menjangkau Indonesia bagian timur.</p>
            </div>

            <div class="lg:col-span-2">
                <div class="space-y-10 border-l-2 border-brand-200 pl-8 dark:border-brand-800">
                    @foreach ($this->timeline as $index => $milestone)
                        <div class="relative" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                            <span class="absolute -left-[2.15rem] flex h-5 w-5 items-center justify-center rounded-full bg-brand-500 ring-4 ring-white dark:ring-zinc-900"></span>
                            <p class="font-display text-lg text-brand-600 dark:text-brand-400">{{ $milestone['year'] }}</p>
                            <p class="mt-1 text-xl font-bold text-zinc-900 dark:text-white">{{ $milestone['title'] }}</p>
                            <p class="mt-2 max-w-lg text-zinc-600 dark:text-zinc-400">{{ $milestone['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Visi & Misi --}}
    @if ($this->visiMisi)
        <section class="border-t border-zinc-100 bg-zinc-50 py-20 dark:border-zinc-800 dark:bg-zinc-950">
            <div class="mx-auto max-w-5xl px-4 sm:px-6">
                <h2 class="heading-display font-display text-3xl font-bold text-zinc-900 sm:text-4xl dark:text-white" data-aos="fade-up">{{ $this->visiMisi->title }}</h2>

                <div class="mt-10 grid gap-6 lg:grid-cols-5">
                    <div class="rounded-2xl bg-brand-800 p-8 text-white lg:col-span-2" data-aos="fade-up">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-brand-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <p class="font-display mt-4 text-lg font-bold">Visi</p>
                        <p class="mt-3 text-lg leading-relaxed font-medium">{{ $this->visiText }}</p>
                    </div>

                    <div class="rounded-2xl border border-zinc-200 bg-white p-8 lg:col-span-3 dark:border-zinc-800 dark:bg-zinc-900" data-aos="fade-up" data-aos-delay="100">
                        <p class="font-display text-lg font-bold text-zinc-900 dark:text-white">Misi</p>
                        <ul class="mt-4 space-y-3">
                            @foreach ($this->misiPoints as $point)
                                <li class="flex gap-3">
                                    <span class="mt-1 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-600 dark:bg-brand-900/50 dark:text-brand-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    </span>
                                    <span class="text-zinc-700 dark:text-zinc-300">{{ $point }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    @endif
</div>
