<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreFuelArrivalRequest extends FormRequest
{


    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'service_id' => ['required', 'integer', 'exists:services,id'],

            'quantity_kg' => ['nullable', 'numeric', 'gt:0'],
            'volume_liters' => ['nullable', 'numeric', 'gt:0'],
            'density' => ['nullable', 'numeric', 'gt:0'],

            'price_per_liter' => ['nullable', 'numeric', 'gte:0'],
            'total_price' => ['nullable', 'numeric', 'gte:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $data = $this->all();

            $measurements = [
                'quantity_kg',
                'volume_liters',
                'density',
            ];

            $filled = collect($measurements)
                ->filter(fn ($field) => !empty($data[$field]))
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
