<?php

namespace App\Models;

use App\Enums\ReconciliationStatus;
use App\Enums\WarehouseStatus;
use Database\Factories\ReconciliationRecordFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id', 'marketplace_status_snapshot', 'warehouse_status_snapshot',
    'match_status', 'checked_at', 'checked_by', 'investigation_note', 'period',
])]
class ReconciliationRecord extends Model
{
    /** @use HasFactory<ReconciliationRecordFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'warehouse_status_snapshot' => WarehouseStatus::class,
            'match_status' => ReconciliationStatus::class,
            'checked_at' => 'datetime',
            'period' => 'date',
        ];
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<User, $this> */
    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
