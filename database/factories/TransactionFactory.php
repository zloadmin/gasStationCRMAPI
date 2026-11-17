<?php

namespace Database\Factories;

use App\Models\TerminalService;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $litrePrice = fake()->randomFloat(2, 50, 90);
        $total = fake()->numberBetween(5, 40) * 100;
        $litres = round($total / $litrePrice, 2);

        return [
            'id' => fake()->unique()->numberBetween(1, 100_000_000),
            'tid' => fake()->unique()->numberBetween(1, 1_000_000),
            'terminal_id' => fn (array $attributes) => TerminalService::find($attributes['service_id'])?->terminal_id,
            'service_id' => TerminalService::factory(),
            'type' => fake()->randomElement(['payment_cash', 'payment_card']),
            'price' => $total,
            'inserted' => $total,
            'dispensed' => 0,
            'price_returned' => 0,
            'discount' => 0,
            'currency' => 643,
            'service_price' => $litrePrice,
            'amount' => $litres,
            'amount_dispensed' => $litres,
            'printed' => false,
            'sync' => false,
            'no_troubles' => true,
            'approved' => false,
            'remote_created_at' => now(),
        ];
    }
}
