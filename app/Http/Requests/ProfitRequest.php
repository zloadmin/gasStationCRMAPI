<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfitRequest extends FormRequest
{


    public function rules(): array
    {
        return [
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
        ];
    }
}
