<?php

use App\Enums\SyncStatus;
use App\Enums\WarehouseStatus;
use App\Jobs\SyncShopeeLogisticsJob;
use App\Jobs\SyncShopeeOrdersJob;
use App\Jobs\SyncShopeeReturnsJob;
use App\Models\Order;
use App\Models\SyncLog;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Status Sinkronisasi')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $activeTab = 'logs';

    #[Url]
    public string $jobType = '';

    #[Url]
    public string $logStatus = '';

    #[Url]
    public string $orderSearch = '';

    #[Url]
    public string $orderStatus = '';

    public ?int $selectedLogId = null;

    public ?int $viewingOrderId = null;

    public function updatedActiveTab(): void
    {
        $this->resetPage();
    }

    public function updatedJobType(): void
    {
        $this->resetPage();
    }

    public function updatedLogStatus(): void
    {
        $this->resetPage();
    }

    public function updatedOrderSearch(): void
    {
        $this->resetPage();
    }

    public function updatedOrderStatus(): void
    {
        $this->resetPage();
    }

    public function syncNow(): void
    {
        SyncShopeeOrdersJob::dispatchSync();
        SyncShopeeLogisticsJob::dispatchSync();
        SyncShopeeReturnsJob::dispatchSync();

        unset($this->logs, $this->metrics, $this->syncedOrders);

        Flux::toast(variant: 'success', text: __('Sinkronisasi Shopee selesai dijalankan. Detail data telah dicatat.'));
    }

    public function openLogDetail(int $logId): void
    {
        $this->selectedLogId = $logId;
    }

    public function closeLogDetail(): void
    {
        $this->selectedLogId = null;
    }

    public function viewOrder(int $orderId): void
    {
        $this->viewingOrderId = $orderId;
    }

    public function closeOrderView(): void
    {
        $this->viewingOrderId = null;
    }

    #[Computed]
    public function mode(): string
    {
        return config('shopee.mode', 'fake');
    }

    #[Computed]
    public function metrics(): array
    {
        $lastSuccess = SyncLog::where('status', SyncStatus::Success)->latest('id')->first();

        return [
            'total_orders' => Order::count(),
            'pending_warehouse' => Order::whereNull('warehouse_status')->count(),
            'total_logs' => SyncLog::count(),
            'last_sync_time' => $lastSuccess?->created_at?->diffForHumans() ?? 'Belum pernah',
            'last_sync_at' => $lastSuccess?->created_at?->translatedFormat('d M Y, H:i') ?? null,
        ];
    }

    #[Computed]
    public function selectedLog(): ?SyncLog
    {
        if (! $this->selectedLogId) {
            return null;
        }

        return SyncLog::find($this->selectedLogId);
    }

    #[Computed]
    public function viewingOrder(): ?Order
    {
        if (! $this->viewingOrderId) {
            return null;
        }

        return Order::with(['items', 'shipments', 'returns', 'processLogs.user'])->find($this->viewingOrderId);
    }

    #[Computed]
    public function logs()
    {
        return SyncLog::query()
            ->when($this->jobType, fn ($q) => $q->where('job_type', $this->jobType))
            ->when($this->logStatus, fn ($q) => $q->where('status', $this->logStatus))
            ->latest('id')
            ->paginate(15, pageName: 'logPage');
    }

    #[Computed]
    public function syncedOrders()
    {
        return Order::query()
            ->when($this->orderSearch, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('shopee_order_sn', 'like', '%' . $this->orderSearch . '%')
                        ->orWhere('buyer_username', 'like', '%' . $this->orderSearch . '%');
                });
            })
            ->when($this->orderStatus, fn ($q) => $q->where('marketplace_status', $this->orderStatus))
            ->with(['items', 'shipments'])
            ->latest('order_placed_at')
            ->paginate(15, pageName: 'orderPage');
    }
}; ?>

