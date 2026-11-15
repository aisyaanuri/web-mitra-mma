<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReconciliationStatus;
use App\Enums\WarehouseStatus;
use App\Http\Controllers\Controller;
use App\Jobs\RunReconciliationJob;
use App\Models\Order;
use App\Models\ReconciliationRecord;
use App\Models\WarehouseProcessLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReconciliationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'period' => ['nullable', 'date_format:Y-m'],
            'match_status' => ['nullable', Rule::in(array_column(ReconciliationStatus::cases(), 'value'))],
            'marketplace_status' => ['nullable', 'string', 'max:50'],
        ]);
        $query = $this->baseQuery($filters);
        $summaryQuery = $this->baseQuery($filters);
        $total = (clone $summaryQuery)->count();
        $matched = (clone $summaryQuery)->where('match_status', ReconciliationStatus::Sesuai->value)->count();
        $records = $query
            ->when($filters['match_status'] ?? null, fn ($recordQuery, $status) => $recordQuery->where('match_status', $status))
            ->with(['order.items', 'order.shipments', 'checkedBy'])
            ->orderByDesc('period')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('pages.admin.reconciliation.index', [
            'filters' => $filters,
            'records' => $records,
            'statuses' => ReconciliationStatus::cases(),
            'marketplaceStatuses' => Order::query()->whereNotNull('marketplace_status')->distinct()->orderBy('marketplace_status')->pluck('marketplace_status'),
            'summary' => [
                'total' => $total,
                'sesuai' => (clone $summaryQuery)->where('match_status', ReconciliationStatus::Sesuai->value)->count(),
                'selisih' => (clone $summaryQuery)->where('match_status', ReconciliationStatus::Selisih->value)->count(),
                'belum_dicek' => (clone $summaryQuery)->where('match_status', ReconciliationStatus::BelumDicek->value)->count(),
                'accuracy' => $total > 0 ? round(($matched / $total) * 100, 1) : 0,
                'selisih_amount' => Order::whereHas('reconciliationRecord', fn ($query) => $query->where('match_status', ReconciliationStatus::Selisih->value))->sum('total_amount'),
            ],
        ]);
    }

    public function run(): RedirectResponse
    {
        RunReconciliationJob::dispatchSync();

        return back()->with('status', __('Rekonsiliasi selesai dijalankan untuk seluruh pesanan.'));
    }

    public function saveNote(Request $request, ReconciliationRecord $record): RedirectResponse
    {
        $validated = $request->validate(['investigation_note' => ['nullable', 'string', 'max:2000']]);
        $record->update([
            'investigation_note' => $validated['investigation_note'] ?? null,
            'checked_by' => Auth::id(),
            'checked_at' => now(),
        ]);

        return back()->with('status', __('Catatan investigasi berhasil disimpan.'));
    }

    public function resolveWarehouseStatus(Request $request, ReconciliationRecord $record): RedirectResponse
    {
        $validated = $request->validate(['warehouse_status' => ['required', Rule::enum(WarehouseStatus::class)]]);
        $order = $record->order;
        abort_unless($order instanceof Order, 404);
        $target = WarehouseStatus::from($validated['warehouse_status']);
        $previous = $order->warehouse_status;
        $order->update(['warehouse_status' => $target->value]);

        WarehouseProcessLog::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'from_status' => $previous?->value,
            'to_status' => $target->value,
            'note' => 'Penyelarasan status melalui modul Rekonsiliasi oleh '.Auth::user()->name,
            'ip_address' => $request->ip(),
        ]);

        $shipped = $target === WarehouseStatus::Dikirim;
        $match = ReconciliationStatus::BelumDicek;
        if ($order->marketplace_status === 'CANCELLED') {
            $match = $shipped ? ReconciliationStatus::Selisih : ReconciliationStatus::Sesuai;
        } elseif (in_array($order->marketplace_status, ['COMPLETED', 'SHIPPED'], true)) {
            $match = $shipped ? ReconciliationStatus::Sesuai : ReconciliationStatus::Selisih;
        }

        $record->update([
            'warehouse_status_snapshot' => $target->value,
            'match_status' => $match->value,
            'checked_by' => Auth::id(),
            'checked_at' => now(),
        ]);

        return back()->with('status', __('Status gudang berhasil diperbarui dan status rekonsiliasi telah disesuaikan.'));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<ReconciliationRecord>
     */
    private function baseQuery(array $filters): Builder
    {
        return ReconciliationRecord::query()
            ->when($filters['period'] ?? null, function (Builder $query, mixed $period): void {
                if (is_string($period) && str_contains($period, '-')) {
                    [$year, $month] = explode('-', $period);
                    $query->whereYear('period', $year)->whereMonth('period', $month);
                }
            })
            ->when($filters['search'] ?? null, fn (Builder $query, mixed $search) => $query->whereHas('order', fn ($orderQuery) => $orderQuery
                ->where('shopee_order_sn', 'like', "%{$search}%")
                ->orWhere('buyer_username', 'like', "%{$search}%")))
            ->when($filters['marketplace_status'] ?? null, fn (Builder $query, mixed $status) => $query->where('marketplace_status_snapshot', $status));
    }
}
