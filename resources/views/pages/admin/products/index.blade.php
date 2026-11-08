@extends('layouts.app')

@section('content')
<div class="flex w-full flex-col gap-4">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <flux:heading size="xl" class="font-display">{{ __('Kelola Produk') }}</flux:heading>
        <a href="{{ route('admin.products.create') }}" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Tambah Produk') }}</a>
    </div>

    @if (session('status'))
        <div class="rounded-lg bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.products.index') }}" class="flex max-w-md gap-2">
        <input name="search" value="{{ $search }}" placeholder="{{ __('Cari nama atau SKU...') }}" class="w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
        <button type="submit" class="rounded-lg border px-4 py-2 text-sm">{{ __('Cari') }}</button>
    </form>

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
                @forelse ($products as $product)
                    <tr>
                        <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">{{ $product->name }}</td>
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $product->sku }}</td>
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">
                            {{ $product->category->label() }}
                            @if ($product->country_of_origin)
                                <span class="text-zinc-400">· {{ $product->country_of_origin }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $product->images_count }}</td>
                        <td class="px-4 py-3">{{ $product->is_active ? __('Aktif') : __('Nonaktif') }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.products.edit', $product) }}" class="rounded border px-3 py-1">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('admin.products.toggle-active', $product) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded border px-3 py-1">{{ $product->is_active ? __('Nonaktifkan') : __('Aktifkan') }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('{{ __('Yakin ingin menghapus produk :name?', ['name' => $product->name]) }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded border border-red-300 px-3 py-1 text-red-600">{{ __('Hapus') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-zinc-500">{{ __('Belum ada produk.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $products->links() }}
</div>
@endsection
