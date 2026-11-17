<?php

namespace App\Models;

use Database\Factories\TerminalDeviceFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\WithoutIncrementing;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A peripheral attached to a terminal (fuel dispenser, fiscal register, bill acceptor, etc.).
 */
#[WithoutIncrementing]
#[Guarded([])]
class TerminalDevice extends Model
{
    /** @use HasFactory<TerminalDeviceFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Terminal, $this>
     */
    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class);
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
            'last_report' => 'array',
            'last_report_at' => 'datetime',
            'last_report_time' => 'integer',
            'from_device' => 'boolean',
            'remote_created_at' => 'datetime',
            'remote_updated_at' => 'datetime',
        ];
    }
}
