<?php

namespace Database\Seeders;

use App\Models\CompanyContent;
use Illuminate\Database\Seeder;

/**
 * Seeds real company profile copy sourced from the client's existing
 * temporary site (see docs/Pemetaan Konten Company Profile...). Visi &
 * misi is intentionally NOT seeded here — the client hasn't provided it
 * yet, so the Tentang Kami page hides that section until it exists.
 */
class CompanyContentSeeder extends Seeder
{
    public function run(): void
    {
        $contents = [
            [
                'section_key' => 'beranda_hero',
                'title' => 'PT Mustika Makmur Abadi',
                'body' => 'PT Mustika Makmur Abadi adalah distributor snack bayi dan beras impor yang berbasis di Sidoarjo, berdiri sejak 2019. Kami adalah distributor resmi brand Yummy Bites serta beras impor dari Jepang, Korea, India, Vietnam, dan Thailand, melayani lebih dari 355 outlet modern & tradisional di Indonesia bagian timur.',
                'sort_order' => 1,
            ],
            [
                'section_key' => 'beranda_distribusi',
                'title' => 'Sarana Distribusi',
                'body' => 'Kami memiliki armada pengiriman sendiri (1 truk box besar dan 1 truk box kecil) yang melayani pengiriman gratis ongkir untuk wilayah Surabaya, Sidoarjo, Gresik, Malang, dan Mojokerto, Senin–Sabtu. Untuk wilayah di luar area tersebut, pengiriman dilakukan melalui ekspedisi rekanan.',
                'sort_order' => 2,
            ],
            [
                'section_key' => 'tentang_kami',
                'title' => 'Tentang Kami',
                'body' => "PT Mustika Makmur Abadi adalah distributor FnB yang berlokasi di Sidoarjo, berdiri sejak 2019. Sejak awal berdiri, kami dipercaya sebagai distributor resmi brand 'Yummy Bites' — camilan/makanan pendamping ASI (MPASI) untuk bayi usia 6 bulan ke atas hingga 1 tahun ke atas — serta beras impor dari Jepang, Korea, India, Vietnam, dan Thailand untuk kebutuhan rumah tangga, restoran kecil dan besar, hingga hotel.\n\nDengan didukung 18 tenaga kerja ahli di bidang distribusi dan armada pengiriman sendiri, kami telah menjangkau lebih dari 355 outlet modern & tradisional di Jawa Timur, Sulawesi, Kalimantan, Timor Leste, Maluku, NTT, NTB, dan Bali.",
                'sort_order' => 1,
            ],
        ];

        foreach ($contents as $content) {
            CompanyContent::updateOrCreate(['section_key' => $content['section_key']], $content);
        }
    }
}
