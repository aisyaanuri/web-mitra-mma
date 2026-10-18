<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-gradient-to-br from-[#166fe5] via-[#1065e2] to-[#0a4bb5] font-sans antialiased text-slate-800 dark:text-zinc-200">
        <div class="flex min-h-screen flex-col justify-between p-4 sm:p-6 lg:p-8">
            {{-- Top Navigation Bar --}}
            <div class="mx-auto w-full max-w-4xl xl:max-w-5xl flex items-center justify-between pb-3 sm:pb-4">
                <a
                    href="{{ route('home') }}"
                    wire:navigate
                    class="group inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-1.5 text-xs font-semibold text-white backdrop-blur-md transition hover:bg-white/25 hover:text-white shadow-xs"
                >
                    <svg class="h-3.5 w-3.5 transition group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Beranda
                </a>
                <span class="text-xs font-medium text-blue-100/90">Portal Resmi Karyawan & Mitra</span>
            </div>

            {{-- Centered Dual-Panel Floating Card --}}
            <div class="my-auto mx-auto w-full max-w-4xl xl:max-w-5xl overflow-hidden rounded-[28px] sm:rounded-3xl bg-white shadow-2xl shadow-blue-950/30 dark:bg-zinc-900 border border-white/20">
                <div class="grid grid-cols-1 lg:grid-cols-2">
                    {{-- Left Column: Vibrant Blue with 3D Truck Illustration --}}
                    <div class="relative bg-[#1065e2] p-8 sm:p-10 lg:p-12 flex flex-col justify-between text-white overflow-hidden">
                        {{-- Top Heading & Copy --}}
                        <div class="relative z-10">
                            <span class="inline-block rounded-full bg-white/20 px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-blue-100 mb-3 backdrop-blur-xs">
                                Logistik & Distribusi
                            </span>
                            <h1 class="font-display text-2xl sm:text-3xl font-bold leading-tight text-white">
                                Growing your Businesses without Worries
                            </h1>
                            <p class="mt-3 text-xs sm:text-sm leading-relaxed text-blue-100/90 font-normal">
                                Solusi rantai pasok terpercaya untuk produk makanan bayi dan beras impor berkualitas tinggi. Efisien, aman, dan tepat waktu.
                            </p>
                        </div>

                        {{-- Bottom 3D Truck Asset --}}
                        <div class="relative z-10 mt-6 lg:mt-auto pt-6 flex justify-center items-end">
                            <img
                                src="{{ asset('images/illustrations/truck-3d.jpg') }}"
                                alt="Distribusi PT Mustika Makmur Abadi"
                                class="w-full max-w-[320px] sm:max-w-[360px] object-contain drop-shadow-xl transition duration-500 hover:scale-105"
                            >
                        </div>
                    </div>

                    {{-- Right Column: Form Container --}}
                    <div class="bg-white p-8 sm:p-10 lg:p-12 flex flex-col justify-between dark:bg-zinc-900">
                        <div>
                            {{ $slot }}
                        </div>

                        {{-- Footer Copyright inside card --}}
                        <div class="mt-8 border-t border-slate-100 pt-4 text-center text-[11px] text-slate-400 dark:border-zinc-800 dark:text-zinc-500">
                            Copyright &copy; {{ now()->year }} PT Mustika Makmur Abadi. All Rights Reserved.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bottom Subtle Label --}}
            <div class="py-2 text-center text-xs text-blue-100/80">
                PT Mustika Makmur Abadi &bull; Integrated Supply Chain Management
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
