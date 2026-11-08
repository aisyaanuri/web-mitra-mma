<?php

namespace App\Http\Controllers;

use App\Enums\ContactMessageStatus;
use App\Enums\ReconciliationStatus;
use App\Enums\UserRole;
use App\Enums\WarehouseStatus;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\ReconciliationRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $counts = Order::query()
            ->selectRaw('warehouse_status, count(*) as total')
            ->groupBy('warehouse_status')
            ->pluck('total', 'warehouse_status');
        $warehouseBreakdown = ['belum_diproses' => (int) ($counts[null] ?? 0)];
        foreach (WarehouseStatus::cases() as $case) {
            $warehouseBreakdown[$case->value] = (int) ($counts[$case->value] ?? 0);
        }

        $monthlyTotals = Order::query()
            ->whereNotIn('marketplace_status', ['CANCELLED', 'UNPAID'])
            ->where('order_placed_at', '>=', now()->subMonths(5)->startOfMonth())
            ->get(['order_placed_at', 'total_amount'])
            ->groupBy(fn (Order $order) => $order->order_placed_at->format('Y-m'))
            ->map(fn ($orders) => $orders->sum('total_amount'));
        $labels = [];
        $sales = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->translatedFormat('M Y');
            $sales[] = (float) ($monthlyTotals[$month->format('Y-m')] ?? 0);
        }

        $selisihOrderIds = ReconciliationRecord::where('match_status', ReconciliationStatus::Selisih)->pluck('order_id');

        return view('pages.dashboard', [
            'isGudangOnly' => Auth::user()->role === UserRole::AdminGudang,
            'totalOrders' => Order::count(),
            'warehouseBreakdown' => $warehouseBreakdown,
            'salesChartData' => ['labels' => $labels, 'data' => $sales],
            'reconciliationBreakdown' => [
                'sesuai' => ReconciliationRecord::where('match_status', ReconciliationStatus::Sesuai)->count(),
                'selisih' => ReconciliationRecord::where('match_status', ReconciliationStatus::Selisih)->count(),
                'belum_dicek' => ReconciliationRecord::where('match_status', ReconciliationStatus::BelumDicek)->count(),
            ],
            'overdueOrdersCount' => Order::query()
                ->where(fn ($query) => $query->whereNull('warehouse_status')->orWhere('warehouse_status', '!=', WarehouseStatus::Dikirim->value))
                ->whereNotIn('marketplace_status', ['CANCELLED', 'IN_CANCEL', 'UNPAID'])
                ->where('order_placed_at', '<', now()->subHours(24))
                ->count(),
            'unreadContactMessagesCount' => ContactMessage::where('status', ContactMessageStatus::New)->count(),
            'financialMetrics' => [
                'selisih_amount' => (float) Order::whereIn('id', $selisihOrderIds)->sum('total_amount'),
                'return_amount' => (float) OrderReturn::sum('refund_amount'),
            ],
            'topProducts' => OrderItem::query()
                ->whereHas('order', fn ($query) => $query->whereNotIn('marketplace_status', ['CANCELLED', 'UNPAID']))
                ->selectRaw('item_name, sku, sum(quantity) as total_qty, sum(subtotal) as total_sales')
                ->groupBy('item_name', 'sku')
                ->orderByDesc('total_qty')
                ->limit(5)
                ->get(),
        ]);
    }
}
