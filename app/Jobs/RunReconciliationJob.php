<?php

namespace App\Jobs;

use App\Enums\ReconciliationStatus;
use App\Enums\WarehouseStatus;
use App\Models\Order;
use App\Models\ReconciliationRecord;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Matches marketplace order data against warehouse processing data by
 * order SN and records (or updates) a reconciliation snapshot for each
 * order. Safe to re-run: existing manual investigation notes are never
 * touched, only the automated match fields.
 */
class RunReconciliationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Shopee order statuses considered "finished" from the marketplace side.
     */
    private const TERMINAL_MARKETPLACE_STATUSES = ['COMPLETED', 'SHIPPED'];

    public function handle(): void
    {
        Order::query()->chunkById(100, function ($orders) {
            foreach ($orders as $order) {
                $this->reconcileOrder($order);
            }
        });
    }

    private function reconcileOrder(Order $order): void
    {
        ReconciliationRecord::updateOrCreate(
            ['order_id' => $order->id],
            [
                'marketplace_status_snapshot' => $order->marketplace_status,
                'warehouse_status_snapshot' => $order->warehouse_status?->value,
                'match_status' => $this->determineMatchStatus($order)->value,
                'checked_at' => now(),
                'period' => ($order->order_placed_at ?? now())->copy()->startOfMonth(),
            ]
        );
    }

    private function determineMatchStatus(Order $order): ReconciliationStatus
    {
        $shipped = $order->warehouse_status === WarehouseStatus::Dikirim;

        if ($order->marketplace_status === 'CANCELLED') {
            // Goods should never have left the warehouse for a cancelled order.
            return $shipped ? ReconciliationStatus::Selisih : ReconciliationStatus::Sesuai;
        }

        if (in_array($order->marketplace_status, self::TERMINAL_MARKETPLACE_STATUSES, true)) {
            // Marketplace says done — warehouse must agree it shipped.
            return $shipped ? ReconciliationStatus::Sesuai : ReconciliationStatus::Selisih;
        }

        // Still in progress on the marketplace side — nothing conclusive to check yet.
        return ReconciliationStatus::BelumDicek;
    }
}
