<?php

namespace App\Console\Commands\Usrbb;

use App\Services\Usrbb\ServiceSynchronizer;

use App\Services\Usrbb\UsrbbClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('usrbb:synchronizer-services')]
#[Description('Sync usrbb service items')]
class UsrbbSynchronizerServices extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(UsrbbClient $client, ServiceSynchronizer $synchronizer)
    {
        $services = $client->getServices();
        $count = $synchronizer->sync($services);
        $this->info('Synchronized services successfully. Count: ' . $count);
    }
}
