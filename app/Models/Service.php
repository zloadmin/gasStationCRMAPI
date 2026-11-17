<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'org_id',
        'name',
        'description',
        'price',
        'currency',
        'good_name',
        'good_tax',
        'good_type',
        'archived',
        'data',
        'yandex_type',
        'remote_created_at',
        'remote_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'archived' => 'boolean',
            'data' => 'array',
            'remote_created_at' => 'datetime',
            'remote_updated_at' => 'datetime',
        ];
    }
}
