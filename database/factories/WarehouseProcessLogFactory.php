<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\WarehouseProcessLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WarehouseProcessLog>
 */
class WarehouseProcessLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => User::factory(),
            'from_status' => null,
            'to_status' => 'diterima',
            'note' => null,
            'ip_address' => fake()->ipv4(),
            'created_at' => now(),
        ];
    }
}
