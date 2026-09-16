<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed a representative product catalog. Real product names/photos are
     * still pending client confirmation (see docs/) — this is sample data
     * for development, not the final catalog. No product_images are
     * seeded since there are no real photo files to point them at yet;
     * the public catalog shows a placeholder until an admin uploads real
     * photos via the Product CRUD (Fase 4).
     */
    public function run(): void
    {
        Product::factory()->count(12)->create();

        // Guarantee the homepage "Produk Unggulan" section always has enough
        // items to fill its grid, regardless of the factory's random featured flag.
        Product::query()->inRandomOrder()->limit(4)->get()->each(
            fn (Product $product) => $product->update(['is_featured' => true])
        );
    }
}
