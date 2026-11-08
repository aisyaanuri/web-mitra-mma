<?php

namespace App\Models;

use App\Enums\WarehouseStatus;
use Database\Factories\WarehouseProcessLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'user_id', 'from_status', 'to_status', 'note', 'ip_address', 'created_at'])]
class WarehouseProcessLog extends Model
{
    /** @use HasFactory<WarehouseProcessLogFactory> */
    use HasFactory;

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'from_status' => WarehouseStatus::class,
            'to_status' => WarehouseStatus::class,
            'created_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
