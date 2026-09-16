<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        {{-- SEOTools renders the <title> and meta/OG tags for public pages,
        so partials.head's own <title> is intentionally not included here
        to avoid a duplicate <title> element. --}}
        {!! \Artesaos\SEOTools\Facades\SEOTools::generate() !!}

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="flex min-h-screen flex-col overflow-x-hidden bg-white text-zinc-800 dark:bg-zinc-900 dark:text-zinc-200">
        @php
            $navLinks = [
                'home' => __('Beranda'),
                'produk' => __('Produk'),
                'tentang-kami' => __('Tentang Kami'),
                'kontak' => __('Kontak'),
            ];
            $whatsapp = \App\Models\SiteSetting::get('whatsapp_number');
        @endphp

        <header
            x-data="{ scrolled: false, mobileOpen: false }"
            x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 12)"
            :class="scrolled ? 'shadow-sm border-transparent bg-white/95 dark:bg-zinc-900/95' : 'border-transparent bg-white/70 dark:bg-zinc-900/70'"
            class="sticky top-0 z-40 border-b backdrop-blur transition-all"
        >
            <nav class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3.5 sm:px-6">
                <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2.5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-brand-400 via-brand-600 to-brand-800 text-xs font-bold tracking-wide text-white shadow-md shadow-brand-500/30">
                        MMA
                    </span>
                    <span class="text-base font-bold tracking-tight text-zinc-900 sm:text-lg dark:text-white">
                        PT Mustika Makmur Abadi
                    </span>
                </a>

                <div class="hidden items-center gap-1 sm:flex">
                    @foreach ($navLinks as $routeName => $label)
                        <a
                            href="{{ route($routeName) }}"
                            wire:navigate
                            class="relative rounded-full px-4 py-2 text-sm font-medium transition {{ request()->routeIs($routeName) ? 'text-brand-700 dark:text-brand-300' : 'text-zinc-600 hover:text-brand-600 dark:text-zinc-300 dark:hover:text-brand-300' }}"
                        >
                            @if (request()->routeIs($routeName))
                                <span class="absolute inset-0 rounded-full bg-brand-50 dark:bg-brand-900/40"></span>
                            @endif
                            <span class="relative">{{ $label }}</span>
                        </a>
                    @endforeach
                    <a href="{{ route('login') }}" wire:navigate class="ml-2 rounded-full border border-zinc-200 px-4 py-2 text-sm font-medium text-zinc-600 transition hover:border-brand-300 hover:text-brand-600 dark:border-zinc-700 dark:text-zinc-300">
                        {{ __('Login Staf') }}
                    </a>
                </div>

                <div class="flex items-center gap-2">
                    @if ($whatsapp)
                        <a
                            href="https://wa.me/{{ $whatsapp }}"
                            target="_blank"
                            rel="noopener"
                            class="hidden items-center gap-2 rounded-full bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-green-600/30 transition hover:bg-green-700 sm:inline-flex"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4"><path d="M12.04 2c-5.46 0-9.9 4.44-9.9 9.9 0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.9-4.44 9.9-9.9 0-2.64-1.03-5.12-2.9-6.99A9.82 9.82 0 0 0 12.04 2zm5.8 14.07c-.24.68-1.4 1.3-1.93 1.36-.5.06-1 .27-3.36-.7-2.84-1.17-4.66-4.05-4.8-4.24-.14-.19-1.15-1.53-1.15-2.92s.72-2.06.98-2.35c.26-.29.56-.36.75-.36h.54c.17 0 .4-.03.62.48.24.56.8 1.95.87 2.09.07.14.11.3.02.49-.09.19-.14.3-.28.46-.14.16-.29.36-.42.48-.14.13-.29.28-.12.55.17.28.75 1.24 1.61 2.01 1.11 1 2.04 1.31 2.32 1.46.28.14.44.12.6-.07.17-.19.72-.84.91-1.13.19-.28.38-.23.63-.14.26.09 1.65.78 1.93.92.28.14.47.21.54.33.07.12.07.68-.17 1.36z"/></svg>
                            {{ __('WhatsApp') }}
                        </a>
                    @endif

                    <button
                        @click="mobileOpen = !mobileOpen"
                        class="flex h-10 w-10 items-center justify-center rounded-full text-zinc-600 transition hover:bg-zinc-100 sm:hidden dark:text-zinc-300 dark:hover:bg-zinc-800"
                        aria-label="Menu"
                    >
                        <svg x-show="!mobileOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" /></svg>
                        <svg x-show="mobileOpen" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18" /></svg>
                    </button>
                </div>
            </nav>

            <div
                x-show="mobileOpen"
                x-cloak
                x-transition
                class="border-t border-zinc-200 bg-white px-4 py-4 sm:hidden dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div class="flex flex-col gap-1">
                    @foreach ($navLinks as $routeName => $label)
                        <a
                            href="{{ route($routeName) }}"
                            wire:navigate
                            class="rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs($routeName) ? 'bg-brand-50 text-brand-700 dark:bg-brand-900/40 dark:text-brand-300' : 'text-zinc-700 dark:text-zinc-300' }}"
                        >
                            {{ $label }}
                        </a>
                    @endforeach
                    <a href="{{ route('login') }}" wire:navigate class="rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ __('Login Staf') }}
                    </a>
                    @if ($whatsapp)
                        <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="mt-2 rounded-lg bg-green-600 px-3 py-2.5 text-center text-sm font-semibold text-white">
                            {{ __('Hubungi via WhatsApp') }}
                        </a>
                    @endif
                </div>
            </div>
        </header>

        <main class="flex-1">
            {{ $slot }}
        </main>

        <footer class="relative overflow-hidden bg-brand-900 text-brand-100">
            <div class="absolute inset-0 bg-dot-grid text-white/5"></div>
            <div class="relative mx-auto max-w-6xl px-4 py-14 sm:px-6">
                <div class="grid gap-10 sm:grid-cols-3">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-brand-300 to-brand-600 text-xs font-bold text-white">
                                MMA
                            </span>
                            <p class="text-base font-bold text-white">PT Mustika Makmur Abadi</p>
                        </div>
                        <p class="mt-4 max-w-xs text-sm leading-relaxed text-brand-200">
                            Distributor resmi Yummy Bites dan beras impor, berdiri sejak {{ \App\Models\SiteSetting::get('established_year', '2019') }}.
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold tracking-wider text-brand-400 uppercase">{{ __('Alamat') }}</p>
                        <p class="mt-4 text-sm leading-relaxed text-brand-200">
                            {{ \App\Models\SiteSetting::get('office_address') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold tracking-wider text-brand-400 uppercase">{{ __('Tautan') }}</p>
                        <ul class="mt-4 space-y-2 text-sm text-brand-200">
                            <li><a href="{{ route('produk') }}" wire:navigate class="transition hover:text-white">{{ __('Produk') }}</a></li>
                            <li><a href="{{ route('tentang-kami') }}" wire:navigate class="transition hover:text-white">{{ __('Tentang Kami') }}</a></li>
                            <li><a href="{{ route('kontak') }}" wire:navigate class="transition hover:text-white">{{ __('Kontak') }}</a></li>
                            <li><a href="{{ route('kebijakan-privasi') }}" wire:navigate class="transition hover:text-white">{{ __('Kebijakan Privasi') }}</a></li>
                        </ul>
                    </div>
                </div>
                <div class="mt-12 flex flex-col gap-4 border-t border-white/10 pt-6 text-xs text-brand-300 sm:flex-row sm:items-center sm:justify-between">
                    <p>&copy; {{ now()->year }} PT Mustika Makmur Abadi. {{ __('Semua hak cipta dilindungi.') }}</p>
                    <p>{{ __('Sidoarjo, Jawa Timur, Indonesia') }}</p>
                </div>
            </div>
        </footer>
    </body>
</html>
