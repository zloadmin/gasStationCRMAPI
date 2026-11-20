<?php

namespace App\Models;

use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\WithoutIncrementing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A sale made on a terminal, mirrored from usrbb.ru.
 */
#[WithoutIncrementing]
#[Guarded([])]
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    protected $hidden = [
        'service_json',
        'data',
    ];

    /**
     * @return BelongsTo<Terminal, $this>
     */
    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class);
    }

    /**
     * @return BelongsTo<TerminalService, $this>
     */
    public function terminalService(): BelongsTo
    {
        return $this->belongsTo(TerminalService::class, 'terminal_service_id', 'id');
    }
    /**
     * @return BelongsTo<TerminalService, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function fuelArrival(): BelongsTo
    {
        return $this->belongsTo(FuelArrival::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tid' => 'integer',
            'terminal_id' => 'integer',
            'service_id' => 'integer',
            'price' => 'decimal:2',
            'inserted' => 'decimal:2',
            'dispensed' => 'decimal:2',
            'price_returned' => 'decimal:2',
            'discount' => 'decimal:2',
            'currency' => 'integer',
            'service_price' => 'decimal:2',
            'amount' => 'decimal:2',
            'amount_dispensed' => 'decimal:2',
            'fisc' => 'array',
            'printed' => 'boolean',
            'sync' => 'boolean',
            'no_troubles' => 'boolean',
            'approved' => 'boolean',
            'user_id' => 'integer',
            'remote_created_at' => 'datetime',
            'service_json' => 'json',
            'data' => 'json',
        ];
    }
}
