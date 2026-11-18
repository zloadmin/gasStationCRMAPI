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
        'total_price',
    ];
    protected $appends = [
        'density',
        'price_per_liter'
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'quantity_kg' => 'decimal:3',
            'volume_liters' => 'decimal:3',
            'density' => 'decimal:4',
            'price_per_liter' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function getDensityAttribute(): float
    {
        return round($this->quantity_kg / $this->volume_liters, 4);
    }
    public function getPricePerLiterAttribute(): float
    {
        return round($this->total_price / $this->volume_liters, 2);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }


}
