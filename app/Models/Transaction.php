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
            'tid'               => 'integer',
            'terminal_id'       => 'integer',
            'service_id'        => 'integer',
            'price'             => 'decimal:2',
            'inserted'          => 'decimal:2',
            'dispensed'         => 'decimal:2',
            'price_returned'    => 'decimal:2',
            'discount'          => 'decimal:2',
            'currency'          => 'integer',
            'service_price'     => 'decimal:2',
            'amount'            => 'decimal:2',
            'amount_dispensed'  => 'decimal:2',
            'fisc'              => 'array',
            'printed'           => 'boolean',
            'sync'              => 'boolean',
            'no_troubles'       => 'boolean',
            'approved'          => 'boolean',
            'user_id'           => 'integer',
            'remote_created_at' => 'datetime',
            'service_json'      => 'json',
            'data'              => 'json',

            'fuel_arrival_price_per_liter_cashless' => 'decimal:2',
            'fuel_arrival_price_per_liter_cash'     => 'decimal:2',
            'profit_revenue'                        => 'decimal:2',
            'profit_cost'                           => 'decimal:2',
            'profit_profit'                         => 'decimal:2',
        ];
    }

    public function updateFuelArrivalAndCostsFields(?FuelArrival $arrival, array &$serviceDic)
    {

        $revenue = $arrival ? $this->getProfitRevenue() : null;
        $cost = $arrival ? $this->getProfitCost($arrival) : null;
        $profit = $arrival ? $revenue - $cost : null;
        $fuel_arrival_price_per_liter = $arrival ? $this->getPricePerLiter($arrival) : null;

        $this->update([
            'fuel_arrival_id'                       => $arrival ? $arrival->id : null,
            'fuel_arrival_price_per_liter_cashless' => $arrival ? $arrival->price_per_liter_cashless : null,
            'fuel_arrival_price_per_liter_cash'     => $arrival ? $arrival->price_per_liter_cash : null,
            'fuel_arrival_price_per_liter'          => $arrival ? $fuel_arrival_price_per_liter : null,
            'profit_service_name'                   => $arrival ? $serviceDic[$arrival->service_id] ?? null : null,
            'profit_revenue'                        => $revenue,
            'profit_cost'                           => $cost,
            'profit_profit'                         => $profit,
        ]);
    }

    public function getProfitRevenue(): float
    {
        return $this->amount_dispensed * $this->service_price;
    }

    public function getProfitCost(FuelArrival $arrival): float
    {
        return $this->amount_dispensed * $this->getPricePerLiter($arrival);
    }

    public function getPricePerLiter(FuelArrival $arrival): float
    {
        return $this->printed ? $arrival->price_per_liter_cashless : $arrival->price_per_liter_cash;
    }


}
