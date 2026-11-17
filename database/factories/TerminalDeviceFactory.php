<?php

namespace Database\Factories;

use App\Models\Terminal;
use App\Models\TerminalDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TerminalDevice>
 */
class TerminalDeviceFactory extends Factory
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
            'terminal_id' => Terminal::factory(),
            'device_name' => fake()->randomElement(['Topaz', 'Kaznachey', 'Printer', 'CashcodeSM', 'Vendotek', 'LevelPMP', 'WSRemote', 'Tank']),
            'last_report' => ['started' => true],
            'last_report_at' => now(),
            'last_report_time' => now()->timestamp,
            'last_error' => '',
            'from_device' => false,
        ];
    }
}
