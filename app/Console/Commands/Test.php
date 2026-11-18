<?php

namespace App\Console\Commands;

use App\Services\Usrbb\UsrbbClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('t')]
#[Description('Test some code')]
class Test extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(UsrbbClient $client): void
    {

        $client->login();
    }
}
