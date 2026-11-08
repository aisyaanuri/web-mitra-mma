@extends('layouts.app')

@section('content')
<div class="space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <flux:heading size="xl" class="font-display">{{ __('Status Sinkronisasi Shopee') }}</flux:heading>
            <p class="mt-1 text-sm text-zinc-500">{{ __('Mode saat ini: :mode', ['mode' => $mode === 'live' ? __('Live (Shopee Open API)') : __('Fake (Data Simulasi)')]) }}</p>
        </div>
        <form method="POST" action="{{ route('admin.sync.run') }}" onsubmit="return confirm('{{ __('Jalankan sinkronisasi sekarang?') }}')">
            @csrf
            <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white">{{ __('Sinkronkan Sekarang') }}</button>
        </form>
    </div>
    @if (session('status'))
        <div class="rounded-lg bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif
    <div class="grid gap-3 sm:grid-cols-4">
        @foreach ($metrics as $label => $value)
            <div class="rounded-xl border p-4"><p class="text-xs uppercase text-zinc-500">{{ __(str_replace('_', ' ', $label)) }}</p><p class="mt-1 text-xl font-bold">{{ $value }}</p></div>
        @endforeach
    </div>
    <form method="GET" action="{{ route('admin.sync.index') }}" class="flex flex-wrap gap-2">
        <select name="job_type" class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
            <option value="">{{ __('Semua jenis pekerjaan') }}</option>
            @foreach ($jobTypes as $type)
                <option value="{{ $type }}" @selected(($filters['job_type'] ?? '') === $type)>{{ $type }}</option>
            @endforeach
        </select>
        <select name="log_status" class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
            <option value="">{{ __('Semua status') }}</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(($filters['log_status'] ?? '') === $status->value)>{{ ucfirst($status->value) }}</option>
            @endforeach
        </select>
        <button class="rounded-lg border px-4 py-2 text-sm">{{ __('Filter Log') }}</button>
        <a href="{{ route('admin.sync.index') }}" class="rounded-lg border px-4 py-2 text-sm">{{ __('Reset') }}</a>
    </form>
    <div class="overflow-x-auto rounded-lg border">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-800"><tr><th class="p-3">{{ __('Waktu') }}</th><th class="p-3">{{ __('Jenis') }}</th><th class="p-3">{{ __('Status') }}</th><th class="p-3">{{ __('Pesan') }}</th></tr></thead>
            <tbody class="divide-y">
                @forelse ($logs as $log)
                    <tr><td class="p-3">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td><td class="p-3">{{ $log->job_type }}</td><td class="p-3">{{ $log->status instanceof \BackedEnum ? ucfirst($log->status->value) : ucfirst($log->status) }}</td><td class="p-3">{{ $log->message ?? $log->error_message ?? '-' }}</td></tr>
                @empty
                    <tr><td colspan="4" class="p-6 text-center text-zinc-500">{{ __('Belum ada log sinkronisasi.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $logs->links() }}

    <div class="flex flex-wrap items-center justify-between gap-2 pt-4">
        <flux:heading size="lg">{{ __('Pesanan Tersinkron') }}</flux:heading>
        <form method="GET" action="{{ route('admin.sync.index') }}" class="flex gap-2">
            <input type="hidden" name="job_type" value="{{ $filters['job_type'] ?? '' }}">
            <input type="hidden" name="log_status" value="{{ $filters['log_status'] ?? '' }}">
            <input name="order_search" value="{{ $filters['order_search'] ?? '' }}" placeholder="{{ __('Cari pesanan/pembeli') }}" class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
            <input name="order_status" value="{{ $filters['order_status'] ?? '' }}" placeholder="{{ __('Status pesanan') }}" class="rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900">
            <button class="rounded-lg border px-3 py-2 text-sm">{{ __('Cari') }}</button>
        </form>
    </div>
    <div class="overflow-x-auto rounded-lg border">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-800"><tr><th class="p-3">{{ __('No. Pesanan') }}</th><th class="p-3">{{ __('Pembeli') }}</th><th class="p-3">{{ __('Status Shopee') }}</th><th class="p-3">{{ __('Total') }}</th><th class="p-3">{{ __('Tanggal') }}</th></tr></thead>
            <tbody class="divide-y">
                @forelse ($orders as $order)
                    <tr>
                        <td class="p-3 font-mono">{{ $order->shopee_order_sn }}
                            <details class="mt-1 font-sans">
                                <summary class="cursor-pointer text-xs text-blue-600">{{ __('Detail barang') }}</summary>
                                <ul class="mt-1 space-y-1 text-xs">
                                    @foreach ($order->items as $item)
                                        <li>{{ $item->item_name }} <span class="text-zinc-500">({{ $item->sku }})</span> × {{ $item->quantity }}</li>
                                    @endforeach
                                </ul>
                            </details>
                        </td>
                        <td class="p-3">{{ $order->buyer_username }}</td>
                        <td class="p-3">{{ $order->marketplace_status }}</td>
                        <td class="p-3">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        <td class="p-3">{{ $order->order_placed_at?->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-6 text-center text-zinc-500">{{ __('Belum ada pesanan.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $orders->links() }}
</div>
@endsection
