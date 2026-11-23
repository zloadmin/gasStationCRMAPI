<?php

namespace App\Jobs;

use App\Models\Service;
use App\Models\Transaction;
use App\Models\FuelArrival;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SynchronizeFuelArrivals implements ShouldQueue, ShouldBeUnique, ShouldQueueAfterCommit
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public int $uniqueFor = 600;

    public function uniqueId(): string
    {
        return 'fuel-arrivals-synchronization';
    }

    public function __construct(public ?int $transaction_id = null)
    {

    }

    public function handle(): void
    {

        $serviceDic = Service::getIdNameDict();

        $this->getQuery()->chunkById(1000, function ($transactions) use (&$serviceDic) {
            foreach ($transactions as $transaction) {

                $arrival = FuelArrival::query()
                    ->where('service_id', $transaction->service_id)
                    ->where('date', '<=', $transaction->remote_created_at)
                    ->orderByDesc('date')
                    ->orderByDesc('id')
                    ->first();

                $transaction->updateFuelArrivalAndCostsFields($arrival, $serviceDic);

            }
        });
    }

    public function getQuery()
    {
        $query = Transaction::query()
            ->select([
                'id',
                'service_id',
                'remote_created_at',
                'fuel_arrival_id',
                'amount_dispensed',
                'service_price',
                'printed'
            ]);

        if ($this->transaction_id) {
            $query->where('id', $this->transaction_id);
        }
        return $query->orderByDesc('transactions.id');
    }
}
