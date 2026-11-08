<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderReturn;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReturnController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'shopee_status' => ['nullable', 'string', 'max:50'],
            'physical_status' => ['nullable', Rule::in(['menunggu_barang', 'diterima_baik', 'diterima_rusak', 'barang_hilang'])],
        ]);
        $returns = OrderReturn::query()
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($subQuery) use ($search) {
                $subQuery->where('shopee_return_sn', 'like', "%{$search}%")
                    ->orWhereHas('order', fn ($orderQuery) => $orderQuery
                        ->where('shopee_order_sn', 'like', "%{$search}%")
                        ->orWhere('buyer_username', 'like', "%{$search}%"));
            }))
            ->when($filters['shopee_status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['physical_status'] ?? null, fn ($query, $status) => $query->where('physical_status', $status))
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('pages.admin.returns.index', [
            'returns' => $returns,
            'filters' => $filters,
            'physicalStatuses' => [
                'menunggu_barang' => __('Menunggu Barang'),
                'diterima_baik' => __('Diterima Baik'),
                'diterima_rusak' => __('Diterima Rusak'),
                'barang_hilang' => __('Barang Hilang'),
            ],
            'metrics' => [
                'total_returns' => OrderReturn::count(),
                'waiting_physical' => OrderReturn::where('physical_status', 'menunggu_barang')->count(),
                'received_count' => OrderReturn::whereIn('physical_status', ['diterima_baik', 'diterima_rusak'])->count(),
                'total_refund' => OrderReturn::sum('refund_amount'),
            ],
        ]);
    }

    public function verify(Request $request, OrderReturn $orderReturn): RedirectResponse
    {
        $validated = $request->validate([
            'physical_status' => ['required', Rule::in(['menunggu_barang', 'diterima_baik', 'diterima_rusak', 'barang_hilang'])],
            'physical_note' => ['nullable', 'string', 'max:2000'],
        ]);
        $orderReturn->update([
            'physical_status' => $validated['physical_status'],
            'physical_note' => $validated['physical_note'] ?? null,
            'verified_by' => Auth::id(),
            'received_at' => now(),
        ]);

        return back()->with('status', __('Verifikasi fisik barang retur :sn berhasil disimpan.', [
            'sn' => $orderReturn->shopee_return_sn,
        ]));
    }
}
