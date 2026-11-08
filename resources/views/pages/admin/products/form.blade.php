@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl" class="font-display">{{ $product ? __('Edit Produk') : __('Tambah Produk') }}</flux:heading>
        <a href="{{ route('admin.products.index') }}" class="text-sm text-zinc-600 hover:text-zinc-900">{{ __('Kembali') }}</a>
    </div>

    @if (session('status'))
        <div class="rounded-lg bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    @if ($product?->images->isNotEmpty())
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <h2 class="font-semibold">{{ __('Foto Produk Saat Ini') }}</h2>
            <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ($product->images as $image)
                    <div class="space-y-2">
                        <img src="{{ $image->url() }}" alt="{{ $image->alt_text ?? $product->name }}" class="aspect-square w-full rounded-lg object-cover">
                        <form method="POST" action="{{ route('admin.products.images.destroy', [$product, $image]) }}" onsubmit="return confirm('{{ __('Hapus foto ini?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 hover:underline">{{ __('Hapus foto') }}</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data" action="{{ $product ? route('admin.products.update', $product) : route('admin.products.store') }}" class="space-y-5">
        @csrf
        @if ($product)
            @method('PUT')
        @endif

        <div>
            <label for="name" class="block text-sm font-medium">{{ __('Nama Produk') }}</label>
            <input id="name" name="name" value="{{ old('name', $product?->name) }}" required class="mt-1 w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="slug" class="block text-sm font-medium">{{ __('Slug') }}</label>
            <input id="slug" name="slug" value="{{ old('slug', $product?->slug) }}" class="mt-1 w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
            @error('slug') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="sku" class="block text-sm font-medium">{{ __('SKU') }}</label>
            <input id="sku" name="sku" value="{{ old('sku', $product?->sku) }}" required class="mt-1 w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
            @error('sku') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="category" class="block text-sm font-medium">{{ __('Kategori') }}</label>
            <select id="category" name="category" required class="mt-1 w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
                <option value="">{{ __('Pilih kategori') }}</option>
                @foreach (\App\Enums\ProductCategory::cases() as $case)
                    <option value="{{ $case->value }}" @selected(old('category', $product?->category?->value) === $case->value)>{{ $case->label() }}</option>
                @endforeach
            </select>
            @error('category') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="country_of_origin" class="block text-sm font-medium">{{ __('Negara Asal (khusus beras impor)') }}</label>
            <input id="country_of_origin" name="country_of_origin" value="{{ old('country_of_origin', $product?->country_of_origin) }}" class="mt-1 w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
            @error('country_of_origin') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="description" class="block text-sm font-medium">{{ __('Deskripsi') }}</label>
            <textarea id="description" name="description" rows="4" class="mt-1 w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">{{ old('description', $product?->description) }}</textarea>
            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="weight" class="block text-sm font-medium">{{ __('Berat/Kemasan') }}</label>
            <input id="weight" name="weight" value="{{ old('weight', $product?->weight) }}" placeholder="mis. 250g" class="mt-1 w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
            @error('weight') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="flex gap-6">
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product?->is_active ?? true))> {{ __('Aktif ditampilkan') }}</label>
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product?->is_featured ?? false))> {{ __('Tampilkan sebagai unggulan') }}</label>
        </div>
        <div>
            <label for="meta_title" class="block text-sm font-medium">{{ __('Meta Title') }}</label>
            <input id="meta_title" name="meta_title" value="{{ old('meta_title', $product?->meta_title) }}" class="mt-1 w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
            @error('meta_title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="meta_description" class="block text-sm font-medium">{{ __('Meta Description') }}</label>
            <textarea id="meta_description" name="meta_description" rows="2" class="mt-1 w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">{{ old('meta_description', $product?->meta_description) }}</textarea>
            @error('meta_description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="newImages" class="block text-sm font-medium">{{ __('Foto Produk') }}</label>
            <input id="newImages" type="file" name="newImages[]" multiple accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp" class="mt-2 block w-full text-sm">
            <p class="mt-1 text-xs text-zinc-500">{{ __('Format: JPG, JPEG, PNG, GIF, WEBP. Maks. 2 MB per file (maks. 5 foto sekaligus).') }}</p>
            @error('newImages') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            @error('newImages.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.products.index') }}" class="rounded-lg border px-4 py-2 text-sm">{{ __('Batal') }}</a>
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Simpan') }}</button>
        </div>
    </form>
</div>
@endsection
