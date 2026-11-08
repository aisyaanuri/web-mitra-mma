<?php

namespace Database\Factories;

use App\Models\CompanyContent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanyContent>
 */
class CompanyContentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'section_key' => fake()->unique()->slug(2),
            'title' => fake()->sentence(4),
            'body' => fake()->paragraphs(3, true),
            'image_path' => null,
            'meta' => [],
            'is_published' => true,
            'sort_order' => 0,
        ];
    }
}
