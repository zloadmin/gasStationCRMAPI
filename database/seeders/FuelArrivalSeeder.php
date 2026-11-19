<?php

namespace Database\Seeders;

use App\Models\FuelArrival;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class FuelArrivalSeeder extends Seeder
{
    public function run(): void
    {
        $services = Service::query()->pluck('id');

        if ($services->isEmpty()) {
            $this->command->warn('Нет услуг в таблице services. Сидер пропущен.');

            return;
        }

        $date = Carbon::now()->subYear();
        $endDate = Carbon::now();

        while ($date->lt($endDate)) {
            $date->addDays(rand(3, 10));

            if ($date->gt($endDate)) {
                break;
            }

            $volume = rand(9500, 10500);
            $weight = rand(71000, 75000);

            // Случайная цена за литр: 55–75 рублей.
            $pricePerLiter = rand(5500, 7500) / 100;

            FuelArrival::query()->create([
                'date' => $date->copy()
                    ->setTime(rand(6, 20), rand(0, 59), rand(0, 59)),

                'service_id' => $services->random(),

                'volume_liters' => $volume,
                'quantity_kg' => $weight,

                'total_price' => round(
                    $volume * $pricePerLiter,
                    2
                ),
            ]);
        }
    }
}
