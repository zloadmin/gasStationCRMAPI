<?php

namespace App\Console\Commands\Usrbb;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('usrbb:synchronizer')]
#[Description('Command description')]
class UsrbbSynchronizer extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->call('usrbb:synchronizer-services');
        $this->call('usrbb:synchronizer-terminals');
        $this->call('usrbb:synchronizer-transactions');
    }
}