<div class="flex w-full flex-col gap-5">
    {{-- Top Header Section --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <flux:heading size="xl" class="font-display">{{ __('Status Sinkronisasi Shopee') }}</flux:heading>
                <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-bold {{ $this->mode === 'live' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300' }}">
                    <span class="h-1.5 w-1.5 rounded-full {{ $this->mode === 'live' ? 'bg-emerald-500' : 'bg-amber-500 animate-pulse' }}"></span>
                    {{ $this->mode === 'live' ? __('Mode Live (API Asli)') : __('Fake (Data Simulasi)') }}
                </span>
            </div>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Mode saat ini: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $this->mode === 'live' ? __('Live (Shopee Open API)') : __('Fake (Data Simulasi)') }}</span>. {{ __('Monitoring pipeline sinkronisasi pesanan, pembaruan status logistik resi, dan retur marketplace Shopee.') }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <flux:button variant="primary" wire:click="syncNow" wire:loading.attr="disabled" class="gap-2">
                <svg wire:loading.remove wire:target="syncNow" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <svg wire:loading wire:target="syncNow" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span wire:loading.remove wire:target="syncNow">{{ __('Sinkronkan Sekarang') }}</span>
                <span wire:loading wire:target="syncNow">{{ __('Memproses Sinkronisasi...') }}</span>
            </flux:button>
        </div>
    </div>

    {{-- System Mode Alert Note --}}
    @if ($this->mode !== 'live')
        <div class="flex items-start gap-3 rounded-xl border border-amber-200/80 bg-amber-50/80 p-4 text-xs sm:text-sm text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-300">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            <div>
                <strong class="font-bold">{{ __('Sistem masih berjalan dengan data simulasi karena akun Shopee Partner/ISV klien belum tersedia.') }}</strong>
                <p class="mt-0.5 leading-relaxed">
                    {{ __('Pesanan yang tersinkron di bawah ini adalah data contoh, bukan pesanan Shopee sungguhan. Gateway simulasi ini menjamin seluruh rantai alur gudang dan rekonsiliasi dapat dimonitoring secara komprehensif.') }}
                </p>
            </div>
        </div>
    @endif

    {{-- Performance & Synchronization Metric Cards --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-xl border border-zinc-200/80 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Total Pesanan Shopee') }}</p>
            <p class="mt-1 font-display text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($this->metrics['total_orders']) }}</p>
            <p class="mt-1 text-xs text-zinc-400">{{ __('Tersimpan di database') }}</p>
        </div>

        <div class="rounded-xl border border-zinc-200/80 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Perlu Proses Gudang') }}</p>
            <p class="mt-1 font-display text-2xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($this->metrics['pending_warehouse']) }}</p>
            <p class="mt-1 text-xs text-zinc-400">{{ __('Belum diproses gudang') }}</p>
        </div>

        <div class="rounded-xl border border-zinc-200/80 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Riwayat Eksekusi') }}</p>
            <p class="mt-1 font-display text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($this->metrics['total_logs']) }}</p>
            <p class="mt-1 text-xs text-zinc-400">{{ __('Total log sinkronisasi') }}</p>
        </div>

        <div class="rounded-xl border border-zinc-200/80 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-900">
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Terakhir Berhasil') }}</p>
            <p class="mt-1 font-display text-base font-bold text-emerald-600 dark:text-emerald-400 truncate" title="{{ $this->metrics['last_sync_at'] ?? '' }}">
                {{ $this->metrics['last_sync_time'] }}
            </p>
            <p class="mt-1 text-xs text-zinc-400 truncate">{{ $this->metrics['last_sync_at'] ?? 'Belum ada data' }}</p>
        </div>
    </div>

    {{-- Main View Tabs --}}
    <div class="border-b border-zinc-200 dark:border-zinc-800">
        <nav class="flex space-x-6 text-sm font-semibold" aria-label="Tabs">
            <button
                type="button"
                wire:click="$set('activeTab', 'logs')"
                class="flex items-center gap-2 border-b-2 py-3 transition {{ $activeTab === 'logs' ? 'border-brand-600 text-brand-600 dark:border-brand-400 dark:text-brand-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300' }}"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ __('Riwayat Log Sinkronisasi') }}</span>
                <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $this->metrics['total_logs'] }}</span>
            </button>

            <button
                type="button"
                wire:click="$set('activeTab', 'orders')"
                class="flex items-center gap-2 border-b-2 py-3 transition {{ $activeTab === 'orders' ? 'border-brand-600 text-brand-600 dark:border-brand-400 dark:text-brand-400' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-300' }}"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span>{{ __('Pesanan Terkini Hasil Sinkronisasi') }}</span>
                <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $this->metrics['total_orders'] }}</span>
            </button>
        </nav>
    </div>

    {{-- TAB 1: RIWAYAT LOG SINKRONISASI --}}
    @if ($activeTab === 'logs')
        {{-- Filters for Logs --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-semibold text-zinc-400 dark:text-zinc-500">{{ __('Jenis Job:') }}</span>
                <button
                    wire:click="$set('jobType', '')"
                    class="rounded-lg px-2.5 py-1 text-xs font-medium transition {{ $jobType === '' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}"
                >
                    {{ __('Semua') }}
                </button>
                <button
                    wire:click="$set('jobType', 'shopee_orders')"
                    class="rounded-lg px-2.5 py-1 text-xs font-medium transition {{ $jobType === 'shopee_orders' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}"
                >
                    {{ __('Pesanan (Orders)') }}
                </button>
                <button
                    wire:click="$set('jobType', 'shopee_logistics')"
                    class="rounded-lg px-2.5 py-1 text-xs font-medium transition {{ $jobType === 'shopee_logistics' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}"
                >
                    {{ __('Logistik (Tracking)') }}
                </button>
                <button
                    wire:click="$set('jobType', 'shopee_returns')"
                    class="rounded-lg px-2.5 py-1 text-xs font-medium transition {{ $jobType === 'shopee_returns' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}"
                >
                    {{ __('Retur (Returns)') }}
                </button>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-zinc-400 dark:text-zinc-500">{{ __('Status:') }}</span>
                <select
                    wire:model.live="logStatus"
                    class="rounded-lg border border-zinc-200 bg-white py-1 px-2.5 text-xs text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                >
                    <option value="">{{ __('Semua Status') }}</option>
                    <option value="success">{{ __('Berhasil') }}</option>
                    <option value="failed">{{ __('Gagal') }}</option>
                </select>
            </div>
        </div>

        {{-- Logs Table --}}
        <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-200 bg-zinc-50 text-[11px] font-semibold uppercase tracking-wider text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400">
                    <tr>
                        <th class="px-4 py-3">{{ __('Waktu') }}</th>
                        <th class="px-4 py-3">{{ __('Jenis Sinkronisasi') }}</th>
                        <th class="px-4 py-3">{{ __('Status') }}</th>
                        <th class="px-4 py-3">{{ __('Ringkasan Keterangan') }}</th>
                        <th class="px-4 py-3 text-center">{{ __('Data Diproses') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->logs as $log)
                        @php
                            $syncedCount = $log->payload['synced'] ?? (is_array($log->payload['orders'] ?? null) ? count($log->payload['orders']) : 0);
                        @endphp
                        <tr wire:key="sync-log-{{ $log->id }}" class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/50 transition">
                            <td class="px-4 py-3 text-xs text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                                <span class="font-medium text-zinc-900 dark:text-white">{{ $log->created_at?->diffForHumans() }}</span>
                                <span class="block text-[11px] text-zinc-400">{{ $log->created_at?->translatedFormat('d M Y, H:i:s') }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs">
                                @if ($log->job_type === 'shopee_orders')
                                    <span class="inline-flex items-center gap-1.5 rounded-md bg-blue-50 px-2 py-0.5 font-bold text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                        {{ __('Pesanan (Orders)') }}
                                    </span>
                                @elseif ($log->job_type === 'shopee_logistics')
                                    <span class="inline-flex items-center gap-1.5 rounded-md bg-purple-50 px-2 py-0.5 font-bold text-purple-700 dark:bg-purple-950/60 dark:text-purple-300">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                                        {{ __('Logistik Resi') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-md bg-orange-50 px-2 py-0.5 font-bold text-orange-700 dark:bg-orange-950/60 dark:text-orange-300">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                        {{ __('Retur Barang') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($log->status === SyncStatus::Success)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        {{ __('Berhasil') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        {{ __('Gagal') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-zinc-600 dark:text-zinc-300">
                                {{ $log->message }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="rounded-md bg-zinc-100 px-2 py-0.5 text-xs font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                    {{ $syncedCount }} item
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <flux:button size="sm" variant="subtle" wire:click="openLogDetail({{ $log->id }})">
                                    {{ __('Lihat Detail') }}
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-zinc-500 dark:text-zinc-400">
                                <p class="text-sm font-medium">{{ __('Belum ada riwayat sinkronisasi.') }}</p>
                                <p class="mt-1 text-xs">{{ __('Klik "Sinkronkan Sekarang" untuk memulai proses pengambilan data dari Shopee.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $this->logs->links() }}</div>
    @endif

    {{-- TAB 2: DATA PESANAN HASIL SINKRONISASI --}}
    @if ($activeTab === 'orders')
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="w-full sm:max-w-xs">
                <flux:input
                    type="search"
                    wire:model.live.debounce.300ms="orderSearch"
                    placeholder="{{ __('Cari Order SN atau Pembeli...') }}"
                />
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-zinc-400 dark:text-zinc-500">{{ __('Status Shopee:') }}</span>
                <select
                    wire:model.live="orderStatus"
                    class="rounded-lg border border-zinc-200 bg-white py-1 px-2.5 text-xs text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                >
                    <option value="">{{ __('Semua Status') }}</option>
                    <option value="UNPAID">UNPAID</option>
                    <option value="READY_TO_SHIP">READY_TO_SHIP</option>
                    <option value="PROCESSED">PROCESSED</option>
                    <option value="SHIPPED">SHIPPED</option>
                    <option value="COMPLETED">COMPLETED</option>
                    <option value="CANCELLED">CANCELLED</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-200 bg-zinc-50 text-[11px] font-semibold uppercase tracking-wider text-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400">
                    <tr>
                        <th class="px-4 py-3">{{ __('Order SN') }}</th>
                        <th class="px-4 py-3">{{ __('Pembeli') }}</th>
                        <th class="px-4 py-3">{{ __('Tanggal Pesan') }}</th>
                        <th class="px-4 py-3">{{ __('Nilai Pesanan') }}</th>
                        <th class="px-4 py-3">{{ __('Status Shopee') }}</th>
                        <th class="px-4 py-3">{{ __('Status Gudang') }}</th>
                        <th class="px-4 py-3">{{ __('Resi & Kurir') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->syncedOrders as $order)
                        <tr wire:key="order-item-{{ $order->id }}" class="hover:bg-zinc-50/60 dark:hover:bg-zinc-800/50 transition">
                            <td class="px-4 py-3 font-semibold text-zinc-900 dark:text-white">
                                {{ $order->shopee_order_sn }}
                            </td>
                            <td class="px-4 py-3 text-xs text-zinc-600 dark:text-zinc-300">
                                {{ $order->buyer_username ?? 'Anonim' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                                {{ $order->order_placed_at?->translatedFormat('d M Y, H:i') ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-xs font-semibold text-zinc-900 dark:text-white whitespace-nowrap">
                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700 dark:bg-zinc-800 dark:text-zinc-300">
                                    {{ $order->marketplace_status }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if ($order->warehouse_status)
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                        {{ $order->warehouse_status->label() }}
                                    </span>
                                @else
                                    <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                        {{ __('Belum Diproses') }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-zinc-500 dark:text-zinc-400">
                                @if ($shipment = $order->shipments->first())
                                    <span class="font-mono">{{ $shipment->tracking_number ?? 'Belum terbit' }}</span>
                                    <span class="block text-[11px] text-zinc-400">{{ $shipment->carrier ?? 'Kurir Shopee' }}</span>
                                @else
                                    <span class="text-zinc-400">Belum ada resi</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <flux:button size="sm" variant="subtle" wire:click="viewOrder({{ $order->id }})">
                                    {{ __('Rincian') }}
                                </flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-zinc-500 dark:text-zinc-400">
                                <p class="text-sm font-medium">{{ __('Tidak ada pesanan yang sesuai dengan filter.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $this->syncedOrders->links() }}</div>
    @endif

    {{-- MODAL 1: DETAIL LOG SINKRONISASI --}}
    @if ($this->selectedLog)
        @php
            $log = $this->selectedLog;
            $payload = $log->payload ?? [];
        @endphp
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
            role="dialog"
            aria-modal="true"
            aria-labelledby="sync-log-title"
            x-data
            @keydown.window.escape="$wire.closeLogDetail()"
        >
            <div class="fixed inset-0 bg-zinc-900/60 backdrop-blur-xs transition-opacity" wire:click="closeLogDetail"></div>

            <div class="relative max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-800 dark:bg-zinc-900">
                {{-- Modal Header --}}
                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-zinc-100 bg-white/95 px-6 py-4 backdrop-blur-xs dark:border-zinc-800 dark:bg-zinc-900/95">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded-md bg-zinc-100 px-2.5 py-0.5 text-xs font-mono font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                Log #{{ $log->id }}
                            </span>
                            @if ($log->status === SyncStatus::Success)
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                    {{ __('Berhasil') }}
                                </span>
                            @else
                                <span class="rounded-full bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                                    {{ __('Gagal') }}
                                </span>
                            @endif
                        </div>
                        <h3 id="sync-log-title" class="mt-1 font-display text-lg font-bold text-zinc-900 dark:text-white">
                            {{ __('Detail Eksekusi: :job', ['job' => $log->job_type]) }}
                        </h3>
                    </div>

                    <button
                        type="button"
                        wire:click="closeLogDetail"
                        class="rounded-lg p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                        aria-label="Tutup"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-6 space-y-6">
                    {{-- Summary Grid --}}
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-xl border border-zinc-100 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/40">
                            <p class="text-[11px] font-semibold text-zinc-500">{{ __('Waktu Eksekusi') }}</p>
                            <p class="mt-1 text-xs font-bold text-zinc-900 dark:text-white">{{ $log->created_at?->translatedFormat('d M Y, H:i:s') }}</p>
                        </div>
                        <div class="rounded-xl border border-zinc-100 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/40">
                            <p class="text-[11px] font-semibold text-zinc-500">{{ __('Jenis Job') }}</p>
                            <p class="mt-1 text-xs font-bold font-mono text-zinc-900 dark:text-white">{{ $log->job_type }}</p>
                        </div>
                        <div class="rounded-xl border border-zinc-100 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/40">
                            <p class="text-[11px] font-semibold text-zinc-500">{{ __('Data Diproses') }}</p>
                            <p class="mt-1 text-xs font-bold text-zinc-900 dark:text-white">{{ $payload['synced'] ?? 0 }} {{ __('item') }}</p>
                        </div>
                        <div class="rounded-xl border border-zinc-100 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-800/40">
                            <p class="text-[11px] font-semibold text-zinc-500">{{ __('Keterangan') }}</p>
                            <p class="mt-1 text-xs font-medium text-zinc-700 dark:text-zinc-300 truncate" title="{{ $log->message }}">{{ $log->message }}</p>
                        </div>
                    </div>

                    {{-- Data Detail Table based on job_type --}}
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                            {{ __('Rincian Data yang Disinkronkan:') }}
                        </h4>

                        @if ($log->job_type === 'shopee_orders' && !empty($payload['orders']))
                            <div class="mt-3 overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-zinc-50 uppercase text-[10px] font-semibold text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                        <tr>
                                            <th class="px-3 py-2.5">{{ __('Order SN') }}</th>
                                            <th class="px-3 py-2.5">{{ __('Pembeli') }}</th>
                                            <th class="px-3 py-2.5">{{ __('Nilai Order') }}</th>
                                            <th class="px-3 py-2.5">{{ __('Status Shopee') }}</th>
                                            <th class="px-3 py-2.5">{{ __('Jumlah Item') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                        @foreach ($payload['orders'] as $ord)
                                            <tr>
                                                <td class="px-3 py-2 font-mono font-bold text-zinc-900 dark:text-white">{{ $ord['order_sn'] }}</td>
                                                <td class="px-3 py-2 text-zinc-600 dark:text-zinc-300">{{ $ord['buyer'] }}</td>
                                                <td class="px-3 py-2 font-semibold text-zinc-900 dark:text-white">Rp {{ number_format($ord['amount'], 0, ',', '.') }}</td>
                                                <td class="px-3 py-2">
                                                    <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-700 dark:bg-zinc-800 dark:text-zinc-300">
                                                        {{ $ord['status'] }}
                                                    </span>
                                                </td>
                                                <td class="px-3 py-2 text-zinc-600 dark:text-zinc-300">{{ $ord['items_count'] }} item</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif ($log->job_type === 'shopee_logistics' && !empty($payload['tracking']))
                            <div class="mt-3 overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-zinc-50 uppercase text-[10px] font-semibold text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                        <tr>
                                            <th class="px-3 py-2.5">{{ __('Order SN') }}</th>
                                            <th class="px-3 py-2.5">{{ __('Nomor Resi') }}</th>
                                            <th class="px-3 py-2.5">{{ __('Ekspedisi/Kurir') }}</th>
                                            <th class="px-3 py-2.5">{{ __('Status Logistik Shopee') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                        @foreach ($payload['tracking'] as $trk)
                                            <tr>
                                                <td class="px-3 py-2 font-mono font-bold text-zinc-900 dark:text-white">{{ $trk['order_sn'] }}</td>
                                                <td class="px-3 py-2 font-mono text-zinc-700 dark:text-zinc-300">{{ $trk['tracking_number'] }}</td>
                                                <td class="px-3 py-2 text-zinc-600 dark:text-zinc-300">{{ $trk['carrier'] }}</td>
                                                <td class="px-3 py-2">
                                                    <span class="rounded bg-purple-50 px-1.5 py-0.5 text-[10px] font-medium text-purple-700 dark:bg-purple-950 dark:text-purple-300">
                                                        {{ $trk['logistics_status'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif ($log->job_type === 'shopee_returns' && !empty($payload['returns']))
                            <div class="mt-3 overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-zinc-50 uppercase text-[10px] font-semibold text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                        <tr>
                                            <th class="px-3 py-2.5">{{ __('Return SN') }}</th>
                                            <th class="px-3 py-2.5">{{ __('Order SN') }}</th>
                                            <th class="px-3 py-2.5">{{ __('Alasan Retur') }}</th>
                                            <th class="px-3 py-2.5">{{ __('Status') }}</th>
                                            <th class="px-3 py-2.5">{{ __('Refund') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                        @foreach ($payload['returns'] as $ret)
                                            <tr>
                                                <td class="px-3 py-2 font-mono font-bold text-zinc-900 dark:text-white">{{ $ret['return_sn'] }}</td>
                                                <td class="px-3 py-2 font-mono text-zinc-600 dark:text-zinc-300">{{ $ret['order_sn'] }}</td>
                                                <td class="px-3 py-2 text-zinc-600 dark:text-zinc-300">{{ $ret['reason'] }}</td>
                                                <td class="px-3 py-2">
                                                    <span class="rounded bg-amber-50 px-1.5 py-0.5 text-[10px] font-medium text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                                                        {{ $ret['status'] }}
                                                    </span>
                                                </td>
                                                <td class="px-3 py-2 font-semibold">Rp {{ number_format($ret['refund_amount'], 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="mt-3 rounded-xl border border-zinc-100 bg-zinc-50 p-4 text-xs text-zinc-500 dark:border-zinc-800 dark:bg-zinc-800/40 dark:text-zinc-400">
                                <p>{{ __('Log ini tercatat sebagai ringkasan sinkronisasi dengan :count data diproses.', ['count' => $payload['synced'] ?? 0]) }}</p>
                                <p class="mt-1">{{ __('Klik tombol "Sinkronkan Sekarang" untuk menjalankan sinkronisasi baru dengan pencatatan data terperinci.') }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Technical Raw Payload Viewer --}}
                    <div x-data="{ openPayload: false }" class="border-t border-zinc-100 pt-4 dark:border-zinc-800">
                        <button
                            type="button"
                            @click="openPayload = !openPayload"
                            class="flex items-center gap-1.5 text-xs font-semibold text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200"
                        >
                            <svg :class="openPayload ? 'rotate-90' : ''" class="h-3.5 w-3.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            <span>{{ __('Lihat Raw JSON Payload (Audit Log)') }}</span>
                        </button>
                        <div x-show="openPayload" x-cloak class="mt-3">
                            <pre class="max-h-48 overflow-y-auto rounded-xl bg-zinc-950 p-3 text-[11px] font-mono text-emerald-400">{{ json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="border-t border-zinc-100 bg-zinc-50 px-6 py-3 text-right dark:border-zinc-800 dark:bg-zinc-800/50">
                    <flux:button size="sm" wire:click="closeLogDetail">
                        {{ __('Tutup') }}
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL 2: RINCIAN PESANAN --}}
    @if ($this->viewingOrder)
        @php
            $ord = $this->viewingOrder;
        @endphp
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
            role="dialog"
            aria-modal="true"
            aria-labelledby="order-detail-title"
            x-data
            @keydown.window.escape="$wire.closeOrderView()"
        >
            <div class="fixed inset-0 bg-zinc-900/60 backdrop-blur-xs transition-opacity" wire:click="closeOrderView"></div>

            <div class="relative max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-800 dark:bg-zinc-900">
                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-zinc-100 bg-white/95 px-6 py-4 backdrop-blur-xs dark:border-zinc-800 dark:bg-zinc-900/95">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 id="order-detail-title" class="font-display text-lg font-bold text-zinc-900 dark:text-white">
                                {{ __('Pesanan :sn', ['sn' => $ord->shopee_order_sn]) }}
                            </h3>
                            <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ $ord->marketplace_status }}
                            </span>
                        </div>
                        <p class="mt-0.5 text-xs text-zinc-400">
                            {{ __('Pembeli: :buyer | Dipesan pada: :date', ['buyer' => $ord->buyer_username ?? 'Anonim', 'date' => $ord->order_placed_at?->translatedFormat('d M Y, H:i') ?? '-']) }}
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="closeOrderView"
                        class="rounded-lg p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                        aria-label="Tutup"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>
                </div>

                <div class="p-6 space-y-6">
                    {{-- Logistics and Warehouse status strip --}}
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border border-zinc-100 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                            <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider">{{ __('Status Gudang') }}</p>
                            <p class="mt-1 text-sm font-bold text-zinc-900 dark:text-white">
                                {{ $ord->warehouse_status?->label() ?? __('Belum Diproses Gudang') }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-zinc-100 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-800/40">
                            <p class="text-xs font-bold text-zinc-500 uppercase tracking-wider">{{ __('Pengiriman & Resi') }}</p>
                            @if ($shipment = $ord->shipments->first())
                                <p class="mt-1 text-sm font-mono font-bold text-zinc-900 dark:text-white">
                                    {{ $shipment->tracking_number ?? 'Belum ada resi' }}
                                </p>
                                <p class="text-xs text-zinc-400">{{ $shipment->carrier ?? 'Kurir Shopee' }} ({{ $shipment->shopee_logistics_status }})</p>
                            @else
                                <p class="mt-1 text-xs text-zinc-400">{{ __('Belum ada informasi pengiriman.') }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Items Table --}}
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                            {{ __('Daftar Barang Dipesan:') }}
                        </h4>
                        <div class="mt-3 overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-zinc-50 uppercase text-[10px] font-semibold text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                    <tr>
                                        <th class="px-4 py-2.5">{{ __('Nama Produk') }}</th>
                                        <th class="px-4 py-2.5">{{ __('SKU') }}</th>
                                        <th class="px-4 py-2.5 text-center">{{ __('Qty') }}</th>
                                        <th class="px-4 py-2.5 text-right">{{ __('Harga Satuan') }}</th>
                                        <th class="px-4 py-2.5 text-right">{{ __('Subtotal') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @forelse ($ord->items as $item)
                                        <tr>
                                            <td class="px-4 py-2.5 font-medium text-zinc-900 dark:text-white">{{ $item->item_name }}</td>
                                            <td class="px-4 py-2.5 font-mono text-zinc-500">{{ $item->sku ?? '-' }}</td>
                                            <td class="px-4 py-2.5 text-center font-bold text-zinc-900 dark:text-white">{{ $item->quantity }}</td>
                                            <td class="px-4 py-2.5 text-right text-zinc-600 dark:text-zinc-300">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                            <td class="px-4 py-2.5 text-right font-semibold text-zinc-900 dark:text-white">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-4 text-center text-zinc-400">{{ __('Tidak ada rincian item.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="border-t border-zinc-200 bg-zinc-50/50 font-bold dark:border-zinc-700 dark:bg-zinc-800/30">
                                    <tr>
                                        <td colspan="4" class="px-4 py-2.5 text-right text-xs uppercase">{{ __('Total Nilai Pesanan:') }}</td>
                                        <td class="px-4 py-2.5 text-right text-sm text-brand-600 dark:text-brand-400">Rp {{ number_format($ord->total_amount, 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="border-t border-zinc-100 bg-zinc-50 px-6 py-3 text-right dark:border-zinc-800 dark:bg-zinc-800/50">
                    <flux:button size="sm" wire:click="closeOrderView">
                        {{ __('Tutup') }}
                    </flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
