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
            $trackingList = [];
            foreach ($orders as $order) {
                $shipment = $this->syncTracking($gateway, $order);
                $synced++;
                $trackingList[] = [
                    'order_sn' => $order->shopee_order_sn,
                    'tracking_number' => $shipment->tracking_number ?? 'Belum terbit',
                    'carrier' => $shipment->carrier ?? 'Kurir Shopee',
                    'logistics_status' => $shipment->shopee_logistics_status,
                ];
            }

            SyncLog::create([
                'job_type' => 'shopee_logistics',
                'status' => SyncStatus::Success,
                'message' => __('Status pengiriman :count pesanan diperbarui.', ['count' => $synced]),
                'payload' => [
                    'synced' => $synced,
                    'tracking' => $trackingList,
                ],
            ]);
        } catch (Throwable $e) {
            SyncLog::create([
                'job_type' => 'shopee_logistics',
                'status' => SyncStatus::Failed,
                'message' => $e->getMessage(),
                'payload' => [
                    'error' => $e->getMessage(),
                ],
            ]);

            throw $e;
        }
    }

    private function syncTracking(ShopeeGatewayInterface $gateway, Order $order): Shipment
    {
        $tracking = $gateway->getTrackingInfo($order->shopee_order_sn);

        $validated = Validator::make($tracking, [
            'tracking_number' => ['nullable', 'string'],
            'logistics_status' => ['required', 'string'],
            'shipping_carrier' => ['nullable', 'string'],
        ])->validate();

        return Shipment::updateOrCreate(
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
