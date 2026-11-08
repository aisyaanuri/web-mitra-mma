<?php

namespace App\Exports;

use App\Models\ReconciliationRecord;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReconciliationExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private readonly ?string $period = null,
        private readonly ?string $matchStatus = null,
        private readonly ?string $search = null,
    ) {}

    public function query(): Builder
    {
        return ReconciliationRecord::query()
            ->when($this->period, function ($q) {
                $q->whereYear('period', substr($this->period, 0, 4))
                    ->whereMonth('period', substr($this->period, 5, 2));
            })
            ->when($this->matchStatus, fn ($q) => $q->where('match_status', $this->matchStatus))
            ->when($this->search, function ($q) {
                $q->whereHas('order', function ($sub) {
                    $sub->where('shopee_order_sn', 'like', '%' . $this->search . '%')
                        ->orWhere('buyer_username', 'like', '%' . $this->search . '%');
                });
            })
            ->with('order')
            ->orderByDesc('period');
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Order SN', 'Status Marketplace', 'Status Gudang', 'Hasil', 'Periode', 'Terakhir Dicek', 'Catatan Investigasi'];
    }

    /**
     * @return array<int, mixed>
     */
    public function map($record): array
    {
        return [
            $record->order?->shopee_order_sn,
            $record->marketplace_status_snapshot,
            $record->warehouse_status_snapshot?->label() ?? 'Belum Diproses',
            $record->match_status->label(),
            $record->period?->format('F Y'),
            $record->checked_at?->format('Y-m-d H:i'),
            $record->investigation_note,
        ];
    }
}
