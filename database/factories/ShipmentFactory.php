<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shipment>
 */
class ShipmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'tracking_number' => strtoupper(fake()->bothify('SPX########ID')),
            'shopee_logistics_status' => fake()->randomElement([
                'ORDER_INIT', 'PICKUP_DONE', 'IN_TRANSIT', 'DELIVERED',
            ]),
            'carrier' => fake()->randomElement(['SPX Express', 'JNE', 'J&T Express']),
            'shipped_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'delivered_at' => null,
            'raw_payload' => [],
        ];
    }
}
