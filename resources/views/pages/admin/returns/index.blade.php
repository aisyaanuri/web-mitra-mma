@extends('layouts.app')

@section('content')
<div class="space-y-5">
    <flux:heading size="xl" class="font-display">{{ __('Manajemen Retur & Komplain') }}</flux:heading>
    @if (session('status'))
        <div class="rounded-lg bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif
    <div class="grid gap-3 sm:grid-cols-4">
        <div class="rounded-xl border p-4"><p class="text-xs text-zinc-500">{{ __('Total Retur') }}</p><p class="text-2xl font-bold">{{ $metrics['total_returns'] }}</p></div>
        <div class="rounded-xl border p-4"><p class="text-xs text-zinc-500">{{ __('Menunggu Barang') }}</p><p class="text-2xl font-bold">{{ $metrics['waiting_physical'] }}</p></div>
        <div class="rounded-xl border p-4"><p class="text-xs text-zinc-500">{{ __('Barang Diterima') }}</p><p class="text-2xl font-bold">{{ $metrics['received_count'] }}</p></div>
        <div class="rounded-xl border p-4"><p class="text-xs text-zinc-500">{{ __('Total Refund') }}</p><p class="text-2xl font-bold">Rp {{ number_format($metrics['total_refund'], 0, ',', '.') }}</p></div>
    </div>
    <form method="GET" action="{{ route('admin.returns.index') }}" class="flex flex-wrap gap-2">
        <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Cari no. retur/pesanan/pembeli') }}" class="min-w-60 rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
        <input name="shopee_status" value="{{ $filters['shopee_status'] ?? '' }}" placeholder="{{ __('Status Shopee') }}" class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
        <select name="physical_status" class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
            <option value="">{{ __('Semua kondisi fisik') }}</option>
            @foreach ($physicalStatuses as $value => $label)
                <option value="{{ $value }}" @selected(($filters['physical_status'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Filter') }}</button>
        <a href="{{ route('admin.returns.index') }}" class="rounded-lg border px-4 py-2 text-sm">{{ __('Reset') }}</a>
    </form>
    <div class="overflow-x-auto rounded-lg border">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-800"><tr><th class="p-3">{{ __('No. Retur / Pesanan') }}</th><th class="p-3">{{ __('Pembeli') }}</th><th class="p-3">{{ __('Alasan') }}</th><th class="p-3">{{ __('Refund') }}</th><th class="p-3">{{ __('Status') }}</th><th class="p-3">{{ __('Verifikasi Fisik') }}</th></tr></thead>
            <tbody class="divide-y">
                @forelse ($returns as $return)
                    <tr>
                        <td class="p-3 font-mono">{{ $return->shopee_return_sn }}<br><span class="text-xs text-zinc-500">{{ $return->order?->shopee_order_sn ?? '-' }}</span></td>
                        <td class="p-3">{{ $return->order?->buyer_username ?? __('Customer Shopee') }}</td>
                        <td class="p-3">{{ $return->reason }}</td>
                        <td class="p-3">Rp {{ number_format($return->refund_amount, 0, ',', '.') }}</td>
                        <td class="p-3">{{ $return->status }}</td>
                        <td class="p-3">
                            <form method="POST" action="{{ route('admin.returns.verify', $return) }}" class="min-w-64 space-y-2">
                                @csrf
                                @method('PATCH')
                                <select name="physical_status" class="w-full rounded border p-1">
                                    @foreach ($physicalStatuses as $value => $label)
                                        <option value="{{ $value }}" @selected(old('physical_status', $return->physical_status) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <textarea name="physical_note" rows="2" placeholder="{{ __('Catatan pemeriksaan') }}" class="w-full rounded border p-1">{{ old('physical_note', $return->physical_note) }}</textarea>
                                <button class="rounded border px-3 py-1 text-xs">{{ __('Simpan Verifikasi') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-6 text-center text-zinc-500">{{ __('Tidak ada data retur.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $returns->links() }}
</div>
@endsection
