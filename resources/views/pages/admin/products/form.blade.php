<?php

use App\Enums\ProductCategory;
use App\Models\Product;
use App\Models\ProductImage;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Form Produk')] class extends Component
{
    use WithFileUploads;

    public ?Product $product = null;

    public string $name = '';

    public string $slug = '';

    public string $sku = '';

    public string $category = '';

    public ?string $country_of_origin = null;

    public ?string $description = null;

    public ?string $weight = null;

    public bool $is_active = true;

    public bool $is_featured = false;

    public ?string $meta_title = null;

    public ?string $meta_description = null;

    /** @var array<int, mixed> */
    public array $newImages = [];

    public function mount(?Product $product = null): void
    {
        if ($product?->exists) {
            $this->product = $product;
            $this->name = $product->name;
            $this->slug = $product->slug;
            $this->sku = $product->sku;
            $this->category = $product->category->value;
            $this->country_of_origin = $product->country_of_origin;
            $this->description = $product->description;
            $this->weight = $product->weight;
            $this->is_active = $product->is_active;
            $this->is_featured = $product->is_featured;
            $this->meta_title = $product->meta_title;
            $this->meta_description = $product->meta_description;
        }
    }

    public function updatedName(): void
    {
        if (! $this->product) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function save(): void
    {
        if ($this->category !== ProductCategory::BerasImpor->value) {
            $this->country_of_origin = null;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($this->product?->id)],
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($this->product?->id)],
            'category' => ['required', Rule::enum(ProductCategory::class)],
            'country_of_origin' => ['nullable', 'required_if:category,'.ProductCategory::BerasImpor->value, 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'weight' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'newImages.*' => ['nullable', 'image', 'max:6144'],
        ]);

        $product = $this->product ?? new Product;
        $product->fill(collect($validated)->except('newImages')->toArray());
        $product->save();

        foreach ($this->newImages as $index => $file) {
            $path = $file->store('products', 'public');

            $product->images()->create([
                'image_path' => $path,
                'alt_text' => $product->name,
                'sort_order' => $product->images()->count() + $index,
            ]);
        }

        $this->newImages = [];

        Flux::toast(variant: 'success', text: __('Produk disimpan.'));

        $this->redirectRoute('admin.products.index', navigate: true);
    }

    public function deleteImage(int $imageId): void
    {
        $image = ProductImage::findOrFail($imageId);

        abort_unless($this->product && $image->product_id === $this->product->id, 403);

        Storage::disk('public')->delete($image->image_path);
        $image->delete();
    }
}; ?>

<div class="max-w-2xl">
    <flux:heading size="xl" class="font-display">{{ $product ? __('Edit Produk') : __('Tambah Produk') }}</flux:heading>

    <form wire:submit="save" class="mt-6 space-y-6">
        <flux:input wire:model.live="name" label="{{ __('Nama Produk') }}" required />
        <flux:input wire:model="slug" label="{{ __('Slug') }}" required />
        <flux:input wire:model="sku" label="{{ __('SKU') }}" required />

        <flux:select wire:model.live="category" label="{{ __('Kategori') }}" required>
            <flux:select.option value="">{{ __('Pilih kategori') }}</flux:select.option>
            @foreach (ProductCategory::cases() as $case)
                <flux:select.option value="{{ $case->value }}">{{ $case->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        @if ($category === ProductCategory::BerasImpor->value)
            <flux:input wire:model="country_of_origin" label="{{ __('Negara Asal') }}" required />
        @endif

        <flux:textarea wire:model="description" label="{{ __('Deskripsi') }}" rows="4" />
        <flux:input wire:model="weight" label="{{ __('Berat/Kemasan') }}" placeholder="mis. 250g" />

        <div class="flex gap-6">
            <flux:checkbox wire:model="is_active" label="{{ __('Aktif ditampilkan') }}" />
            <flux:checkbox wire:model="is_featured" label="{{ __('Tampilkan sebagai unggulan') }}" />
        </div>

        <div>
            <flux:heading size="sm">{{ __('Foto Produk') }}</flux:heading>

            @if ($product?->images->isNotEmpty())
                <div class="mt-3 grid grid-cols-4 gap-3">
                    @foreach ($product->images as $image)
                        <div class="relative" wire:key="existing-image-{{ $image->id }}">
                            <img src="{{ $image->url() }}" class="aspect-square w-full rounded-lg object-cover">
                            <button
                                type="button"
                                wire:click="deleteImage({{ $image->id }})"
                                wire:confirm="{{ __('Hapus foto ini?') }}"
                                class="absolute -top-2 -right-2 rounded-full bg-red-600 px-2 py-0.5 text-xs text-white"
                            >
                                &times;
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif

            <input type="file" wire:model="newImages" multiple accept="image/*" class="mt-3 block text-sm" />
            @error('newImages.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

            <div wire:loading wire:target="newImages" class="mt-2 text-sm text-zinc-500">{{ __('Mengunggah...') }}</div>

            @if (! empty($newImages))
                <div class="mt-3 grid grid-cols-4 gap-3">
                    @foreach ($newImages as $file)
                        <img src="{{ $file->temporaryUrl() }}" class="aspect-square w-full rounded-lg object-cover">
                    @endforeach
                </div>
            @endif
        </div>

        <div class="flex justify-end gap-2">
            <flux:button :href="route('admin.products.index')" wire:navigate>{{ __('Batal') }}</flux:button>
            <flux:button type="submit" variant="primary">{{ __('Simpan') }}</flux:button>
        </div>
    </form>
</div>
