<?php

namespace Tests\Feature\Admin;

use App\Enums\ProductCategory;
use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_gudang_cannot_access_product_management(): void
    {
        $user = User::factory()->role(UserRole::AdminGudang)->create();

        $this->actingAs($user)
            ->get(route('admin.products.index'))
            ->assertForbidden();
    }

    public function test_owner_can_view_product_index(): void
    {
        $user = User::factory()->role(UserRole::Owner)->create();

        $this->actingAs($user)
            ->get(route('admin.products.index'))
            ->assertOk();
    }

    public function test_admin_marketplace_can_create_a_product(): void
    {
        Storage::fake('public');
        $user = User::factory()->role(UserRole::AdminMarketplace)->create();

        Livewire::actingAs($user)
            ->test('pages::admin.products.form')
            ->set('name', 'Yummy Bites Puree Pisang')
            ->set('sku', 'YB-PISANG-001')
            ->set('category', ProductCategory::YummyBites->value)
            ->set('newImages', [UploadedFile::fake()->image('produk.jpg')])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('products', ['sku' => 'YB-PISANG-001']);

        $product = Product::where('sku', 'YB-PISANG-001')->first();
        $this->assertCount(1, $product->images);
        Storage::disk('public')->assertExists($product->images->first()->image_path);
    }

    public function test_country_of_origin_required_for_imported_rice(): void
    {
        $user = User::factory()->role(UserRole::Owner)->create();

        Livewire::actingAs($user)
            ->test('pages::admin.products.form')
            ->set('name', 'Beras Tanpa Negara')
            ->set('sku', 'BR-001')
            ->set('category', ProductCategory::BerasImpor->value)
            ->call('save')
            ->assertHasErrors(['country_of_origin']);
    }

    public function test_sku_must_be_unique(): void
    {
        Product::factory()->create(['sku' => 'DUPLICATE-SKU']);
        $user = User::factory()->role(UserRole::Owner)->create();

        Livewire::actingAs($user)
            ->test('pages::admin.products.form')
            ->set('name', 'Produk Lain')
            ->set('sku', 'DUPLICATE-SKU')
            ->set('category', ProductCategory::YummyBites->value)
            ->call('save')
            ->assertHasErrors(['sku']);
    }

    public function test_owner_can_edit_existing_product(): void
    {
        $product = Product::factory()->create(['name' => 'Nama Lama']);
        $user = User::factory()->role(UserRole::Owner)->create();

        Livewire::actingAs($user)
            ->test('pages::admin.products.form', ['product' => $product])
            ->assertSet('name', 'Nama Lama')
            ->set('name', 'Nama Baru')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Nama Baru', $product->fresh()->name);
    }

    public function test_owner_can_delete_a_product(): void
    {
        Storage::fake('public');
        $product = Product::factory()->create();
        $product->images()->create([
            'image_path' => 'products/dummy.jpg',
            'sort_order' => 0,
        ]);
        Storage::disk('public')->put('products/dummy.jpg', 'fake-content');

        $user = User::factory()->role(UserRole::Owner)->create();

        Livewire::actingAs($user)
            ->test('pages::admin.products.index')
            ->call('delete', $product->id);

        $this->assertModelMissing($product);
        Storage::disk('public')->assertMissing('products/dummy.jpg');
    }

    public function test_owner_can_toggle_product_active_status(): void
    {
        $product = Product::factory()->create(['is_active' => true]);
        $user = User::factory()->role(UserRole::Owner)->create();

        Livewire::actingAs($user)
            ->test('pages::admin.products.index')
            ->call('toggleActive', $product->id);

        $this->assertFalse($product->fresh()->is_active);
    }
}
