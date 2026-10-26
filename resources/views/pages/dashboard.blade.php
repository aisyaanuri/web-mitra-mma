<?php

use App\Enums\ReconciliationStatus;
use App\Enums\UserRole;
use App\Enums\WarehouseStatus;
use App\Models\Order;
use App\Models\ReconciliationRecord;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard')] class extends Component
{
    #[Computed]
    public function isGudangOnly(): bool
    {
        return Auth::user()->role === UserRole::AdminGudang;
    }

    #[Computed]
    public function totalOrders(): int
    {
        return Order::count();
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function warehouseBreakdown(): array
    {
        $counts = Order::query()
            ->selectRaw('warehouse_status, count(*) as total')
            ->groupBy('warehouse_status')
            ->pluck('total', 'warehouse_status');

        $breakdown = ['belum_diproses' => (int) ($counts[null] ?? 0)];

        foreach (WarehouseStatus::cases() as $case) {
            $breakdown[$case->value] = (int) ($counts[$case->value] ?? 0);
        }

        return $breakdown;
    }

    /**
     * @return array{labels: array<int, string>, data: array<int, float>}
     */
    #[Computed]
    public function salesChartData(): array
    {
        // Grouped in PHP rather than via a DB-specific date-formatting
        // function, so this doesn't depend on which database is active
        // (production runs MySQL, but tests run against SQLite).
        $monthlyTotals = Order::query()
            ->whereNotIn('marketplace_status', ['CANCELLED', 'UNPAID'])
            ->where('order_placed_at', '>=', now()->subMonths(5)->startOfMonth())
            ->get(['order_placed_at', 'total_amount'])
            ->groupBy(fn (Order $order) => $order->order_placed_at->format('Y-m'))
            ->map(fn ($orders) => $orders->sum('total_amount'));

        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->translatedFormat('M Y');
            $data[] = (float) ($monthlyTotals[$month->format('Y-m')] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function reconciliationBreakdown(): array
    {
        return [
            'sesuai' => ReconciliationRecord::where('match_status', ReconciliationStatus::Sesuai)->count(),
            'selisih' => ReconciliationRecord::where('match_status', ReconciliationStatus::Selisih)->count(),
            'belum_dicek' => ReconciliationRecord::where('match_status', ReconciliationStatus::BelumDicek)->count(),
        ];
    }

    #[Computed]
    public function overdueOrdersCount(): int
    {
        return Order::query()
            ->where(function ($q) {
                $q->whereNull('warehouse_status')
                    ->orWhere('warehouse_status', '!=', WarehouseStatus::Dikirim->value);
            })
            ->whereNotIn('marketplace_status', ['CANCELLED', 'IN_CANCEL', 'UNPAID'])
            ->where('order_placed_at', '<', now()->subHours(24))
            ->count();
    }

    #[Computed]
    public function financialMetrics(): array
    {
        $selisihOrderIds = ReconciliationRecord::where('match_status', ReconciliationStatus::Selisih)->pluck('order_id');
        $selisihAmount = Order::whereIn('id', $selisihOrderIds)->sum('total_amount');
        $returnAmount = \App\Models\OrderReturn::sum('refund_amount');

        return [
            'selisih_amount' => (float) $selisihAmount,
            'return_amount' => (float) $returnAmount,
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    #[Computed]
    public function topProducts(): \Illuminate\Support\Collection
    {
        return \App\Models\OrderItem::query()
            ->whereHas('order', function ($q) {
                $q->whereNotIn('marketplace_status', ['CANCELLED', 'UNPAID']);
            })
            ->selectRaw('item_name, sku, sum(quantity) as total_qty, sum(subtotal) as total_sales')
            ->groupBy('item_name', 'sku')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" class="font-display">{{ __('Dashboard Operasional') }}</flux:heading>
            <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                {{ __('Pemantauan komprehensif alur pergudangan, SLA pengiriman, rekonsiliasi Shopee, dan performa produk PT Mustika Makmur Abadi.') }}
            </p>
        </div>
    </div>

    {{-- Overdue Fulfillment SLA Warning Banner --}}
    @if ($this->overdueOrdersCount > 0)
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-amber-300 bg-amber-50 p-4 text-amber-900 shadow-xs dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-200">
            <div class="flex items-center gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-500 text-white font-bold">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div>
                    <h4 class="text-sm font-bold">{{ __('Peringatan SLA Gudang: :count Pesanan Mengendap > 24 Jam', ['count' => $this->overdueOrdersCount]) }}</h4>
                    <p class="text-xs text-amber-800 dark:text-amber-300">
                        {{ __('Pesanan telah masuk lebih dari 24 jam yang lalu namun belum diserahkan ke pihak ekspedisi / kurir. Mohon segera proses untuk menghindari penalti Shopee.') }}
                    </p>
                </div>
            </div>
            <flux:button size="sm" variant="primary" :href="route('admin.warehouse.index')" wire:navigate>
                {{ __('Proses di Gudang Sekarang') }}
            </flux:button>
        </div>
    @endif

    {{-- Orders by Warehouse Status Grid --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
            <p class="font-display text-2xl font-bold text-zinc-900 dark:text-white">{{ $this->totalOrders }}</p>
            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Total Pesanan') }}</p>
        </div>
        @foreach (WarehouseStatus::cases() as $case)
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                <p class="font-display text-2xl font-bold text-brand-600 dark:text-brand-400">{{ $this->warehouseBreakdown[$case->value] }}</p>
                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mt-1">{{ $case->label() }}</p>
            </div>
        @endforeach
    </div>

    @unless ($this->isGudangOnly)
        {{-- Financial Discrepancy & Returns Valuation Grid --}}
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-rose-200 bg-rose-50/60 p-4 dark:border-rose-900/60 dark:bg-rose-950/30">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-rose-700 dark:text-rose-400">{{ __('Dana Tertahan Selisih Rekonsiliasi') }}</p>
                    <span class="rounded bg-rose-200/60 px-2 py-0.5 text-[10px] font-bold text-rose-800 dark:bg-rose-900 dark:text-rose-200">{{ __('Perlu Audit') }}</span>
                </div>
                <p class="mt-2 text-2xl font-bold font-display text-rose-900 dark:text-rose-100">
                    Rp {{ number_format($this->financialMetrics['selisih_amount'], 0, ',', '.') }}
                </p>
                <p class="mt-1 text-xs text-rose-700/80 dark:text-rose-300">
                    {{ __('Nilai pesanan dengan disparitas status antara gudang dan Shopee.') }}
                </p>
            </div>

            <div class="rounded-xl border border-amber-200 bg-amber-50/60 p-4 dark:border-amber-900/60 dark:bg-amber-950/30">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">{{ __('Potensi Refund Komplain Retur') }}</p>
                    <span class="rounded bg-amber-200/60 px-2 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-900 dark:text-amber-200">{{ __('Retur Barang') }}</span>
                </div>
                <p class="mt-2 text-2xl font-bold font-display text-amber-900 dark:text-amber-100">
                    Rp {{ number_format($this->financialMetrics['return_amount'], 0, ',', '.') }}
                </p>
                <p class="mt-1 text-xs text-amber-700/80 dark:text-amber-300">
                    {{ __('Total klaim pengembalian dana pembeli yang terdaftar di Shopee.') }}
                </p>
            </div>
        </div>

        {{-- Sales Chart & Top Products Row --}}
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Sales Chart (2 cols) --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700 dark:bg-zinc-900 lg:col-span-2">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg" class="font-display">{{ __('Tren Penjualan 6 Bulan Terakhir') }}</flux:heading>
                    <span class="text-xs text-zinc-400">{{ __('Exclude pesanan dibatalkan') }}</span>
                </div>
                <div
                    x-data
                    x-init="
                        new ApexCharts($refs.salesChart, {
                            chart: { type: 'area', height: 280, toolbar: { show: false } },
                            series: [{ name: 'Penjualan', data: @js($this->salesChartData['data']) }],
                            xaxis: { categories: @js($this->salesChartData['labels']) },
                            colors: ['#1c7fc4'],
                            dataLabels: { enabled: false },
                            yaxis: { labels: { formatter: (val) => 'Rp ' + Number(val).toLocaleString('id-ID') } },
                            tooltip: { y: { formatter: (val) => 'Rp ' + Number(val).toLocaleString('id-ID') } },
                        }).render()
                    "
                    class="mt-4"
                    wire:ignore
                >
                    <div x-ref="salesChart"></div>
                </div>
            </div>

            {{-- Top 5 Selling Products (1 col) --}}
            <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg" class="font-display">{{ __('Produk Terlaris') }}</flux:heading>
                <p class="text-xs text-zinc-400 mt-0.5">{{ __('Top 5 kuantitas terjual terbanyak') }}</p>

                <div class="mt-4 divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($this->topProducts as $idx => $prod)
                        <div class="py-2.5 first:pt-0 last:pb-0 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-zinc-100 text-xs font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                    {{ $idx + 1 }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-bold text-zinc-900 dark:text-white" title="{{ $prod->item_name }}">
                                        {{ $prod->item_name }}
                                    </p>
                                    <p class="text-[11px] font-mono text-zinc-400">{{ $prod->sku ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-xs font-bold text-brand-600 dark:text-brand-400">{{ $prod->total_qty }} pcs</p>
                                <p class="text-[10px] text-zinc-400">Rp {{ number_format($prod->total_sales, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="py-6 text-center text-xs text-zinc-400">{{ __('Belum ada data produk terjual.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Reconciliation Breakdown Card --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="lg" class="font-display">{{ __('Ringkasan Rekonsiliasi') }}</flux:heading>
                    <p class="text-xs text-zinc-400 mt-0.5">{{ __('Integritas data marketplace Shopee terhadap fisik pengiriman gudang') }}</p>
                </div>
                <flux:button size="sm" :href="route('admin.reconciliation.index')" wire:navigate>
                    {{ __('Buka Modul Rekonsiliasi') }}
                </flux:button>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl bg-emerald-50 p-4 dark:bg-emerald-950/40 border border-emerald-100 dark:border-emerald-900/50">
                    <p class="font-display text-2xl font-bold text-emerald-700 dark:text-emerald-400">{{ $this->reconciliationBreakdown['sesuai'] }}</p>
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-800 dark:text-emerald-300 mt-1">{{ __('Status Sesuai') }}</p>
                </div>
                <div class="rounded-xl bg-rose-50 p-4 dark:bg-rose-950/40 border border-rose-100 dark:border-rose-900/50">
                    <p class="font-display text-2xl font-bold text-rose-700 dark:text-rose-400">{{ $this->reconciliationBreakdown['selisih'] }}</p>
                    <p class="text-xs font-semibold uppercase tracking-wider text-rose-800 dark:text-rose-300 mt-1">{{ __('Status Selisih') }}</p>
                </div>
                <div class="rounded-xl bg-zinc-100 p-4 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700">
                    <p class="font-display text-2xl font-bold text-zinc-700 dark:text-zinc-300">{{ $this->reconciliationBreakdown['belum_dicek'] }}</p>
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Belum Dicek') }}</p>
                </div>
            </div>
        </div>
    @endunless
</div>
