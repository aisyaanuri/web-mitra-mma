<?php

namespace Tests\Feature\Public;

use App\Enums\ProductCategory;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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

        Livewire::test('pages::public.produk')
            ->set('category', ProductCategory::YummyBites->value)
            ->assertSee('Snack Bayi A')
            ->assertDontSee('Beras Jepang A');
    }

    public function test_can_filter_by_country_of_origin(): void
    {
        Product::factory()->create(['name' => 'Beras Jepang', 'category' => ProductCategory::BerasImpor, 'country_of_origin' => 'Jepang', 'is_active' => true]);
        Product::factory()->create(['name' => 'Beras Vietnam', 'category' => ProductCategory::BerasImpor, 'country_of_origin' => 'Vietnam', 'is_active' => true]);

        Livewire::test('pages::public.produk')
            ->set('category', ProductCategory::BerasImpor->value)
            ->set('country', 'Jepang')
            ->assertSee('Beras Jepang')
            ->assertDontSee('Beras Vietnam');
    }
}
