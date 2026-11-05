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

    public function test_upload_txt_file_is_rejected_by_server_validation(): void
    {
        Storage::fake('public');
        $user = User::factory()->role(UserRole::AdminMarketplace)->create();
        $fakeTxt = UploadedFile::fake()->create('dokumen_rahasia.txt', 50, 'text/plain');

        Livewire::actingAs($user)
            ->test('pages::admin.products.form')
            ->set('name', 'Produk Tes TXT')
            ->set('sku', 'TEST-TXT-001')
            ->set('category', ProductCategory::YummyBites->value)
            ->set('newImages', [$fakeTxt])
            ->assertHasErrors(['newImages.0'])
            ->call('save')
            ->assertHasErrors(['newImages.0']);
    }

    public function test_upload_file_exceeding_2mb_is_rejected_by_server_validation(): void
    {
        Storage::fake('public');
        $user = User::factory()->role(UserRole::AdminMarketplace)->create();
        // 3000 KB > 2048 KB (2 MB)
        $largeImage = UploadedFile::fake()->create('foto_raksasa.jpg', 3000, 'image/jpeg');

        Livewire::actingAs($user)
            ->test('pages::admin.products.form')
            ->set('name', 'Produk Tes Ukuran Besar')
            ->set('sku', 'TEST-SIZE-001')
            ->set('category', ProductCategory::YummyBites->value)
            ->set('newImages', [$largeImage])
            ->assertHasErrors(['newImages.0'])
            ->call('save')
            ->assertHasErrors(['newImages.0']);
    }

    public function test_upload_accepts_jpeg_png_and_gif_formats(): void
    {
        Storage::fake('public');
        $user = User::factory()->role(UserRole::AdminMarketplace)->create();

        $jpgImage = UploadedFile::fake()->image('foto1.jpeg');
        $pngImage = UploadedFile::fake()->image('foto2.png');
        $gifImage = UploadedFile::fake()->image('foto3.gif');

        Livewire::actingAs($user)
            ->test('pages::admin.products.form')
            ->set('name', 'Produk Multi Format')
            ->set('sku', 'TEST-MULTI-FORMAT')
            ->set('category', ProductCategory::YummyBites->value)
            ->set('newImages', [$jpgImage, $pngImage, $gifImage])
            ->call('save')
            ->assertHasNoErrors();

        $product = Product::where('sku', 'TEST-MULTI-FORMAT')->first();
        $this->assertNotNull($product);
        $this->assertCount(3, $product->images);
    }
}
