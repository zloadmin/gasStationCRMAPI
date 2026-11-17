<?php

namespace Database\Factories;

use App\Models\Terminal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Terminal>
 */
class TerminalFactory extends Factory
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
            'org_id' => fake()->numberBetween(1, 100),
            'name' => 'Терм-'.fake()->numberBetween(1, 999),
            'description' => fake()->city(),
            'secret' => fake()->uuid(),
            'type' => 'default',
            'archived' => false,
            'settings' => ['terminal_id' => fake()->numerify('###-1')],
            'yandex_enabled' => false,
            'yandex_test' => false,
            'yandex_autooplata_enabled' => false,
            'loyal_client_enabled' => false,
        ];
    }
}
