<?php

use App\Models\Product;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Kelola Produk')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function toggleActive(Product $product): void
    {
        $product->update(['is_active' => ! $product->is_active]);
    }

    public function delete(Product $product): void
    {
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        $product->delete();

        Flux::toast(variant: 'success', text: __('Produk ":name" dihapus.', ['name' => $product->name]));
    }

    #[Computed]
    public function products()
    {
        return Product::query()
            ->when($this->search, fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('sku', 'like', "%{$this->search}%"))
            ->withCount('images')
            ->orderBy('name')
            ->paginate(10);
    }
}; ?>

<div class="flex w-full flex-col gap-4">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <flux:heading size="xl">{{ __('Kelola Produk') }}</flux:heading>
        <flux:button variant="primary" :href="route('admin.products.create')" wire:navigate>
            {{ __('Tambah Produk') }}
        </flux:button>
    </div>

    <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Cari nama atau SKU...') }}" class="max-w-sm" />

    <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 text-xs uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                <tr>
                    <th class="px-4 py-3">{{ __('Nama') }}</th>
                    <th class="px-4 py-3">{{ __('SKU') }}</th>
                    <th class="px-4 py-3">{{ __('Kategori') }}</th>
                    <th class="px-4 py-3">{{ __('Foto') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Aksi') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($this->products as $product)
                    <tr wire:key="product-{{ $product->id }}">
                        <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">{{ $product->name }}</td>
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $product->sku }}</td>
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">
                            {{ $product->category->label() }}
                            @if ($product->country_of_origin)
                                <span class="text-zinc-400">· {{ $product->country_of_origin }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $product->images_count }}</td>
                        <td class="px-4 py-3">
                            <button
                                wire:click="toggleActive({{ $product->id }})"
                                class="rounded-full px-2 py-0.5 text-xs font-medium {{ $product->is_active ? 'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-400' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}"
                            >
                                {{ $product->is_active ? __('Aktif') : __('Nonaktif') }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <flux:button size="sm" :href="route('admin.products.edit', $product)" wire:navigate>
                                    {{ __('Edit') }}
                                </flux:button>
                                <flux:button
                                    size="sm"
                                    variant="danger"
                                    wire:click="delete({{ $product->id }})"
                                    wire:confirm="{{ __('Yakin ingin menghapus produk :name?', ['name' => $product->name]) }}"
                                >
                                    {{ __('Hapus') }}
                                </flux:button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-zinc-500 dark:text-zinc-400">
                            {{ __('Belum ada produk.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $this->products->links() }}
</div>
