<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\WithoutIncrementing;
use Illuminate\Database\Eloquent\Model;

#[WithoutIncrementing]
#[Guarded([])]
class Service extends Model
{


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
    static function getIdNameDict(): array
    {
        return self::pluck('name', 'id')->toArray();
    }
}
