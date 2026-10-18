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

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedMonth(): void
    {
        if ($this->month !== '') {
            $this->dateFrom = '';
            $this->dateTo = '';
        }
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        if ($this->dateFrom !== '') {
            $this->month = '';
        }
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        if ($this->dateTo !== '') {
            $this->month = '';
        }
        $this->resetPage();
    }

    public function filterToday(): void
    {
        $this->month = '';
        $this->dateFrom = now()->toDateString();
        $this->dateTo = now()->toDateString();
        $this->resetPage();
    }

    public function filterThisMonth(): void
    {
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->month = now()->format('Y-m');
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->status = '';
        $this->search = '';
        $this->month = '';
        $this->dateFrom = '';
        $this->dateTo = '';
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

        $this->recordTransition($order, null, WarehouseStatus::Diterima);
    }

    public function advance(Order $order): void
    {
        abort_unless($this->canUpdate, 403);

        $next = $order->warehouse_status?->next();

        abort_if($next === null, 403);

        $this->recordTransition($order, $order->warehouse_status, $next);
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

    {{-- Orders Table --}}
    <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-zinc-200 bg-zinc-50 text-[11px] font-semibold uppercase tracking-wider text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400">
                <tr>
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
                    <tr wire:key="order-{{ $order->id }}" class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/50 transition">
                        <td class="px-4 py-3 font-semibold text-zinc-900 dark:text-white">
                            {{ $order->shopee_order_sn }}
                        </td>
                        <td class="px-4 py-3 text-xs text-zinc-600 dark:text-zinc-300 whitespace-nowrap">
                            {{ $order->order_placed_at?->translatedFormat('d M Y, H:i') ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-xs font-mono text-zinc-500 dark:text-zinc-400">
                            {{ $order->shipments->first()?->tracking_number ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-xs text-zinc-600 dark:text-zinc-300">
                            {{ $order->items->count() }} item
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700 dark:bg-zinc-800 dark:text-zinc-300">
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
                            @if ($this->canUpdate)
                                @if (! $order->warehouse_status)
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
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-zinc-500 dark:text-zinc-400">
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
</div>
