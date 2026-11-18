<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexTransactionRequest extends FormRequest
{


    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],

            'service_id' => ['nullable', 'integer', 'exists:services,id'],

            'terminal_id' => ['nullable', 'integer', 'exists:terminals,id'],

            'column_id' => ['nullable', 'integer'],

            'type' => ['nullable', 'string', 'max:100'],

            'status' => ['nullable', 'string', 'max:100'],

            'id' => ['nullable', 'integer', 'min:1'],

            'tid' => ['nullable', 'integer', 'min:1'],

            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
