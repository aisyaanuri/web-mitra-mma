<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SyncShopeeReturnsJob;
use App\Models\Order;
use App\Services\Shopee\ShopeeGatewayInterface;
use DateTimeInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncShopeeReturnsJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_return_for_known_order(): void
    {
        $order = Order::factory()->create(['shopee_order_sn' => 'ORDER-WITH-RETURN']);

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
                return [];
            }

            public function getReturnList(DateTimeInterface $since, DateTimeInterface $until): array
            {
                return [[
                    'return_sn' => 'RETURN-001',
                    'order_sn' => 'ORDER-WITH-RETURN',
                    'reason' => 'DAMAGED',
                    'status' => 'REQUESTED',
                    'refund_amount' => 25000,
                ]];
            }

            public function refreshToken(string $shopId, string $refreshToken): array
            {
                return [];
            }
        });

        $this->app->make(SyncShopeeReturnsJob::class)->handle($this->app->make(ShopeeGatewayInterface::class));

        $this->assertDatabaseHas('order_returns', [
            'order_id' => $order->id,
            'shopee_return_sn' => 'RETURN-001',
            'reason' => 'DAMAGED',
        ]);
    }

    public function test_skips_return_for_unknown_order(): void
    {
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
                return [];
            }

            public function getReturnList(DateTimeInterface $since, DateTimeInterface $until): array
            {
                return [[
                    'return_sn' => 'RETURN-ORPHAN',
                    'order_sn' => 'ORDER-DOES-NOT-EXIST',
                    'reason' => 'DAMAGED',
                    'status' => 'REQUESTED',
                    'refund_amount' => 25000,
                ]];
            }

            public function refreshToken(string $shopId, string $refreshToken): array
            {
                return [];
            }
        });

        $this->app->make(SyncShopeeReturnsJob::class)->handle($this->app->make(ShopeeGatewayInterface::class));

        $this->assertDatabaseMissing('order_returns', ['shopee_return_sn' => 'RETURN-ORPHAN']);
    }
}
