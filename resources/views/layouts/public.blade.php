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
    <body class="flex min-h-screen flex-col bg-white text-zinc-800 dark:bg-zinc-900 dark:text-zinc-200">
        <header class="sticky top-0 z-40 border-b border-zinc-200 bg-white/90 backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/90">
            <nav class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
                <a href="{{ route('home') }}" wire:navigate class="text-lg font-semibold text-zinc-900 dark:text-white">
                    PT Mustika Makmur Abadi
                </a>

                <div class="hidden items-center gap-6 text-sm font-medium sm:flex">
                    <a href="{{ route('home') }}" wire:navigate class="hover:text-amber-600">{{ __('Beranda') }}</a>
                    <a href="{{ route('produk') }}" wire:navigate class="hover:text-amber-600">{{ __('Produk') }}</a>
                    <a href="{{ route('tentang-kami') }}" wire:navigate class="hover:text-amber-600">{{ __('Tentang Kami') }}</a>
                    <a href="{{ route('kontak') }}" wire:navigate class="hover:text-amber-600">{{ __('Kontak') }}</a>
                    <a href="{{ route('login') }}" wire:navigate class="hover:text-amber-600">{{ __('Login Staf') }}</a>
                </div>

                @php $whatsapp = \App\Models\SiteSetting::get('whatsapp_number'); @endphp
                @if ($whatsapp)
                    <a
                        href="https://wa.me/{{ $whatsapp }}"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center gap-2 rounded-full bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700"
                    >
                        {{ __('Hubungi via WhatsApp') }}
                    </a>
                @endif
            </nav>
        </header>

        <main class="flex-1">
            {{ $slot }}
        </main>

        <footer class="border-t border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-950">
            <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
                <div class="grid gap-8 sm:grid-cols-3">
                    <div>
                        <p class="text-base font-semibold text-zinc-900 dark:text-white">PT Mustika Makmur Abadi</p>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                            Distributor resmi Yummy Bites dan beras impor, berdiri sejak {{ \App\Models\SiteSetting::get('established_year', '2019') }}.
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Alamat') }}</p>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                            {{ \App\Models\SiteSetting::get('office_address') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Tautan') }}</p>
                        <ul class="mt-2 space-y-1 text-sm text-zinc-600 dark:text-zinc-400">
                            <li><a href="{{ route('tentang-kami') }}" wire:navigate class="hover:text-amber-600">{{ __('Tentang Kami') }}</a></li>
                            <li><a href="{{ route('kontak') }}" wire:navigate class="hover:text-amber-600">{{ __('Kontak') }}</a></li>
                            <li><a href="{{ route('kebijakan-privasi') }}" wire:navigate class="hover:text-amber-600">{{ __('Kebijakan Privasi') }}</a></li>
                        </ul>
                    </div>
                </div>
                <p class="mt-8 text-xs text-zinc-500 dark:text-zinc-500">
                    &copy; {{ now()->year }} PT Mustika Makmur Abadi. {{ __('Semua hak cipta dilindungi.') }}
                </p>
            </div>
        </footer>
    </body>
</html>
