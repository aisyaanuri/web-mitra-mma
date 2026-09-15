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
}; ?>

<div class="mx-auto max-w-4xl px-4 py-16 sm:px-6">
    <h1 class="text-3xl font-bold text-zinc-900 sm:text-4xl dark:text-white" data-aos="fade-up">
        {{ $this->content?->title ?? 'Tentang Kami' }}
    </h1>

    <div class="mt-6 space-y-4 whitespace-pre-line text-zinc-600 dark:text-zinc-400" data-aos="fade-up">
        {{ $this->content?->body }}
    </div>

    <div class="mt-10 grid gap-4 sm:grid-cols-3" data-aos="fade-up">
        <div class="rounded-xl border border-zinc-200 p-5 text-center dark:border-zinc-800">
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ SiteSetting::get('established_year') }}</p>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Tahun Berdiri</p>
        </div>
        <div class="rounded-xl border border-zinc-200 p-5 text-center dark:border-zinc-800">
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ SiteSetting::get('outlets_count') }}+</p>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Outlet Terjangkau</p>
        </div>
        <div class="rounded-xl border border-zinc-200 p-5 text-center dark:border-zinc-800">
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ SiteSetting::get('staff_count') }}</p>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Tenaga Ahli Distribusi</p>
        </div>
    </div>

    @if ($this->visiMisi)
        <div class="mt-10" data-aos="fade-up">
            <h2 class="text-xl font-bold text-zinc-900 dark:text-white">{{ $this->visiMisi->title }}</h2>
            <div class="mt-3 whitespace-pre-line text-zinc-600 dark:text-zinc-400">{{ $this->visiMisi->body }}</div>
        </div>
    @endif
</div>
