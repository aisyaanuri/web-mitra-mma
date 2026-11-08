<?php

namespace Database\Factories;

use App\Enums\ReconciliationStatus;
use App\Models\Order;
use App\Models\ReconciliationRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReconciliationRecord>
 */
class ReconciliationRecordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'marketplace_status_snapshot' => 'COMPLETED',
            'warehouse_status_snapshot' => 'dikirim',
            'match_status' => ReconciliationStatus::BelumDicek,
            'checked_at' => null,
            'checked_by' => null,
            'investigation_note' => null,
            'period' => now()->startOfMonth(),
        ];
    }
}
