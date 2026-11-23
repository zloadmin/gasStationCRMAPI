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

            'quantity_kg' => ['required', 'numeric', 'gt:0'],
            'volume_liters' => ['required', 'numeric', 'gt:0'],

            'total_price' => ['required', 'numeric', 'gte:0'],
            'price_per_liter_cashless' => ['required', 'numeric', 'gte:0'],
            'price_per_liter_cash' => ['required', 'numeric', 'gte:0'],
        ];
    }

}
