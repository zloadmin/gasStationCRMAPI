<?php

namespace App\Models;

use Database\Factories\TerminalServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\WithoutIncrementing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A fuel grade bound to a dispenser station on a terminal, mirrored from usrbb.ru.
 */
#[WithoutIncrementing]
#[Guarded([])]
class TerminalService extends Model
{
    /** @use HasFactory<TerminalServiceFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Terminal, $this>
     */
    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class);
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'service_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'terminal_id' => 'integer',
            'price' => 'decimal:2',
        ];
    }
}
