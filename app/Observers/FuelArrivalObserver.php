<?php

namespace App\Observers;


use App\Jobs\SynchronizeFuelArrivals;
use App\Models\FuelArrival;

class FuelArrivalObserver
{
    public function created(FuelArrival $fuelArrival): void
    {
        SynchronizeFuelArrivals::dispatch();
    }

    public function updated(FuelArrival $fuelArrival): void
    {
        SynchronizeFuelArrivals::dispatch();
    }

    public function deleted(FuelArrival $fuelArrival): void
    {
        SynchronizeFuelArrivals::dispatch();
    }
}
