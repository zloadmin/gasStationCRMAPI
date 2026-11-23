<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FuelArrivalIndexRequest;
use App\Http\Requests\StoreFuelArrivalRequest;

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
        $query->select([
            'id',
            'date',
            'service_id',
            'quantity_kg',
            'volume_liters',
            'total_price',
            'price_per_liter_cashless',
            'price_per_liter_cash',
            'created_at',
            'updated_at',
        ]);

        $query->when(
            $filters['date_from'] ?? null,
            fn($query, $date) => $query->whereDate('date', '>=', $date)
        );

        $query->when(
            $filters['date_to'] ?? null,
            fn($query, $date) => $query->whereDate('date', '<=', $date)
        );

        $query->when(
            $filters['service_id'] ?? null,
            fn($query, $serviceId) => $query->where('service_id', $serviceId)
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


        $fuelArrival = FuelArrival::create($request->validated());

        return new FuelArrivalResource(
            $fuelArrival->load('service')
        );
    }

    /**
     * Обновление прихода.
     */
    public function update(StoreFuelArrivalRequest $request, FuelArrival $fuelArrival): FuelArrivalResource
    {

        $fuelArrival->update($request->validated());

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


}
