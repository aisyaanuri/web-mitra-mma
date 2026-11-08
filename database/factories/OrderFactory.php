<?php

namespace Database\Factories;

use App\Enums\WarehouseStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $placedAt = fake()->dateTimeBetween('-2 months', 'now');
        $marketplaceStatus = fake()->randomElement([
            'UNPAID', 'READY_TO_SHIP', 'PROCESSED', 'SHIPPED', 'COMPLETED', 'CANCELLED',
        ]);

        return [
            'shopee_order_sn' => strtoupper(fake()->unique()->bothify('##########?')),
            'shop_id' => '123456',
            'marketplace_status' => $marketplaceStatus,
            'buyer_username' => fake()->userName(),
            'total_amount' => fake()->randomFloat(2, 50_000, 2_000_000),
            'currency' => 'IDR',
            'order_placed_at' => $placedAt,
            'raw_payload' => [
                'order_sn' => null,
                'order_status' => $marketplaceStatus,
                'create_time' => $placedAt->getTimestamp(),
            ],
            'warehouse_status' => fake()->randomElement(WarehouseStatus::cases()),
            'last_synced_at' => now(),
        ];
    }
}
