<?php

use Artesaos\SEOTools\Facades\SEOMeta;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::public')] class extends Component
{
    public function mount(): void
    {
        SEOMeta::setTitle('Kebijakan Privasi');
        SEOMeta::setDescription('Kebijakan privasi PT Mustika Makmur Abadi mengenai data yang dikumpulkan melalui website ini.');
    }
}; ?>

<div class="mx-auto max-w-3xl px-4 py-16 sm:px-6">
    <h1 class="text-3xl font-bold text-zinc-900 dark:text-white">Kebijakan Privasi</h1>
    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">Terakhir diperbarui: {{ now()->translatedFormat('d F Y') }}</p>

    <div class="mt-8 space-y-6 text-zinc-600 dark:text-zinc-400">
        <section>
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">1. Data yang Kami Kumpulkan</h2>
            <p class="mt-2">
                Kami hanya mengumpulkan data yang Anda berikan secara langsung melalui formulir kontak pada
                website ini, yaitu: nama, alamat email, nomor telepon (opsional), dan isi pesan Anda. Alamat IP
                pengirim juga dicatat secara otomatis untuk keperluan keamanan dan pencegahan spam.
            </p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">2. Tujuan Penggunaan Data</h2>
            <p class="mt-2">
                Data yang Anda kirimkan melalui formulir kontak digunakan semata-mata untuk menindaklanjuti
                pertanyaan atau permintaan kerja sama Anda dengan PT Mustika Makmur Abadi. Kami tidak menggunakan
                data tersebut untuk tujuan pemasaran tanpa persetujuan Anda, dan tidak membagikannya kepada pihak
                ketiga mana pun selain yang diwajibkan oleh hukum.
            </p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">3. Prinsip Minimalisasi Data</h2>
            <p class="mt-2">
                Kami hanya meminta data yang benar-benar diperlukan untuk merespons pesan Anda. Kami tidak
                meminta data pribadi yang tidak relevan dengan keperluan komunikasi tersebut.
            </p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">4. Penyimpanan &amp; Keamanan Data</h2>
            <p class="mt-2">
                Data disimpan pada basis data internal kami dengan akses yang dibatasi hanya untuk staf yang
                berwenang menangani komunikasi pelanggan dan mitra. Kami menerapkan praktik keamanan standar
                industri untuk melindungi data dari akses yang tidak sah.
            </p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">5. Hak Anda</h2>
            <p class="mt-2">
                Anda berhak meminta akses, koreksi, atau penghapusan data pribadi yang telah Anda kirimkan kepada
                kami melalui formulir kontak, sesuai dengan Undang-Undang No. 27 Tahun 2022 tentang Pelindungan
                Data Pribadi. Untuk mengajukan permintaan ini, silakan hubungi kami melalui halaman
                <a href="{{ route('kontak') }}" wire:navigate class="font-medium text-amber-600 hover:text-amber-700">Kontak</a>.
            </p>
        </section>

        <section>
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">6. Perubahan Kebijakan</h2>
            <p class="mt-2">
                Kebijakan privasi ini dapat diperbarui dari waktu ke waktu. Perubahan akan dipublikasikan pada
                halaman ini beserta tanggal pembaruannya.
            </p>
        </section>
    </div>
</div>
