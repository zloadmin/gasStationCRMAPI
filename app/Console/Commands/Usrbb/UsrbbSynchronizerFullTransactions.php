<?php

namespace App\Console\Commands\Usrbb;

use App\Models\Transaction;
use App\Services\Usrbb\TerminalSynchronizer;
use App\Services\Usrbb\TransactionSynchronizer;
use App\Services\Usrbb\UsrbbClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('usrbb:synchronizer-full-transactions')]
#[Description('Sync usrbb transaction full items')]
class UsrbbSynchronizerFullTransactions extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(UsrbbClient $client, TransactionSynchronizer $synchronizer): void
    {
        $notProcessedIds = Transaction::where('parsed_full', false)->orderByDesc('id')->pluck('id')->toArray();

        $fullCount = 0;
        foreach ($notProcessedIds as $id) {

            $transaction = $client->getTransaction($id);
            $fullCount += $synchronizer->sync([ 'rows' => [ $transaction ] ]);
        }

        $this->info('Synchronized transactions successfully. Count: ' . $fullCount);
    }
}
