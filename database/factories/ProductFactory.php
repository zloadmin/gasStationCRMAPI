<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $grade = fake()->randomElement(['92', '95', '98']);

        return [
            'id' => fake()->unique()->numberBetween(1, 1_000_000),
            'org_id' => fake()->numberBetween(1, 100),
            'name' => "АИ-{$grade}",
            'description' => null,
            'price' => fake()->randomFloat(2, 50, 90),
            'currency' => 643,
            'good_name' => "АИ-{$grade}-К5",
            'good_tax' => 0,
            'good_type' => 'excise',
            'archived' => false,
            'data' => null,
            'yandex_type' => "a{$grade}",
        ];
    }
}
