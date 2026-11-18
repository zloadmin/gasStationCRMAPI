<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FuelArrivalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date,

            'service_id' => $this->service_id,
            'service' => new ServiceResource(
                $this->whenLoaded('service')
            ),

            'quantity_kg' => $this->quantity_kg,
            'volume_liters' => $this->volume_liters,
            'density' => $this->density,

            'price_per_liter' => $this->price_per_liter,
            'total_price' => $this->total_price,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
