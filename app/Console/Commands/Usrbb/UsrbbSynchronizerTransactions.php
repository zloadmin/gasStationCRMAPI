<?php

namespace App\Console\Commands\Usrbb;

use App\Services\Usrbb\TerminalSynchronizer;
use App\Services\Usrbb\TransactionSynchronizer;
use App\Services\Usrbb\UsrbbClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('usrbb:synchronizer-transactions')]
#[Description('Sync usrbb transaction items')]
class UsrbbSynchronizerTransactions extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(UsrbbClient $client, TransactionSynchronizer $synchronizer) : void
    {
        $hasMore = true;
        $fullCount = 0;
        $page = 0;
        while ($hasMore) {
            $this->info('Process transactions page ' . $page);
            $transactions = $client->getTransactions($page);
            $count = $synchronizer->sync($transactions);
            $fullCount += $count;
            $page++;
            if(!$transactions['rows'] ?? []) {
                $hasMore = false;
            }
        }
        $this->info('Synchronized transactions successfully. Count: ' . $fullCount);

        $this->info('Synchronized terminals successfully. Count: ' . $count);
    }
}
