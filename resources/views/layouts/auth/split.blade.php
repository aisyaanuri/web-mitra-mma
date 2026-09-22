<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-zinc-900">
        <div class="relative grid h-dvh flex-col items-center justify-center lg:max-w-none lg:grid-cols-2">
            <div class="relative hidden h-full flex-col overflow-hidden bg-brand-900 p-10 text-white lg:flex">
                <span class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-brand-600/20 blur-3xl"></span>

                <a href="{{ route('home') }}" class="relative z-20 flex items-center gap-3" wire:navigate>
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-brand-400 via-brand-600 to-brand-800 text-xs font-bold tracking-wide text-white shadow-md shadow-brand-500/30">
                        MMA
                    </span>
                    <span class="font-display text-lg font-bold">{{ config('app.name') }}</span>
                </a>

                <div class="relative z-20 mt-auto">
                    <p class="font-display text-3xl leading-tight font-bold text-white">Portal Internal Distribusi</p>
                    <p class="mt-3 max-w-sm text-sm text-brand-100">
                        Akses untuk tim Gudang, Marketplace, dan Manajemen dalam mengelola pesanan, produk, dan rekonsiliasi Shopee.
                    </p>
                </div>
            </div>

            <div class="w-full px-8 sm:px-0 lg:p-8">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <a href="{{ route('home') }}" class="z-20 flex flex-col items-center gap-2 lg:hidden" wire:navigate>
                        <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-brand-400 via-brand-600 to-brand-800 text-xs font-bold tracking-wide text-white shadow-md shadow-brand-500/30">
                            MMA
                        </span>
                        <span class="sr-only">{{ config('app.name') }}</span>
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
