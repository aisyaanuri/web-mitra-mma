<?php

namespace Database\Factories;

use App\Models\ShopeeToken;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ShopeeToken>
 */
class ShopeeTokenFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shop_id' => (string) fake()->unique()->numberBetween(100000, 999999),
            'access_token' => Str::random(40),
            'refresh_token' => Str::random(40),
            'access_expires_at' => now()->addHours(4),
            'refresh_expires_at' => now()->addDays(30),
            'last_refreshed_at' => now(),
        ];
    }
}
