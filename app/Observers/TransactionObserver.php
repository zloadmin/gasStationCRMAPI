<?php

namespace App\Observers;

use App\Jobs\SynchronizeFuelArrivals;
use App\Models\Transaction;

class TransactionObserver
{
    public function created(Transaction $transaction): void
    {
        SynchronizeFuelArrivals::dispatch($transaction->id);
    }

    public function updated(Transaction $transaction): void
    {
        SynchronizeFuelArrivals::dispatch($transaction->id);
    }

    public function deleted(Transaction $transaction): void
    {
        SynchronizeFuelArrivals::dispatch($transaction->id);
    }
}
