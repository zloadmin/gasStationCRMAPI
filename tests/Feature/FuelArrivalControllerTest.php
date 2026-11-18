<?php

namespace Tests\Feature;

use App\Models\FuelArrival;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FuelArrivalControllerTest extends TestCase
{
    use RefreshDatabase;

    protected int $serviceId;
    protected int $secondServiceId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serviceId = DB::table('services')->insertGetId([
            'org_id' => 1,
            'name' => 'Дизельное топливо',
            'price' => 65.50,
            'currency' => 643,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->secondServiceId = DB::table('services')->insertGetId([
            'org_id' => 1,
            'name' => 'Бензин АИ-95',
            'price' => 70,
            'currency' => 643,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createArrival(array $attributes = []): FuelArrival
    {
        return FuelArrival::create(array_merge([
            'date' => '2026-11-10 10:00:00',
            'service_id' => $this->serviceId,
            'quantity_kg' => 10000,
            'volume_liters' => 13333.333,
            'density' => 0.7500,
            'price_per_liter' => 65.50,
            'total_price' => 873333.33,
        ], $attributes));
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function test_returns_paginated_fuel_arrivals(): void
    {
        $this->createArrival();

        $this->createArrival([
            'date' => '2026-11-11 10:00:00',
        ]);

        $this->getJson('/api/fuel-arrivals')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'date',
                        'service_id',
                        'service' => [
                            'id',
                            'name',
                        ],
                        'quantity_kg',
                        'volume_liters',
                        'density',
                        'price_per_liter',
                        'total_price',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_filters_fuel_arrivals_by_date_period(): void
    {
        $this->createArrival([
            'date' => '2026-11-01 10:00:00',
        ]);

        $expected = $this->createArrival([
            'date' => '2026-11-10 10:00:00',
        ]);

        $this->createArrival([
            'date' => '2026-11-20 10:00:00',
        ]);

        $this->getJson(
            '/api/fuel-arrivals?date_from=2026-11-05&date_to=2026-11-15'
        )
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $expected->id);
    }

    public function test_filters_fuel_arrivals_by_service(): void
    {
        $this->createArrival();

        $this->createArrival([
            'service_id' => $this->secondServiceId,
        ]);

        $this->getJson(
            '/api/fuel-arrivals?service_id=' . $this->secondServiceId
        )
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.service_id', $this->secondServiceId)
            ->assertJsonPath('data.0.service.name', 'Бензин АИ-95');
    }

    public function test_validates_index_filters(): void
    {
        $this->getJson('/api/fuel-arrivals?date_from=invalid')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('date_from');

        $this->getJson(
            '/api/fuel-arrivals?date_from=2026-11-20&date_to=2026-11-10'
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('date_to');

        $this->getJson('/api/fuel-arrivals?service_id=999999')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('service_id');
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function test_returns_single_fuel_arrival(): void
    {
        $arrival = $this->createArrival();

        $this->getJson("/api/fuel-arrivals/{$arrival->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $arrival->id)
            ->assertJsonPath('data.service.name', 'Дизельное топливо')
            ->assertJsonPath('data.quantity_kg', '10000.000');
    }

    public function test_returns_404_when_fuel_arrival_does_not_exist(): void
    {
        $this->getJson('/api/fuel-arrivals/999999')
            ->assertNotFound();
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function test_creates_fuel_arrival_and_calculates_volume(): void
    {
        $response = $this->postJson('/api/fuel-arrivals', [
            'date' => '2026-11-18 10:00:00',
            'service_id' => $this->serviceId,
            'quantity_kg' => 10000,
            'density' => 0.7500,
            'price_per_liter' => 65.50,
            'total_price' => 873333.33,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.service.name', 'Дизельное топливо')
            ->assertJsonPath('data.quantity_kg', '10000.000')
            ->assertJsonPath('data.volume_liters', '13333.333')
            ->assertJsonPath('data.density', '0.7500');

        $this->assertDatabaseHas('fuel_arrivals', [
            'service_id' => $this->serviceId,
            'quantity_kg' => 10000,
            'volume_liters' => 13333.333,
            'density' => 0.7500,
        ]);
    }

    public function test_creates_fuel_arrival_and_calculates_density(): void
    {
        $this->postJson('/api/fuel-arrivals', [
            'date' => '2026-11-18 10:00:00',
            'service_id' => $this->serviceId,
            'quantity_kg' => 10000,
            'volume_liters' => 12500,
        ])
            ->assertCreated()
            ->assertJsonPath('data.density', '0.8000');
    }

    public function test_creates_fuel_arrival_and_calculates_mass(): void
    {
        $this->postJson('/api/fuel-arrivals', [
            'date' => '2026-11-18 10:00:00',
            'service_id' => $this->serviceId,
            'volume_liters' => 12500,
            'density' => 0.8000,
        ])
            ->assertCreated()
            ->assertJsonPath('data.quantity_kg', '10000.000');
    }

    public function test_validates_required_fields_when_creating(): void
    {
        $this->postJson('/api/fuel-arrivals', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'date',
                'service_id',
            ]);
    }

    public function test_requires_at_least_two_measurement_parameters(): void
    {
        $this->postJson('/api/fuel-arrivals', [
            'date' => '2026-11-18 10:00:00',
            'service_id' => $this->serviceId,
            'quantity_kg' => 10000,
        ])
            ->assertUnprocessable();
    }

    public function test_validates_service_when_creating(): void
    {
        $this->postJson('/api/fuel-arrivals', [
            'date' => '2026-11-18 10:00:00',
            'service_id' => 999999,
            'quantity_kg' => 10000,
            'density' => 0.7500,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('service_id');
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function test_updates_fuel_arrival(): void
    {
        $arrival = $this->createArrival();

        $this->patchJson("/api/fuel-arrivals/{$arrival->id}", [
            'date' => '2026-11-18 12:00:00',
            'price_per_liter' => 70,
            'total_price' => 900000,
        ])
            ->assertOk()
            ->assertJsonPath('data.price_per_liter', '70.00')
            ->assertJsonPath('data.total_price', '900000.00')
            ->assertJsonPath('data.quantity_kg', '10000.000');
    }

    public function test_supports_partial_update_using_patch(): void
    {
        $arrival = $this->createArrival();

        $this->patchJson("/api/fuel-arrivals/{$arrival->id}", [
            'price_per_liter' => 70,
        ])
            ->assertOk()
            ->assertJsonPath('data.price_per_liter', '70.00');
    }

    public function test_recalculates_measurement_when_another_is_cleared(): void
    {
        $arrival = $this->createArrival();

        $this->patchJson("/api/fuel-arrivals/{$arrival->id}", [
            'volume_liters' => null,
        ])
            ->assertOk()
            ->assertJsonPath('data.volume_liters', '13333.333');
    }

    public function test_validates_measurement_parameters_when_updating(): void
    {
        $arrival = $this->createArrival();

        $this->patchJson("/api/fuel-arrivals/{$arrival->id}", [
            'quantity_kg' => null,
            'volume_liters' => null,
            'density' => null,
        ])
            ->assertUnprocessable();
    }

    public function test_returns_404_when_updating_missing_fuel_arrival(): void
    {
        $this->putJson('/api/fuel-arrivals/999999', [
            'price_per_liter' => 70,
        ])
            ->assertNotFound();
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function test_deletes_fuel_arrival(): void
    {
        $arrival = $this->createArrival();

        $this->deleteJson("/api/fuel-arrivals/{$arrival->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('fuel_arrivals', [
            'id' => $arrival->id,
        ]);
    }

    public function test_returns_404_when_deleting_missing_fuel_arrival(): void
    {
        $this->deleteJson('/api/fuel-arrivals/999999')
            ->assertNotFound();
    }
}
