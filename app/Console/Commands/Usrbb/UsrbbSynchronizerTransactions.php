<?php

namespace App\Console\Commands\Usrbb;

use App\Services\Usrbb\TerminalSynchronizer;
use App\Services\Usrbb\TransactionSynchronizer;
use App\Services\Usrbb\UsrbbClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('usrbb:synchronizer-transactions {--full}')]
#[Description('Sync usrbb transaction items')]
class UsrbbSynchronizerTransactions extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(UsrbbClient $client, TransactionSynchronizer $synchronizer): void
    {
        $isFull = $this->option('full');

        $hasMore = true;
        $fullCount = 0;
        $page = 0;
        while ($hasMore) {
            $perPage = $isFull ? 100 : 10;
            $this->info('Process transactions page ' . $page. '. Per page ' . $perPage);
            $transactions = $client->getTransactions($page, $perPage);

            $count = $synchronizer->sync($transactions);
            $fullCount += $count;
            if (!$isFull) {
                $hasMore = false;
            }
            $page++;
            if (!$transactions['rows'] ?? []) {
                $hasMore = false;
            }
        }
        $this->info('Synchronized transactions successfully. Count: ' . $fullCount);

    }
}
