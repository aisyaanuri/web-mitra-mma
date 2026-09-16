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
     * @return array<int, array{year: string, title: string, desc: string}>
     */
    #[Computed]
    public function timeline(): array
    {
        return [
            [
                'year' => SiteSetting::get('established_year', '2019'),
                'title' => 'Berdiri',
                'desc' => 'Memulai usaha sebagai distributor resmi Yummy Bites dan beras impor di Sidoarjo.',
            ],
            [
                'year' => 'Kini',
                'title' => 'Terus Berkembang',
                'desc' => SiteSetting::get('outlets_count', '355').'+ outlet di 8 wilayah Indonesia, '.SiteSetting::get('staff_count', '18').' tenaga ahli, dengan armada pengiriman sendiri.',
            ],
        ];
    }
}; ?>

<div>
    <section class="bg-gradient-to-b from-brand-50 to-white dark:from-zinc-800 dark:to-zinc-900">
        <div class="mx-auto max-w-4xl px-4 py-16 sm:px-6">
            <h1 class="text-3xl font-bold text-zinc-900 sm:text-4xl dark:text-white" data-aos="fade-up">
                {{ $this->content?->title ?? 'Tentang Kami' }}
            </h1>

            <div class="mt-6 space-y-4 whitespace-pre-line text-zinc-600 dark:text-zinc-400" data-aos="fade-up">
                {{ $this->content?->body }}
            </div>

            <div class="mt-10 grid gap-4 sm:grid-cols-3" data-aos="zoom-in">
                <div class="rounded-xl border border-zinc-200 p-5 text-center dark:border-zinc-800">
                    <p class="text-3xl font-bold text-brand-600 dark:text-brand-300 sm:text-4xl">{{ SiteSetting::get('established_year') }}</p>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Tahun Berdiri</p>
                </div>
                <x-stat-counter :target="(int) SiteSetting::get('outlets_count', 0)" suffix="+" label="Outlet Terjangkau" class="rounded-xl border border-zinc-200 p-5 text-center dark:border-zinc-800" />
                <x-stat-counter :target="(int) SiteSetting::get('staff_count', 0)" label="Tenaga Ahli Distribusi" class="rounded-xl border border-zinc-200 p-5 text-center dark:border-zinc-800" />
            </div>
        </div>
    </section>

    {{-- Timeline --}}
    <section class="mx-auto max-w-4xl px-4 py-16 sm:px-6">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white" data-aos="fade-up">Perjalanan Kami</h2>

        <div class="mt-8 space-y-8 border-l-2 border-brand-200 pl-6 dark:border-brand-800">
            @foreach ($this->timeline as $index => $milestone)
                <div class="relative" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                    <span class="absolute -left-[1.85rem] flex h-4 w-4 items-center justify-center rounded-full bg-brand-500 ring-4 ring-white dark:ring-zinc-900"></span>
                    <p class="text-sm font-semibold text-brand-600 dark:text-brand-400">{{ $milestone['year'] }}</p>
                    <p class="mt-1 font-semibold text-zinc-900 dark:text-white">{{ $milestone['title'] }}</p>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ $milestone['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Visi & Misi --}}
    @if ($this->visiMisi)
        <section class="border-t border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
            <div class="mx-auto max-w-4xl px-4 py-16 sm:px-6" data-aos="fade-up">
                <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $this->visiMisi->title }}</h2>
                <div class="mt-4 whitespace-pre-line text-zinc-600 dark:text-zinc-400">{{ $this->visiMisi->body }}</div>
            </div>
        </section>
    @endif
</div>
