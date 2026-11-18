<?php

namespace App\Http\Requests;

use App\Models\FuelArrival;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateFuelArrivalRequest extends FormRequest
{


    public function rules(): array
    {
        return [
            'date' => ['sometimes', 'required', 'date'],
            'service_id' => ['sometimes', 'required', 'integer', 'exists:services,id'],

            'quantity_kg' => ['sometimes', 'nullable', 'numeric', 'gt:0'],
            'volume_liters' => ['sometimes', 'nullable', 'numeric', 'gt:0'],
            'density' => ['sometimes', 'nullable', 'numeric', 'gt:0'],

            'price_per_liter' => ['sometimes', 'nullable', 'numeric', 'gte:0'],
            'total_price' => ['sometimes', 'nullable', 'numeric', 'gte:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var FuelArrival $fuelArrival */
            $fuelArrival = $this->route('fuel_arrival');

            $data = array_merge(
                $fuelArrival->only([
                    'quantity_kg',
                    'volume_liters',
                    'density',
                ]),
                $this->all()
            );

            $filled = collect([
                'quantity_kg',
                'volume_liters',
                'density',
            ])->filter(fn ($field) => $data[$field] !== null && $data[$field] !== '')
                ->count();

            if ($filled < 2) {
                $validator->errors()->add(
                    'quantity_kg',
                    'Необходимо указать минимум два параметра: массу, объем или плотность.'
                );
            }
        });
    }
}
