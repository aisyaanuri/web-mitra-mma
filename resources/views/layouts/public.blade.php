<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <link rel="icon" href="/favicon.ico?v=2" sizes="any">
        <link rel="icon" href="/favicon.svg?v=2" type="image/svg+xml">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=2">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v=2">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png?v=2">

        @fonts

        {!! \Artesaos\SEOTools\Facades\SEOTools::generate() !!}

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="flex min-h-screen flex-col bg-white text-slate-700 dark:bg-zinc-900 dark:text-zinc-200">
        @php
            $navLinks = [
                'home' => __('Beranda'),
                'produk' => __('Produk'),
                'tentang-kami' => __('Tentang Kami'),
                'kontak' => __('Kontak'),
            ];
            $whatsapp = \App\Models\SiteSetting::get('whatsapp_number');
        @endphp

        {{-- Navbar: solid white, no blur/glass (R-10). Sticky with subtle
        border on scroll via Alpine. --}}
        <header
            x-data="{ scrolled: false, mobileOpen: false }"
            x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 8)"
            :class="scrolled ? 'shadow-sm border-slate-200 dark:border-zinc-700' : 'border-transparent'"
            class="sticky top-0 z-40 border-b bg-white transition-shadow dark:bg-zinc-900"
        >
            <nav class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6">
                {{-- Official Logo: image + text --}}
                <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2.5">
                    <img
                        src="{{ asset('images/logo-mustika-square.png') }}"
                        alt="Logo PT Mustika Makmur Abadi"
                        class="h-9 w-9 rounded-lg object-contain bg-white shadow-xs border border-slate-100 dark:border-zinc-700 p-0.5"
                    >
                    <span class="font-display text-lg font-bold text-slate-900 dark:text-white">
                        Mustika
                    </span>
                </a>

                {{-- Desktop links --}}
                <div class="hidden items-center gap-1 md:flex">
                    @foreach ($navLinks as $routeName => $label)
                        <a
                            href="{{ route($routeName) }}"
                            wire:navigate
                            class="rounded-lg px-3.5 py-2 text-sm font-medium transition {{ request()->routeIs($routeName) ? 'text-brand-600 dark:text-brand-300' : 'text-slate-600 hover:text-brand-600 dark:text-zinc-300 dark:hover:text-brand-300' }}"
                        >
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <div class="flex items-center gap-2">
                    @if ($whatsapp)
                        <a
                            href="https://wa.me/{{ $whatsapp }}"
                            target="_blank"
                            rel="noopener"
                            class="hidden items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700 md:inline-flex"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4"><path d="M12.04 2c-5.46 0-9.9 4.44-9.9 9.9 0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.9-4.44 9.9-9.9 0-2.64-1.03-5.12-2.9-6.99A9.82 9.82 0 0 0 12.04 2zm5.8 14.07c-.24.68-1.4 1.3-1.93 1.36-.5.06-1 .27-3.36-.7-2.84-1.17-4.66-4.05-4.8-4.24-.14-.19-1.15-1.53-1.15-2.92s.72-2.06.98-2.35c.26-.29.56-.36.75-.36h.54c.17 0 .4-.03.62.48.24.56.8 1.95.87 2.09.07.14.11.3.02.49-.09.19-.14.3-.28.46-.14.16-.29.36-.42.48-.14.13-.29.28-.12.55.17.28.75 1.24 1.61 2.01 1.11 1 2.04 1.31 2.32 1.46.28.14.44.12.6-.07.17-.19.72-.84.91-1.13.19-.28.38-.23.63-.14.26.09 1.65.78 1.93.92.28.14.47.21.54.33.07.12.07.68-.17 1.36z"/></svg>
                            WhatsApp
                        </a>
                    @endif

                    <a href="{{ route('login') }}" wire:navigate class="hidden text-sm font-medium text-slate-500 transition hover:text-brand-600 md:inline-block dark:text-zinc-400">
                        Login Staf
                    </a>

                    {{-- Mobile hamburger --}}
                    <button
                        @click="mobileOpen = !mobileOpen"
                        class="flex h-10 w-10 items-center justify-center rounded-lg text-slate-600 transition hover:bg-slate-100 md:hidden dark:text-zinc-300 dark:hover:bg-zinc-800"
                        aria-label="Menu"
                    >
                        <svg x-show="!mobileOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" /></svg>
                        <svg x-show="mobileOpen" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 6l12 12M18 6 6 18" /></svg>
                    </button>
                </div>
            </nav>

            {{-- Mobile menu --}}
            <div
                x-show="mobileOpen"
                x-cloak
                x-transition
                class="border-t border-slate-200 bg-white px-4 py-4 md:hidden dark:border-zinc-800 dark:bg-zinc-900"
            >
                <div class="flex flex-col gap-1">
                    @foreach ($navLinks as $routeName => $label)
                        <a
                            href="{{ route($routeName) }}"
                            wire:navigate
                            class="rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs($routeName) ? 'bg-brand-50 text-brand-600 dark:bg-brand-900/40 dark:text-brand-300' : 'text-slate-700 dark:text-zinc-300' }}"
                        >
                            {{ $label }}
                        </a>
                    @endforeach
                    <a href="{{ route('login') }}" wire:navigate class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 dark:text-zinc-300">
                        Login Staf
                    </a>
                    @if ($whatsapp)
                        <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="mt-2 rounded-lg bg-green-600 px-3 py-2.5 text-center text-sm font-semibold text-white">
                            Hubungi via WhatsApp
                        </a>
                    @endif
                </div>
            </div>
        </header>

        <main class="flex-1">
            {{ $slot }}
        </main>

        {{-- Footer: 2-column layout based on actual content (R-05). Dark
        brand-900 background for contrast against white page body. --}}
        <footer class="bg-brand-900 text-white">
            <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6">
                <div class="grid gap-10 sm:grid-cols-2">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <img
                                src="{{ asset('images/logo-mustika-square.png') }}"
                                alt="Logo PT Mustika Makmur Abadi"
                                class="h-9 w-9 rounded-lg object-contain bg-white shadow-xs p-0.5"
                            >
                            <p class="font-display text-lg font-bold">PT Mustika Makmur Abadi</p>
                        </div>
                        <p class="mt-4 max-w-sm text-sm leading-relaxed text-brand-200">
                            Distributor resmi Yummy Bites dan beras impor, berdiri sejak {{ \App\Models\SiteSetting::get('established_year', '2019') }}.
                        </p>
                        <p class="mt-4 text-sm leading-relaxed text-brand-200">
                            {{ \App\Models\SiteSetting::get('office_address') }}
                        </p>
                    </div>
                    <div class="flex flex-col gap-8 sm:flex-row sm:gap-16">
                        <div>
                            <p class="text-sm font-bold text-white">Navigasi</p>
                            <ul class="mt-3 space-y-2 text-sm text-brand-200">
                                <li><a href="{{ route('home') }}" wire:navigate class="transition hover:text-white">Beranda</a></li>
                                <li><a href="{{ route('produk') }}" wire:navigate class="transition hover:text-white">Produk</a></li>
                                <li><a href="{{ route('tentang-kami') }}" wire:navigate class="transition hover:text-white">Tentang Kami</a></li>
                                <li><a href="{{ route('kontak') }}" wire:navigate class="transition hover:text-white">Kontak</a></li>
                            </ul>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-white">Lainnya</p>
                            <ul class="mt-3 space-y-2 text-sm text-brand-200">
                                <li><a href="{{ route('kebijakan-privasi') }}" wire:navigate class="transition hover:text-white">Kebijakan Privasi</a></li>
                                @if ($whatsapp)
                                    <li><a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="transition hover:text-white">WhatsApp</a></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="mt-10 border-t border-white/10 pt-6 text-xs text-brand-300">
                    <p>&copy; {{ now()->year }} PT Mustika Makmur Abadi. Semua hak cipta dilindungi.</p>
                </div>
            </div>
        </footer>
    </body>
</html>
