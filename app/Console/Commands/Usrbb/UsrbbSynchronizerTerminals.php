<?php

namespace App\Console\Commands\Usrbb;

use App\Services\Usrbb\TerminalSynchronizer;
use App\Services\Usrbb\UsrbbClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('usrbb:synchronizer-terminals')]
#[Description('Sync usrbb terminal items with device items')]
class UsrbbSynchronizerTerminals extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(UsrbbClient $client, TerminalSynchronizer $synchronizer)
    {
        $terminals = $client->getTerminals();
        $count = $synchronizer->sync($terminals);
        $this->info('Synchronized terminals successfully. Count: ' . $count);
    }
}
