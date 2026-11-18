<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelArrival extends Model
{
    protected $fillable = [
        'date',
        'service_id',
        'quantity_kg',
        'volume_liters',
        'density',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'quantity_kg' => 'decimal:3',
            'volume_liters' => 'decimal:3',
            'density' => 'decimal:4',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
