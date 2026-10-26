<?php

use App\Enums\UserRole;
use App\Enums\WarehouseStatus;
use App\Models\Order;
use App\Models\WarehouseProcessLog;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Proses Gudang')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $status = '';

    #[Url]
    public string $search = '';

    #[Url]
    public string $month = '';

    #[Url(as: 'date_from')]
    public string $dateFrom = '';

    #[Url(as: 'date_to')]
    public string $dateTo = '';

    /** @var array<int, string> */
    public array $selectedOrderIds = [];

    public bool $selectAll = false;

    public ?int $packingSlipOrderId = null;

    public function updatedStatus(): void
    {
        $this->clearSelection();
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->clearSelection();
        $this->resetPage();
    }

    public function updatedMonth(): void
    {
        if ($this->month !== '') {
            $this->dateFrom = '';
            $this->dateTo = '';
        }
        $this->clearSelection();
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        if ($this->dateFrom !== '') {
            $this->month = '';
        }
        $this->clearSelection();
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        if ($this->dateTo !== '') {
            $this->month = '';
        }
        $this->clearSelection();
        $this->resetPage();
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selectedOrderIds = collect($this->orders->items())
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedOrderIds = [];
        }
    }

    public function clearSelection(): void
    {
        $this->selectedOrderIds = [];
        $this->selectAll = false;
    }

    public function filterToday(): void
    {
        $this->month = '';
        $this->dateFrom = now()->toDateString();
        $this->dateTo = now()->toDateString();
        $this->clearSelection();
        $this->resetPage();
    }

    public function filterThisMonth(): void
    {
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->month = now()->format('Y-m');
        $this->clearSelection();
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->status = '';
        $this->search = '';
        $this->month = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->clearSelection();
        $this->resetPage();
    }

    #[Computed]
    public function hasActiveFilters(): bool
    {
        return $this->status !== '' || $this->search !== '' || $this->month !== '' || $this->dateFrom !== '' || $this->dateTo !== '';
    }

    #[Computed]
    public function isFilterToday(): bool
    {
        $today = now()->toDateString();

        return $this->dateFrom === $today && $this->dateTo === $today && $this->month === '';
    }

    #[Computed]
    public function isFilterThisMonth(): bool
    {
        return $this->month === now()->format('Y-m') && $this->dateFrom === '' && $this->dateTo === '';
    }

    #[Computed]
    public function canUpdate(): bool
    {
        return Auth::user()->role === UserRole::AdminGudang;
    }

    public function markReceived(Order $order): void
    {
        abort_unless($this->canUpdate, 403);
        abort_if($order->warehouse_status !== null, 403);
        abort_if(in_array($order->marketplace_status, ['CANCELLED', 'IN_CANCEL']), 403);

        $this->recordTransition($order, null, WarehouseStatus::Diterima);
    }

    public function advance(Order $order): void
    {
        abort_unless($this->canUpdate, 403);
        abort_if(in_array($order->marketplace_status, ['CANCELLED', 'IN_CANCEL']), 403);

        $next = $order->warehouse_status?->next();

        abort_if($next === null, 403);

        $this->recordTransition($order, $order->warehouse_status, $next);
    }

    public function bulkUpdateStatus(string $targetStatus): void
    {
        abort_unless($this->canUpdate, 403);

        $target = WarehouseStatus::tryFrom($targetStatus);
        if (! $target || empty($this->selectedOrderIds)) {
            return;
        }

        $orders = Order::whereIn('id', $this->selectedOrderIds)->get();
        $updatedCount = 0;

        foreach ($orders as $order) {
            if (in_array($order->marketplace_status, ['CANCELLED', 'IN_CANCEL'])) {
                continue;
            }

            $from = $order->warehouse_status;
            $order->update(['warehouse_status' => $target->value]);

            WarehouseProcessLog::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'from_status' => $from?->value,
                'to_status' => $target->value,
                'ip_address' => request()->ip(),
            ]);

            $updatedCount++;
        }

        $this->clearSelection();

        Flux::toast(variant: 'success', text: __(':count pesanan berhasil diperbarui ke status :status.', [
            'count' => $updatedCount,
            'status' => $target->label(),
        ]));
    }

    public function openPackingSlip(int $orderId): void
    {
        $this->packingSlipOrderId = $orderId;
    }

    public function closePackingSlip(): void
    {
        $this->packingSlipOrderId = null;
    }

    #[Computed]
    public function packingSlipOrder(): ?Order
    {
        if (! $this->packingSlipOrderId) {
            return null;
        }

        return Order::with(['items', 'shipments'])->find($this->packingSlipOrderId);
    }

    private function recordTransition(Order $order, ?WarehouseStatus $from, WarehouseStatus $to): void
    {
        $order->update(['warehouse_status' => $to->value]);

        WarehouseProcessLog::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'ip_address' => request()->ip(),
        ]);

        Flux::toast(variant: 'success', text: __('Status pesanan :sn diperbarui ke :status.', [
            'sn' => $order->shopee_order_sn,
            'status' => $to->label(),
        ]));
    }

    #[Computed]
    public function orders()
    {
        return Order::query()
            ->when($this->status, fn ($q) => $q->where('warehouse_status', $this->status))
            ->when($this->search, fn ($q) => $q->where(function ($sub) {
                $sub->where('shopee_order_sn', 'like', "%{$this->search}%")
                    ->orWhereHas('shipments', fn ($sq) => $sq->where('tracking_number', 'like', "%{$this->search}%"));
            }))
            ->when($this->month, function ($q) {
                $parts = explode('-', $this->month);
                if (count($parts) === 2) {
                    $q->whereYear('order_placed_at', $parts[0])
                        ->whereMonth('order_placed_at', $parts[1]);
                }
            })
            ->when($this->dateFrom, fn ($q) => $q->whereDate('order_placed_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('order_placed_at', '<=', $this->dateTo))
            ->with(['items', 'shipments'])
            ->latest('order_placed_at')
            ->paginate(10);
    }
}; ?>

<div class="flex w-full flex-col gap-5">
    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" class="font-display">{{ __('Proses Gudang') }}</flux:heading>
            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                Kelola alur fisik pesanan, verifikasi penerimaan, pengemasan, dan pengiriman barang.
            </p>
        </div>
        <flux:button
            icon="arrow-down-tray"
            :href="route('admin.warehouse.export', [
                'status' => $status,
                'search' => $search,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'month' => $month,
            ])"
        >
            {{ __('Export Excel') }}
        </flux:button>
    </div>

    {{-- Status Filters --}}
    <div class="flex flex-wrap gap-2">
        <button
            wire:click="$set('status', '')"
            class="rounded-full px-3.5 py-1.5 text-xs font-semibold transition {{ $status === '' ? 'bg-zinc-900 text-white shadow-xs dark:bg-white dark:text-zinc-900' : 'border border-zinc-200 bg-white text-zinc-600 hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}"
        >
            {{ __('Semua Status') }}
        </button>
        @foreach (WarehouseStatus::cases() as $case)
            <button
                wire:click="$set('status', '{{ $case->value }}')"
                class="rounded-full px-3.5 py-1.5 text-xs font-semibold transition {{ $status === $case->value ? 'bg-zinc-900 text-white shadow-xs dark:bg-white dark:text-zinc-900' : 'border border-zinc-200 bg-white text-zinc-600 hover:border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}"
            >
                {{ $case->label() }}
            </button>
        @endforeach
    </div>

    {{-- Filter Panel: Search, Date Range, Month & Quick Presets --}}
    <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-700 dark:bg-zinc-900/50">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            {{-- Left: Search & Date Inputs --}}
            <div class="flex flex-1 flex-wrap items-end gap-3">
                {{-- Search --}}
                <div class="w-full sm:w-64">
                    <label class="mb-1 block text-xs font-medium text-zinc-600 dark:text-zinc-400">{{ __('Cari Pesanan') }}</label>
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        placeholder="{{ __('Order SN atau nomor resi...') }}"
                        icon="magnifying-glass"
                    />
                </div>

                {{-- Month Filter --}}
                <div class="w-full sm:w-44">
                    <label class="mb-1 block text-xs font-medium text-zinc-600 dark:text-zinc-400">{{ __('Pilih Bulan') }}</label>
                    <flux:input
                        type="month"
                        wire:model.live="month"
                    />
                </div>

                {{-- Specific Date Range --}}
                <div class="w-full sm:w-36">
                    <label class="mb-1 block text-xs font-medium text-zinc-600 dark:text-zinc-400">{{ __('Dari Tanggal') }}</label>
                    <flux:input
                        type="date"
                        wire:model.live="dateFrom"
                    />
                </div>

                <div class="w-full sm:w-36">
                    <label class="mb-1 block text-xs font-medium text-zinc-600 dark:text-zinc-400">{{ __('Sampai Tanggal') }}</label>
                    <flux:input
                        type="date"
                        wire:model.live="dateTo"
                    />
                </div>
            </div>

            {{-- Right: Quick Presets & Reset --}}
            <div class="flex flex-wrap items-center gap-2 pt-1 lg:pt-0">
                <button
                    type="button"
                    wire:click="filterToday"
                    class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ $this->isFilterToday ? 'bg-brand-600 text-white shadow-xs' : 'border border-zinc-200 bg-white text-zinc-600 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}"
                >
                    {{ __('Hari Ini') }}
                </button>

                <button
                    type="button"
                    wire:click="filterThisMonth"
                    class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ $this->isFilterThisMonth ? 'bg-brand-600 text-white shadow-xs' : 'border border-zinc-200 bg-white text-zinc-600 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}"
                >
                    {{ __('Bulan Ini') }}
                </button>

                @if ($this->hasActiveFilters)
                    <button
                        type="button"
                        wire:click="resetFilters"
                        class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-100 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-400"
                    >
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        {{ __('Reset Filter') }}
                    </button>
                @endif
            </div>
        </div>

        {{-- Active Filter Feedback Badge --}}
        @if ($month || $dateFrom || $dateTo)
            <div class="mt-3 flex items-center gap-2 text-xs text-brand-700 dark:text-brand-300">
                <span class="inline-flex items-center gap-1 rounded-md bg-brand-50 px-2 py-0.5 font-medium dark:bg-brand-950">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    @if ($month)
                        Periode Bulan: {{ \Carbon\Carbon::parse($month.'-01')->translatedFormat('F Y') }}
                    @elseif ($dateFrom && $dateTo)
                        Rentang: {{ \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($dateTo)->translatedFormat('d M Y') }}
                    @elseif ($dateFrom)
                        Mulai: {{ \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M Y') }}
                    @elseif ($dateTo)
                        Sampai: {{ \Carbon\Carbon::parse($dateTo)->translatedFormat('d M Y') }}
                    @endif
                </span>
                <span class="text-zinc-500">({{ $this->orders->total() }} pesanan ditemukan)</span>
            </div>
        @endif
    </div>

    @unless ($this->canUpdate)
        <p class="text-xs text-zinc-500 dark:text-zinc-400">
            {{ __('Mode pantau: Anda dapat melihat pesanan, namun hanya Admin Gudang yang dapat mengubah status penerimaan/pengemasan.') }}
        </p>
    @endunless

    {{-- Floating Contextual Bulk Action Bar --}}
    @if ($this->canUpdate && count($selectedOrderIds) > 0)
        <div class="sticky top-4 z-20 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-300 bg-brand-50 p-4 shadow-lg backdrop-blur-md dark:border-brand-800 dark:bg-brand-950/90 animate-in fade-in slide-in-from-top-2 duration-200">
            <div class="flex items-center gap-3">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-600 text-xs font-bold text-white shadow-xs">
                    {{ count($selectedOrderIds) }}
                </span>
                <div>
                    <p class="text-xs font-bold text-brand-950 dark:text-brand-100">
                        {{ __(':count pesanan dipilih untuk diproses masal', ['count' => count($selectedOrderIds)]) }}
                    </p>
                    <p class="text-[11px] text-brand-700 dark:text-brand-300">
                        {{ __('Pilih status target untuk memperbarui seluruh pesanan terpilih sekaligus:') }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <flux:button size="sm" wire:click="bulkUpdateStatus('diterima')">
                    {{ __('Set Diterima') }}
                </flux:button>
                <flux:button size="sm" wire:click="bulkUpdateStatus('dikemas')">
                    {{ __('Set Dikemas') }}
                </flux:button>
                <flux:button size="sm" wire:click="bulkUpdateStatus('siap_kirim')">
                    {{ __('Set Siap Kirim') }}
                </flux:button>
                <flux:button size="sm" variant="primary" wire:click="bulkUpdateStatus('dikirim')">
                    {{ __('Set Dikirim (Selesai)') }}
                </flux:button>
                <button
                    type="button"
                    wire:click="clearSelection"
                    class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-zinc-600 hover:bg-brand-100 dark:text-zinc-300 dark:hover:bg-brand-900/50"
                >
                    {{ __('Batal') }}
                </button>
            </div>
        </div>
    @endif

    {{-- Orders Table --}}
    <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-zinc-200 bg-zinc-50 text-[11px] font-semibold uppercase tracking-wider text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400">
                <tr>
                    @if ($this->canUpdate)
                        <th class="w-10 px-4 py-3 text-center">
                            <input
                                type="checkbox"
                                wire:model.live="selectAll"
                                class="h-4 w-4 rounded border-zinc-300 text-brand-600 focus:ring-brand-500 dark:border-zinc-600 dark:bg-zinc-800"
                                aria-label="{{ __('Pilih semua pesanan pada halaman ini') }}"
                            />
                        </th>
                    @endif
                    <th class="px-4 py-3">{{ __('Order SN') }}</th>
                    <th class="px-4 py-3">{{ __('Tanggal Pesan') }}</th>
                    <th class="px-4 py-3">{{ __('Resi') }}</th>
                    <th class="px-4 py-3">{{ __('Item') }}</th>
                    <th class="px-4 py-3">{{ __('Status Marketplace') }}</th>
                    <th class="px-4 py-3">{{ __('Status Gudang') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Aksi') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($this->orders as $order)
                    @php
                        $isCancelled = in_array($order->marketplace_status, ['CANCELLED', 'IN_CANCEL', 'TO_RETURN']);
                    @endphp
                    <tr wire:key="order-{{ $order->id }}" class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/50 transition {{ $isCancelled ? 'bg-rose-50/30 dark:bg-rose-950/10' : '' }}">
                        @if ($this->canUpdate)
                            <td class="px-4 py-3 text-center">
                                <input
                                    type="checkbox"
                                    wire:model.live="selectedOrderIds"
                                    value="{{ (string) $order->id }}"
                                    class="h-4 w-4 rounded border-zinc-300 text-brand-600 focus:ring-brand-500 dark:border-zinc-600 dark:bg-zinc-800"
                                    aria-label="{{ __('Pilih pesanan :sn', ['sn' => $order->shopee_order_sn]) }}"
                                />
                            </td>
                        @endif
                        <td class="px-4 py-3 font-semibold text-zinc-900 dark:text-white">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span>{{ $order->shopee_order_sn }}</span>
                                @if ($isCancelled)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-800 dark:bg-rose-950 dark:text-rose-300 animate-pulse">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        {{ __('Dibatalkan Shopee') }}
                                    </span>
                                @endif
                            </div>
                            @if ($isCancelled)
                                <p class="text-[11px] font-bold text-rose-600 dark:text-rose-400 mt-0.5">
                                    {{ __('Hentikan pengemasan! Pesanan ini dibatalkan di Shopee.') }}
                                </p>
                            @else
                                <p class="text-[11px] text-zinc-400 dark:text-zinc-500 font-normal">
                                    {{ $order->buyer_username ?? 'Pembeli' }}
                                </p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-zinc-600 dark:text-zinc-300 whitespace-nowrap">
                            {{ $order->order_placed_at?->translatedFormat('d M Y, H:i') ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-xs font-mono text-zinc-500 dark:text-zinc-400">
                            {{ $order->shipments->first()?->tracking_number ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-xs text-zinc-600 dark:text-zinc-300">
                            {{ $order->items->count() }} item
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-[11px] font-medium {{ $isCancelled ? 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 font-bold' : 'bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300' }}">
                                {{ $order->marketplace_status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if ($order->warehouse_status)
                                <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                    {{ $order->warehouse_status->label() }}
                                </span>
                            @else
                                <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                    {{ __('Belum Diproses') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                {{-- Tombol Cetak Lembar Kemas / Packing Slip --}}
                                <flux:button
                                    size="sm"
                                    icon="document-text"
                                    wire:click="openPackingSlip({{ $order->id }})"
                                    title="{{ __('Cetak Lembar Kemas / Packing Slip') }}"
                                >
                                    {{ __('Slip') }}
                                </flux:button>

                                @if ($this->canUpdate)
                                    @if ($isCancelled)
                                        <span class="rounded-md border border-rose-200 bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-600 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-400">
                                            {{ __('Batal') }}
                                        </span>
                                    @elseif (! $order->warehouse_status)
                                        <flux:button size="sm" variant="primary" wire:click="markReceived({{ $order->id }})">
                                            {{ __('Tandai Diterima') }}
                                        </flux:button>
                                    @elseif ($next = $order->warehouse_status->next())
                                        <flux:button size="sm" variant="primary" wire:click="advance({{ $order->id }})">
                                            {{ __('Lanjutkan ke :status', ['status' => $next->label()]) }}
                                        </flux:button>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 dark:text-emerald-400">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            {{ __('Selesai') }}
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $this->canUpdate ? '8' : '7' }}" class="px-4 py-10 text-center text-zinc-500 dark:text-zinc-400">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <svg class="h-8 w-8 text-zinc-300 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <p class="text-sm font-medium">{{ __('Tidak ada pesanan yang cocok dengan filter.') }}</p>
                                @if ($this->hasActiveFilters)
                                    <button
                                        type="button"
                                        wire:click="resetFilters"
                                        class="mt-1 text-xs text-brand-600 hover:underline dark:text-brand-400"
                                    >
                                        {{ __('Reset semua filter') }}
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $this->orders->links() }}</div>

    {{-- Modal Lembar Kemas / Packing Slip (Print Friendly) --}}
    @if ($this->packingSlipOrder)
        @php
            $slip = $this->packingSlipOrder;
            $shipment = $slip->shipments->first();
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
            <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 print:max-w-none print:shadow-none print:border-none print:p-0">
                {{-- Header Print Controls (Hidden on Print) --}}
                <div class="flex items-center justify-between border-b border-zinc-200 pb-4 dark:border-zinc-800 print:hidden">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-brand-600 dark:text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        <h3 class="font-display text-base font-bold text-zinc-900 dark:text-white">
                            {{ __('Lembar Kemas / Packing Slip') }}
                        </h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:button size="sm" variant="primary" onclick="window.print()" class="gap-1.5">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            {{ __('Cetak Sekarang') }}
                        </flux:button>
                        <button
                            type="button"
                            wire:click="closePackingSlip"
                            class="rounded-lg p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Printable Document Content --}}
                <div class="mt-5 space-y-5 text-zinc-900 dark:text-zinc-100 print:m-0 print:text-black">
                    {{-- Company Header & Order SN --}}
                    <div class="flex items-start justify-between border-b border-zinc-200 pb-4 dark:border-zinc-800">
                        <div>
                            <h2 class="font-display text-lg font-bold tracking-tight">PT MUSTIKA MAKMUR ABADI</h2>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                Divisi Distribusi & Pergudangan FMCG Resmi
                            </p>
                            <p class="text-[11px] text-zinc-400">
                                Jawa Timur, Indonesia | Telp: (0341) 5058888
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="rounded bg-zinc-100 px-2.5 py-1 font-mono text-xs font-bold text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                                {{ $slip->shopee_order_sn }}
                            </span>
                            <p class="mt-1 text-[11px] text-zinc-500">
                                {{ __('Tgl:') }} {{ $slip->order_placed_at?->translatedFormat('d M Y, H:i') ?? '-' }}
                            </p>
                        </div>
                    </div>

                    {{-- Order & Shipping Details --}}
                    <div class="grid grid-cols-2 gap-4 rounded-xl border border-zinc-200 bg-zinc-50/60 p-4 text-xs dark:border-zinc-800 dark:bg-zinc-800/40 print:bg-white print:border-zinc-300">
                        <div>
                            <p class="font-bold text-zinc-400 uppercase text-[10px]">{{ __('Informasi Pembeli') }}</p>
                            <p class="mt-1 font-bold text-zinc-900 dark:text-white">{{ $slip->buyer_username ?? 'Customer Shopee' }}</p>
                            <p class="text-zinc-500">Marketplace: Shopee Indonesia</p>
                        </div>
                        <div>
                            <p class="font-bold text-zinc-400 uppercase text-[10px]">{{ __('Ekspedisi & Nomor Resi') }}</p>
                            <p class="mt-1 font-mono font-bold text-zinc-900 dark:text-white">
                                {{ $shipment?->tracking_number ?? 'Belum ada nomor resi' }}
                            </p>
                            <p class="text-zinc-500">{{ $shipment?->carrier ?? 'Kurir Shopee' }}</p>
                        </div>
                    </div>

                    {{-- Pick & Pack Items Table --}}
                    <div>
                        <div class="flex items-center justify-between pb-2">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-500">
                                {{ __('Daftar Barang yang Wajib Dikemas:') }}
                            </h4>
                            <span class="text-[11px] text-zinc-400">{{ $slip->items->count() }} macam produk</span>
                        </div>
                        <table class="w-full text-left text-xs border border-zinc-200 dark:border-zinc-700">
                            <thead class="bg-zinc-100 uppercase text-[10px] font-semibold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                <tr>
                                    <th class="w-10 px-3 py-2 text-center border-r border-zinc-200 dark:border-zinc-700">QC</th>
                                    <th class="px-3 py-2 border-r border-zinc-200 dark:border-zinc-700">{{ __('Nama Produk & Varian') }}</th>
                                    <th class="px-3 py-2 border-r border-zinc-200 dark:border-zinc-700">{{ __('SKU') }}</th>
                                    <th class="w-16 px-3 py-2 text-center font-bold">{{ __('Qty') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($slip->items as $item)
                                    <tr>
                                        <td class="px-3 py-2 text-center border-r border-zinc-200 dark:border-zinc-700">
                                            <div class="mx-auto h-4 w-4 rounded border border-zinc-400"></div>
                                        </td>
                                        <td class="px-3 py-2 font-medium border-r border-zinc-200 dark:border-zinc-700">
                                            {{ $item->item_name }}
                                        </td>
                                        <td class="px-3 py-2 font-mono text-zinc-500 border-r border-zinc-200 dark:border-zinc-700">
                                            {{ $item->sku ?? '-' }}
                                        </td>
                                        <td class="px-3 py-2 text-center font-bold text-sm">
                                            {{ $item->quantity }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Signatures & Warehouse Verification --}}
                    <div class="grid grid-cols-2 gap-6 pt-6 text-xs text-center border-t border-zinc-200 dark:border-zinc-800 print:pt-4">
                        <div>
                            <p class="text-zinc-500">{{ __('Petugas Pengemas (Packer):') }}</p>
                            <div class="mt-12 border-b border-zinc-400 mx-auto w-36"></div>
                            <p class="mt-1 text-[11px] text-zinc-400">(..................................)</p>
                        </div>
                        <div>
                            <p class="text-zinc-500">{{ __('Verifikator Quality Control (QC):') }}</p>
                            <div class="mt-12 border-b border-zinc-400 mx-auto w-36"></div>
                            <p class="mt-1 text-[11px] text-zinc-400">(..................................)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
