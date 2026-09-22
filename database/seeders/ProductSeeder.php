<?php

namespace Database\Seeders;

use App\Enums\ProductCategory;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Real Yummy Bites catalog, sourced from product photos the client
 * supplied in the "ALL PRODUK" folder (2026-09-22) and description
 * wording matched to the brand voice on yummybites.com/id ("Bunda",
 * "Si Kecil", natural/no-preservative positioning).
 *
 * Beras Impor stays placeholder — the client has not yet sent real
 * rice product names or photos, only confirmed the five countries of
 * origin already used elsewhere on the site.
 */
class ProductSeeder extends Seeder
{
    /**
     * @var array<int, array{name: string, slug: string, sku: string, source: string, weight: string, description: string, featured?: bool}>
     */
    private const YUMMY_BITES = [
        [
            'name' => 'Yummy Bites Meal Potato & Cheese (Step 1)',
            'slug' => 'yummy-bites-meal-potato-cheese-step-1',
            'sku' => 'YB-MEAL-POTCHEESE-1',
            'source' => 'MEAL/Yummy Bites 0019.png',
            'weight' => '145g',
            'description' => 'Bubur MPASI siap santap dengan resep rumahan, kentang lembut berpadu keju gurih untuk tahap awal MPASI Si Kecil (Step 1). Bebas gluten dan rendah sodium.',
            'featured' => true,
        ],
        [
            'name' => 'Yummy Bites Meal Chicken Risotto with Cheese (Step 3)',
            'slug' => 'yummy-bites-meal-chicken-risotto-cheese-step-3',
            'sku' => 'YB-MEAL-CHIXRISOTTO-3',
            'source' => 'MEAL/Yummy Bites 0010.png',
            'weight' => '145g',
            'description' => 'Risotto ayam dan keju bertekstur lebih kaya untuk Si Kecil yang sudah terbiasa dengan variasi rasa (Step 3), dibuat dari bahan pilihan tanpa pengawet.',
            'featured' => true,
        ],
        [
            'name' => 'Yummy Bites Meal Salmon, Egg & Spinach (Step 3)',
            'slug' => 'yummy-bites-meal-salmon-egg-spinach-step-3',
            'sku' => 'YB-MEAL-SALMONEGG-3',
            'source' => 'MEAL/Yummy Bites 0001.png',
            'weight' => '145g',
            'description' => 'Kombinasi ikan salmon, telur, dan bayam untuk kebutuhan gizi MPASI lanjutan (Step 3), tinggi omega 3 serta sumber vitamin K dan B3.',
        ],
        [
            'name' => 'Yummy Bites Crunchies Chocolate Malt',
            'slug' => 'yummy-bites-crunchies-chocolate-malt',
            'sku' => 'YB-CRUNCH-CHOCOMALT',
            'source' => 'Crunchies-Choco1-edit.png',
            'weight' => '25g',
            'description' => 'Snack stik panggang rasa cokelat malt yang mudah meleleh di mulut, rendah sodium dan bebas gluten, diperkaya DHA serta omega 3 & 6.',
            'featured' => true,
        ],
        [
            'name' => 'Yummy Bites Crunchies Cheese',
            'slug' => 'yummy-bites-crunchies-cheese',
            'sku' => 'YB-CRUNCH-CHEESE',
            'source' => 'Crunchies-Cheese2-edit.png',
            'weight' => '25g',
            'description' => 'Snack stik panggang rasa keju gurih, mudah meleleh di mulut Si Kecil, rendah sodium dan bebas gluten, diperkaya DHA serta omega 3 & 6.',
        ],
        [
            'name' => 'Yummy Bites Crunchies Strawberry',
            'slug' => 'yummy-bites-crunchies-strawberry',
            'sku' => 'YB-CRUNCH-STROBERI',
            'source' => 'Crunchies-Stroberi2-edit.png',
            'weight' => '25g',
            'description' => 'Snack stik panggang rasa stroberi yang mudah meleleh di mulut, rendah sodium dan bebas gluten, diperkaya DHA serta omega 3 & 6.',
        ],
        [
            'name' => 'Yummy Bites BebeRoll Cheese',
            'slug' => 'yummy-bites-beberoll-cheese',
            'sku' => 'YB-ROLL-CHEESE',
            'source' => 'BEBEROLL/2-removebg-preview.png',
            'weight' => '40g',
            'description' => 'Camilan roll panggang rasa keju, tinggi kalsium dan diperkaya vitamin E, praktis dibawa untuk camilan Si Kecil di mana saja.',
        ],
        [
            'name' => 'Yummy Bites BebeRoll Blueberry',
            'slug' => 'yummy-bites-beberoll-blueberry',
            'sku' => 'YB-ROLL-BLUEBERRY',
            'source' => 'BEBEROLL/3-removebg-preview.png',
            'weight' => '40g',
            'description' => 'Camilan roll panggang rasa blueberry, tinggi kalsium dan diperkaya vitamin E, praktis dibawa untuk camilan Si Kecil di mana saja.',
            'featured' => true,
        ],
        [
            'name' => 'Yummy Bites BebeRoll Strawberry',
            'slug' => 'yummy-bites-beberoll-strawberry',
            'sku' => 'YB-ROLL-STROBERI',
            'source' => 'BEBEROLL/4-removebg-preview.png',
            'weight' => '40g',
            'description' => 'Camilan roll panggang rasa stroberi, tinggi kalsium dan diperkaya vitamin E, praktis dibawa untuk camilan Si Kecil di mana saja.',
        ],
        [
            'name' => 'Yummy Bites Free Range Chicken Broth',
            'slug' => 'yummy-bites-free-range-chicken-broth',
            'sku' => 'YB-KALDU-AYAM',
            'source' => 'CHICKEN.png',
            'weight' => '50g',
            'description' => 'Kaldu bubuk alami dari ayam kampung pilihan, tanpa MSG, pewarna, maupun pengawet buatan — praktis untuk menambah cita rasa gurih masakan Si Kecil.',
        ],
        [
            'name' => 'Yummy Bites Beef Broth',
            'slug' => 'yummy-bites-beef-broth',
            'sku' => 'YB-KALDU-SAPI',
            'source' => 'BEEF.png',
            'weight' => '50g',
            'description' => 'Kaldu bubuk alami dari daging sapi pilihan, tanpa MSG, pewarna, maupun pengawet buatan — praktis untuk menambah cita rasa gurih masakan Si Kecil.',
        ],
        [
            'name' => 'Yummy Bites Cheese Broth',
            'slug' => 'yummy-bites-cheese-broth',
            'sku' => 'YB-KALDU-KEJU',
            'source' => 'CHEESE.png',
            'weight' => '50g',
            'description' => 'Kaldu bubuk alami rasa keju, tanpa MSG, pewarna, maupun pengawet buatan — praktis untuk menambah cita rasa gurih masakan Si Kecil.',
        ],
        [
            'name' => 'Yummy Bites Wild Forest Honey',
            'slug' => 'yummy-bites-wild-forest-honey',
            'sku' => 'YB-MADU-FOREST',
            'source' => 'WFH 5.png',
            'weight' => '275g',
            'description' => 'Madu hutan murni, natural dan raw tanpa campuran tambahan — melengkapi menu sehat keluarga sehari-hari.',
        ],
        [
            'name' => 'Yummy Bites Black Elderberry Honey',
            'slug' => 'yummy-bites-black-elderberry-honey',
            'sku' => 'YB-MADU-ELDERBERRY',
            'source' => 'BEH 5.png',
            'weight' => '275g',
            'description' => 'Madu murni dengan black elderberry, natural dan raw tanpa campuran tambahan — melengkapi menu sehat keluarga sehari-hari.',
        ],
        [
            'name' => 'Yummy Bites Purée Blueberry, Banana & Apple',
            'slug' => 'yummy-bites-puree-blueberry-banana-apple',
            'sku' => 'YB-PUREE-BBA',
            'source' => 'BBA 1.png',
            'weight' => '100g',
            'description' => 'Puree buah blueberry, pisang, dan apel tanpa tambahan gula, sumber serat dan kalium, dikemas dalam pouch praktis untuk camilan sehat Si Kecil.',
            'featured' => true,
        ],
        [
            'name' => 'Yummy Bites Purée Strawberry, Guava & Banana',
            'slug' => 'yummy-bites-puree-strawberry-guava-banana',
            'sku' => 'YB-PUREE-SGB',
            'source' => 'SGB 1.png',
            'weight' => '100g',
            'description' => 'Puree buah stroberi, jambu, dan pisang tanpa tambahan gula, sumber serat dan vitamin B6 & B9, dikemas dalam pouch praktis untuk camilan sehat Si Kecil.',
        ],
        [
            'name' => 'Yummy Bites Furikake Chicken',
            'slug' => 'yummy-bites-furikake-chicken',
            'sku' => 'YB-FURI-AYAM',
            'source' => 'TAP09881.png',
            'weight' => '35g',
            'description' => 'Taburan rumput laut rasa ayam untuk melengkapi nasi Si Kecil, tinggi vitamin D, B1, dan kalsium, dengan kandungan DHA & EPA.',
        ],
        [
            'name' => 'Yummy Bites Furikake Seafood',
            'slug' => 'yummy-bites-furikake-seafood',
            'sku' => 'YB-FURI-SEAFOOD',
            'source' => 'TAP09874.png',
            'weight' => '35g',
            'description' => 'Taburan rumput laut rasa seafood untuk melengkapi nasi Si Kecil, tinggi vitamin D, B1, dan kalsium, dengan kandungan DHA & EPA.',
        ],
        [
            'name' => 'Yummy Bites Crackers Blueberry',
            'slug' => 'yummy-bites-crackers-blueberry',
            'sku' => 'YB-CRACKER-BLUEBERRY',
            'source' => 'GSP_8756.png',
            'weight' => '50g',
            'description' => 'Kerupuk beras rasa blueberry yang mudah larut di mulut Si Kecil (6 bulan+), mudah digenggam, tinggi vitamin dan mineral penting seperti kalsium, zat besi, dan zinc.',
        ],
        [
            'name' => 'Yummy Bites Crackers Vegetables',
            'slug' => 'yummy-bites-crackers-vegetables',
            'sku' => 'YB-CRACKER-SAYUR',
            'source' => 'GSP_8751 rev.png',
            'weight' => '50g',
            'description' => 'Kerupuk beras rasa sayuran yang mudah larut di mulut Si Kecil (6 bulan+), mudah digenggam, tinggi vitamin dan mineral penting seperti kalsium, zat besi, dan zinc.',
        ],
    ];

