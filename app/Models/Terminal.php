<?php

namespace App\Models;

use Database\Factories\TerminalFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\WithoutIncrementing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A fuel kiosk terminal mirrored from usrbb.ru.
 */
#[WithoutIncrementing]
#[Guarded([])]
#[Hidden(['secret', 'yandex_token'])]
class Terminal extends Model
{
    /** @use HasFactory<TerminalFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * @return HasMany<TerminalDevice, $this>
     */
    public function devices(): HasMany
    {
        return $this->hasMany(TerminalDevice::class);
    }

    /**
     * @return HasMany<TerminalService, $this>
     */
    public function services(): HasMany
    {
        return $this->hasMany(TerminalService::class);
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'org_id' => 'integer',
            'place_id' => 'integer',
            'archived' => 'boolean',
            'terminal_info' => 'array',
            'settings' => 'array',
            'settings_updated_at' => 'datetime',
            'last_query_at' => 'datetime',
            'yandex_enabled' => 'boolean',
            'yandex_test' => 'boolean',
            'yandex_location' => 'array',
            'yandex_autooplata_enabled' => 'boolean',
            'ping_settings' => 'array',
            'ping_at' => 'datetime',
            'loyal_client_enabled' => 'boolean',
            'remote_created_at' => 'datetime',
            'remote_updated_at' => 'datetime',
        ];
    }
}
