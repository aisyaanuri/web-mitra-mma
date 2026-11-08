<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 5);
        $unitPrice = fake()->randomFloat(2, 10_000, 200_000);

        return [
            'order_id' => Order::factory(),
            'product_id' => null,
            'shopee_item_id' => (string) fake()->unique()->numberBetween(1000000, 9999999),
            'item_name' => fake()->words(3, true),
            'sku' => strtoupper(fake()->bothify('SKU-####-???')),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $quantity * $unitPrice,
        ];
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'item_name' => $product->name,
            'sku' => $product->sku,
        ]);
    }
}