    /**
     * @var array<int, array{country: string, description: string}>
     */
    private const BERAS_IMPOR = [
        ['country' => 'Jepang', 'description' => 'Beras impor asal Jepang, dipasok untuk kebutuhan rumah tangga, restoran, hingga hotel. Nama varian dan foto produk menyusul konfirmasi dari klien.'],
        ['country' => 'Korea', 'description' => 'Beras impor asal Korea, dipasok untuk kebutuhan rumah tangga, restoran, hingga hotel. Nama varian dan foto produk menyusul konfirmasi dari klien.'],
        ['country' => 'India', 'description' => 'Beras impor asal India, dipasok untuk kebutuhan rumah tangga, restoran, hingga hotel. Nama varian dan foto produk menyusul konfirmasi dari klien.'],
        ['country' => 'Vietnam', 'description' => 'Beras impor asal Vietnam, dipasok untuk kebutuhan rumah tangga, restoran, hingga hotel. Nama varian dan foto produk menyusul konfirmasi dari klien.'],
        ['country' => 'Thailand', 'description' => 'Beras impor asal Thailand, dipasok untuk kebutuhan rumah tangga, restoran, hingga hotel. Nama varian dan foto produk menyusul konfirmasi dari klien.'],
    ];

    public function run(): void
    {
        $sourceRoot = base_path('ALL PRODUK');
        $targetDir = 'products';
        File::ensureDirectoryExists(storage_path('app/public/'.$targetDir));

        foreach (self::YUMMY_BITES as $item) {
            $product = Product::updateOrCreate(
                ['sku' => $item['sku']],
                [
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'category' => ProductCategory::YummyBites,
                    'country_of_origin' => null,
                    'description' => $item['description'],
                    'weight' => $item['weight'],
                    'is_active' => true,
                    'is_featured' => $item['featured'] ?? false,
                    'meta_title' => $item['name'],
                    'meta_description' => $item['description'],
                ]
            );

            $sourcePath = $sourceRoot.'/'.$item['source'];

            if (! File::exists($sourcePath)) {
                continue;
            }

            $extension = File::extension($sourcePath);
            $storedPath = $targetDir.'/'.$item['slug'].'.'.$extension;
            File::copy($sourcePath, storage_path('app/public/'.$storedPath));

            ProductImage::updateOrCreate(
                ['product_id' => $product->id, 'sort_order' => 0],
                ['image_path' => $storedPath, 'alt_text' => $item['name']]
            );
        }

        foreach (self::BERAS_IMPOR as $index => $item) {
            $name = 'Beras Impor '.$item['country'].' 5kg';

            Product::updateOrCreate(
                ['sku' => 'BERAS-'.strtoupper(Str::slug($item['country']))],
                [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'category' => ProductCategory::BerasImpor,
                    'country_of_origin' => $item['country'],
                    'description' => $item['description'],
                    'weight' => '5kg',
                    'is_active' => true,
                    'is_featured' => $index === 0,
                    'meta_title' => $name,
                    'meta_description' => $item['description'],
                ]
            );
        }
    }
}
