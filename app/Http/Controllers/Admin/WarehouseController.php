<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Enums\WarehouseStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\WarehouseProcessLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(array_column(WarehouseStatus::cases(), 'value'))],
            'search' => ['nullable', 'string', 'max:100'],
            'month' => ['nullable', 'date_format:Y-m'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);
        $orders = Order::query()
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('warehouse_status', $status))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($subQuery) use ($search) {
                $subQuery->where('shopee_order_sn', 'like', "%{$search}%")
                    ->orWhereHas('shipments', fn ($shipmentQuery) => $shipmentQuery->where('tracking_number', 'like', "%{$search}%"));
            }))
            ->when($filters['month'] ?? null, function ($query, $month) {
                [$year, $monthNumber] = explode('-', $month);
                $query->whereYear('order_placed_at', $year)->whereMonth('order_placed_at', $monthNumber);
            })
            ->when(! ($filters['month'] ?? null) && ($filters['date_from'] ?? null), fn ($query) => $query->whereDate('order_placed_at', '>=', $filters['date_from']))
            ->when(! ($filters['month'] ?? null) && ($filters['date_to'] ?? null), fn ($query) => $query->whereDate('order_placed_at', '<=', $filters['date_to']))
            ->with(['items', 'shipments'])
            ->latest('order_placed_at')
            ->paginate(10)
            ->withQueryString();

        return view('pages.admin.warehouse.index', [
            'orders' => $orders,
            'filters' => $filters,
            'statuses' => WarehouseStatus::cases(),
            'canUpdate' => Auth::user()->role === UserRole::AdminGudang,
        ]);
    }

    public function markReceived(Order $order): RedirectResponse
    {
        abort_unless(Auth::user()->role === UserRole::AdminGudang, 403);
        abort_if($order->warehouse_status !== null || in_array($order->marketplace_status, ['CANCELLED', 'IN_CANCEL'], true), 403);

        return $this->recordTransition($order, null, WarehouseStatus::Diterima);
    }

    public function advance(Order $order): RedirectResponse
    {
        abort_unless(Auth::user()->role === UserRole::AdminGudang, 403);
        abort_if(in_array($order->marketplace_status, ['CANCELLED', 'IN_CANCEL'], true), 403);
        $next = $order->warehouse_status?->next();
        abort_if($next === null, 403);

        return $this->recordTransition($order, $order->warehouse_status, $next);
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        abort_unless(Auth::user()->role === UserRole::AdminGudang, 403);
        $validated = $request->validate([
            'target_status' => ['required', Rule::enum(WarehouseStatus::class)],
            'selected_order_ids' => ['required', 'array', 'min:1', 'max:100'],
            'selected_order_ids.*' => ['required', 'integer', Rule::exists('orders', 'id')],
        ]);
        $target = WarehouseStatus::from($validated['target_status']);
        $orders = Order::whereIn('id', $validated['selected_order_ids'])->get();
        $updated = 0;

        foreach ($orders as $order) {
            if (in_array($order->marketplace_status, ['CANCELLED', 'IN_CANCEL'], true)) {
                continue;
            }

            $from = $order->warehouse_status;
            $order->update(['warehouse_status' => $target->value]);
            $this->logTransition($order, $from, $target);
            $updated++;
        }

        return back()->with('status', __(':count pesanan berhasil diperbarui ke status :status.', [
            'count' => $updated,
            'status' => $target->label(),
        ]));
    }

    private function recordTransition(Order $order, ?WarehouseStatus $from, WarehouseStatus $to): RedirectResponse
    {
        $order->update(['warehouse_status' => $to->value]);
        $this->logTransition($order, $from, $to);

        return back()->with('status', __('Status pesanan :sn diperbarui ke :status.', [
            'sn' => $order->shopee_order_sn,
            'status' => $to->label(),
        ]));
    }

    private function logTransition(Order $order, ?WarehouseStatus $from, WarehouseStatus $to): void
    {
        WarehouseProcessLog::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'ip_address' => request()->ip(),
        ]);
    }
}
