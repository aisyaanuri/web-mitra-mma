<?php

namespace App\Models;

use App\Enums\WarehouseStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $shopee_order_sn
 * @property string $shop_id
 * @property string $marketplace_status
 * @property string|null $buyer_username
 * @property string $total_amount
 * @property string $currency
 * @property Carbon|null $order_placed_at
 * @property array<string, mixed>|null $raw_payload
 * @property WarehouseStatus|null $warehouse_status
 * @property Carbon|null $last_synced_at
 */
#[Fillable([
    'shopee_order_sn', 'shop_id', 'marketplace_status', 'buyer_username',
    'total_amount', 'currency', 'order_placed_at', 'raw_payload',
    'warehouse_status', 'last_synced_at',
])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'raw_payload' => 'array',
            'warehouse_status' => WarehouseStatus::class,
            'order_placed_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'total_amount' => 'decimal:2',
        ];
    }

    /** @return HasMany<OrderItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** @return HasMany<Shipment, $this> */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    /** @return HasMany<OrderReturn, $this> */
    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }

    /** @return HasMany<WarehouseProcessLog, $this> */
    public function processLogs(): HasMany
    {
        return $this->hasMany(WarehouseProcessLog::class)->latest('created_at');
    }

    /** @return HasOne<ReconciliationRecord, $this> */
    public function reconciliationRecord(): HasOne
    {
        return $this->hasOne(ReconciliationRecord::class);
    }
}
