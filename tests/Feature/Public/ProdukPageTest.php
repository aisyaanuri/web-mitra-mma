<?php

namespace Tests\Feature\Public;

use App\Enums\ProductCategory;
use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdukPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_produk_page_lists_active_products(): void
    {
        Product::factory()->create(['name' => 'Yummy Bites Puree Apel', 'is_active' => true]);
        Product::factory()->create(['name' => 'Produk Nonaktif', 'is_active' => false]);

        $this->get(route('produk'))
            ->assertOk()
            ->assertSee('Yummy Bites Puree Apel')
            ->assertDontSee('Produk Nonaktif');
    }

    public function test_can_filter_by_category(): void
    {
        Product::factory()->create(['name' => 'Snack Bayi A', 'category' => ProductCategory::YummyBites, 'is_active' => true]);
        Product::factory()->create(['name' => 'Beras Jepang A', 'category' => ProductCategory::BerasImpor, 'country_of_origin' => 'Jepang', 'is_active' => true]);

        $this->get(route('produk', ['category' => ProductCategory::YummyBites->value]))
            ->assertOk()
            ->assertSee('Snack Bayi A')
            ->assertDontSee('Beras Jepang A');
    }

    public function test_can_filter_by_country_of_origin(): void
    {
        Product::factory()->create(['name' => 'Beras Jepang', 'category' => ProductCategory::BerasImpor, 'country_of_origin' => 'Jepang', 'is_active' => true]);
        Product::factory()->create(['name' => 'Beras Vietnam', 'category' => ProductCategory::BerasImpor, 'country_of_origin' => 'Vietnam', 'is_active' => true]);

        $this->get(route('produk', [
            'category' => ProductCategory::BerasImpor->value,
            'country' => 'Jepang',
        ]))
            ->assertOk()
            ->assertSee('Beras Jepang')
            ->assertDontSee('Beras Vietnam');
    }

    public function test_product_seeder_creates_all_rice_products_with_images(): void
    {
        $this->seed(ProductSeeder::class);

        $expectedRiceNames = [
            'Koshikari 5 Kg',
            'Koshikari 2 Kg',
            'Hanaro 5 Kg',
            'Lai Qila Diabetes 5 Kg',
            'Punjab 5 Kg',
            'Jasmine Bangkok 5 Kg',
            'Leon Thai Jasmine 5 Kg',
            'Leon Thai Jasmin 2 Kg',
            'Hitam Organik 2 Kg',
            'Merah Organik 2 Kg',
            'Kombinasi Organik 2 Kg',
            'Fragrant Organic 2 Kg',
            'Sentra Ramos 2 Kg',
            'Beras Merah 2 Kg',
        ];

        foreach ($expectedRiceNames as $name) {
            $product = Product::where('name', $name)->first();
            $this->assertNotNull($product, "Product {$name} should exist.");
            $this->assertTrue($product->is_active);
            $this->assertNotEmpty($product->images, "Product {$name} should have an image.");
            $this->assertFileExists(storage_path('app/public/'.$product->images->first()->image_path));
        }
    }

    public function test_can_search_products(): void
    {
        Product::factory()->create(['name' => 'Yummy Bites BebeRoll Cheese', 'is_active' => true]);
        Product::factory()->create(['name' => 'Beras Koshihikari 5kg', 'is_active' => true]);

        $this->get(route('produk', ['search' => 'BebeRoll']))
            ->assertOk()
            ->assertSee('Yummy Bites BebeRoll Cheese')
            ->assertDontSee('Beras Koshihikari 5kg');
    }

    public function test_product_card_links_to_product_detail_page(): void
    {
        $product = Product::factory()->create([
            'name' => 'Yummy Bites BebeRoll Cheese',
            'description' => "Camilan roll beras panggang isi krim keju.\n\nKomposisi: 8 serealia Korea, keju.",
            'is_active' => true,
        ]);

        $this->get(route('produk'))
            ->assertOk()
            ->assertSee(route('produk.detail', $product), false);
    }

    public function test_product_detail_page_renders_successfully(): void
    {
        $product = Product::factory()->create([
            'name' => 'Koshikari 5 Kg',
            'slug' => 'koshikari-5-kg',
            'category' => ProductCategory::BerasImpor,
            'country_of_origin' => 'Jepang',
            'description' => "Beras Jepang Koshihikari super premium kemasan 5 kg.\n\nRekomendasi Sajian: Sushi roll, Onigiri.\nTekstur: Bulat utuh, sangat pulen.",
            'is_active' => true,
        ]);

        $this->get(route('produk.detail', $product))
            ->assertOk()
            ->assertSee('Koshikari 5 Kg')
            ->assertSee('Jepang')
            ->assertSee('Rekomendasi Sajian')
            ->assertSee('Sushi roll, Onigiri')
            ->assertSee('Tekstur')
            ->assertSee('Pesan via WhatsApp');
    }

    public function test_inactive_product_detail_page_returns_404(): void
    {
        $product = Product::factory()->create([
            'name' => 'Produk Nonaktif',
            'slug' => 'produk-nonaktif',
            'is_active' => false,
        ]);

        $this->get(route('produk.detail', $product))
            ->assertNotFound();
    }
}
