@extends('layouts.public')
@section('content')


<div>
    {{-- ============================================================
         SECTION 1: HERO — Authentic team photo background with overlay
         ============================================================ --}}
    <section class="relative min-h-[420px] overflow-hidden sm:min-h-[480px]">
        <img
            src="/images/gudang/gudang-tim.webp"
            alt="Tim operasional dan manajemen PT Mustika Makmur Abadi di gudang Sidoarjo"
            class="absolute inset-0 h-full w-full object-cover object-center"
            fetchpriority="high"
        >
        <div class="overlay-left absolute inset-0"></div>

        <div class="relative z-10 mx-auto flex max-w-5xl flex-col justify-center px-4 py-20 sm:px-6 sm:py-28">
            <div class="max-w-lg reveal">
                <h1 class="heading-display font-display text-4xl font-extrabold text-white sm:text-5xl lg:text-6xl">
                    {{ $content?->title ?? 'Tentang Kami' }}
                </h1>
                <p class="mt-5 text-lg leading-relaxed text-white/85">
                    {{ Str::limit($content?->body, 200) }}
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
                {{ $content?->body }}
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
                @foreach ($timeline as $milestone)
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
    @if ($visiMisi)
        <section class="py-16 sm:py-20 dark:bg-zinc-900">
            <div class="mx-auto max-w-5xl px-4 sm:px-6">
                <h2 class="heading-display font-display text-2xl font-extrabold text-slate-900 sm:text-3xl dark:text-white reveal">{{ $visiMisi->title }}</h2>

                <div class="mt-10 grid gap-6 lg:grid-cols-5">
                    <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 p-8 text-white lg:col-span-2 reveal">
                        <p class="font-display text-xl font-extrabold">Visi</p>
                        <p class="mt-4 text-lg leading-relaxed text-brand-100">{{ $visiText }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-8 lg:col-span-3 reveal dark:border-zinc-700 dark:bg-zinc-800">
                        <p class="font-display text-xl font-extrabold text-slate-900 dark:text-white">Misi</p>
                        <ul class="mt-4 space-y-3">
                            @foreach ($misiPoints as $point)
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
                @foreach ($differentiators as $item)
                    <div class="rounded-2xl bg-white/15 p-6 backdrop-blur-sm reveal">
                        <p class="font-display text-xl font-extrabold text-white">{{ $item['title'] }}</p>
                        <p class="mt-2 leading-relaxed text-white/85">{{ $item['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>

@endsection
