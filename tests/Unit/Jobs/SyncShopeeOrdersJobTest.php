<?php

namespace Tests\Unit\Jobs;

use App\Enums\SyncStatus;
use App\Jobs\SyncShopeeOrdersJob;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\Shopee\ShopeeGatewayInterface;
use DateTimeInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class SyncShopeeOrdersJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_syncs_orders_and_items_from_the_gateway(): void
    {
        $product = Product::factory()->create(['sku' => 'MATCHED-SKU']);

        $this->app->bind(ShopeeGatewayInterface::class, fn () => new class($product) implements ShopeeGatewayInterface
        {
            public function __construct(private Product $product) {}

            public function getOrderList(DateTimeInterface $since, DateTimeInterface $until): array
            {
                return [['order_sn' => 'SYNC-TEST-001', 'order_status' => 'READY_TO_SHIP']];
            }

            public function getOrderDetail(string $orderSn): array
            {
                return [
                    'order_sn' => $orderSn,
                    'order_status' => 'READY_TO_SHIP',
                    'buyer_username' => 'buyer1',
                    'total_amount' => 100000,
                    'currency' => 'IDR',
                    'create_time' => now()->getTimestamp(),
                    'item_list' => [[
                        'item_id' => 555,
                        'item_name' => 'Test Item',
                        'model_sku' => $this->product->sku,
                        'model_quantity_purchased' => 2,
                        'model_discounted_price' => 50000,
                    ]],
                ];
            }

            public function getTrackingInfo(string $orderSn): array
            {
                return [];
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

        $this->app->make(SyncShopeeOrdersJob::class)->handle($this->app->make(ShopeeGatewayInterface::class));

        $order = Order::where('shopee_order_sn', 'SYNC-TEST-001')->first();
        $this->assertNotNull($order);
        $this->assertSame('READY_TO_SHIP', $order->marketplace_status);
        $this->assertSame('buyer1', $order->buyer_username);

        $item = OrderItem::where('order_id', $order->id)->first();
        $this->assertNotNull($item);
        $this->assertSame($product->id, $item->product_id);
        $this->assertSame(2, $item->quantity);
        $this->assertEquals(100000, $item->subtotal);

        $this->assertDatabaseHas('sync_logs', [
            'job_type' => 'shopee_orders',
            'status' => SyncStatus::Success->value,
        ]);
    }

    public function test_logs_failure_and_rethrows_when_gateway_fails(): void
    {
        $this->app->bind(ShopeeGatewayInterface::class, fn () => new class implements ShopeeGatewayInterface
        {
            public function getOrderList(DateTimeInterface $since, DateTimeInterface $until): array
            {
                throw new RuntimeException('Shopee API unreachable');
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
                return [];
            }

            public function refreshToken(string $shopId, string $refreshToken): array
            {
                return [];
            }
        });

        $this->expectException(RuntimeException::class);

        try {
            $this->app->make(SyncShopeeOrdersJob::class)->handle($this->app->make(ShopeeGatewayInterface::class));
        } finally {
            $this->assertDatabaseHas('sync_logs', [
                'job_type' => 'shopee_orders',
                'status' => SyncStatus::Failed->value,
                'message' => 'Shopee API unreachable',
            ]);
        }
    }

    public function test_rejects_malformed_order_data(): void
    {
        $this->app->bind(ShopeeGatewayInterface::class, fn () => new class implements ShopeeGatewayInterface
        {
            public function getOrderList(DateTimeInterface $since, DateTimeInterface $until): array
            {
                return [['order_sn' => 'BAD-ORDER', 'order_status' => 'READY_TO_SHIP']];
            }

            public function getOrderDetail(string $orderSn): array
            {
                // Missing required fields like total_amount/currency/create_time.
                return ['order_sn' => $orderSn, 'order_status' => 'READY_TO_SHIP'];
            }

            public function getTrackingInfo(string $orderSn): array
            {
                return [];
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

        try {
            $this->app->make(SyncShopeeOrdersJob::class)->handle($this->app->make(ShopeeGatewayInterface::class));
        } catch (ValidationException $e) {
            // expected
        }

        $this->assertDatabaseMissing('orders', ['shopee_order_sn' => 'BAD-ORDER']);
    }
}
