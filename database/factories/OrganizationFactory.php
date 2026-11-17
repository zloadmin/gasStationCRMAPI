<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->unique()->numberBetween(1, 1_000_000),
            'name' => 'ООО '.fake()->lastName(),
            'description' => fake()->city(),
            'type' => 'default',
            'email' => fake()->optional()->companyEmail(),
            'active' => true,
        ];
    }
}
