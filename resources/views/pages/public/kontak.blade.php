<?php

use App\Models\ContactMessage;
use App\Models\SiteSetting;
use Artesaos\SEOTools\Facades\SEOMeta;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::public')] class extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $message = '';

    // Honeypot: left blank by humans, filled in by most spam bots.
    public string $website = '';

    public bool $submitted = false;

    public function mount(): void
    {
        SEOMeta::setTitle('Kontak');
        SEOMeta::setDescription('Hubungi PT Mustika Makmur Abadi untuk kerja sama distribusi Yummy Bites dan beras impor.');
    }

    public function submit(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        if (filled($this->website)) {
            // Pretend success so the bot doesn't learn the honeypot was tripped.
            $this->resetForm();

            return;
        }

        ContactMessage::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'message' => $this->message,
            'ip_address' => request()->ip(),
        ]);

        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'email', 'phone', 'message', 'website']);
        $this->submitted = true;
    }
}; ?>

<div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
    <h1 class="text-3xl font-bold text-zinc-900 sm:text-4xl dark:text-white" data-aos="fade-up">Kontak</h1>
    <p class="mt-3 max-w-2xl text-zinc-600 dark:text-zinc-400" data-aos="fade-up">
        Tertarik menjadi mitra distribusi kami? Kirimkan pesan Anda atau hubungi kami langsung.
    </p>

    <div class="mt-10 grid gap-10 lg:grid-cols-2" data-aos="fade-up">
        <form wire:submit="submit" class="space-y-4">
            @if ($submitted)
                <div class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-950 dark:text-green-400">
                    Terima kasih, pesan Anda sudah kami terima. Tim kami akan segera menghubungi Anda kembali.
                </div>
            @endif

            {{-- Honeypot field: hidden from real visitors via off-screen positioning. --}}
            <div class="absolute -left-[9999px]" aria-hidden="true">
                <label for="website">Website</label>
                <input type="text" id="website" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Nama</label>
                <input type="text" id="name" wire:model="name" required
                    class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Email</label>
                <input type="email" id="email" wire:model="email" required
                    class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">No. Telepon (opsional)</label>
                <input type="text" id="phone" wire:model="phone"
                    class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900">
                @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="message" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Pesan</label>
                <textarea id="message" wire:model="message" rows="5" required
                    class="mt-1 w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900"></textarea>
                @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="w-full rounded-lg bg-zinc-900 px-5 py-3 text-sm font-semibold text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                Kirim Pesan
            </button>
        </form>

        <div class="space-y-6">
            <div>
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-white">Alamat Kantor</h2>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ SiteSetting::get('office_address') }}</p>
            </div>

            @if ($mapsSrc = SiteSetting::get('google_maps_embed_src'))
                <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-800">
                    <iframe
                        src="{{ $mapsSrc }}"
                        width="100%"
                        height="300"
                        style="border:0"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Lokasi PT Mustika Makmur Abadi"
                    ></iframe>
                </div>
            @endif

            @if ($whatsapp = SiteSetting::get('whatsapp_number'))
                <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-5 py-3 text-sm font-semibold text-white hover:bg-green-700">
                    Hubungi via WhatsApp
                </a>
            @endif
        </div>
    </div>
</div>
