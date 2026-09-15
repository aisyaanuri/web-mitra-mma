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
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6">
    <flux:heading size="xl">{{ __('Dashboard') }}</flux:heading>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $this->totalOrders }}</p>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Total Pesanan') }}</p>
        </div>
        @foreach (WarehouseStatus::cases() as $case)
            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $this->warehouseBreakdown[$case->value] }}</p>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $case->label() }}</p>
            </div>
        @endforeach
    </div>

    @unless ($this->isGudangOnly)
        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:heading size="lg">{{ __('Penjualan 6 Bulan Terakhir') }}</flux:heading>
            <div
                x-data
                x-init="
                    new ApexCharts($refs.salesChart, {
                        chart: { type: 'area', height: 300, toolbar: { show: false } },
                        series: [{ name: 'Penjualan', data: @js($this->salesChartData['data']) }],
                        xaxis: { categories: @js($this->salesChartData['labels']) },
                        colors: ['#d97706'],
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

        <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:heading size="lg">{{ __('Ringkasan Rekonsiliasi') }}</flux:heading>
            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                <div class="rounded-lg bg-green-50 p-4 dark:bg-green-950">
                    <p class="text-xl font-bold text-green-700 dark:text-green-400">{{ $this->reconciliationBreakdown['sesuai'] }}</p>
                    <p class="text-sm text-green-700 dark:text-green-400">{{ __('Sesuai') }}</p>
                </div>
                <div class="rounded-lg bg-red-50 p-4 dark:bg-red-950">
                    <p class="text-xl font-bold text-red-700 dark:text-red-400">{{ $this->reconciliationBreakdown['selisih'] }}</p>
                    <p class="text-sm text-red-700 dark:text-red-400">{{ __('Selisih') }}</p>
                </div>
                <div class="rounded-lg bg-zinc-100 p-4 dark:bg-zinc-800">
                    <p class="text-xl font-bold text-zinc-600 dark:text-zinc-300">{{ $this->reconciliationBreakdown['belum_dicek'] }}</p>
                    <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('Belum Dicek') }}</p>
                </div>
            </div>
            <a href="{{ route('admin.reconciliation.index') }}" wire:navigate class="mt-4 inline-block text-sm font-semibold text-amber-600 hover:text-amber-700">
                {{ __('Lihat Laporan Lengkap →') }}
            </a>
        </div>
    @endunless
</div>
