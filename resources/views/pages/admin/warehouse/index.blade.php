@extends('layouts.app')

@section('content')
<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <flux:heading size="xl" class="font-display">{{ __('Proses Gudang') }}</flux:heading>
        <a href="{{ route('admin.warehouse.export', request()->query()) }}" class="rounded-lg border px-4 py-2 text-sm">{{ __('Ekspor Excel') }}</a>
    </div>
    @if (session('status'))
        <div class="rounded-lg bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.warehouse.index') }}" class="grid gap-3 rounded-xl border p-4 sm:grid-cols-2 lg:grid-cols-5">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Cari pesanan atau resi') }}" class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
        <select name="status" class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
            <option value="">{{ __('Semua status gudang') }}</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        <input type="month" name="month" value="{{ $filters['month'] ?? '' }}" class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex gap-2 sm:col-span-2 lg:col-span-5">
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Filter') }}</button>
            <a href="{{ route('admin.warehouse.index') }}" class="rounded-lg border px-4 py-2 text-sm">{{ __('Reset') }}</a>
        </div>
    </form>

    @if ($canUpdate)
        <form method="POST" action="{{ route('admin.warehouse.bulk-update') }}">
            @csrf
            @method('PATCH')
            <div class="mb-3 flex flex-wrap items-center gap-2">
                <label for="target_status" class="text-sm">{{ __('Ubah status pesanan terpilih:') }}</label>
                <select id="target_status" name="target_status" required class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
                    <option value="">{{ __('Pilih status') }}</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-lg border px-3 py-2 text-sm">{{ __('Terapkan') }}</button>
            </div>
    @endif

    <div class="overflow-x-auto rounded-lg border">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-800">
                <tr>
                    @if ($canUpdate)<th class="p-3">{{ __('Pilih') }}</th>@endif
                    <th class="p-3">{{ __('Pesanan') }}</th>
                    <th class="p-3">{{ __('Pembeli') }}</th>
                    <th class="p-3">{{ __('Tanggal') }}</th>
                    <th class="p-3">{{ __('Barang') }}</th>
                    <th class="p-3">{{ __('Status Gudang') }}</th>
                    @if ($canUpdate)<th class="p-3">{{ __('Aksi') }}</th>@endif
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($orders as $order)
                    <tr>
                        @if ($canUpdate)
                            <td class="p-3"><input type="checkbox" name="selected_order_ids[]" value="{{ $order->id }}"></td>
                        @endif
                        <td class="p-3 font-mono">{{ $order->shopee_order_sn }}</td>
                        <td class="p-3">{{ $order->buyer_username }}</td>
                        <td class="p-3">{{ $order->order_placed_at?->format('d/m/Y H:i') }}</td>
                        <td class="p-3">
                            {{ $order->items->sum('quantity') }}
                            <details class="mt-1">
                                <summary class="cursor-pointer text-xs text-blue-600">{{ __('Lihat barang') }}</summary>
                                <ul class="mt-1 space-y-1">
                                    @foreach ($order->items as $item)
                                        <li>{{ $item->item_name }} <span class="text-zinc-500">({{ $item->sku }})</span> × {{ $item->quantity }}</li>
                                    @endforeach
                                </ul>
                            </details>
                        </td>
                        <td class="p-3">{{ $order->warehouse_status?->label() ?? __('Belum Diproses') }}</td>
                        @if ($canUpdate)
                            <td class="p-3">
                                @if (in_array($order->marketplace_status, ['CANCELLED', 'IN_CANCEL'], true))
                                    <span class="text-zinc-500">{{ __('Dibatalkan') }}</span>
                                @elseif ($order->warehouse_status === null)
                                    <button type="submit" formmethod="POST" formaction="{{ route('admin.warehouse.receive', $order) }}" class="rounded border px-3 py-1" onclick="this.form.querySelector('[name=target_status]').required=false">{{ __('Terima') }}</button>
                                @elseif ($order->warehouse_status->next())
                                    <button type="submit" formmethod="POST" formaction="{{ route('admin.warehouse.advance', $order) }}" class="rounded border px-3 py-1" onclick="this.form.querySelector('[name=target_status]').required=false">{{ __('Lanjut') }}</button>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ $canUpdate ? 7 : 6 }}" class="p-6 text-center text-zinc-500">{{ __('Tidak ada pesanan.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($canUpdate)
        </form>
    @endif
    {{ $orders->links() }}
</div>
@endsection
