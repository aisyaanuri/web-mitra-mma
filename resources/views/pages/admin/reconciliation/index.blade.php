@extends('layouts.app')

@section('content')
<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <flux:heading size="xl" class="font-display">{{ __('Rekonsiliasi') }}</flux:heading>
        <form method="POST" action="{{ route('admin.reconciliation.run') }}">
            @csrf
            <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Jalankan Rekonsiliasi') }}</button>
        </form>
    </div>
    @if (session('status'))
        <div class="rounded-lg bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif
    <div class="grid gap-3 sm:grid-cols-5">
        @foreach (['total' => __('Total'), 'sesuai' => __('Sesuai'), 'selisih' => __('Selisih'), 'belum_dicek' => __('Belum Dicek'), 'accuracy' => __('Akurasi %')] as $key => $label)
            <div class="rounded-xl border p-4"><p class="text-xs text-zinc-500">{{ $label }}</p><p class="mt-1 text-2xl font-bold">{{ $summary[$key] }}{{ $key === 'accuracy' ? '%' : '' }}</p></div>
        @endforeach
    </div>
    <form method="GET" action="{{ route('admin.reconciliation.index') }}" class="flex flex-wrap gap-2">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Cari no. pesanan/pembeli') }}" class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
        <input type="month" name="period" value="{{ $filters['period'] ?? '' }}" class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
        <select name="match_status" class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
            <option value="">{{ __('Semua hasil') }}</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(($filters['match_status'] ?? '') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <select name="marketplace_status" class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
            <option value="">{{ __('Semua status Shopee') }}</option>
            @foreach ($marketplaceStatuses as $status)
                <option value="{{ $status }}" @selected(($filters['marketplace_status'] ?? '') === $status)>{{ $status }}</option>
            @endforeach
        </select>
        <button class="rounded-lg border px-4 py-2 text-sm">{{ __('Filter') }}</button>
        <a href="{{ route('admin.reconciliation.index') }}" class="rounded-lg border px-4 py-2 text-sm">{{ __('Reset') }}</a>
    </form>
    <div class="overflow-x-auto rounded-lg border">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-800"><tr><th class="p-3">{{ __('Periode') }}</th><th class="p-3">{{ __('Pesanan') }}</th><th class="p-3">{{ __('Status Shopee') }}</th><th class="p-3">{{ __('Status Gudang') }}</th><th class="p-3">{{ __('Hasil') }}</th><th class="p-3">{{ __('Catatan / Aksi') }}</th></tr></thead>
            <tbody class="divide-y">
                @forelse ($records as $record)
                    <tr>
                        <td class="p-3">{{ $record->period?->format('Y-m') }}</td>
                        <td class="p-3 font-mono">
                            {{ $record->order?->shopee_order_sn ?? '-' }}<br>
                            <span class="text-xs text-zinc-500">{{ $record->order?->buyer_username }}</span>
                            @if ($record->order)
                                <details class="mt-1 font-sans">
                                    <summary class="cursor-pointer text-xs text-blue-600">{{ __('Detail pesanan') }}</summary>
                                    <ul class="mt-1 space-y-1 text-xs">
                                        @foreach ($record->order->items as $item)
                                            <li>{{ $item->item_name }} <span class="text-zinc-500">({{ $item->sku }})</span> × {{ $item->quantity }}</li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                        </td>
                        <td class="p-3">{{ $record->marketplace_status_snapshot }}</td>
                        <td class="p-3">{{ $record->warehouse_status_snapshot?->label() ?? __('Belum Diproses') }}</td>
                        <td class="p-3">{{ $record->match_status->label() }}</td>
                        <td class="p-3">
                            <form method="POST" action="{{ route('admin.reconciliation.note', $record) }}" class="mb-2 flex min-w-72 gap-2">
                                @csrf
                                @method('PATCH')
                                <input name="investigation_note" value="{{ old('investigation_note', $record->investigation_note) }}" placeholder="{{ __('Catatan investigasi') }}" class="min-w-0 flex-1 rounded border p-1">
                                <button class="rounded border px-2 text-xs">{{ __('Simpan') }}</button>
                            </form>
                            @if ($record->order)
                                <form method="POST" action="{{ route('admin.reconciliation.warehouse-status', $record) }}" class="flex gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="warehouse_status" required class="rounded border p-1 text-xs">
                                        @foreach (\App\Enums\WarehouseStatus::cases() as $status)
                                            <option value="{{ $status->value }}" @selected($record->warehouse_status_snapshot === $status)>{{ $status->label() }}</option>
                                        @endforeach
                                    </select>
                                    <button class="rounded border px-2 text-xs">{{ __('Selaraskan') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-6 text-center text-zinc-500">{{ __('Belum ada data rekonsiliasi.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $records->links() }}
</div>
@endsection
