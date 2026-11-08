<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SyncShopeeLogisticsJob;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\Shopee\ShopeeGatewayInterface;
use DateTimeInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncShopeeLogisticsJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_updates_tracking_info_for_eligible_orders(): void
    {
        $order = Order::factory()->create(['marketplace_status' => 'SHIPPED']);
        Order::factory()->create(['marketplace_status' => 'UNPAID']); // not eligible yet

        $this->app->bind(ShopeeGatewayInterface::class, fn () => new class implements ShopeeGatewayInterface
        {
            public function getOrderList(DateTimeInterface $since, DateTimeInterface $until): array
            {
                return [];
            }

            public function getOrderDetail(string $orderSn): array
            {
                return [];
            }

            public function getTrackingInfo(string $orderSn): array
            {
                return [
                    'order_sn' => $orderSn,
                    'tracking_number' => 'RESI123456',
                    'logistics_status' => 'LOGISTICS_PICKUP_DONE',
                    'shipping_carrier' => 'SPX Express',
                ];
            }

            public function getReturnList(DateTimeInterface $since, DateTimeInterface $until): array
            {
                return [];
            }

            public function refreshToken(string $shopId, string $refreshToken): array
            {
                return [];
            }
        });

        $this->app->make(SyncShopeeLogisticsJob::class)->handle($this->app->make(ShopeeGatewayInterface::class));

        $this->assertSame(1, Shipment::where('order_id', $order->id)->count());
        $shipment = Shipment::where('order_id', $order->id)->first();
        $this->assertSame('RESI123456', $shipment->tracking_number);
        $this->assertSame('LOGISTICS_PICKUP_DONE', $shipment->shopee_logistics_status);
    }
}
