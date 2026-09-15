<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed a representative product catalog. Real product names/photos are
     * still pending client confirmation (see docs/) — this is sample data
     * for development, not the final catalog.
     */
    public function run(): void
    {
        Product::factory()
            ->count(12)
            ->create()
            ->each(function (Product $product) {
                ProductImage::factory()->count(2)->create(['product_id' => $product->id]);
            });
    }
}
