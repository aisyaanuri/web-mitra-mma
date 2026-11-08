@extends('layouts.public')
@section('content')


<div>
    {{-- Hero: Brand gradient background instead of flat white --}}
    <section class="bg-gradient-brand py-14 sm:py-20">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 reveal">
            <h1 class="heading-display font-display text-4xl font-extrabold text-white sm:text-5xl">Mari Bekerja Sama</h1>
            <p class="mt-4 max-w-xl text-lg text-white/80">
                Tertarik menjadi mitra distribusi kami? Kirimkan pesan Anda atau hubungi kami langsung.
            </p>
        </div>
    </section>

    {{-- Split: form left, info right --}}
    <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6">
        <div class="grid gap-10 lg:grid-cols-5 lg:gap-12">
            <form method="POST" action="{{ route('kontak.store') }}" class="space-y-5 lg:col-span-3 reveal">
                @csrf
                @if (session('contact-submitted'))
                    <div class="flex items-start gap-3 rounded-xl bg-mint-500/10 px-5 py-4 text-sm text-mint-600 dark:text-mint-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        <span>Terima kasih, pesan Anda sudah kami terima. Tim kami akan segera menghubungi Anda kembali.</span>
                    </div>
                @endif

                <div class="absolute -left-[9999px]" aria-hidden="true">
                    <label for="website">Website</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="name" class="block text-sm font-bold text-slate-700 dark:text-zinc-300">Nama</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm transition focus:border-brand-400 focus:bg-white focus:ring-2 focus:ring-brand-100 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-brand-900">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-bold text-slate-700 dark:text-zinc-300">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm transition focus:border-brand-400 focus:bg-white focus:ring-2 focus:ring-brand-100 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-brand-900">
                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="phone" class="block text-sm font-bold text-slate-700 dark:text-zinc-300">No. Telepon (opsional)</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm transition focus:border-brand-400 focus:bg-white focus:ring-2 focus:ring-brand-100 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-brand-900">
                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="message" class="block text-sm font-bold text-slate-700 dark:text-zinc-300">Pesan</label>
                    <textarea id="message" name="message" rows="5" required
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm transition focus:border-brand-400 focus:bg-white focus:ring-2 focus:ring-brand-100 focus:outline-none dark:border-zinc-700 dark:bg-zinc-800 dark:focus:ring-brand-900">{{ old('message') }}</textarea>
                    @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full rounded-xl bg-brand-600 px-5 py-3.5 text-sm font-extrabold text-white shadow-lg shadow-brand-600/25 transition hover:bg-brand-700 hover:shadow-xl focus:ring-2 focus:ring-brand-400 focus:ring-offset-2 focus:outline-none">
                    Kirim Pesan
                </button>

                <p class="text-center text-xs text-slate-500 dark:text-zinc-400">
                    Dengan mengirim formulir ini, Anda menyetujui
                    <a href="{{ route('kebijakan-privasi') }}"  class="font-bold text-brand-600 underline hover:text-brand-700">Kebijakan Privasi</a> kami.
                </p>
            </form>

            <div class="space-y-5 lg:col-span-2 reveal">
                <div class="rounded-2xl bg-gradient-brand p-6 text-white">
                    <div class="flex items-start gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/15">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21c-4.5-4.2-7-7.9-7-11a7 7 0 1 1 14 0c0 3.1-2.5 6.8-7 11Z" /><circle cx="12" cy="10" r="2.5" /></svg>
                        </span>
                        <div>
                            <h2 class="text-sm font-bold">Alamat Kantor</h2>
                            <p class="mt-1 text-sm text-white/80">{{ \App\Models\SiteSetting::get('office_address') }}</p>
                        </div>
                    </div>
                </div>

                @if ($mapsSrc = \App\Models\SiteSetting::get('google_maps_embed_src'))
                    <div class="overflow-hidden rounded-2xl shadow-lg">
                        <iframe
                            src="{{ $mapsSrc }}"
                            width="100%"
                            height="280"
                            style="border:0"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Lokasi PT Mustika Makmur Abadi"
                        ></iframe>
                    </div>
                @endif

                @if ($whatsapp = \App\Models\SiteSetting::get('whatsapp_number'))
                    <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener"
                        class="flex items-center justify-center gap-2.5 rounded-2xl bg-green-600 px-5 py-4 text-sm font-extrabold text-white shadow-lg shadow-green-600/20 transition hover:bg-green-700 hover:shadow-xl focus:ring-2 focus:ring-green-400 focus:ring-offset-2 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M12.04 2c-5.46 0-9.9 4.44-9.9 9.9 0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 0 0 4.79 1.22h.01c5.46 0 9.9-4.44 9.9-9.9 0-2.64-1.03-5.12-2.9-6.99A9.82 9.82 0 0 0 12.04 2zm5.8 14.07c-.24.68-1.4 1.3-1.93 1.36-.5.06-1 .27-3.36-.7-2.84-1.17-4.66-4.05-4.8-4.24-.14-.19-1.15-1.53-1.15-2.92s.72-2.06.98-2.35c.26-.29.56-.36.75-.36h.54c.17 0 .4-.03.62.48.24.56.8 1.95.87 2.09.07.14.11.3.02.49-.09.19-.14.3-.28.46-.14.16-.29.36-.42.48-.14.13-.29.28-.12.55.17.28.75 1.24 1.61 2.01 1.11 1 2.04 1.31 2.32 1.46.28.14.44.12.6-.07.17-.19.72-.84.91-1.13.19-.28.38-.23.63-.14.26.09 1.65.78 1.93.92.28.14.47.21.54.33.07.12.07.68-.17 1.36z"/></svg>
                        Hubungi via WhatsApp
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
