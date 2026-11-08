<?php

namespace Tests\Feature\Admin;

use App\Enums\ProductCategory;
use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

        $this->actingAs($user)
            ->post(route('admin.products.store'), [
                'name' => 'Yummy Bites Puree Pisang',
                'sku' => 'YB-PISANG-001',
                'category' => ProductCategory::YummyBites->value,
                'is_active' => '1',
                'newImages' => [UploadedFile::fake()->image('produk.jpg')],
            ])
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', ['sku' => 'YB-PISANG-001']);

        $product = Product::where('sku', 'YB-PISANG-001')->first();
        $this->assertCount(1, $product->images);
        Storage::disk('public')->assertExists($product->images->first()->image_path);
    }

    public function test_country_of_origin_required_for_imported_rice(): void
    {
        $user = User::factory()->role(UserRole::Owner)->create();

        $this->actingAs($user)
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), [
                'name' => 'Beras Tanpa Negara',
                'sku' => 'BR-001',
                'category' => ProductCategory::BerasImpor->value,
            ])
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors(['country_of_origin']);
    }

    public function test_sku_must_be_unique(): void
    {
        Product::factory()->create(['sku' => 'DUPLICATE-SKU']);
        $user = User::factory()->role(UserRole::Owner)->create();

        $this->actingAs($user)
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), [
                'name' => 'Produk Lain',
                'sku' => 'DUPLICATE-SKU',
                'category' => ProductCategory::YummyBites->value,
            ])
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors(['sku']);
    }

    public function test_owner_can_edit_existing_product(): void
    {
        $product = Product::factory()->create(['name' => 'Nama Lama']);
        $user = User::factory()->role(UserRole::Owner)->create();

        $this->actingAs($user)
            ->put(route('admin.products.update', $product), [
                'name' => 'Nama Baru',
                'sku' => $product->sku,
                'category' => $product->category->value,
                'country_of_origin' => $product->country_of_origin,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHasNoErrors();

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

        $this->actingAs($user)->delete(route('admin.products.destroy', $product))->assertRedirect();

        $this->assertModelMissing($product);
        Storage::disk('public')->assertMissing('products/dummy.jpg');
    }

    public function test_owner_can_toggle_product_active_status(): void
    {
        $product = Product::factory()->create(['is_active' => true]);
        $user = User::factory()->role(UserRole::Owner)->create();

        $this->actingAs($user)
            ->patch(route('admin.products.toggle-active', $product))
            ->assertRedirect();

        $this->assertFalse($product->fresh()->is_active);
    }

    public function test_upload_txt_file_is_rejected_by_server_validation(): void
    {
        Storage::fake('public');
        $user = User::factory()->role(UserRole::AdminMarketplace)->create();
        $fakeTxt = UploadedFile::fake()->create('dokumen_rahasia.txt', 50, 'text/plain');

        $this->actingAs($user)
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), [
                'name' => 'Produk Tes TXT',
                'sku' => 'TEST-TXT-001',
                'category' => ProductCategory::YummyBites->value,
                'newImages' => [$fakeTxt],
            ])
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors(['newImages.0']);
    }

    public function test_upload_file_exceeding_2mb_is_rejected_by_server_validation(): void
    {
        Storage::fake('public');
        $user = User::factory()->role(UserRole::AdminMarketplace)->create();
        // 3000 KB > 2048 KB (2 MB)
        $largeImage = UploadedFile::fake()->create('foto_raksasa.jpg', 3000, 'image/jpeg');

        $this->actingAs($user)
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), [
                'name' => 'Produk Tes Ukuran Besar',
                'sku' => 'TEST-SIZE-001',
                'category' => ProductCategory::YummyBites->value,
                'newImages' => [$largeImage],
            ])
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors(['newImages.0']);
    }

    public function test_upload_accepts_jpeg_png_and_gif_formats(): void
    {
        Storage::fake('public');
        $user = User::factory()->role(UserRole::AdminMarketplace)->create();

        $jpgImage = UploadedFile::fake()->image('foto1.jpeg');
        $pngImage = UploadedFile::fake()->image('foto2.png');
        $gifImage = UploadedFile::fake()->image('foto3.gif');

        $this->actingAs($user)
            ->post(route('admin.products.store'), [
                'name' => 'Produk Multi Format',
                'sku' => 'TEST-MULTI-FORMAT',
                'category' => ProductCategory::YummyBites->value,
                'is_active' => '1',
                'newImages' => [$jpgImage, $pngImage, $gifImage],
            ])
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHasNoErrors();

        $product = Product::where('sku', 'TEST-MULTI-FORMAT')->first();
        $this->assertNotNull($product);
        $this->assertCount(3, $product->images);
    }
}
