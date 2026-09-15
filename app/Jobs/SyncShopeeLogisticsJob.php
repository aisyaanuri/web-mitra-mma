<?php

namespace App\Jobs;

use App\Enums\SyncStatus;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\SyncLog;
use App\Services\Shopee\ShopeeGatewayInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Validator;
use Throwable;

class SyncShopeeLogisticsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(ShopeeGatewayInterface $gateway): void
    {
        try {
            $orders = Order::query()
                ->whereIn('marketplace_status', ['READY_TO_SHIP', 'PROCESSED', 'SHIPPED', 'COMPLETED'])
                ->get();

            $synced = 0;
            foreach ($orders as $order) {
                $this->syncTracking($gateway, $order);
                $synced++;
            }

            SyncLog::create([
                'job_type' => 'shopee_logistics',
                'status' => SyncStatus::Success,
                'message' => __('Status pengiriman :count pesanan diperbarui.', ['count' => $synced]),
                'payload' => ['synced' => $synced],
            ]);
        } catch (Throwable $e) {
            SyncLog::create([
                'job_type' => 'shopee_logistics',
                'status' => SyncStatus::Failed,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function syncTracking(ShopeeGatewayInterface $gateway, Order $order): void
    {
        $tracking = $gateway->getTrackingInfo($order->shopee_order_sn);

        $validated = Validator::make($tracking, [
            'tracking_number' => ['nullable', 'string'],
            'logistics_status' => ['required', 'string'],
            'shipping_carrier' => ['nullable', 'string'],
        ])->validate();

        Shipment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'tracking_number' => $validated['tracking_number'] ?? null,
                'shopee_logistics_status' => $validated['logistics_status'],
                'carrier' => $validated['shipping_carrier'] ?? null,
                'raw_payload' => $tracking,
            ]
        );
    }
}
