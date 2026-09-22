<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $officeAddress = 'Pergudangan Sirie Blok G-6, Lingkar Timur, Rangkah Kidul, Sidoarjo';

        $settings = [
            'whatsapp_number' => '6281359147191',
            'office_address' => $officeAddress,
            'google_maps_embed_src' => 'https://www.google.com/maps?q='.rawurlencode($officeAddress).'&output=embed',
            'outlets_count' => '355',
            'staff_count' => '18',
            'established_year' => '2019',
            'distribution_free_area' => 'Surabaya, Sidoarjo, Gresik, Malang, Mojokerto',
            'distribution_area' => 'Jawa Timur, Sulawesi, Kalimantan, Timor Leste, Maluku, NTT, NTB, dan Bali',
        ];

        foreach ($settings as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
