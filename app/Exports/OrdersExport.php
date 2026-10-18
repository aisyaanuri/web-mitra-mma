<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private readonly ?string $status = null,
        private readonly ?string $search = null,
        private readonly ?string $dateFrom = null,
        private readonly ?string $dateTo = null,
        private readonly ?string $month = null,
    ) {}

    public function query(): Builder
    {
        return Order::query()
            ->when($this->status, fn ($q) => $q->where('warehouse_status', $this->status))
            ->when($this->search, function ($q) {
                $search = $this->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('shopee_order_sn', 'like', "%{$search}%")
                        ->orWhereHas('shipments', fn ($sq) => $sq->where('tracking_number', 'like', "%{$search}%"));
                });
            })
            ->when($this->month, function ($q) {
                $parts = explode('-', $this->month);
                if (count($parts) === 2) {
                    $q->whereYear('order_placed_at', $parts[0])
                        ->whereMonth('order_placed_at', $parts[1]);
                }
            })
            ->when($this->dateFrom, fn ($q) => $q->whereDate('order_placed_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('order_placed_at', '<=', $this->dateTo))
            ->with('shipments')
            ->orderByDesc('order_placed_at');
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Order SN', 'Resi', 'Status Marketplace', 'Status Gudang', 'Buyer', 'Total', 'Tanggal Pesan'];
    }

    /**
     * @param  Order  $order
     * @return array<int, mixed>
     */
    public function map($order): array
    {
        $shipment = $order->shipments->first();

        return [
            $order->shopee_order_sn,
            $shipment === null ? '' : ($shipment->tracking_number ?? ''),
            $order->marketplace_status,
            $order->warehouse_status?->label() ?? 'Belum Diproses',
            $order->buyer_username,
            $order->total_amount,
            $order->order_placed_at?->format('Y-m-d H:i'),
        ];
    }
}
