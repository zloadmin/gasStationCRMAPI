<?php

namespace App\Console\Commands\Usrbb;

use App\Services\Usrbb\UsrbbClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

#[Signature('usrbb:synchronizer {--full}')]
#[Description('Command description')]
class UsrbbSynchronizer extends Command
{
    /**
     * Execute the console command.
     * @throws RequestException
     * @throws ConnectionException
     */
    public function handle(UsrbbClient $client)
    {


        $client->loginIsNeeded();

        $this->call('usrbb:synchronizer-services');
        $this->call('usrbb:synchronizer-terminals');
        $this->call('usrbb:synchronizer-transactions', [
            '--full' => $this->option('full'),
        ]);
        $this->call('usrbb:synchronizer-full-transactions');
    }
}
