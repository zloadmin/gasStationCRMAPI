<?php

namespace Database\Factories;

use App\Models\Terminal;
use App\Models\TerminalService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TerminalService>
 */
class TerminalServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $station = (string) fake()->numberBetween(1, 4);

        return [
            'id' => fake()->unique()->numberBetween(1, 1_000_000),
            'terminal_id' => Terminal::factory(),
            'station_id' => $station,
            'station_name' => $station,
            'name' => fake()->randomElement(['АИ-92', 'АИ-95', 'ДТ']),
            'price' => fake()->randomFloat(2, 50, 90),
        ];
    }
}
