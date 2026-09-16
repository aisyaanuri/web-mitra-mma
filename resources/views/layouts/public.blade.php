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
        <header
            x-data="{ scrolled: false }"
            x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 12)"
            :class="scrolled ? 'shadow-md border-transparent' : 'border-zinc-200 dark:border-zinc-800'"
            class="sticky top-0 z-40 border-b bg-white/90 backdrop-blur transition-shadow dark:bg-zinc-900/90"
        >
            <nav class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
                <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-brand-400 to-brand-700 text-sm font-bold text-white shadow-sm">
                        MMA
                    </span>
                    <span class="text-lg font-semibold text-zinc-900 dark:text-white">
                        PT Mustika Makmur Abadi
                    </span>
                </a>

                <div class="hidden items-center gap-6 text-sm font-medium sm:flex">
                    <a href="{{ route('home') }}" wire:navigate class="transition hover:text-brand-600 dark:hover:text-brand-300">{{ __('Beranda') }}</a>
                    <a href="{{ route('produk') }}" wire:navigate class="transition hover:text-brand-600 dark:hover:text-brand-300">{{ __('Produk') }}</a>
                    <a href="{{ route('tentang-kami') }}" wire:navigate class="transition hover:text-brand-600 dark:hover:text-brand-300">{{ __('Tentang Kami') }}</a>
                    <a href="{{ route('kontak') }}" wire:navigate class="transition hover:text-brand-600 dark:hover:text-brand-300">{{ __('Kontak') }}</a>
                    <a href="{{ route('login') }}" wire:navigate class="transition hover:text-brand-600 dark:hover:text-brand-300">{{ __('Login Staf') }}</a>
                </div>

                @php $whatsapp = \App\Models\SiteSetting::get('whatsapp_number'); @endphp
                @if ($whatsapp)
                    <a
                        href="https://wa.me/{{ $whatsapp }}"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-2 rounded-full bg-green-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-700"
                    >
                        {{ __('Hubungi via WhatsApp') }}
                    </a>
                @endif
            </nav>
        </header>

        <main class="flex-1">
            {{ $slot }}
        </main>

        <footer class="bg-brand-900 text-brand-100">
            <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
                <div class="grid gap-8 sm:grid-cols-3">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-brand-300 to-brand-600 text-sm font-bold text-white">
                                MMA
                            </span>
                            <p class="text-base font-semibold text-white">PT Mustika Makmur Abadi</p>
                        </div>
                        <p class="mt-3 text-sm text-brand-200">
                            Distributor resmi Yummy Bites dan beras impor, berdiri sejak {{ \App\Models\SiteSetting::get('established_year', '2019') }}.
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">{{ __('Alamat') }}</p>
                        <p class="mt-3 text-sm text-brand-200">
                            {{ \App\Models\SiteSetting::get('office_address') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">{{ __('Tautan') }}</p>
                        <ul class="mt-3 space-y-1.5 text-sm text-brand-200">
                            <li><a href="{{ route('produk') }}" wire:navigate class="transition hover:text-white">{{ __('Produk') }}</a></li>
                            <li><a href="{{ route('tentang-kami') }}" wire:navigate class="transition hover:text-white">{{ __('Tentang Kami') }}</a></li>
                            <li><a href="{{ route('kontak') }}" wire:navigate class="transition hover:text-white">{{ __('Kontak') }}</a></li>
                            <li><a href="{{ route('kebijakan-privasi') }}" wire:navigate class="transition hover:text-white">{{ __('Kebijakan Privasi') }}</a></li>
                        </ul>
                    </div>
                </div>
                <p class="mt-10 border-t border-white/10 pt-6 text-xs text-brand-300">
                    &copy; {{ now()->year }} PT Mustika Makmur Abadi. {{ __('Semua hak cipta dilindungi.') }}
                </p>
            </div>
        </footer>
    </body>
</html>
