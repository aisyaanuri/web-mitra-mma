<?php

namespace App\Services\Shopee;

use DateTimeInterface;
use Illuminate\Support\Str;

/**
 * Generates believable Shopee-shaped fixture data so the sync pipeline,
 * warehouse module, and reconciliation module can be built and demoed
 * before the client's real Shopee Partner/ISV credentials exist.
 */
class FakeShopeeGateway implements ShopeeGatewayInterface
{
    public function getOrderList(DateTimeInterface $since, DateTimeInterface $until): array
    {
        return collect(range(1, fake()->numberBetween(1, 3)))
            ->map(fn () => [
                'order_sn' => $this->fakeOrderSn(),
                'order_status' => fake()->randomElement(['UNPAID', 'READY_TO_SHIP', 'PROCESSED', 'SHIPPED', 'COMPLETED', 'CANCELLED']),
            ])
            ->all();
    }

    public function getOrderDetail(string $orderSn): array
    {
        $itemCount = fake()->numberBetween(1, 3);

        return [
            'order_sn' => $orderSn,
            'order_status' => fake()->randomElement(['UNPAID', 'READY_TO_SHIP', 'PROCESSED', 'SHIPPED', 'COMPLETED']),
            'buyer_username' => fake()->userName(),
            'total_amount' => fake()->randomFloat(2, 50_000, 1_000_000),
            'currency' => 'IDR',
            'create_time' => now()->subMinutes(fake()->numberBetween(1, 120))->getTimestamp(),
            'item_list' => collect(range(1, $itemCount))->map(fn () => [
                'item_id' => fake()->numberBetween(1_000_000, 9_999_999),
                'item_name' => fake()->sentence(3),
                'model_sku' => strtoupper(fake()->bothify('SKU-####-???')),
                'model_quantity_purchased' => fake()->numberBetween(1, 4),
                'model_discounted_price' => fake()->randomFloat(2, 10_000, 200_000),
            ])->all(),
        ];
    }

    public function getTrackingInfo(string $orderSn): array
    {
        return [
            'order_sn' => $orderSn,
            'tracking_number' => strtoupper(fake()->bothify('SPX########ID')),
            'logistics_status' => fake()->randomElement([
                'LOGISTICS_REQUEST_CREATED', 'LOGISTICS_PICKUP_DONE', 'LOGISTICS_DELIVERY_DONE',
            ]),
            'shipping_carrier' => fake()->randomElement(['SPX Express', 'JNE', 'J&T Express']),
        ];
    }

    public function getReturnList(DateTimeInterface $since, DateTimeInterface $until): array
    {
        if (! fake()->boolean(30)) {
            return [];
        }

        return [[
            'return_sn' => strtoupper(fake()->bothify('RT#########')),
            'order_sn' => $this->fakeOrderSn(),
            'reason' => fake()->randomElement(['DAMAGED', 'WRONG_ITEM', 'CHANGE_OF_MIND']),
            'status' => fake()->randomElement(['REQUESTED', 'ACCEPTED', 'REJECTED']),
            'refund_amount' => fake()->randomFloat(2, 10_000, 300_000),
        ]];
    }

    public function refreshToken(string $shopId, string $refreshToken): array
    {
        return [
            'access_token' => 'fake_access_'.Str::random(32),
            'refresh_token' => 'fake_refresh_'.Str::random(32),
            'expire_in' => 14400,
        ];
    }

    private function fakeOrderSn(): string
    {
        return strtoupper(fake()->bothify('##########?'));
    }
}
