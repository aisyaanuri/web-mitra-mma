<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderReturn;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderReturn>
 */
class OrderReturnFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'shopee_return_sn' => strtoupper(fake()->unique()->bothify('RT#########')),
            'reason' => fake()->randomElement(['Barang rusak', 'Tidak sesuai deskripsi', 'Berubah pikiran']),
            'status' => fake()->randomElement(['REQUESTED', 'ACCEPTED', 'REJECTED', 'COMPLETED']),
            'refund_amount' => fake()->randomFloat(2, 10_000, 500_000),
            'raw_payload' => [],
        ];
    }
}
