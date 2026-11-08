<?php

namespace Database\Factories;

use App\Enums\ProductCategory;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $category = fake()->randomElement(ProductCategory::cases());
        $name = $category === ProductCategory::YummyBites
            ? 'Yummy Bites '.fake()->randomElement(['Puree Buah', 'Biskuit Bayi', 'Snack Wafer', 'Puree Sayur']).' '.fake()->randomElement(['6m+', '1y+'])
            : 'Beras Impor '.fake()->randomElement(['Premium', 'Super', 'Pandan Wangi']).' 5kg';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####-???')),
            'category' => $category,
            'country_of_origin' => $category === ProductCategory::BerasImpor
                ? fake()->randomElement(['Jepang', 'Korea', 'India', 'Vietnam', 'Thailand'])
                : null,
            'description' => fake()->paragraph(),
            'weight' => fake()->randomElement(['100g', '250g', '500g', '5kg']),
            'is_active' => true,
            'is_featured' => fake()->boolean(20),
            'meta_title' => $name,
            'meta_description' => fake()->sentence(),
        ];
    }
}
