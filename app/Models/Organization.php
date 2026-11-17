<?php

namespace App\Models;

use Database\Factories\OrganizationFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\WithoutIncrementing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A fuel retail company mirrored from usrbb.ru.
 */
#[WithoutIncrementing]
#[Guarded([])]
class Organization extends Model
{
    /** @use HasFactory<OrganizationFactory> */
    use HasFactory;

    /**
     * @return HasMany<OrganizationUser, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(OrganizationUser::class, 'org_id');
    }

    /**
     * @return HasMany<Terminal, $this>
     */
    public function terminals(): HasMany
    {
        return $this->hasMany(Terminal::class, 'org_id');
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'org_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'remote_created_at' => 'datetime',
            'remote_updated_at' => 'datetime',
        ];
    }
}
