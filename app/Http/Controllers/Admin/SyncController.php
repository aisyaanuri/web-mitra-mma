<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SyncStatus;
use App\Http\Controllers\Controller;
use App\Jobs\SyncShopeeLogisticsJob;
use App\Jobs\SyncShopeeOrdersJob;
use App\Jobs\SyncShopeeReturnsJob;
use App\Models\Order;
use App\Models\SyncLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SyncController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'job_type' => ['nullable', 'string', 'max:100'],
            'log_status' => ['nullable', Rule::in(array_column(SyncStatus::cases(), 'value'))],
            'order_search' => ['nullable', 'string', 'max:100'],
            'order_status' => ['nullable', 'string', 'max:50'],
        ]);
        $lastSuccess = SyncLog::where('status', SyncStatus::Success)->latest('id')->first();

        return view('pages.admin.sync.index', [
            'filters' => $filters,
            'mode' => config('shopee.mode', 'fake'),
            'metrics' => [
                'total_orders' => Order::count(),
                'pending_warehouse' => Order::whereNull('warehouse_status')->count(),
                'total_logs' => SyncLog::count(),
                'last_sync_time' => $lastSuccess?->created_at?->diffForHumans() ?? 'Belum pernah',
                'last_sync_at' => $lastSuccess?->created_at?->translatedFormat('d M Y, H:i'),
            ],
            'logs' => SyncLog::query()
                ->when($filters['job_type'] ?? null, fn ($query, $type) => $query->where('job_type', $type))
                ->when($filters['log_status'] ?? null, fn ($query, $status) => $query->where('status', $status))
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
            'orders' => Order::query()
                ->when($filters['order_search'] ?? null, fn ($query, $search) => $query->where(fn ($subQuery) => $subQuery
                    ->where('shopee_order_sn', 'like', "%{$search}%")
                    ->orWhere('buyer_username', 'like', "%{$search}%")))
                ->when($filters['order_status'] ?? null, fn ($query, $status) => $query->where('marketplace_status', $status))
                ->with(['items', 'shipments'])
                ->latest('order_placed_at')
                ->paginate(15, ['*'], 'orderPage')
                ->withQueryString(),
            'statuses' => SyncStatus::cases(),
            'jobTypes' => SyncLog::query()->distinct()->orderBy('job_type')->pluck('job_type'),
        ]);
    }

    public function run(): RedirectResponse
    {
        SyncShopeeOrdersJob::dispatchSync();
        SyncShopeeLogisticsJob::dispatchSync();
        SyncShopeeReturnsJob::dispatchSync();

        return back()->with('status', __('Sinkronisasi Shopee selesai dijalankan. Detail data telah dicatat.'));
    }
}
