<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FuelArrivalIndexRequest;
use App\Http\Requests\StoreFuelArrivalRequest;
use App\Http\Requests\UpdateFuelArrivalRequest;
use App\Http\Resources\FuelArrivalResource;
use App\Models\FuelArrival;
use Illuminate\Http\JsonResponse;

class FuelArrivalController extends Controller
{
    /**
     * Список приходов топлива.
     */
    public function index(FuelArrivalIndexRequest $request)
    {
        $filters = $request->validated();

        $query = FuelArrival::query()->with('service');

        $query->when(
            $filters['date_from'] ?? null,
            fn ($query, $date) => $query->whereDate('date', '>=', $date)
        );

        $query->when(
            $filters['date_to'] ?? null,
            fn ($query, $date) => $query->whereDate('date', '<=', $date)
        );

        $query->when(
            $filters['service_id'] ?? null,
            fn ($query, $serviceId) => $query->where('service_id', $serviceId)
        );

        return FuelArrivalResource::collection(
            $query->orderByDesc('date')
                ->paginate($filters['per_page'] ?? 15)
        );
    }

    /**
     * Просмотр прихода.
     */
    public function show(FuelArrival $fuelArrival): FuelArrivalResource
    {
        return new FuelArrivalResource(
            $fuelArrival->load('service')
        );
    }

    /**
     * Создание прихода.
     */
    public function store(StoreFuelArrivalRequest $request): FuelArrivalResource
    {
        $data = $this->calculateMeasurements($request->validated());

        $fuelArrival = FuelArrival::create($data);

        return new FuelArrivalResource(
            $fuelArrival->load('service')
        );
    }

    /**
     * Обновление прихода.
     */
    public function update(
        UpdateFuelArrivalRequest $request,
        FuelArrival $fuelArrival
    ): FuelArrivalResource {
        $data = $request->validated();

        $measurements = array_merge(
            $fuelArrival->only([
                'quantity_kg',
                'volume_liters',
                'density',
            ]),
            $data
        );

        $calculated = $this->calculateMeasurements($measurements);

        foreach (['quantity_kg', 'volume_liters', 'density'] as $field) {
            if (($measurements[$field] ?? null) === null) {
                $data[$field] = $calculated[$field] ?? null;
            }
        }

        $fuelArrival->update($data);

        return new FuelArrivalResource(
            $fuelArrival->refresh()->load('service')
        );
    }

    /**
     * Удаление прихода.
     */
    public function destroy(FuelArrival $fuelArrival): JsonResponse
    {
        $fuelArrival->delete();

        return response()->json(null, 204);
    }

    /**
     * Расчет отсутствующего параметра.
     */
    private function calculateMeasurements(array $data): array
    {
        $kg = $data['quantity_kg'] ?? null;
        $liters = $data['volume_liters'] ?? null;
        $density = $data['density'] ?? null;

        if ($kg !== null && $density !== null && $liters === null) {
            $data['volume_liters'] = round($kg / $density, 3);
        } elseif ($kg !== null && $liters !== null && $density === null) {
            $data['density'] = round($kg / $liters, 4);
        } elseif ($liters !== null && $density !== null && $kg === null) {
            $data['quantity_kg'] = round($liters * $density, 3);
        }

        return $data;
    }
}
